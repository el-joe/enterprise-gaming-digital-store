// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'api_error.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_ApiError _$ApiErrorFromJson(Map<String, dynamic> json) => _ApiError(
  code: json['code'] as String,
  message: json['message'] as String?,
  errors:
      (json['errors'] as Map<String, dynamic>?)?.map(
        (k, e) =>
            MapEntry(k, (e as List<dynamic>).map((e) => e as String).toList()),
      ) ??
      const <String, List<String>>{},
  requestId: json['request_id'] as String?,
);

Map<String, dynamic> _$ApiErrorToJson(_ApiError instance) => <String, dynamic>{
  'code': instance.code,
  'message': instance.message,
  'errors': instance.errors,
  'request_id': instance.requestId,
};
