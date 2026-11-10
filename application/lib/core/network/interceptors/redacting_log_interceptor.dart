import 'dart:convert';

import 'package:application/core/network/request_extras.dart';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

/// Masks sensitive values before anything reaches a log (MOBILE_APP_ROADMAP.md 6.5).
///
/// The key rule mirrors the backend's `SensitiveDataRedactor` and adds the client-side keys
/// `refresh_token`, `idempotency_key` and `signature` (signed links).
abstract final class Redactor {
  static const replacement = '[REDACTED]';

  static final _sensitiveKey = RegExp(
    r'(password|passwd|token|secret|api_?key|app_?id|authorization|cookie|ciphertext)'
    r'|^card|^pin(_|$)|^redemption|^code$|^cvv|^otp'
    r'|(^|_)(digital|gift|recovery|voucher|activation)_codes?$'
    r'|^idempotency_key$|^signature$',
  );

  /// Paths whose bodies are never logged: reveal, auth and payment traffic.
  static final _bodylessPath = RegExp(
    r'/(reveal|login|logout|register|refresh|two-factor[^/]*|confirm-password'
    r'|forgot-password|reset-password|place-order|retry-payment|verify-payment'
    r'|capture|otp)(/|$)'
    r'|/mobile/auth/|/security/',
  );

  /// Normalises a JSON key or header name like the backend (snake case, `-` and spaces to `_`).
  static String normaliseKey(String key) {
    final snake = key
        .replaceAll(RegExp('[- ]'), '_')
        .replaceAllMapped(RegExp('([a-z0-9])([A-Z])'), (m) => '${m[1]}_${m[2]}')
        .toLowerCase();
    return snake;
  }

  static bool isSensitiveKey(String key) =>
      _sensitiveKey.hasMatch(normaliseKey(key));

  static bool dropsBody(RequestOptions options) =>
      options.extra[RequestExtras.sensitive] == true ||
      _bodylessPath.hasMatch(options.uri.path);

  /// Returns a copy of [value] with every sensitive key's value replaced.
  static Object? redact(Object? value) {
    if (value is Map) {
      return {
        for (final entry in value.entries)
          entry.key: entry.key is String && isSensitiveKey(entry.key as String)
              ? replacement
              : redact(entry.value),
      };
    }
    if (value is List) {
      return [for (final item in value) redact(item)];
    }
    return value;
  }

  static Map<String, Object?> redactHeaders(Map<String, Object?> headers) => {
    for (final entry in headers.entries)
      entry.key: isSensitiveKey(entry.key) ? replacement : entry.value,
  };

  static Uri redactUri(Uri uri) {
    if (uri.queryParameters.isEmpty) {
      return uri;
    }
    return uri.replace(
      queryParameters: {
        for (final entry in uri.queryParameters.entries)
          entry.key: isSensitiveKey(entry.key) ? replacement : entry.value,
      },
    );
  }
}

/// Debug-only request log. It is added to the client only when `kDebugMode` is true, and
/// every line goes through [Redactor].
class RedactingLogInterceptor extends Interceptor {
  RedactingLogInterceptor({void Function(String line)? log})
    : _log = log ?? debugPrint;

  final void Function(String line) _log;

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    _log(
      '--> ${options.method} ${Redactor.redactUri(options.uri)} '
      'headers=${jsonEncode(Redactor.redactHeaders(options.headers))} '
      'body=${_body(options, options.data)}',
    );
    handler.next(options);
  }

  @override
  void onResponse(
    Response<dynamic> response,
    ResponseInterceptorHandler handler,
  ) {
    final options = response.requestOptions;
    _log(
      '<-- ${response.statusCode} ${options.method} ${Redactor.redactUri(options.uri)} '
      'body=${_body(options, response.data)}',
    );
    handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    final options = err.requestOptions;
    _log(
      '<-- ${err.response?.statusCode ?? err.type.name} ${options.method} '
      '${Redactor.redactUri(options.uri)} '
      'body=${_body(options, err.response?.data)}',
    );
    handler.next(err);
  }

  String _body(RequestOptions options, Object? data) {
    if (data == null) {
      return '-';
    }
    if (Redactor.dropsBody(options)) {
      return '[omitted]';
    }
    if (data is Map || data is List) {
      return jsonEncode(Redactor.redact(data));
    }
    return '[non-JSON body]';
  }
}
