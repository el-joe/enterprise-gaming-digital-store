import 'package:application/core/network/interceptors/redacting_log_interceptor.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/fake_http_adapter.dart';

void main() {
  group('Redactor', () {
    test('masks the backend key list and the client additions', () {
      for (final key in [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'access_token',
        'refresh_token',
        'refreshToken',
        'client_secret',
        'secret',
        'Authorization',
        'Cookie',
        'Set-Cookie',
        'X-Cart-Token',
        'Idempotency-Key',
        'code',
        'pin',
        'pin_code',
        'redemption_url',
        'card_number',
        'cvv',
        'otp',
        'recovery_codes',
        'digital_code',
        'api_key',
        'signature',
      ]) {
        expect(Redactor.isSensitiveKey(key), isTrue, reason: key);
      }
    });

    test('leaves ordinary keys alone', () {
      for (final key in [
        'amount',
        'currency',
        'email',
        'status',
        'public_id',
        'country',
        'X-Request-Id',
        'promo_code_hint',
      ]) {
        expect(Redactor.isSensitiveKey(key), isFalse, reason: key);
      }
    });

    test('redacts nested maps and lists', () {
      final redacted = Redactor.redact({
        'data': {
          'units': [
            {'id': 'u1', 'code': 'ABCD-EFGH', 'pin': '1234'},
          ],
          'next': {'type': 'stripe', 'client_secret': 'pi_secret'},
          'total': {'amount': '10.00', 'currency': 'USD'},
        },
      });
      expect(redacted, {
        'data': {
          'units': [
            {
              'id': 'u1',
              'code': Redactor.replacement,
              'pin': Redactor.replacement,
            },
          ],
          'next': {'type': 'stripe', 'client_secret': Redactor.replacement},
          'total': {'amount': '10.00', 'currency': 'USD'},
        },
      });
    });

    test('masks sensitive query parameters', () {
      final uri = Redactor.redactUri(
        Uri.parse('https://x.test/access?expires=1&signature=abc&token=t'),
      );
      expect(uri.queryParameters['signature'], Redactor.replacement);
      expect(uri.queryParameters['token'], Redactor.replacement);
      expect(uri.queryParameters['expires'], '1');
    });

    test('drops bodies of reveal, auth and payment traffic', () {
      for (final path in [
        '/api/v1/account/orders/o/units/u/reveal',
        '/api/v1/checkout/guest/orders/o/units/u/reveal',
        '/api/v1/mobile/auth/login',
        '/api/v1/mobile/auth/refresh',
        '/api/v1/account/confirm-password',
        '/api/v1/checkout/place-order',
        '/api/v1/checkout/orders/o/verify-payment',
        '/api/v1/checkout/orders/o/paypal/capture',
        '/api/v1/checkout/guest/otp/verify',
        '/api/v1/account/security/two-factor',
      ]) {
        expect(
          Redactor.dropsBody(RequestOptions(path: 'https://x.test$path')),
          isTrue,
          reason: path,
        );
      }
      expect(
        Redactor.dropsBody(
          RequestOptions(
            path: 'https://x.test/api/v1/storefront/catalog/products',
          ),
        ),
        isFalse,
      );
      expect(
        Redactor.dropsBody(
          RequestOptions(
            path: 'https://x.test/a',
            extra: {RequestExtras.sensitive: true},
          ),
        ),
        isTrue,
      );
    });
  });

  group('RedactingLogInterceptor', () {
    test('never writes secrets to the log', () async {
      final lines = <String>[];
      final adapter = FakeHttpAdapter(
        (options, _) async =>
            jsonBody(okEnvelope({'token': 'tok-123', 'name': 'Ada'})),
      );
      final dio = dioWith(
        adapter,
        interceptors: [RedactingLogInterceptor(log: lines.add)],
      );
      await dio.post<Object?>(
        '/account/profile',
        data: {'name': 'Ada', 'password': 'hunter2'},
        options: Options(
          headers: {'Authorization': 'Bearer abc', 'Idempotency-Key': 'K1'},
        ),
      );
      final log = lines.join('\n');
      expect(log, isNot(contains('hunter2')));
      expect(log, isNot(contains('Bearer abc')));
      expect(log, isNot(contains('K1')));
      expect(log, isNot(contains('tok-123')));
      expect(log, contains('Ada'));
      expect(log, contains(Redactor.replacement));
    });

    test('omits reveal bodies entirely', () async {
      final lines = <String>[];
      final adapter = FakeHttpAdapter(
        (options, _) async => jsonBody(okEnvelope({'serial': 'SERIAL-999'})),
      );
      final dio = dioWith(
        adapter,
        interceptors: [RedactingLogInterceptor(log: lines.add)],
      );
      await dio.post<Object?>('/account/orders/o/units/u/reveal');
      final log = lines.join('\n');
      expect(log, isNot(contains('SERIAL-999')));
      expect(log, contains('[omitted]'));
    });
  });
}
