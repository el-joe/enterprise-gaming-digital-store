import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/auth/auth_providers.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/auth/domain/usecases/biometric_unlock.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'biometric_controllers.g.dart';

/// Biometric unlock on or off for this sign-in (owner decision 6). The Security screen (M-A16)
/// uses the same controller for its switch; until then the account card on home shows it.
@Riverpod(keepAlive: true)
class BiometricUnlockSettings extends _$BiometricUnlockSettings {
  @override
  Future<bool> build() =>
      ref.watch(biometricEnrolmentStoreProvider).isEnabled();

  Future<EnrolOutcome> enable({
    required BiometricPromptText prompt,
    required PasswordPrompt askPassword,
  }) async {
    final outcome = await ref
        .read(enrolBiometricUnlockProvider)
        .call(prompt: prompt, askPassword: askPassword);
    if (ref.mounted) {
      state = AsyncData(outcome is EnrolDone);
    }
    return outcome;
  }

  Future<void> disable() async {
    await ref.read(disableBiometricUnlockProvider).call();
    if (ref.mounted) {
      state = const AsyncData(false);
    }
  }

  /// Re-reads the flag (after sign-out or an invalidated key).
  void reload() => ref.invalidateSelf();
}

/// The one-time offer after the first sign-in (owner decision 6). State: enrolling.
@Riverpod(keepAlive: true)
class BiometricOfferController extends _$BiometricOfferController {
  @override
  bool build() => false;

  /// Decides at launch whether the offer is due at the next sign-in: never shown on this install,
  /// a strong biometric is enrolled, and unlock is not already on.
  Future<void> evaluate() async {
    final prefs = ref.read(preferencesStoreProvider);
    var due = false;
    if (!prefs.biometricOfferShown) {
      final support = await ref.read(biometricKeyStoreProvider).support();
      due =
          support == BiometricSupport.available &&
          !await ref.read(biometricEnrolmentStoreProvider).isEnabled();
    }
    ref.read(biometricOfferDueProvider.notifier).update(due: due);
  }

  /// Turn on from the offer page. The offer is marked shown whatever the outcome.
  Future<EnrolOutcome> accept({
    required BiometricPromptText prompt,
    required PasswordPrompt askPassword,
  }) async {
    state = true;
    final link = ref.keepAlive();
    try {
      final outcome = await ref
          .read(biometricUnlockSettingsProvider.notifier)
          .enable(prompt: prompt, askPassword: askPassword);
      await _markShown();
      return outcome;
    } finally {
      if (ref.mounted) {
        state = false;
      }
      link.close();
    }
  }

  Future<void> decline() => _markShown();

  Future<void> _markShown() async {
    await ref.read(preferencesStoreProvider).setBiometricOfferShown();
    ref.read(biometricOfferDueProvider.notifier).update(due: false);
  }
}

@immutable
final class AppLockState {
  const AppLockState({
    this.locked = false,
    this.busy = false,
    this.passwordMode = false,
    this.biometricTurnedOff = false,
    this.failure,
  });

  final bool locked;
  final bool busy;

  /// Show the password field instead of the biometric button.
  final bool passwordMode;

  /// Biometrics changed (or the server dropped the key): unlock was turned off.
  final bool biometricTurnedOff;
  final Failure? failure;
}

/// App lock (owner decision 6): with biometric unlock on, the app locks after [timeout] in the
/// background (and at a cold start) and opens again with a biometric prompt or the password
/// (`confirm-password`). It gates opening the app only; the server's recent-auth check for code
/// reveal is unchanged.
@Riverpod(keepAlive: true)
class AppLock extends _$AppLock {
  static const timeout = Duration(minutes: 5);

  DateTime? _backgroundedAt;

  @override
  AppLockState build() => const AppLockState();

  Future<bool> _shouldLock() async =>
      ref.read(sessionProvider) == SessionStatus.signedIn &&
      await ref.read(biometricEnrolmentStoreProvider).isEnabled();

  /// Cold start: a signed-in session with unlock on starts locked.
  Future<void> lockAtLaunch() async {
    if (await _shouldLock()) {
      state = const AppLockState(locked: true);
    }
  }

  void backgrounded() {
    _backgroundedAt ??= ref.read(clockProvider)();
  }

  Future<void> resumed() async {
    final since = _backgroundedAt;
    _backgroundedAt = null;
    if (since == null || state.locked) {
      return;
    }
    final away = ref.read(clockProvider)().difference(since);
    if (away >= timeout && await _shouldLock()) {
      state = const AppLockState(locked: true);
    }
  }

  Future<void> unlockWithBiometric(BiometricPromptText prompt) async {
    if (!state.locked || state.busy) {
      return;
    }
    state = const AppLockState(locked: true, busy: true);
    final check = await ref.read(unlockWithBiometricProvider).call(prompt);
    switch (check) {
      case BiometricCheck.confirmed:
        state = const AppLockState();
      case BiometricCheck.cancelled:
        state = const AppLockState(locked: true);
      case BiometricCheck.usePassword || BiometricCheck.failed:
        final stillOn = await ref
            .read(biometricEnrolmentStoreProvider)
            .isEnabled();
        if (!stillOn) {
          ref.invalidate(biometricUnlockSettingsProvider);
        }
        state = AppLockState(
          locked: true,
          passwordMode: true,
          biometricTurnedOff: !stillOn,
        );
    }
  }

  void usePassword() {
    state = AppLockState(
      locked: true,
      passwordMode: true,
      biometricTurnedOff: state.biometricTurnedOff,
    );
  }

  Future<void> unlockWithPassword(String password) async {
    if (!state.locked || state.busy) {
      return;
    }
    final turnedOff = state.biometricTurnedOff;
    if (password.isEmpty) {
      return;
    }
    state = AppLockState(
      locked: true,
      busy: true,
      passwordMode: true,
      biometricTurnedOff: turnedOff,
    );
    final result = await ref
        .read(biometricAuthRepositoryProvider)
        .confirmPassword(password);
    state = switch (result) {
      Ok() => const AppLockState(),
      Err(:final failure) => AppLockState(
        locked: true,
        passwordMode: true,
        biometricTurnedOff: turnedOff,
        failure: failure,
      ),
    };
  }

  /// Signed out: nothing to protect.
  void reset() {
    _backgroundedAt = null;
    state = const AppLockState();
  }
}

/// Whether this device can use biometric unlock (hides the switch where it cannot).
@riverpod
Future<BiometricSupport> biometricSupport(Ref ref) =>
    ref.watch(biometricKeyStoreProvider).support();
