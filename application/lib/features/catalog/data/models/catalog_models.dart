import 'package:application/core/money/currency_info.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'catalog_models.freezed.dart';
part 'catalog_models.g.dart';

@freezed
abstract class PlatformDto with _$PlatformDto {
  const factory PlatformDto({
    required String name,
    required String slug,
    required int productCount,
    required bool isFeatured,
    String? description,
    MediaImageDto? logo,
    MediaImageDto? banner,
  }) = _PlatformDto;

  const PlatformDto._();

  factory PlatformDto.fromJson(Map<String, dynamic> json) =>
      _$PlatformDtoFromJson(json);

  Platform toEntity() => Platform(
    name: name,
    slug: slug,
    productCount: productCount,
    isFeatured: isFeatured,
    description: description,
    logo: logo?.toEntity(),
    banner: banner?.toEntity(),
  );
}

@freezed
abstract class CategoryDto with _$CategoryDto {
  const factory CategoryDto({
    required String name,
    required String slug,
    required int productCount,
    String? description,
    String? parent,
    MediaImageDto? banner,
  }) = _CategoryDto;

  const CategoryDto._();

  factory CategoryDto.fromJson(Map<String, dynamic> json) =>
      _$CategoryDtoFromJson(json);

  Category toEntity() => Category(
    name: name,
    slug: slug,
    productCount: productCount,
    description: description,
    parent: parent,
    banner: banner?.toEntity(),
  );
}

@freezed
abstract class CountryDto with _$CountryDto {
  const factory CountryDto({
    required String code,
    required String name,
    String? dialCode,
    String? defaultCurrency,
  }) = _CountryDto;

  const CountryDto._();

  factory CountryDto.fromJson(Map<String, dynamic> json) =>
      _$CountryDtoFromJson(json);

  Country toEntity() => Country(
    code: code,
    name: name,
    dialCode: dialCode,
    defaultCurrency: defaultCurrency,
  );
}

@freezed
abstract class DisplayCurrencyDto with _$DisplayCurrencyDto {
  const factory DisplayCurrencyDto({
    required String code,
    required String name,
    required String symbol,
    required int exponent,
  }) = _DisplayCurrencyDto;

  const DisplayCurrencyDto._();

  factory DisplayCurrencyDto.fromJson(Map<String, dynamic> json) =>
      _$DisplayCurrencyDtoFromJson(json);

  DisplayCurrencyOption toEntity() => DisplayCurrencyOption(
    name: name,
    info: CurrencyInfo(code: code, symbol: symbol, exponent: exponent),
  );
}
