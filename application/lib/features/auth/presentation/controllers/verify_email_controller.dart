import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/account/account_providers.dart';
import 'package:application/features/auth/presentation/controllers/session_user.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'verify_email_controller.g.dart';

@immutable
final class VerifyEmailState {
  const VerifyEmailState({
    required this.resendAvailableAt,
    this.sending = false,
    this.checking = false,
    this.failure,
  });

  /// Resend is disabled until then (the email was just sent at registration, or a 429).
  final DateTime resendAvailableAt;
  final bool sending;
  final bool checking;
  final Failure? failure;
}

enum ResendOutcome { sent, failed }

/// Verify-email prompt (02.7): resend through `POST /mobile/account/email/verification-notification`
/// with a 60 s cooldown (or the server's `Retry-After`), and "I've verified" re-reads the user.
@riverpod
class VerifyEmailController extends _$VerifyEmailController {
  static const cooldown = Duration(seconds: 60);

  @override
  VerifyEmailState build() => VerifyEmailState(
    resendAvailableAt: ref.read(clockProvider)().add(cooldown),
  );

  /// Loads the user when the prompt opens without one (app restarted while unverified).
  Future<void> ensureUser() async {
    if (ref.read(currentUserProvider) == null) {
      await ref.read(currentUserProvider.notifier).refresh();
    }
  }

  bool canResend(DateTime now) =>
      !state.sending && !now.isBefore(state.resendAvailableAt);

  Future<ResendOutcome?> resend() async {
    final now = ref.read(clockProvider)();
    if (!canResend(now)) {
      return null;
    }
    state = VerifyEmailState(
      resendAvailableAt: state.resendAvailableAt,
      sending: true,
    );
    final result = await ref
        .read(accountRepositoryProvider)
        .resendVerificationEmail();
    if (!ref.mounted) {
      return null;
    }
    final after = ref.read(clockProvider)();
    switch (result) {
      case Ok():
        state = VerifyEmailState(resendAvailableAt: after.add(cooldown));
        return ResendOutcome.sent;
      case Err(failure: final RateLimitedFailure failure):
        state = VerifyEmailState(
          resendAvailableAt: after.add(failure.retryAfter ?? cooldown),
          failure: failure,
        );
        return ResendOutcome.failed;
      case Err(:final failure):
        state = VerifyEmailState(resendAvailableAt: after, failure: failure);
        return ResendOutcome.failed;
    }
  }

  /// Re-reads the user. Returns true when the email is now verified, false when not yet, null on
  /// a failure (shown in the banner).
  Future<bool?> checkVerified() async {
    state = VerifyEmailState(
      resendAvailableAt: state.resendAvailableAt,
      checking: true,
    );
    final refreshed = await ref.read(currentUserProvider.notifier).refresh();
    if (!ref.mounted) {
      return null;
    }
    state = VerifyEmailState(
      resendAvailableAt: state.resendAvailableAt,
      failure: refreshed ? null : const NetworkFailure(),
    );
    if (!refreshed) {
      return null;
    }
    return ref.read(currentUserProvider)?.emailVerified ?? false;
  }
}
