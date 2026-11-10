import 'package:freezed_annotation/freezed_annotation.dart';

part 'auth_entities.freezed.dart';

/// The account `UserResource` (API_DOCUMENTATION.md 1b).
@freezed
abstract class AuthUser with _$AuthUser {
  const factory AuthUser({
    required String publicId,
    required String name,
    required String email,
    required String type,
    required bool emailVerified,
    required bool twoFactorEnabled,
  }) = _AuthUser;
}

/// Result of a password sign-in: signed in, or a second factor is required. The challenge token
/// is held in memory only (it lives 300 s and is single use).
@freezed
sealed class LoginOutcome with _$LoginOutcome {
  const factory LoginOutcome.signedIn(AuthUser user) = LoginSignedIn;

  const factory LoginOutcome.twoFactorRequired({
    required String challengeToken,
    required DateTime expiresAt,
  }) = LoginTwoFactorRequired;
}
