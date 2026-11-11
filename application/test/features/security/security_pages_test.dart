import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/fakes.dart';
import '../../helpers/pump_app.dart';
import '../../helpers/security_fakes.dart';
import '../../helpers/wallet_fakes.dart';

Finder key(String value) => find.byKey(ValueKey(value));

void main() {
  for (final lang in ['en', 'ar']) {
    final l10n = l10nFor(lang);

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      bool twoFactorOn = false,
      BiometricSupport biometricSupport = BiometricSupport.available,
      bool biometricEnabled = false,
      void Function(TestHarness h)? arrange,
      Size size = const Size(390, 844),
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: true,
        size: size,
        biometricSupport: biometricSupport,
        biometricEnabled: biometricEnabled,
        config: [Ok(dollarConfig())],
        arrange: (h) {
          when(h.account.profile).thenAnswer(
            (_) async =>
                Ok(fixtureProfile().copyWith(twoFactorEnabled: twoFactorOn)),
          );
          arrange?.call(h);
        },
        settle: false,
      );
      await tester.pumpAndSettle();
      h.read(appRouterProvider).go(Routes.account.path);
      await tester.pumpAndSettle();
      if (location != Routes.account.path) {
        await tester.pumpAndSettle();
        unawaited(h.read(appRouterProvider).push<void>(location));
        await tester.pumpAndSettle();
      }
      return h;
    }

    String path(TestHarness h) => h.read(appRouterProvider).state.uri.path;

    Future<void> tapKey(WidgetTester tester, String value) async {
      await tester.ensureVisible(key(value));
      await tester.pumpAndSettle();
      await tester.tap(key(value));
      await tester.pumpAndSettle();
    }

    group('security ($lang)', () {
      testWidgets('two-step off, password, biometric switch, devices, note', (
        tester,
      ) async {
        await at(tester, Routes.security.path);
        expect(find.text(l10n.twoFactorOffSubtitle), findsOneWidget);
        expect(find.text(l10n.twoFactorTurnOn), findsOneWidget);
        expect(find.text(l10n.changePasswordTitle), findsOneWidget);
        expect(find.text(l10n.biometricSettingTitle), findsOneWidget);
        expect(find.text(l10n.devicesCount(2)), findsOneWidget);
        expect(find.text(l10n.securityRevealNoticeTitle), findsOneWidget);
        expect(key('security-sign-out-everywhere'), findsOneWidget);
      });

      testWidgets('the biometric switch off deletes the server and local key', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.security.path,
          biometricEnabled: true,
        );
        await tester.tap(find.text(l10n.biometricSettingTitle));
        await tester.pumpAndSettle();
        expect(h.biometricServer.deleteCalls, 1);
        expect(h.keys.deleteCalls, 1);
        expect(find.text(l10n.biometricDisabled), findsOneWidget);
      });

      testWidgets('turn on: password, key and QR, code, recovery codes', (
        tester,
      ) async {
        final h = await at(tester, Routes.security.path);
        await tapKey(tester, 'security-two-factor');
        expect(path(h), Routes.twoFactorSetup.path);
        expect(find.text(l10n.twoFactorPasswordBody), findsOneWidget);

        h.twoFactor.enables = [
          Err(validation({'current_password': 'The password is incorrect.'})),
          Ok(canaryEnrollment()),
        ];
        await tester.enterText(key('two-factor-password'), 'wrong');
        await tapKey(tester, 'two-factor-start');
        expect(find.text('The password is incorrect.'), findsOneWidget);
        expect(h.security.secureCalls, isEmpty);

        await tester.enterText(key('two-factor-password'), 'right');
        await tapKey(tester, 'two-factor-start');
        expect(key('two-factor-qr'), findsOneWidget);
        expect(find.text('JBSW Y3DP EHPK 3PXP CANA RY00'), findsOneWidget);
        expect(h.security.secureCalls, [true]);

        await tapKey(tester, 'two-factor-copy-key');
        expect(h.clipboard.copies, 1);
        expect(find.text(l10n.twoFactorKeyCopied), findsOneWidget);

        await tapKey(tester, 'two-factor-confirm');
        expect(find.text(l10n.twoFactorCodeInvalid), findsOneWidget);

        await tester.enterText(find.byType(TextField).last, '123456');
        await tester.pumpAndSettle();
        expect(h.twoFactor.codes, ['123456']);
        expect(find.text(l10n.recoveryCodesTitle), findsOneWidget);
        for (final code in canaryRecoveryCodes) {
          expect(find.text(code), findsOneWidget);
        }
        expect(h.security.secureCalls, [true]);

        await tapKey(tester, 'recovery-codes-copy');
        expect(h.clipboard.copies, 2);

        // Leaving asks first.
        await tester.tap(find.byTooltip(l10n.actionClose).first);
        await tester.pumpAndSettle();
        expect(find.text(l10n.recoveryCodesLeaveTitle), findsOneWidget);
        await tester.tap(find.text(l10n.actionCancel));
        await tester.pumpAndSettle();
        expect(find.text(l10n.recoveryCodesTitle), findsOneWidget);

        await tapKey(tester, 'recovery-codes-saved');
        expect(path(h), Routes.security.path);
        expect(find.text(l10n.twoFactorEnabled), findsOneWidget);
        expect(find.text(l10n.twoFactorOnSubtitle), findsOneWidget);
        expect(h.security.secureCalls, [true, false]);
        expect(find.text(canaryRecoveryCodes.first), findsNothing);
      });

      testWidgets('a wrong code keeps the key with the server message', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.twoFactorSetup.path,
          arrange: (h) => h.twoFactor.confirms = [
            const Err(
              DomainFailure(
                'two_factor_invalid',
                message: 'The authentication code is invalid.',
              ),
            ),
          ],
        );
        await tester.enterText(key('two-factor-password'), 'right');
        await tapKey(tester, 'two-factor-start');
        await tester.enterText(find.byType(TextField).last, '000000');
        await tester.pumpAndSettle();
        expect(
          find.text('The authentication code is invalid.'),
          findsOneWidget,
        );
        expect(key('two-factor-qr'), findsOneWidget);
        expect(h.twoFactor.codes, ['000000']);
      });

      testWidgets('turn off asks for the password', (tester) async {
        final h = await at(tester, Routes.security.path, twoFactorOn: true);
        expect(find.text(l10n.twoFactorOnSubtitle), findsOneWidget);
        h.twoFactor.disables = [
          Err(validation({'current_password': 'The password is incorrect.'})),
          const Ok(null),
        ];
        await tapKey(tester, 'security-two-factor');
        expect(find.text(l10n.twoFactorDisableTitle), findsOneWidget);
        await tester.enterText(key('password-action-field'), 'wrong');
        await tapKey(tester, 'password-action-submit');
        expect(find.text('The password is incorrect.'), findsOneWidget);
        await tester.enterText(key('password-action-field'), 'right');
        await tapKey(tester, 'password-action-submit');
        expect(find.text(l10n.twoFactorDisabled), findsOneWidget);
        expect(find.text(l10n.twoFactorOffSubtitle), findsOneWidget);
        expect(h.twoFactor.passwords, ['wrong', 'right']);
      });

      testWidgets('sign out everywhere', (tester) async {
        final h = await at(tester, Routes.security.path);
        await tapKey(tester, 'security-sign-out-everywhere');
        expect(find.text(l10n.signOutEverywhereTitle), findsOneWidget);
        await tester.tap(
          find.widgetWithText(FilledButton, l10n.signOutEverywhere),
        );
        await tester.pumpAndSettle();
        expect(h.auth.logoutAllCalls, 1);
        expect(h.read(sessionProvider), SessionStatus.signedOut);
      });

      testWidgets('screen-reader labels and tap targets', (tester) async {
        final handle = tester.ensureSemantics();
        await at(tester, Routes.security.path);
        await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
        await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
        handle.dispose();
      });
    });

    group('devices ($lang)', () {
      testWidgets('current first; sign a device out after confirming', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.devices.path,
          arrange: (h) =>
              when(() => h.account.revokeDevice(any()))
                  .thenAnswer((_) async => const Ok(null)),
        );
        expect(find.text('Pixel 9'), findsOneWidget);
        expect(find.text('Tablet'), findsOneWidget);
        expect(find.text(l10n.deviceCurrentBadge), findsOneWidget);
        expect(find.text(l10n.devicesWebNotListed), findsOneWidget);
        expect(
          tester.getTopLeft(find.text('Pixel 9')).dy,
          lessThan(tester.getTopLeft(find.text('Tablet')).dy),
        );

        await tester.tap(find.text(l10n.deviceSignOut));
        await tester.pumpAndSettle();
        expect(find.text(l10n.deviceRevokeTitle('Tablet')), findsOneWidget);
        await tester.tap(
          find.widgetWithText(FilledButton, l10n.deviceRevokeAction),
        );
        await tester.pumpAndSettle();
        verify(() => h.account.revokeDevice('01JF00000000000000000TABLET'))
            .called(1);
        expect(find.text(l10n.deviceSignedOut('Tablet')), findsOneWidget);
        expect(find.text('Tablet'), findsNothing);
        expect(find.text(l10n.devicesOnlyThis), findsOneWidget);
        expect(key('devices-sign-out-others'), findsNothing);
      });

      testWidgets('sign out of all other devices with the password', (
        tester,
      ) async {
        final h = await at(tester, Routes.devices.path);
        when(
          () => h.account.signOutOtherDevices(
            currentPassword: any(named: 'currentPassword'),
          ),
        ).thenAnswer(
          (_) async => Err(
            validation({'current_password': 'The password is incorrect.'}),
          ),
        );
        await tapKey(tester, 'devices-sign-out-others');
        expect(find.text(l10n.signOutOthersTitle), findsOneWidget);
        await tester.enterText(key('password-action-field'), 'wrong');
        await tapKey(tester, 'password-action-submit');
        expect(find.text('The password is incorrect.'), findsOneWidget);

        when(
          () => h.account.signOutOtherDevices(
            currentPassword: any(named: 'currentPassword'),
          ),
        ).thenAnswer(
          (_) async =>
              const Ok(PasswordChangeResult(mobileFamilies: 1, webSessions: 0)),
        );
        when(h.account.devices).thenAnswer(
          (_) async => Ok(
            fixtureDevices().copyWith(devices: [fixtureDevices().devices.last]),
          ),
        );
        await tester.enterText(key('password-action-field'), 'right');
        await tapKey(tester, 'password-action-submit');
        expect(find.text(l10n.signOutOthersDone), findsOneWidget);
        expect(find.text('Tablet'), findsNothing);
      });

      testWidgets('error with retry', (tester) async {
        final h = await at(
          tester,
          Routes.devices.path,
          arrange: (h) => when(h.account.devices).thenAnswer(
            (_) async =>
                const Err(ServerFailure(statusCode: 500, requestId: 'req-7')),
          ),
        );
        expect(find.text(l10n.errorServerBody('req-7')), findsOneWidget);
        when(h.account.devices).thenAnswer((_) async => Ok(fixtureDevices()));
        await tester.tap(find.text(l10n.actionTryAgain));
        await tester.pumpAndSettle();
        expect(find.text('Tablet'), findsOneWidget);
      });

      testWidgets('screen-reader labels and tap targets', (tester) async {
        final handle = tester.ensureSemantics();
        await at(tester, Routes.devices.path);
        expect(
          find.bySemanticsLabel(l10n.deviceSignOutSemantics('Tablet')),
          findsOneWidget,
        );
        await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
        await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
        handle.dispose();
      });
    });
  }
}
