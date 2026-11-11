import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/semantic_colors.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/date_format.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/shared/widgets/logo_slot.dart';
import 'package:application/shared/widgets/network_image_box.dart';
import 'package:application/shared/widgets/product_tile.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Colour family of a status pill (Figma `Order Status`).
enum StatusTone { success, info, warning, ember, danger, neutral }

/// Label and tone of an order status (API `orders.status`).
(String, StatusTone) orderStatusView(
  AppLocalizations l10n,
  String status,
) => switch (status) {
  'completed' => (l10n.orderStatusCompleted, StatusTone.success),
  'pending_payment' => (l10n.orderStatusAwaitingPayment, StatusTone.warning),
  'payment_processing' => (l10n.orderStatusPaymentProcessing, StatusTone.info),
  'partially_fulfilled' => (l10n.orderStatusPartlyDelivered, StatusTone.ember),
  'failed' => (l10n.orderStatusFailed, StatusTone.danger),
  'cancelled' => (l10n.orderStatusCancelled, StatusTone.neutral),
  'refund_pending' => (l10n.orderStatusRefundPending, StatusTone.ember),
  'partially_refunded' => (l10n.orderStatusPartiallyRefunded, StatusTone.ember),
  'refunded' => (l10n.orderStatusRefunded, StatusTone.neutral),
  // paid, processing, supplier_pending, pending_verification, manual_review and anything new.
  _ => (l10n.orderStatusProcessing, StatusTone.info),
};

/// Label of a status filter chip (null: all).
String orderFilterLabel(AppLocalizations l10n, String? status) =>
    status == null ? l10n.ordersFilterAll : orderStatusView(l10n, status).$1;

/// Label and tone of a unit status; a delivered unit seen before reads "Revealed".
(String, StatusTone) unitStatusView(AppLocalizations l10n, OrderUnit unit) =>
    switch (unit.status) {
      'delivered' when unit.firstRevealedAt != null => (
        l10n.orderUnitRevealed,
        StatusTone.neutral,
      ),
      'delivered' => (l10n.orderUnitDelivered, StatusTone.success),
      'failed' => (l10n.orderUnitFailed, StatusTone.danger),
      'refunded' => (l10n.orderUnitRefunded, StatusTone.warning),
      'refund_pending' => (l10n.orderUnitRefundPending, StatusTone.warning),
      'cancelled' => (l10n.orderUnitCancelled, StatusTone.neutral),
      _ => (l10n.orderUnitPreparing, StatusTone.info),
    };

/// Display name of a payment provider (`payments[].provider`); brands as plain text, no logos.
String gatewayName(AppLocalizations l10n, String provider) =>
    switch (provider) {
      'paypal' => 'PayPal',
      'paymob' => 'Paymob',
      'stripe' => 'Stripe',
      'wallet' => l10n.orderGatewayWallet,
      _ => provider,
    };

/// Payment status text (API `payment_status`).
String paymentStatusLabel(AppLocalizations l10n, String status) =>
    switch (status) {
      'paid' => l10n.orderPaymentPaid,
      'processing' => l10n.orderPaymentProcessing,
      'partially_refunded' => l10n.orderPaymentPartiallyRefunded,
      'refunded' => l10n.orderPaymentRefunded,
      _ => l10n.orderPaymentUnpaid,
    };

(Color, Color) _toneColors(SemanticColors colors, StatusTone tone) =>
    switch (tone) {
      StatusTone.success => (colors.statusSuccessTint, colors.statusSuccess),
      StatusTone.info => (colors.statusInfoTint, colors.statusInfo),
      StatusTone.warning => (colors.statusWarningTint, colors.statusWarning),
      StatusTone.ember => (colors.accentEmberTint, colors.accentEmberText),
      StatusTone.danger => (colors.statusDangerTint, colors.statusDanger),
      StatusTone.neutral => (colors.surfaceHover, colors.contentSecondary),
    };

/// Status pill: tinted background, dot and label (text, never colour alone).
class StatusPill extends StatelessWidget {
  const StatusPill({
    required this.label,
    required this.tone,
    this.dot = true,
    super.key,
  });

  final String label;
  final StatusTone tone;
  final bool dot;

  @override
  Widget build(BuildContext context) {
    final (tint, ink) = _toneColors(context.colors, tone);
    return Container(
      padding: const EdgeInsetsDirectional.symmetric(
        horizontal: Spacing.s2,
        vertical: Spacing.s0_5,
      ),
      decoration: BoxDecoration(
        color: tint,
        borderRadius: BorderRadius.circular(Radii.xs),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (dot) ...[
            Container(
              width: 6,
              height: 6,
              decoration: BoxDecoration(color: ink, shape: BoxShape.circle),
            ),
            const SizedBox(width: Spacing.s1_5),
          ],
          Text(label, style: context.type.labelSm.copyWith(color: ink)),
        ],
      ),
    );
  }
}

/// A card on `surface-raised` with the key-cut radius of the design.
class OrderCard extends StatelessWidget {
  const OrderCard({required this.child, this.padding, super.key});

  final Widget child;
  final EdgeInsetsGeometry? padding;

  @override
  Widget build(BuildContext context) => Container(
    padding: padding ?? const EdgeInsetsDirectional.all(Spacing.s3),
    decoration: BoxDecoration(
      color: context.colors.surfaceRaised,
      border: Border.all(color: context.colors.lineSubtle),
      borderRadius: BorderRadius.circular(Radii.md),
    ),
    child: child,
  );
}

/// Square thumbnail: the API image when there is one, else a token tile (BD8 Logo Slot outside
/// production).
class OrderThumbnail extends ConsumerWidget {
  const OrderThumbnail({required this.order, super.key});

  final OrderSummary order;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final colors = context.colors;
    final fallback = Container(
      color: fieldColorFor(order.number, colors),
      alignment: Alignment.center,
      child: ref.watch(showLogoSlotsProvider)
          ? const LogoSlot()
          : const SizedBox.shrink(),
    );
    return ClipRRect(
      borderRadius: BorderRadius.circular(Radii.sm),
      child: SizedBox.square(
        dimension: 48,
        child: NetworkImageBox(url: order.thumbnailUrl, fallback: fallback),
      ),
    );
  }
}

/// A row of the order list (09.1): thumbnail, number (mono), date and items, status, total.
class OrderTile extends StatelessWidget {
  const OrderTile({required this.order, required this.onTap, super.key});

  final OrderSummary order;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final (status, tone) = orderStatusView(l10n, order.status);
    final date = AppDates.day(context, order.createdAt);
    final total = moneyText(order.displayTotal);
    return Semantics(
      button: true,
      label: l10n.orderTileSemantics(order.number, status, date, total),
      excludeSemantics: true,
      onTap: onTap,
      child: Material(
        color: colors.surfaceRaised,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(Radii.md),
          side: BorderSide(color: colors.lineSubtle),
        ),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsetsDirectional.all(Spacing.s3),
            child: Row(
              children: [
                OrderThumbnail(order: order),
                const SizedBox(width: Spacing.s3),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Directionality(
                        textDirection: TextDirection.ltr,
                        child: Text(order.number, style: type.monoMd),
                      ),
                      const SizedBox(height: Spacing.s1),
                      Text(
                        '$date · ${l10n.ordersItemCount(order.itemCount)}',
                        style: type.bodySm.copyWith(
                          color: colors.contentSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: Spacing.s2),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    StatusPill(label: status, tone: tone),
                    const SizedBox(height: Spacing.s1),
                    Directionality(
                      textDirection: TextDirection.ltr,
                      child: Text(total, style: type.priceSm),
                    ),
                  ],
                ),
                const SizedBox(width: Spacing.s1),
                // chevron_right mirrors itself in RTL (matchTextDirection).
                Icon(Icons.chevron_right, color: colors.contentMuted),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// Loading row (09.1 next page, 09.3).
class OrderTileSkeleton extends StatelessWidget {
  const OrderTileSkeleton({super.key});

  @override
  Widget build(BuildContext context) => const OrderCard(
    child: Row(
      children: [
        SkeletonBlock(width: 48, height: 48),
        SizedBox(width: Spacing.s3),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              SkeletonBlock(width: 180),
              SizedBox(height: Spacing.s2),
              SkeletonBlock(width: 120),
            ],
          ),
        ),
      ],
    ),
  );
}

/// A unit row of the order detail (09.4): key icon, "Code" and the masked code, status, chevron
/// when it opens the code screen.
class UnitRow extends StatelessWidget {
  const UnitRow({
    required this.unit,
    required this.index,
    required this.productName,
    this.onTap,
    super.key,
  });

  final OrderUnit unit;
  final int index;
  final String productName;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final (status, tone) = unitStatusView(l10n, unit);
    final masked = unit.maskedCode;
    return Semantics(
      button: onTap != null,
      label: l10n.orderUnitSemantics(index, productName, status),
      hint: onTap == null ? null : l10n.orderUnitOpenHint,
      excludeSemantics: true,
      onTap: onTap,
      child: Material(
        color: colors.surfaceSunken,
        borderRadius: BorderRadius.circular(Radii.sm),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          child: ConstrainedBox(
            constraints: const BoxConstraints(minHeight: 56),
            child: Padding(
              padding: const EdgeInsetsDirectional.symmetric(
                horizontal: Spacing.s3,
                vertical: Spacing.s2,
              ),
              child: Row(
                children: [
                  Icon(Icons.key_outlined, color: colors.contentSecondary),
                  const SizedBox(width: Spacing.s3),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          l10n.orderUnitCode,
                          style: type.bodyCaption.copyWith(
                            color: colors.contentSecondary,
                          ),
                        ),
                        Directionality(
                          textDirection: TextDirection.ltr,
                          child: Text(
                            masked == null || masked.isEmpty ? '—' : masked,
                            style: type.monoMd,
                          ),
                        ),
                      ],
                    ),
                  ),
                  StatusPill(label: status, tone: tone, dot: false),
                  if (onTap != null) ...[
                    const SizedBox(width: Spacing.s1),
                    // chevron_right mirrors itself in RTL (matchTextDirection).
                    Icon(Icons.chevron_right, color: colors.contentMuted),
                  ],
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// An order line with its units (09.4).
class OrderLineCard extends StatelessWidget {
  const OrderLineCard({
    required this.line,
    required this.onOpenUnit,
    this.onWriteReview,
    super.key,
  });

  final OrderLine line;

  /// "Write a review": only for a line the API lists as eligible (M-A17).
  final VoidCallback? onWriteReview;

  /// Opens a unit's code screen; null for units that have no code yet.
  final void Function(OrderUnit unit)? onOpenUnit;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final unitPrice = line.unitPrice;
    final lineTotal = line.lineTotal;
    return OrderCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(child: Text(line.name, style: type.headingSm)),
              if (lineTotal != null) ...[
                const SizedBox(width: Spacing.s2),
                Directionality(
                  textDirection: TextDirection.ltr,
                  child: Text(moneyText(lineTotal), style: type.priceSm),
                ),
              ],
            ],
          ),
          const SizedBox(height: Spacing.s1),
          Text(
            line.quantity > 1 && unitPrice != null
                ? l10n.orderQtyEach(
                    line.quantity,
                    ltrIsolate(moneyText(unitPrice)),
                  )
                : l10n.orderQty(line.quantity),
            style: type.bodySm.copyWith(color: colors.contentSecondary),
          ),
          for (var i = 0; i < line.units.length; i++) ...[
            const SizedBox(height: Spacing.s2),
            UnitRow(
              unit: line.units[i],
              index: i + 1,
              productName: line.name,
              onTap: onOpenUnit == null || !_opens(line.units[i])
                  ? null
                  : () => onOpenUnit!(line.units[i]),
            ),
          ],
          if (onWriteReview != null) ...[
            const SizedBox(height: Spacing.s1),
            Align(
              alignment: AlignmentDirectional.centerStart,
              child: TextButton.icon(
                key: ValueKey('order-line-review-${line.id}'),
                style: TextButton.styleFrom(
                  minimumSize: const Size(48, 48),
                  foregroundColor: colors.accentText,
                ),
                onPressed: onWriteReview,
                icon: const Icon(Icons.star_border, size: 18),
                label: Text(
                  l10n.reviewWriteAction,
                  semanticsLabel: l10n.reviewWriteSemantics(line.name),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  /// A unit opens the code screen when the server says it holds a code (`revealable`); the
  /// reveal route still checks everything.
  static bool _opens(OrderUnit unit) => unit.revealable;
}

/// Label of a timeline status.
String timelineLabel(AppLocalizations l10n, String status) => switch (status) {
  'pending_payment' => l10n.orderTimelinePlaced,
  'paid' => l10n.orderTimelinePaid,
  _ => orderStatusView(l10n, status).$1,
};

/// The status changes of an order (09.5).
class OrderTimeline extends StatelessWidget {
  const OrderTimeline({required this.entries, super.key});

  final List<OrderTimelineEntry> entries;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Semantics(
          header: true,
          child: Text(l10n.orderTimeline, style: type.headingSm),
        ),
        const SizedBox(height: Spacing.s2),
        for (final entry in entries)
          MergeSemantics(
            child: ConstrainedBox(
              constraints: const BoxConstraints(minHeight: 48),
              child: Row(
                children: [
                  Container(
                    width: 10,
                    height: 10,
                    decoration: BoxDecoration(
                      color: colors.accentPrimary,
                      shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(width: Spacing.s3),
                  Expanded(
                    child: Text(
                      timelineLabel(l10n, entry.status),
                      style: type.bodyMd,
                    ),
                  ),
                  if (entry.at != null)
                    Text(
                      AppDates.dayTime(context, entry.at!),
                      style: type.bodySm.copyWith(color: colors.contentMuted),
                    ),
                ],
              ),
            ),
          ),
      ],
    );
  }
}

/// One label/value row of a summary card.
class SummaryRow extends StatelessWidget {
  const SummaryRow({
    required this.label,
    required this.value,
    this.strong = false,
    this.ltrValue = false,
    super.key,
  });

  final String label;
  final String value;
  final bool strong;
  final bool ltrValue;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final type = context.type;
    final text = Text(
      value,
      style: strong
          ? type.priceSm
          : type.bodySm.copyWith(color: colors.contentSecondary),
      textAlign: TextAlign.end,
    );
    return MergeSemantics(
      child: Padding(
        padding: const EdgeInsetsDirectional.symmetric(vertical: Spacing.s1),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: strong
                  ? type.bodyMd
                  : type.bodySm.copyWith(color: colors.contentSecondary),
            ),
            const SizedBox(width: Spacing.s3),
            Expanded(
              child: Align(
                alignment: AlignmentDirectional.centerEnd,
                child: ltrValue
                    ? Directionality(
                        textDirection: TextDirection.ltr,
                        child: text,
                      )
                    : text,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// A refund request's status on the order detail (M-A17).
(String, BannerTone) refundRequestBanner(
  AppLocalizations l10n,
  String status,
) => switch (status) {
  'approved' => (l10n.refundStatusApproved, BannerTone.info),
  'rejected' => (l10n.refundStatusRejected, BannerTone.danger),
  'completed' => (l10n.refundStatusCompleted, BannerTone.success),
  _ => (l10n.refundStatusRequested, BannerTone.warning),
};
