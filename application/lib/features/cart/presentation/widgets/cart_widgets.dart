import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/cart/presentation/controllers/cart_controller.dart';
import 'package:application/shared/domain/entities/cart.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/fit_amount.dart';
import 'package:application/shared/widgets/quantity_stepper.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:flutter/material.dart';

/// The customer-facing text of a line's `block_reason` (06.2).
String blockReasonText(AppLocalizations l10n, String reason) =>
    switch (reason) {
      'unavailable' => l10n.blockUnavailable,
      'out_of_stock' => l10n.blockOutOfStock,
      'custom_amount_unsupported' => l10n.blockCustomAmount,
      'quantity_limit' => l10n.blockQuantityLimit,
      'not_sold_in_country' => l10n.blockNotSoldInCountry,
      'rate_stale' || 'rate_missing' => l10n.blockPricing,
      _ => l10n.blockOther,
    };

/// A server amount as an LTR island (owner decision 2).
class MoneyLabel extends StatelessWidget {
  const MoneyLabel(this.money, {this.style, super.key});

  final Money money;
  final TextStyle? style;

  @override
  Widget build(BuildContext context) => Text(
    moneyText(money),
    textDirection: TextDirection.ltr,
    style: style ?? context.type.labelMd,
  );
}

/// A cart line (06.1, 06.2): name, amount and SKU, delivery values, block reason, the line total,
/// the quantity stepper and remove. One semantics group for the line; quantity and remove are
/// separate 48dp actions.
class CartLineCard extends StatelessWidget {
  const CartLineCard({
    required this.line,
    required this.busy,
    required this.onQuantity,
    required this.onRemove,
    this.error,
    super.key,
  });

  final CartLine line;
  final bool busy;
  final String? error;
  final ValueChanged<int> onQuantity;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final amount = line.total ?? line.subtotal;
    final reason = line.blockReason;
    final blocked = !line.purchasable || reason != null;
    final values = line.deliveryValues.values.where((v) => v.isNotEmpty);
    return DecoratedBox(
      decoration: BoxDecoration(
        color: colors.surfaceRaised,
        borderRadius: BorderRadius.circular(Radii.md),
        border: Border.all(color: colors.lineSubtle),
      ),
      child: Padding(
        padding: const EdgeInsetsDirectional.fromSTEB(
          Spacing.s3,
          Spacing.s3,
          Spacing.s1,
          Spacing.s3,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: MergeSemantics(
                    child: Semantics(
                      label: amount == null
                          ? null
                          : l10n.cartLineSemantics(
                              line.productName,
                              line.variantTitle,
                              moneyText(amount),
                              line.quantity,
                            ),
                      excludeSemantics: amount != null,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(line.productName, style: type.labelLg),
                          const SizedBox(height: Spacing.s0_5),
                          Text(
                            line.variantTitle,
                            style: type.bodySm.copyWith(
                              color: colors.contentSecondary,
                            ),
                          ),
                          if (line.variantSku != null)
                            Text(
                              line.variantSku!,
                              textDirection: TextDirection.ltr,
                              style: type.monoSm.copyWith(
                                color: colors.contentMuted,
                              ),
                            ),
                          if (values.isNotEmpty)
                            Text(
                              values.join(' · '),
                              textDirection: TextDirection.ltr,
                              style: type.monoSm.copyWith(
                                color: colors.contentSecondary,
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                ),
                SizedBox.square(
                  dimension: 48,
                  child: IconButton(
                    tooltip: l10n.cartRemoveItem(line.productName),
                    icon: const Icon(Icons.delete_outline, size: 20),
                    color: colors.contentSecondary,
                    onPressed: busy ? null : onRemove,
                  ),
                ),
              ],
            ),
            if (reason != null || blocked) ...[
              const SizedBox(height: Spacing.s2),
              Padding(
                padding: const EdgeInsetsDirectional.only(end: Spacing.s2),
                child: _Problem(
                  text: blockReasonText(l10n, reason ?? 'unavailable'),
                ),
              ),
            ],
            if (error != null) ...[
              const SizedBox(height: Spacing.s2),
              Padding(
                padding: const EdgeInsetsDirectional.only(end: Spacing.s2),
                child: _Problem(text: error!),
              ),
            ],
            const SizedBox(height: Spacing.s2),
            Row(
              children: [
                Expanded(
                  child: amount == null
                      ? const SizedBox.shrink()
                      : Align(
                          alignment: AlignmentDirectional.centerStart,
                          child: MoneyLabel(
                            amount,
                            style: type.priceSm.copyWith(
                              color: blocked
                                  ? colors.contentMuted
                                  : colors.contentPrimary,
                            ),
                          ),
                        ),
                ),
                QuantityStepper(
                  quantity: line.quantity,
                  max: maxLineQuantity,
                  enabled: !busy,
                  onIncrement: () => onQuantity(line.quantity + 1),
                  onDecrement: () => onQuantity(line.quantity - 1),
                ),
                const SizedBox(width: Spacing.s2),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _Problem extends StatelessWidget {
  const _Problem({required this.text});

  final String text;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(Icons.warning_amber_rounded, size: 16, color: colors.statusDanger),
        const SizedBox(width: Spacing.s1_5),
        Expanded(
          child: Text(
            text,
            style: context.type.bodySm.copyWith(color: colors.statusDanger),
          ),
        ),
      ],
    );
  }
}

/// Coupon code (06.1, 06.2): field with Apply, or the applied code with Remove; the server's
/// refusal under the field.
class CouponSection extends StatelessWidget {
  const CouponSection({
    required this.coupon,
    required this.controller,
    required this.busy,
    required this.onApply,
    required this.onRemove,
    this.error,
    super.key,
  });

  final CartCoupon? coupon;
  final TextEditingController controller;
  final bool busy;
  final String? error;
  final VoidCallback onApply;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final applied = coupon != null && coupon!.applied;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Expanded(
              child: applied
                  ? _AppliedCode(
                      label: l10n.cartCouponLabel,
                      code: coupon!.code,
                    )
                  : AppTextField(
                      key: const ValueKey('coupon-field'),
                      label: l10n.cartCouponLabel,
                      controller: controller,
                      ltr: true,
                      enabled: !busy,
                      textCapitalization: TextCapitalization.characters,
                      textInputAction: TextInputAction.done,
                      onSubmitted: (_) => onApply(),
                      errorText: error,
                    ),
            ),
            const SizedBox(width: Spacing.s2),
            Padding(
              padding: EdgeInsetsDirectional.only(
                bottom: !applied && error != null ? Spacing.s6 : 0,
              ),
              child: AppButton(
                label: applied ? l10n.cartCouponRemove : l10n.cartCouponApply,
                variant: AppButtonVariant.secondary,
                loading: busy,
                onPressed: busy ? null : (applied ? onRemove : onApply),
              ),
            ),
          ],
        ),
        if (applied) ...[
          const SizedBox(height: Spacing.s1_5),
          Text(
            l10n.cartCouponApplied(coupon!.code),
            style: type.bodySm.copyWith(color: colors.statusSuccess),
          ),
          if (coupon!.limitedByMargin)
            Text(
              l10n.cartCouponLimited,
              style: type.bodySm.copyWith(color: colors.contentSecondary),
            ),
        ] else if (coupon != null && coupon!.error != null && error == null)
          Padding(
            padding: const EdgeInsetsDirectional.only(top: Spacing.s1_5),
            child: Text(
              coupon!.error!,
              style: type.bodySm.copyWith(color: colors.statusDanger),
            ),
          ),
      ],
    );
  }
}

/// The applied code in a field-shaped box with the success check (06.1).
class _AppliedCode extends StatelessWidget {
  const _AppliedCode({required this.label, required this.code});

  final String label;
  final String code;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final type = context.type;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Text(
          label,
          style: type.labelSm.copyWith(color: colors.contentSecondary),
        ),
        const SizedBox(height: Spacing.s1_5),
        Container(
          constraints: const BoxConstraints(minHeight: 48),
          padding: const EdgeInsetsDirectional.symmetric(
            horizontal: Spacing.s3,
          ),
          decoration: BoxDecoration(
            color: colors.surfaceSunken,
            borderRadius: BorderRadius.circular(Radii.sm),
            border: Border.all(color: colors.statusSuccess),
          ),
          child: Row(
            children: [
              Expanded(child: Text(ltrIsolate(code), style: type.bodyMd)),
              Icon(Icons.check, size: 18, color: colors.statusSuccess),
            ],
          ),
        ),
      ],
    );
  }
}

/// Server totals (06.1): subtotal, discount when there is one, total.
class CartTotalsCard extends StatelessWidget {
  const CartTotalsCard({required this.cart, super.key});

  final Cart cart;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final totals = cart.totals;
    final hasDiscount = totals.discount.amount.sign != 0;
    final code = cart.coupon?.applied ?? false ? cart.coupon!.code : null;
    Widget row(String label, Widget value, {TextStyle? style}) => Padding(
      padding: const EdgeInsetsDirectional.symmetric(vertical: Spacing.s1),
      // The amount scales down rather than overflow at large text; never cut (M-A19).
      child: Row(
        children: [
          Expanded(child: Text(label, style: style)),
          const SizedBox(width: Spacing.s2),
          FitAmount(child: value),
        ],
      ),
    );
    return DecoratedBox(
      decoration: BoxDecoration(
        color: colors.surfaceRaised,
        borderRadius: BorderRadius.circular(Radii.md),
      ),
      child: Padding(
        padding: const EdgeInsetsDirectional.all(Spacing.s3),
        child: Column(
          children: [
            row(
              l10n.cartSubtotal,
              MoneyLabel(totals.subtotal, style: type.bodySm),
              style: type.bodySm.copyWith(color: colors.contentSecondary),
            ),
            if (hasDiscount)
              row(
                code == null ? l10n.cartDiscount : l10n.cartDiscountCode(code),
                Text(
                  '-${moneyText(totals.discount).replaceFirst('-', '')}',
                  textDirection: TextDirection.ltr,
                  style: type.bodySm.copyWith(color: colors.statusSuccess),
                ),
                style: type.bodySm.copyWith(color: colors.statusSuccess),
              ),
            Divider(color: colors.lineSubtle, height: Spacing.s4),
            row(
              l10n.cartTotal,
              MoneyLabel(totals.total, style: type.priceMd),
              style: type.labelLg,
            ),
          ],
        ),
      ),
    );
  }
}

/// Loading state of the cart (06.4).
class CartSkeleton extends StatelessWidget {
  const CartSkeleton({super.key});

  @override
  Widget build(BuildContext context) => SkeletonRegion(
    child: ListView(
      padding: const EdgeInsetsDirectional.all(Spacing.s4),
      children: [
        for (var i = 0; i < 2; i++) ...[
          const SkeletonBlock(height: 96, radius: Radii.md),
          const SizedBox(height: Spacing.s3),
        ],
        const SizedBox(height: Spacing.s3),
        const SkeletonBlock(width: 160),
        const SizedBox(height: Spacing.s3),
        const SkeletonBlock(height: 14),
        const SizedBox(height: Spacing.s2),
        const FractionallySizedBox(
          widthFactor: 0.7,
          alignment: AlignmentDirectional.centerStart,
          child: SkeletonBlock(height: 14),
        ),
      ],
    ),
  );
}
