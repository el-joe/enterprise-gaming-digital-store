import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/security/device_integrity.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/code_reveal/presentation/controllers/reveal_controller.dart';
import 'package:application/features/code_reveal/presentation/widgets/reauth_sheet.dart';
import 'package:application/features/code_reveal/presentation/widgets/reveal_card.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter/semantics.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// One unit's code (10.1 to 10.6; guest 11.4). Renders [RevealController] and forwards taps and
/// the app lifecycle to it. The screen is protected while it is mounted (the controller holds
/// `setSecure`), covered while the app is inactive, and the code hides on background.
class RevealPage extends ConsumerStatefulWidget {
  const RevealPage({
    required this.orderPublicId,
    required this.unitPublicId,
    this.guest = false,
    super.key,
  });

  final String orderPublicId;
  final String unitPublicId;
  final bool guest;

  @override
  ConsumerState<RevealPage> createState() => _RevealPageState();
}

class _RevealPageState extends ConsumerState<RevealPage> {
  late final RevealControllerProvider _provider = revealControllerProvider(
    widget.orderPublicId,
    widget.unitPublicId,
    widget.guest,
  );
  late final AppLifecycleListener _lifecycle;
  bool _sheetOpen = false;

  @override
  void initState() {
    super.initState();
    _lifecycle = AppLifecycleListener(
      onStateChange: (lifecycle) =>
          ref.read(_provider.notifier).lifecycleChanged(lifecycle),
    );
  }

  @override
  void dispose() {
    _lifecycle.dispose();
    super.dispose();
  }

  RevealController get _controller => ref.read(_provider.notifier);

  void _back() {
    if (context.canPop()) {
      context.pop();
    } else {
      context.go(
        widget.guest
            ? Routes.guestOrderLocation(widget.orderPublicId)
            : Routes.orderLocation(widget.orderPublicId),
      );
    }
  }

  Future<void> _openSheet() async {
    if (_sheetOpen) {
      return;
    }
    _sheetOpen = true;
    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: context.colors.surfaceRaised,
      builder: (_) => ReauthSheet(provider: _provider),
    );
    _sheetOpen = false;
    if (mounted && ref.read(_provider).reauth != null) {
      _controller.cancelReauth();
    }
  }

  void _announce(String message) {
    unawaited(
      SemanticsService.sendAnnouncement(
        View.of(context),
        message,
        Directionality.of(context),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final state = ref.watch(_provider);
    final clock = ref.watch(clockProvider);

    ref.listen(_provider, (previous, next) {
      if (previous?.reauth == null && next.reauth != null) {
        unawaited(_openSheet());
      }
      if (next.copyCount > (previous?.copyCount ?? 0)) {
        showAppSnackbar(context, message: l10n.revealCopied);
      }
      if (next.linkFailedCount > (previous?.linkFailedCount ?? 0)) {
        showAppSnackbar(
          context,
          message: l10n.linkOpenFailed,
          tone: SnackbarTone.danger,
        );
      }
      if (next.hideCount > (previous?.hideCount ?? 0)) {
        _announce(
          next.lastHide == RevealHideReason.timeout
              ? l10n.revealHiddenTimeout
              : l10n.revealHidden,
        );
      }
    });

    final ios = Theme.of(context).platform == TargetPlatform.iOS;
    final prompt = BiometricPromptText(
      title: l10n.revealBiometricPrompt,
      cancel: l10n.actionCancel,
    );
    final failure = state.failure;
    final retryAt = state.retryAt;

    final banners = <Widget>[
      if (state.captured)
        StatusBanner(
          tone: BannerTone.warning,
          title: l10n.revealCaptureTitle,
          body: l10n.revealCaptureBody,
        ),
      if (state.emailUnverified)
        StatusBanner(
          tone: BannerTone.danger,
          title: l10n.revealUnverifiedTitle,
          body: l10n.revealUnverifiedBody,
        ),
      if (state.phase == RevealPhase.rateLimited)
        StatusBanner(
          tone: BannerTone.warning,
          title: l10n.revealRateLimitedTitle,
          body: retryAt == null
              ? l10n.revealRateLimitedLater
              : l10n.revealRateLimitedBody(
                  (retryAt.difference(clock()).inSeconds / 60).ceil().clamp(
                    1,
                    24 * 60,
                  ),
                ),
        ),
      if (state.phase == RevealPhase.denied)
        StatusBanner(
          tone: BannerTone.danger,
          title: l10n.revealDeniedTitle,
          body: widget.guest
              ? l10n.revealGuestDeniedBody
              : l10n.revealDeniedBody,
        ),
      if (state.phase == RevealPhase.failed && failure != null)
        StatusBanner(
          tone: isOfflineFailure(failure)
              ? BannerTone.offline
              : BannerTone.danger,
          title: l10n.revealFailedTitle,
          body: failureDetail(l10n, failure),
        ),
      if (state.integrity == DeviceIntegrity.compromised)
        StatusBanner(
          tone: BannerTone.info,
          title: l10n.revealIntegrityTitle,
          body: l10n.revealIntegrityBody,
        ),
    ];

    final Widget body = switch (state.unit) {
      Loading() => const Center(child: CircularProgressIndicator()),
      Failed(failure: NotFoundFailure()) => Center(
        child: SingleChildScrollView(
          child: EmptyState(
            icon: Icons.search_off,
            title: l10n.revealNotFoundTitle,
            body: l10n.revealNotFoundBody,
          ),
        ),
      ),
      Failed(:final failure) => FailureView(
        failure: failure,
        onRetry: () => unawaited(_controller.retryLoad()),
      ),
      Loaded(:final value) =>
        state.phase == RevealPhase.notFound
            ? Center(
                child: SingleChildScrollView(
                  child: EmptyState(
                    icon: Icons.search_off,
                    title: l10n.revealNotFoundTitle,
                    body: l10n.revealNotFoundBody,
                  ),
                ),
              )
            : ListView(
                padding: const EdgeInsetsDirectional.fromSTEB(
                  Spacing.s4,
                  Spacing.s2,
                  Spacing.s4,
                  Spacing.s6,
                ),
                children: [
                  for (final banner in banners) ...[
                    banner,
                    const SizedBox(height: Spacing.s3),
                  ],
                  if (!value.revealable) ...[
                    StatusBanner(
                      tone: BannerTone.info,
                      title: l10n.revealNotReady,
                    ),
                    const SizedBox(height: Spacing.s3),
                  ],
                  RevealCard(
                    info: value,
                    state: state,
                    clock: clock,
                    secureNote: ios
                        ? l10n.revealSecureNoteIos
                        : l10n.revealSecureNoteAndroid,
                    onReveal: () => unawaited(_controller.reveal(prompt)),
                    onCopy: () => unawaited(_controller.copyCode()),
                    onHide: _controller.hide,
                    onAutoHideDue: () =>
                        _controller.hide(RevealHideReason.timeout),
                    onRateLimitOver: _controller.rateLimitOver,
                    onOpenRedemption: () =>
                        unawaited(_controller.openRedemption()),
                    onReadAloud: () {
                      final code = state.code;
                      if (code != null) {
                        // Explicit action only; spaced so it is read character by character.
                        _announce(code.code.split('').join(' '));
                      }
                    },
                  ),
                  const SizedBox(height: Spacing.s5),
                  Semantics(
                    header: true,
                    child: Text(l10n.revealHowTo, style: type.headingSm),
                  ),
                  const SizedBox(height: Spacing.s2),
                  Text(
                    state.instructions ?? l10n.revealHowToAfterReveal,
                    style: type.bodyMd.copyWith(color: colors.contentSecondary),
                  ),
                ],
              ),
    };

    return Scaffold(
      appBar: MobileAppBar(title: l10n.revealTitle, onLeading: _back),
      body: Stack(
        fit: StackFit.expand,
        children: [
          body,
          // App switcher / inactive: the content is covered (owner decision 7). iOS also draws
          // a native blur before the snapshot; Android's FLAG_SECURE blanks the recents preview.
          if (state.obscured)
            Positioned.fill(
              child: Semantics(
                label: l10n.revealObscured,
                child: ColoredBox(
                  color: colors.surfaceCanvas,
                  child: Center(
                    child: Icon(
                      Icons.lock_outline,
                      size: 48,
                      color: colors.contentMuted,
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
      bottomNavigationBar: state.emailUnverified && !widget.guest
          ? SafeArea(
              child: Padding(
                padding: const EdgeInsetsDirectional.all(Spacing.s4),
                child: AppButton(
                  label: l10n.revealVerifyAction,
                  icon: Icons.mail_outline,
                  expand: true,
                  onPressed: () =>
                      unawaited(context.push(Routes.verifyEmail.path)),
                ),
              ),
            )
          : null,
    );
  }
}
