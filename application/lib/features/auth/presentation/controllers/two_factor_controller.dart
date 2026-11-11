import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/auth/auth_providers.dart';
import 'package:application/features/auth/presentation/controllers/auth_form_state.dart';
import 'package:application/features/auth/presentation/controllers/login_controller.dart';
import 'package:application/features/auth/presentation/controllers/session_user.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'two_factor_controller.g.dart';

/// Second factor (02.3, 02.4): a 6-digit TOTP code or a recovery code against the pending
/// challenge. `two_factor_invalid` is shown under the field; `two_factor_expired` (or the
/// challenge's own expiry) ends the attempt and sends the customer back to sign in.
@riverpod
class TwoFactorController extends _$TwoFactorController {
  @override
  AuthFormState build() => const AuthFormState();

  /// Field key of the current mode, also the API field name.
  static String fieldFor({required bool recovery}) =>
      recovery ? 'recovery_code' : 'code';

  /// Returns true once signed in (the guards move the customer on).
  Future<bool> submit(String value, {required bool recovery}) async {
    final challenge = ref.read(pendingTwoFactorProvider);
    final now = ref.read(clockProvider)();
    if (challenge == null || !now.isBefore(challenge.expiresAt)) {
      state = const AuthFormState(expired: true);
      return false;
    }
    if (state.submitting || state.isRateLimited(now)) {
      return false;
    }
    final field = fieldFor(recovery: recovery);
    final trimmed = value.trim();
    if (trimmed.isEmpty ||
        (!recovery && !RegExp(r'^\d{6}$').hasMatch(trimmed))) {
      state = AuthFormState(
        fieldErrors: {
          field: FieldIssue.local(
            recovery ? LocalFieldIssue.required : LocalFieldIssue.otpIncomplete,
          ),
        },
      );
      return false;
    }

    final link = ref.keepAlive();
    try {
      state = const AuthFormState(submitting: true);
      final result = await ref
          .read(authRepositoryProvider)
          .completeTwoFactor(
            challengeToken: challenge.challengeToken,
            code: recovery ? null : trimmed,
            recoveryCode: recovery ? trimmed : null,
          );
      switch (result) {
        case Ok(:final value):
          ref.read(pendingTwoFactorProvider.notifier).clear();
          ref.read(currentUserProvider.notifier).signedIn(value);
          state = const AuthFormState();
          return true;
        case Err(failure: DomainFailure(code: 'two_factor_invalid')):
          state = AuthFormState(
            fieldErrors: {
              field: const FieldIssue.local(LocalFieldIssue.codeInvalid),
            },
          );
        case Err(failure: DomainFailure(code: 'two_factor_expired')):
          ref.read(pendingTwoFactorProvider.notifier).clear();
          state = const AuthFormState(expired: true);
        case Err(:final failure):
          state = AuthFormState.fromFailure(
            failure,
            ref.read(clockProvider)(),
            fields: {field},
          );
      }
      return false;
    } finally {
      link.close();
    }
  }

  /// The challenge's countdown reached zero.
  void challengeExpired() {
    ref.read(pendingTwoFactorProvider.notifier).clear();
    state = const AuthFormState(expired: true);
  }

  /// Switching between code and recovery code clears the errors.
  void modeChanged() {
    if (!state.expired) {
      state = state.copyWith(fieldErrors: const {}, failure: () => null);
    }
  }

  void rateLimitEnded() {
    state = state.copyWith(retryAt: () => null, failure: () => null);
  }
}
