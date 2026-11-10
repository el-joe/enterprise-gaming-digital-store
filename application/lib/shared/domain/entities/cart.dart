import 'package:application/core/money/money.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'cart.freezed.dart';

/// The cart (API_DOCUMENTATION.md 3a), returned by the cart routes and by checkout validate.
/// Totals always come from the server; the app never adds amounts up.
@freezed
abstract class Cart with _$Cart {
  const factory Cart({
    required String publicId,
    required String currency,
    required List<CartLine> items,
    required CartTotals totals,
    required bool priceMayVary,
    required bool checkoutReady,
    String? country,
    CartCoupon? coupon,
  }) = _Cart;
}

@freezed
abstract class CartLine with _$CartLine {
  const factory CartLine({
    required int id,
    required String variantPublicId,
    required String variantTitle,
    required String productName,
    required String productSlug,
    required int quantity,
    required Map<String, String> deliveryValues,
    required bool purchasable,
    String? variantSku,
    String? deliveryType,

    /// `unavailable`, `out_of_stock`, `custom_amount_unsupported`, `quantity_limit`,
    /// `not_sold_in_country` or a pricing reason; null when purchasable.
    String? blockReason,
    Money? unitPrice,
    Money? subtotal,
    Money? total,
  }) = _CartLine;
}

@freezed
abstract class CartCoupon with _$CartCoupon {
  const factory CartCoupon({
    required String code,
    required bool applied,
    required bool limitedByMargin,
    String? error,
  }) = _CartCoupon;
}

@freezed
abstract class CartTotals with _$CartTotals {
  const factory CartTotals({
    required Money subtotal,
    required Money discount,
    required Money total,
  }) = _CartTotals;
}
