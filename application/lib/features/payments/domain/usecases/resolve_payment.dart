import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/features/payments/domain/repositories/payments_repository.dart';
import 'package:application/shared/domain/entities/payment_next.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'resolve_payment.freezed.dart';

/// Where a payment ended up, as far as the server knows.
@freezed
sealed class PaymentResolution with _$PaymentResolution {
  /// The server says `payment_status: paid`. The only state that may be shown as paid.
  const factory PaymentResolution.paid(OrderPaymentStatus status) =
      PaymentResolved;

  /// `is_final` without payment (failed, expired, cancelled).
  const factory PaymentResolution.finished(OrderPaymentStatus status) =
      PaymentFinished;

  /// Two minutes of polling without a final answer: "still processing, we will notify you".
  /// [lastKnown] is null when no status call succeeded.
  const factory PaymentResolution.stillProcessing(
    OrderPaymentStatus? lastKnown,
  ) = PaymentStillProcessing;
}

/// After any provider UI closes (success, cancel or crash), confirms with the server and polls
/// the order status (MOBILE_APP_ROADMAP.md 5):
///
/// 1. PayPal: `paypal/capture`; wallet: nothing; every other type and a resume after restart:
///    `verify-payment`.
/// 2. `GET status` after 2 s, 3 s, 5 s, then every 10 s, up to [budget] (2 minutes) from the
///    start of polling, until `is_final` or `payment_status` is `paid`.
///
/// It never reports paid unless the server does. Transient failures (network, timeout, 429, 5xx)
/// keep polling; any other failure (404, 401, ...) stops with that failure.
class ResolvePayment {
  ResolvePayment(
    this._payments, {
    Future<void> Function(Duration delay)? wait,
    DateTime Function()? now,
  }) : _wait = wait ?? Future<void>.delayed,
       _now = now ?? DateTime.now;

  static const schedule = [
    Duration(seconds: 2),
    Duration(seconds: 3),
    Duration(seconds: 5),
  ];
  static const steadyInterval = Duration(seconds: 10);
  static const budget = Duration(minutes: 2);

  final PaymentsRepository _payments;
  final Future<void> Function(Duration delay) _wait;
  final DateTime Function() _now;

  /// [next] is the step that was shown to the customer, or null when resuming.
  Future<Result<PaymentResolution>> call(
    String orderPublicId, {
    PaymentNext? next,
  }) async {
    final Future<Result<OrderPaymentStatus>>? confirmation = switch (next) {
      PayPalNext() => _payments.capturePayPal(orderPublicId),
      WalletNext() => null,
      _ => _payments.verifyPayment(orderPublicId),
    };

    OrderPaymentStatus? last;
    if (confirmation != null) {
      final confirmed = await confirmation;
      switch (confirmed) {
        case Ok(:final value):
          last = value;
          final decided = _decide(value);
          if (decided != null) {
            return Ok(decided);
          }
        case Err(:final failure):
          if (!_transient(failure)) {
            return Err(failure);
          }
      }
    }

    final start = _now();
    for (var attempt = 0; ; attempt++) {
      final delay = attempt < schedule.length
          ? schedule[attempt]
          : steadyInterval;
      if (_now().difference(start) + delay > budget) {
        return Ok(PaymentResolution.stillProcessing(last));
      }
      await _wait(delay);
      final polled = await _payments.status(orderPublicId);
      switch (polled) {
        case Ok(:final value):
          last = value;
          final decided = _decide(value);
          if (decided != null) {
            return Ok(decided);
          }
        case Err(:final failure):
          if (!_transient(failure)) {
            return Err(failure);
          }
      }
    }
  }

  static PaymentResolution? _decide(OrderPaymentStatus status) {
    if (status.isPaid) {
      return PaymentResolution.paid(status);
    }
    if (status.isFinal) {
      return PaymentResolution.finished(status);
    }
    return null;
  }

  static bool _transient(Failure failure) =>
      failure.isRetryable || failure is ServerFailure;
}
