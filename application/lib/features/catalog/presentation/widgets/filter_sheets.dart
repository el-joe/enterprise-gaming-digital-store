import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/features/catalog/presentation/controllers/catalog_controllers.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_chip.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/option_tile.dart';
import 'package:application/shared/widgets/sheet_header.dart';
import 'package:flutter/material.dart';

/// Localized label of a sort key.
String sortLabel(AppLocalizations l10n, String? sort) => switch (sort) {
  CatalogSort.priceAsc => l10n.sortPriceAsc,
  CatalogSort.priceDesc => l10n.sortPriceDesc,
  CatalogSort.newest => l10n.sortNewest,
  CatalogSort.name => l10n.sortName,
  _ => l10n.sortPopular,
};

String _deliveryLabel(AppLocalizations l10n, String type) => switch (type) {
  CatalogDelivery.directTopup => l10n.deliveryDirectTopup,
  _ => l10n.deliveryInstantCode,
};

/// The filters chosen in [FiltersSheet].
typedef ListingFilters = ({
  String? deliveryType,
  String? sort,
  String? minPrice,
  String? maxPrice,
});

/// Filters sheet (04.3): delivery, sort and a price range in the display currency. Returns the
/// filters on "Show results", null when dismissed.
Future<ListingFilters?> showFiltersSheet(
  BuildContext context, {
  required CatalogQuery query,
  String? currency,
}) => showModalBottomSheet<ListingFilters>(
  context: context,
  isScrollControlled: true,
  useRootNavigator: true,
  backgroundColor: context.colors.surfaceRaised,
  builder: (context) => FiltersSheet(query: query, currency: currency),
);

/// Sort sheet: returns the chosen sort key.
Future<String?> showSortSheet(BuildContext context, {String? current}) =>
    showModalBottomSheet<String>(
      context: context,
      useRootNavigator: true,
      backgroundColor: context.colors.surfaceRaised,
      builder: (context) {
        final l10n = context.l10n;
        return SafeArea(
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                SheetHeader(title: l10n.sortTitle),
                for (final sort in CatalogSort.all)
                  Padding(
                    padding: const EdgeInsetsDirectional.fromSTEB(
                      Spacing.s4,
                      0,
                      Spacing.s4,
                      Spacing.s2,
                    ),
                    child: OptionTile(
                      title: sortLabel(l10n, sort),
                      selected: (current ?? CatalogSort.popular) == sort,
                      onTap: () => Navigator.of(context).pop(sort),
                    ),
                  ),
                const SizedBox(height: Spacing.s2),
              ],
            ),
          ),
        );
      },
    );

class FiltersSheet extends StatefulWidget {
  const FiltersSheet({required this.query, this.currency, super.key});

  final CatalogQuery query;
  final String? currency;

  @override
  State<FiltersSheet> createState() => _FiltersSheetState();
}

/// Holds the draft only; validation is `validatePriceRange` of the catalog controllers.
class _FiltersSheetState extends State<FiltersSheet> {
  late String? _delivery = widget.query.deliveryType;
  late String? _sort = widget.query.sort;
  late final _min = TextEditingController(text: widget.query.minPrice);
  late final _max = TextEditingController(text: widget.query.maxPrice);
  PriceInputError? _error;

  @override
  void dispose() {
    _min.dispose();
    _max.dispose();
    super.dispose();
  }

  void _clear() => setState(() {
    _delivery = null;
    _sort = null;
    _min.clear();
    _max.clear();
    _error = null;
  });

  void _apply() {
    final error = validatePriceRange(_min.text, _max.text);
    if (error != null) {
      setState(() => _error = error);
      return;
    }
    Navigator.of(context).pop<ListingFilters>((
      deliveryType: _delivery,
      sort: _sort,
      minPrice: priceBound(_min.text),
      maxPrice: priceBound(_max.text),
    ));
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final type = context.type;
    final colors = context.colors;
    Widget group(String title) => Padding(
      padding: const EdgeInsetsDirectional.only(
        top: Spacing.s3,
        bottom: Spacing.s1,
      ),
      child: Semantics(
        header: true,
        child: Text(
          title,
          style: type.labelMd.copyWith(color: colors.contentSecondary),
        ),
      ),
    );
    final helper = widget.currency == null
        ? null
        : l10n.filterPriceHelper(widget.currency!);
    return Padding(
      padding: EdgeInsetsDirectional.only(
        bottom: MediaQuery.viewInsetsOf(context).bottom,
      ),
      child: SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            SheetHeader(title: l10n.filtersTitle),
            Flexible(
              child: SingleChildScrollView(
                padding: const EdgeInsetsDirectional.symmetric(
                  horizontal: Spacing.s4,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    group(l10n.filterDelivery),
                    Wrap(
                      spacing: Spacing.s2,
                      children: [
                        for (final delivery in CatalogDelivery.filters)
                          AppChip(
                            label: _deliveryLabel(l10n, delivery),
                            selected: _delivery == delivery,
                            onTap: () => setState(
                              () => _delivery = _delivery == delivery
                                  ? null
                                  : delivery,
                            ),
                          ),
                      ],
                    ),
                    group(l10n.sortTitle),
                    Wrap(
                      spacing: Spacing.s2,
                      children: [
                        for (final sort in CatalogSort.all)
                          AppChip(
                            label: sortLabel(l10n, sort),
                            selected: (_sort ?? CatalogSort.popular) == sort,
                            onTap: () => setState(() => _sort = sort),
                          ),
                      ],
                    ),
                    const SizedBox(height: Spacing.s3),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: AppTextField(
                            label: l10n.filterMinPrice,
                            controller: _min,
                            ltr: true,
                            helperText: helper,
                            keyboardType: const TextInputType.numberWithOptions(
                              decimal: true,
                            ),
                            textInputAction: TextInputAction.next,
                            errorText: switch (_error) {
                              PriceInputError.invalidMin =>
                                l10n.filterPriceInvalid,
                              PriceInputError.range => l10n.filterPriceRange,
                              _ => null,
                            },
                          ),
                        ),
                        const SizedBox(width: Spacing.s3),
                        Expanded(
                          child: AppTextField(
                            label: l10n.filterMaxPrice,
                            controller: _max,
                            ltr: true,
                            helperText: helper,
                            keyboardType: const TextInputType.numberWithOptions(
                              decimal: true,
                            ),
                            onSubmitted: (_) => _apply(),
                            errorText: _error == PriceInputError.invalidMax
                                ? l10n.filterPriceInvalid
                                : null,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsetsDirectional.all(Spacing.s4),
              child: Row(
                children: [
                  Expanded(
                    child: AppButton(
                      label: l10n.filtersClear,
                      variant: AppButtonVariant.secondary,
                      expand: true,
                      onPressed: _clear,
                    ),
                  ),
                  const SizedBox(width: Spacing.s3),
                  Expanded(
                    child: AppButton(
                      label: l10n.filtersApply,
                      expand: true,
                      onPressed: _apply,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
