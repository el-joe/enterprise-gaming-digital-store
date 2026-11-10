// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'auth_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_TokensDto _$TokensDtoFromJson(Map<String, dynamic> json) => _TokensDto(
  tokenType: json['token_type'] as String,
  accessToken: json['access_token'] as String,
  accessTokenExpiresAt: DateTime.parse(
    json['access_token_expires_at'] as String,
  ),
  refreshToken: json['refresh_token'] as String,
  refreshTokenExpiresAt: DateTime.parse(
    json['refresh_token_expires_at'] as String,
  ),
);

Map<String, dynamic> _$TokensDtoToJson(
  _TokensDto instance,
) => <String, dynamic>{
  'token_type': instance.tokenType,
  'access_token': instance.accessToken,
  'access_token_expires_at': instance.accessTokenExpiresAt.toIso8601String(),
  'refresh_token': instance.refreshToken,
  'refresh_token_expires_at': instance.refreshTokenExpiresAt.toIso8601String(),
};

_UserDto _$UserDtoFromJson(Map<String, dynamic> json) => _UserDto(
  publicId: json['public_id'] as String,
  name: json['name'] as String,
  email: json['email'] as String,
  type: json['type'] as String,
  emailVerified: json['email_verified'] as bool,
  twoFactorEnabled: json['two_factor_enabled'] as bool,
);

Map<String, dynamic> _$UserDtoToJson(_UserDto instance) => <String, dynamic>{
  'public_id': instance.publicId,
  'name': instance.name,
  'email': instance.email,
  'type': instance.type,
  'email_verified': instance.emailVerified,
  'two_factor_enabled': instance.twoFactorEnabled,
};

_ChallengeDto _$ChallengeDtoFromJson(Map<String, dynamic> json) =>
    _ChallengeDto(
      token: json['token'] as String,
      expiresAt: DateTime.parse(json['expires_at'] as String),
    );

Map<String, dynamic> _$ChallengeDtoToJson(_ChallengeDto instance) =>
    <String, dynamic>{
      'token': instance.token,
      'expires_at': instance.expiresAt.toIso8601String(),
    };

_AuthSessionDto _$AuthSessionDtoFromJson(Map<String, dynamic> json) =>
    _AuthSessionDto(
      tokens: TokensDto.fromJson(json['tokens'] as Map<String, dynamic>),
      user: UserDto.fromJson(json['user'] as Map<String, dynamic>),
    );

Map<String, dynamic> _$AuthSessionDtoToJson(_AuthSessionDto instance) =>
    <String, dynamic>{
      'tokens': instance.tokens.toJson(),
      'user': instance.user.toJson(),
    };

_LoginResponseDto _$LoginResponseDtoFromJson(Map<String, dynamic> json) =>
    _LoginResponseDto(
      twoFactorRequired: json['two_factor_required'] as bool,
      challenge: json['challenge'] == null
          ? null
          : ChallengeDto.fromJson(json['challenge'] as Map<String, dynamic>),
      tokens: json['tokens'] == null
          ? null
          : TokensDto.fromJson(json['tokens'] as Map<String, dynamic>),
      user: json['user'] == null
          ? null
          : UserDto.fromJson(json['user'] as Map<String, dynamic>),
    );

Map<String, dynamic> _$LoginResponseDtoToJson(_LoginResponseDto instance) =>
    <String, dynamic>{
      'two_factor_required': instance.twoFactorRequired,
      'challenge': instance.challenge?.toJson(),
      'tokens': instance.tokens?.toJson(),
      'user': instance.user?.toJson(),
    };
