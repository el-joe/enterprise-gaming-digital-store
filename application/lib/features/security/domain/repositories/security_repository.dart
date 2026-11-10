import 'package:application/core/network/result.dart';
import 'package:application/features/security/domain/entities/two_factor.dart';

/// Two-factor enrolment (API_DOCUMENTATION.md 4, 1c). Every change takes the current password.
abstract interface class SecurityRepository {
  /// 409 `two_factor_already_enabled`, 422 for a wrong password.
  Future<Result<TwoFactorEnrollment>> enableTwoFactor({
    required String currentPassword,
  });

  /// 422 `two_factor_invalid` for a wrong code.
  Future<Result<RecoveryCodes>> confirmTwoFactor({required String code});

  Future<Result<void>> disableTwoFactor({required String currentPassword});
}
