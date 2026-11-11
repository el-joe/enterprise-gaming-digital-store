import 'package:application/app.dart';
import 'package:application/core/config/app_env.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/account/account_providers.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';
import 'package:application/features/auth/auth_providers.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/misc.dart' show Override, ProviderListenable;
import 'package:flutter_test/flutter_test.dart';

import 'fakes.dart';
import 'memory_stores.dart';

final testEnv = AppEnv(
  flavor: Flavor.dev,
  apiBaseUrl: Uri.parse('https://api.test'),
  appLinkHost: 'boxcards.test',
);

/// Loads the bundled fonts so text renders with the real families in goldens.
Future<void> loadAppFonts() async {
  const families = {
    'Changa': ['assets/fonts/changa/Changa-Variable.ttf'],
    'ReadexPro': ['assets/fonts/readex_pro/ReadexPro-Variable.ttf'],
    'IBMPlexMono': [
      'assets/fonts/ibm_plex_mono/IBMPlexMono-Regular.ttf',
      'assets/fonts/ibm_plex_mono/IBMPlexMono-Medium.ttf',
    ],
    // Bundled by `uses-material-design`; without it icons render as boxes in goldens.
    'MaterialIcons': ['fonts/MaterialIcons-Regular.otf'],
  };
  for (final entry in families.entries) {
    final loader = FontLoader(entry.key);
    for (final asset in entry.value) {
      loader.addFont(rootBundle.load(asset));
    }
    await loader.load();
  }
}

/// Every fake behind a pumped app, for arranging and asserting.
final class TestHarness {
  TestHarness({
    required this.container,
    required this.appConfig,
    required this.auth,
    required this.account,
    required this.keys,
    required this.biometricServer,
    required this.native,
    required this.prefs,
    required this.secure,
    required this.now,
  });

  final ProviderContainer container;
  final FakeAppConfigRepository appConfig;
  final FakeAuthRepository auth;
  final MockAccountRepository account;
  final FakeBiometricKeyStore keys;
  final FakeBiometricAuthRepository biometricServer;
  final FakeNativeBridge native;
  final InMemoryPreferencesBackend prefs;
  final Map<String, String> secure;

  /// The test clock; move it with `harness.now.value = ...`.
  final ValueNotifier<DateTime> now;

  T read<T>(ProviderListenable<T> provider) => container.read(provider);

  void dispose() {
    container.dispose();
    now.dispose();
  }
}

/// A provider container with in-memory stores and fakes for every repository the auth and
/// launch screens use (controller tests use it directly; [pumpHarness] pumps the app on it).
TestHarness buildHarness({
  String? languageCode,
  List<Locale> deviceLocales = const [Locale('en')],
  bool onboarded = true,
  bool signedIn = false,
  bool biometricEnabled = false,
  BiometricSupport biometricSupport = BiometricSupport.unavailable,
  List<Result<AppConfig>>? config,
  String integrity = 'ok',
  Map<String, Object> preferences = const {},
  List<Override> overrides = const [],
}) {
  final prefsBackend = InMemoryPreferencesBackend({
    PreferencesStore.localeKey: ?languageCode,
    if (onboarded) PreferencesStore.onboardingCompletedKey: true,
    ...preferences,
  });
  final secure = <String, String>{
    if (signedIn) ...{
      SecureStore.accessTokenKey: 'test-access',
      SecureStore.refreshTokenKey: 'test-refresh',
    },
    if (biometricEnabled) SecureStore.biometricEnabledKey: 'true',
  };
  final appConfig = FakeAppConfigRepository(config);
  final auth = FakeAuthRepository();
  final account = MockAccountRepository();
  final keys = FakeBiometricKeyStore(supportValue: biometricSupport)
    ..hasKey = biometricEnabled;
  final biometricServer = FakeBiometricAuthRepository();
  final native = FakeNativeBridge(integrity: integrity);
  final now = ValueNotifier(DateTime.utc(2026, 11, 11, 12));

  final container = ProviderContainer(
    overrides: [
      appEnvProvider.overrideWithValue(testEnv),
      preferencesStoreProvider.overrideWithValue(
        PreferencesStore(prefsBackend),
      ),
      deviceLocalesProvider.overrideWithValue(deviceLocales),
      secureStoreProvider.overrideWithValue(
        SecureStore(storage: memorySecureStorage(secure)),
      ),
      initialSessionProvider.overrideWithValue(
        signedIn ? SessionStatus.signedIn : SessionStatus.signedOut,
      ),
      appConfigRepositoryProvider.overrideWithValue(appConfig),
      authRepositoryProvider.overrideWith((ref) {
        auth
          ..onSignedIn = (() =>
              ref.read(sessionProvider.notifier).markSignedIn())
          ..onSignedOut = (() =>
              ref.read(sessionProvider.notifier).markSignedOut());
        return auth;
      }),
      accountRepositoryProvider.overrideWithValue(account),
      biometricKeyStoreProvider.overrideWithValue(keys),
      biometricAuthRepositoryProvider.overrideWithValue(biometricServer),
      nativeBridgeProvider.overrideWithValue(native),
      clockProvider.overrideWithValue(() => now.value),
      ...overrides,
    ],
  );
  return TestHarness(
    container: container,
    appConfig: appConfig,
    auth: auth,
    account: account,
    keys: keys,
    biometricServer: biometricServer,
    native: native,
    prefs: prefsBackend,
    secure: secure,
    now: now,
  );
}

/// Pumps the whole app on [buildHarness]. The app starts on the splash, which runs the (fake)
/// config check and continues to home ([onboarded]) or onboarding.
Future<TestHarness> pumpHarness(
  WidgetTester tester, {
  String? languageCode,
  List<Locale> deviceLocales = const [Locale('en')],
  Size size = const Size(390, 844),
  bool onboarded = true,
  bool signedIn = false,
  bool biometricEnabled = false,
  BiometricSupport biometricSupport = BiometricSupport.unavailable,
  List<Result<AppConfig>>? config,
  String integrity = 'ok',
  Map<String, Object> preferences = const {},
  List<Override> overrides = const [],
  bool settle = true,
}) async {
  tester.view
    ..physicalSize = size
    ..devicePixelRatio = 1;
  addTearDown(tester.view.reset);
  final harness = buildHarness(
    languageCode: languageCode,
    deviceLocales: deviceLocales,
    onboarded: onboarded,
    signedIn: signedIn,
    biometricEnabled: biometricEnabled,
    biometricSupport: biometricSupport,
    config: config,
    integrity: integrity,
    preferences: preferences,
    overrides: overrides,
  );
  addTearDown(harness.dispose);
  await tester.pumpWidget(
    UncontrolledProviderScope(container: harness.container, child: const App()),
  );
  if (settle) {
    await tester.pumpAndSettle();
  }
  return harness;
}

/// Kept for the foundation tests: pumps the app and returns its container.
Future<ProviderContainer> pumpApp(
  WidgetTester tester, {
  String? languageCode,
  List<Locale> deviceLocales = const [Locale('en')],
  Size size = const Size(390, 844),
}) async {
  final harness = await pumpHarness(
    tester,
    languageCode: languageCode,
    deviceLocales: deviceLocales,
    size: size,
  );
  return harness.container;
}

/// Decodes every [Image] on screen so goldens include the brand assets.
Future<void> precacheImages(WidgetTester tester) async {
  await tester.runAsync(() async {
    for (final element in find.byType(Image).evaluate()) {
      final image = element.widget as Image;
      await precacheImage(image.image, element);
    }
  });
  await tester.pumpAndSettle();
}

/// The English or Arabic strings.
AppLocalizations l10nFor(String languageCode) =>
    lookupAppLocalizations(Locale(languageCode));
