import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/features/account/account_providers.dart';
import 'package:application/features/auth/auth_providers.dart';
import 'package:application/features/auth/presentation/controllers/biometric_controllers.dart';
import 'package:application/features/security/presentation/controllers/devices_controller.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'change_password_controller.g.dart';

/// A field the form checks before sending.
enum PasswordFieldProblem { required, mismatch }

@immutable
final class ChangePasswordState {
  const ChangePasswordState({
    this.saving = false,
    this.problems = const {},
    this.failure,
  });

  final bool saving;

  /// Local checks by API field (`current_password`, `password`, `password_confirmation`).
  final Map<String, PasswordFieldProblem> problems;

  /// The last attempt's failure: a 422 holds the server's field messages, anything else is a
  /// banner. Never holds a password.
  final Failure? failure;

  String? serverError(String field) => switch (failure) {
    final ValidationFailure validation => validation.firstErrorFor(field),
    _ => null,
  };

  Failure? get bannerFailure => switch (failure) {
    final ValidationFailure validation
        when const [
          'current_password',
          'password',
          'password_confirmation',
        ].any((f) => validation.firstErrorFor(f) != null) =>
      null,
    _ => failure,
  };
}

/// Result of a successful change.
@immutable
final class PasswordChanged {
  const PasswordChanged({
    required this.signedOutDevices,
    required this.biometricTurnedOff,
  });

  /// Other app sign-ins and browser sessions the server signed out.
  final int signedOutDevices;

  /// Biometric unlock was on: the server revoked every biometric key of the account (ADR-040),
  /// so it was turned off here too and the customer is asked to turn it on again.
  final bool biometricTurnedOff;
}

/// Change password (18.7): `PUT /account/password` with the current, new and confirmed password.
/// The passwords are only passed through (never kept in the state, logged or stored). On
/// success every other device is signed out and every biometric key of the account is revoked
/// (ADR-040), so the local key and flag are removed as well.
@riverpod
class ChangePasswordController extends _$ChangePasswordController {
  @override
  ChangePasswordState build() => const ChangePasswordState();

  void edited() {
    if (state.failure != null || state.problems.isNotEmpty) {
      state = const ChangePasswordState();
    }
  }

  Future<PasswordChanged?> submit({
    required String currentPassword,
    required String password,
    required String confirmation,
  }) async {
    if (state.saving) {
      return null;
    }
    final problems = <String, PasswordFieldProblem>{
      if (currentPassword.isEmpty)
        'current_password': PasswordFieldProblem.required,
      if (password.isEmpty) 'password': PasswordFieldProblem.required,
      if (confirmation.isEmpty)
        'password_confirmation': PasswordFieldProblem.required
      else if (confirmation != password)
        'password_confirmation': PasswordFieldProblem.mismatch,
    };
    if (problems.isNotEmpty) {
      state = ChangePasswordState(problems: problems);
      return null;
    }
    state = const ChangePasswordState(saving: true);
    final link = ref.keepAlive();
    try {
      final result = await ref
          .read(accountRepositoryProvider)
          .changePassword(
            currentPassword: currentPassword,
            password: password,
            passwordConfirmation: confirmation,
          );
      switch (result) {
        case Ok(:final value):
          final wasOn = await ref
              .read(biometricEnrolmentStoreProvider)
              .isEnabled();
          if (wasOn) {
            // The server already revoked the key with the password change.
            await ref
                .read(disableBiometricUnlockProvider)
                .call(revokeOnServer: false);
            ref.invalidate(biometricUnlockSettingsProvider);
            ref.read(biometricResetNoticeProvider.notifier).show();
          }
          ref.invalidate(devicesControllerProvider);
          if (ref.mounted) {
            state = const ChangePasswordState();
          }
          return PasswordChanged(
            signedOutDevices: value.mobileFamilies + value.webSessions,
            biometricTurnedOff: wasOn,
          );
        case Err(:final failure):
          if (ref.mounted) {
            state = ChangePasswordState(failure: failure);
          }
          return null;
      }
    } finally {
      link.close();
    }
  }
}

/// Set when a password change turned biometric unlock off (ADR-040): Security (18.1) shows the
/// note to turn it on again until it is on again or dismissed. In memory only.
@Riverpod(keepAlive: true)
class BiometricResetNotice extends _$BiometricResetNotice {
  @override
  bool build() {
    // A sign-in or sign-out starts without the note.
    ref.watch(sessionProvider);
    return false;
  }

  void show() => state = true;

  void dismiss() => state = false;
}
