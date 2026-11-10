import 'package:application/core/money/money.dart';
import 'package:application/core/money/money_json.dart';
import 'package:application/shared/domain/entities/cart.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'cart_models.freezed.dart';
part 'cart_models.g.dart';

@freezed
abstract class CartDto with _$CartDto {
  const factory CartDto({
    required String publicId,
    required String currency,
    required List<CartItemDto> items,
    required CartTotalsDto totals,
    required bool priceMayVary,
    required bool checkoutReady,
    String? country,
    CartCouponDto? coupon,
  }) = _CartDto;

  const CartDto._();

  factory CartDto.fromJson(Map<String, dynamic> json) =>
      _$CartDtoFromJson(json);

  Cart toEntity() => Cart(
    publicId: publicId,
    currency: currency,
    country: country,
    items: [for (final item in items) item.toEntity()],
    totals: CartTotals(
      subtotal: totals.subtotal,
      discount: totals.discount,
      total: totals.total,
    ),
    priceMayVary: priceMayVary,
    checkoutReady: checkoutReady,
    coupon: coupon == null
        ? null
        : CartCoupon(
            code: coupon!.code,
            applied: coupon!.applied,
            limitedByMargin: coupon!.limitedByMargin,
            error: coupon!.error,
          ),
  );
}

@freezed
abstract class CartVariantDto with _$CartVariantDto {
  const factory CartVariantDto({
    required String publicId,
    required String title,
    String? sku,
  }) = _CartVariantDto;

  factory CartVariantDto.fromJson(Map<String, dynamic> json) =>
      _$CartVariantDtoFromJson(json);
}

@freezed
abstract class CartProductDto with _$CartProductDto {
  const factory CartProductDto({
    required String name,
    required String slug,
    String? deliveryType,
  }) = _CartProductDto;

  factory CartProductDto.fromJson(Map<String, dynamic> json) =>
      _$CartProductDtoFromJson(json);
}

@freezed
abstract class CartItemDto with _$CartItemDto {
  const factory CartItemDto({
    required int id,
    required CartVariantDto variant,
    required CartProductDto product,
    required int quantity,
    @JsonKey(fromJson: deliveryValuesFromJson)
    required Map<String, String> deliveryValues,
    required bool purchasable,
    String? blockReason,
    @NullableMoneyConverter() Money? unitPrice,
    @NullableMoneyConverter() Money? subtotal,
    @NullableMoneyConverter() Money? total,
  }) = _CartItemDto;

  const CartItemDto._();

  factory CartItemDto.fromJson(Map<String, dynamic> json) =>
      _$CartItemDtoFromJson(json);

  CartLine toEntity() => CartLine(
    id: id,
    variantPublicId: variant.publicId,
    variantTitle: variant.title,
    variantSku: variant.sku,
    productName: product.name,
    productSlug: product.slug,
    deliveryType: product.deliveryType,
    quantity: quantity,
    deliveryValues: deliveryValues,
    purchasable: purchasable,
    blockReason: blockReason,
    unitPrice: unitPrice,
    subtotal: subtotal,
    total: total,
  );
}

/// `delivery_values` is a `{ key: value }` object; an empty one is `{}`.
Map<String, String> deliveryValuesFromJson(Object? json) {
  if (json is Map<String, dynamic>) {
    return json.map((key, value) => MapEntry(key, value.toString()));
  }
  throw const FormatException('delivery_values must be an object.');
}

@freezed
abstract class CartCouponDto with _$CartCouponDto {
  const factory CartCouponDto({
    required String code,
    required bool applied,
    required bool limitedByMargin,
    String? error,
  }) = _CartCouponDto;

  factory CartCouponDto.fromJson(Map<String, dynamic> json) =>
      _$CartCouponDtoFromJson(json);
}

@freezed
abstract class CartTotalsDto with _$CartTotalsDto {
  const factory CartTotalsDto({
    @MoneyConverter() required Money subtotal,
    @MoneyConverter() required Money discount,
    @MoneyConverter() required Money total,
  }) = _CartTotalsDto;

  factory CartTotalsDto.fromJson(Map<String, dynamic> json) =>
      _$CartTotalsDtoFromJson(json);
}
