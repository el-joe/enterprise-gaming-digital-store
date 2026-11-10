import 'package:application/app.dart';
import 'package:application/core/config/app_env.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

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
  };
  for (final entry in families.entries) {
    final loader = FontLoader(entry.key);
    for (final asset in entry.value) {
      loader.addFont(rootBundle.load(asset));
    }
    await loader.load();
  }
}

/// Pumps the whole app with in-memory stores, a fixed device locale and [languageCode] saved.
Future<ProviderContainer> pumpApp(
  WidgetTester tester, {
  String? languageCode,
  List<Locale> deviceLocales = const [Locale('en')],
  Size size = const Size(390, 844),
}) async {
  tester.view
    ..physicalSize = size
    ..devicePixelRatio = 1;
  addTearDown(tester.view.reset);

  final prefs = PreferencesStore(
    InMemoryPreferencesBackend({PreferencesStore.localeKey: ?languageCode}),
  );
  final container = ProviderContainer(
    overrides: [
      appEnvProvider.overrideWithValue(testEnv),
      preferencesStoreProvider.overrideWithValue(prefs),
      deviceLocalesProvider.overrideWithValue(deviceLocales),
    ],
  );
  addTearDown(container.dispose);
  await tester.pumpWidget(
    UncontrolledProviderScope(container: container, child: const App()),
  );
  await tester.pumpAndSettle();
  return container;
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
