import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/features/auth/domain/entities/auth_entities.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/auth/presentation/controllers/biometric_controllers.dart';
import 'package:application/features/home/presentation/pages/home_page.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import '../../helpers/fakes.dart';
import '../../helpers/pump_app.dart';

Future<void> _go(WidgetTester tester, TestHarness h, String location) async {
  h.read(appRouterProvider).go(location);
  await tester.pumpAndSettle();
}

Finder _button(String label) => find.ancestor(
  of: find.text(label),
  matching: find.byWidgetPredicate((w) => w is ButtonStyleButton),
);

void main() {
  for (final lang in ['en', 'ar']) {
    final l10n = l10nFor(lang);
    final rtl = lang == 'ar';

    group('sign in ($lang)', () {
      testWidgets('signs in and returns to the protected location', (
        tester,
      ) async {
        final h = await pumpHarness(tester, languageCode: lang);
        await _go(tester, h, '/login?from=%2Fverify-email');
        expect(find.text(l10n.loginSubtitle), findsOneWidget);
        expect(
          Directionality.of(tester.element(find.text(l10n.loginSubtitle))),
          rtl ? TextDirection.rtl : TextDirection.ltr,
        );
        await tester.enterText(
          find.byType(TextField).at(0),
          'sara.k@example.com',
        );
        await tester.enterText(find.byType(TextField).at(1), 'secret');
        await tester.tap(_button(l10n.signInAction));
        await tester.pumpAndSettle();
        expect(h.read(sessionProvider), SessionStatus.signedIn);
        expect(find.text(l10n.verifyEmailTitle), findsOneWidget);
      });

      testWidgets('server field errors appear beside the field', (
        tester,
      ) async {
        final h = await pumpHarness(tester, languageCode: lang);
        h.auth.loginResult = Err(validation({'email': 'Server says no.'}));
        await _go(tester, h, Routes.login.path);
        await tester.enterText(
          find.byType(TextField).at(0),
          'sara.k@example.com',
        );
        await tester.enterText(find.byType(TextField).at(1), 'wrong');
        await tester.tap(_button(l10n.signInAction));
        await tester.pumpAndSettle();
        expect(find.text('Server says no.'), findsOneWidget);
      });

      testWidgets('local checks before any request', (tester) async {
        final h = await pumpHarness(tester, languageCode: lang);
        await _go(tester, h, Routes.login.path);
        await tester.tap(_button(l10n.signInAction));
        await tester.pumpAndSettle();
        expect(find.text(l10n.fieldRequired), findsNWidgets(2));
        expect(h.auth.loginCalls, isEmpty);
      });

      testWidgets(
        '429 shows the countdown and disables sign-in until it ends',
        (tester) async {
          final h = await pumpHarness(tester, languageCode: lang);
          h.auth.loginResult = const Err(
            RateLimitedFailure(retryAfter: Duration(seconds: 58)),
          );
          await _go(tester, h, Routes.login.path);
          await tester.enterText(
            find.byType(TextField).at(0),
            'sara.k@example.com',
          );
          await tester.enterText(find.byType(TextField).at(1), 'wrong');
          await tester.tap(_button(l10n.signInAction));
          await tester.pumpAndSettle();
          expect(find.text(l10n.rateLimitedTitle), findsOneWidget);
          expect(find.text(l10n.rateLimitedBody(58)), findsOneWidget);
          final disabled = _button(l10n.tryAgainIn('0:58'));
          expect(disabled, findsOneWidget);
          expect(tester.widget<ButtonStyleButton>(disabled).onPressed, isNull);

          h.now.value = h.now.value.add(const Duration(seconds: 10));
          await tester.pump(const Duration(seconds: 1));
          expect(find.text(l10n.tryAgainIn('0:48')), findsOneWidget);

          h.now.value = h.now.value.add(const Duration(seconds: 50));
          await tester.pump(const Duration(seconds: 1));
          await tester.pumpAndSettle();
          expect(find.text(l10n.rateLimitedTitle), findsNothing);
          expect(_button(l10n.signInAction), findsOneWidget);
        },
      );

      testWidgets('forgot password opens the web reset page', (tester) async {
        final h = await pumpHarness(tester, languageCode: lang);
        await _go(tester, h, Routes.login.path);
        await tester.tap(find.text(l10n.forgotPasswordLink));
        await tester.pumpAndSettle();
        expect(find.text(l10n.forgotTitle), findsOneWidget);
        await tester.tap(find.text(l10n.openResetPage));
        await tester.pumpAndSettle();
        expect(h.native.opened.single.path, '/forgot-password');
        expect(find.text(l10n.resetPageOpenedTitle), findsOneWidget);
      });
    });

    group('two-step verification ($lang)', () {
      Future<TestHarness> openChallenge(WidgetTester tester) async {
        final h = await pumpHarness(tester, languageCode: lang);
        h.auth.loginResult = Ok(
          LoginOutcome.twoFactorRequired(
            challengeToken: 'challenge',
            expiresAt: h.now.value.add(const Duration(minutes: 4, seconds: 12)),
          ),
        );
        await _go(tester, h, Routes.login.path);
        await tester.enterText(
          find.byType(TextField).at(0),
          'totp@example.com',
        );
        await tester.enterText(find.byType(TextField).at(1), 'secret');
        await tester.tap(_button(l10n.signInAction));
        await tester.pumpAndSettle();
        return h;
      }

      testWidgets('a full code signs in', (tester) async {
        final h = await openChallenge(tester);
        expect(find.text(l10n.twoFactorCodeTitle), findsOneWidget);
        expect(find.text(l10n.twoFactorExpiresIn('4:12')), findsOneWidget);
        await tester.enterText(find.byType(TextField).first, '481902');
        await tester.pumpAndSettle();
        expect(h.auth.twoFactorCalls.single.code, '481902');
        expect(h.read(sessionProvider), SessionStatus.signedIn);
        expect(find.byKey(HomeScreen.pageKey), findsOneWidget);
      });

      testWidgets('an invalid code is shown under the boxes', (tester) async {
        final h = await openChallenge(tester);
        h.auth.twoFactorResult = const Err(DomainFailure('two_factor_invalid'));
        await tester.enterText(find.byType(TextField).first, '000000');
        await tester.pumpAndSettle();
        expect(find.text(l10n.twoFactorInvalid), findsOneWidget);
      });

      testWidgets('recovery code mode and the expired challenge', (
        tester,
      ) async {
        final h = await openChallenge(tester);
        await tester.tap(find.text(l10n.useRecoveryCode));
        await tester.pumpAndSettle();
        expect(find.text(l10n.recoveryTitle), findsOneWidget);
        await tester.enterText(find.byType(TextField).first, '7HQK-2M9P');
        h.auth.twoFactorResult = const Err(DomainFailure('two_factor_expired'));
        await tester.tap(_button(l10n.verifyAction));
        await tester.pumpAndSettle();
        expect(h.auth.twoFactorCalls.single.recovery, '7HQK-2M9P');
        expect(find.text(l10n.twoFactorExpiredTitle), findsOneWidget);
        await tester.tap(_button(l10n.backToSignIn));
        await tester.pumpAndSettle();
        expect(find.text(l10n.loginSubtitle), findsOneWidget);
      });

      testWidgets('the countdown ends the challenge', (tester) async {
        final h = await openChallenge(tester);
        h.now.value = h.now.value.add(const Duration(minutes: 5));
        await tester.pump(const Duration(seconds: 1));
        await tester.pumpAndSettle();
        expect(find.text(l10n.twoFactorExpiredTitle), findsOneWidget);
      });
    });

    group('register and verify email ($lang)', () {
      testWidgets('server errors: summary and field messages', (tester) async {
        final h = await pumpHarness(tester, languageCode: lang);
        h.auth.registerResult = Err(
          validation({
            'email': 'The email has already been taken.',
            'password': 'The password must be at least 8 characters.',
          }),
        );
        await _go(tester, h, Routes.register.path);
        final fields = find.byType(TextField);
        await tester.enterText(fields.at(0), 'Sara Khalil');
        await tester.enterText(fields.at(1), 'sara.k@example.com');
        await tester.enterText(fields.at(2), 'pass');
        await tester.enterText(fields.at(3), 'pass');
        await tester.tap(find.byType(Checkbox));
        await tester.tap(_button(l10n.registerTitle));
        await tester.pumpAndSettle();
        expect(find.text(l10n.formErrorsTitle(2)), findsOneWidget);
        expect(find.text('The email has already been taken.'), findsOneWidget);
      });

      testWidgets('terms are required; success opens the verify prompt', (
        tester,
      ) async {
        final h = await pumpHarness(tester, languageCode: lang);
        when(h.account.resendVerificationEmail)
            .thenAnswer((_) async => const Ok(null));
        await _go(tester, h, Routes.register.path);
        final fields = find.byType(TextField);
        await tester.enterText(fields.at(0), 'Sara Khalil');
        await tester.enterText(fields.at(1), 'sara.k@example.com');
        await tester.enterText(fields.at(2), 'long-enough-1');
        await tester.enterText(fields.at(3), 'long-enough-1');
        await tester.tap(_button(l10n.registerTitle));
        await tester.pumpAndSettle();
        expect(find.text(l10n.termsRequired), findsOneWidget);
        expect(h.auth.registerCalls, isEmpty);

        await tester.tap(find.byType(Checkbox));
        await tester.tap(_button(l10n.registerTitle));
        await tester.pumpAndSettle();
        expect(find.text(l10n.verifyEmailTitle), findsOneWidget);
        expect(
          find.text(l10n.verifyEmailBody('sara.k@example.com')),
          findsOneWidget,
        );
        expect(find.text(l10n.resendIn('1:00')), findsOneWidget);
        final resend = _button(l10n.resendEmail);
        expect(tester.widget<ButtonStyleButton>(resend).onPressed, isNull);

        h.now.value = h.now.value.add(const Duration(seconds: 61));
        await tester.pump(const Duration(seconds: 1));
        await tester.tap(resend);
        await tester.pumpAndSettle();
        expect(find.text(l10n.verificationSent), findsOneWidget);

        h.auth.meResult = const Ok(testUser);
        await tester.tap(_button(l10n.iVerified));
        await tester.pumpAndSettle();
        expect(find.byKey(HomeScreen.pageKey), findsOneWidget);
      });
    });

    group('biometric unlock ($lang)', () {
      testWidgets('offered once after the first sign-in, then turned on', (
        tester,
      ) async {
        final h = await pumpHarness(
          tester,
          languageCode: lang,
          biometricSupport: BiometricSupport.available,
        );
        await _go(tester, h, Routes.login.path);
        await tester.enterText(
          find.byType(TextField).at(0),
          'sara.k@example.com',
        );
        await tester.enterText(find.byType(TextField).at(1), 'secret');
        await tester.tap(_button(l10n.signInAction));
        await tester.pumpAndSettle();
        expect(find.text(l10n.biometricOfferTitle), findsOneWidget);

        // The server wants a fresh password first: the sheet asks for it.
        h.biometricServer.registerResults.add(
          const Err(RecentAuthRequiredFailure()),
        );
        await tester.tap(_button(l10n.biometricTurnOn));
        // The button shows progress while the sheet is open: pump, do not settle.
        await tester.pump();
        await tester.pump(const Duration(milliseconds: 500));
        expect(find.text(l10n.passwordSheetTitle), findsOneWidget);
        await tester.enterText(find.byType(TextField).last, 'secret');
        await tester.tap(_button(l10n.confirmAction));
        await tester.pumpAndSettle();

        expect(h.biometricServer.passwords, ['secret']);
        expect(h.biometricServer.registeredKeys, hasLength(2));
        expect(find.text(l10n.biometricEnabled), findsOneWidget);
        expect(find.byKey(HomeScreen.pageKey), findsOneWidget);
        expect(await h.read(biometricUnlockSettingsProvider.future), isTrue);
      });

      testWidgets('not now continues without asking again', (tester) async {
        final h = await pumpHarness(
          tester,
          languageCode: lang,
          biometricSupport: BiometricSupport.available,
        );
        await _go(tester, h, '/login?from=%2Fverify-email');
        await tester.enterText(
          find.byType(TextField).at(0),
          'sara.k@example.com',
        );
        await tester.enterText(find.byType(TextField).at(1), 'secret');
        await tester.tap(_button(l10n.signInAction));
        await tester.pumpAndSettle();
        await tester.tap(_button(l10n.actionNotNow));
        await tester.pumpAndSettle();
        expect(find.text(l10n.verifyEmailTitle), findsOneWidget);
        expect(h.read(biometricOfferDueProvider), isFalse);
        expect(h.keys.prompts, isEmpty);
      });

      testWidgets('locks at launch; password fallback unlocks', (tester) async {
        final pumped = await pumpHarness(
          tester,
          languageCode: lang,
          signedIn: true,
          biometricEnabled: true,
          biometricSupport: BiometricSupport.available,
        );
        // Launch locked the app and the automatic prompt unlocked it with the (fake) key.
        expect(pumped.keys.signedPayloads, hasLength(1));
        expect(find.text(l10n.lockTitle), findsNothing);
        expect(find.byKey(HomeScreen.pageKey), findsOneWidget);

        // Lock again, cancel the prompt and use the password.
        pumped.keys.signResults.add(
          const BiometricFailed(BiometricIssue.cancelled),
        );
        await pumped.read(appLockProvider.notifier).lockAtLaunch();
        await tester.pumpAndSettle();
        expect(find.text(l10n.lockTitle), findsOneWidget);
        await tester.tap(find.text(l10n.lockUsePassword));
        await tester.pumpAndSettle();
        await tester.enterText(find.byType(TextField).last, 'secret');
        await tester.tap(_button(l10n.unlockAction));
        await tester.pumpAndSettle();
        expect(find.text(l10n.lockTitle), findsNothing);
        expect(pumped.biometricServer.passwords, ['secret']);
      });
    });

    testWidgets('sign out asks first, then signs out ($lang)', (tester) async {
      final h = await pumpHarness(tester, languageCode: lang, signedIn: true);
      // Sign out sits at the end of the Account hub (M-A16).
      h.read(appRouterProvider).go(Routes.account.path);
      await tester.pumpAndSettle();
      expect(find.text('sara@example.com'), findsOneWidget);
      await tester.scrollUntilVisible(
        find.byKey(const ValueKey('account-sign-out')),
        200,
      );
      // Built is not yet on screen: bring it fully into view above the tab bar.
      await tester.ensureVisible(
        find.byKey(const ValueKey('account-sign-out')),
      );
      await tester.pumpAndSettle();
      await tester.tap(find.byKey(const ValueKey('account-sign-out')));
      await tester.pumpAndSettle();
      expect(find.text(l10n.signOutConfirmTitle), findsOneWidget);
      await tester.tap(find.widgetWithText(FilledButton, l10n.signOutAction));
      await tester.pumpAndSettle();
      expect(h.auth.logoutCalls, 1);
      expect(h.read(sessionProvider), SessionStatus.signedOut);
      expect(find.text(l10n.signedOutMessage), findsOneWidget);
      expect(find.text(l10n.accountSignedOutBody), findsOneWidget);
    });
  }
}
