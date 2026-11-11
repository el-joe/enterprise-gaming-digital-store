import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/auth/presentation/controllers/session_user.dart';
import 'package:application/features/auth/presentation/controllers/verify_email_controller.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/countdown.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/icon_badge.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Verify-email prompt (02.7) with resend cooldown.
class VerifyEmailPage extends ConsumerStatefulWidget {
  const VerifyEmailPage({super.key});

  @override
  ConsumerState<VerifyEmailPage> createState() => _VerifyEmailPageState();
}

class _VerifyEmailPageState extends ConsumerState<VerifyEmailPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        unawaited(
          ref.read(verifyEmailControllerProvider.notifier).ensureUser(),
        );
      }
    });
  }

  Future<void> _resend() async {
    final l10n = context.l10n;
    final outcome = await ref
        .read(verifyEmailControllerProvider.notifier)
        .resend();
    if (outcome == ResendOutcome.sent && mounted) {
      showAppSnackbar(context, message: l10n.verificationSent);
    }
  }

  Future<void> _check() async {
    final l10n = context.l10n;
    final verified = await ref
        .read(verifyEmailControllerProvider.notifier)
        .checkVerified();
    if (!mounted || verified == null) {
      return;
    }
    if (verified) {
      showAppSnackbar(context, message: l10n.emailVerifiedDone);
      context.go(Routes.home.path);
    } else {
      showAppSnackbar(
        context,
        message: l10n.stillUnverified,
        tone: SnackbarTone.info,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(verifyEmailControllerProvider);
    final email = ref.watch(currentUserProvider)?.email;
    final clock = ref.watch(clockProvider);
    final failure = state.failure;

    return FlowScaffold(
      appBar: MobileAppBar(
        leading: AppBarLeading.close,
        onLeading: () => context.go(Routes.home.path),
      ),
      body: [
        const Align(
          alignment: AlignmentDirectional.centerStart,
          child: IconBadge(icon: Icons.mail_outline),
        ),
        const SizedBox(height: Spacing.s4),
        FlowHeading(
          title: l10n.verifyEmailTitle,
          body: email == null
              ? l10n.verifyEmailBodyNoEmail
              : l10n.verifyEmailBody(email),
        ),
        if (failure != null) ...[
          const SizedBox(height: Spacing.s4),
          StatusBanner(
            tone: BannerTone.danger,
            title: failureMessage(l10n, failure),
          ),
        ],
        const SizedBox(height: Spacing.s12),
        CountdownBuilder(
          until: state.resendAvailableAt,
          clock: clock,
          builder: (context, remaining) => remaining == Duration.zero
              ? const SizedBox.shrink()
              : Text(
                  l10n.resendIn(formatCountdown(remaining)),
                  textAlign: TextAlign.center,
                  style: context.type.bodySm.copyWith(
                    color: context.colors.contentSecondary,
                  ),
                ),
        ),
      ],
      footer: [
        AppButton(
          label: l10n.iVerified,
          expand: true,
          loading: state.checking,
          onPressed: () => unawaited(_check()),
        ),
        CountdownBuilder(
          until: state.resendAvailableAt,
          clock: clock,
          builder: (context, remaining) => AppButton(
            label: l10n.resendEmail,
            variant: AppButtonVariant.secondary,
            expand: true,
            loading: state.sending,
            onPressed: remaining == Duration.zero
                ? () => unawaited(_resend())
                : null,
          ),
        ),
      ],
    );
  }
}
