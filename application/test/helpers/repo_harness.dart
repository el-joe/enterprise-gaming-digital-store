import 'package:application/core/network/dio_client.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/interceptors/auth_interceptor.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';

import 'fake_http_adapter.dart';
import 'fixtures.dart';
import 'memory_stores.dart';
import 'pump_app.dart';

/// The real API client (every interceptor) on a fake adapter and in-memory secure storage.
class Harness {
  Harness({
    Map<String, String>? stored,
    String languageCode = 'en',
    String? currency,
    TokenRefresher? refresh,
  }) : stored = stored ?? <String, String>{} {
    store = SecureStore(storage: memorySecureStorage(this.stored));
    dio = createApiClient(
      env: testEnv,
      store: store,
      languageCode: () => languageCode,
      currency: () => currency,
      refresh: refresh ?? (_) async => null,
      onSessionExpired: () => sessionExpired++,
      enableLogging: false,
    );
  }

  final Map<String, String> stored;
  late final SecureStore store;
  late final Dio dio;
  late FakeHttpAdapter adapter;
  int sessionExpired = 0;

  /// Answers every request with [fixture] after checking that the request uses the fixture's
  /// method, path and recorded query (the request of the backend contract test).
  void serve(ApiFixture fixture) {
    adapter = FakeHttpAdapter((options, _) async {
      final recorded = Uri.parse(fixture.path);
      expect(options.method, fixture.method, reason: fixture.name);
      expect(
        options.uri.path,
        recorded.path,
        reason: '${fixture.name}: request path',
      );
      // Every recorded query parameter is sent with the same value (defaults such as
      // `page=1` may be added by the data source).
      for (final entry in recorded.queryParameters.entries) {
        expect(
          options.uri.queryParameters[entry.key],
          entry.value,
          reason: '${fixture.name}: query ${entry.key}',
        );
      }
      return fixture.response();
    });
    dio.httpClientAdapter = adapter;
  }

  /// Serves `test/fixtures/api/<name>.json`.
  ApiFixture serveFixture(String name) {
    final fixture = ApiFixture.load(name);
    serve(fixture);
    return fixture;
  }
}

/// The value of an [Ok], failing the test for an [Err].
T okValue<T>(Result<T> result) => switch (result) {
  Ok(:final value) => value,
  Err(:final failure) => throw TestFailure('Expected Ok, got $failure'),
};

/// The failure of an [Err], failing the test for an [Ok].
F failureOf<F extends Failure>(Result<Object?> result) {
  final failure = result.failureOrNull;
  expect(failure, isA<F>());
  return failure! as F;
}
