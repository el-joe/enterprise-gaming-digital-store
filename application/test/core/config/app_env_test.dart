import 'dart:convert';
import 'dart:io';

import 'package:application/core/config/app_env.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('each env file matches its flavor and holds only known keys', () {
    for (final flavor in Flavor.values) {
      final file = File('lib/core/config/env_${flavor.name}.json');
      final json = jsonDecode(file.readAsStringSync()) as Map<String, Object?>;
      expect(json.keys.toSet(), {'FLAVOR', 'API_BASE_URL', 'APP_LINK_HOST'});
      final env = AppEnv.fromValues(
        expected: flavor,
        flavorName: json['FLAVOR']! as String,
        apiBaseUrl: json['API_BASE_URL']! as String,
        appLinkHost: json['APP_LINK_HOST']! as String,
      );
      expect(env.flavor, flavor);
      expect(env.apiV1BaseUrl, endsWith('/api/v1'));
    }
  });

  test('env files contain no secret-looking keys or values', () {
    final pattern = RegExp(
      'secret|password|sk_|private|token',
      caseSensitive: false,
    );
    for (final flavor in Flavor.values) {
      final text = File('lib/core/config/env_${flavor.name}.json')
          .readAsStringSync();
      expect(pattern.hasMatch(text), isFalse, reason: flavor.name);
    }
  });

  test('rejects a mismatched flavor, bad URL and cleartext outside dev', () {
    expect(
      () => AppEnv.fromValues(
        expected: Flavor.prod,
        flavorName: 'dev',
        apiBaseUrl: 'https://a.test',
        appLinkHost: 'a.test',
      ),
      throwsStateError,
    );
    expect(
      () => AppEnv.fromValues(
        expected: Flavor.dev,
        flavorName: 'dev',
        apiBaseUrl: 'not a url',
        appLinkHost: 'a.test',
      ),
      throwsStateError,
    );
    expect(
      () => AppEnv.fromValues(
        expected: Flavor.staging,
        flavorName: 'staging',
        apiBaseUrl: 'http://a.test',
        appLinkHost: 'a.test',
      ),
      throwsStateError,
    );
    expect(
      AppEnv.fromValues(
        expected: Flavor.dev,
        flavorName: 'dev',
        apiBaseUrl: 'http://10.0.2.2:8000/',
        appLinkHost: 'localhost',
      ).apiV1BaseUrl,
      'http://10.0.2.2:8000/api/v1',
    );
  });
}
