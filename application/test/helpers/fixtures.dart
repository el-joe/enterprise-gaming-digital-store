import 'dart:convert';
import 'dart:io';

import 'package:dio/dio.dart';

import 'fake_http_adapter.dart';

/// A recorded response from `test/fixtures/api` (copied from `docs/api-fixtures` by
/// `tool/sync_fixtures.sh`; written by the backend contract suite, gap G11).
final class ApiFixture {
  ApiFixture._(
    this.name,
    this.method,
    this.path,
    this.status,
    this.headers,
    this.body,
  );

  /// Loads `test/fixtures/api/<name>.json`, for example `catalog/products_en`.
  factory ApiFixture.load(String name) {
    final bundled = bundle;
    final text = bundled != null
        ? bundled[name] ?? (throw StateError('No bundled fixture $name'))
        : File('test/fixtures/api/$name.json').readAsStringSync();
    final json = jsonDecode(text) as Map<String, dynamic>;
    final request = json['request'] as Map<String, dynamic>;
    return ApiFixture._(
      name,
      request['method'] as String,
      request['path'] as String,
      json['status'] as int,
      (json['headers'] as Map<String, dynamic>).cast<String, String>(),
      json['body'],
    );
  }

  /// A fixture built in a test (for a variant of a recorded response).
  factory ApiFixture.fromParts({
    required String name,
    required String method,
    required String path,
    required int status,
    required Map<String, String> headers,
    required Object? body,
  }) => ApiFixture._(name, method, path, status, headers, body);

  /// The fixtures compiled into an integration test (name -> JSON text, from
  /// `integration_test/fixtures/api_fixtures.g.dart`). On a device or emulator the files under
  /// `test/fixtures/api` do not exist, so the integration suite sets this before any test; widget
  /// and unit tests leave it null and read the files.
  static Map<String, String>? bundle;

  final String name;
  final String method;

  /// Path as recorded, starting with `/api/v1/mobile/...` (ids normalized).
  final String path;
  final int status;
  final Map<String, String> headers;
  final Object? body;

  /// The response `data` of a success envelope.
  Object? get data => (body! as Map<String, dynamic>)['data'];

  Map<String, dynamic> get dataMap => data! as Map<String, dynamic>;

  /// A response body for the fake adapter, with the recorded status and headers.
  ResponseBody response() => jsonBody(
    body,
    status: status,
    headers: {
      for (final entry in headers.entries) entry.key: [entry.value],
    },
  );

  /// Every fixture name under `test/fixtures/api` (for "every fixture parses" tests).
  static List<String> all() {
    if (bundle case final bundled?) {
      return bundled.keys.toList()..sort();
    }
    final root = Directory('test/fixtures/api');
    final names = <String>[];
    for (final entity in root.listSync(recursive: true)) {
      if (entity is File && entity.path.endsWith('.json')) {
        final relative = entity.path.substring(root.path.length + 1);
        names.add(relative.substring(0, relative.length - '.json'.length));
      }
    }
    return names..sort();
  }
}

/// Answers each request with the fixture registered for its method and path (path compared
/// without `/api/v1` and without the query), or [fallback]. Records requests like
/// [FakeHttpAdapter].
FakeHttpAdapter fixtureAdapter(
  Map<String, ApiFixture> routes, {
  ResponseBody Function(RequestOptions options)? fallback,
}) {
  return FakeHttpAdapter((options, _) async {
    final key =
        '${options.method} ${options.uri.path.replaceFirst('/api/v1', '')}';
    final fixture = routes[key];
    if (fixture != null) {
      return fixture.response();
    }
    if (fallback != null) {
      return fallback(options);
    }
    throw StateError('No fixture for $key');
  });
}
