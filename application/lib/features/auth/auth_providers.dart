import 'package:application/core/network/dio_client.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/auth/data/datasources/auth_remote_data_source.dart';
import 'package:application/features/auth/data/repositories/auth_repository_impl.dart';
import 'package:application/features/auth/domain/repositories/auth_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'auth_providers.g.dart';

@Riverpod(keepAlive: true)
AuthRemoteDataSource authRemoteDataSource(Ref ref) =>
    AuthRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
AuthRepository authRepository(Ref ref) => AuthRepositoryImpl(
  remote: ref.watch(authRemoteDataSourceProvider),
  store: ref.watch(secureStoreProvider),
  refresher: ref.watch(tokenRefresherProvider),
  onSignedIn: () => ref.read(sessionProvider.notifier).markSignedIn(),
  onSignedOut: () => ref.read(sessionProvider.notifier).markSignedOut(),
);
