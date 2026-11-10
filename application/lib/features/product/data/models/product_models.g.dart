// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'product_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_ProductDetailDto _$ProductDetailDtoFromJson(Map<String, dynamic> json) =>
    _ProductDetailDto(
      publicId: json['public_id'] as String,
      name: json['name'] as String,
      slug: json['slug'] as String,
      deliveryType: json['delivery_type'] as String,
      priceMayVary: json['price_may_vary'] as bool,
      isFeatured: json['is_featured'] as bool,
      images: (json['images'] as List<dynamic>)
          .map((e) => MediaImageDto.fromJson(e as Map<String, dynamic>))
          .toList(),
      regions: (json['regions'] as List<dynamic>)
          .map((e) => RegionDto.fromJson(e as Map<String, dynamic>))
          .toList(),
      rating: RatingDto.fromJson(json['rating'] as Map<String, dynamic>),
      variantsSummary: VariantsSummaryDto.fromJson(
        json['variants_summary'] as Map<String, dynamic>,
      ),
      deliveryFields: (json['delivery_fields'] as List<dynamic>)
          .map((e) => DeliveryFieldDto.fromJson(e as Map<String, dynamic>))
          .toList(),
      seo: SeoDto.fromJson(json['seo'] as Map<String, dynamic>),
      shortDescription: json['short_description'] as String?,
      platform: json['platform'] == null
          ? null
          : NamedSlugDto.fromJson(json['platform'] as Map<String, dynamic>),
      category: json['category'] == null
          ? null
          : NamedSlugDto.fromJson(json['category'] as Map<String, dynamic>),
      image: json['image'] == null
          ? null
          : MediaImageDto.fromJson(json['image'] as Map<String, dynamic>),
      priceFrom: const NullableMoneyConverter().fromJson(json['price_from']),
      ratingAvg: json['rating_avg'] as String?,
      sku: json['sku'] as String?,
      description: json['description'] as String?,
      instructions: json['instructions'] as String?,
      terms: json['terms'] as String?,
      deliveryTimeMinutes: (json['delivery_time_minutes'] as num?)?.toInt(),
      maxQuantityPerOrder: (json['max_quantity_per_order'] as num?)?.toInt(),
      availability: json['availability'] as String?,
    );

Map<String, dynamic> _$ProductDetailDtoToJson(
  _ProductDetailDto instance,
) => <String, dynamic>{
  'public_id': instance.publicId,
  'name': instance.name,
  'slug': instance.slug,
  'delivery_type': instance.deliveryType,
  'price_may_vary': instance.priceMayVary,
  'is_featured': instance.isFeatured,
  'images': instance.images.map((e) => e.toJson()).toList(),
  'regions': instance.regions.map((e) => e.toJson()).toList(),
  'rating': instance.rating.toJson(),
  'variants_summary': instance.variantsSummary.toJson(),
  'delivery_fields': instance.deliveryFields.map((e) => e.toJson()).toList(),
  'seo': instance.seo.toJson(),
  'short_description': instance.shortDescription,
  'platform': instance.platform?.toJson(),
  'category': instance.category?.toJson(),
  'image': instance.image?.toJson(),
  'price_from': const NullableMoneyConverter().toJson(instance.priceFrom),
  'rating_avg': instance.ratingAvg,
  'sku': instance.sku,
  'description': instance.description,
  'instructions': instance.instructions,
  'terms': instance.terms,
  'delivery_time_minutes': instance.deliveryTimeMinutes,
  'max_quantity_per_order': instance.maxQuantityPerOrder,
  'availability': instance.availability,
};

_RegionDto _$RegionDtoFromJson(Map<String, dynamic> json) =>
    _RegionDto(code: json['code'] as String, name: json['name'] as String);

Map<String, dynamic> _$RegionDtoToJson(_RegionDto instance) =>
    <String, dynamic>{'code': instance.code, 'name': instance.name};

_RatingDto _$RatingDtoFromJson(Map<String, dynamic> json) => _RatingDto(
  average: json['average'] as String,
  count: (json['count'] as num).toInt(),
);

Map<String, dynamic> _$RatingDtoToJson(_RatingDto instance) =>
    <String, dynamic>{'average': instance.average, 'count': instance.count};

_VariantsSummaryDto _$VariantsSummaryDtoFromJson(Map<String, dynamic> json) =>
    _VariantsSummaryDto(
      count: (json['count'] as num).toInt(),
      regions: (json['regions'] as List<dynamic>)
          .map((e) => RegionDto.fromJson(e as Map<String, dynamic>))
          .toList(),
      hasCustomAmount: json['has_custom_amount'] as bool,
    );

Map<String, dynamic> _$VariantsSummaryDtoToJson(_VariantsSummaryDto instance) =>
    <String, dynamic>{
      'count': instance.count,
      'regions': instance.regions.map((e) => e.toJson()).toList(),
      'has_custom_amount': instance.hasCustomAmount,
    };

_DeliveryFieldOptionDto _$DeliveryFieldOptionDtoFromJson(
  Map<String, dynamic> json,
) => _DeliveryFieldOptionDto(
  value: json['value'] as String,
  label: json['label'] as String,
);

Map<String, dynamic> _$DeliveryFieldOptionDtoToJson(
  _DeliveryFieldOptionDto instance,
) => <String, dynamic>{'value': instance.value, 'label': instance.label};

_DeliveryFieldDto _$DeliveryFieldDtoFromJson(Map<String, dynamic> json) =>
    _DeliveryFieldDto(
      key: json['key'] as String,
      label: json['label'] as String,
      type: json['type'] as String,
      required: json['required'] as bool,
      helpText: json['help_text'] as String?,
      maxLength: (json['max_length'] as num?)?.toInt(),
      pattern: json['pattern'] as String?,
      options: (json['options'] as List<dynamic>?)
          ?.map(
            (e) => DeliveryFieldOptionDto.fromJson(e as Map<String, dynamic>),
          )
          .toList(),
    );

Map<String, dynamic> _$DeliveryFieldDtoToJson(_DeliveryFieldDto instance) =>
    <String, dynamic>{
      'key': instance.key,
      'label': instance.label,
      'type': instance.type,
      'required': instance.required,
      'help_text': instance.helpText,
      'max_length': instance.maxLength,
      'pattern': instance.pattern,
      'options': instance.options?.map((e) => e.toJson()).toList(),
    };

_SeoDto _$SeoDtoFromJson(Map<String, dynamic> json) => _SeoDto(
  title: json['title'] as String?,
  description: json['description'] as String?,
  robots: json['robots'] as String?,
  image: json['image'] as String?,
);

Map<String, dynamic> _$SeoDtoToJson(_SeoDto instance) => <String, dynamic>{
  'title': instance.title,
  'description': instance.description,
  'robots': instance.robots,
  'image': instance.image,
};

_VariantOfferDto _$VariantOfferDtoFromJson(Map<String, dynamic> json) =>
    _VariantOfferDto(
      publicId: json['public_id'] as String,
      title: json['title'] as String,
      sku: json['sku'] as String,
      isCustomAmount: json['is_custom_amount'] as bool,
      availability: json['availability'] as String,
      purchasable: json['purchasable'] as bool,
      priceMayVary: json['price_may_vary'] as bool,
      region: json['region'] as String?,
      denominationValue: json['denomination_value'] as String?,
      denominationCurrency: json['denomination_currency'] as String?,
      price: const NullableMoneyConverter().fromJson(json['price']),
    );

Map<String, dynamic> _$VariantOfferDtoToJson(_VariantOfferDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'title': instance.title,
      'sku': instance.sku,
      'is_custom_amount': instance.isCustomAmount,
      'availability': instance.availability,
      'purchasable': instance.purchasable,
      'price_may_vary': instance.priceMayVary,
      'region': instance.region,
      'denomination_value': instance.denominationValue,
      'denomination_currency': instance.denominationCurrency,
      'price': const NullableMoneyConverter().toJson(instance.price),
    };

_ProductReviewDto _$ProductReviewDtoFromJson(Map<String, dynamic> json) =>
    _ProductReviewDto(
      id: (json['id'] as num).toInt(),
      rating: (json['rating'] as num).toInt(),
      reviewer: json['reviewer'] as String,
      verifiedPurchase: json['verified_purchase'] as bool,
      createdAt: DateTime.parse(json['created_at'] as String),
      title: json['title'] as String?,
      body: json['body'] as String?,
    );

Map<String, dynamic> _$ProductReviewDtoToJson(_ProductReviewDto instance) =>
    <String, dynamic>{
      'id': instance.id,
      'rating': instance.rating,
      'reviewer': instance.reviewer,
      'verified_purchase': instance.verifiedPurchase,
      'created_at': instance.createdAt.toIso8601String(),
      'title': instance.title,
      'body': instance.body,
    };
