import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'app_env.g.dart';

/// Build flavors. Android product flavors use the same names (`android/app/build.gradle.kts`).
enum Flavor { dev, staging, prod }

/// Non-secret build configuration read from `--dart-define-from-file=lib/core/config/env_<flavor>.json`.
///
/// Provider publishable keys and feature flags come from the server at runtime, never from here.
final class AppEnv {
  const AppEnv({
    required this.flavor,
    required this.apiBaseUrl,
    required this.appLinkHost,
  });

  /// Reads the compile-time defines and checks that they match the entry point's [expected] flavor.
  factory AppEnv.fromEnvironment({required Flavor expected}) {
    return AppEnv.fromValues(
      expected: expected,
      flavorName: const String.fromEnvironment('FLAVOR'),
      apiBaseUrl: const String.fromEnvironment('API_BASE_URL'),
      appLinkHost: const String.fromEnvironment('APP_LINK_HOST'),
    );
  }

  /// Validates raw define values; split from [AppEnv.fromEnvironment] so it can be tested.
  factory AppEnv.fromValues({
    required Flavor expected,
    required String flavorName,
    required String apiBaseUrl,
    required String appLinkHost,
  }) {
    if (flavorName != expected.name) {
      throw StateError(
        'Entry point for "${expected.name}" was built with FLAVOR="$flavorName". '
        'Pass --dart-define-from-file=lib/core/config/env_${expected.name}.json.',
      );
    }
    final base = Uri.tryParse(apiBaseUrl);
    if (base == null || !base.hasScheme || base.host.isEmpty) {
      throw StateError('API_BASE_URL "$apiBaseUrl" is not an absolute URL.');
    }
    if (expected != Flavor.dev && base.scheme != 'https') {
      throw StateError('API_BASE_URL must use https outside the dev flavor.');
    }
    if (appLinkHost.isEmpty) {
      throw StateError('APP_LINK_HOST is required.');
    }
    return AppEnv(flavor: expected, apiBaseUrl: base, appLinkHost: appLinkHost);
  }

  final Flavor flavor;

  /// Origin of the Laravel app, for example `https://boxcards.shop`.
  final Uri apiBaseUrl;

  /// Host of verified app links (payment returns, email links).
  final String appLinkHost;

  /// Base URL for the JSON API (`/api/v1`).
  String get apiV1BaseUrl {
    final origin = apiBaseUrl.toString().replaceFirst(RegExp(r'/+$'), '');
    return '$origin/api/v1';
  }
}

/// The running environment. Overridden once in `bootstrap.dart`.
@Riverpod(keepAlive: true)
AppEnv appEnv(Ref ref) {
  throw StateError('appEnvProvider must be overridden in bootstrap.');
}
