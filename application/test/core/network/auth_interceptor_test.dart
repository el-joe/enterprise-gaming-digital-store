import 'dart:async';

import 'package:application/core/network/interceptors/auth_interceptor.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/fake_http_adapter.dart';
import '../../helpers/memory_stores.dart';

void main() {
  late Map<String, String> stored;
  late SecureStore store;
  late FakeHttpAdapter adapter;
  late int refreshCalls;
  late int expiredCalls;
  late Completer<AuthTokens?> refreshGate;

  Dio buildDio({TokenRefresher? refresh}) {
    final dio = dioWith(adapter);
    dio.interceptors.add(
      AuthInterceptor(
        dio: dio,
        store: store,
        refresh:
            refresh ??
            (token) {
              refreshCalls++;
              expect(token, 'refresh-old');
              return refreshGate.future;
            },
        onSessionExpired: () => expiredCalls++,
      ),
    );
    return dio;
  }

  setUp(() {
    stored = {
      SecureStore.accessTokenKey: 'access-old',
      SecureStore.refreshTokenKey: 'refresh-old',
    };
    store = SecureStore(storage: memorySecureStorage(stored));
    refreshCalls = 0;
    expiredCalls = 0;
    refreshGate = Completer<AuthTokens?>();
    adapter = FakeHttpAdapter((options, _) async {
      if (options.headers['Authorization'] == 'Bearer access-new') {
        return jsonBody(okEnvelope({'path': options.path}));
      }
      return jsonBody(errorEnvelope('unauthenticated'), status: 401);
    });
  });

  test('adds the bearer token from secure storage', () async {
    adapter = FakeHttpAdapter((options, _) async => jsonBody(okEnvelope()));
    await buildDio().get<Object?>('/account/wallet');
    expect(adapter.sentHeaders.single['Authorization'], 'Bearer access-old');
  });

  test('skips the token when the request opts out', () async {
    adapter = FakeHttpAdapter((options, _) async => jsonBody(okEnvelope()));
    await buildDio().post<Object?>(
      '/mobile/auth/login',
      options: Options(extra: {RequestExtras.authenticate: false}),
    );
    expect(adapter.sentHeaders.single.containsKey('Authorization'), isFalse);
  });

  test(
    'concurrent 401s trigger one refresh and every request is replayed',
    () async {
      final dio = buildDio();
      final responses = Future.wait([
        dio.get<Object?>('/a'),
        dio.get<Object?>('/b'),
        dio.get<Object?>('/c'),
      ]);
      // Let all three fail with the old token before the refresh answers.
      await pumpEventQueue();
      expect(refreshCalls, 1);
      refreshGate.complete(
        const AuthTokens(
          accessToken: 'access-new',
          refreshToken: 'refresh-new',
        ),
      );

      final results = await responses;
      expect(results.map((r) => r.statusCode), [200, 200, 200]);
      expect(refreshCalls, 1);
      expect(stored[SecureStore.accessTokenKey], 'access-new');
      expect(stored[SecureStore.refreshTokenKey], 'refresh-new');
      expect(
        adapter.sentHeaders.where(
          (h) => h['Authorization'] == 'Bearer access-new',
        ),
        hasLength(3),
      );
      expect(expiredCalls, 0);
    },
  );

  test(
    'a 401 arriving after a finished refresh reuses the new token',
    () async {
      final releaseSlow = Completer<void>();
      adapter = FakeHttpAdapter((options, _) async {
        if (options.headers['Authorization'] == 'Bearer access-new') {
          return jsonBody(okEnvelope());
        }
        if (options.path == '/slow') {
          await releaseSlow.future;
        }
        return jsonBody(errorEnvelope('unauthenticated'), status: 401);
      });
      final dio = buildDio();
      refreshGate.complete(
        const AuthTokens(
          accessToken: 'access-new',
          refreshToken: 'refresh-new',
        ),
      );
      final slow = dio.get<Object?>('/slow');
      await dio.get<Object?>('/fast');
      expect(refreshCalls, 1);

      releaseSlow.complete();
      expect((await slow).statusCode, 200);
      expect(refreshCalls, 1);
    },
  );

  test('refused refresh clears tokens once and surfaces the 401', () async {
    final dio = buildDio();
    final results = [
      dio.get<Object?>('/a'),
      dio.get<Object?>('/b'),
    ].map((f) => f.then<Object?>((r) => r, onError: (Object e) => e)).toList();
    await pumpEventQueue();
    refreshGate.complete(null);

    final outcomes = await Future.wait(results);
    for (final outcome in outcomes) {
      expect(
        outcome,
        isA<DioException>().having(
          (e) => e.response?.statusCode,
          'status',
          401,
        ),
      );
    }
    expect(refreshCalls, 1);
    expect(expiredCalls, 1);
    expect(stored, isEmpty);
  });

  test('a replayed request that fails again is not refreshed twice', () async {
    adapter = FakeHttpAdapter(
      (options, _) async =>
          jsonBody(errorEnvelope('unauthenticated'), status: 401),
    );
    final dio = buildDio();
    refreshGate.complete(
      const AuthTokens(accessToken: 'access-new', refreshToken: 'refresh-new'),
    );
    await expectLater(dio.get<Object?>('/a'), throwsA(isA<DioException>()));
    expect(refreshCalls, 1);
    expect(adapter.requests, hasLength(2));
  });

  test(
    'no stored refresh token signs nothing in and returns the 401',
    () async {
      stored.remove(SecureStore.refreshTokenKey);
      final dio = buildDio();
      await expectLater(dio.get<Object?>('/a'), throwsA(isA<DioException>()));
      expect(refreshCalls, 0);
    },
  );
}
