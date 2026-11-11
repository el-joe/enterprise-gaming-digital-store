import 'package:application/features/product/domain/entities/product.dart';

/// Why a variant cannot be added to the cart. Derived from the variant offer
/// (`GET .../variants`): the API publishes `availability`, `is_custom_amount`, `purchasable` and
/// `price`, but not the pricing engine's block reason, so every other refusal reads
/// "not available right now".
enum VariantBlock {
  /// `availability: out_of_stock` (the cart refuses it with "This product is out of stock").
  soldOut,

  /// `is_custom_amount` (the cart refuses custom amounts for now).
  customAmount,

  /// `purchasable: false` or no price (for example no fresh exchange rate).
  unavailable,
}

/// Availability values of a variant and of the product.
abstract final class Availability {
  static const inStock = 'in_stock';
  static const limited = 'limited';
  static const outOfStock = 'out_of_stock';
}

/// Null when [variant] can be added to the cart, else why not.
VariantBlock? blockOf(VariantOffer variant) {
  if (variant.availability == Availability.outOfStock) {
    return VariantBlock.soldOut;
  }
  if (variant.isCustomAmount) {
    return VariantBlock.customAmount;
  }
  if (!variant.purchasable || variant.price == null) {
    return VariantBlock.unavailable;
  }
  return null;
}

/// One choice of the region selector. `code` is null for variants without a region.
typedef RegionOption = ({String? code, String? name, bool available});

/// Regions of [variants] in the order of the product's [regions] (then any other code in variant
/// order); `available` when at least one of its variants can be bought.
List<RegionOption> regionOptions(
  List<VariantOffer> variants,
  List<Region> regions,
) {
  final codes = <String?>[];
  for (final region in regions) {
    if (variants.any((v) => v.region == region.code)) {
      codes.add(region.code);
    }
  }
  for (final variant in variants) {
    if (!codes.contains(variant.region)) {
      codes.add(variant.region);
    }
  }
  String? nameOf(String? code) {
    for (final region in regions) {
      if (region.code == code) {
        return region.name;
      }
    }
    return code;
  }

  return [
    for (final code in codes)
      (
        code: code,
        name: nameOf(code),
        available: variants.any((v) => v.region == code && blockOf(v) == null),
      ),
  ];
}

/// The variants offered for [region], in the server's order.
List<VariantOffer> variantsIn(List<VariantOffer> variants, String? region) => [
  for (final variant in variants)
    if (variant.region == region) variant,
];

/// The first selection: the first region with a variant that can be bought and its first such
/// variant; without any, the first region and no variant.
({String? region, String? variantId}) initialSelection(
  List<VariantOffer> variants,
  List<Region> regions,
) {
  final options = regionOptions(variants, regions);
  if (options.isEmpty) {
    return (region: null, variantId: null);
  }
  final region = options
      .firstWhere((o) => o.available, orElse: () => options.first)
      .code;
  return (region: region, variantId: firstAvailableIn(variants, region));
}

/// The first variant of [region] that can be bought, if any.
String? firstAvailableIn(List<VariantOffer> variants, String? region) {
  for (final variant in variantsIn(variants, region)) {
    if (blockOf(variant) == null) {
      return variant.publicId;
    }
  }
  return null;
}

/// After switching to [region]: the same denomination there when it can be bought, else the
/// region's first variant that can be bought, else none.
String? variantAfterRegionChange(
  List<VariantOffer> variants,
  String? region,
  VariantOffer? current,
) {
  if (current != null) {
    for (final variant in variantsIn(variants, region)) {
      if (blockOf(variant) == null &&
          variant.denominationValue == current.denominationValue &&
          variant.denominationCurrency == current.denominationCurrency &&
          variant.denominationValue != null) {
        return variant.publicId;
      }
    }
  }
  return firstAvailableIn(variants, region);
}

/// Most units of one product per order: the product's `max_quantity_per_order`, never above the
/// cart request's own limit of 10 (`AddCartItemRequest`), never below 1.
int maxQuantityFor(ProductDetail product) {
  const requestLimit = 10;
  final limit = product.maxQuantityPerOrder ?? requestLimit;
  return limit.clamp(1, requestLimit);
}
