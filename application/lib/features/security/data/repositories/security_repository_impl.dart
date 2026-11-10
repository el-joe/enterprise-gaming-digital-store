import 'package:application/core/network/result.dart';
import 'package:application/features/security/data/datasources/security_remote_data_source.dart';
import 'package:application/features/security/domain/entities/two_factor.dart';
import 'package:application/features/security/domain/repositories/security_repository.dart';

class SecurityRepositoryImpl implements SecurityRepository {
  SecurityRepositoryImpl(this._remote);

  final SecurityRemoteDataSource _remote;

  @override
  Future<Result<TwoFactorEnrollment>> enableTwoFactor({
    required String currentPassword,
  }) => guardRequest(() => _remote.enable(currentPassword));

  @override
  Future<Result<RecoveryCodes>> confirmTwoFactor({required String code}) =>
      guardRequest(() => _remote.confirm(code));

  @override
  Future<Result<void>> disableTwoFactor({required String currentPassword}) =>
      guardRequest(() => _remote.disable(currentPassword));
}
