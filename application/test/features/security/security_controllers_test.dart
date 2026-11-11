import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/account/presentation/controllers/account_controllers.dart';
import 'package:application/features/security/presentation/controllers/devices_controller.dart';
import 'package:application/features/security/presentation/controllers/two_factor_controllers.dart';
import 'package:flutter_riverpod/misc.dart' show ProviderListenable;
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import '../../helpers/fakes.dart';
import '../../helpers/pump_app.dart';
import '../../helpers/security_fakes.dart';

Future<void> settle() async {
  for (var i = 0; i < 100; i++) {
    await Future<void>.delayed(Duration.zero);
  }
}

void keep<T>(TestHarness h, ProviderListenable<T> provider) {
  final sub = h.container.listen(provider, (_, _) {});
  addTearDown(sub.close);
}

void main() {
  TestHarness harness({bool signedIn = true}) {
    final h = buildHarness(signedIn: signedIn);
    addTearDown(h.dispose);
    return h;
  }

  group('turn on two-step verification', () {
    TwoFactorSetupState stateOf(TestHarness h) =>
        h.read(twoFactorSetupControllerProvider);
    TwoFactorSetupController controller(TestHarness h) =>
        h.read(twoFactorSetupControllerProvider.notifier);

    Future<TestHarness> ready() async {
      final h = harness();
      keep(h, accountProfileControllerProvider);
      keep(h, accountHeaderProvider);
      keep(h, twoFactorSetupControllerProvider);
      await settle();
      return h;
    }

    test('enable, confirm, recovery codes once, then finish', () async {
      final h = await ready();
      expect(stateOf(h).phase, TwoFactorPhase.password);
      expect(h.security.secureCalls, isEmpty);

      await controller(h).start('my-password');
      expect(h.twoFactor.passwords, ['my-password']);
      var state = stateOf(h);
      expect(state.phase, TwoFactorPhase.scan);
      expect(state.qr, isNotNull);
      expect(state.groupedSecret, 'JBSW Y3DP EHPK 3PXP CANA RY00');
      // The key is on screen: screen protection is on.
      expect(h.security.secureCalls, [true]);

      await controller(h).copySecret();
      expect(h.clipboard.copies, 1);

      await controller(h).confirm('12345');
      expect(stateOf(h).codeProblem, isTrue);
      expect(h.twoFactor.codes, isEmpty);

      await controller(h).confirm('123456');
      expect(h.twoFactor.codes, ['123456']);
      state = stateOf(h);
      expect(state.phase, TwoFactorPhase.recoveryCodes);
      expect(state.recoveryCodes!.codes, canaryRecoveryCodes);
      // The key left memory once confirmed; protection stays for the codes.
      expect(state.enrollment, isNull);
      expect(h.security.secureCalls, [true]);
      expect(h.read(accountHeaderProvider)!.twoFactorEnabled, isTrue);

      await controller(h).copyRecoveryCodes();
      expect(h.clipboard.copies, 2);

      controller(h).finish();
      expect(stateOf(h).phase, TwoFactorPhase.done);
      expect(stateOf(h).recoveryCodes, isNull);
      expect(h.security.secureCalls, [true, false]);
    });

    test('the state never prints the key or the codes', () async {
      final h = await ready();
      await controller(h).start('my-password');
      expect('${stateOf(h)}', isNot(contains(canarySecret)));
      expect('${stateOf(h)}', isNot(contains('my-password')));
      await controller(h).confirm('123456');
      for (final code in canaryRecoveryCodes) {
        expect('${stateOf(h)}', isNot(contains(code)));
      }
      expect('${stateOf(h).recoveryCodes}', isNot(contains('CANARY')));
    });

    test('a wrong password stays on the password step', () async {
      final h = await ready();
      h.twoFactor.enables = [
        Err(validation({'current_password': 'The password is incorrect.'})),
      ];
      await controller(h).start('wrong');
      final state = stateOf(h);
      expect(state.phase, TwoFactorPhase.password);
      expect(
        (state.failure! as ValidationFailure).firstErrorFor('current_password'),
        'The password is incorrect.',
      );
      expect(h.security.secureCalls, isEmpty);
      controller(h).edited();
      expect(stateOf(h).failure, isNull);
    });

    test('a wrong code keeps the key and says so', () async {
      final h = await ready();
      h.twoFactor.confirms = [
        const Err(
          DomainFailure(
            'two_factor_invalid',
            message: 'The authentication code is invalid.',
          ),
        ),
      ];
      await controller(h).start('my-password');
      await controller(h).confirm('000000');
      final state = stateOf(h);
      expect(state.phase, TwoFactorPhase.scan);
      expect(state.enrollment, isNotNull);
      expect((state.failure! as DomainFailure).code, 'two_factor_invalid');
      expect(h.read(accountHeaderProvider)!.twoFactorEnabled, isFalse);
    });

    test('already on (409) marks the account and shows no key', () async {
      final h = await ready();
      h.twoFactor.enables = [
        const Err(ConflictFailure('two_factor_already_enabled')),
      ];
      await controller(h).start('my-password');
      expect(stateOf(h).phase, TwoFactorPhase.alreadyOn);
      expect(h.read(accountHeaderProvider)!.twoFactorEnabled, isTrue);
      expect(h.security.secureCalls, isEmpty);
    });

    test(
      'leaving the screen turns screen protection off and drops the key',
      () async {
        final h = harness();
        final sub = h.container.listen(
          twoFactorSetupControllerProvider,
          (_, _) {},
        );
        await h.read(twoFactorSetupControllerProvider.notifier).start('pw');
        expect(h.security.secureCalls, [true]);
        sub.close();
        await settle();
        expect(h.read(twoFactorSetupControllerProvider.exists), isFalse);
        expect(h.security.secureCalls, [true, false]);
      },
    );
  });

  group('turn off two-step verification', () {
    test('needs the password; success marks the account', () async {
      final h = harness();
      keep(h, accountProfileControllerProvider);
      keep(h, accountHeaderProvider);
      keep(h, twoFactorDisableControllerProvider);
      await settle();
      h
          .read(accountProfileControllerProvider.notifier)
          .setTwoFactor(enabled: true);
      final controller = h.read(twoFactorDisableControllerProvider.notifier);

      h.twoFactor.disables = [
        Err(validation({'current_password': 'The password is incorrect.'})),
        const Ok(null),
      ];
      final failure = await controller.disable('wrong');
      expect(failure, isA<ValidationFailure>());
      expect(h.read(accountHeaderProvider)!.twoFactorEnabled, isTrue);

      expect(await controller.disable('right'), isNull);
      expect(h.twoFactor.passwords, ['wrong', 'right']);
      expect(h.read(accountHeaderProvider)!.twoFactorEnabled, isFalse);
    });
  });

  group('devices', () {
    DevicesState stateOf(TestHarness h) => h.read(devicesControllerProvider);
    DevicesController controller(TestHarness h) =>
        h.read(devicesControllerProvider.notifier);

    Future<TestHarness> ready() async {
      final h = harness();
      keep(h, devicesControllerProvider);
      await settle();
      return h;
    }

    test('this device first, then the others by last use', () async {
      final h = await ready();
      final names = [
        for (final device in stateOf(h).devices) device.deviceName,
      ];
      expect(names, ['Pixel 9', 'Tablet']);
      expect(stateOf(h).hasOthers, isTrue);
    });

    test(
      'signing a device out drops it; this device is never offered',
      () async {
        final h = await ready();
        when(() => h.account.revokeDevice(any()))
            .thenAnswer((_) async => const Ok(null));
        final current = stateOf(h).devices.first;
        expect(await controller(h).revoke(current), isNull);
        verifyNever(() => h.account.revokeDevice(any()));

        final tablet = stateOf(h).devices.last;
        expect(await controller(h).revoke(tablet), isA<DeviceSignedOut>());
        verify(() => h.account.revokeDevice(tablet.id)).called(1);
        expect(stateOf(h).devices.map((d) => d.deviceName), ['Pixel 9']);
        expect(stateOf(h).hasOthers, isFalse);
        expect(stateOf(h).revoking, isNull);
      },
    );

    test(
      'a device already gone (404) is dropped and the list reloaded',
      () async {
        final h = await ready();
        when(() => h.account.revokeDevice(any()))
            .thenAnswer((_) async => const Err(NotFoundFailure()));
        final tablet = stateOf(h).devices.last;
        expect(await controller(h).revoke(tablet), isA<DeviceSignedOut>());
        await settle();
        verify(h.account.devices).called(2);
      },
    );

    test('another failure keeps the device', () async {
      final h = await ready();
      when(
        () => h.account.revokeDevice(any()),
      ).thenAnswer((_) async => const Err(ConflictFailure('current_device')));
      final outcome = await controller(h).revoke(stateOf(h).devices.last);
      expect(outcome, isA<RevokeFailed>());
      expect(stateOf(h).devices, hasLength(2));
    });

    test('sign out of all other devices needs the current password', () async {
      final h = await ready();
      when(
        () => h.account.signOutOtherDevices(
          currentPassword: any(named: 'currentPassword'),
        ),
      ).thenAnswer(
        (_) async =>
            Err(validation({'current_password': 'The password is incorrect.'})),
      );
      final failure = await controller(h).signOutOthers('wrong');
      expect(
        (failure! as ValidationFailure).firstErrorFor('current_password'),
        'The password is incorrect.',
      );
      expect(stateOf(h).devices, hasLength(2));

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
      expect(await controller(h).signOutOthers('right'), isNull);
      verify(() => h.account.signOutOtherDevices(currentPassword: 'right'))
          .called(1);
      expect(stateOf(h).devices.map((d) => d.deviceName), ['Pixel 9']);
      await settle();
      expect(stateOf(h).hasOthers, isFalse);
    });

    test('a failed load stays failed until reload', () async {
      final h = harness();
      when(h.account.devices)
          .thenAnswer((_) async => const Err(NetworkFailure()));
      keep(h, devicesControllerProvider);
      await settle();
      expect(stateOf(h).content, isA<Failed<DeviceList>>());
      when(h.account.devices).thenAnswer((_) async => Ok(fixtureDevices()));
      await controller(h).reload();
      expect(stateOf(h).devices, hasLength(2));
    });

    test('signed out loads nothing', () async {
      final h = harness(signedIn: false);
      keep(h, devicesControllerProvider);
      await settle();
      expect(h.read(sessionProvider), SessionStatus.signedOut);
      verifyNever(h.account.devices);
      expect(stateOf(h).devices, isEmpty);
    });
  });
}
