import 'package:application/core/money/currency_info.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'catalog.freezed.dart';

/// Filters of `GET /storefront/catalog/products` (API_DOCUMENTATION.md 2a). Price bounds are
/// decimal strings in the display currency, never numbers.
@freezed
abstract class CatalogQuery with _$CatalogQuery {
  const factory CatalogQuery({
    String? category,
    String? platform,
    String? region,
    String? deliveryType,
    String? minPrice,
    String? maxPrice,

    /// `popular`, `newest`, `price_asc`, `price_desc` or `name`.
    String? sort,
    @Default(1) int page,
    @Default(20) int perPage,
  }) = _CatalogQuery;
}

@freezed
abstract class CatalogPage with _$CatalogPage {
  const factory CatalogPage({
    required List<ProductCard> products,
    required PageInfo page,
    required String displayCurrency,
  }) = _CatalogPage;
}

@freezed
abstract class Platform with _$Platform {
  const factory Platform({
    required String name,
    required String slug,
    required int productCount,
    required bool isFeatured,
    String? description,
    MediaImage? logo,
    MediaImage? banner,
  }) = _Platform;
}

@freezed
abstract class Category with _$Category {
  const factory Category({
    required String name,
    required String slug,
    required int productCount,
    String? description,

    /// Parent slug, null for a top-level category.
    String? parent,
    MediaImage? banner,
  }) = _Category;
}

/// A country accepted by checkout (`GET /storefront/countries`).
@freezed
abstract class Country with _$Country {
  const factory Country({
    required String code,
    required String name,
    String? dialCode,
    String? defaultCurrency,
  }) = _Country;
}

/// An active display currency (`GET /storefront/currencies`).
@freezed
abstract class DisplayCurrencyOption with _$DisplayCurrencyOption {
  const factory DisplayCurrencyOption({
    required String name,
    required CurrencyInfo info,
  }) = _DisplayCurrencyOption;
}
