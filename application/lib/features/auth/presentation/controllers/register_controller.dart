import 'package:application/core/network/result.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/auth/auth_providers.dart';
import 'package:application/features/auth/presentation/controllers/auth_form_state.dart';
import 'package:application/features/auth/presentation/controllers/session_user.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'register_controller.g.dart';

/// The register form's values.
final class RegisterInput {
  const RegisterInput({
    required this.name,
    required this.email,
    required this.password,
    required this.passwordConfirmation,
    required this.termsAccepted,
  });

  final String name;
  final String email;
  final String password;
  final String passwordConfirmation;
  final bool termsAccepted;
}

/// Create account (02.5, 02.6): local checks, `POST /mobile/auth/register`, the 422 field errors
/// beside each field with a summary banner. On success the customer is signed in and the guards
/// open the verify-email prompt.
@riverpod
class RegisterController extends _$RegisterController {
  static const fields = {'name', 'email', 'password', 'password_confirmation'};

  @override
  AuthFormState build() => const AuthFormState();

  Future<bool> submit(RegisterInput input) async {
    final now = ref.read(clockProvider)();
    if (state.submitting || state.isRateLimited(now)) {
      return false;
    }
    const required = FieldIssue.local(LocalFieldIssue.required);
    final issues = <String, FieldIssue>{
      if (input.name.trim().isEmpty) 'name': required,
      if (input.email.trim().isEmpty)
        'email': required
      else if (!looksLikeEmail(input.email))
        'email': const FieldIssue.local(LocalFieldIssue.invalidEmail),
      if (input.password.isEmpty) 'password': required,
      if (input.passwordConfirmation != input.password)
        'password_confirmation': const FieldIssue.local(
          LocalFieldIssue.passwordMismatch,
        ),
      if (!input.termsAccepted)
        'terms': const FieldIssue.local(LocalFieldIssue.termsRequired),
    };
    if (issues.isNotEmpty) {
      state = AuthFormState(fieldErrors: issues);
      return false;
    }

    final link = ref.keepAlive();
    try {
      state = const AuthFormState(submitting: true);
      final deviceName = await ref.read(deviceNameProvider.future);
      final result = await ref
          .read(authRepositoryProvider)
          .register(
            name: input.name.trim(),
            email: input.email.trim(),
            password: input.password,
            passwordConfirmation: input.passwordConfirmation,
            deviceName: deviceName,
          );
      switch (result) {
        case Ok(:final value):
          ref.read(currentUserProvider.notifier).signedIn(value);
          state = const AuthFormState();
          return true;
        case Err(:final failure):
          state = AuthFormState.fromFailure(
            failure,
            ref.read(clockProvider)(),
            fields: fields,
          );
          return false;
      }
    } finally {
      link.close();
    }
  }

  void edited(String field) {
    if (state.fieldErrors.containsKey(field)) {
      state = state.copyWith(
        fieldErrors: Map.of(state.fieldErrors)..remove(field),
      );
    }
  }

  void rateLimitEnded() {
    state = state.copyWith(retryAt: () => null, failure: () => null);
  }
}
