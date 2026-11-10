import 'package:application/core/money/money.dart';
import 'package:application/shared/domain/entities/payment_next.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'checkout.freezed.dart';

/// An eligible gateway of `GET /checkout/payment-options` (API_DOCUMENTATION.md 3b).
@freezed
abstract class PaymentOption with _$PaymentOption {
  const factory PaymentOption({
    required String gateway,
    required String label,
    required List<PaymentMethodOption> methods,
    required bool available,
    required String paymentCurrency,
    required Money paymentAmount,
    required String displayCurrency,
    required Money displayAmount,

    /// null, `charged_in_payment_currency` (show it) or `payment_currency_below_margin`.
    String? notice,
  }) = _PaymentOption;
}

@freezed
abstract class PaymentMethodOption with _$PaymentMethodOption {
  const factory PaymentMethodOption({
    required String code,
    required String label,
  }) = _PaymentMethodOption;
}

/// The place-order body. [currency] and [expectedTotal] are exactly the strings the customer saw
/// (`Money.currency` and `Money.amountText` of the last validate or payment-options response).
@freezed
abstract class PlaceOrderRequest with _$PlaceOrderRequest {
  const factory PlaceOrderRequest({
    required String country,
    required String gateway,
    required String currency,
    required String expectedTotal,
    required bool acceptTerms,

    /// Guests only; customers use their account email.
    String? email,
    String? method,
    bool? marketingConsent,
    String? gaClientId,
  }) = _PlaceOrderRequest;

  const PlaceOrderRequest._();

  /// The JSON body; `client` is always `app` (API_DOCUMENTATION.md 1e).
  Map<String, Object?> toBody() => {
    'country': country,
    'gateway': gateway,
    'currency': currency,
    'expected_total': expectedTotal,
    'consents': {'terms': acceptTerms, 'marketing': ?marketingConsent},
    'email': ?email,
    'method': ?method,
    'ga_client_id': ?gaClientId,
    'client': 'app',
  };
}

/// The order part of a place-order response. A duplicate (`meta.duplicate: true`) carries only
/// [publicId] and [status].
@freezed
abstract class PlacedOrder with _$PlacedOrder {
  const factory PlacedOrder({
    required String publicId,
    required String status,
    String? orderNumber,
    Money? displayTotal,
    Money? paymentTotal,
    DateTime? expiresAt,
  }) = _PlacedOrder;
}

@freezed
abstract class PlaceOrderResponse with _$PlaceOrderResponse {
  const factory PlaceOrderResponse({
    required PlacedOrder order,

    /// The same buyer already has an open order with the same contents: go to its status.
    required bool duplicate,

    /// The server replayed a stored response (`Idempotency-Replayed: true`).
    required bool replayed,

    /// Null when no attempt was started (replay, or the provider was down): call retry-payment.
    PaymentNext? next,
  }) = _PlaceOrderResponse;
}

/// Result of opening the signed delivery link in the app.
@freezed
abstract class GuestLinkAccess with _$GuestLinkAccess {
  const factory GuestLinkAccess({
    required String orderPublicId,
    required bool otpRequired,
  }) = _GuestLinkAccess;
}

/// A checkout in flight, kept in secure storage so a restart re-sends the same
/// `Idempotency-Key` with the same body, or resumes at the order's status.
@freezed
abstract class PendingCheckout with _$PendingCheckout {
  const factory PendingCheckout({
    required String idempotencyKey,
    required String bodyHash,
    String? orderPublicId,
  }) = _PendingCheckout;
}
