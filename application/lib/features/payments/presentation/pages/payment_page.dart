import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/motion.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/semantic_colors.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/payments/payment_flow.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart' show DateFormat;

/// Payment in progress and results of one order (07.6, 07.7, 08.1 to 08.4). Renders
/// [PaymentFlow]; "paid" appears only when the server said so.
class PaymentPage extends ConsumerWidget {
  const PaymentPage({required this.orderPublicId, super.key});

  final String orderPublicId;

  Future<void> _leave(BuildContext context, WidgetRef ref, String to) async {
    await ref.read(paymentFlowProvider.notifier).close();
    if (context.mounted) {
      context.go(to);
    }
  }

  Future<void> _support(BuildContext context, WidgetRef ref) async {
    final message = context.l10n.linkOpenFailed;
    final url = ref.read(latestAppConfigProvider)?.links.support;
    final opened =
        url != null &&
        await ref.read(nativeBridgeProvider).openExternalUrl(url);
    if (!opened && context.mounted) {
      showAppSnackbar(context, message: message, tone: SnackbarTone.danger);
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final flow = ref.read(paymentFlowProvider.notifier);
    final current = ref.watch(paymentFlowProvider);
    // Another order's flow (or none): this screen only offers to ask the server.
    final state = current.orderPublicId == orderPublicId
        ? current
        : PaymentFlowState(orderPublicId: orderPublicId);
    final gateway = state.gatewayLabel ?? l10n.paymentProviderFallback;
    final hasSupport =
        ref.watch(latestAppConfigProvider)?.links.support != null;

    final view = _viewFor(l10n, state, gateway, colors);
    final busy =
        state.phase == PaymentPhase.confirming ||
        state.phase == PaymentPhase.opening ||
        state.retrying;

    final actions = <Widget>[
      ...switch (state.phase) {
        PaymentPhase.idle => [
          AppButton(
            label: l10n.paymentCheckStatus,
            expand: true,
            onPressed: () => unawaited(flow.resume(orderPublicId)),
          ),
        ],
        PaymentPhase.opening ||
        PaymentPhase.awaitingProvider ||
        PaymentPhase.confirming => [
          if (state.providerUrl != null)
            AppButton(
              label: l10n.paymentReturnTo(gateway),
              icon: Icons.open_in_new,
              expand: true,
              onPressed: busy ? null : () => unawaited(flow.reopenProvider()),
            ),
          AppButton(
            label: l10n.paymentCheckStatus,
            variant: state.providerUrl != null
                ? AppButtonVariant.ghost
                : AppButtonVariant.primary,
            expand: true,
            loading: state.phase == PaymentPhase.confirming,
            onPressed: busy ? null : () => unawaited(flow.checkStatus()),
          ),
        ],
        PaymentPhase.paid => [
          AppButton(
            label: l10n.paymentViewOrder,
            expand: true,
            onPressed: () => unawaited(
              _leave(context, ref, Routes.orderLocation(orderPublicId)),
            ),
          ),
          AppButton(
            label: l10n.paymentKeepShopping,
            variant: AppButtonVariant.ghost,
            expand: true,
            onPressed: () => unawaited(_leave(context, ref, Routes.home.path)),
          ),
        ],
        PaymentPhase.processing => [
          AppButton(
            label: l10n.paymentCheckAgain,
            icon: Icons.refresh,
            variant: AppButtonVariant.secondary,
            expand: true,
            onPressed: () => unawaited(flow.checkStatus()),
          ),
          AppButton(
            label: l10n.paymentGoToOrders,
            variant: AppButtonVariant.ghost,
            expand: true,
            onPressed: () =>
                unawaited(_leave(context, ref, Routes.orders.path)),
          ),
        ],
        PaymentPhase.failed || PaymentPhase.notStarted => [
          if (state.retryable)
            AppButton(
              label: state.phase == PaymentPhase.notStarted
                  ? l10n.actionTryAgain
                  : l10n.paymentTryAgain,
              icon: Icons.refresh,
              expand: true,
              loading: state.retrying,
              onPressed: busy ? null : () => unawaited(flow.retryPayment()),
            ),
          AppButton(
            label: l10n.checkoutBackToCart,
            variant: state.retryable
                ? AppButtonVariant.ghost
                : AppButtonVariant.primary,
            expand: true,
            onPressed: () => unawaited(_leave(context, ref, Routes.cart.path)),
          ),
        ],
        PaymentPhase.expired => [
          AppButton(
            label: l10n.paymentReturnToCart,
            expand: true,
            onPressed: () => unawaited(_leave(context, ref, Routes.cart.path)),
          ),
          if (hasSupport)
            AppButton(
              label: l10n.paymentContactSupport,
              variant: AppButtonVariant.ghost,
              expand: true,
              onPressed: () => unawaited(_support(context, ref)),
            ),
        ],
        PaymentPhase.unsupported => [
          AppButton(
            label: l10n.paymentCheckStatus,
            expand: true,
            onPressed: () => unawaited(flow.checkStatus()),
          ),
          AppButton(
            label: l10n.checkoutBackToCart,
            variant: AppButtonVariant.ghost,
            expand: true,
            onPressed: () => unawaited(_leave(context, ref, Routes.cart.path)),
          ),
        ],
      },
    ];

    final lastChecked = state.lastCheckedAt;
    final failure = state.failure;
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, _) {
        if (!didPop) {
          unawaited(_leave(context, ref, Routes.home.path));
        }
      },
      child: Scaffold(
        backgroundColor: colors.surfaceCanvas,
        appBar: MobileAppBar(
          leading: AppBarLeading.close,
          onLeading: () => unawaited(_leave(context, ref, Routes.home.path)),
        ),
        body: ListView(
          padding: const EdgeInsetsDirectional.fromSTEB(
            Spacing.s4,
            Spacing.s4,
            Spacing.s4,
            Spacing.s6,
          ),
          children: [
            _StatusIcon(view: view, busy: busy && !state.isResult),
            const SizedBox(height: Spacing.s5),
            Semantics(
              header: true,
              liveRegion: true,
              child: Text(view.title, style: type.displaySm),
            ),
            const SizedBox(height: Spacing.s3),
            Text(
              view.body,
              style: type.bodyMd.copyWith(color: colors.contentSecondary),
            ),
            const SizedBox(height: Spacing.s4),
            _SummaryCard(state: state, gateway: state.gatewayLabel),
            if (state.phase == PaymentPhase.processing &&
                lastChecked != null) ...[
              const SizedBox(height: Spacing.s3),
              Text(
                l10n.paymentLastChecked(
                  ltrIsolate(DateFormat.Hm('en').format(lastChecked.toLocal())),
                ),
                style: type.bodySm.copyWith(color: colors.contentMuted),
              ),
            ],
            ..._notices(context, state, gateway),
            if (failure != null) ...[
              const SizedBox(height: Spacing.s3),
              StatusBanner(
                tone: BannerTone.danger,
                title: state.phase == PaymentPhase.notStarted
                    ? l10n.paymentNotStartedTitle
                    : l10n.paymentCallFailedTitle,
                body: failureDetail(l10n, failure),
              ),
            ],
          ],
        ),
        bottomNavigationBar: DecoratedBox(
          decoration: BoxDecoration(
            color: colors.surfaceBase,
            border: Border(top: BorderSide(color: colors.lineSubtle)),
          ),
          child: SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsetsDirectional.symmetric(
                horizontal: Spacing.s4,
                vertical: Spacing.s3,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  for (var i = 0; i < actions.length; i++) ...[
                    if (i > 0) const SizedBox(height: Spacing.s2),
                    actions[i],
                  ],
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  List<Widget> _notices(
    BuildContext context,
    PaymentFlowState state,
    String gateway,
  ) {
    final l10n = context.l10n;
    final notices = <Widget>[];
    void add(BannerTone tone, String title, String? body) => notices
      ..add(const SizedBox(height: Spacing.s3))
      ..add(StatusBanner(tone: tone, title: title, body: body));
    switch (state.phase) {
      case PaymentPhase.opening ||
          PaymentPhase.awaitingProvider ||
          PaymentPhase.confirming:
        if (state.openFailed) {
          add(BannerTone.danger, l10n.paymentOpenFailed, null);
        }
        add(
          BannerTone.info,
          l10n.paymentDontPayTwiceTitle,
          l10n.paymentDontPayTwiceBody,
        );
      case PaymentPhase.paid:
        add(
          BannerTone.success,
          l10n.paymentReceiptTitle,
          l10n.paymentReceiptBody,
        );
      case PaymentPhase.failed when state.attemptsExhausted:
        add(BannerTone.warning, l10n.paymentAttemptsExhausted, null);
      case PaymentPhase.failed when state.retryable:
        add(
          BannerTone.info,
          state.attempt == null
              ? l10n.paymentAnotherMethodTitle
              : l10n.paymentAttempt(state.attempt!),
          l10n.paymentAnotherMethodBody,
        );
      case _:
        break;
    }
    return notices;
  }
}

/// Title, body and icon of a phase.
@immutable
final class _View {
  const _View({
    required this.title,
    required this.body,
    required this.icon,
    required this.color,
  });

  final String title;
  final String body;
  final IconData icon;
  final Color color;
}

_View _viewFor(
  AppLocalizations l10n,
  PaymentFlowState state,
  String gateway,
  SemanticColors colors,
) {
  final expires = state.expiresAt;
  final heldUntil = expires == null
      ? null
      : l10n.paymentHeldUntil(
          ltrIsolate(DateFormat.Hm('en').format(expires.toLocal())),
        );
  return switch (state.phase) {
    PaymentPhase.idle => _View(
      title: l10n.paymentIdleTitle,
      body: l10n.paymentIdleBody,
      icon: Icons.receipt_long_outlined,
      color: colors.statusInfo,
    ),
    PaymentPhase.opening || PaymentPhase.awaitingProvider => _View(
      title: l10n.paymentInProgressTitle,
      body: l10n.paymentInProgressBody(gateway),
      icon: Icons.open_in_new,
      color: colors.statusInfo,
    ),
    PaymentPhase.confirming => _View(
      title: l10n.paymentConfirmingTitle,
      body: l10n.paymentConfirmingBody,
      icon: Icons.schedule,
      color: colors.statusInfo,
    ),
    PaymentPhase.paid => _View(
      title: l10n.paymentPaidTitle,
      body: l10n.paymentPaidBody,
      icon: Icons.check,
      color: colors.statusSuccess,
    ),
    PaymentPhase.processing => _View(
      title: l10n.paymentProcessingTitle,
      body: l10n.paymentProcessingBody,
      icon: Icons.schedule,
      color: colors.statusInfo,
    ),
    PaymentPhase.failed => _View(
      title: l10n.paymentFailedTitle,
      body: [
        if (!state.retryable)
          l10n.paymentFailedFinalBody
        else if (state.cancelledByCustomer)
          l10n.paymentCancelledBody
        else
          l10n.paymentFailedBody,
        if (state.retryable && heldUntil != null) heldUntil,
      ].join(' '),
      icon: Icons.close,
      color: colors.statusDanger,
    ),
    PaymentPhase.expired => _View(
      title: l10n.paymentExpiredTitle,
      body: l10n.paymentExpiredBody,
      icon: Icons.schedule,
      color: colors.statusWarning,
    ),
    PaymentPhase.notStarted => _View(
      title: l10n.paymentNotStartedTitle,
      body: [l10n.paymentNotStartedBody(gateway), ?heldUntil].join(' '),
      icon: Icons.warning_amber_rounded,
      color: colors.statusDanger,
    ),
    PaymentPhase.unsupported => _View(
      title: l10n.paymentUnsupportedTitle,
      body: l10n.paymentUnsupportedBody,
      icon: Icons.info_outline,
      color: colors.statusWarning,
    ),
  };
}

class _StatusIcon extends StatelessWidget {
  const _StatusIcon({required this.view, required this.busy});

  final _View view;
  final bool busy;

  @override
  Widget build(BuildContext context) => ExcludeSemantics(
    child: Align(
      alignment: AlignmentDirectional.centerStart,
      child: SizedBox.square(
        dimension: 68,
        child: Stack(
          alignment: Alignment.center,
          children: [
            DecoratedBox(
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: view.color.withValues(alpha: 0.16),
                border: Border.all(color: view.color, width: 2),
              ),
              child: SizedBox.square(
                dimension: 68,
                child: Icon(view.icon, color: view.color, size: 30),
              ),
            ),
            if (busy && !MotionTokens.reduced(context))
              SizedBox.square(
                dimension: 68,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  color: view.color,
                ),
              ),
          ],
        ),
      ),
    ),
  );
}

/// Order, amount charged, gateway and status (text and colour, never colour alone).
class _SummaryCard extends StatelessWidget {
  const _SummaryCard({required this.state, required this.gateway});

  final PaymentFlowState state;
  final String? gateway;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final (statusText, statusColor) = _status(l10n, colors, state);
    Widget row(String label, Widget value) => Padding(
      padding: const EdgeInsetsDirectional.symmetric(vertical: Spacing.s1_5),
      child: MergeSemantics(
        child: Row(
          children: [
            Expanded(
              child: Text(
                label,
                style: type.bodySm.copyWith(color: colors.contentSecondary),
              ),
            ),
            value,
          ],
        ),
      ),
    );
    return DecoratedBox(
      decoration: BoxDecoration(
        color: colors.surfaceRaised,
        borderRadius: BorderRadius.circular(Radii.md),
      ),
      child: Padding(
        padding: const EdgeInsetsDirectional.symmetric(
          horizontal: Spacing.s3,
          vertical: Spacing.s2,
        ),
        child: Column(
          children: [
            row(
              l10n.paymentSummaryOrder,
              Text(
                state.orderNumber ?? '—',
                textDirection: TextDirection.ltr,
                style: type.monoSm,
              ),
            ),
            row(
              l10n.paymentSummaryAmount,
              Text(
                state.paymentTotal == null
                    ? '—'
                    : moneyText(state.paymentTotal!),
                textDirection: TextDirection.ltr,
                style: type.labelMd,
              ),
            ),
            row(
              l10n.paymentSummaryPaidWith,
              Text(gateway ?? '—', style: type.labelMd),
            ),
            row(
              l10n.paymentSummaryStatus,
              DecoratedBox(
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.16),
                  borderRadius: BorderRadius.circular(Radii.xs),
                ),
                child: Padding(
                  padding: const EdgeInsetsDirectional.symmetric(
                    horizontal: Spacing.s2,
                    vertical: Spacing.s0_5,
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.circle, size: 6, color: statusColor),
                      const SizedBox(width: Spacing.s1),
                      Text(
                        statusText,
                        style: type.labelSm.copyWith(color: statusColor),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  static (String, Color) _status(
    AppLocalizations l10n,
    SemanticColors colors,
    PaymentFlowState state,
  ) => switch (state.phase) {
    PaymentPhase.paid => (l10n.paymentStatusPaid, colors.statusSuccess),
    PaymentPhase.failed => (l10n.paymentStatusFailed, colors.statusDanger),
    PaymentPhase.expired => (
      l10n.paymentStatusExpired,
      colors.contentSecondary,
    ),
    PaymentPhase.processing
        when state.lastStatus?.status == 'pending_payment' =>
      (l10n.paymentStatusAwaiting, colors.statusWarning),
    PaymentPhase.processing || PaymentPhase.confirming => (
      l10n.paymentStatusProcessing,
      colors.statusInfo,
    ),
    PaymentPhase.idle => (l10n.paymentStatusUnknown, colors.contentSecondary),
    _ => (l10n.paymentStatusAwaiting, colors.statusWarning),
  };
}
