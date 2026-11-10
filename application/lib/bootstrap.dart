import 'dart:async';

import 'package:application/app.dart';
import 'package:application/core/config/app_env.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Receives uncaught errors. Debug builds print them; release builds stay silent until the
/// owner picks a crash reporter (MOBILE_APP_ROADMAP.md open decision 10).
typedef ErrorReporter = void Function(Object error, StackTrace stack);

void _defaultReporter(Object error, StackTrace stack) {
  if (kDebugMode) {
    debugPrint('Uncaught error: $error\n$stack');
  }
}

/// Starts the app for [flavor]: zone guard, error hooks, stores, then the [ProviderScope].
Future<void> bootstrap(
  Flavor flavor, {
  ErrorReporter reporter = _defaultReporter,
}) async {
  await runZonedGuarded(() async {
    WidgetsFlutterBinding.ensureInitialized();
    FlutterError.onError = (details) {
      FlutterError.presentError(details);
      reporter(details.exception, details.stack ?? StackTrace.current);
    };
    PlatformDispatcher.instance.onError = (error, stack) {
      reporter(error, stack);
      return true;
    };

    final env = AppEnv.fromEnvironment(expected: flavor);
    final preferences = await PreferencesStore.open();
    const secureStore = SecureStore();
    final signedIn = await secureStore.hasSession();

    runApp(
      ProviderScope(
        overrides: [
          appEnvProvider.overrideWithValue(env),
          preferencesStoreProvider.overrideWithValue(preferences),
          initialSessionProvider.overrideWithValue(
            signedIn ? SessionStatus.signedIn : SessionStatus.signedOut,
          ),
        ],
        child: const App(),
      ),
    );
  }, reporter);
}
