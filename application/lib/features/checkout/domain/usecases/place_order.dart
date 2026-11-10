import 'dart:convert';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/checkout/domain/repositories/checkout_repository.dart';
import 'package:application/features/payments/domain/repositories/payments_repository.dart';
import 'package:application/shared/domain/entities/payment_next.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'place_order.freezed.dart';

/// What the app does after place-order (MOBILE_APP_ROADMAP.md 5).
@freezed
sealed class PlaceOrderOutcome with _$PlaceOrderOutcome {
  /// Start the provider step [next] for [order].
  const factory PlaceOrderOutcome.pay({
    required PlacedOrder order,
    required PaymentNext next,
    required bool replayed,
  }) = PlaceOrderPay;

  /// `meta.duplicate: true`: an open order with the same contents exists; go to its status
  /// screen instead of paying again.
  const factory PlaceOrderOutcome.duplicate({required PlacedOrder order}) =
      PlaceOrderDuplicate;

  /// The order exists but no attempt could be started, even after retry-payment ([failure]).
  /// Show the status screen with a retry.
  const factory PlaceOrderOutcome.paymentNotStarted({
    required PlacedOrder order,
    required Failure failure,
  }) = PlaceOrderPaymentNotStarted;
}

/// Places the order exactly once per checkout attempt.
///
/// - The `Idempotency-Key` (ULID) is generated once per request body and stored with the body
///   hash in the pending checkout before the request is sent. A retry, or a restart, with the
///   same body re-sends the same key, so the server replays instead of creating a second order.
///   A changed body (for example a new `expected_total` after `price_changed`) gets a new key.
/// - `meta.duplicate: true` routes to the existing order.
/// - `next: null` (replay, or the provider was down) calls retry-payment once.
class PlaceOrder {
  PlaceOrder({
    required this._checkout,
    required this._payments,
    required this._pending,
    required this._newKey,
  });

  final CheckoutRepository _checkout;
  final PaymentsRepository _payments;
  final PendingCheckoutStore _pending;
  final String Function() _newKey;

  /// Stable hash of the body: canonical JSON (sorted keys), FNV-1a 64-bit, hex.
  static String bodyHash(Map<String, Object?> body) {
    final canonical = jsonEncode(_canonical(body));
    var hash = 0xcbf29ce484222325;
    const prime = 0x100000001b3;
    for (final byte in utf8.encode(canonical)) {
      hash ^= byte;
      hash = (hash * prime) & 0xFFFFFFFFFFFFFFFF;
    }
    return hash.toUnsigned(64).toRadixString(16).padLeft(16, '0');
  }

  static Object? _canonical(Object? value) {
    if (value is Map) {
      final keys = value.keys.map((key) => key.toString()).toList()..sort();
      return {for (final key in keys) key: _canonical(value[key])};
    }
    if (value is List) {
      return [for (final item in value) _canonical(item)];
    }
    return value;
  }

  /// The idempotency key to use for [hash]: the stored one when the body is unchanged.
  Future<String> _keyFor(String hash) async {
    final stored = await _pending.read();
    if (stored != null && stored.bodyHash == hash) {
      return stored.idempotencyKey;
    }
    final key = _newKey();
    await _pending.write(PendingCheckout(idempotencyKey: key, bodyHash: hash));
    return key;
  }

  Future<Result<PlaceOrderOutcome>> call(PlaceOrderRequest request) async {
    final hash = bodyHash(request.toBody());
    final key = await _keyFor(hash);

    final placed = await _checkout.placeOrder(request, idempotencyKey: key);
    switch (placed) {
      case Err(:final failure):
        if (_isFinalRejection(failure)) {
          await _pending.clear();
        }
        return Err(failure);
      case Ok(:final value):
        await _pending.write(
          PendingCheckout(
            idempotencyKey: key,
            bodyHash: hash,
            orderPublicId: value.order.publicId,
          ),
        );
        if (value.duplicate) {
          return Ok(PlaceOrderOutcome.duplicate(order: value.order));
        }
        final next = value.next;
        if (next != null) {
          return Ok(
            PlaceOrderOutcome.pay(
              order: value.order,
              next: next,
              replayed: value.replayed,
            ),
          );
        }
        final retried = await _payments.retryPayment(value.order.publicId);
        return Ok(
          retried.fold(
            (retryNext) => PlaceOrderOutcome.pay(
              order: value.order,
              next: retryNext,
              replayed: value.replayed,
            ),
            (failure) => PlaceOrderOutcome.paymentNotStarted(
              order: value.order,
              failure: failure,
            ),
          ),
        );
    }
  }

  /// The order of a checkout interrupted after placement (app restart), to resume at its status
  /// screen and verify again; null when nothing was placed.
  Future<String?> pendingOrder() async =>
      (await _pending.read())?.orderPublicId;

  /// Forget the pending checkout once the order reached a final state.
  Future<void> finish() => _pending.clear();

  /// The server refused this body for good: the next attempt has a different body and key.
  /// Transport errors, timeouts, 5xx, 429 and `idempotency_in_progress` keep the key.
  static bool _isFinalRejection(Failure failure) => switch (failure) {
    ValidationFailure() || DomainFailure() => true,
    ConflictFailure(:final code) => code == 'idempotency_mismatch',
    _ => false,
  };
}
