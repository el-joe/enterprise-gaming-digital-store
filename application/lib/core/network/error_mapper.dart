import 'dart:io';

import 'package:application/core/network/api_error.dart';
import 'package:application/core/network/failure.dart';
import 'package:dio/dio.dart';

/// Maps any error thrown while calling the API to a [Failure] (MOBILE_APP_ROADMAP.md 2.3).
Failure mapError(Object error) {
  return switch (error) {
    final DioException e => mapDioException(e),
    final FormatException e => DecodingFailure(e.message),
    final Failure failure => failure,
    _ => const UnexpectedFailure(),
  };
}

Failure mapDioException(DioException error) {
  switch (error.type) {
    case DioExceptionType.connectionTimeout:
    case DioExceptionType.sendTimeout:
    case DioExceptionType.receiveTimeout:
    case DioExceptionType.transformTimeout:
      return const TimeoutFailure();
    case DioExceptionType.connectionError:
    case DioExceptionType.badCertificate:
      return const NetworkFailure();
    case DioExceptionType.cancel:
      return const CancelledFailure();
    case DioExceptionType.badResponse:
      final response = error.response;
      if (response == null) {
        return const UnexpectedFailure();
      }
      return mapErrorResponse(
        statusCode: response.statusCode ?? 0,
        body: response.data,
        headers: response.headers,
      );
    case DioExceptionType.unknown:
      final cause = error.error;
      if (cause is SocketException ||
          cause is HandshakeException ||
          cause is TlsException ||
          cause is HttpException) {
        return const NetworkFailure();
      }
      if (cause is FormatException) {
        return DecodingFailure(cause.message);
      }
      return const UnexpectedFailure();
  }
}

/// Maps an HTTP error response (status and error envelope) to a [Failure].
Failure mapErrorResponse({
  required int statusCode,
  required Object? body,
  required Headers headers,
  DateTime? now,
}) {
  final apiError = ApiError.tryParse(body);
  final code = apiError?.code;
  final requestId = apiError?.requestId ?? headers.value('x-request-id');

  switch (statusCode) {
    case 401:
      return UnauthenticatedFailure(requestId: requestId);
    case 403:
      return switch (code) {
        'two_factor_required' => TwoFactorRequiredFailure(requestId: requestId),
        'email_unverified' => EmailUnverifiedFailure(requestId: requestId),
        _ => ForbiddenFailure(code ?? 'forbidden', requestId: requestId),
      };
    case 404:
      return NotFoundFailure(requestId: requestId);
    case 409:
      return ConflictFailure(
        code ?? 'conflict',
        message: apiError?.message,
        requestId: requestId,
      );
    case 422:
      if (code == 'validation_failed') {
        return ValidationFailure(
          apiError?.errors ?? const {},
          message: apiError?.message,
          requestId: requestId,
        );
      }
      return DomainFailure(
        code ?? 'unprocessable',
        message: apiError?.message,
        errors: apiError?.errors ?? const {},
        requestId: requestId,
      );
    case 423 when code == 'biometric_locked':
      return BiometricLockedFailure(requestId: requestId);
    case 423:
      return RecentAuthRequiredFailure(requestId: requestId);
    case 429:
      return RateLimitedFailure(
        retryAfter: parseRetryAfter(headers.value('retry-after'), now: now),
        requestId: requestId,
      );
    case 503 when code == 'maintenance':
      return MaintenanceFailure(requestId: requestId);
  }
  if (statusCode >= 500 && statusCode <= 599) {
    return ServerFailure(statusCode: statusCode, requestId: requestId);
  }
  return UnexpectedFailure(
    statusCode: statusCode,
    code: code,
    requestId: requestId,
  );
}

/// Parses `Retry-After` as delta seconds or an HTTP date. Returns null when absent or invalid.
Duration? parseRetryAfter(String? value, {DateTime? now}) {
  if (value == null || value.trim().isEmpty) {
    return null;
  }
  final trimmed = value.trim();
  final seconds = int.tryParse(trimmed);
  if (seconds != null) {
    return seconds < 0 ? null : Duration(seconds: seconds);
  }
  try {
    final date = HttpDate.parse(trimmed);
    final delta = date.difference(now ?? DateTime.now().toUtc());
    return delta.isNegative ? Duration.zero : delta;
  } on HttpException {
    return null;
  }
}
