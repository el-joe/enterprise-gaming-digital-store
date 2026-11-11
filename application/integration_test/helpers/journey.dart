import 'package:application/app.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';

import '../../test/helpers/fixtures.dart';
import '../../test/helpers/pump_app.dart';
import '../fixtures/api_fixtures.g.dart';

/// Shared set-up of the M-A19 journeys. They run the real app (router, theme, controllers,
/// use cases) on an emulator or device against the same fake repositories as the widget tests,
/// answering with the backend contract fixtures: no live backend, no provider sandbox, no
/// network. The fixtures are compiled in ([apiFixtureBundle]) because the device has no
/// `test/fixtures` folder.
IntegrationTestWidgetsFlutterBinding initJourneys() {
  final binding = IntegrationTestWidgetsFlutterBinding.ensureInitialized();
  ApiFixture.bundle = apiFixtureBundle;
  return binding;
}

/// Starts the app at the splash, as a cold launch does, and waits for it to settle. The device's
/// own screen size and pixel ratio are used (unlike the widget tests' fixed 390x844).
Future<TestHarness> launchApp(
  WidgetTester tester, {
  String lang = 'en',
  bool onboarded = true,
  bool signedIn = false,
  List<Result<AppConfig>>? config,
  Map<String, Object> preferences = const {},
  void Function(TestHarness h)? arrange,
}) async {
  final h = buildHarness(
    languageCode: lang,
    deviceLocales: [Locale(lang)],
    onboarded: onboarded,
    signedIn: signedIn,
    config: config,
    preferences: preferences,
  );
  addTearDown(h.dispose);
  arrange?.call(h);
  await tester.pumpWidget(
    UncontrolledProviderScope(container: h.container, child: const App()),
  );
  await tester.pumpAndSettle();
  return h;
}

/// The current route path, including pushed routes.
String currentPath(TestHarness h) => h.read(appRouterProvider).state.uri.path;

Finder byKey(String value) => find.byKey(ValueKey(value));

/// Brings [finder] on screen (scrolling the first scrollable that contains it), then taps it.
Future<void> tapVisible(WidgetTester tester, Finder finder) async {
  await tester.ensureVisible(finder);
  await tester.pumpAndSettle();
  await tester.tap(finder);
  await tester.pumpAndSettle();
}

/// Taps the button whose label is [label] (filled, outlined or text button).
Future<void> tapButton(WidgetTester tester, String label) => tapVisible(
  tester,
  find
      .ancestor(
        of: find.text(label),
        matching: find.byWidgetPredicate((w) => w is ButtonStyleButton),
      )
      .first,
);

/// Scrolls the first scrollable of the screen until [finder] is built, then shows it.
Future<void> scrollTo(WidgetTester tester, Finder finder) async {
  await tester.scrollUntilVisible(
    finder,
    240,
    scrollable: find.byType(Scrollable).first,
  );
  await tester.pumpAndSettle();
}

/// Opens a bottom navigation tab by its label.
Future<void> openTab(WidgetTester tester, String label) async {
  await tester.tap(find.text(label).last);
  await tester.pumpAndSettle();
}

/// [text] without the LRI/PDI isolates the app puts around amounts.
String plain(String text) => text.replaceAll(RegExp('[\\u2066\\u2069]'), '');

/// A `Text` whose data contains [part] (isolates ignored).
Finder textContaining(String part) => find.byWidgetPredicate(
  (widget) => widget is Text && plain(widget.data ?? '').contains(plain(part)),
);
