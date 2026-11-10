import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/auth/domain/entities/auth_entities.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'auth_models.freezed.dart';
part 'auth_models.g.dart';

/// `MobileTokenResource`. Never logged; stored only in secure storage.
@freezed
abstract class TokensDto with _$TokensDto {
  const factory TokensDto({
    required String tokenType,
    required String accessToken,
    required DateTime accessTokenExpiresAt,
    required String refreshToken,
    required DateTime refreshTokenExpiresAt,
  }) = _TokensDto;

  const TokensDto._();

  factory TokensDto.fromJson(Map<String, dynamic> json) =>
      _$TokensDtoFromJson(json);

  AuthTokens toAuthTokens() =>
      AuthTokens(accessToken: accessToken, refreshToken: refreshToken);

  @override
  String toString() => 'TokensDto(<redacted>)';
}

@freezed
abstract class UserDto with _$UserDto {
  const factory UserDto({
    required String publicId,
    required String name,
    required String email,
    required String type,
    required bool emailVerified,
    required bool twoFactorEnabled,
  }) = _UserDto;

  const UserDto._();

  factory UserDto.fromJson(Map<String, dynamic> json) =>
      _$UserDtoFromJson(json);

  AuthUser toEntity() => AuthUser(
    publicId: publicId,
    name: name,
    email: email,
    type: type,
    emailVerified: emailVerified,
    twoFactorEnabled: twoFactorEnabled,
  );
}

@freezed
abstract class ChallengeDto with _$ChallengeDto {
  const factory ChallengeDto({
    required String token,
    required DateTime expiresAt,
  }) = _ChallengeDto;

  factory ChallengeDto.fromJson(Map<String, dynamic> json) =>
      _$ChallengeDtoFromJson(json);
}

/// `{ tokens, user }` of register and the two-factor challenge.
@freezed
abstract class AuthSessionDto with _$AuthSessionDto {
  const factory AuthSessionDto({
    required TokensDto tokens,
    required UserDto user,
  }) = _AuthSessionDto;

  factory AuthSessionDto.fromJson(Map<String, dynamic> json) =>
      _$AuthSessionDtoFromJson(json);
}

/// `{ two_factor_required, challenge, tokens, user }` of login.
@freezed
abstract class LoginResponseDto with _$LoginResponseDto {
  const factory LoginResponseDto({
    required bool twoFactorRequired,
    ChallengeDto? challenge,
    TokensDto? tokens,
    UserDto? user,
  }) = _LoginResponseDto;

  factory LoginResponseDto.fromJson(Map<String, dynamic> json) =>
      _$LoginResponseDtoFromJson(json);
}
