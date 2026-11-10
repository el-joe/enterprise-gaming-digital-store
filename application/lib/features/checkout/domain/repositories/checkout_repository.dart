import 'package:application/core/network/result.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/shared/domain/entities/cart.dart';
import 'package:application/shared/domain/entities/payment_next.dart';

/// Checkout under `/api/v1/mobile/checkout` (API_DOCUMENTATION.md 1c, 3a, 3b). Guest order tokens
/// returned by the server are written to secure storage per order by the implementation.
abstract interface class CheckoutRepository {
  /// Reprices the cart with checkout rules for [country].
  Future<Result<Cart>> validate({required String country, String? email});

  Future<Result<List<PaymentOption>>> paymentOptions({
    required String country,
    String? email,
  });

  /// Sends place-order with [idempotencyKey]. Use the `PlaceOrder` use case, which owns the key.
  Future<Result<PlaceOrderResponse>> placeOrder(
    PlaceOrderRequest request, {
    required String idempotencyKey,
  });

  Future<Result<OrderPaymentStatus>> status(String orderPublicId);

  /// Always succeeds with the same message whether or not the email matches.
  Future<Result<void>> sendGuestOtp({
    required String orderPublicId,
    required String email,
  });

  /// Verifies the emailed code; the new guest order token replaces the stored one.
  Future<Result<void>> verifyGuestOtp({
    required String orderPublicId,
    required String code,
  });

  /// Opens the signed delivery link (its `/api/v1/mobile` twin) with the stored token, if any.
  Future<Result<GuestLinkAccess>> openGuestLink(Uri signedLink);
}

/// Persistence of the pending checkout (secure storage in the app).
abstract interface class PendingCheckoutStore {
  Future<PendingCheckout?> read();

  Future<void> write(PendingCheckout pending);

  Future<void> clear();
}
