import 'package:application/core/money/money.dart';
import 'package:application/core/money/money_json.dart';
import 'package:application/core/network/api_envelope.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'catalog_ref_models.freezed.dart';
part 'catalog_ref_models.g.dart';

@freezed
abstract class MediaImageDto with _$MediaImageDto {
  const factory MediaImageDto({
    required String path,
    required String url,
    String? alt,
    int? width,
    int? height,
    @Default(<String, String>{}) Map<String, String> formats,
  }) = _MediaImageDto;

  const MediaImageDto._();

  factory MediaImageDto.fromJson(Map<String, dynamic> json) =>
      _$MediaImageDtoFromJson(json);

  MediaImage toEntity() => MediaImage(
    path: path,
    url: Uri.parse(url),
    alt: alt,
    width: width,
    height: height,
    formats: formats.map((key, value) => MapEntry(key, Uri.parse(value))),
  );
}

@freezed
abstract class LinkTargetDto with _$LinkTargetDto {
  const factory LinkTargetDto({
    required String type,
    required String value,
    required String url,
  }) = _LinkTargetDto;

  const LinkTargetDto._();

  factory LinkTargetDto.fromJson(Map<String, dynamic> json) =>
      _$LinkTargetDtoFromJson(json);

  LinkTarget toEntity() =>
      LinkTarget(type: type, value: value, url: Uri.parse(url));
}

@freezed
abstract class NamedSlugDto with _$NamedSlugDto {
  const factory NamedSlugDto({required String name, required String slug}) =
      _NamedSlugDto;

  const NamedSlugDto._();

  factory NamedSlugDto.fromJson(Map<String, dynamic> json) =>
      _$NamedSlugDtoFromJson(json);

  NamedSlug toEntity() => NamedSlug(name: name, slug: slug);
}

/// `ProductCardResource` (API_DOCUMENTATION.md 2a); `link` is present in home items only.
@freezed
abstract class ProductCardDto with _$ProductCardDto {
  const factory ProductCardDto({
    required String publicId,
    required String name,
    required String slug,
    required String deliveryType,
    required bool priceMayVary,
    required bool isFeatured,
    String? shortDescription,
    NamedSlugDto? platform,
    NamedSlugDto? category,
    MediaImageDto? image,
    @NullableMoneyConverter() Money? priceFrom,
    String? ratingAvg,
    LinkTargetDto? link,
  }) = _ProductCardDto;

  const ProductCardDto._();

  factory ProductCardDto.fromJson(Map<String, dynamic> json) =>
      _$ProductCardDtoFromJson(json);

  ProductCard toEntity() => ProductCard(
    publicId: publicId,
    name: name,
    slug: slug,
    deliveryType: deliveryType,
    priceMayVary: priceMayVary,
    isFeatured: isFeatured,
    shortDescription: shortDescription,
    platform: platform?.toEntity(),
    category: category?.toEntity(),
    image: image?.toEntity(),
    priceFrom: priceFrom,
    ratingAvg: ratingAvg,
    link: link?.toEntity(),
  );
}

/// [PageInfo] from envelope pagination; throws [FormatException] when `meta` has none.
PageInfo pageInfoOf(ApiEnvelope<Object?> envelope) {
  final page = envelope.page;
  if (page == null) {
    throw const FormatException('List response has no pagination meta.');
  }
  return PageInfo(
    currentPage: page.currentPage,
    perPage: page.perPage,
    total: page.total,
    lastPage: page.lastPage,
  );
}
