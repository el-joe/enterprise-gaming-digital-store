// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'catalog_ref_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_MediaImageDto _$MediaImageDtoFromJson(Map<String, dynamic> json) =>
    _MediaImageDto(
      path: json['path'] as String,
      url: json['url'] as String,
      alt: json['alt'] as String?,
      width: (json['width'] as num?)?.toInt(),
      height: (json['height'] as num?)?.toInt(),
      formats:
          (json['formats'] as Map<String, dynamic>?)?.map(
            (k, e) => MapEntry(k, e as String),
          ) ??
          const <String, String>{},
    );

Map<String, dynamic> _$MediaImageDtoToJson(_MediaImageDto instance) =>
    <String, dynamic>{
      'path': instance.path,
      'url': instance.url,
      'alt': instance.alt,
      'width': instance.width,
      'height': instance.height,
      'formats': instance.formats,
    };

_LinkTargetDto _$LinkTargetDtoFromJson(Map<String, dynamic> json) =>
    _LinkTargetDto(
      type: json['type'] as String,
      value: json['value'] as String,
      url: json['url'] as String,
    );

Map<String, dynamic> _$LinkTargetDtoToJson(_LinkTargetDto instance) =>
    <String, dynamic>{
      'type': instance.type,
      'value': instance.value,
      'url': instance.url,
    };

_NamedSlugDto _$NamedSlugDtoFromJson(Map<String, dynamic> json) =>
    _NamedSlugDto(name: json['name'] as String, slug: json['slug'] as String);

Map<String, dynamic> _$NamedSlugDtoToJson(_NamedSlugDto instance) =>
    <String, dynamic>{'name': instance.name, 'slug': instance.slug};

_ProductCardDto _$ProductCardDtoFromJson(Map<String, dynamic> json) =>
    _ProductCardDto(
      publicId: json['public_id'] as String,
      name: json['name'] as String,
      slug: json['slug'] as String,
      deliveryType: json['delivery_type'] as String,
      priceMayVary: json['price_may_vary'] as bool,
      isFeatured: json['is_featured'] as bool,
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
      link: json['link'] == null
          ? null
          : LinkTargetDto.fromJson(json['link'] as Map<String, dynamic>),
    );

Map<String, dynamic> _$ProductCardDtoToJson(_ProductCardDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'name': instance.name,
      'slug': instance.slug,
      'delivery_type': instance.deliveryType,
      'price_may_vary': instance.priceMayVary,
      'is_featured': instance.isFeatured,
      'short_description': instance.shortDescription,
      'platform': instance.platform?.toJson(),
      'category': instance.category?.toJson(),
      'image': instance.image?.toJson(),
      'price_from': const NullableMoneyConverter().toJson(instance.priceFrom),
      'rating_avg': instance.ratingAvg,
      'link': instance.link?.toJson(),
    };
