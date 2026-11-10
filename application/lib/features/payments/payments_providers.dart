import 'package:application/core/network/dio_client.dart';
import 'package:application/features/payments/data/datasources/payments_remote_data_source.dart';
import 'package:application/features/payments/data/repositories/payments_repository_impl.dart';
import 'package:application/features/payments/domain/repositories/payments_repository.dart';
import 'package:application/features/payments/domain/usecases/resolve_payment.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'payments_providers.g.dart';

@Riverpod(keepAlive: true)
PaymentsRemoteDataSource paymentsRemoteDataSource(Ref ref) =>
    PaymentsRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
PaymentsRepository paymentsRepository(Ref ref) =>
    PaymentsRepositoryImpl(ref.watch(paymentsRemoteDataSourceProvider));

@riverpod
ResolvePayment resolvePayment(Ref ref) =>
    ResolvePayment(ref.watch(paymentsRepositoryProvider));
