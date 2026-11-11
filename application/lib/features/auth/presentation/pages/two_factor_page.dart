import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/auth/presentation/controllers/login_controller.dart';
import 'package:application/features/auth/presentation/controllers/two_factor_controller.dart';
import 'package:application/features/auth/presentation/widgets/auth_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/countdown.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/icon_badge.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/otp_input.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Two-step verification (02.3, 02.4): authenticator code or recovery code.
class TwoFactorPage extends ConsumerStatefulWidget {
  const TwoFactorPage({this.from, super.key});

  final String? from;

  @override
  ConsumerState<TwoFactorPage> createState() => _TwoFactorPageState();
}

class _TwoFactorPageState extends ConsumerState<TwoFactorPage> {
  final _code = TextEditingController();
  final _recovery = TextEditingController();
  bool _recoveryMode = false;

  @override
  void dispose() {
    _code.dispose();
    _recovery.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    await ref
        .read(twoFactorControllerProvider.notifier)
        .submit(
          _recoveryMode ? _recovery.text : _code.text,
          recovery: _recoveryMode,
        );
  }

  void _toggleMode() {
    setState(() => _recoveryMode = !_recoveryMode);
    ref.read(twoFactorControllerProvider.notifier).modeChanged();
  }

  void _backToSignIn() => context.go(
    Uri(
      path: Routes.login.path,
      queryParameters: {
        if (widget.from != null) Routes.fromParameter: widget.from!,
      },
    ).toString(),
  );

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final type = context.type;
    final colors = context.colors;
    final state = ref.watch(twoFactorControllerProvider);
    final controller = ref.read(twoFactorControllerProvider.notifier);
    final challenge = ref.watch(pendingTwoFactorProvider);
    final clock = ref.watch(clockProvider);
    final expired = state.expired || challenge == null;
    final field = TwoFactorController.fieldFor(recovery: _recoveryMode);
    final error = fieldIssueText(l10n, state.fieldErrors[field]);

    final body = <Widget>[
      if (!_recoveryMode) ...[
        const Align(
          alignment: AlignmentDirectional.centerStart,
          child: IconBadge(icon: Icons.verified_user_outlined),
        ),
        const SizedBox(height: Spacing.s4),
        FlowHeading(
          title: l10n.twoFactorCodeTitle,
          body: l10n.twoFactorCodeBody,
        ),
        const SizedBox(height: Spacing.s5),
        AuthFailureBanner(
          state: state,
          clock: clock,
          title: l10n.signInFailedTitle,
          onRateLimitEnded: controller.rateLimitEnded,
        ),
        Center(
          child: OtpInput(
            semanticsLabel: l10n.otpSemantics,
            controller: _code,
            hasError: error != null,
            autofocus: !expired,
            onChanged: (_) => controller.modeChanged(),
            onCompleted: (_) => unawaited(_submit()),
          ),
        ),
        if (error != null) ...[
          const SizedBox(height: Spacing.s2),
          Semantics(
            liveRegion: true,
            child: Text(
              error,
              textAlign: TextAlign.center,
              style: type.bodyCaption.copyWith(color: colors.statusDanger),
            ),
          ),
        ],
      ] else ...[
        FlowHeading(title: l10n.recoveryTitle, body: l10n.recoveryBody),
        const SizedBox(height: Spacing.s5),
        AuthFailureBanner(
          state: state,
          clock: clock,
          title: l10n.signInFailedTitle,
          onRateLimitEnded: controller.rateLimitEnded,
        ),
        AppTextField(
          label: l10n.recoveryCodeLabel,
          controller: _recovery,
          ltr: true,
          errorText: error,
          textCapitalization: TextCapitalization.characters,
          textInputAction: TextInputAction.done,
          onSubmitted: (_) => unawaited(_submit()),
        ),
      ],
      const SizedBox(height: Spacing.s4),
      if (expired)
        StatusBanner(
          tone: BannerTone.warning,
          title: l10n.twoFactorExpiredTitle,
          body: l10n.twoFactorExpiredBody,
        )
      else if (!_recoveryMode)
        CountdownBuilder(
          until: challenge.expiresAt,
          clock: clock,
          onDone: controller.challengeExpired,
          builder: (context, remaining) => Text(
            l10n.twoFactorExpiresIn(formatCountdown(remaining)),
            textAlign: TextAlign.center,
            style: type.bodySm.copyWith(color: colors.contentSecondary),
          ),
        ),
      const SizedBox(height: Spacing.s2),
      if (!expired)
        Center(
          child: AuthLink(
            label: _recoveryMode
                ? l10n.useAuthenticatorCode
                : l10n.useRecoveryCode,
            onTap: _toggleMode,
          ),
        ),
    ];

    return FlowScaffold(
      appBar: MobileAppBar(
        title: l10n.twoFactorAppBar,
        onLeading: _backToSignIn,
      ),
      body: body,
      footer: [
        if (expired)
          AppButton(
            label: l10n.backToSignIn,
            expand: true,
            onPressed: _backToSignIn,
          )
        else
          RateLimitedButton(
            state: state,
            clock: clock,
            child: AppButton(
              label: l10n.verifyAction,
              expand: true,
              loading: state.submitting,
              onPressed: () => unawaited(_submit()),
            ),
          ),
      ],
    );
  }
}
