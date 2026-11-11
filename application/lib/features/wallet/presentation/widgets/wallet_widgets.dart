import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/currency_info.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/date_format.dart';
import 'package:application/features/wallet/domain/entities/wallet.dart';
import 'package:application/features/wallet/presentation/controllers/wallet_controller.dart';
import 'package:application/shared/widgets/skeleton.dart' show SkeletonRegion;
import 'package:flutter/material.dart';

/// The label of a statement line's type (API `wallet_transactions.type`).
String walletTypeLabel(AppLocalizations l10n, String type) => switch (type) {
  'credit_refund' => l10n.walletTypeCreditRefund,
  'credit_admin' => l10n.walletTypeCreditAdmin,
  'debit_purchase' => l10n.walletTypeDebitPurchase,
  'debit_admin' => l10n.walletTypeDebitAdmin,
  'reversal' => l10n.walletTypeReversal,
  _ => l10n.walletTypeOther,
};

/// A statement line's title: the type, and the order number when the line belongs to an order
/// ("Refund · BX-1002"; the number is an LTR island in Arabic).
String walletLineTitle(AppLocalizations l10n, WalletTransaction line) {
  final type = walletTypeLabel(l10n, line.type);
  final order = line.order;
  return order == null
      ? type
      : l10n.walletLineWithOrder(type, ltrIsolate(order.orderNumber));
}

/// The balance card (12.1 to 12.3): "Store credit", the status badge, the balance as server
/// money and the no-top-up note. Volt outline while active; muted and neutral when frozen.
class WalletBalanceCard extends StatelessWidget {
  const WalletBalanceCard({
    required this.wallet,
    required this.currency,
    super.key,
  });

  final Wallet wallet;
  final CurrencyInfo currency;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final frozen = wallet.status == 'frozen';
    final balance = moneyText(wallet.balance, currencies: [currency]);
    final status = frozen ? l10n.walletStatusFrozen : l10n.walletStatusActive;
    return Semantics(
      container: true,
      label: l10n.walletBalanceSemantics(balance, status),
      excludeSemantics: true,
      child: Container(
        padding: const EdgeInsetsDirectional.all(Spacing.s5),
        decoration: ShapeDecoration(
          color: colors.surfaceRaised,
          shape: KeyCut.cardShape(
            side: BorderSide(
              color: frozen ? colors.lineDefault : colors.accentPrimary,
            ),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    l10n.walletStoreCredit,
                    style: type.labelMd.copyWith(
                      color: colors.contentSecondary,
                    ),
                  ),
                ),
                WalletStatusBadge(label: status, frozen: frozen),
              ],
            ),
            const SizedBox(height: Spacing.s2),
            FittedBox(
              fit: BoxFit.scaleDown,
              alignment: AlignmentDirectional.centerStart,
              child: Text(
                balance,
                textDirection: TextDirection.ltr,
                style: type.priceXl.copyWith(
                  color: frozen ? colors.contentMuted : colors.contentPrimary,
                ),
              ),
            ),
            const SizedBox(height: Spacing.s2),
            Text(
              l10n.walletBalanceNote,
              style: type.bodySm.copyWith(color: colors.contentMuted),
            ),
          ],
        ),
      ),
    );
  }
}

/// Library `Badge` (Success or Warning) with the small key-cut.
class WalletStatusBadge extends StatelessWidget {
  const WalletStatusBadge({
    required this.label,
    required this.frozen,
    super.key,
  });

  final String label;
  final bool frozen;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final (tint, ink) = frozen
        ? (colors.statusWarningTint, colors.statusWarning)
        : (colors.statusSuccessTint, colors.statusSuccess);
    return Container(
      padding: const EdgeInsetsDirectional.symmetric(
        horizontal: Spacing.s2,
        vertical: 3,
      ),
      decoration: BoxDecoration(
        color: tint,
        borderRadius: const BorderRadiusDirectional.only(
          topStart: Radius.circular(2),
          topEnd: Radius.circular(8),
          bottomStart: Radius.circular(2),
          bottomEnd: Radius.circular(2),
        ),
      ),
      child: Text(label, style: context.type.labelSm.copyWith(color: ink)),
    );
  }
}

/// A statement row (12.1): credit (arrow down on the success tint) or debit (arrow up), the
/// type with the order number, the date, the signed amount and the balance after. A row linked
/// to an order opens its detail; the whole row is the 64dp target.
class WalletStatementRow extends StatelessWidget {
  const WalletStatementRow({
    required this.line,
    required this.currency,
    this.onOpenOrder,
    super.key,
  });

  final WalletTransaction line;
  final CurrencyInfo currency;

  /// Set when the line belongs to an order.
  final VoidCallback? onOpenOrder;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final credit = line.isCredit;
    final title = walletLineTitle(l10n, line);
    final amount = walletAmountText(line.amount, currency, signed: true);
    final balance = walletAmountText(line.balanceAfter, currency);
    final date = AppDates.full(context, line.createdAt);
    final note = line.order == null ? line.note : null;
    final row = ConstrainedBox(
      constraints: const BoxConstraints(minHeight: 64),
      child: Padding(
        padding: const EdgeInsetsDirectional.symmetric(vertical: Spacing.s2),
        child: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: credit
                    ? colors.statusSuccessTint
                    : colors.surfaceOverlay,
                shape: BoxShape.circle,
              ),
              child: Icon(
                credit ? Icons.arrow_downward : Icons.arrow_upward,
                size: 18,
                color: credit ? colors.statusSuccess : colors.contentSecondary,
              ),
            ),
            const SizedBox(width: Spacing.s3),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: type.labelMd),
                  if (note != null && note.isNotEmpty) ...[
                    const SizedBox(height: Spacing.s0_5),
                    Text(
                      note,
                      style: type.bodyCaption.copyWith(
                        color: colors.contentSecondary,
                      ),
                    ),
                  ],
                  const SizedBox(height: Spacing.s0_5),
                  Text(
                    date,
                    style: type.bodyCaption.copyWith(
                      color: colors.contentMuted,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: Spacing.s3),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  amount,
                  textDirection: TextDirection.ltr,
                  style: type.labelMd.copyWith(
                    color: credit
                        ? colors.statusSuccess
                        : colors.contentPrimary,
                  ),
                ),
                const SizedBox(height: Spacing.s0_5),
                Text(
                  balance,
                  textDirection: TextDirection.ltr,
                  style: type.bodyCaption.copyWith(color: colors.contentMuted),
                ),
              ],
            ),
            if (onOpenOrder != null) ...[
              const SizedBox(width: Spacing.s1),
              // Mirrors by itself in RTL (`matchTextDirection`).
              Icon(Icons.chevron_right, color: colors.contentMuted),
            ],
          ],
        ),
      ),
    );
    return Semantics(
      container: true,
      button: onOpenOrder != null,
      label: l10n.walletLineSemantics(title, amount, balance, date),
      hint: onOpenOrder != null ? l10n.walletLineOpenOrderHint : null,
      excludeSemantics: true,
      onTap: onOpenOrder,
      child: onOpenOrder == null
          ? row
          : InkWell(
              onTap: onOpenOrder,
              borderRadius: BorderRadius.circular(Radii.md),
              child: row,
            ),
    );
  }
}

/// Library `Mobile / Skeleton` (List tile): the next statement page loading.
class WalletRowSkeleton extends StatelessWidget {
  const WalletRowSkeleton({super.key});

  @override
  Widget build(BuildContext context) => Container(
    height: 64,
    padding: const EdgeInsetsDirectional.symmetric(
      horizontal: Spacing.s3,
      vertical: Spacing.s2,
    ),
    decoration: BoxDecoration(
      color: context.colors.surfaceRaised,
      borderRadius: BorderRadius.circular(Radii.md),
    ),
    child: const Row(
      children: [
        _Shimmer(width: 40, height: 40, radius: Radii.md),
        SizedBox(width: Spacing.s3),
        Expanded(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _Shimmer(width: 180),
              SizedBox(height: Spacing.s2),
              _Shimmer(width: 120),
            ],
          ),
        ),
      ],
    ),
  );
}

/// The whole screen loading: the balance card shape and statement rows.
class WalletSkeleton extends StatelessWidget {
  const WalletSkeleton({super.key});

  @override
  Widget build(BuildContext context) => SkeletonRegion(
    child: ListView(
      padding: const EdgeInsetsDirectional.fromSTEB(
        Spacing.s4,
        Spacing.s2,
        Spacing.s4,
        Spacing.s6,
      ),
      children: [
        Container(
          height: 168,
          padding: const EdgeInsetsDirectional.all(Spacing.s5),
          decoration: ShapeDecoration(
            color: context.colors.surfaceRaised,
            shape: KeyCut.cardShape(),
          ),
          child: const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _Shimmer(width: 96),
              SizedBox(height: Spacing.s4),
              _Shimmer(width: 160, height: 40),
              SizedBox(height: Spacing.s4),
              _Shimmer(width: 240),
            ],
          ),
        ),
        const SizedBox(height: Spacing.s6),
        for (var i = 0; i < 4; i++) ...[
          const WalletRowSkeleton(),
          const SizedBox(height: Spacing.s3),
        ],
      ],
    ),
  );
}

/// A shimmer block on `surface-overlay`, drawn on a `surface-raised` skeleton (library
/// `Mobile / Skeleton`).
class _Shimmer extends StatelessWidget {
  const _Shimmer({
    required this.width,
    this.height = 12,
    this.radius = Radii.sm,
  });

  final num width;
  final num height;
  final num radius;

  @override
  Widget build(BuildContext context) => Container(
    width: width.toDouble(),
    height: height.toDouble(),
    decoration: BoxDecoration(
      color: context.colors.surfaceOverlay,
      borderRadius: BorderRadius.circular(radius.toDouble()),
    ),
  );
}
