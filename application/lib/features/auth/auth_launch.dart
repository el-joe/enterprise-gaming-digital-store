import 'package:application/core/router/guards.dart';
import 'package:application/features/auth/presentation/controllers/biometric_controllers.dart';
import 'package:application/features/auth/presentation/controllers/session_user.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'auth_launch.g.dart';

/// The auth feature's launch tasks, run once the app config check passed (the launch feature
/// calls this; it is the auth feature's only public entry besides its routes): reload the
/// signed-in user (email verification for the guards), decide whether the biometric offer is due,
/// and lock a signed-in session that has biometric unlock on.
@Riverpod(keepAlive: true)
class AuthLaunch extends _$AuthLaunch {
  @override
  void build() {}

  Future<void> run() async {
    if (ref.read(sessionProvider) == SessionStatus.signedIn) {
      await ref.read(appLockProvider.notifier).lockAtLaunch();
      await ref.read(currentUserProvider.notifier).refresh();
    }
    await ref.read(biometricOfferControllerProvider.notifier).evaluate();
  }
}
