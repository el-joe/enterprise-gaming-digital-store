import 'package:freezed_annotation/freezed_annotation.dart';

part 'push_models.freezed.dart';
part 'push_models.g.dart';

/// `data.push_token` of `POST /mobile/push-token` (API_DOCUMENTATION.md 1h). The token itself is
/// never echoed.
@freezed
abstract class PushTokenDto with _$PushTokenDto {
  const factory PushTokenDto({
    /// `android` or `ios`.
    required String platform,
    required DateTime registeredAt,
  }) = _PushTokenDto;

  factory PushTokenDto.fromJson(Map<String, dynamic> json) =>
      _$PushTokenDtoFromJson(json);
}
