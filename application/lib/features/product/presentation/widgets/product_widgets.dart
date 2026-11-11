import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/product/domain/delivery_field_rules.dart';
import 'package:application/features/product/domain/entities/product.dart';
import 'package:application/features/product/domain/variant_options.dart';
import 'package:application/features/product/presentation/controllers/product_controllers.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_chip.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/logo_slot.dart';
import 'package:application/shared/widgets/network_image_box.dart';
import 'package:application/shared/widgets/option_tile.dart';
import 'package:application/shared/widgets/product_tile.dart';
import 'package:application/shared/widgets/quantity_stepper.dart';
import 'package:application/shared/widgets/sheet_header.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/trailing_limit.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

/// Key-cut of the product art: the `radius/key` corner on the top end, `radius/sm` elsewhere.
const _artRadius = BorderRadiusDirectional.only(
  topStart: Radius.circular(Radii.sm),
  topEnd: Radius.circular(Radii.key),
  bottomStart: Radius.circular(Radii.sm),
  bottomEnd: Radius.circular(Radii.sm),
);

/// Key-cut of amount tiles, the quantity stepper and buttons (`radius/lg` on the top end).
const _tileRadius = BorderRadiusDirectional.only(
  topStart: Radius.circular(Radii.sm),
  topEnd: Radius.circular(Radii.lg),
  bottomStart: Radius.circular(Radii.sm),
  bottomEnd: Radius.circular(Radii.sm),
);

/// Product art (05.1): the API gallery (`images[].url`, swipeable with page dots when there are
/// several), else the plain-text tile of BD8 on a `color/field/*` token with the platform name,
/// the category, the delivery badge and (outside production) the empty Logo Slot.
class ProductGallery extends StatefulWidget {
  const ProductGallery({
    required this.product,
    this.showLogoSlot = false,
    super.key,
  });

  final ProductDetail product;
  final bool showLogoSlot;

  static const height = 180.0;

  @override
  State<ProductGallery> createState() => _ProductGalleryState();
}

class _ProductGalleryState extends State<ProductGallery> {
  int _page = 0;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final card = widget.product.card;
    final images = widget.product.images.isNotEmpty
        ? widget.product.images
        : [?card.image];
    final badge = deliveryBadge(l10n, card.deliveryType);
    // The plain-text tile (BD8) shows until an API image has loaded, and instead of one.
    final fallback = ColoredBox(
      color: fieldColorFor(card.platform?.slug ?? card.slug, colors),
      child: Padding(
        padding: const EdgeInsetsDirectional.all(Spacing.s4),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (widget.showLogoSlot) const LogoSlot(),
            const Spacer(),
            Text(
              card.platform?.name ?? card.name,
              style: type.displaySm,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
            if (card.category != null)
              Text(
                card.category!.name,
                style: type.labelSm.copyWith(color: colors.contentSecondary),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
          ],
        ),
      ),
    );

    Widget slide(MediaImage? image) => Stack(
      fit: StackFit.expand,
      children: [
        NetworkImageBox(
          url: image?.url,
          fallback: fallback,
          semanticLabel: image?.alt ?? card.name,
        ),
        if (badge != null)
          PositionedDirectional(
            top: Spacing.s4,
            end: Spacing.s4,
            child: TileBadge(label: badge),
          ),
      ],
    );

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        SizedBox(
          height: ProductGallery.height,
          child: ClipRRect(
            borderRadius: _artRadius,
            child: images.length <= 1
                ? slide(images.firstOrNull)
                : PageView.builder(
                    itemCount: images.length,
                    onPageChanged: (page) => setState(() => _page = page),
                    itemBuilder: (context, index) => Semantics(
                      label: l10n.galleryImage(index + 1, images.length),
                      child: slide(images[index]),
                    ),
                  ),
          ),
        ),
        if (images.length > 1) ...[
          const SizedBox(height: Spacing.s2),
          ExcludeSemantics(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                for (var i = 0; i < images.length; i++)
                  Container(
                    width: i == _page ? 16 : 6,
                    height: 6,
                    margin: const EdgeInsetsDirectional.symmetric(
                      horizontal: 2,
                    ),
                    decoration: BoxDecoration(
                      color: i == _page
                          ? colors.accentPrimary
                          : colors.lineStrong,
                      borderRadius: BorderRadius.circular(Radii.full),
                    ),
                  ),
              ],
            ),
          ),
        ],
      ],
    );
  }
}

/// Title block (05.1): "Category · Platform", the name, the rating and the delivery time.
class ProductTitleBlock extends StatelessWidget {
  const ProductTitleBlock({required this.product, super.key});

  final ProductDetail product;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final card = product.card;
    final trail = [?card.category?.name, ?card.platform?.name].join(' · ');
    final minutes = product.deliveryTimeMinutes;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (trail.isNotEmpty)
          Text(trail, style: type.labelSm.copyWith(color: colors.contentMuted)),
        const SizedBox(height: Spacing.s1),
        Semantics(header: true, child: Text(card.name, style: type.displaySm)),
        const SizedBox(height: Spacing.s1),
        Row(
          children: [
            Expanded(
              child: product.rating.count == 0
                  ? const SizedBox.shrink()
                  : Semantics(
                      label:
                          '${l10n.productAverageRating(ratingLabel(product.rating.average))}, '
                          '${l10n.productReviewsCount(product.rating.count)}',
                      excludeSemantics: true,
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.star, size: 16, color: colors.accentText),
                          const SizedBox(width: Spacing.s1_5),
                          Text(
                            ratingLabel(product.rating.average),
                            style: type.labelMd,
                            textDirection: TextDirection.ltr,
                          ),
                          const SizedBox(width: Spacing.s1_5),
                          Flexible(
                            child: Text(
                              '(${l10n.productReviewsCount(product.rating.count)})',
                              style: type.bodySm.copyWith(
                                color: colors.contentMuted,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
            ),
            if (minutes != null)
              TrailingLimit(
                widthFactor: 0.5,
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.bolt, size: 16, color: colors.accentText),
                    const SizedBox(width: Spacing.s1),
                    Flexible(
                      child: Text(
                        l10n.deliveryTime(minutes),
                        style: type.bodySm.copyWith(
                          color: colors.contentSecondary,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
          ],
        ),
      ],
    );
  }
}

/// Field label above a selector (`label/md`, secondary).
class FieldLabel extends StatelessWidget {
  const FieldLabel(this.text, {super.key});

  final String text;

  @override
  Widget build(BuildContext context) => Semantics(
    header: true,
    child: Text(
      text,
      style: context.type.labelMd.copyWith(
        color: context.colors.contentSecondary,
      ),
    ),
  );
}

/// Region chips (05.1): one radio chip per region of the variants.
class RegionSelector extends StatelessWidget {
  const RegionSelector({
    required this.regions,
    required this.selected,
    required this.onSelected,
    super.key,
  });

  final List<RegionOption> regions;
  final String? selected;
  final ValueChanged<String?> onSelected;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    return Wrap(
      spacing: Spacing.s2,
      children: [
        for (final region in regions)
          AppChip(
            label: region.name ?? l10n.productAnyRegion,
            selected: region.code == selected,
            onTap: () => onSelected(region.code),
          ),
      ],
    );
  }
}

/// The text under an amount: the server's price ("Pay USD10.00") or why it cannot be bought.
String amountDetail(AppLocalizations l10n, VariantOffer variant) {
  final price = variant.price;
  return switch (blockOf(variant)) {
    VariantBlock.soldOut => l10n.variantSoldOut,
    VariantBlock.customAmount => l10n.variantCustomAmount,
    VariantBlock.unavailable => l10n.variantUnavailable,
    null when price != null => l10n.productPay(ltrIsolate(moneyText(price))),
    null => l10n.variantUnavailable,
  };
}

/// Amount tiles (05.1, Option Tile semantics): three per row; amounts that cannot be bought
/// stay visible, dimmed and disabled with their reason ("Sold out").
class AmountSelector extends StatelessWidget {
  const AmountSelector({
    required this.amounts,
    required this.selected,
    required this.onSelected,
    super.key,
  });

  final List<VariantOffer> amounts;
  final String? selected;
  final ValueChanged<String> onSelected;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    return LayoutBuilder(
      builder: (context, constraints) {
        const columns = 3;
        final width =
            (constraints.maxWidth - Spacing.s2 * (columns - 1)) / columns;
        return Wrap(
          spacing: Spacing.s2,
          runSpacing: Spacing.s2,
          children: [
            for (final variant in amounts)
              SizedBox(
                width: width,
                child: AmountTile(
                  title: variant.title,
                  detail: amountDetail(l10n, variant),
                  selected: variant.publicId == selected,
                  enabled: blockOf(variant) == null,
                  onTap: () => onSelected(variant.publicId),
                ),
              ),
          ],
        );
      },
    );
  }
}

/// One amount: the variant title and the price or reason; selected tiles are volt-tinted with a
/// 2px volt border. Announced as a radio of the amount group.
class AmountTile extends StatelessWidget {
  const AmountTile({
    required this.title,
    required this.detail,
    required this.selected,
    required this.enabled,
    required this.onTap,
    super.key,
  });

  final String title;
  final String detail;
  final bool selected;
  final bool enabled;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    return Semantics(
      inMutuallyExclusiveGroup: true,
      checked: selected,
      enabled: enabled,
      button: true,
      label: l10n.variantSemantics(title, detail),
      excludeSemantics: true,
      onTap: enabled ? onTap : null,
      child: Opacity(
        opacity: enabled ? 1 : 0.45,
        child: Material(
          color: selected ? colors.accentPrimaryTint : colors.surfaceRaised,
          shape: RoundedRectangleBorder(
            borderRadius: _tileRadius,
            side: BorderSide(
              color: selected ? colors.accentPrimary : colors.lineDefault,
              width: selected ? 2 : 1,
            ),
          ),
          clipBehavior: Clip.antiAlias,
          child: InkWell(
            onTap: enabled ? onTap : null,
            // 76dp at default text size; grows with large text instead of clipping (M-A19).
            child: ConstrainedBox(
              constraints: const BoxConstraints(minHeight: 76),
              child: Padding(
                padding: const EdgeInsetsDirectional.symmetric(
                  horizontal: Spacing.s1,
                  vertical: Spacing.s2,
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    FittedBox(
                      fit: BoxFit.scaleDown,
                      child: Text(title, style: type.priceMd, maxLines: 1),
                    ),
                    const SizedBox(height: Spacing.s0_5),
                    Text(
                      detail,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: type.bodyCaption.copyWith(
                        color: selected
                            ? colors.accentText
                            : colors.contentMuted,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// Localized text of a delivery field problem: the server's own message, or the local check.
String fieldIssueText(
  AppLocalizations l10n,
  DeliveryField field,
  FieldIssue issue,
) => switch (issue) {
  ServerFieldIssue(:final message) => message,
  LocalFieldIssue(:final error) => switch (error) {
    DeliveryFieldError.required => l10n.deliveryFieldRequired(field.label),
    DeliveryFieldError.tooLong => l10n.deliveryFieldTooLong(
      field.label,
      field.maxLength ?? defaultDeliveryMaxLength,
    ),
    DeliveryFieldError.notDigits => l10n.deliveryFieldDigits(field.label),
    DeliveryFieldError.invalidEmail => l10n.deliveryFieldEmail,
    DeliveryFieldError.invalidOption => l10n.deliveryFieldOption,
    DeliveryFieldError.invalidFormat => l10n.deliveryFieldFormat(field.label),
  },
};

/// Arabic-Indic and Persian digits typed into a `number` field become Western digits.
class WesternDigitsFormatter extends TextInputFormatter {
  const WesternDigitsFormatter();

  @override
  TextEditingValue formatEditUpdate(
    TextEditingValue oldValue,
    TextEditingValue newValue,
  ) {
    final text = westernDigits(newValue.text);
    return text == newValue.text ? newValue : newValue.copyWith(text: text);
  }
}

/// The delivery-field form (05.1, 05.3), generated from `delivery_fields`: label, help text,
/// keyboard per type (`number` digits, `email`), select fields as a sheet of options, and the
/// local or server error beside each field.
class DeliveryFieldsForm extends StatelessWidget {
  const DeliveryFieldsForm({
    required this.fields,
    required this.values,
    required this.issues,
    required this.controllerFor,
    required this.keyFor,
    required this.onChanged,
    super.key,
  });

  final List<DeliveryField> fields;
  final Map<String, String> values;
  final Map<String, FieldIssue> issues;
  final TextEditingController Function(String key) controllerFor;
  final GlobalKey Function(String key) keyFor;
  final void Function(String key, String value) onChanged;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (var i = 0; i < fields.length; i++) ...[
          if (i > 0) const SizedBox(height: Spacing.s4),
          KeyedSubtree(
            key: keyFor(fields[i].key),
            child: _field(context, l10n, fields[i]),
          ),
        ],
      ],
    );
  }

  Widget _field(BuildContext context, AppLocalizations l10n, DeliveryField f) {
    final issue = issues[f.key];
    final error = issue == null ? null : fieldIssueText(l10n, f, issue);
    if (f.type == DeliveryFieldType.select) {
      return SelectField(
        field: f,
        value: values[f.key],
        errorText: error,
        onChanged: (value) => onChanged(f.key, value),
      );
    }
    final number = f.type == DeliveryFieldType.number;
    final email = f.type == DeliveryFieldType.email;
    return AppTextField(
      label: f.label,
      controller: controllerFor(f.key),
      helperText: f.helpText,
      errorText: error,
      ltr: number || email,
      keyboardType: number
          ? TextInputType.number
          : email
          ? TextInputType.emailAddress
          : TextInputType.text,
      autofillHints: email ? const [AutofillHints.email] : null,
      inputFormatters: number ? const [WesternDigitsFormatter()] : null,
      textInputAction: TextInputAction.done,
      onChanged: (value) => onChanged(f.key, value),
    );
  }
}

/// A `select` delivery field: looks like the Input, opens a sheet with one Option Tile per
/// `options[]` entry.
class SelectField extends StatelessWidget {
  const SelectField({
    required this.field,
    required this.value,
    required this.onChanged,
    this.errorText,
    super.key,
  });

  final DeliveryField field;
  final String? value;
  final String? errorText;
  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final options = field.options ?? const <DeliveryFieldOption>[];
    final chosen = options.where((o) => o.value == value).firstOrNull;
    final hasError = errorText != null && errorText!.isNotEmpty;
    Future<void> open() async {
      final picked = await showModalBottomSheet<String>(
        context: context,
        isScrollControlled: true,
        backgroundColor: colors.surfaceRaised,
        builder: (sheet) => SafeArea(
          child: ConstrainedBox(
            constraints: BoxConstraints(
              maxHeight: MediaQuery.sizeOf(sheet).height * 0.7,
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                SheetHeader(title: field.label),
                Flexible(
                  child: ListView.separated(
                    shrinkWrap: true,
                    padding: const EdgeInsetsDirectional.all(Spacing.s4),
                    itemCount: options.length,
                    separatorBuilder: (_, _) =>
                        const SizedBox(height: Spacing.s2),
                    itemBuilder: (context, index) => OptionTile(
                      title: options[index].label,
                      selected: options[index].value == value,
                      onTap: () =>
                          Navigator.of(sheet).pop(options[index].value),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
      if (picked != null) {
        onChanged(picked);
      }
    }

    return Semantics(
      button: true,
      label: [
        field.label,
        chosen?.label ?? l10n.selectPlaceholder,
        ?errorText,
      ].join(', '),
      excludeSemantics: true,
      onTap: () => unawaited(open()),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            field.label,
            style: type.labelSm.copyWith(color: colors.contentSecondary),
          ),
          const SizedBox(height: Spacing.s1_5),
          Material(
            color: colors.surfaceSunken,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(Radii.sm),
              side: BorderSide(
                color: hasError ? colors.statusDanger : colors.lineStrong,
                width: hasError ? 1.5 : 1,
              ),
            ),
            child: InkWell(
              onTap: () => unawaited(open()),
              child: ConstrainedBox(
                constraints: const BoxConstraints(minHeight: 48),
                child: Padding(
                  padding: const EdgeInsetsDirectional.symmetric(
                    horizontal: Spacing.s3,
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: Text(
                          chosen?.label ?? l10n.selectPlaceholder,
                          style: type.bodyMd.copyWith(
                            color: chosen == null
                                ? colors.contentMuted
                                : colors.contentPrimary,
                          ),
                        ),
                      ),
                      Icon(Icons.expand_more, color: colors.contentSecondary),
                    ],
                  ),
                ),
              ),
            ),
          ),
          if (hasError) ...[
            const SizedBox(height: Spacing.s1_5),
            Text(
              errorText!,
              style: type.bodyCaption.copyWith(color: colors.statusDanger),
            ),
          ] else if (field.helpText != null) ...[
            const SizedBox(height: Spacing.s1_5),
            Text(
              field.helpText!,
              style: type.bodyCaption.copyWith(color: colors.contentMuted),
            ),
          ],
        ],
      ),
    );
  }
}

/// Text section (instructions, terms): a heading and plain text with its line breaks, escaped.
class TextSection extends StatelessWidget {
  const TextSection({required this.title, required this.child, super.key});

  final String title;
  final Widget child;

  @override
  Widget build(BuildContext context) => Column(
    crossAxisAlignment: CrossAxisAlignment.stretch,
    children: [
      Semantics(
        header: true,
        child: Text(title, style: context.type.headingSm),
      ),
      const SizedBox(height: Spacing.s2),
      child,
    ],
  );
}

/// Sticky footer of the product page: the selected amount's server price, the quantity and
/// "Add to cart" (disabled while nothing can be bought, busy while adding).
class ProductFooter extends StatelessWidget {
  const ProductFooter({
    required this.state,
    required this.onIncrement,
    required this.onDecrement,
    required this.onAdd,
    super.key,
  });

  final ProductPageState state;
  final VoidCallback onIncrement;
  final VoidCallback onDecrement;
  final VoidCallback onAdd;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final price = state.selected?.price;
    final priceText = price == null
        ? null
        : state.quantity > 1
        ? l10n.priceTimesQuantity(moneyText(price), state.quantity)
        : moneyText(price);
    final quantityNote =
        state.quantityError ??
        (state.quantity >= state.maxQuantity && state.maxQuantity > 1
            ? l10n.quantityLimit(state.maxQuantity)
            : null);
    return DecoratedBox(
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
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          l10n.priceLabel,
                          style: type.bodyCaption.copyWith(
                            color: colors.contentMuted,
                          ),
                        ),
                        if (priceText != null)
                          // Amounts are LTR islands, never mirrored.
                          Directionality(
                            textDirection: TextDirection.ltr,
                            child: Text(
                              priceText,
                              style: type.priceMd,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          )
                        else
                          Text(
                            l10n.variantUnavailable,
                            style: type.labelMd.copyWith(
                              color: colors.contentMuted,
                            ),
                          ),
                      ],
                    ),
                  ),
                  QuantityStepper(
                    quantity: state.quantity,
                    max: state.maxQuantity,
                    enabled: state.selected != null && !state.adding,
                    onIncrement: onIncrement,
                    onDecrement: onDecrement,
                  ),
                ],
              ),
              if (quantityNote != null) ...[
                const SizedBox(height: Spacing.s1),
                Semantics(
                  liveRegion: state.quantityError != null,
                  child: Text(
                    quantityNote,
                    textAlign: TextAlign.end,
                    style: type.bodyCaption.copyWith(
                      color: state.quantityError != null
                          ? colors.statusDanger
                          : colors.contentMuted,
                    ),
                  ),
                ),
              ],
              const SizedBox(height: Spacing.s2),
              AppButton(
                label: l10n.addToCart,
                icon: Icons.shopping_bag_outlined,
                expand: true,
                loading: state.adding,
                onPressed: state.canAdd ? onAdd : null,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Skeleton of the product page (05.5).
class ProductSkeleton extends StatelessWidget {
  const ProductSkeleton({super.key});

  @override
  Widget build(BuildContext context) => const SkeletonRegion(
    child: Padding(
      padding: EdgeInsetsDirectional.fromSTEB(
        Spacing.s4,
        Spacing.s2,
        Spacing.s4,
        Spacing.s6,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SkeletonBlock(height: ProductGallery.height, radius: Radii.lg),
          SizedBox(height: Spacing.s6),
          FractionallySizedBox(widthFactor: 0.3, child: SkeletonBlock()),
          SizedBox(height: Spacing.s3),
          FractionallySizedBox(
            widthFactor: 0.7,
            child: SkeletonBlock(height: 24),
          ),
          SizedBox(height: Spacing.s3),
          FractionallySizedBox(widthFactor: 0.5, child: SkeletonBlock()),
          SizedBox(height: Spacing.s6),
          Row(
            children: [
              Expanded(child: SkeletonBlock(height: 76)),
              SizedBox(width: Spacing.s2),
              Expanded(child: SkeletonBlock(height: 76)),
              SizedBox(width: Spacing.s2),
              Expanded(child: SkeletonBlock(height: 76)),
            ],
          ),
          SizedBox(height: Spacing.s6),
          FractionallySizedBox(widthFactor: 0.9, child: SkeletonBlock()),
          SizedBox(height: Spacing.s2),
          FractionallySizedBox(widthFactor: 0.8, child: SkeletonBlock()),
          SizedBox(height: Spacing.s2),
          FractionallySizedBox(widthFactor: 0.6, child: SkeletonBlock()),
        ],
      ),
    ),
  );
}
