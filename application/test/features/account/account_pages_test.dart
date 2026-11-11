import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/fakes.dart';
import '../../helpers/pump_app.dart';
import '../../helpers/wallet_fakes.dart';

Finder key(String value) => find.byKey(ValueKey(value));

void main() {
  setUpAll(() => registerFallbackValue(const ProfileUpdate()));

  for (final lang in ['en', 'ar']) {
    final l10n = l10nFor(lang);

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      bool signedIn = true,
      AppConfig? config,
      BiometricSupport biometricSupport = BiometricSupport.unavailable,
      bool biometricEnabled = false,
      void Function(TestHarness h)? arrange,
      Size size = const Size(390, 844),
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: signedIn,
        size: size,
        biometricSupport: biometricSupport,
        biometricEnabled: biometricEnabled,
        config: [Ok(config ?? dollarConfig())],
        arrange: (h) {
          h.wallet.walletResult = Ok(dollarWallet('40.00'));
          when(() => h.account.updateProfile(any()))
              .thenAnswer((_) async => Ok(fixtureProfile()));
          arrange?.call(h);
        },
        settle: false,
      );
      await tester.pumpAndSettle();
      h.read(appRouterProvider).go(location);
      await tester.pumpAndSettle();
      return h;
    }

    String path(TestHarness h) => h.read(appRouterProvider).state.uri.path;

    Future<void> tapKey(WidgetTester tester, String value) async {
      await tester.ensureVisible(key(value));
      await tester.pumpAndSettle();
      await tester.tap(key(value));
      await tester.pumpAndSettle();
    }

    group('account hub ($lang)', () {
      testWidgets('signed in: header, rows and the wallet balance', (
        tester,
      ) async {
        final h = await at(tester, Routes.account.path);
        expect(find.text('Sara Customer'), findsOneWidget);
        expect(find.text('sara@example.com'), findsOneWidget);
        expect(find.text('SC'), findsOneWidget);
        expect(find.text(l10n.accountVerifiedBadge), findsOneWidget);
        for (final row in [
          'account-orders',
          'account-wallet',
          'account-notifications',
          'account-support',
          'account-profile',
          'account-security',
          'account-settings',
        ]) {
          expect(key(row), findsOneWidget, reason: row);
        }
        expect(find.text('\u2066\$40.00\u2069'), findsOneWidget);
        expect(find.text(l10n.accountTwoFactorOff), findsOneWidget);

        await tapKey(tester, 'account-profile');
        expect(path(h), Routes.accountProfile.path);
        await tester.tap(find.byTooltip(l10n.actionBack).first);
        await tester.pumpAndSettle();

        await tapKey(tester, 'account-orders');
        expect(path(h), Routes.orders.path);
      });

      testWidgets('M-A17 rows open notifications, reviews, refunds, support', (
        tester,
      ) async {
        final h = await at(tester, Routes.account.path);
        for (final (row, location, title) in [
          (
            'account-notifications',
            Routes.accountNotifications.path,
            l10n.notificationsEmptyTitle,
          ),
          ('account-reviews', Routes.myReviews.path, l10n.myReviewsEmptyTitle),
          (
            'account-refunds',
            Routes.refundRequests.path,
            l10n.refundRequestsEmptyTitle,
          ),
          (
            'account-support',
            Routes.accountSupport.path,
            l10n.ticketsEmptyTitle,
          ),
        ]) {
          await tapKey(tester, row);
          expect(path(h), location, reason: row);
          expect(find.text(title), findsOneWidget, reason: row);
          await tester.tap(find.byTooltip(l10n.actionBack).first);
          await tester.pumpAndSettle();
          expect(path(h), Routes.account.path);
        }
      });

      testWidgets('wallet_enabled off hides the wallet row', (tester) async {
        await at(
          tester,
          Routes.account.path,
          config: dollarConfig(walletEnabled: false),
        );
        expect(key('account-wallet'), findsNothing);
        expect(key('account-orders'), findsOneWidget);
      });

      testWidgets('an unverified email shows its badge', (tester) async {
        await at(
          tester,
          Routes.account.path,
          arrange: (h) => when(h.account.profile).thenAnswer(
            (_) async => Ok(fixtureProfile().copyWith(emailVerified: false)),
          ),
        );
        expect(find.text(l10n.accountUnverifiedBadge), findsOneWidget);
      });

      testWidgets('signed out: sign in, register, guest orders and settings', (
        tester,
      ) async {
        final h = await at(tester, Routes.account.path, signedIn: false);
        expect(find.text(l10n.accountSignedOutTitle), findsOneWidget);
        expect(key('account-profile'), findsNothing);
        expect(find.text(l10n.guestAccessEntry), findsOneWidget);
        expect(key('account-settings'), findsOneWidget);
        await tapKey(tester, 'account-sign-in');
        expect(path(h), Routes.login.path);
        expect(
          h.read(appRouterProvider).state.uri.queryParameters['from'],
          Routes.account.path,
        );
      });

      testWidgets('sign out from the hub', (tester) async {
        final h = await at(tester, Routes.account.path);
        await tapKey(tester, 'account-sign-out');
        expect(find.text(l10n.signOutConfirmTitle), findsOneWidget);
        await tester.tap(find.widgetWithText(FilledButton, l10n.signOutAction));
        await tester.pumpAndSettle();
        expect(h.auth.logoutCalls, 1);
        expect(find.text(l10n.accountSignedOutTitle), findsOneWidget);
      });

      testWidgets('360 wide with a long name', (tester) async {
        await at(
          tester,
          Routes.account.path,
          size: const Size(360, 800),
          arrange: (h) => when(h.account.profile).thenAnswer(
            (_) async => Ok(
              fixtureProfile().copyWith(
                name: 'Sara Maria Customer With A Very Long Family Name',
              ),
            ),
          ),
        );
        expect(tester.takeException(), isNull);
        expect(find.text('SN'), findsOneWidget);
      });

      testWidgets('screen-reader labels and tap targets', (tester) async {
        final handle = tester.ensureSemantics();
        await at(tester, Routes.account.path);
        expect(
          find.bySemanticsLabel(
            RegExp(
              RegExp.escape(
                l10n.accountHeaderSemantics(
                  'Sara Customer',
                  'sara@example.com',
                ),
              ),
            ),
          ),
          findsOneWidget,
        );
        await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
        await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
        handle.dispose();
      });
    });

    group('settings ($lang)', () {
      testWidgets('language, currency, legal links, version and sign out', (
        tester,
      ) async {
        final h = await at(tester, Routes.accountSettings.path);
        expect(key('settings-language'), findsOneWidget);
        expect(key('settings-currency'), findsOneWidget);
        expect(key('settings-notifications'), findsOneWidget);
        // Only the links the configuration has (terms and support here).
        expect(key('settings-terms'), findsOneWidget);
        expect(key('settings-help'), findsOneWidget);
        expect(key('settings-privacy'), findsNothing);
        expect(key('settings-refund'), findsNothing);
        expect(find.text(l10n.appVersionLabel('1.0.0')), findsOneWidget);
        expect(key('settings-sign-out'), findsOneWidget);

        await tapKey(tester, 'settings-terms');
        expect(
          h.native.opened.single.toString(),
          'https://boxcards.test/pages/terms',
        );

        h.native.opens = false;
        await tapKey(tester, 'settings-help');
        expect(find.text(l10n.linkOpenFailed), findsOneWidget);
      });

      testWidgets('signed out: no notifications and no sign out', (
        tester,
      ) async {
        await at(tester, Routes.accountSettings.path, signedIn: false);
        expect(key('settings-language'), findsOneWidget);
        expect(key('settings-notifications'), findsNothing);
        expect(key('settings-sign-out'), findsNothing);
      });

      testWidgets('the language sheet saves on the profile and switches', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.accountSettings.path,
          arrange: (h) => when(() => h.account.updateProfile(any())).thenAnswer(
            (_) async => Ok(
              fixtureProfile().copyWith(locale: lang == 'en' ? 'ar' : 'en'),
            ),
          ),
        );
        await tapKey(tester, 'settings-language');
        final other = lang == 'en' ? l10n.languageArabic : l10n.languageEnglish;
        await tester.tap(find.text(other).last);
        await tester.pumpAndSettle();
        final update =
            verify(() => h.account.updateProfile(captureAny())).captured.single
                as ProfileUpdate;
        expect(update.toBody(), {'locale': lang == 'en' ? 'ar' : 'en'});
        expect(
          h.prefs.values[PreferencesStore.localeKey],
          lang == 'en' ? 'ar' : 'en',
        );
      });

      testWidgets('signed in, the display currency is saved on the profile', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.accountSettings.path,
          arrange: (h) => when(() => h.account.updateProfile(any())).thenAnswer(
            (_) async => Ok(fixtureProfile().copyWith(displayCurrency: 'EGP')),
          ),
        );
        await tapKey(tester, 'settings-currency');
        await tester.tap(find.text('EGP'));
        await tester.pump();
        await tester.tap(find.text(l10n.currencyApply));
        await tester.pumpAndSettle();
        final update =
            verify(() => h.account.updateProfile(captureAny())).captured.single
                as ProfileUpdate;
        expect(update.toBody(), {'display_currency': 'EGP'});
        expect(h.prefs.values[PreferencesStore.currencyKey], 'EGP');
        expect(find.text(l10n.currencyChanged('EGP')), findsOneWidget);
      });

      testWidgets('a refused language save keeps the sheet with the error', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.accountSettings.path,
          arrange: (h) =>
              when(() => h.account.updateProfile(any()))
                  .thenAnswer((_) async => const Err(NetworkFailure())),
        );
        await tapKey(tester, 'settings-language');
        final other = lang == 'en' ? l10n.languageArabic : l10n.languageEnglish;
        await tester.tap(find.text(other).last);
        await tester.pumpAndSettle();
        expect(find.text(l10n.failureNetwork), findsOneWidget);
        expect(h.prefs.values[PreferencesStore.localeKey], lang);
      });
    });

    group('profile ($lang)', () {
      testWidgets('fields, read-only email with badge, save', (tester) async {
        final h = await at(
          tester,
          Routes.accountProfile.path,
          arrange: (h) => when(() => h.account.updateProfile(any())).thenAnswer(
            (_) async => Ok(fixtureProfile().copyWith(name: 'Sara Khalil')),
          ),
        );
        expect(find.text('Sara Customer'), findsOneWidget);
        expect(find.text('sara@example.com'), findsOneWidget);
        expect(find.text(l10n.profileEmailHelper), findsOneWidget);
        expect(find.text(l10n.accountVerifiedBadge), findsOneWidget);
        expect(key('profile-resend'), findsNothing);

        await tester.enterText(key('profile-name'), 'Sara Khalil');
        await tapKey(tester, 'profile-save');
        expect(find.text(l10n.profileSaved), findsOneWidget);
        final update =
            verify(() => h.account.updateProfile(captureAny())).captured.single
                as ProfileUpdate;
        expect(update.toBody(), {'name': 'Sara Khalil'});
      });

      testWidgets('server errors show beside their fields', (tester) async {
        await at(
          tester,
          Routes.accountProfile.path,
          arrange: (h) => when(() => h.account.updateProfile(any())).thenAnswer(
            (_) async => Err(
              validation({
                'phone': 'Enter a valid phone number with country code.',
              }),
            ),
          ),
        );
        await tester.enterText(key('profile-phone'), '12');
        await tapKey(tester, 'profile-save');
        expect(
          find.text('Enter a valid phone number with country code.'),
          findsOneWidget,
        );
        await tester.enterText(key('profile-name'), ' ');
        await tapKey(tester, 'profile-save');
        expect(find.text(l10n.fieldRequired), findsOneWidget);
      });

      testWidgets('unverified: banner and resend with cooldown', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.accountProfile.path,
          arrange: (h) {
            when(h.account.profile).thenAnswer(
              (_) async => Ok(fixtureProfile().copyWith(emailVerified: false)),
            );
            when(h.account.resendVerificationEmail)
                .thenAnswer((_) async => const Ok(null));
          },
        );
        expect(find.text(l10n.profileUnverifiedTitle), findsOneWidget);
        expect(find.text(l10n.accountUnverifiedBadge), findsOneWidget);
        await tapKey(tester, 'profile-resend');
        expect(find.text(l10n.verificationSent), findsOneWidget);
        expect(find.text(l10n.resendIn('1:00')), findsOneWidget);
        verify(h.account.resendVerificationEmail).called(1);
      });

      testWidgets('loading, then a failure with retry', (tester) async {
        final gate = Completer<Result<Profile>>();
        final h = await pumpHarness(
          tester,
          languageCode: lang,
          signedIn: true,
          config: [Ok(dollarConfig())],
          settle: false,
          arrange: (h) =>
              when(h.account.profile).thenAnswer((_) => gate.future),
        );
        await tester.pumpAndSettle();
        h.read(appRouterProvider).go(Routes.accountProfile.path);
        await tester.pumpAndSettle();
        expect(key('profile-form-${fixtureProfile().publicId}'), findsNothing);
        expect(find.text(l10n.accountProfile), findsOneWidget);
        gate.complete(
          const Err(ServerFailure(statusCode: 500, requestId: 'req-9')),
        );
        await tester.pumpAndSettle();
        expect(find.text(l10n.errorServerBody('req-9')), findsOneWidget);
        when(h.account.profile).thenAnswer((_) async => Ok(fixtureProfile()));
        await tester.tap(find.text(l10n.actionTryAgain));
        await tester.pumpAndSettle();
        expect(key('profile-name'), findsOneWidget);
      });
    });

    group('change password ($lang)', () {
      void answer(TestHarness h, Result<PasswordChangeResult> result) => when(
        () => h.account.changePassword(
          currentPassword: any(named: 'currentPassword'),
          password: any(named: 'password'),
          passwordConfirmation: any(named: 'passwordConfirmation'),
        ),
      ).thenAnswer((_) async => result);

      testWidgets('mismatch, wrong current password, then success', (
        tester,
      ) async {
        final h = await at(tester, Routes.security.path);
        await tapKey(tester, 'security-password');
        expect(path(h), Routes.changePassword.path);
        expect(find.text(l10n.changePasswordNoticeTitle), findsOneWidget);

        await tester.enterText(key('password-current'), 'current-pass');
        await tester.enterText(key('password-new'), 'new-passphrase-1');
        await tester.enterText(key('password-confirmation'), 'other');
        await tapKey(tester, 'password-submit');
        expect(find.text(l10n.passwordMismatch), findsOneWidget);

        answer(
          h,
          Err(validation({'current_password': 'The password is incorrect.'})),
        );
        await tester.enterText(
          key('password-confirmation'),
          'new-passphrase-1',
        );
        await tapKey(tester, 'password-submit');
        expect(find.text('The password is incorrect.'), findsOneWidget);

        answer(
          h,
          const Ok(PasswordChangeResult(mobileFamilies: 1, webSessions: 0)),
        );
        await tapKey(tester, 'password-submit');
        expect(find.text(l10n.passwordChanged), findsOneWidget);
        expect(path(h), Routes.security.path);
        // Biometric unlock was off: no note.
        expect(find.text(l10n.passwordChangedBiometricTitle), findsNothing);
      });

      testWidgets('with biometric unlock on: turned off and the note shows', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.changePassword.path,
          biometricSupport: BiometricSupport.available,
          biometricEnabled: true,
        );
        answer(
          h,
          const Ok(PasswordChangeResult(mobileFamilies: 0, webSessions: 0)),
        );
        await tester.enterText(key('password-current'), 'current-pass');
        await tester.enterText(key('password-new'), 'new-passphrase-1');
        await tester.enterText(
          key('password-confirmation'),
          'new-passphrase-1',
        );
        await tapKey(tester, 'password-submit');
        expect(path(h), Routes.security.path);
        expect(find.text(l10n.passwordChangedBiometricTitle), findsOneWidget);
        expect(h.keys.hasKey, isFalse);
      });
    });
  }
}
