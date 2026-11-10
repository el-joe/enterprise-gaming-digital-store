import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/guards.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/pump_app.dart';

void main() {
  testWidgets('boots to the home shell', (tester) async {
    await pumpApp(tester);
    final l10n = lookupAppLocalizations(const Locale('en'));
    expect(find.text(l10n.homeTitle), findsOneWidget);
    expect(find.text(l10n.environmentBadge('dev')), findsOneWidget);
  });

  testWidgets('maintenance state redirects and recovers', (tester) async {
    final container = await pumpApp(tester);
    final l10n = lookupAppLocalizations(const Locale('en'));

    container
        .read(availabilityProvider.notifier)
        .update(AppAvailability.maintenance);
    await tester.pumpAndSettle();
    expect(find.text(l10n.maintenanceTitle), findsOneWidget);

    container
        .read(availabilityProvider.notifier)
        .update(AppAvailability.available);
    await tester.pumpAndSettle();
    expect(find.text(l10n.homeTitle), findsOneWidget);
  });

  testWidgets('update required shows its notice', (tester) async {
    final container = await pumpApp(tester);
    container
        .read(availabilityProvider.notifier)
        .update(AppAvailability.updateRequired);
    await tester.pumpAndSettle();
    expect(
      find.text(lookupAppLocalizations(const Locale('en')).updateRequiredTitle),
      findsOneWidget,
    );
  });

  testWidgets('unknown location shows not found with a way home', (
    tester,
  ) async {
    final container = await pumpApp(tester);
    final l10n = lookupAppLocalizations(const Locale('en'));
    container.read(appRouterProvider).go('/does-not-exist');
    await tester.pumpAndSettle();
    expect(find.text(l10n.notFoundTitle), findsOneWidget);
    await tester.tap(find.text(l10n.backToHome));
    await tester.pumpAndSettle();
    expect(find.text(l10n.homeTitle), findsOneWidget);
  });

  testWidgets('switching to Arabic mirrors the shell', (tester) async {
    await pumpApp(tester);
    final en = lookupAppLocalizations(const Locale('en'));
    final ar = lookupAppLocalizations(const Locale('ar'));
    expect(
      Directionality.of(tester.element(find.text(en.homeTitle))),
      TextDirection.ltr,
    );

    await tester.tap(find.text(en.switchLanguage));
    await tester.pumpAndSettle();

    expect(find.text(ar.homeTitle), findsOneWidget);
    expect(
      Directionality.of(tester.element(find.text(ar.homeTitle))),
      TextDirection.rtl,
    );

    await tester.tap(find.text(ar.switchLanguage));
    await tester.pumpAndSettle();
    expect(find.text(en.homeTitle), findsOneWidget);
  });

  testWidgets('device locale picks Arabic when nothing is saved', (
    tester,
  ) async {
    await pumpApp(tester, deviceLocales: const [Locale('ar', 'EG')]);
    expect(
      find.text(lookupAppLocalizations(const Locale('ar')).homeTitle),
      findsOneWidget,
    );
  });
}
