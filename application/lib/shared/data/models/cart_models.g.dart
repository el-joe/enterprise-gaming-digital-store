// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'cart_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_CartDto _$CartDtoFromJson(Map<String, dynamic> json) => _CartDto(
  publicId: json['public_id'] as String,
  currency: json['currency'] as String,
  items: (json['items'] as List<dynamic>)
      .map((e) => CartItemDto.fromJson(e as Map<String, dynamic>))
      .toList(),
  totals: CartTotalsDto.fromJson(json['totals'] as Map<String, dynamic>),
  priceMayVary: json['price_may_vary'] as bool,
  checkoutReady: json['checkout_ready'] as bool,
  country: json['country'] as String?,
  coupon: json['coupon'] == null
      ? null
      : CartCouponDto.fromJson(json['coupon'] as Map<String, dynamic>),
);

Map<String, dynamic> _$CartDtoToJson(_CartDto instance) => <String, dynamic>{
  'public_id': instance.publicId,
  'currency': instance.currency,
  'items': instance.items.map((e) => e.toJson()).toList(),
  'totals': instance.totals.toJson(),
  'price_may_vary': instance.priceMayVary,
  'checkout_ready': instance.checkoutReady,
  'country': instance.country,
  'coupon': instance.coupon?.toJson(),
};

_CartVariantDto _$CartVariantDtoFromJson(Map<String, dynamic> json) =>
    _CartVariantDto(
      publicId: json['public_id'] as String,
      title: json['title'] as String,
      sku: json['sku'] as String?,
    );

Map<String, dynamic> _$CartVariantDtoToJson(_CartVariantDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'title': instance.title,
      'sku': instance.sku,
    };

_CartProductDto _$CartProductDtoFromJson(Map<String, dynamic> json) =>
    _CartProductDto(
      name: json['name'] as String,
      slug: json['slug'] as String,
      deliveryType: json['delivery_type'] as String?,
    );

Map<String, dynamic> _$CartProductDtoToJson(_CartProductDto instance) =>
    <String, dynamic>{
      'name': instance.name,
      'slug': instance.slug,
      'delivery_type': instance.deliveryType,
    };

_CartItemDto _$CartItemDtoFromJson(Map<String, dynamic> json) => _CartItemDto(
  id: (json['id'] as num).toInt(),
  variant: CartVariantDto.fromJson(json['variant'] as Map<String, dynamic>),
  product: CartProductDto.fromJson(json['product'] as Map<String, dynamic>),
  quantity: (json['quantity'] as num).toInt(),
  deliveryValues: deliveryValuesFromJson(json['delivery_values']),
  purchasable: json['purchasable'] as bool,
  blockReason: json['block_reason'] as String?,
  unitPrice: const NullableMoneyConverter().fromJson(json['unit_price']),
  subtotal: const NullableMoneyConverter().fromJson(json['subtotal']),
  total: const NullableMoneyConverter().fromJson(json['total']),
);

Map<String, dynamic> _$CartItemDtoToJson(_CartItemDto instance) =>
    <String, dynamic>{
      'id': instance.id,
      'variant': instance.variant.toJson(),
      'product': instance.product.toJson(),
      'quantity': instance.quantity,
      'delivery_values': instance.deliveryValues,
      'purchasable': instance.purchasable,
      'block_reason': instance.blockReason,
      'unit_price': const NullableMoneyConverter().toJson(instance.unitPrice),
      'subtotal': const NullableMoneyConverter().toJson(instance.subtotal),
      'total': const NullableMoneyConverter().toJson(instance.total),
    };

_CartCouponDto _$CartCouponDtoFromJson(Map<String, dynamic> json) =>
    _CartCouponDto(
      code: json['code'] as String,
      applied: json['applied'] as bool,
      limitedByMargin: json['limited_by_margin'] as bool,
      error: json['error'] as String?,
    );

Map<String, dynamic> _$CartCouponDtoToJson(_CartCouponDto instance) =>
    <String, dynamic>{
      'code': instance.code,
      'applied': instance.applied,
      'limited_by_margin': instance.limitedByMargin,
      'error': instance.error,
    };

_CartTotalsDto _$CartTotalsDtoFromJson(Map<String, dynamic> json) =>
    _CartTotalsDto(
      subtotal: const MoneyConverter().fromJson(json['subtotal']),
      discount: const MoneyConverter().fromJson(json['discount']),
      total: const MoneyConverter().fromJson(json['total']),
    );

Map<String, dynamic> _$CartTotalsDtoToJson(_CartTotalsDto instance) =>
    <String, dynamic>{
      'subtotal': const MoneyConverter().toJson(instance.subtotal),
      'discount': const MoneyConverter().toJson(instance.discount),
      'total': const MoneyConverter().toJson(instance.total),
    };
