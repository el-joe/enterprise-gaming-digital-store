// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'catalog_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_PlatformDto _$PlatformDtoFromJson(Map<String, dynamic> json) => _PlatformDto(
  name: json['name'] as String,
  slug: json['slug'] as String,
  productCount: (json['product_count'] as num).toInt(),
  isFeatured: json['is_featured'] as bool,
  description: json['description'] as String?,
  logo: json['logo'] == null
      ? null
      : MediaImageDto.fromJson(json['logo'] as Map<String, dynamic>),
  banner: json['banner'] == null
      ? null
      : MediaImageDto.fromJson(json['banner'] as Map<String, dynamic>),
);

Map<String, dynamic> _$PlatformDtoToJson(_PlatformDto instance) =>
    <String, dynamic>{
      'name': instance.name,
      'slug': instance.slug,
      'product_count': instance.productCount,
      'is_featured': instance.isFeatured,
      'description': instance.description,
      'logo': instance.logo?.toJson(),
      'banner': instance.banner?.toJson(),
    };

_CategoryDto _$CategoryDtoFromJson(Map<String, dynamic> json) => _CategoryDto(
  name: json['name'] as String,
  slug: json['slug'] as String,
  productCount: (json['product_count'] as num).toInt(),
  description: json['description'] as String?,
  parent: json['parent'] as String?,
  banner: json['banner'] == null
      ? null
      : MediaImageDto.fromJson(json['banner'] as Map<String, dynamic>),
);

Map<String, dynamic> _$CategoryDtoToJson(_CategoryDto instance) =>
    <String, dynamic>{
      'name': instance.name,
      'slug': instance.slug,
      'product_count': instance.productCount,
      'description': instance.description,
      'parent': instance.parent,
      'banner': instance.banner?.toJson(),
    };

_CountryDto _$CountryDtoFromJson(Map<String, dynamic> json) => _CountryDto(
  code: json['code'] as String,
  name: json['name'] as String,
  dialCode: json['dial_code'] as String?,
  defaultCurrency: json['default_currency'] as String?,
);

Map<String, dynamic> _$CountryDtoToJson(_CountryDto instance) =>
    <String, dynamic>{
      'code': instance.code,
      'name': instance.name,
      'dial_code': instance.dialCode,
      'default_currency': instance.defaultCurrency,
    };

_DisplayCurrencyDto _$DisplayCurrencyDtoFromJson(Map<String, dynamic> json) =>
    _DisplayCurrencyDto(
      code: json['code'] as String,
      name: json['name'] as String,
      symbol: json['symbol'] as String,
      exponent: (json['exponent'] as num).toInt(),
    );

Map<String, dynamic> _$DisplayCurrencyDtoToJson(_DisplayCurrencyDto instance) =>
    <String, dynamic>{
      'code': instance.code,
      'name': instance.name,
      'symbol': instance.symbol,
      'exponent': instance.exponent,
    };
