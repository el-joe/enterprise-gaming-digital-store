import 'dart:convert';

import 'package:application/core/network/result.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/checkout/data/datasources/checkout_remote_data_source.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/checkout/domain/repositories/checkout_repository.dart';
import 'package:application/shared/data/models/payment_models.dart';
import 'package:application/shared/domain/entities/cart.dart';
import 'package:application/shared/domain/entities/payment_next.dart';

class CheckoutRepositoryImpl implements CheckoutRepository {
  CheckoutRepositoryImpl(this._remote, this._store);

  final CheckoutRemoteDataSource _remote;
  final SecureStore _store;

  Future<void> _keep(GuestOrderTokenDto? token) async {
    if (token != null) {
      await _store.writeGuestOrderToken(token.orderPublicId, token.token);
    }
  }

  @override
  Future<Result<Cart>> validate({required String country, String? email}) =>
      guardRequest(
        () async =>
            (await _remote.validate(country: country, email: email)).toEntity(),
      );

  @override
  Future<Result<List<PaymentOption>>> paymentOptions({
    required String country,
    String? email,
  }) => guardRequest(
    () async => [
      for (final dto in await _remote.paymentOptions(
        country: country,
        email: email,
      ))
        dto.toEntity(),
    ],
  );

  @override
  Future<Result<PlaceOrderResponse>> placeOrder(
    PlaceOrderRequest request, {
    required String idempotencyKey,
  }) => guardRequest(() async {
    final response = await _remote.placeOrder(
      request.toBody(),
      idempotencyKey: idempotencyKey,
    );
    final data = response.envelope.data;
    await _keep(data.guestOrderToken);
    return PlaceOrderResponse(
      order: data.order.toEntity(),
      next: data.next?.toEntity(),
      duplicate: response.duplicate,
      replayed: response.replayed,
    );
  });

  @override
  Future<Result<OrderPaymentStatus>> status(String orderPublicId) =>
      guardRequest(
        () async => (await _remote.status(orderPublicId)).toEntity(),
      );

  @override
  Future<Result<void>> sendGuestOtp({
    required String orderPublicId,
    required String email,
  }) => guardRequest(
    () => _remote.sendGuestOtp(orderPublicId: orderPublicId, email: email),
  );

  @override
  Future<Result<void>> verifyGuestOtp({
    required String orderPublicId,
    required String code,
  }) => guardRequest(() async {
    final verified = await _remote.verifyGuestOtp(
      orderPublicId: orderPublicId,
      code: code,
    );
    await _keep(verified.guestOrderToken);
  });

  @override
  Future<Result<GuestLinkAccess>> openGuestLink(Uri signedLink) =>
      guardRequest(() async {
        final segments = signedLink.pathSegments;
        final index = segments.indexOf('orders');
        if (index < 0 || index + 1 >= segments.length) {
          throw const FormatException('Not a guest order access link.');
        }
        final orderPublicId = segments[index + 1];
        final access = await _remote.openGuestLink(
          orderPublicId,
          signedLink.queryParameters,
        );
        await _keep(access.guestOrderToken);
        return GuestLinkAccess(
          orderPublicId: access.orderPublicId,
          otpRequired: access.otpRequired,
        );
      });
}

/// [PendingCheckoutStore] on secure storage (one pending checkout at a time).
class SecurePendingCheckoutStore implements PendingCheckoutStore {
  SecurePendingCheckoutStore(this._store);

  final SecureStore _store;

  @override
  Future<PendingCheckout?> read() async {
    final raw = await _store.readPendingCheckout();
    if (raw == null) {
      return null;
    }
    try {
      final json = jsonDecode(raw) as Map<String, dynamic>;
      return PendingCheckout(
        idempotencyKey: json['idempotency_key'] as String,
        bodyHash: json['body_hash'] as String,
        orderPublicId: json['order_public_id'] as String?,
      );
    } on Object {
      await _store.deletePendingCheckout();
      return null;
    }
  }

  @override
  Future<void> write(PendingCheckout pending) => _store.writePendingCheckout(
    jsonEncode({
      'idempotency_key': pending.idempotencyKey,
      'body_hash': pending.bodyHash,
      'order_public_id': pending.orderPublicId,
    }),
  );

  @override
  Future<void> clear() => _store.deletePendingCheckout();
}
