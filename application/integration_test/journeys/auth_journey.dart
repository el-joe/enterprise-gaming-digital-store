import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/auth/domain/entities/auth_entities.dart';
import 'package:application/features/home/presentation/pages/home_page.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../test/helpers/pump_app.dart';
import '../helpers/journey.dart';

/// Launch -> onboarding (language, currency) -> home -> Account -> sign in -> two-step code ->
/// signed in.
void authJourney(String lang) {
  final l10n = l10nFor(lang);

  testWidgets(
    'launch, onboarding, sign in with two-step verification ($lang)',
    (tester) async {
      final h = await launchApp(tester, lang: lang, onboarded: false);

      // Onboarding 1/2: the language chosen at first launch is kept.
      expect(find.text(l10n.onboardingLanguageTitle), findsOneWidget);
      await tapButton(tester, l10n.actionContinue);

      // Onboarding 2/2: a display currency, then the store.
      expect(find.text(l10n.onboardingCurrencyTitle), findsOneWidget);
      await tester.tap(find.text('USD'));
      await tester.pumpAndSettle();
      await tapButton(tester, l10n.startShopping);
      expect(find.byKey(HomeScreen.pageKey), findsOneWidget);
      expect(h.prefs.values[PreferencesStore.onboardingCompletedKey], isTrue);

      // Account tab, signed out: sign in.
      await openTab(tester, l10n.navAccount);
      await tapVisible(tester, byKey('account-sign-in'));
      expect(currentPath(h), Routes.login.path);

      h.auth.loginResult = Ok(
        LoginOutcome.twoFactorRequired(
          challengeToken: 'challenge',
          expiresAt: h.now.value.add(const Duration(minutes: 5)),
        ),
      );
      await tester.enterText(find.byType(TextField).at(0), 'totp@example.com');
      await tester.enterText(find.byType(TextField).at(1), 'secret');
      await tapButton(tester, l10n.signInAction);
      expect(h.auth.loginCalls.single.email, 'totp@example.com');

      // Two-step verification: six digits complete the sign-in.
      expect(find.text(l10n.twoFactorCodeTitle), findsOneWidget);
      expect(h.read(sessionProvider), SessionStatus.signedOut);
      await tester.enterText(find.byType(TextField).first, '481902');
      await tester.pumpAndSettle();
      expect(h.auth.twoFactorCalls.single.code, '481902');
      expect(h.read(sessionProvider), SessionStatus.signedIn);
      expect(find.text(l10n.twoFactorCodeTitle), findsNothing);
    },
  );
}
