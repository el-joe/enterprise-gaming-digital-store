import 'package:application/core/router/guards.dart';
import 'package:application/features/auth/auth_providers.dart';
import 'package:application/features/auth/domain/entities/auth_entities.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'session_user.g.dart';

/// The signed-in customer (`UserResource`), in memory only. Null until a sign-in or `me`.
@Riverpod(keepAlive: true)
class CurrentUser extends _$CurrentUser {
  @override
  AuthUser? build() => null;

  /// Records a sign-in: the user and the email-verification state the guards read.
  void signedIn(AuthUser user) {
    state = user;
    ref
        .read(emailVerificationProvider.notifier)
        .update(verified: user.emailVerified);
  }

  /// Reloads the user with `GET /mobile/auth/me`. Returns whether it succeeded.
  Future<bool> refresh() async {
    final result = await ref.read(authRepositoryProvider).me();
    final user = result.valueOrNull;
    if (user == null || !ref.mounted) {
      return false;
    }
    signedIn(user);
    return true;
  }

  void clear() {
    state = null;
    ref.read(emailVerificationProvider.notifier).reset();
  }
}
