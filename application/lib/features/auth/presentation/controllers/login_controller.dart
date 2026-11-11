import 'package:application/core/network/result.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/auth/auth_providers.dart';
import 'package:application/features/auth/domain/entities/auth_entities.dart';
import 'package:application/features/auth/presentation/controllers/auth_form_state.dart';
import 'package:application/features/auth/presentation/controllers/session_user.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'login_controller.g.dart';

/// What the sign-in page does next.
enum LoginStep {
  /// Tokens stored; the guards move the customer on.
  signedIn,

  /// Open the two-factor page; the challenge is in [PendingTwoFactor].
  twoFactor,
}

/// A pending two-factor challenge (the token is never stored).
final class TwoFactorChallenge {
  const TwoFactorChallenge({
    required this.challengeToken,
    required this.expiresAt,
  });

  final String challengeToken;
  final DateTime expiresAt;
}

/// The pending two-factor challenge of a password sign-in. Memory only: it lives 300 s and is
/// single use (API_DOCUMENTATION.md 1b).
@Riverpod(keepAlive: true)
class PendingTwoFactor extends _$PendingTwoFactor {
  @override
  TwoFactorChallenge? build() => null;

  void set(TwoFactorChallenge challenge) => state = challenge;

  void clear() => state = null;
}

/// Sign-in form (02.1, 02.2): local checks, `POST /mobile/auth/login`, server field errors beside
/// the fields and the 429 countdown.
@riverpod
class LoginController extends _$LoginController {
  static const fields = {'email', 'password'};

  @override
  AuthFormState build() => const AuthFormState();

  Future<LoginStep?> submit({
    required String email,
    required String password,
  }) async {
    final now = ref.read(clockProvider)();
    if (state.submitting || state.isRateLimited(now)) {
      return null;
    }
    final issues = <String, FieldIssue>{
      if (email.trim().isEmpty)
        'email': const FieldIssue.local(LocalFieldIssue.required)
      else if (!looksLikeEmail(email))
        'email': const FieldIssue.local(LocalFieldIssue.invalidEmail),
      if (password.isEmpty)
        'password': const FieldIssue.local(LocalFieldIssue.required),
    };
    if (issues.isNotEmpty) {
      state = AuthFormState(fieldErrors: issues);
      return null;
    }

    final link = ref.keepAlive();
    try {
      state = const AuthFormState(submitting: true);
      final deviceName = await ref.read(deviceNameProvider.future);
      final result = await ref
          .read(authRepositoryProvider)
          .login(
            email: email.trim(),
            password: password,
            deviceName: deviceName,
          );
      switch (result) {
        case Ok(value: LoginSignedIn(:final user)):
          ref.read(currentUserProvider.notifier).signedIn(user);
          state = const AuthFormState();
          return LoginStep.signedIn;
        case Ok(
          value: LoginTwoFactorRequired(
            :final challengeToken,
            :final expiresAt,
          ),
        ):
          ref
              .read(pendingTwoFactorProvider.notifier)
              .set(
                TwoFactorChallenge(
                  challengeToken: challengeToken,
                  expiresAt: expiresAt,
                ),
              );
          state = const AuthFormState();
          return LoginStep.twoFactor;
        case Err(:final failure):
          state = AuthFormState.fromFailure(
            failure,
            ref.read(clockProvider)(),
            fields: fields,
          );
          return null;
      }
    } finally {
      link.close();
    }
  }

  /// Called when the rate-limit countdown ends.
  void rateLimitEnded() {
    state = state.copyWith(retryAt: () => null, failure: () => null);
  }

  /// Clears the error of [field] while the customer edits it.
  void edited(String field) {
    if (state.fieldErrors.containsKey(field)) {
      state = state.copyWith(
        fieldErrors: Map.of(state.fieldErrors)..remove(field),
      );
    }
  }
}
