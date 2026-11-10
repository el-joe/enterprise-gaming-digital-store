import 'dart:async';

import 'package:application/core/network/api_error.dart';
import 'package:application/core/network/error_mapper.dart';
import 'package:application/core/network/interceptors/idempotency_interceptor.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:dio/dio.dart';

/// Retries transient failures for requests that are safe to repeat:
/// - `GET`, `HEAD` and `OPTIONS`;
/// - any request carrying an `Idempotency-Key` (place-order), re-sent with the same key and body.
///
/// Every other write (code reveal, payments, cart changes) is never retried automatically.
/// `Retry-After` is honoured for 429 and 503 up to [maxRetryAfter]; longer waits are left to the UI.
class RetryInterceptor extends Interceptor {
  RetryInterceptor({
    required this._dio,
    this.maxRetries = 2,
    this.backoff = const [Duration(milliseconds: 500), Duration(seconds: 2)],
    this.maxRetryAfter = const Duration(seconds: 10),
    Future<void> Function(Duration delay)? wait,
  }) : _wait = wait ?? Future<void>.delayed;

  final Dio _dio;
  final int maxRetries;
  final List<Duration> backoff;
  final Duration maxRetryAfter;
  final Future<void> Function(Duration delay) _wait;

  static const _safeMethods = {'GET', 'HEAD', 'OPTIONS'};

  /// Whether [options] may be sent twice without a duplicate side effect.
  static bool isReplayable(RequestOptions options) {
    if (options.extra[RequestExtras.noRetry] == true) {
      return false;
    }
    if (_safeMethods.contains(options.method.toUpperCase())) {
      return true;
    }
    final key = options.headers[IdempotencyInterceptor.header];
    return key is String && key.isNotEmpty;
  }

  @override
  Future<void> onError(
    DioException err,
    ErrorInterceptorHandler handler,
  ) async {
    final options = err.requestOptions;
    final attempt = options.extra[RequestExtras.retryAttempt] is int
        ? options.extra[RequestExtras.retryAttempt] as int
        : 0;
    final delay = _delayFor(err, attempt);
    if (delay == null || attempt >= maxRetries || !isReplayable(options)) {
      handler.next(err);
      return;
    }

    await _wait(delay);
    options.extra[RequestExtras.retryAttempt] = attempt + 1;
    try {
      handler.resolve(await _dio.fetch<dynamic>(options));
    } on DioException catch (retryError) {
      handler.next(retryError);
    }
  }

  /// Delay before the next attempt, or null when [err] is not transient.
  Duration? _delayFor(DioException err, int attempt) {
    final step = backoff.isEmpty
        ? Duration.zero
        : backoff[attempt.clamp(0, backoff.length - 1)];
    switch (err.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
      case DioExceptionType.transformTimeout:
      case DioExceptionType.connectionError:
        return step;
      case DioExceptionType.badResponse:
        final response = err.response;
        final status = response?.statusCode ?? 0;
        if (status == 429 || status == 503) {
          if (ApiError.tryParse(response?.data)?.code == 'maintenance') {
            return null;
          }
          final retryAfter = parseRetryAfter(
            response?.headers.value('retry-after'),
          );
          if (retryAfter == null) {
            return step;
          }
          return retryAfter > maxRetryAfter ? null : retryAfter;
        }
        if (status == 502 || status == 504) {
          return step;
        }
        return null;
      case DioExceptionType.badCertificate:
      case DioExceptionType.cancel:
      case DioExceptionType.unknown:
        return null;
    }
  }
}
