import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/date_format.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/features/orders/presentation/controllers/orders_controllers.dart';
import 'package:application/features/orders/presentation/widgets/order_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Opens the store's support page in the browser (no in-app support until M-A17).
Future<void> openSupport(BuildContext context, WidgetRef ref) async {
  final message = context.l10n.linkOpenFailed;
  final url = ref.read(latestAppConfigProvider)?.links.support;
  final opened =
      url != null && await ref.read(nativeBridgeProvider).openExternalUrl(url);
  if (!opened && context.mounted) {
    showAppSnackbar(context, message: message, tone: SnackbarTone.danger);
  }
}

/// Order detail (09.4, 09.5): status and date, lines with units (masked codes only), refund
/// notice, totals (display and charged), payment, timeline; retry payment, help and the refund
/// request entry.
class OrderDetailPage extends ConsumerWidget {
  const OrderDetailPage({required this.orderPublicId, super.key});

  final String orderPublicId;

  void _back(BuildContext context) =>
      context.canPop() ? context.pop() : context.go(Routes.orders.path);

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final provider = orderDetailControllerProvider(orderPublicId);
    final state = ref.watch(provider);
    final controller = ref.read(provider.notifier);
    final number = state.valueOrNull?.summary.number;
    return Scaffold(
      appBar: MobileAppBar(
        onLeading: () => _back(context),
        titleWidget: number == null
            ? null
            : Semantics(
                label: l10n.orderDetailSemantics(number),
                excludeSemantics: true,
                child: Directionality(
                  textDirection: TextDirection.ltr,
                  child: Text(number, style: context.type.headingMd),
                ),
              ),
      ),
      body: switch (state) {
        Loading() => const Padding(
          padding: EdgeInsetsDirectional.all(Spacing.s4),
          child: Column(
            children: [
              OrderTileSkeleton(),
              SizedBox(height: Spacing.s3),
              OrderTileSkeleton(),
            ],
          ),
        ),
        Failed(failure: NotFoundFailure()) => Center(
          child: SingleChildScrollView(
            child: EmptyState(
              icon: Icons.search_off,
              title: l10n.orderNotFoundTitle,
              body: l10n.orderNotFoundBody,
              actionLabel: l10n.ordersTitle,
              onAction: () => context.go(Routes.orders.path),
            ),
          ),
        ),
        Failed(:final failure) => FailureView(
          failure: failure,
          errorTitle: l10n.orderLoadErrorTitle,
          onRetry: () => unawaited(controller.retry()),
        ),
        Loaded(:final value) => RefreshIndicator(
          onRefresh: controller.refresh,
          child: _Detail(
            detail: value,
            onOpenUnit: (unit) => unawaited(
              context.push(
                Routes.codeRevealLocation(orderPublicId, unit.publicId),
              ),
            ),
            onRetryPayment: () {
              controller.retryPayment();
              context.go(Routes.paymentLocation(orderPublicId));
            },
            onHelp: () => unawaited(openSupport(context, ref)),
            onRefund: () => unawaited(
              context.push(Routes.refundRequestLocation(orderPublicId)),
            ),
            hasSupport:
                ref.watch(latestAppConfigProvider)?.links.support != null,
          ),
        ),
      },
    );
  }
}

/// The loaded detail; also used by the guest order page (11.4) without account actions.
class OrderDetailBody extends StatelessWidget {
  const OrderDetailBody({
    required this.detail,
    required this.onOpenUnit,
    this.header = const [],
    this.footer = const [],
    super.key,
  });

  final OrderDetail detail;
  final void Function(OrderUnit unit)? onOpenUnit;
  final List<Widget> header;
  final List<Widget> footer;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final summary = detail.summary;
    final (status, tone) = orderStatusView(l10n, summary.status);
    final latest = latestPayment(detail);
    final charged = summary.paymentTotal;
    final refunded = [
      for (final refund in detail.refunds)
        if (refund.status == 'succeeded') refund,
    ];
    final refundBanner = switch (detail.refundState) {
      'pending' => (BannerTone.warning, l10n.orderRefundPendingTitle),
      'partial' => (BannerTone.warning, l10n.orderRefundPartialTitle),
      'full' => (BannerTone.info, l10n.orderRefundFullTitle),
      _ => null,
    };
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsetsDirectional.fromSTEB(
        Spacing.s4,
        Spacing.s2,
        Spacing.s4,
        Spacing.s6,
      ),
      children: [
        ...header,
        Wrap(
          alignment: WrapAlignment.spaceBetween,
          crossAxisAlignment: WrapCrossAlignment.center,
          spacing: Spacing.s2,
          runSpacing: Spacing.s1,
          children: [
            StatusPill(label: status, tone: tone),
            Text(
              AppDates.full(context, summary.createdAt),
              style: type.bodySm.copyWith(color: colors.contentMuted),
            ),
          ],
        ),
        if (refundBanner != null) ...[
          const SizedBox(height: Spacing.s3),
          StatusBanner(
            tone: refundBanner.$1,
            title: refundBanner.$2,
            body: refunded.isEmpty
                ? null
                : [
                    for (final refund in refunded)
                      refund.destination == 'wallet'
                          ? l10n.orderRefundToWallet(
                              ltrIsolate(moneyText(refund.amount)),
                            )
                          : l10n.orderRefundToOriginal(
                              ltrIsolate(moneyText(refund.amount)),
                            ),
                  ].join('\n'),
          ),
        ],
        for (final line in detail.items) ...[
          const SizedBox(height: Spacing.s3),
          OrderLineCard(line: line, onOpenUnit: onOpenUnit),
        ],
        const SizedBox(height: Spacing.s3),
        OrderCard(
          child: Column(
            children: [
              SummaryRow(
                label: l10n.orderTotal,
                value: moneyText(summary.displayTotal),
                strong: true,
                ltrValue: true,
              ),
              if (charged != null &&
                  charged.currency != summary.displayTotal.currency)
                SummaryRow(
                  label: l10n.orderCharged,
                  value: moneyText(charged),
                  ltrValue: true,
                ),
              SummaryRow(
                label: l10n.orderPaymentStatus,
                value: paymentStatusLabel(l10n, summary.paymentStatus),
              ),
              if (latest != null)
                SummaryRow(
                  label: l10n.orderPayment,
                  value: [
                    if (latest.provider case final String provider)
                      gatewayName(l10n, provider),
                    ?latest.method,
                    l10n.orderAttempt(latest.attempt),
                  ].join(' · '),
                ),
            ],
          ),
        ),
        if (detail.timeline.isNotEmpty) ...[
          const SizedBox(height: Spacing.s5),
          OrderTimeline(entries: detail.timeline),
        ],
        ...footer,
      ],
    );
  }
}

class _Detail extends StatelessWidget {
  const _Detail({
    required this.detail,
    required this.onOpenUnit,
    required this.onRetryPayment,
    required this.onHelp,
    required this.onRefund,
    required this.hasSupport,
  });

  final OrderDetail detail;
  final void Function(OrderUnit unit) onOpenUnit;
  final VoidCallback onRetryPayment;
  final VoidCallback onHelp;
  final VoidCallback onRefund;
  final bool hasSupport;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final retry = canRetryPayment(detail);
    final refund = canRequestRefund(detail);
    return OrderDetailBody(
      detail: detail,
      onOpenUnit: onOpenUnit,
      header: [
        if (retry) ...[
          StatusBanner(
            tone: BannerTone.warning,
            title: l10n.orderStatusAwaitingPayment,
            body: l10n.orderAwaitingPaymentNote,
          ),
          const SizedBox(height: Spacing.s3),
        ],
      ],
      footer: [
        const SizedBox(height: Spacing.s5),
        if (retry) ...[
          AppButton(
            label: l10n.orderRetryPayment,
            icon: Icons.refresh,
            expand: true,
            onPressed: onRetryPayment,
          ),
          const SizedBox(height: Spacing.s2),
        ],
        if (hasSupport)
          AppButton(
            label: l10n.orderGetHelp,
            icon: Icons.help_outline,
            variant: AppButtonVariant.secondary,
            expand: true,
            onPressed: onHelp,
          ),
        if (refund) ...[
          const SizedBox(height: Spacing.s2),
          AppButton(
            label: l10n.orderRequestRefund,
            variant: AppButtonVariant.ghost,
            expand: true,
            onPressed: onRefund,
          ),
        ],
      ],
    );
  }
}
