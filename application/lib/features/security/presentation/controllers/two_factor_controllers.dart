import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/security/clipboard_guard.dart';
import 'package:application/core/security/secure_screen.dart';
import 'package:application/core/utils/qr_code.dart';
import 'package:application/features/account/presentation/controllers/account_controllers.dart';
import 'package:application/features/security/domain/entities/two_factor.dart';
import 'package:application/features/security/security_providers.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'two_factor_controllers.g.dart';

enum TwoFactorPhase {
  /// The current password, which `POST /security/two-factor` needs.
  password,

  /// The QR code and the key (18.2), then the 6-digit code.
  scan,

  /// The recovery codes, shown once (18.3).
  recoveryCodes,

  /// Two-step verification was already on (409 `two_factor_already_enabled`).
  alreadyOn,

  /// The customer confirmed the codes were saved; the screen closes.
  done,
}

/// State of the enrolment screen. The secret, the enrolment URI and the recovery codes live only
/// here, in an auto-disposed controller, and [toString] never prints them.
@immutable
final class TwoFactorSetupState {
  const TwoFactorSetupState({
    this.phase = TwoFactorPhase.password,
    this.busy = false,
    this.enrollment,
    this.qr,
    this.recoveryCodes,
    this.failure,
    this.codeProblem = false,
  });

  final TwoFactorPhase phase;
  final bool busy;
  final TwoFactorEnrollment? enrollment;

  /// The enrolment URI as a QR code; null when it cannot be encoded (the key is still shown).
  final QrCode? qr;
  final RecoveryCodes? recoveryCodes;

  /// The last failure: wrong password (422 `current_password`), wrong code (422
  /// `two_factor_invalid`), or any other.
  final Failure? failure;

  /// The code is not 6 digits (checked before sending).
  final bool codeProblem;

  /// The key in groups of four for reading and typing ("JBSW Y3DP ...").
  String? get groupedSecret {
    final secret = enrollment?.secret;
    if (secret == null) {
      return null;
    }
    final groups = <String>[];
    for (var i = 0; i < secret.length; i += 4) {
      groups.add(
        secret.substring(i, i + 4 > secret.length ? secret.length : i + 4),
      );
    }
    return groups.join(' ');
  }

  /// Whether the secret or the recovery codes are on screen (screen protection is on).
  bool get showsSecrets =>
      phase == TwoFactorPhase.scan || phase == TwoFactorPhase.recoveryCodes;

  @override
  String toString() =>
      'TwoFactorSetupState($phase, busy: $busy, secret: <redacted>, '
      'recoveryCodes: ${recoveryCodes == null ? 'none' : '<redacted>'}, failure: $failure)';
}

/// Turn on two-step verification (18.2, 18.3): the current password starts the enrolment
/// (`POST /security/two-factor`), the QR code of the `otpauth://` URI and the key are shown with
/// copy, a 6-digit code confirms it (`POST /security/two-factor/confirm`), and the recovery codes
/// are shown once with copy until the customer says they saved them.
///
/// Nothing here is logged or stored: the password is only passed through, the secret and the
/// codes stay in this auto-disposed state, and copies go through the clipboard guard (cleared
/// after 60 s). Screen protection (`ScreenSecurity`, M-A14) is on while the key or the codes are
/// on screen.
@riverpod
class TwoFactorSetupController extends _$TwoFactorSetupController {
  bool _secured = false;
  late ScreenSecurity _security;

  @override
  TwoFactorSetupState build() {
    _security = ref.read(screenSecurityProvider);
    final security = _security;
    ref.onDispose(() {
      if (_secured) {
        _secured = false;
        unawaited(security.disable());
      }
    });
    return const TwoFactorSetupState();
  }

  void _protect({required bool on}) {
    if (on == _secured) {
      return;
    }
    _secured = on;
    unawaited(on ? _security.enable() : _security.disable());
  }

  void edited() {
    if (state.failure != null || state.codeProblem) {
      state = TwoFactorSetupState(
        phase: state.phase,
        enrollment: state.enrollment,
        qr: state.qr,
        recoveryCodes: state.recoveryCodes,
      );
    }
  }

  Future<void> start(String currentPassword) async {
    if (state.busy || state.phase != TwoFactorPhase.password) {
      return;
    }
    if (currentPassword.isEmpty) {
      return;
    }
    state = const TwoFactorSetupState(busy: true);
    final result = await ref
        .read(securityRepositoryProvider)
        .enableTwoFactor(currentPassword: currentPassword);
    if (!ref.mounted) {
      return;
    }
    switch (result) {
      case Ok(:final value):
        QrCode? qr;
        try {
          qr = QrCode.encode(value.otpauthUri.toString());
        } on ArgumentError {
          qr = null;
        }
        _protect(on: true);
        state = TwoFactorSetupState(
          phase: TwoFactorPhase.scan,
          enrollment: value,
          qr: qr,
        );
      case Err(failure: ConflictFailure(code: 'two_factor_already_enabled')):
        ref
            .read(accountProfileControllerProvider.notifier)
            .setTwoFactor(enabled: true);
        state = const TwoFactorSetupState(phase: TwoFactorPhase.alreadyOn);
      case Err(:final failure):
        state = TwoFactorSetupState(failure: failure);
    }
  }

  Future<void> copySecret() async {
    final secret = state.enrollment?.secret;
    if (secret != null) {
      await ref.read(clipboardGuardProvider).copy(secret);
    }
  }

  Future<void> confirm(String code) async {
    if (state.busy || state.phase != TwoFactorPhase.scan) {
      return;
    }
    final digits = code.trim();
    if (!RegExp(r'^[0-9]{6}$').hasMatch(digits)) {
      state = TwoFactorSetupState(
        phase: state.phase,
        enrollment: state.enrollment,
        qr: state.qr,
        codeProblem: true,
      );
      return;
    }
    state = TwoFactorSetupState(
      phase: state.phase,
      enrollment: state.enrollment,
      qr: state.qr,
      busy: true,
    );
    final result = await ref
        .read(securityRepositoryProvider)
        .confirmTwoFactor(code: digits);
    if (!ref.mounted) {
      return;
    }
    switch (result) {
      case Ok(:final value):
        ref
            .read(accountProfileControllerProvider.notifier)
            .setTwoFactor(enabled: true);
        // The key is no longer needed; only the codes stay until the customer saved them.
        state = TwoFactorSetupState(
          phase: TwoFactorPhase.recoveryCodes,
          recoveryCodes: value,
        );
      case Err(:final failure):
        state = TwoFactorSetupState(
          phase: state.phase,
          enrollment: state.enrollment,
          qr: state.qr,
          failure: failure,
        );
    }
  }

  Future<void> copyRecoveryCodes() async {
    final codes = state.recoveryCodes;
    if (codes != null) {
      await ref.read(clipboardGuardProvider).copy(codes.codes.join('\n'));
    }
  }

  /// "I saved my codes": the codes leave memory and screen protection ends.
  void finish() {
    _protect(on: false);
    state = const TwoFactorSetupState(phase: TwoFactorPhase.done);
  }
}

/// Turn off two-step verification (18.4): `DELETE /security/two-factor` with the current
/// password. State: busy.
@riverpod
class TwoFactorDisableController extends _$TwoFactorDisableController {
  @override
  bool build() => false;

  /// Returns null when turned off, else the failure (a 422 holds the message for
  /// `current_password`).
  Future<Failure?> disable(String currentPassword) async {
    if (state || currentPassword.isEmpty) {
      return null;
    }
    state = true;
    final link = ref.keepAlive();
    try {
      final result = await ref
          .read(securityRepositoryProvider)
          .disableTwoFactor(currentPassword: currentPassword);
      if (result.isOk) {
        ref
            .read(accountProfileControllerProvider.notifier)
            .setTwoFactor(enabled: false);
      }
      return result.failureOrNull;
    } finally {
      if (ref.mounted) {
        state = false;
      }
      link.close();
    }
  }
}
