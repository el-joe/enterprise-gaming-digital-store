import 'package:application/core/platform/native_bridge.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'forgot_password_controller.g.dart';

enum ForgotPasswordStatus { idle, opening, opened, unavailable, openFailed }

/// Forgot password (02.8): the app has no stateless reset endpoint, so it opens the web page
/// `links.forgot_password` from `GET /app/config` in the system browser.
@riverpod
class ForgotPasswordController extends _$ForgotPasswordController {
  @override
  ForgotPasswordStatus build() => ForgotPasswordStatus.idle;

  Future<void> openResetPage() async {
    final url = ref.read(latestAppConfigProvider)?.links.forgotPassword;
    if (url == null) {
      state = ForgotPasswordStatus.unavailable;
      return;
    }
    state = ForgotPasswordStatus.opening;
    final opened = await ref.read(nativeBridgeProvider).openExternalUrl(url);
    if (ref.mounted) {
      state = opened
          ? ForgotPasswordStatus.opened
          : ForgotPasswordStatus.openFailed;
    }
  }
}
