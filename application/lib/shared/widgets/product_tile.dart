import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/semantic_colors.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:application/shared/widgets/logo_slot.dart';
import 'package:application/shared/widgets/network_image_box.dart';
import 'package:flutter/material.dart';

/// Field colour of a plain-text art tile for [key] (a platform slug), stable across runs, from
/// the `color/field/*` tokens (BD8: no brand art).
Color fieldColorFor(String key, SemanticColors colors) {
  final palette = [
    colors.fieldUltra,
    colors.fieldVolt,
    colors.fieldEmber,
    colors.fieldAqua,
    colors.fieldFlux,
    colors.fieldSun,
    colors.fieldInk,
  ];
  var hash = 0;
  for (final unit in key.codeUnits) {
    hash = (hash * 31 + unit) & 0x7fffffff;
  }
  return palette[hash % palette.length];
}

/// Directional key-cut of the product tile: 14 on the top-end corner, 6 elsewhere.
const _tileRadius = BorderRadiusDirectional.only(
  topStart: Radius.circular(Radii.sm),
  topEnd: Radius.circular(Radii.lg),
  bottomStart: Radius.circular(Radii.sm),
  bottomEnd: Radius.circular(Radii.sm),
);

/// Product tile (Figma `Mobile / Product Tile` `76:385`): a 112dp art block (the API image, else
/// a plain-text platform tile on a field colour with an optional empty Logo Slot), the delivery
/// badge, title, category and the server's "from" price. A card without a price is shown as
/// unavailable (Sold out styling). One button node for screen readers: "title, platform, price".
class ProductTile extends StatelessWidget {
  const ProductTile({
    required this.product,
    required this.onTap,
    this.showLogoSlot = false,
    super.key,
  });

  final ProductCard product;
  final VoidCallback onTap;

  /// Empty Logo Slot on the art when there is no image (staging and dev only, BD8).
  final bool showLogoSlot;

  static const artHeight = 112.0;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final price = product.priceFrom;
    final priceText = price == null
        ? l10n.productUnavailable
        : moneyText(price);
    final platform = product.platform?.name;
    final subtitle = product.category?.name;
    final badge = deliveryBadge(l10n, product.deliveryType);
    final art = ColoredBox(
      color: fieldColorFor(product.platform?.slug ?? product.slug, colors),
    );
    final label = [product.name, ?platform, priceText].join(', ');

    return Semantics(
      button: true,
      label: label,
      excludeSemantics: true,
      onTap: onTap,
      child: Material(
        color: colors.surfaceRaised,
        shape: const RoundedRectangleBorder(borderRadius: _tileRadius),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            mainAxisSize: MainAxisSize.min,
            children: [
              SizedBox(
                height: artHeight,
                child: ClipRRect(
                  borderRadius: _tileRadius,
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      NetworkImageBox(url: product.image?.url, fallback: art),
                      Padding(
                        padding: const EdgeInsetsDirectional.all(Spacing.s2_5),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                if (showLogoSlot && product.image == null)
                                  const LogoSlot(),
                                // The badge keeps to the end and shortens with large text.
                                Expanded(
                                  child: Align(
                                    alignment: AlignmentDirectional.topEnd,
                                    heightFactor: 1,
                                    child: badge == null
                                        ? null
                                        : TileBadge(label: badge),
                                  ),
                                ),
                              ],
                            ),
                            const Spacer(),
                            if (platform != null && product.image == null)
                              Text(
                                platform,
                                style: type.labelSm,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsetsDirectional.fromSTEB(
                  Spacing.s2_5,
                  Spacing.s2_5,
                  Spacing.s2_5,
                  Spacing.s3,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      product.name,
                      style: type.labelMd.copyWith(
                        color: price == null
                            ? colors.contentMuted
                            : colors.contentPrimary,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    if (subtitle != null) ...[
                      const SizedBox(height: Spacing.s1),
                      Text(
                        subtitle,
                        style: type.bodyCaption.copyWith(
                          color: colors.contentMuted,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                    const SizedBox(height: Spacing.s1),
                    if (price == null)
                      Text(
                        priceText,
                        style: type.labelMd.copyWith(
                          color: colors.statusDanger,
                        ),
                      )
                    else
                      // Amounts are never mirrored (LTR island inside RTL text).
                      Directionality(
                        textDirection: TextDirection.ltr,
                        child: Text(priceText, style: type.priceSm),
                      ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// The delivery badge text of a product card, or null for types without one.
String? deliveryBadge(AppLocalizations l10n, String deliveryType) =>
    switch (deliveryType) {
      'instant_code' => l10n.badgeInstant,
      'direct_topup' || 'account_recharge' => l10n.badgeTopup,
      'subscription_activation' => l10n.badgeSubscription,
      _ => null,
    };

/// Volt badge with the small key-cut (library `Badge`, tone Volt).
class TileBadge extends StatelessWidget {
  const TileBadge({required this.label, super.key});

  final String label;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return Container(
      padding: const EdgeInsetsDirectional.symmetric(
        horizontal: Spacing.s2,
        vertical: 3,
      ),
      decoration: BoxDecoration(
        color: colors.accentPrimaryTint,
        borderRadius: const BorderRadiusDirectional.only(
          topStart: Radius.circular(2),
          topEnd: Radius.circular(8),
          bottomStart: Radius.circular(2),
          bottomEnd: Radius.circular(2),
        ),
      ),
      child: Text(
        label,
        style: context.type.labelSm.copyWith(color: colors.accentText),
        maxLines: 1,
        overflow: TextOverflow.ellipsis,
      ),
    );
  }
}
