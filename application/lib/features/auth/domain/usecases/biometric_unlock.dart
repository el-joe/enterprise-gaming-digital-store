import 'dart:convert';
import 'dart:math';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/auth/domain/repositories/biometric_repositories.dart';

/// Asks the customer for the password (the confirm-password sheet). [previous] is the failure of
/// the last attempt (for example a wrong password), null the first time. Returns null when
/// cancelled.
typedef PasswordPrompt = Future<String?> Function(Failure? previous);

/// Outcome of [EnrolBiometricUnlock].
sealed class EnrolOutcome {
  const EnrolOutcome();
}

final class EnrolDone extends EnrolOutcome {
  const EnrolDone();
}

/// The customer cancelled the biometric prompt or the password sheet.
final class EnrolCancelled extends EnrolOutcome {
  const EnrolCancelled();
}

/// No strong biometric on this device (or none enrolled).
final class EnrolUnavailable extends EnrolOutcome {
  const EnrolUnavailable();
}

final class EnrolFailed extends EnrolOutcome {
  const EnrolFailed([this.failure]);

  /// The server failure, or null for a platform failure.
  final Failure? failure;
}

const _maxPasswordAttempts = 3;

/// Turns biometric unlock on (owner decision 6): creates the hardware key behind a biometric
/// prompt, registers its public key, and on 423 `recent_auth_required` asks for the password,
/// confirms it and registers again once. Any failure removes the new local key, so the device never
/// keeps a key the server does not know.
class EnrolBiometricUnlock {
  EnrolBiometricUnlock({
    required this._keys,
    required this._server,
    required this._enrolment,
  });

  final BiometricKeyStore _keys;
  final BiometricAuthRepository _server;
  final BiometricEnrolmentStore _enrolment;

  Future<EnrolOutcome> call({
    required BiometricPromptText prompt,
    required PasswordPrompt askPassword,
  }) async {
    if (await _keys.support() != BiometricSupport.available) {
      return const EnrolUnavailable();
    }
    final String publicKey;
    switch (await _keys.createKey(prompt)) {
      case BiometricDone(:final value):
        publicKey = value;
      case BiometricFailed(:final issue):
        return _fromIssue(issue);
    }

    var registered = await _server.registerKey(publicKey);
    if (registered case Err(failure: RecentAuthRequiredFailure())) {
      final confirmed = await _confirmPassword(askPassword);
      if (confirmed != null) {
        await _keys.deleteKey();
        return confirmed;
      }
      registered = await _server.registerKey(publicKey);
    }
    if (registered case Err(:final failure)) {
      await _keys.deleteKey();
      return EnrolFailed(failure);
    }
    await _enrolment.setEnabled(enabled: true);
    return const EnrolDone();
  }

  /// Null when the password was confirmed, else the outcome to return.
  Future<EnrolOutcome?> _confirmPassword(PasswordPrompt askPassword) async {
    Failure? previous;
    for (var attempt = 0; attempt < _maxPasswordAttempts; attempt++) {
      final password = await askPassword(previous);
      if (password == null) {
        return const EnrolCancelled();
      }
      final result = await _server.confirmPassword(password);
      switch (result) {
        case Ok():
          return null;
        case Err(:final failure) when failure is ValidationFailure:
          previous = failure;
        case Err(:final failure):
          return EnrolFailed(failure);
      }
    }
    return EnrolFailed(previous);
  }

  EnrolOutcome _fromIssue(BiometricIssue issue) => switch (issue) {
    BiometricIssue.cancelled => const EnrolCancelled(),
    BiometricIssue.unavailable => const EnrolUnavailable(),
    _ => const EnrolFailed(),
  };
}

/// Turns biometric unlock off: revokes the server key (best effort: the key also dies with the
/// sign-in), deletes the local key and clears the flag.
class DisableBiometricUnlock {
  DisableBiometricUnlock({
    required this._keys,
    required this._server,
    required this._enrolment,
  });

  final BiometricKeyStore _keys;
  final BiometricAuthRepository _server;
  final BiometricEnrolmentStore _enrolment;

  /// [revokeOnServer] is false after sign-out (the token is gone and the family is revoked).
  Future<void> call({bool revokeOnServer = true}) async {
    if (revokeOnServer) {
      await _server.deleteKey();
    }
    await _keys.deleteKey();
    await _enrolment.setEnabled(enabled: false);
  }
}

/// Outcome of a biometric unlock or re-auth attempt.
enum BiometricCheck {
  /// The customer is confirmed.
  confirmed,

  /// The prompt was dismissed; the customer may try again or use the password.
  cancelled,

  /// Biometrics cannot be used (off, changed, locked): use the password.
  usePassword,

  /// A server or network failure; see the failure.
  failed,
}

/// Unlocks the app locally after the background timeout: a fresh random nonce is signed with the
/// hardware key, which the platform releases only after a successful strong biometric prompt.
/// When biometrics changed the key is invalidated: unlock is turned off and the password is used.
class UnlockWithBiometric {
  UnlockWithBiometric({
    required this._keys,
    required this._enrolment,
    required this._disable,
    Random? random,
  }) : _random = random ?? Random.secure();

  final BiometricKeyStore _keys;
  final BiometricEnrolmentStore _enrolment;
  final DisableBiometricUnlock _disable;
  final Random _random;

  Future<BiometricCheck> call(BiometricPromptText prompt) async {
    if (!await _enrolment.isEnabled()) {
      return BiometricCheck.usePassword;
    }
    final nonce = base64Url.encode(
      List<int>.generate(32, (_) => _random.nextInt(256)),
    );
    final signed = await _keys.sign('boxcards-unlock.$nonce', prompt);
    return switch (signed) {
      BiometricDone() => BiometricCheck.confirmed,
      BiometricFailed(issue: BiometricIssue.cancelled) =>
        BiometricCheck.cancelled,
      BiometricFailed(issue: BiometricIssue.invalidated) => await _turnOff(),
      BiometricFailed() => BiometricCheck.usePassword,
    };
  }

  Future<BiometricCheck> _turnOff() async {
    await _disable();
    return BiometricCheck.usePassword;
  }
}

/// Result of [ConfirmRecentAuthWithBiometric].
final class RecentAuthCheck {
  const RecentAuthCheck(this.check, [this.failure]);

  final BiometricCheck check;
  final Failure? failure;
}

/// The biometric path of the recent-auth confirmation (code reveal, M-A14): challenge -> sign with
/// the hardware key -> `confirm-biometric`, which opens the same window as `confirm-password`.
/// [BiometricCheck.usePassword] means the caller shows the password sheet instead.
class ConfirmRecentAuthWithBiometric {
  ConfirmRecentAuthWithBiometric({
    required this._keys,
    required this._server,
    required this._enrolment,
    required this._disable,
  });

  final BiometricKeyStore _keys;
  final BiometricAuthRepository _server;
  final BiometricEnrolmentStore _enrolment;
  final DisableBiometricUnlock _disable;

  Future<RecentAuthCheck> call(BiometricPromptText prompt) async {
    if (!await _enrolment.isEnabled()) {
      return const RecentAuthCheck(BiometricCheck.usePassword);
    }
    final challenge = await _server.challenge();
    final String value;
    switch (challenge) {
      case Ok(value: final issued):
        value = issued;
      case Err(failure: NotFoundFailure()):
        // biometric_not_registered: the server no longer knows this device's key.
        await _disable(revokeOnServer: false);
        return const RecentAuthCheck(BiometricCheck.usePassword);
      case Err(:final failure):
        return RecentAuthCheck(BiometricCheck.failed, failure);
    }

    final signed = await _keys.sign(value, prompt);
    final String signature;
    switch (signed) {
      case BiometricDone(value: final s):
        signature = s;
      case BiometricFailed(issue: BiometricIssue.cancelled):
        return const RecentAuthCheck(BiometricCheck.cancelled);
      case BiometricFailed(issue: BiometricIssue.invalidated):
        await _disable();
        return const RecentAuthCheck(BiometricCheck.usePassword);
      case BiometricFailed():
        return const RecentAuthCheck(BiometricCheck.usePassword);
    }

    final confirmed = await _server.confirm(
      challenge: value,
      signature: signature,
    );
    return switch (confirmed) {
      Ok() => const RecentAuthCheck(BiometricCheck.confirmed),
      Err(failure: BiometricLockedFailure()) => await _locked(),
      Err(:final failure) => RecentAuthCheck(BiometricCheck.failed, failure),
    };
  }

  Future<RecentAuthCheck> _locked() async {
    // The server revoked the key after five bad signatures.
    await _disable(revokeOnServer: false);
    return const RecentAuthCheck(BiometricCheck.usePassword);
  }
}
