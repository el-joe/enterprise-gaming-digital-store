import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/home/presentation/pages/home_page.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/fakes.dart';
import '../../helpers/pump_app.dart';

void main() {
  for (final lang in ['en', 'ar']) {
    final l10n = l10nFor(lang);
    final rtl = lang == 'ar';

    group('launch pages ($lang)', () {
      testWidgets('splash offline offers a retry that continues home', (
        tester,
      ) async {
        final h = await pumpHarness(
          tester,
          languageCode: lang,
          config: [const Err(NetworkFailure()), Ok(fixtureAppConfig())],
        );
        expect(find.text(l10n.offlineTitle), findsOneWidget);
        expect(
          Directionality.of(tester.element(find.text(l10n.offlineTitle))),
          rtl ? TextDirection.rtl : TextDirection.ltr,
        );
        await tester.tap(find.text(l10n.actionTryAgain));
        await tester.pumpAndSettle();
        expect(find.byKey(HomeScreen.pageKey), findsOneWidget);
        expect(h.appConfig.calls, hasLength(2));
      });

      testWidgets('splash failure shows the mapped message and reference', (
        tester,
      ) async {
        await pumpHarness(
          tester,
          languageCode: lang,
          config: [
            const Err(ServerFailure(statusCode: 500, requestId: 'REQ1')),
          ],
        );
        expect(find.text(l10n.startupErrorTitle), findsOneWidget);
        expect(
          find.textContaining(l10n.requestReference('REQ1')),
          findsOneWidget,
        );
      });

      testWidgets('force update shows the versions and opens the store', (
        tester,
      ) async {
        final h = await pumpHarness(
          tester,
          languageCode: lang,
          config: [Ok(fixtureAppConfig(updateRequired: true))],
        );
        expect(find.text(l10n.updateRequiredTitle), findsOneWidget);
        expect(
          find.text(
            l10n.updateRequiredVersions(
              '\u20661.0.0\u2069',
              '\u20661.2.0\u2069',
            ),
          ),
          findsOneWidget,
        );
        await tester.tap(find.text(l10n.updateNow));
        await tester.pumpAndSettle();
        expect(h.native.storeOpens, 1);

        h.native.opens = false;
        await tester.tap(find.text(l10n.updateNow));
        await tester.pumpAndSettle();
        expect(find.text(l10n.updateStoreUnavailable), findsOneWidget);
      });

      testWidgets(
        'maintenance shows the server message, retries and recovers',
        (tester) async {
          final h = await pumpHarness(
            tester,
            languageCode: lang,
            config: [
              Ok(
                fixtureAppConfig(
                  maintenance: true,
                  maintenanceMessage: 'Back at 14:00',
                ),
              ),
              Ok(fixtureAppConfig()),
            ],
          );
          expect(find.text(l10n.maintenanceTitle), findsOneWidget);
          expect(find.text('Back at 14:00'), findsOneWidget);
          expect(find.text(l10n.maintenanceOrdersSafeTitle), findsOneWidget);

          await tester.tap(find.text(l10n.contactSupport));
          await tester.pumpAndSettle();
          expect(h.native.opened.single.host, 'help.boxcards.test');

          await tester.tap(find.text(l10n.actionTryAgain));
          await tester.pumpAndSettle();
          expect(find.byKey(HomeScreen.pageKey), findsOneWidget);
        },
      );

      testWidgets('onboarding: language, then currency, then home', (
        tester,
      ) async {
        final h = await pumpHarness(
          tester,
          languageCode: lang,
          onboarded: false,
        );
        expect(find.text(l10n.onboardingLanguageTitle), findsOneWidget);
        expect(find.text(l10n.onboardingStep(1, 2)), findsOneWidget);

        // Pick the other language: the screen switches and mirrors at once.
        final other = rtl ? 'en' : 'ar';
        final otherL10n = l10nFor(other);
        await tester.tap(
          find.text(rtl ? l10n.languageEnglish : l10n.languageArabic),
        );
        await tester.pumpAndSettle();
        expect(find.text(otherL10n.onboardingLanguageTitle), findsOneWidget);
        expect(
          Directionality.of(
            tester.element(find.text(otherL10n.onboardingLanguageTitle)),
          ),
          rtl ? TextDirection.ltr : TextDirection.rtl,
        );

        await tester.tap(find.text(otherL10n.actionContinue));
        await tester.pumpAndSettle();
        expect(find.text(otherL10n.onboardingCurrencyTitle), findsOneWidget);
        expect(find.text(otherL10n.chargeNoticeTitle), findsOneWidget);
        final start = find.widgetWithText(
          FilledButton,
          otherL10n.startShopping,
        );
        expect(tester.widget<FilledButton>(start).onPressed, isNull);

        await tester.tap(find.text('USD'));
        await tester.pumpAndSettle();
        await tester.tap(start);
        await tester.pumpAndSettle();
        expect(find.byKey(HomeScreen.pageKey), findsOneWidget);
        expect(h.prefs.values[PreferencesStore.currencyKey], 'USD');
        expect(h.prefs.values[PreferencesStore.onboardingCompletedKey], isTrue);
      });

      testWidgets('onboarding can be skipped without a currency', (
        tester,
      ) async {
        final h = await pumpHarness(
          tester,
          languageCode: lang,
          onboarded: false,
        );
        await tester.tap(find.text(l10n.actionContinue));
        await tester.pumpAndSettle();
        await tester.tap(find.text(l10n.skipForNow));
        await tester.pumpAndSettle();
        expect(find.byKey(HomeScreen.pageKey), findsOneWidget);
        expect(
          h.prefs.values.containsKey(PreferencesStore.currencyKey),
          isFalse,
        );
      });

      testWidgets('rooted device: the warning sheet shows once, never blocks', (
        tester,
      ) async {
        final h = await pumpHarness(
          tester,
          languageCode: lang,
          integrity: 'compromised',
        );
        expect(find.text(l10n.integrityTitle), findsOneWidget);
        await tester.tap(find.text(l10n.integrityAcknowledge));
        await tester.pumpAndSettle();
        expect(find.byKey(HomeScreen.pageKey), findsOneWidget);
        expect(
          h.prefs.values[PreferencesStore.integrityWarningShownKey],
          isTrue,
        );

        // A second launch (splash again) does not show it.
        h.read(appRouterProvider).go('/splash');
        await tester.pumpAndSettle();
        expect(find.text(l10n.integrityTitle), findsNothing);
        expect(find.byKey(HomeScreen.pageKey), findsOneWidget);
      });
    });
  }

  testWidgets('status banners are live regions', (tester) async {
    await pumpHarness(tester, config: [const Err(NetworkFailure())]);
    final semantics = tester.getSemantics(find.byType(StatusBanner));
    expect(semantics.flagsCollection.isLiveRegion, isTrue);
  });
}
