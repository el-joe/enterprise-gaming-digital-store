import 'package:freezed_annotation/freezed_annotation.dart';

part 'api_error.freezed.dart';
part 'api_error.g.dart';

/// Standard error envelope (API_DOCUMENTATION.md section 1):
/// `{ success: false, message, code, errors, request_id }`.
@freezed
abstract class ApiError with _$ApiError {
  const factory ApiError({
    required String code,
    String? message,
    @Default(<String, List<String>>{}) Map<String, List<String>> errors,
    @JsonKey(name: 'request_id') String? requestId,
  }) = _ApiError;

  factory ApiError.fromJson(Map<String, dynamic> json) =>
      _$ApiErrorFromJson(json);

  /// Parses [body] when it is an error envelope; returns null for anything else
  /// (HTML error pages, empty bodies, success envelopes).
  static ApiError? tryParse(Object? body) {
    if (body is! Map<String, dynamic>) {
      return null;
    }
    if (body['success'] != false || body['code'] is! String) {
      return null;
    }
    try {
      return ApiError.fromJson(body);
    } on Object {
      return ApiError(
        code: body['code']! as String,
        message: body['message'] is String ? body['message'] as String : null,
        requestId: body['request_id'] is String
            ? body['request_id'] as String
            : null,
      );
    }
  }
}
