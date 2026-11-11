import 'package:application/core/network/dio_client.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/auth/data/biometric/plugin_biometric_key_store.dart';
import 'package:application/features/auth/data/datasources/auth_remote_data_source.dart';
import 'package:application/features/auth/data/datasources/biometric_remote_data_source.dart';
import 'package:application/features/auth/data/repositories/auth_repository_impl.dart';
import 'package:application/features/auth/data/repositories/biometric_auth_repository_impl.dart';
import 'package:application/features/auth/domain/repositories/auth_repository.dart';
import 'package:application/features/auth/domain/repositories/biometric_repositories.dart';
import 'package:application/features/auth/domain/usecases/biometric_unlock.dart';
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

// Biometric unlock and re-auth (owner decision 6, ADR-040).

@Riverpod(keepAlive: true)
BiometricKeyStore biometricKeyStore(Ref ref) => PluginBiometricKeyStore();

@Riverpod(keepAlive: true)
BiometricAuthRepository biometricAuthRepository(Ref ref) =>
    BiometricAuthRepositoryImpl(
      BiometricRemoteDataSource(ref.watch(apiClientProvider)),
    );

@Riverpod(keepAlive: true)
BiometricEnrolmentStore biometricEnrolmentStore(Ref ref) =>
    SecureBiometricEnrolmentStore(ref.watch(secureStoreProvider));

@Riverpod(keepAlive: true)
EnrolBiometricUnlock enrolBiometricUnlock(Ref ref) => EnrolBiometricUnlock(
  keys: ref.watch(biometricKeyStoreProvider),
  server: ref.watch(biometricAuthRepositoryProvider),
  enrolment: ref.watch(biometricEnrolmentStoreProvider),
);

@Riverpod(keepAlive: true)
DisableBiometricUnlock disableBiometricUnlock(Ref ref) =>
    DisableBiometricUnlock(
      keys: ref.watch(biometricKeyStoreProvider),
      server: ref.watch(biometricAuthRepositoryProvider),
      enrolment: ref.watch(biometricEnrolmentStoreProvider),
    );

@Riverpod(keepAlive: true)
UnlockWithBiometric unlockWithBiometric(Ref ref) => UnlockWithBiometric(
  keys: ref.watch(biometricKeyStoreProvider),
  enrolment: ref.watch(biometricEnrolmentStoreProvider),
  disable: ref.watch(disableBiometricUnlockProvider),
);

/// The biometric path of the recent-auth confirmation, for the code reveal re-auth (M-A14).
@Riverpod(keepAlive: true)
ConfirmRecentAuthWithBiometric confirmRecentAuthWithBiometric(Ref ref) =>
    ConfirmRecentAuthWithBiometric(
      keys: ref.watch(biometricKeyStoreProvider),
      server: ref.watch(biometricAuthRepositoryProvider),
      enrolment: ref.watch(biometricEnrolmentStoreProvider),
      disable: ref.watch(disableBiometricUnlockProvider),
    );
