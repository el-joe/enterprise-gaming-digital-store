import 'dart:async';

import 'package:application/core/config/app_env.dart';
import 'package:application/core/network/dio_client.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/network/token_refresher.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/account/account_providers.dart';
import 'package:application/features/notifications/notifications_providers.dart';
import 'package:application/features/wallet/wallet_providers.dart';
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/fake_http_adapter.dart';
import '../../helpers/fixtures.dart';
import '../../helpers/memory_stores.dart';
import '../../helpers/pump_app.dart';
import '../../helpers/repo_harness.dart';

void main() {
  late Map<String, String> stored;
  late ProviderContainer container;
  late FakeHttpAdapter adapter;
  late int refreshCalls;
  late Completer<void> refreshGate;
  late ApiFixture refreshAnswer;

  final wallet = ApiFixture.load('wallet/wallet_ok');
  final devices = ApiFixture.load('account/devices_ok');
  final unread = ApiFixture.load('notifications/unread_count_ok');
  final unauthenticated = ApiFixture.load('auth/me_unauthenticated');

  setUp(() {
    stored = {
      SecureStore.accessTokenKey: 'access-old',
      SecureStore.refreshTokenKey: 'refresh-old',
    };
    refreshCalls = 0;
    refreshGate = Completer<void>();
    refreshAnswer = ApiFixture.load('auth/refresh_ok');
    adapter = FakeHttpAdapter((options, _) async {
      final path = options.uri.path;
      if (path.endsWith(MobileTokenRefresher.path)) {
        refreshCalls++;
        expect((options.data as Map)['refresh_token'], 'refresh-old');
        expect(options.headers.containsKey('Authorization'), isFalse);
        await refreshGate.future;
        return refreshAnswer.response();
      }
      if (options.headers['Authorization'] != 'Bearer fixture-access-token') {
        return unauthenticated.response();
      }
      if (path.endsWith('/wallet')) {
        return wallet.response();
      }
      if (path.endsWith('/devices')) {
        return devices.response();
      }
      return unread.response();
    });
    container = ProviderContainer(
      overrides: [
        appEnvProvider.overrideWithValue(testEnv),
        preferencesStoreProvider.overrideWithValue(
          PreferencesStore(InMemoryPreferencesBackend()),
        ),
        secureStoreProvider.overrideWithValue(
          SecureStore(storage: memorySecureStorage(stored)),
        ),
        initialSessionProvider.overrideWithValue(SessionStatus.signedIn),
      ],
    );
    addTearDown(container.dispose);
    container.read(apiClientProvider).httpClientAdapter = adapter;
    container.read(refreshClientProvider).httpClientAdapter = adapter;
  });

  Future<List<Result<Object?>>> threeConcurrentCalls() {
    final calls = Future.wait<Result<Object?>>([
      container.read(walletRepositoryProvider).wallet(),
      container.read(accountRepositoryProvider).devices(),
      container.read(notificationsRepositoryProvider).unreadCount(),
    ]);
    return calls;
  }

  test('concurrent 401s refresh once and every request is replayed', () async {
    final calls = threeConcurrentCalls();
    await pumpEventQueue();
    refreshGate.complete();
    final results = await calls;

    expect(refreshCalls, 1);
    expect(results.map((r) => r.isOk), [true, true, true]);
    expect(stored[SecureStore.accessTokenKey], 'fixture-access-token');
    expect(stored[SecureStore.refreshTokenKey], 'fixture-refresh-token');
    expect(container.read(sessionProvider), SessionStatus.signedIn);
    final replays = adapter.sentHeaders.where(
      (h) => h['Authorization'] == 'Bearer fixture-access-token',
    );
    expect(replays, hasLength(3));
  });

  test(
    'a reused refresh token (401 refresh_token_invalid) signs out once',
    () async {
      refreshAnswer = ApiFixture.load('auth/refresh_token_invalid');

      final calls = threeConcurrentCalls();
      await pumpEventQueue();
      refreshGate.complete();
      final results = await calls;

      expect(refreshCalls, 1);
      for (final result in results) {
        failureOf<UnauthenticatedFailure>(result);
      }
      expect(stored.containsKey(SecureStore.accessTokenKey), isFalse);
      expect(stored.containsKey(SecureStore.refreshTokenKey), isFalse);
      expect(container.read(sessionProvider), SessionStatus.signedOut);
    },
  );

  test('a refresh that cannot reach the server keeps the session', () async {
    container.read(refreshClientProvider).httpClientAdapter = FakeHttpAdapter(
      (options, _) async => throw DioException(
        requestOptions: options,
        type: DioExceptionType.connectionError,
      ),
    );
    refreshGate.complete();

    final result = await container.read(walletRepositoryProvider).wallet();

    failureOf<UnauthenticatedFailure>(result);
    expect(stored[SecureStore.refreshTokenKey], 'refresh-old');
    expect(container.read(sessionProvider), SessionStatus.signedIn);
  });
}
