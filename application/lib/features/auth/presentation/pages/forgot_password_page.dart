import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/auth/presentation/controllers/forgot_password_controller.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Forgot password (02.8): opens the web reset page (`links.forgot_password`).
class ForgotPasswordPage extends ConsumerWidget {
  const ForgotPasswordPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final status = ref.watch(forgotPasswordControllerProvider);
    final banner = switch (status) {
      ForgotPasswordStatus.opened => StatusBanner(
        tone: BannerTone.success,
        title: l10n.resetPageOpenedTitle,
        body: l10n.resetPageOpenedBody,
      ),
      ForgotPasswordStatus.unavailable => StatusBanner(
        tone: BannerTone.danger,
        title: l10n.resetPageUnavailable,
      ),
      ForgotPasswordStatus.openFailed => StatusBanner(
        tone: BannerTone.danger,
        title: l10n.linkOpenFailed,
      ),
      _ => null,
    };
    return FlowScaffold(
      appBar: MobileAppBar(
        onLeading: () =>
            context.canPop() ? context.pop() : context.go(Routes.login.path),
      ),
      body: [
        FlowHeading(title: l10n.forgotTitle, body: l10n.forgotBody),
        if (banner != null) ...[const SizedBox(height: Spacing.s4), banner],
      ],
      footer: [
        AppButton(
          label: l10n.openResetPage,
          icon: Icons.open_in_new,
          expand: true,
          loading: status == ForgotPasswordStatus.opening,
          onPressed: () => unawaited(
            ref.read(forgotPasswordControllerProvider.notifier).openResetPage(),
          ),
        ),
        if (status == ForgotPasswordStatus.opened)
          AppButton(
            label: l10n.backToSignIn,
            variant: AppButtonVariant.ghost,
            expand: true,
            onPressed: () => context.go(Routes.login.path),
          ),
      ],
    );
  }
}
