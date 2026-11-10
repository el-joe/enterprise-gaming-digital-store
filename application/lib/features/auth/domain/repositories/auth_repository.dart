import 'package:application/core/network/result.dart';
import 'package:application/features/auth/domain/entities/auth_entities.dart';

/// Token lifecycle of the mobile auth API (API_DOCUMENTATION.md 1b). Tokens are written to and
/// removed from secure storage by the implementation; callers never see them.
abstract interface class AuthRepository {
  Future<Result<AuthUser>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String deviceName,
  });

  Future<Result<LoginOutcome>> login({
    required String email,
    required String password,
    required String deviceName,
  });

  /// Completes a sign-in with a TOTP [code] or a [recoveryCode] (exactly one).
  Future<Result<AuthUser>> completeTwoFactor({
    required String challengeToken,
    String? code,
    String? recoveryCode,
  });

  /// Exchanges the stored refresh token. A refused token (expired, revoked or reused) clears the
  /// session and returns `UnauthenticatedFailure`.
  Future<Result<void>> refresh();

  Future<Result<AuthUser>> me();

  /// Revokes this device's sign-in, then wipes secure storage (also when the call fails).
  Future<Result<void>> logout();

  /// Revokes every sign-in of the account, then wipes secure storage.
  Future<Result<void>> logoutAll();

  Future<bool> hasSession();
}
