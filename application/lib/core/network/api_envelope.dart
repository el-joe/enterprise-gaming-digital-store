import 'package:freezed_annotation/freezed_annotation.dart';

part 'api_envelope.freezed.dart';
part 'api_envelope.g.dart';

/// Pagination from the success envelope's `meta` (`current_page`, `per_page`, `total`, `last_page`).
@freezed
abstract class PageMeta with _$PageMeta {
  const factory PageMeta({
    @JsonKey(name: 'current_page') required int currentPage,
    @JsonKey(name: 'per_page') required int perPage,
    required int total,
    @JsonKey(name: 'last_page') required int lastPage,
  }) = _PageMeta;

  const PageMeta._();

  factory PageMeta.fromJson(Map<String, dynamic> json) =>
      _$PageMetaFromJson(json);

  /// Returns null when [meta] carries no pagination keys.
  static PageMeta? tryParse(Map<String, dynamic> meta) {
    const keys = ['current_page', 'per_page', 'total', 'last_page'];
    if (!keys.every((key) => meta[key] is int)) {
      return null;
    }
    return PageMeta.fromJson(meta);
  }

  bool get hasNextPage => currentPage < lastPage;
}

/// Standard success envelope: `{ success: true, message, data, meta }`.
///
/// Throws [FormatException] when the body is not a success envelope, so the error mapper turns
/// it into a `DecodingFailure`.
final class ApiEnvelope<T> {
  const ApiEnvelope({required this.data, this.message, this.meta = const {}});

  factory ApiEnvelope.fromJson(
    Object? json,
    T Function(Object? data) decodeData,
  ) {
    if (json is! Map<String, dynamic>) {
      throw const FormatException('Response body is not a JSON object.');
    }
    if (json['success'] != true) {
      throw const FormatException('Response is not a success envelope.');
    }
    final message = json['message'];
    if (message != null && message is! String) {
      throw const FormatException('Envelope "message" must be a string.');
    }
    final meta = json['meta'];
    final Map<String, dynamic> metaMap;
    if (meta == null || (meta is List && meta.isEmpty)) {
      // Laravel serialises an empty associative array as `[]`.
      metaMap = const {};
    } else if (meta is Map<String, dynamic>) {
      metaMap = meta;
    } else {
      throw const FormatException('Envelope "meta" must be an object.');
    }
    return ApiEnvelope<T>(
      data: decodeData(json['data']),
      message: message as String?,
      meta: metaMap,
    );
  }

  final T data;
  final String? message;
  final Map<String, dynamic> meta;

  /// Pagination, when [meta] carries it.
  PageMeta? get page => PageMeta.tryParse(meta);
}
