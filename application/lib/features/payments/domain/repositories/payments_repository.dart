import 'package:application/core/network/result.dart';
import 'package:application/shared/domain/entities/payment_next.dart';

/// Payment calls of an order (API_DOCUMENTATION.md 3b, 1e). None of them can mark an order paid
/// by itself; the server decides after a provider read or a verified webhook.
abstract interface class PaymentsRepository {
  /// A new attempt (201 `{ next }`), always with `client: app`. 409 `order_not_payable`,
  /// `order_expired`, `payment_attempts_exhausted`; 422 `gateway_change_unsupported`.
  Future<Result<PaymentNext>> retryPayment(
    String orderPublicId, {
    String? gateway,
    String? method,
  });

  /// Server-side read of the latest attempt, after any provider UI closed.
  Future<Result<OrderPaymentStatus>> verifyPayment(String orderPublicId);

  /// Server capture of an approved PayPal order; paid only on capture `COMPLETED`.
  Future<Result<OrderPaymentStatus>> capturePayPal(String orderPublicId);

  Future<Result<OrderPaymentStatus>> status(String orderPublicId);
}
