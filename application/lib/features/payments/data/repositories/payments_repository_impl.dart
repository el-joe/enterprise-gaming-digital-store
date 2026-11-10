import 'package:application/core/network/result.dart';
import 'package:application/features/payments/data/datasources/payments_remote_data_source.dart';
import 'package:application/features/payments/domain/repositories/payments_repository.dart';
import 'package:application/shared/domain/entities/payment_next.dart';

class PaymentsRepositoryImpl implements PaymentsRepository {
  PaymentsRepositoryImpl(this._remote);

  final PaymentsRemoteDataSource _remote;

  @override
  Future<Result<PaymentNext>> retryPayment(
    String orderPublicId, {
    String? gateway,
    String? method,
  }) => guardRequest(
    () async => (await _remote.retryPayment(
      orderPublicId,
      gateway: gateway,
      method: method,
    )).toEntity(),
  );

  @override
  Future<Result<OrderPaymentStatus>> verifyPayment(String orderPublicId) =>
      guardRequest(
        () async => (await _remote.verifyPayment(orderPublicId)).toEntity(),
      );

  @override
  Future<Result<OrderPaymentStatus>> capturePayPal(String orderPublicId) =>
      guardRequest(
        () async => (await _remote.capturePayPal(orderPublicId)).toEntity(),
      );

  @override
  Future<Result<OrderPaymentStatus>> status(String orderPublicId) =>
      guardRequest(
        () async => (await _remote.status(orderPublicId)).toEntity(),
      );
}
