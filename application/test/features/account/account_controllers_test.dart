import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/account/presentation/controllers/account_controllers.dart';
import 'package:application/features/account/presentation/controllers/change_password_controller.dart';
import 'package:application/features/account/presentation/controllers/profile_controller.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/auth/presentation/controllers/biometric_controllers.dart';
import 'package:application/features/auth/presentation/controllers/session_user.dart';
import 'package:application/features/auth/presentation/controllers/sign_out_controller.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:flutter_riverpod/misc.dart' show ProviderListenable;
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/fakes.dart';
import '../../helpers/pump_app.dart';

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
  setUpAll(() => registerFallbackValue(const ProfileUpdate()));

  TestHarness harness({
    bool signedIn = true,
    bool biometricEnabled = false,
    String? languageCode,
  }) {
    final h = buildHarness(
      signedIn: signedIn,
      biometricEnabled: biometricEnabled,
      biometricSupport: BiometricSupport.available,
      languageCode: languageCode,
    );
    addTearDown(h.dispose);
    return h;
  }

  Profile profileOf(TestHarness h) =>
      h.read(accountProfileControllerProvider)!.valueOrNull!;

  group('account profile', () {
    test('loads when signed in and keeps the signed-in user in step', () async {
      final h = harness();
      h.read(currentUserProvider.notifier).signedIn(testUser);
      keep(h, accountProfileControllerProvider);
      expect(h.read(accountProfileControllerProvider), isA<Loading<Profile>>());
      await settle();
      expect(profileOf(h).name, 'Sara Customer');
      expect(h.read(currentUserProvider)!.name, 'Sara Customer');
      expect(h.read(accountHeaderProvider)!.email, 'sara@example.com');
    });

    test('is null signed out and cleared on sign-out', () async {
      final h = harness(signedIn: false);
      keep(h, accountProfileControllerProvider);
      expect(h.read(accountProfileControllerProvider), isNull);
      verifyNever(h.account.profile);
      expect(h.read(accountHeaderProvider), isNull);

      h.read(sessionProvider.notifier).markSignedIn();
      await settle();
      expect(profileOf(h).email, 'sara@example.com');
      h.read(sessionProvider.notifier).markSignedOut();
      await settle();
      expect(h.read(accountProfileControllerProvider), isNull);
    });

    test('a failed load stays failed until reload; the header falls back to the user', () async {
      final h = harness();
      when(h.account.profile).thenAnswer(
        (_) async => const Err(ServerFailure(statusCode: 500, requestId: 'r')),
      );
      h.read(currentUserProvider.notifier).signedIn(testUser);
      keep(h, accountProfileControllerProvider);
      keep(h, accountHeaderProvider);
      await settle();
      expect(h.read(accountProfileControllerProvider), isA<Failed<Profile>>());
      expect(h.read(accountHeaderProvider)!.name, testUser.name);

      when(h.account.profile).thenAnswer((_) async => Ok(fixtureProfile()));
      await h.read(accountProfileControllerProvider.notifier).reload();
      expect(profileOf(h).name, 'Sara Customer');
    });

    test('initials: Latin, Arabic apart, one word, blank', () {
      expect(initialsOf('Sara Khalil'), 'SK');
      expect(initialsOf('  sara   maria khalil '), 'SK');
      expect(initialsOf('سارة خليل'), 'س خ');
      expect(initialsOf('Sara'), 'S');
      expect(initialsOf('   '), '?');
    });
  });

  group('profile form', () {
    Future<TestHarness> ready() async {
      final h = harness();
      when(() => h.account.updateProfile(any()))
          .thenAnswer((_) async => Ok(fixtureProfile()));
      keep(h, accountProfileControllerProvider);
      keep(h, profileFormControllerProvider);
      await settle();
      return h;
    }

    ProfileFormController form(TestHarness h) =>
        h.read(profileFormControllerProvider.notifier);

    test('sends only the fields that changed', () async {
      final h = await ready();
      expect(
        await form(h).save(name: ' Sara Customer ', phone: ''),
        ProfileSaveOutcome.unchanged,
      );
      verifyNever(() => h.account.updateProfile(any()));

      when(() => h.account.updateProfile(any())).thenAnswer(
        (_) async => Ok(fixtureProfile().copyWith(name: 'Sara Khalil')),
      );
      form(h).chooseCountry('EG');
      expect(
        await form(h).save(name: 'Sara Khalil', phone: '+20 100 123 4567'),
        ProfileSaveOutcome.saved,
      );
      final update =
          verify(() => h.account.updateProfile(captureAny())).captured.single
              as ProfileUpdate;
      expect(update.toBody(), {
        'name': 'Sara Khalil',
        'country_code': 'EG',
        'phone': '+20 100 123 4567',
      });
      expect(profileOf(h).name, 'Sara Khalil');
      expect(h.read(profileFormControllerProvider).countryChanged, isFalse);
    });

    test('a blank phone clears a saved one', () async {
      final h = await ready();
      h
          .read(accountProfileControllerProvider.notifier)
          .replace(fixtureProfile().copyWith(phone: '+201001234567'));
      await form(h).save(name: 'Sara Customer', phone: '  ');
      final update =
          verify(() => h.account.updateProfile(captureAny())).captured.single
              as ProfileUpdate;
      expect(update.toBody(), {'phone': null});
    });

    test('checks the name before sending', () async {
      final h = await ready();
      expect(
        await form(h).save(name: '   ', phone: ''),
        ProfileSaveOutcome.invalid,
      );
      expect(
        h.read(profileFormControllerProvider).problems['name'],
        ProfileFieldProblem.nameRequired,
      );
      expect(
        await form(h).save(name: 'a' * 121, phone: ''),
        ProfileSaveOutcome.invalid,
      );
      expect(
        h.read(profileFormControllerProvider).problems['name'],
        ProfileFieldProblem.nameTooLong,
      );
      verifyNever(() => h.account.updateProfile(any()));
      form(h).edited();
      expect(h.read(profileFormControllerProvider).problems, isEmpty);
    });

    test('a 422 keeps the server messages beside their fields', () async {
      final h = await ready();
      when(() => h.account.updateProfile(any())).thenAnswer(
        (_) async => Err(
          validation({
            'phone': 'Enter a valid phone number with country code.',
          }),
        ),
      );
      expect(
        await form(h).save(name: 'Sara Customer', phone: '12'),
        ProfileSaveOutcome.failed,
      );
      final state = h.read(profileFormControllerProvider);
      expect(
        state.serverError('phone'),
        'Enter a valid phone number with country code.',
      );
      expect(state.bannerFailure, isNull);
      expect(profileOf(h).phone, isNull);

      when(() => h.account.updateProfile(any()))
          .thenAnswer((_) async => const Err(NetworkFailure()));
      await form(h).save(name: 'Sara Customer', phone: '13');
      expect(
        h.read(profileFormControllerProvider).bannerFailure,
        isA<NetworkFailure>(),
      );
    });

    test('loads the countries, and the chosen country wins', () async {
      final h = await ready();
      final state = h.read(profileFormControllerProvider);
      expect(state.countries, isA<Loaded<List<Country>>>());
      expect(state.countryFor(profileOf(h)), 'US');
      form(h).chooseCountry('EG');
      expect(
        h.read(profileFormControllerProvider).countryFor(profileOf(h)),
        'EG',
      );
    });

    test('resend: 60 s cooldown, Retry-After on 429', () async {
      final h = await ready();
      when(h.account.resendVerificationEmail)
          .thenAnswer((_) async => const Ok(null));
      expect(await form(h).resendVerification(), isTrue);
      expect(await form(h).resendVerification(), isFalse);
      verify(h.account.resendVerificationEmail).called(1);

      h.now.value = h.now.value.add(const Duration(seconds: 60));
      when(h.account.resendVerificationEmail).thenAnswer(
        (_) async =>
            const Err(RateLimitedFailure(retryAfter: Duration(seconds: 90))),
      );
      expect(await form(h).resendVerification(), isFalse);
      final state = h.read(profileFormControllerProvider);
      expect(state.resendFailure, isA<RateLimitedFailure>());
      expect(
        state.resendAvailableAt,
        h.now.value.add(const Duration(seconds: 90)),
      );
    });
  });

  group('language', () {
    test('signed in: saved on the profile first, then applied', () async {
      final h = harness(languageCode: 'en');
      keep(h, accountProfileControllerProvider);
      keep(h, regionalSettingsControllerProvider);
      await settle();
      when(() => h.account.updateProfile(any()))
          .thenAnswer((_) async => Ok(fixtureProfile().copyWith(locale: 'ar')));
      final changed = await h
          .read(regionalSettingsControllerProvider.notifier)
          .selectLanguage('ar');
      expect(changed, isTrue);
      final update =
          verify(() => h.account.updateProfile(captureAny())).captured.single
              as ProfileUpdate;
      expect(update.toBody(), {'locale': 'ar'});
      expect(h.prefs.values[PreferencesStore.localeKey], 'ar');
      expect(profileOf(h).locale, 'ar');
    });

    test('a refused save keeps the language', () async {
      final h = harness(languageCode: 'en');
      keep(h, regionalSettingsControllerProvider);
      when(() => h.account.updateProfile(any()))
          .thenAnswer((_) async => const Err(NetworkFailure()));
      expect(
        await h
            .read(regionalSettingsControllerProvider.notifier)
            .selectLanguage('ar'),
        isFalse,
      );
      expect(h.prefs.values[PreferencesStore.localeKey], 'en');
      expect(
        h.read(regionalSettingsControllerProvider).failure,
        isA<NetworkFailure>(),
      );
    });

    test('signed out: saved on the device only', () async {
      final h = harness(signedIn: false, languageCode: 'en');
      keep(h, regionalSettingsControllerProvider);
      await h
          .read(regionalSettingsControllerProvider.notifier)
          .selectLanguage('ar');
      expect(h.prefs.values[PreferencesStore.localeKey], 'ar');
      verifyNever(() => h.account.updateProfile(any()));
    });
  });

  group('change password', () {
    ChangePasswordController controller(TestHarness h) =>
        h.read(changePasswordControllerProvider.notifier);

    void answer(TestHarness h, Result<PasswordChangeResult> result) => when(
      () => h.account.changePassword(
        currentPassword: any(named: 'currentPassword'),
        password: any(named: 'password'),
        passwordConfirmation: any(named: 'passwordConfirmation'),
      ),
    ).thenAnswer((_) async => result);

    test('checks empty fields and the confirmation first', () async {
      final h = harness();
      keep(h, changePasswordControllerProvider);
      expect(
        await controller(h).submit(
          currentPassword: '',
          password: 'new-passphrase-1',
          confirmation: 'other-passphrase',
        ),
        isNull,
      );
      final problems = h.read(changePasswordControllerProvider).problems;
      expect(problems['current_password'], PasswordFieldProblem.required);
      expect(problems['password_confirmation'], PasswordFieldProblem.mismatch);
      verifyNever(
        () => h.account.changePassword(
          currentPassword: any(named: 'currentPassword'),
          password: any(named: 'password'),
          passwordConfirmation: any(named: 'passwordConfirmation'),
        ),
      );
    });

    test(
      'a 422 shows the server message beside the field and keeps no password',
      () async {
        final h = harness();
        keep(h, changePasswordControllerProvider);
        answer(
          h,
          Err(validation({'current_password': 'The password is incorrect.'})),
        );
        await controller(h).submit(
          currentPassword: 'wrong-current',
          password: 'new-passphrase-1',
          confirmation: 'new-passphrase-1',
        );
        final state = h.read(changePasswordControllerProvider);
        expect(
          state.serverError('current_password'),
          'The password is incorrect.',
        );
        expect(state.bannerFailure, isNull);
        expect('$state', isNot(contains('wrong-current')));
        expect('$state', isNot(contains('new-passphrase-1')));
      },
    );

    test('success revokes biometric unlock locally (the server already did) and says so', () async {
      final h = harness(biometricEnabled: true);
      keep(h, changePasswordControllerProvider);
      keep(h, biometricResetNoticeProvider);
      keep(h, biometricUnlockSettingsProvider);
      await settle();
      expect(h.read(biometricUnlockSettingsProvider).value, isTrue);
      answer(
        h,
        const Ok(PasswordChangeResult(mobileFamilies: 2, webSessions: 1)),
      );
      final changed = await controller(h).submit(
        currentPassword: 'current-pass',
        password: 'new-passphrase-1',
        confirmation: 'new-passphrase-1',
      );
      expect(changed!.biometricTurnedOff, isTrue);
      expect(changed.signedOutDevices, 3);
      expect(h.keys.deleteCalls, 1);
      expect(h.keys.hasKey, isFalse);
      // The key died with the password change: no DELETE /biometric-key.
      expect(h.biometricServer.deleteCalls, 0);
      expect(h.secure[SecureStore.biometricEnabledKey], isNot('true'));
      await settle();
      expect(h.read(biometricUnlockSettingsProvider).value, isFalse);
      expect(h.read(biometricResetNoticeProvider), isTrue);
    });

    test('success without biometric unlock leaves the keys alone', () async {
      final h = harness();
      keep(h, changePasswordControllerProvider);
      answer(
        h,
        const Ok(PasswordChangeResult(mobileFamilies: 0, webSessions: 0)),
      );
      final changed = await controller(h).submit(
        currentPassword: 'current-pass',
        password: 'new-passphrase-1',
        confirmation: 'new-passphrase-1',
      );
      expect(changed!.biometricTurnedOff, isFalse);
      expect(h.keys.deleteCalls, 0);
      expect(h.read(biometricResetNoticeProvider), isFalse);
    });
  });

  group('biometric switch and sign out', () {
    test(
      'turning biometric unlock off deletes the server key and the local key',
      () async {
        final h = harness(biometricEnabled: true);
        keep(h, biometricUnlockSettingsProvider);
        await settle();
        await h.read(biometricUnlockSettingsProvider.notifier).disable();
        expect(h.biometricServer.deleteCalls, 1);
        expect(h.keys.deleteCalls, 1);
        expect(h.keys.hasKey, isFalse);
        expect(h.secure[SecureStore.biometricEnabledKey], isNot('true'));
        expect(h.read(biometricUnlockSettingsProvider).value, isFalse);
      },
    );

    test(
      'sign out everywhere revokes every sign-in and cleans up locally',
      () async {
        final h = harness(biometricEnabled: true);
        keep(h, signOutControllerProvider);
        await h.read(signOutControllerProvider.notifier).signOutEverywhere();
        expect(h.auth.logoutAllCalls, 1);
        expect(h.auth.logoutCalls, 0);
        expect(h.read(sessionProvider), SessionStatus.signedOut);
        expect(h.keys.hasKey, isFalse);
        expect(h.biometricServer.deleteCalls, 0);
      },
    );
  });
}
