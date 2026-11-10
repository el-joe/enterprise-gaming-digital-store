import 'dart:io';

import 'package:application/core/network/error_mapper.dart';
import 'package:application/core/network/failure.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';

final _options = RequestOptions(path: '/test');

DioException _response(
  int status, {
  String? code,
  Map<String, Object?> extra = const {},
  Map<String, List<String>> headers = const {},
  Object? body,
}) {
  return DioException.badResponse(
    statusCode: status,
    requestOptions: _options,
    response: Response<Object?>(
      requestOptions: _options,
      statusCode: status,
      headers: Headers.fromMap(headers),
      data:
          body ??
          {
            'success': false,
            'message': 'Server message.',
            'code': ?code,
            'request_id': 'req-42',
            ...extra,
          },
    ),
  );
}

void main() {
  group('section 2.3 rows', () {
    test('no connection, DNS and TLS -> NetworkFailure', () {
      final errors = [
        DioException.connectionError(
          requestOptions: _options,
          reason: 'offline',
        ),
        DioException(
          requestOptions: _options,
          error: const SocketException('Failed host lookup'),
        ),
        DioException(
          requestOptions: _options,
          error: const HandshakeException('bad cert'),
        ),
        DioException.badCertificate(requestOptions: _options),
      ];
      for (final error in errors) {
        expect(mapError(error), isA<NetworkFailure>(), reason: '${error.type}');
      }
      expect(const NetworkFailure().isRetryable, isTrue);
    });

    test('timeout -> TimeoutFailure (retryable)', () {
      final errors = [
        DioException.connectionTimeout(
          requestOptions: _options,
          timeout: const Duration(seconds: 1),
        ),
        DioException.sendTimeout(
          requestOptions: _options,
          timeout: const Duration(seconds: 1),
        ),
        DioException.receiveTimeout(
          requestOptions: _options,
          timeout: const Duration(seconds: 1),
        ),
      ];
      for (final error in errors) {
        final failure = mapError(error);
        expect(failure, isA<TimeoutFailure>());
        expect(failure.isRetryable, isTrue);
      }
    });

    test('401 unauthenticated -> UnauthenticatedFailure', () {
      final failure = mapError(_response(401, code: 'unauthenticated'));
      expect(failure, isA<UnauthenticatedFailure>());
      expect(failure.requestId, 'req-42');
    });

    test('403 two_factor_required -> TwoFactorRequiredFailure', () {
      expect(
        mapError(_response(403, code: 'two_factor_required')),
        isA<TwoFactorRequiredFailure>(),
      );
    });

    test('403 email_unverified -> EmailUnverifiedFailure', () {
      expect(
        mapError(_response(403, code: 'email_unverified')),
        isA<EmailUnverifiedFailure>(),
      );
    });

    test('403 reveal_denied and forbidden -> ForbiddenFailure(code)', () {
      for (final code in [
        'reveal_denied',
        'forbidden',
        'margin_override_forbidden',
      ]) {
        final failure = mapError(_response(403, code: code));
        expect(
          failure,
          isA<ForbiddenFailure>().having((f) => f.code, 'code', code),
        );
      }
      expect(
        mapError(_response(403, body: '<html>Forbidden</html>')),
        isA<ForbiddenFailure>().having((f) => f.code, 'code', 'forbidden'),
      );
    });

    test('404 -> NotFoundFailure', () {
      expect(
        mapError(_response(404, code: 'not_found')),
        isA<NotFoundFailure>(),
      );
      expect(mapError(_response(404, body: '')), isA<NotFoundFailure>());
    });

    test('409 codes -> ConflictFailure(code)', () {
      for (final code in [
        'idempotency_in_progress',
        'idempotency_mismatch',
        'order_not_payable',
        'review_exists',
        'ticket_closed',
        'refund_request_open',
        'conflict',
      ]) {
        final failure = mapError(_response(409, code: code));
        expect(
          failure,
          isA<ConflictFailure>()
              .having((f) => f.code, 'code', code)
              .having((f) => f.message, 'message', 'Server message.'),
        );
      }
    });

    test('423 recent_auth_required -> RecentAuthRequiredFailure', () {
      expect(
        mapError(_response(423, code: 'recent_auth_required')),
        isA<RecentAuthRequiredFailure>(),
      );
    });

    test(
      '422 validation_failed -> ValidationFailure(fieldErrors) by dot key',
      () {
        final failure = mapError(
          _response(
            422,
            code: 'validation_failed',
            extra: {
              'errors': {
                'email': ['The email field is required.'],
                'variants.0.price': ['The price must be at least 0.01.'],
              },
            },
          ),
        );
        expect(failure, isA<ValidationFailure>());
        final validation = failure as ValidationFailure;
        expect(validation.fieldErrors.keys, ['email', 'variants.0.price']);
        expect(
          validation.firstErrorFor('variants.0.price'),
          'The price must be at least 0.01.',
        );
        expect(validation.firstErrorFor('name'), isNull);
      },
    );

    test('422 domain codes -> DomainFailure(code, message, errors)', () {
      for (final code in [
        'price_changed',
        'coupon_invalid',
        'item_unavailable',
        'gateway_not_eligible',
        'otp_invalid',
        'rate_stale',
      ]) {
        final failure = mapError(
          _response(
            422,
            code: code,
            extra: {
              'errors': {
                'items.7': ['Unavailable.'],
              },
            },
          ),
        );
        expect(
          failure,
          isA<DomainFailure>()
              .having((f) => f.code, 'code', code)
              .having((f) => f.message, 'message', 'Server message.')
              .having((f) => f.errors['items.7'], 'errors', ['Unavailable.']),
        );
      }
    });

    test('429 -> RateLimitedFailure(retryAfter) from seconds', () {
      final failure = mapError(
        _response(
          429,
          code: 'rate_limited',
          headers: {
            'retry-after': ['42'],
          },
        ),
      );
      expect(
        failure,
        isA<RateLimitedFailure>().having(
          (f) => f.retryAfter,
          'retryAfter',
          const Duration(seconds: 42),
        ),
      );
    });

    test('429 Retry-After as an HTTP date', () {
      final now = DateTime.utc(2026, 11, 10, 12);
      expect(
        parseRetryAfter('Tue, 10 Nov 2026 12:01:30 GMT', now: now),
        const Duration(seconds: 90),
      );
      expect(parseRetryAfter('soon'), isNull);
      expect(parseRetryAfter(null), isNull);
      expect(
        mapError(_response(429, code: 'rate_limited')),
        isA<RateLimitedFailure>().having(
          (f) => f.retryAfter,
          'retryAfter',
          isNull,
        ),
      );
    });

    test('503 maintenance -> MaintenanceFailure', () {
      expect(
        mapError(_response(503, code: 'maintenance')),
        isA<MaintenanceFailure>(),
      );
    });

    test('5xx -> ServerFailure(requestId), body ignored', () {
      for (final status in [500, 502, 504]) {
        final failure = mapError(_response(status, code: 'server_error'));
        expect(
          failure,
          isA<ServerFailure>()
              .having((f) => f.requestId, 'requestId', 'req-42')
              .having((f) => f.statusCode, 'statusCode', status),
        );
      }
      expect(
        mapError(_response(503, code: 'rates_provider_unavailable')),
        isA<ServerFailure>(),
      );
      final htmlFailure = mapError(
        _response(
          500,
          body: '<html>stack trace</html>',
          headers: {
            'x-request-id': ['01JREQ'],
          },
        ),
      );
      expect(
        htmlFailure,
        isA<ServerFailure>().having((f) => f.requestId, 'requestId', '01JREQ'),
      );
    });
  });

  group('outside the table', () {
    test('decoding errors -> DecodingFailure', () {
      expect(mapError(const FormatException('amount')), isA<DecodingFailure>());
    });

    test('cancel -> CancelledFailure', () {
      expect(
        mapError(
          DioException.requestCancelled(requestOptions: _options, reason: null),
        ),
        isA<CancelledFailure>(),
      );
    });

    test('419 and 400 -> UnexpectedFailure with the code', () {
      expect(
        mapError(_response(419, code: 'csrf_expired')),
        isA<UnexpectedFailure>()
            .having((f) => f.statusCode, 'status', 419)
            .having((f) => f.code, 'code', 'csrf_expired'),
      );
    });

    test('unknown errors -> UnexpectedFailure', () {
      expect(mapError(StateError('boom')), isA<UnexpectedFailure>());
    });
  });
}
