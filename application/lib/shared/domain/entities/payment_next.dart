import 'package:freezed_annotation/freezed_annotation.dart';

part 'payment_next.freezed.dart';

/// The provider step after place-order or retry-payment (API_DOCUMENTATION.md 3b, 1e). Publishable
/// values only. Launching the provider UI is M-A13; this layer only models it.
@freezed
sealed class PaymentNext with _$PaymentNext {
  /// Stripe PaymentSheet: [clientSecret] and, for app attempts, [returnUrl] for `returnURL`.
  const factory PaymentNext.stripe({
    required String paymentPublicId,
    required String gateway,
    required String clientSecret,
    Uri? returnUrl,
  }) = StripeNext;

  /// PayPal: open [approveUrl]; on return call `paypal/capture`.
  const factory PaymentNext.paypal({
    required String paymentPublicId,
    required String gateway,
    required String providerOrderId,
    required Uri approveUrl,
  }) = PayPalNext;

  /// Hosted redirect (Paymob Unified Checkout): open [redirectUrl]; on return call verify-payment.
  const factory PaymentNext.redirect({
    required String paymentPublicId,
    required String gateway,
    required Uri redirectUrl,
  }) = RedirectNext;

  /// Paid from the wallet balance at placement: no provider UI, go to status polling.
  const factory PaymentNext.wallet({
    required String paymentPublicId,
    required String gateway,
  }) = WalletNext;

  /// A `type` this app version does not know; the UI offers status and support instead.
  const factory PaymentNext.unknown({
    required String type,
    required String gateway,
  }) = UnknownNext;
}

/// `{ public_id, status, payment_status, is_final }` of status, verify-payment and capture.
@freezed
abstract class OrderPaymentStatus with _$OrderPaymentStatus {
  const factory OrderPaymentStatus({
    required String publicId,
    required String status,

    /// `unpaid`, `processing`, `paid`, `partially_refunded` or `refunded`.
    required String paymentStatus,
    required bool isFinal,
  }) = _OrderPaymentStatus;

  const OrderPaymentStatus._();

  /// Only the server can say an order is paid.
  bool get isPaid => paymentStatus == 'paid';
}

/// A per-order guest token (`guest_order_token`), stored in secure storage keyed by order.
@freezed
abstract class GuestOrderToken with _$GuestOrderToken {
  const factory GuestOrderToken({
    required String token,
    required String orderPublicId,
    required DateTime expiresAt,
  }) = _GuestOrderToken;

  const GuestOrderToken._();

  @override
  String toString() => 'GuestOrderToken($orderPublicId, <redacted>)';
}
