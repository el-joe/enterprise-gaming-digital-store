import 'package:application/features/auth/auth_providers.dart';
import 'package:application/features/auth/presentation/controllers/biometric_controllers.dart';
import 'package:application/features/auth/presentation/controllers/session_user.dart';
import 'package:application/features/push/presentation/controllers/push_controllers.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'sign_out_controller.g.dart';

/// Sign out (17.4): removes this device's push token while the sign-in still works (M-A18;
/// the server also drops it with the sign-in), revokes this device's sign-in
/// (`POST /mobile/auth/logout`), wipes secure
/// storage (also when the call fails), deletes the local biometric key (the server key died with
/// the sign-in) and clears the in-memory account state. State: busy.
@riverpod
class SignOutController extends _$SignOutController {
  @override
  bool build() => false;

  Future<void> signOut() => _signOut(everywhere: false);

  /// Sign out everywhere (18.1): `POST /mobile/auth/logout-all` revokes every app sign-in of the
  /// account (this one included), then the same local cleanup as [signOut].
  Future<void> signOutEverywhere() => _signOut(everywhere: true);

  Future<void> _signOut({required bool everywhere}) async {
    if (state) {
      return;
    }
    state = true;
    final link = ref.keepAlive();
    try {
      await ref.read(pushNotificationsProvider.notifier).unregister();
      final auth = ref.read(authRepositoryProvider);
      await (everywhere ? auth.logoutAll() : auth.logout());
      await ref
          .read(disableBiometricUnlockProvider)
          .call(revokeOnServer: false);
      ref.read(currentUserProvider.notifier).clear();
      ref.read(appLockProvider.notifier).reset();
      ref.invalidate(biometricUnlockSettingsProvider);
    } finally {
      if (ref.mounted) {
        state = false;
      }
      link.close();
    }
  }
}
