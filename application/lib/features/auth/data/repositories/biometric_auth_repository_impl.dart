import 'package:application/core/network/result.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/auth/data/datasources/biometric_remote_data_source.dart';
import 'package:application/features/auth/domain/repositories/biometric_repositories.dart';

class BiometricAuthRepositoryImpl implements BiometricAuthRepository {
  BiometricAuthRepositoryImpl(this._remote);

  final BiometricRemoteDataSource _remote;

  @override
  Future<Result<void>> registerKey(String publicKey) =>
      guardRequest(() => _remote.registerKey(publicKey));

  @override
  Future<Result<void>> deleteKey() => guardRequest(_remote.deleteKey);

  @override
  Future<Result<String>> challenge() => guardRequest(_remote.challenge);

  @override
  Future<Result<void>> confirm({
    required String challenge,
    required String signature,
  }) => guardRequest(
    () => _remote.confirm(challenge: challenge, signature: signature),
  );

  @override
  Future<Result<void>> confirmPassword(String password) =>
      guardRequest(() => _remote.confirmPassword(password));
}

/// The enabled flag in secure storage (wiped with the tokens on sign-out).
class SecureBiometricEnrolmentStore implements BiometricEnrolmentStore {
  SecureBiometricEnrolmentStore(this._store);

  final SecureStore _store;

  @override
  Future<bool> isEnabled() => _store.readBiometricEnabled();

  @override
  Future<void> setEnabled({required bool enabled}) =>
      _store.writeBiometricEnabled(enabled: enabled);
}
