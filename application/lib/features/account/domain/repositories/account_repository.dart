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

  /// Signs every other app sign-in and browser session out; this one stays signed in and the
  /// password does not change. 422 `errors.current_password` for a wrong password.
  Future<Result<PasswordChangeResult>> signOutOtherDevices({
    required String currentPassword,
  });

  Future<Result<void>> resendVerificationEmail();
}
