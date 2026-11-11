import 'package:application/core/router/link_navigation.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:application/shared/widgets/logo_slot.dart';
import 'package:application/shared/widgets/product_tile.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// One row of two Product Tiles (the second slot may be empty). Rows size to their content, so
/// large text never clips; tapping a tile opens the product.
class ProductPairRow extends ConsumerWidget {
  const ProductPairRow({required this.products, super.key})
    : assert(products.length > 0 && products.length <= 2, '1 or 2 products');

  final List<ProductCard> products;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final showSlot = ref.watch(showLogoSlotsProvider);
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        for (var column = 0; column < 2; column++) ...[
          if (column == 1) const SizedBox(width: Spacing.s4),
          Expanded(
            child: column < products.length
                ? ProductTile(
                    product: products[column],
                    showLogoSlot: showSlot,
                    onTap: () => openLocation(
                      context,
                      Routes.productLocation(products[column].slug),
                    ),
                  )
                : const SizedBox.shrink(),
          ),
        ],
      ],
    );
  }
}

/// [products] two per row (Featured on Home, 03.1).
class ProductGrid extends StatelessWidget {
  const ProductGrid({required this.products, super.key});

  final List<ProductCard> products;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsetsDirectional.symmetric(horizontal: Spacing.s4),
    child: Column(
      children: [
        for (var start = 0; start < products.length; start += 2) ...[
          if (start > 0) const SizedBox(height: Spacing.s4),
          ProductPairRow(
            products: products.sublist(
              start,
              start + 2 > products.length ? products.length : start + 2,
            ),
          ),
        ],
      ],
    ),
  );
}
