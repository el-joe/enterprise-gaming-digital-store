import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/auth/domain/entities/auth_entities.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/auth/domain/usecases/biometric_unlock.dart';
import 'package:application/features/auth/presentation/controllers/auth_form_state.dart';
import 'package:application/features/auth/presentation/controllers/biometric_controllers.dart';
import 'package:application/features/auth/presentation/controllers/forgot_password_controller.dart';
import 'package:application/features/auth/presentation/controllers/login_controller.dart';
import 'package:application/features/auth/presentation/controllers/register_controller.dart';
import 'package:application/features/auth/presentation/controllers/session_user.dart';
import 'package:application/features/auth/presentation/controllers/sign_out_controller.dart';
import 'package:application/features/auth/presentation/controllers/two_factor_controller.dart';
import 'package:application/features/auth/presentation/controllers/verify_email_controller.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import '../../helpers/fakes.dart';
import '../../helpers/pump_app.dart';

const _prompt = BiometricPromptText(title: 'Unlock', cancel: 'Cancel');

void main() {
  late TestHarness h;

  tearDown(() => h.dispose());

  group('LoginController', () {
    setUp(() => h = buildHarness());

    test('checks the fields locally before calling the server', () async {
      final step = await h
          .read(loginControllerProvider.notifier)
          .submit(email: 'not-an-email', password: '');
      expect(step, isNull);
      final state = h.read(loginControllerProvider);
      expect(
        state.fieldErrors['email'],
        const FieldIssue.local(LocalFieldIssue.invalidEmail),
      );
      expect(
        state.fieldErrors['password'],
        const FieldIssue.local(LocalFieldIssue.required),
      );
      expect(h.auth.loginCalls, isEmpty);
    });

    test('signs in with the device name and records the user', () async {
      final step = await h
          .read(loginControllerProvider.notifier)
          .submit(email: ' sara.k@example.com ', password: 'secret');
      expect(step, LoginStep.signedIn);
      expect(h.auth.loginCalls.single.email, 'sara.k@example.com');
      expect(h.auth.loginCalls.single.deviceName, 'Pixel 9');
      expect(h.read(sessionProvider), SessionStatus.signedIn);
      expect(h.read(currentUserProvider), testUser);
      expect(h.read(emailVerificationProvider), isTrue);
    });

    test('keeps the two-factor challenge in memory only', () async {
      h.auth.loginResult = Ok(
        LoginOutcome.twoFactorRequired(
          challengeToken: 'challenge',
          expiresAt: h.now.value.add(const Duration(minutes: 5)),
        ),
      );
      final step = await h
          .read(loginControllerProvider.notifier)
          .submit(email: 'sara.k@example.com', password: 'secret');
      expect(step, LoginStep.twoFactor);
      expect(h.read(pendingTwoFactorProvider)?.challengeToken, 'challenge');
      expect(h.read(sessionProvider), SessionStatus.signedOut);
      expect(h.secure.values, isNot(contains('challenge')));
    });

    test('server field errors go beside their fields', () async {
      h.auth.loginResult = Err(
        validation({'email': 'These credentials do not match our records.'}),
      );
      await h
          .read(loginControllerProvider.notifier)
          .submit(email: 'sara.k@example.com', password: 'wrong');
      final state = h.read(loginControllerProvider);
      expect(
        state.fieldErrors['email']?.serverMessage,
        'These credentials do not match our records.',
      );
      expect(state.failure, isNull);
      h.read(loginControllerProvider.notifier).edited('email');
      expect(h.read(loginControllerProvider).fieldErrors, isEmpty);
    });

    test('429 blocks submits until Retry-After has passed', () async {
      h.auth.loginResult = const Err(
        RateLimitedFailure(retryAfter: Duration(seconds: 58)),
      );
      final controller = h.read(loginControllerProvider.notifier);
      await controller.submit(email: 'sara.k@example.com', password: 'x');
      final state = h.read(loginControllerProvider);
      expect(state.retryAt, h.now.value.add(const Duration(seconds: 58)));
      expect(state.isRateLimited(h.now.value), isTrue);

      await controller.submit(email: 'sara.k@example.com', password: 'x');
      expect(h.auth.loginCalls, hasLength(1));

      h.now.value = h.now.value.add(const Duration(seconds: 59));
      h.auth.loginResult = const Ok(LoginOutcome.signedIn(testUser));
      expect(
        await controller.submit(email: 'sara.k@example.com', password: 'x'),
        LoginStep.signedIn,
      );
    });

    test('a 429 without Retry-After waits 60 seconds', () async {
      h.auth.loginResult = const Err(RateLimitedFailure());
      await h
          .read(loginControllerProvider.notifier)
          .submit(email: 'sara.k@example.com', password: 'x');
      expect(
        h.read(loginControllerProvider).retryAt,
        h.now.value.add(const Duration(seconds: 60)),
      );
    });
  });

  group('TwoFactorController', () {
    setUp(() {
      h = buildHarness();
      h
          .read(pendingTwoFactorProvider.notifier)
          .set(
            TwoFactorChallenge(
              challengeToken: 'challenge',
              expiresAt: h.now.value.add(const Duration(minutes: 5)),
            ),
          );
    });

    test('completes with a code and clears the challenge', () async {
      final ok = await h
          .read(twoFactorControllerProvider.notifier)
          .submit('123456', recovery: false);
      expect(ok, isTrue);
      expect(h.auth.twoFactorCalls.single.code, '123456');
      expect(h.auth.twoFactorCalls.single.recovery, isNull);
      expect(h.read(pendingTwoFactorProvider), isNull);
      expect(h.read(sessionProvider), SessionStatus.signedIn);
    });

    test('sends a recovery code as recovery_code', () async {
      await h
          .read(twoFactorControllerProvider.notifier)
          .submit(' 7HQK-2M9P ', recovery: true);
      expect(h.auth.twoFactorCalls.single.recovery, '7HQK-2M9P');
      expect(h.auth.twoFactorCalls.single.code, isNull);
    });

    test('an incomplete code is caught locally', () async {
      await h
          .read(twoFactorControllerProvider.notifier)
          .submit('123', recovery: false);
      expect(
        h.read(twoFactorControllerProvider).fieldErrors['code'],
        const FieldIssue.local(LocalFieldIssue.otpIncomplete),
      );
      expect(h.auth.twoFactorCalls, isEmpty);
    });

    test('two_factor_invalid is shown under the field', () async {
      h.auth.twoFactorResult = const Err(DomainFailure('two_factor_invalid'));
      await h
          .read(twoFactorControllerProvider.notifier)
          .submit('000000', recovery: false);
      expect(
        h.read(twoFactorControllerProvider).fieldErrors['code'],
        const FieldIssue.local(LocalFieldIssue.codeInvalid),
      );
      expect(h.read(pendingTwoFactorProvider), isNotNull);
    });

    test('two_factor_expired and the local expiry end the attempt', () async {
      h.auth.twoFactorResult = const Err(DomainFailure('two_factor_expired'));
      await h
          .read(twoFactorControllerProvider.notifier)
          .submit('000000', recovery: false);
      expect(h.read(twoFactorControllerProvider).expired, isTrue);
      expect(h.read(pendingTwoFactorProvider), isNull);
    });

    test('an expired challenge is not sent', () async {
      h.now.value = h.now.value.add(const Duration(minutes: 6));
      await h
          .read(twoFactorControllerProvider.notifier)
          .submit('123456', recovery: false);
      expect(h.read(twoFactorControllerProvider).expired, isTrue);
      expect(h.auth.twoFactorCalls, isEmpty);
    });
  });

  group('RegisterController', () {
    setUp(() => h = buildHarness());

    RegisterInput input({
      bool terms = true,
      String confirmation = 'pass-1234',
    }) => RegisterInput(
      name: 'Sara Khalil',
      email: 'sara.k@example.com',
      password: 'pass-1234',
      passwordConfirmation: confirmation,
      termsAccepted: terms,
    );

    test('needs the terms and matching passwords', () async {
      await h
          .read(registerControllerProvider.notifier)
          .submit(input(terms: false, confirmation: 'other'));
      final errors = h.read(registerControllerProvider).fieldErrors;
      expect(errors['terms']?.local, LocalFieldIssue.termsRequired);
      expect(
        errors['password_confirmation']?.local,
        LocalFieldIssue.passwordMismatch,
      );
      expect(h.auth.registerCalls, isEmpty);
    });

    test('registers, signs in and leaves the email unverified', () async {
      expect(
        await h.read(registerControllerProvider.notifier).submit(input()),
        isTrue,
      );
      expect(h.auth.registerCalls.single['device_name'], 'Pixel 9');
      expect(h.read(sessionProvider), SessionStatus.signedIn);
      expect(h.read(emailVerificationProvider), isFalse);
    });

    test('maps every 422 field error', () async {
      h.auth.registerResult = Err(
        validation({
          'email': 'The email has already been taken.',
          'password': 'The password must be at least 8 characters.',
        }),
      );
      await h.read(registerControllerProvider.notifier).submit(input());
      final errors = h.read(registerControllerProvider).fieldErrors;
      expect(errors.keys, unorderedEquals(['email', 'password']));
      expect(
        errors['email']?.serverMessage,
        'The email has already been taken.',
      );
    });
  });

  group('VerifyEmailController', () {
    setUp(() {
      h = buildHarness(signedIn: true);
      h.container.listen(verifyEmailControllerProvider, (_, _) {});
    });

    test('starts in cooldown, resends after it and restarts it', () async {
      when(() => h.account.resendVerificationEmail())
          .thenAnswer((_) async => const Ok(null));
      final controller = h.read(verifyEmailControllerProvider.notifier);
      expect(await controller.resend(), isNull);

      h.now.value = h.now.value.add(VerifyEmailController.cooldown);
      expect(await controller.resend(), ResendOutcome.sent);
      expect(
        h.read(verifyEmailControllerProvider).resendAvailableAt,
        h.now.value.add(VerifyEmailController.cooldown),
      );
      verify(() => h.account.resendVerificationEmail()).called(1);
    });

    test('a 429 waits for Retry-After', () async {
      when(() => h.account.resendVerificationEmail()).thenAnswer(
        (_) async =>
            const Err(RateLimitedFailure(retryAfter: Duration(seconds: 90))),
      );
      h.container.listen(verifyEmailControllerProvider, (_, _) {});
      final controller = h.read(verifyEmailControllerProvider.notifier);
      h.now.value = h.now.value.add(const Duration(minutes: 2));
      expect(await controller.resend(), ResendOutcome.failed);
      expect(
        h.read(verifyEmailControllerProvider).resendAvailableAt,
        h.now.value.add(const Duration(seconds: 90)),
      );
    });

    test('re-reads the user to see the verification', () async {
      final controller = h.read(verifyEmailControllerProvider.notifier);
      h.auth.meResult = Ok(testUser.copyWith(emailVerified: false));
      expect(await controller.checkVerified(), isFalse);
      h.auth.meResult = const Ok(testUser);
      expect(await controller.checkVerified(), isTrue);
      expect(h.read(emailVerificationProvider), isTrue);
    });
  });

  group('ForgotPasswordController', () {
    setUp(() => h = buildHarness());

    test('opens links.forgot_password from the app config', () async {
      h.read(latestAppConfigProvider.notifier).set(fixtureAppConfig());
      await h.read(forgotPasswordControllerProvider.notifier).openResetPage();
      expect(
        h.read(forgotPasswordControllerProvider),
        ForgotPasswordStatus.opened,
      );
      expect(h.native.opened, [
        Uri.parse('https://boxcards.test/forgot-password'),
      ]);
    });

    test('reports a missing link or a browser that did not open', () async {
      final controller = h.read(forgotPasswordControllerProvider.notifier);
      await controller.openResetPage();
      expect(
        h.read(forgotPasswordControllerProvider),
        ForgotPasswordStatus.unavailable,
      );
      h.read(latestAppConfigProvider.notifier).set(fixtureAppConfig());
      h.native.opens = false;
      await controller.openResetPage();
      expect(
        h.read(forgotPasswordControllerProvider),
        ForgotPasswordStatus.openFailed,
      );
    });
  });

  group('SignOutController', () {
    test(
      'revokes the sign-in, wipes storage and deletes the local key',
      () async {
        h = buildHarness(signedIn: true, biometricEnabled: true);
        h.read(currentUserProvider.notifier).signedIn(testUser);
        await h.read(signOutControllerProvider.notifier).signOut();
        expect(h.auth.logoutCalls, 1);
        expect(h.read(sessionProvider), SessionStatus.signedOut);
        expect(h.read(currentUserProvider), isNull);
        expect(h.read(emailVerificationProvider), isNull);
        expect(h.keys.deleteCalls, 1);
        expect(h.secure.containsKey(SecureStore.biometricEnabledKey), isFalse);
        // The server key died with the sign-in: no extra call.
        expect(h.biometricServer.deleteCalls, 0);
      },
    );
  });

  group('BiometricOfferController', () {
    test('is due once on a device with biometrics, until shown', () async {
      h = buildHarness(biometricSupport: BiometricSupport.available);
      final controller = h.read(biometricOfferControllerProvider.notifier);
      await controller.evaluate();
      expect(h.read(biometricOfferDueProvider), isTrue);
      await controller.decline();
      expect(h.read(biometricOfferDueProvider), isFalse);
      expect(h.prefs.values[PreferencesStore.biometricOfferShownKey], isTrue);
      await controller.evaluate();
      expect(h.read(biometricOfferDueProvider), isFalse);
    });

    test('is never due without biometrics', () async {
      h = buildHarness();
      await h.read(biometricOfferControllerProvider.notifier).evaluate();
      expect(h.read(biometricOfferDueProvider), isFalse);
    });

    test('accept enrols, turns unlock on and marks the offer shown', () async {
      h = buildHarness(
        signedIn: true,
        biometricSupport: BiometricSupport.available,
      );
      final outcome = await h
          .read(biometricOfferControllerProvider.notifier)
          .accept(prompt: _prompt, askPassword: (_) async => 'password');
      expect(outcome, isA<EnrolDone>());
      expect(await h.read(biometricUnlockSettingsProvider.future), isTrue);
      expect(h.prefs.values[PreferencesStore.biometricOfferShownKey], isTrue);
      expect(h.biometricServer.registeredKeys, hasLength(1));
    });
  });

  group('AppLock', () {
    setUp(() => h = buildHarness(signedIn: true, biometricEnabled: true));

    test('locks after 5 minutes in the background, not before', () async {
      final lock = h.read(appLockProvider.notifier)..backgrounded();
      h.now.value = h.now.value.add(const Duration(minutes: 4, seconds: 59));
      await lock.resumed();
      expect(h.read(appLockProvider).locked, isFalse);

      lock.backgrounded();
      h.now.value = h.now.value.add(AppLock.timeout);
      await lock.resumed();
      expect(h.read(appLockProvider).locked, isTrue);
    });

    test('never locks without biometric unlock or when signed out', () async {
      h.secure.remove(SecureStore.biometricEnabledKey);
      final lock = h.read(appLockProvider.notifier)..backgrounded();
      h.now.value = h.now.value.add(const Duration(hours: 1));
      await lock.resumed();
      await lock.lockAtLaunch();
      expect(h.read(appLockProvider).locked, isFalse);
    });

    test('locks a cold start and unlocks with biometrics', () async {
      final lock = h.read(appLockProvider.notifier);
      await lock.lockAtLaunch();
      expect(h.read(appLockProvider).locked, isTrue);
      await lock.unlockWithBiometric(_prompt);
      expect(h.read(appLockProvider).locked, isFalse);
    });

    test('changed biometrics fall back to the password', () async {
      final lock = h.read(appLockProvider.notifier);
      await lock.lockAtLaunch();
      h.keys.signResults.add(const BiometricFailed(BiometricIssue.invalidated));
      await lock.unlockWithBiometric(_prompt);
      final state = h.read(appLockProvider);
      expect(state.locked, isTrue);
      expect(state.passwordMode, isTrue);
      expect(state.biometricTurnedOff, isTrue);
      expect(h.secure.containsKey(SecureStore.biometricEnabledKey), isFalse);

      h.biometricServer.confirmPasswordResults.add(
        Err(validation({'password': 'The password is incorrect.'})),
      );
      await lock.unlockWithPassword('wrong');
      expect(h.read(appLockProvider).failure, isA<ValidationFailure>());
      await lock.unlockWithPassword('right');
      expect(h.read(appLockProvider).locked, isFalse);
      expect(h.biometricServer.passwords, ['wrong', 'right']);
    });

    test('a cancelled prompt stays locked', () async {
      final lock = h.read(appLockProvider.notifier);
      await lock.lockAtLaunch();
      h.keys.signResults.add(const BiometricFailed(BiometricIssue.cancelled));
      await lock.unlockWithBiometric(_prompt);
      expect(h.read(appLockProvider).locked, isTrue);
      expect(h.read(appLockProvider).passwordMode, isFalse);
    });
  });
}
