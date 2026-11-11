import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/core/utils/date_format.dart';
import 'package:application/features/code_reveal/domain/entities/revealed_code.dart';
import 'package:application/features/code_reveal/presentation/controllers/reveal_controller.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/countdown.dart';
import 'package:flutter/material.dart';

/// Masked code text: the stored mask (`****7777`) with bullets for the hidden characters.
String maskedDisplay(String? masked) {
  final value = masked == null || masked.isEmpty ? '********' : masked;
  return value.replaceAll('*', '•');
}

/// The last visible characters of a mask, for the screen-reader hint ("ends in 7777").
String? maskedTail(String? masked) {
  if (masked == null) {
    return null;
  }
  final tail = masked.replaceAll('*', '');
  return tail.isEmpty ? null : tail;
}

/// The Code Reveal Card (`76:594`: Masked, Revealed, Capture hidden). Shows only what the state
/// gives it; every action goes to the reveal controller.
class RevealCard extends StatelessWidget {
  const RevealCard({
    required this.info,
    required this.state,
    required this.clock,
    required this.onReveal,
    required this.onCopy,
    required this.onHide,
    required this.onReadAloud,
    required this.onOpenRedemption,
    required this.onAutoHideDue,
    required this.onRateLimitOver,
    required this.secureNote,
    super.key,
  });

  final RevealUnitInfo info;
  final RevealScreenState state;
  final Clock clock;
  final VoidCallback onReveal;
  final VoidCallback onCopy;
  final VoidCallback onHide;
  final VoidCallback onReadAloud;
  final VoidCallback onOpenRedemption;

  /// The countdown reached zero (the controller's own timer hides the code too).
  final VoidCallback onAutoHideDue;
  final VoidCallback onRateLimitOver;
  final String secureNote;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final code = state.isRevealed ? state.code : null;
    final delivered = info.deliveredAt;
    final subtitle = [
      l10n.revealCodeOf(info.index, info.count),
      if (delivered != null)
        l10n.revealDeliveredAt(AppDates.dayTime(context, delivered)),
    ].join(' · ');

    return Container(
      padding: const EdgeInsetsDirectional.all(Spacing.s4),
      decoration: BoxDecoration(
        color: colors.surfaceRaised,
        border: Border.all(color: colors.lineSubtle),
        borderRadius: BorderRadius.circular(Radii.md),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(info.productName, style: type.headingSm),
          const SizedBox(height: Spacing.s1),
          Text(
            subtitle,
            style: type.bodySm.copyWith(color: colors.contentSecondary),
          ),
          const SizedBox(height: Spacing.s4),
          if (state.captured)
            _CapturedBox(text: l10n.revealCaptureHidden)
          else
            _CodeBox(info: info, code: code),
          const SizedBox(height: Spacing.s3),
          if (code != null) ...[
            Row(
              children: [
                Expanded(
                  flex: 2,
                  child: Semantics(
                    label: l10n.revealCopySemantics,
                    button: true,
                    excludeSemantics: true,
                    onTap: onCopy,
                    child: AppButton(
                      label: l10n.revealCopy,
                      icon: Icons.copy,
                      expand: true,
                      onPressed: onCopy,
                    ),
                  ),
                ),
                const SizedBox(width: Spacing.s2),
                Expanded(
                  child: Semantics(
                    label: l10n.revealHideSemantics,
                    button: true,
                    excludeSemantics: true,
                    onTap: onHide,
                    child: AppButton(
                      label: l10n.revealHide,
                      variant: AppButtonVariant.secondary,
                      expand: true,
                      onPressed: onHide,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: Spacing.s1),
            Wrap(
              alignment: WrapAlignment.spaceBetween,
              crossAxisAlignment: WrapCrossAlignment.center,
              children: [
                if (state.hideAt != null)
                  Padding(
                    padding: const EdgeInsetsDirectional.only(end: Spacing.s2),
                    child: CountdownBuilder(
                      until: state.hideAt!,
                      clock: clock,
                      onDone: onAutoHideDue,
                      builder: (context, remaining) => Text(
                        l10n.revealHidesIn(formatCountdown(remaining)),
                        style: type.bodySm.copyWith(color: colors.contentMuted),
                      ),
                    ),
                  ),
                TextButton(
                  onPressed: onReadAloud,
                  style: TextButton.styleFrom(
                    minimumSize: const Size(48, 48),
                    foregroundColor: colors.accentText,
                  ),
                  child: Text(l10n.revealReadAloud),
                ),
              ],
            ),
            if (code.redemptionUrl?.scheme == 'https')
              Align(
                alignment: AlignmentDirectional.centerStart,
                child: TextButton.icon(
                  onPressed: onOpenRedemption,
                  icon: const Icon(Icons.language),
                  label: Text(l10n.revealOpenRedemption),
                  style: TextButton.styleFrom(
                    minimumSize: const Size(48, 48),
                    foregroundColor: colors.accentText,
                  ),
                ),
              ),
          ] else if (!state.captured)
            _RevealButton(
              state: state,
              clock: clock,
              enabled: info.revealable,
              onReveal: onReveal,
              onRateLimitOver: onRateLimitOver,
            ),
          const SizedBox(height: Spacing.s3),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(Icons.shield_outlined, size: 16, color: colors.contentMuted),
              const SizedBox(width: Spacing.s2),
              Expanded(
                child: Text(
                  secureNote,
                  style: type.bodySm.copyWith(color: colors.contentMuted),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _RevealButton extends StatelessWidget {
  const _RevealButton({
    required this.state,
    required this.clock,
    required this.enabled,
    required this.onReveal,
    required this.onRateLimitOver,
  });

  final RevealScreenState state;
  final Clock clock;
  final bool enabled;
  final VoidCallback onReveal;
  final VoidCallback onRateLimitOver;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final retryAt = state.retryAt;
    if (state.phase == RevealPhase.rateLimited && retryAt != null) {
      return CountdownBuilder(
        until: retryAt,
        clock: clock,
        onDone: onRateLimitOver,
        builder: (context, remaining) => AppButton(
          label: l10n.revealTryAgainIn(formatCountdown(remaining)),
          icon: Icons.visibility_outlined,
          expand: true,
          onPressed: null,
        ),
      );
    }
    final blocked = state.phase == RevealPhase.rateLimited || !enabled;
    return Semantics(
      hint: l10n.revealButtonHint,
      child: AppButton(
        label: l10n.revealButton,
        icon: Icons.visibility_outlined,
        expand: true,
        // No spinner behind the password sheet; the sheet has its own.
        loading: (state.busy && state.reauth == null) || state.biometricPending,
        onPressed: blocked || state.busy ? null : onReveal,
      ),
    );
  }
}

class _CodeBox extends StatelessWidget {
  const _CodeBox({required this.info, required this.code});

  final RevealUnitInfo info;
  final RevealedCode? code;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final revealed = code;
    final tail = maskedTail(info.maskedCode);
    final codeText = revealed?.code ?? maskedDisplay(info.maskedCode);
    final pin = revealed?.pin;
    return Container(
      padding: const EdgeInsetsDirectional.all(Spacing.s4),
      decoration: BoxDecoration(
        color: colors.surfaceSunken,
        border: Border.all(
          color: revealed == null ? colors.lineSubtle : colors.accentPrimary,
        ),
        borderRadius: BorderRadius.circular(Radii.md),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          ExcludeSemantics(
            child: Text(
              l10n.revealCodeLabel.toUpperCase(),
              style: type.overline.copyWith(color: colors.contentSecondary),
            ),
          ),
          const SizedBox(height: Spacing.s2),
          // The code is never read out on its own: only "Read code aloud" announces it.
          Semantics(
            container: true,
            label: revealed != null
                ? l10n.revealShownSemantics
                : tail == null
                ? l10n.revealMaskedSemanticsNoHint
                : l10n.revealMaskedSemantics(tail),
            excludeSemantics: true,
            child: Directionality(
              textDirection: TextDirection.ltr,
              child: FittedBox(
                fit: BoxFit.scaleDown,
                child: Text(
                  codeText,
                  textAlign: TextAlign.center,
                  style: type.monoLg.copyWith(
                    color: revealed == null
                        ? colors.contentSecondary
                        : colors.accentText,
                    letterSpacing: revealed == null ? 2 : 0.5,
                  ),
                ),
              ),
            ),
          ),
          if (pin != null && pin.isNotEmpty) ...[
            const SizedBox(height: Spacing.s3),
            Divider(height: 1, color: colors.lineSubtle),
            const SizedBox(height: Spacing.s2),
            ExcludeSemantics(
              child: Row(
                children: [
                  Text(
                    l10n.revealPinLabel,
                    style: type.bodySm.copyWith(color: colors.contentSecondary),
                  ),
                  const SizedBox(width: Spacing.s3),
                  Expanded(
                    child: Align(
                      alignment: AlignmentDirectional.centerEnd,
                      child: FittedBox(
                        fit: BoxFit.scaleDown,
                        child: Directionality(
                          textDirection: TextDirection.ltr,
                          child: Text(pin, style: type.monoMd),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _CapturedBox extends StatelessWidget {
  const _CapturedBox({required this.text});

  final String text;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return Container(
      padding: const EdgeInsetsDirectional.all(Spacing.s6),
      decoration: BoxDecoration(
        color: colors.surfaceSunken,
        border: Border.all(color: colors.lineDefault),
        borderRadius: BorderRadius.circular(Radii.md),
      ),
      child: Column(
        children: [
          Icon(Icons.visibility_off_outlined, color: colors.contentSecondary),
          const SizedBox(height: Spacing.s2),
          Text(
            text,
            textAlign: TextAlign.center,
            style: context.type.bodyMd.copyWith(color: colors.contentSecondary),
          ),
        ],
      ),
    );
  }
}
