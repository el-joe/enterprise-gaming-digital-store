import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/features/auth/domain/entities/auth_entities.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/auth/presentation/controllers/biometric_controllers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/fakes.dart';
import '../helpers/pump_app.dart';

/// Key M-A10 states in LTR (en) and RTL (ar), compared with the approved M-A8 frames by review.
void main() {
  setUpAll(loadAppFonts);

  for (final lang in ['en', 'ar']) {
    final dir = lang == 'ar' ? 'rtl' : 'ltr';
    final l10n = l10nFor(lang);

    Future<void> snap(WidgetTester tester, String name) async {
      await precacheImages(tester);
      await expectLater(
        find.byType(MaterialApp),
        matchesGoldenFile('m_a10/${name}_$dir.png'),
      );
    }

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      bool onboarded = true,
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        onboarded: onboarded,
      );
      h.read(appRouterProvider).go(location);
      await tester.pumpAndSettle();
      return h;
    }

    Finder button(String label) => find.ancestor(
      of: find.text(label),
      matching: find.byWidgetPredicate((w) => w is ButtonStyleButton),
    );

    testWidgets('01.2 splash offline ($dir)', (tester) async {
      await pumpHarness(
        tester,
        languageCode: lang,
        config: [const Err(NetworkFailure())],
      );
      await snap(tester, 'splash_offline');
    });

    testWidgets('01.3 force update ($dir)', (tester) async {
      await pumpHarness(
        tester,
        languageCode: lang,
        config: [Ok(fixtureAppConfig(updateRequired: true))],
      );
      await snap(tester, 'update_required');
    });

    testWidgets('01.4 maintenance ($dir)', (tester) async {
      await pumpHarness(
        tester,
        languageCode: lang,
        config: [Ok(fixtureAppConfig(maintenance: true))],
      );
      await snap(tester, 'maintenance');
    });

    testWidgets('01.5 onboarding language ($dir)', (tester) async {
      await pumpHarness(tester, languageCode: lang, onboarded: false);
      await snap(tester, 'onboarding_language');
    });

    testWidgets('01.6 onboarding currency ($dir)', (tester) async {
      await pumpHarness(tester, languageCode: lang, onboarded: false);
      await tester.tap(find.text(l10n.actionContinue));
      await tester.pumpAndSettle();
      await tester.tap(find.text('USD'));
      await tester.pumpAndSettle();
      await snap(tester, 'onboarding_currency');
    });

    testWidgets('02.2 login rate limited with field error ($dir)', (
      tester,
    ) async {
      final h = await at(tester, Routes.login.path);
      h.auth.loginResult = Err(
        validation({
          'password': lang == 'ar'
              ? 'بيانات الدخول غير صحيحة.'
              : 'These credentials do not match our records.',
        }),
      );
      await tester.enterText(
        find.byType(TextField).at(0),
        'sara.k@example.com',
      );
      await tester.enterText(find.byType(TextField).at(1), 'password');
      await tester.tap(button(l10n.signInAction));
      await tester.pumpAndSettle();
      h.auth.loginResult = const Err(
        RateLimitedFailure(retryAfter: Duration(seconds: 58)),
      );
      await tester.tap(button(l10n.signInAction));
      await tester.pumpAndSettle();
      FocusManager.instance.primaryFocus?.unfocus();
      await tester.pumpAndSettle();
      await snap(tester, 'login_rate_limited');
    });

    testWidgets('02.3 two-factor code ($dir)', (tester) async {
      final h = await at(tester, Routes.login.path);
      h.auth.loginResult = Ok(
        LoginOutcome.twoFactorRequired(
          challengeToken: 'challenge',
          expiresAt: h.now.value.add(const Duration(minutes: 4, seconds: 12)),
        ),
      );
      await tester.enterText(
        find.byType(TextField).at(0),
        'sara.k@example.com',
      );
      await tester.enterText(find.byType(TextField).at(1), 'password');
      await tester.tap(button(l10n.signInAction));
      await tester.pumpAndSettle();
      await tester.enterText(find.byType(TextField).first, '481');
      await tester.pumpAndSettle();
      await snap(tester, 'two_factor_code');
    });

    testWidgets('02.6 register errors ($dir)', (tester) async {
      final h = await at(tester, Routes.register.path);
      h.auth.registerResult = Err(
        validation({
          'email': lang == 'ar'
              ? 'البريد الإلكتروني مستخدم بالفعل.'
              : 'The email has already been taken.',
          'password': lang == 'ar'
              ? 'يجب ألا تقل كلمة المرور عن 8 أحرف.'
              : 'The password must be at least 8 characters.',
        }),
      );
      final fields = find.byType(TextField);
      await tester.enterText(fields.at(0), 'Sara Khalil');
      await tester.enterText(fields.at(1), 'sara.k@example.com');
      await tester.enterText(fields.at(2), 'pass');
      await tester.enterText(fields.at(3), 'pass');
      await tester.tap(find.byType(Checkbox));
      await tester.tap(button(l10n.registerTitle));
      await tester.pumpAndSettle();
      FocusManager.instance.primaryFocus?.unfocus();
      await tester.pumpAndSettle();
      await snap(tester, 'register_errors');
    });

    testWidgets('02.7 verify email ($dir)', (tester) async {
      final h = await pumpHarness(tester, languageCode: lang, signedIn: true);
      h.auth.meResult = Ok(testUser.copyWith(emailVerified: false));
      h.read(appRouterProvider).go(Routes.verifyEmail.path);
      await tester.pumpAndSettle();
      await snap(tester, 'verify_email');
    });

    testWidgets('02.8 forgot password ($dir)', (tester) async {
      await at(tester, Routes.forgotPassword.path);
      await tester.tap(find.text(l10n.openResetPage));
      await tester.pumpAndSettle();
      await snap(tester, 'forgot_password');
    });

    testWidgets('biometric offer ($dir)', (tester) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: true,
        biometricSupport: BiometricSupport.available,
      );
      h.read(appRouterProvider).go(Routes.biometricOffer.path);
      await tester.pumpAndSettle();
      await snap(tester, 'biometric_offer');
    });

    testWidgets('app lock, password fallback ($dir)', (tester) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: true,
        biometricEnabled: true,
        biometricSupport: BiometricSupport.available,
      );
      h.keys.signResults.add(const BiometricFailed(BiometricIssue.invalidated));
      await h.read(appLockProvider.notifier).lockAtLaunch();
      await tester.pumpAndSettle();
      await snap(tester, 'app_lock_password');
    });

    testWidgets('rooted device sheet ($dir)', (tester) async {
      await pumpHarness(tester, languageCode: lang, integrity: 'compromised');
      await snap(tester, 'integrity_sheet');
    });
  }
}
