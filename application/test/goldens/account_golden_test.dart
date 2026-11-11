import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import '../helpers/checkout_fakes.dart';
import '../helpers/fakes.dart';
import '../helpers/pump_app.dart';
import '../helpers/security_fakes.dart';
import '../helpers/wallet_fakes.dart';

Finder key(String value) => find.byKey(ValueKey(value));

/// Key M-A16 states in LTR (en) and RTL (ar), compared with the approved M-A8 frames (17.1 to
/// 17.4, 18.1 to 18.7) by review. Canary secrets and recovery codes only.
void main() {
  setUpAll(() async {
    registerFallbackValue(const ProfileUpdate());
    await loadAppFonts();
  });

  for (final lang in ['en', 'ar']) {
    final dir = lang == 'ar' ? 'rtl' : 'ltr';
    final l10n = l10nFor(lang);

    Future<void> snap(WidgetTester tester, String name) async {
      await precacheImages(tester);
      await expectLater(
        find.byType(MaterialApp),
        matchesGoldenFile('m_a16/${name}_$dir.png'),
      );
    }

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      bool signedIn = true,
      bool twoFactorOn = false,
      void Function(TestHarness h)? arrange,
      Size size = const Size(390, 844),
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: signedIn,
        size: size,
        biometricSupport: BiometricSupport.available,
        config: [Ok(dollarConfig())],
        arrange: (h) {
          h.wallet.walletResult = Ok(dollarWallet('40.00'));
          when(h.account.profile).thenAnswer(
            (_) async => Ok(
              fixtureProfile().copyWith(
                name: lang == 'ar' ? 'سارة خليل' : 'Sara Khalil',
                email: 'sara.k@example.com',
                twoFactorEnabled: twoFactorOn,
              ),
            ),
          );
          when(h.account.devices).thenAnswer(
            (_) async => Ok(
              fixtureDevices().copyWith(
                devices: [
                  for (final device in fixtureDevices().devices)
                    device.copyWith(
                      lastUsedAt: device.current
                          ? DateTime(2026, 11, 11, 12)
                          : DateTime(2026, 11, 10, 12),
                    ),
                ],
              ),
            ),
          );
          arrange?.call(h);
        },
      );
      h.read(appRouterProvider).go(Routes.account.path);
      await tester.pumpAndSettle();
      if (location != Routes.account.path) {
        unawaited(h.read(appRouterProvider).push<void>(location));
        await tester.pumpAndSettle();
      }
      return h;
    }

    Future<void> tapKey(WidgetTester tester, String value) async {
      await tester.ensureVisible(key(value));
      await tester.pumpAndSettle();
      await tester.tap(key(value));
      await tester.pumpAndSettle();
    }

    testWidgets('17.1 account hub ($lang)', (tester) async {
      await at(tester, Routes.account.path, twoFactorOn: true);
      await snap(tester, 'account_hub');
    });

    testWidgets('17.1 account hub, signed out ($lang)', (tester) async {
      await at(tester, Routes.account.path, signedIn: false);
      await snap(tester, 'account_signed_out');
    });

    testWidgets('17.1 account hub at 360x800 ($lang)', (tester) async {
      await at(tester, Routes.account.path, size: const Size(360, 800));
      await snap(tester, 'account_hub_360');
    });

    testWidgets('17.2 profile with a phone error ($lang)', (tester) async {
      await at(
        tester,
        Routes.accountProfile.path,
        arrange: (h) => when(() => h.account.updateProfile(any())).thenAnswer(
          (_) async => Err(
            validation({
              'phone': lang == 'ar'
                  ? 'أدخل رقم هاتف صالحًا مع رمز الدولة.'
                  : 'Enter a valid phone number with country code.',
            }),
          ),
        ),
      );
      await tester.enterText(key('profile-phone'), '+20 100 123 4567');
      await tapKey(tester, 'profile-save');
      await snap(tester, 'profile_error');
    });

    testWidgets('17.2 profile, email not verified ($lang)', (tester) async {
      await at(
        tester,
        Routes.accountProfile.path,
        arrange: (h) => when(h.account.profile).thenAnswer(
          (_) async => Ok(
            fixtureProfile().copyWith(
              name: 'Sara Khalil',
              email: 'sara.k@example.com',
              emailVerified: false,
            ),
          ),
        ),
      );
      await snap(tester, 'profile_unverified');
    });

    testWidgets('17.3 settings ($lang)', (tester) async {
      await at(tester, Routes.accountSettings.path);
      await snap(tester, 'settings');
    });

    testWidgets('17.4 sign out confirm ($lang)', (tester) async {
      await at(tester, Routes.accountSettings.path);
      await tapKey(tester, 'settings-sign-out');
      await snap(tester, 'sign_out_confirm');
    });

    testWidgets('18.1 security ($lang)', (tester) async {
      await at(tester, Routes.security.path);
      await snap(tester, 'security');
    });

    testWidgets('18.2 turn on two-step verification ($lang)', (tester) async {
      await at(tester, Routes.twoFactorSetup.path);
      await tester.enterText(key('two-factor-password'), 'password');
      await tapKey(tester, 'two-factor-start');
      await tester.enterText(find.byType(TextField).last, '481');
      await tester.pumpAndSettle();
      await snap(tester, 'two_factor_scan');
    });

    testWidgets('18.3 recovery codes ($lang)', (tester) async {
      await at(tester, Routes.twoFactorSetup.path);
      await tester.enterText(key('two-factor-password'), 'password');
      await tapKey(tester, 'two-factor-start');
      await tester.enterText(find.byType(TextField).last, '481902');
      await tester.pumpAndSettle();
      await snap(tester, 'recovery_codes');
    });

    testWidgets('18.4 turn off two-step verification ($lang)', (tester) async {
      await at(tester, Routes.security.path, twoFactorOn: true);
      await tapKey(tester, 'security-two-factor');
      await snap(tester, 'two_factor_off_sheet');
    });

    testWidgets('18.5 devices ($lang)', (tester) async {
      await at(tester, Routes.devices.path);
      await snap(tester, 'devices');
    });

    testWidgets('18.6 sign a device out ($lang)', (tester) async {
      await at(tester, Routes.devices.path);
      await tester.tap(find.text(l10n.deviceSignOut));
      await tester.pumpAndSettle();
      await snap(tester, 'device_revoke');
    });

    testWidgets('18.7 change password, wrong current password ($lang)', (
      tester,
    ) async {
      await at(
        tester,
        Routes.changePassword.path,
        arrange: (h) =>
            when(
              () => h.account.changePassword(
                currentPassword: any(named: 'currentPassword'),
                password: any(named: 'password'),
                passwordConfirmation: any(named: 'passwordConfirmation'),
              ),
            ).thenAnswer(
              (_) async => Err(
                validation({
                  'current_password': lang == 'ar'
                      ? 'كلمة المرور غير صحيحة.'
                      : 'The password is incorrect.',
                }),
              ),
            ),
      );
      await tester.enterText(key('password-current'), 'wrongpass');
      await tester.enterText(key('password-new'), 'new-passphrase-1');
      await tester.enterText(key('password-confirmation'), 'new-passphrase-1');
      await tapKey(tester, 'password-submit');
      await snap(tester, 'change_password_error');
    });

    testWidgets('devices, loading failed ($lang)', (tester) async {
      await at(
        tester,
        Routes.devices.path,
        arrange: (h) => when(h.account.devices).thenAnswer(
          (_) async =>
              const Err(ServerFailure(statusCode: 500, requestId: 'req-1')),
        ),
      );
      await snap(tester, 'devices_error');
    });
  }
}
