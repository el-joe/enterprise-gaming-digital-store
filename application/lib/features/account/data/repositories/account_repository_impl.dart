import 'package:application/core/network/result.dart';
import 'package:application/features/account/data/datasources/account_remote_data_source.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/account/domain/repositories/account_repository.dart';

class AccountRepositoryImpl implements AccountRepository {
  AccountRepositoryImpl(this._remote);

  final AccountRemoteDataSource _remote;

  @override
  Future<Result<Profile>> profile() =>
      guardRequest(() async => (await _remote.profile()).toEntity());

  @override
  Future<Result<Profile>> updateProfile(ProfileUpdate update) => guardRequest(
    () async => (await _remote.updateProfile(update.toBody())).toEntity(),
  );

  @override
  Future<Result<PasswordChangeResult>> changePassword({
    required String currentPassword,
    required String password,
    required String passwordConfirmation,
  }) => guardRequest(() async {
    final signedOut = await _remote.changePassword({
      'current_password': currentPassword,
      'password': password,
      'password_confirmation': passwordConfirmation,
    });
    return PasswordChangeResult(
      mobileFamilies: signedOut.mobileFamilies,
      webSessions: signedOut.webSessions,
    );
  });

  @override
  Future<Result<DeviceList>> devices() => guardRequest(() async {
    final envelope = await _remote.devices();
    return DeviceList(
      devices: [for (final dto in envelope.data) dto.toEntity()],
      webSessionsListed: envelope.meta['web_sessions_listed'] == true,
    );
  });

  @override
  Future<Result<void>> revokeDevice(String id) =>
      guardRequest(() => _remote.revokeDevice(id));

  @override
  Future<Result<void>> resendVerificationEmail() =>
      guardRequest(_remote.resendVerificationEmail);
}
