import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/account/presentation/widgets/account_widgets.dart';
import 'package:application/features/security/presentation/controllers/two_factor_controllers.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/otp_input.dart';
import 'package:application/shared/widgets/qr_code_view.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Turn on two-step verification (18.2, 18.3): password, then the QR code and key with copy and
/// the 6-digit code, then the recovery codes shown once. Leaving while the codes are on screen
/// asks whether they were saved. Screen protection is on while the key or the codes show.
class TwoFactorSetupPage extends ConsumerStatefulWidget {
  const TwoFactorSetupPage({super.key});

  @override
  ConsumerState<TwoFactorSetupPage> createState() => _TwoFactorSetupPageState();
}

class _TwoFactorSetupPageState extends ConsumerState<TwoFactorSetupPage> {
  final _password = TextEditingController();
  final _code = TextEditingController();

  @override
  void dispose() {
    _password.dispose();
    _code.dispose();
    super.dispose();
  }

  TwoFactorSetupController get _controller =>
      ref.read(twoFactorSetupControllerProvider.notifier);

  void _close() {
    if (context.canPop()) {
      context.pop();
    } else {
      context.go(Routes.security.path);
    }
  }

  /// Leaving the recovery codes: ask first; "I saved them" finishes.
  Future<void> _leaveCodes() async {
    final l10n = context.l10n;
    final leave = await showConfirmDialog(
      context,
      title: l10n.recoveryCodesLeaveTitle,
      body: l10n.recoveryCodesLeaveBody,
      action: l10n.recoveryCodesSaved,
      destructive: false,
    );
    if (leave && mounted) {
      _finish();
    }
  }

  void _finish() {
    final l10n = context.l10n;
    _controller.finish();
    showAppSnackbar(context, message: l10n.twoFactorEnabled);
    _close();
  }

  Future<void> _copy(Future<void> Function() copy, String message) async {
    await copy();
    if (mounted) {
      showAppSnackbar(context, message: message);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(twoFactorSetupControllerProvider);
    final codesShown = state.phase == TwoFactorPhase.recoveryCodes;
    final appBar = MobileAppBar(
      title: codesShown ? l10n.recoveryCodesTitle : l10n.twoFactorTitle,
      leading: AppBarLeading.close,
      onLeading: codesShown ? () => unawaited(_leaveCodes()) : _close,
    );
    final page = switch (state.phase) {
      TwoFactorPhase.password => _passwordStep(context, state, appBar),
      TwoFactorPhase.scan => _scanStep(context, state, appBar),
      TwoFactorPhase.recoveryCodes => _codesStep(context, state, appBar),
      TwoFactorPhase.alreadyOn || TwoFactorPhase.done => FlowScaffold(
        appBar: appBar,
        body: [
          StatusBanner(
            tone: BannerTone.success,
            title: l10n.twoFactorAlreadyOnTitle,
            body: l10n.twoFactorAlreadyOnBody,
          ),
        ],
        footer: [
          AppButton(label: l10n.actionBack, expand: true, onPressed: _close),
        ],
      ),
    };
    return PopScope(
      canPop: !codesShown,
      onPopInvokedWithResult: (didPop, _) {
        if (!didPop && codesShown) {
          unawaited(_leaveCodes());
        }
      },
      child: page,
    );
  }

  Widget _passwordStep(
    BuildContext context,
    TwoFactorSetupState state,
    PreferredSizeWidget appBar,
  ) {
    final l10n = context.l10n;
    final failure = state.failure;
    final fieldError = switch (failure) {
      final ValidationFailure validation =>
        validation.firstErrorFor('current_password') ??
            failureMessage(l10n, validation),
      _ => null,
    };
    return FlowScaffold(
      appBar: appBar,
      body: [
        FlowHeading(
          title: l10n.twoFactorTitle,
          body: l10n.twoFactorPasswordBody,
        ),
        const SizedBox(height: Spacing.s5),
        if (failure != null && failure is! ValidationFailure) ...[
          StatusBanner(
            tone: BannerTone.danger,
            title: failureDetail(l10n, failure),
          ),
          const SizedBox(height: Spacing.s4),
        ],
        AppTextField(
          key: const ValueKey('two-factor-password'),
          label: l10n.passwordLabel,
          controller: _password,
          obscure: true,
          errorText: fieldError,
          autofillHints: const [AutofillHints.password],
          textInputAction: TextInputAction.done,
          onChanged: (_) => _controller.edited(),
          onSubmitted: (value) {
            _password.clear();
            unawaited(_controller.start(value));
          },
        ),
      ],
      footer: [
        AppButton(
          key: const ValueKey('two-factor-start'),
          label: l10n.actionContinue,
          expand: true,
          loading: state.busy,
          onPressed: () {
            final password = _password.text;
            _password.clear();
            unawaited(_controller.start(password));
          },
        ),
      ],
    );
  }

  Widget _scanStep(
    BuildContext context,
    TwoFactorSetupState state,
    PreferredSizeWidget appBar,
  ) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final failure = state.failure;
    final codeError = state.codeProblem
        ? l10n.twoFactorCodeInvalid
        : failure == null
        ? null
        : failureMessage(l10n, failure);
    final qr = state.qr;
    final secret = state.groupedSecret ?? '';
    return FlowScaffold(
      appBar: appBar,
      body: [
        Text(l10n.twoFactorScanStep, style: type.labelLg),
        const SizedBox(height: Spacing.s3),
        if (qr != null)
          Center(
            child: QrCodeView(
              key: const ValueKey('two-factor-qr'),
              code: qr,
              semanticsLabel: l10n.twoFactorQrSemantics,
            ),
          ),
        const SizedBox(height: Spacing.s4),
        Text(
          l10n.twoFactorKeyLabel,
          style: type.labelSm.copyWith(color: colors.contentMuted),
        ),
        const SizedBox(height: Spacing.s1),
        Row(
          children: [
            Expanded(
              child: Text(
                secret,
                key: const ValueKey('two-factor-secret'),
                textDirection: TextDirection.ltr,
                textAlign: TextAlign.start,
                style: type.monoMd,
              ),
            ),
            IconButton(
              key: const ValueKey('two-factor-copy-key'),
              tooltip: l10n.twoFactorCopyKey,
              icon: const Icon(Icons.copy_outlined),
              constraints: const BoxConstraints(minWidth: 48, minHeight: 48),
              onPressed: () => unawaited(
                _copy(_controller.copySecret, l10n.twoFactorKeyCopied),
              ),
            ),
          ],
        ),
        Text(
          l10n.twoFactorSecureNote,
          style: type.bodySm.copyWith(color: colors.contentSecondary),
        ),
        const SizedBox(height: Spacing.s5),
        Text(l10n.twoFactorCodeStep, style: type.labelLg),
        const SizedBox(height: Spacing.s3),
        OtpInput(
          key: const ValueKey('two-factor-code'),
          semanticsLabel: l10n.twoFactorCodeSemantics,
          controller: _code,
          hasError: codeError != null,
          onChanged: (_) => _controller.edited(),
          onCompleted: (value) => unawaited(_controller.confirm(value)),
        ),
        if (codeError != null) ...[
          const SizedBox(height: Spacing.s2),
          Text(
            codeError,
            style: type.bodySm.copyWith(color: colors.statusDanger),
          ),
        ],
      ],
      footer: [
        AppButton(
          key: const ValueKey('two-factor-confirm'),
          label: l10n.confirmAction,
          expand: true,
          loading: state.busy,
          onPressed: () => unawaited(_controller.confirm(_code.text)),
        ),
      ],
    );
  }

  Widget _codesStep(
    BuildContext context,
    TwoFactorSetupState state,
    PreferredSizeWidget appBar,
  ) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final codes = state.recoveryCodes?.codes ?? const <String>[];
    return FlowScaffold(
      appBar: appBar,
      body: [
        StatusBanner(
          tone: BannerTone.success,
          title: l10n.twoFactorOnBannerTitle,
          body: l10n.twoFactorOnBannerBody,
        ),
        const SizedBox(height: Spacing.s3),
        Text(
          l10n.recoveryCodesBody,
          style: type.bodySm.copyWith(color: colors.contentSecondary),
        ),
        const SizedBox(height: Spacing.s3),
        Semantics(
          container: true,
          label: l10n.recoveryCodesListSemantics(codes.join(', ')),
          excludeSemantics: true,
          child: Container(
            key: const ValueKey('recovery-codes'),
            padding: const EdgeInsetsDirectional.all(Spacing.s4),
            decoration: BoxDecoration(
              color: colors.surfaceSunken,
              border: Border.all(color: colors.lineSubtle),
              borderRadius: BorderRadius.circular(Radii.md),
            ),
            child: Directionality(
              textDirection: TextDirection.ltr,
              child: Wrap(
                runSpacing: Spacing.s2,
                children: [
                  for (final code in codes)
                    FractionallySizedBox(
                      widthFactor: 0.5,
                      child: Text(code, style: type.monoSm),
                    ),
                ],
              ),
            ),
          ),
        ),
        const SizedBox(height: Spacing.s3),
        AppButton(
          key: const ValueKey('recovery-codes-copy'),
          label: l10n.recoveryCodesCopy,
          icon: Icons.copy_outlined,
          variant: AppButtonVariant.secondary,
          expand: true,
          onPressed: () => unawaited(
            _copy(_controller.copyRecoveryCodes, l10n.recoveryCodesCopied),
          ),
        ),
      ],
      footer: [
        AppButton(
          key: const ValueKey('recovery-codes-saved'),
          label: l10n.recoveryCodesSaved,
          expand: true,
          onPressed: _finish,
        ),
      ],
    );
  }
}
