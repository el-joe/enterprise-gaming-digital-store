import 'package:application/core/network/result.dart';
import 'package:application/features/account/domain/entities/account.dart';

abstract interface class AccountRepository {
  Future<Result<Profile>> profile();

  Future<Result<Profile>> updateProfile(ProfileUpdate update);

  /// Signs every other device out; this one stays signed in.
  Future<Result<PasswordChangeResult>> changePassword({
    required String currentPassword,
    required String password,
    required String passwordConfirmation,
  });

  Future<Result<DeviceList>> devices();

  /// 409 `current_device` for this device (use logout instead), 404 for an unknown id.
  Future<Result<void>> revokeDevice(String id);

  Future<Result<void>> resendVerificationEmail();
}
