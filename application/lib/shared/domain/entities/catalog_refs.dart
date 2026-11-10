import 'package:application/core/money/money.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'catalog_refs.freezed.dart';

/// Image object of the storefront (API_DOCUMENTATION.md 2c): absolute [url], WebP/AVIF formats.
@freezed
abstract class MediaImage with _$MediaImage {
  const factory MediaImage({
    required String path,
    required Uri url,
    String? alt,
    int? width,
    int? height,
    @Default(<String, Uri>{}) Map<String, Uri> formats,
  }) = _MediaImage;
}

/// Link target of home items and banners: `product`, `category`, `platform`, `search` or `url`.
@freezed
abstract class LinkTarget with _$LinkTarget {
  const factory LinkTarget({
    required String type,
    required String value,
    required Uri url,
  }) = _LinkTarget;
}

/// A `{ name, slug }` reference (platform or category of a product).
@freezed
abstract class NamedSlug with _$NamedSlug {
  const factory NamedSlug({required String name, required String slug}) =
      _NamedSlug;
}

/// A product card of the listing, search and home carousels.
@freezed
abstract class ProductCard with _$ProductCard {
  const factory ProductCard({
    required String publicId,
    required String name,
    required String slug,
    required String deliveryType,
    required bool priceMayVary,
    required bool isFeatured,
    String? shortDescription,
    NamedSlug? platform,
    NamedSlug? category,
    MediaImage? image,
    Money? priceFrom,

    /// Decimal string, for example `4.50`.
    String? ratingAvg,
    LinkTarget? link,
  }) = _ProductCard;
}

/// Pagination of a list response (`meta.current_page`, `per_page`, `total`, `last_page`).
@freezed
abstract class PageInfo with _$PageInfo {
  const factory PageInfo({
    required int currentPage,
    required int perPage,
    required int total,
    required int lastPage,
  }) = _PageInfo;

  const PageInfo._();

  bool get hasNextPage => currentPage < lastPage;
}

/// One page of [items].
@immutable
final class Paged<T> {
  const Paged({required this.items, required this.page});

  final List<T> items;
  final PageInfo page;
}
