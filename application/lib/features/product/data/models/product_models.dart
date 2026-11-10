import 'package:application/core/money/money.dart';
import 'package:application/core/money/money_json.dart';
import 'package:application/features/product/domain/entities/product.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'product_models.freezed.dart';
part 'product_models.g.dart';

@freezed
abstract class ProductDetailDto with _$ProductDetailDto {
  const factory ProductDetailDto({
    required String publicId,
    required String name,
    required String slug,
    required String deliveryType,
    required bool priceMayVary,
    required bool isFeatured,
    required List<MediaImageDto> images,
    required List<RegionDto> regions,
    required RatingDto rating,
    required VariantsSummaryDto variantsSummary,
    required List<DeliveryFieldDto> deliveryFields,
    required SeoDto seo,
    String? shortDescription,
    NamedSlugDto? platform,
    NamedSlugDto? category,
    MediaImageDto? image,
    @NullableMoneyConverter() Money? priceFrom,
    String? ratingAvg,
    String? sku,
    String? description,
    String? instructions,
    String? terms,
    int? deliveryTimeMinutes,
    int? maxQuantityPerOrder,
    String? availability,
  }) = _ProductDetailDto;

  const ProductDetailDto._();

  factory ProductDetailDto.fromJson(Map<String, dynamic> json) =>
      _$ProductDetailDtoFromJson(json);

  ProductDetail toEntity({required String displayCurrency}) => ProductDetail(
    card: ProductCardDto(
      publicId: publicId,
      name: name,
      slug: slug,
      deliveryType: deliveryType,
      priceMayVary: priceMayVary,
      isFeatured: isFeatured,
      shortDescription: shortDescription,
      platform: platform,
      category: category,
      image: image,
      priceFrom: priceFrom,
      ratingAvg: ratingAvg,
    ).toEntity(),
    images: [for (final dto in images) dto.toEntity()],
    regions: [for (final dto in regions) dto.toEntity()],
    rating: ProductRating(average: rating.average, count: rating.count),
    variantsSummary: VariantsSummary(
      count: variantsSummary.count,
      regions: [for (final dto in variantsSummary.regions) dto.toEntity()],
      hasCustomAmount: variantsSummary.hasCustomAmount,
    ),
    deliveryFields: [for (final dto in deliveryFields) dto.toEntity()],
    seo: ProductSeo(
      title: seo.title,
      description: seo.description,
      robots: seo.robots,
      image: seo.image == null ? null : Uri.parse(seo.image!),
    ),
    displayCurrency: displayCurrency,
    sku: sku,
    description: description,
    instructions: instructions,
    terms: terms,
    deliveryTimeMinutes: deliveryTimeMinutes,
    maxQuantityPerOrder: maxQuantityPerOrder,
    availability: availability,
  );
}

@freezed
abstract class RegionDto with _$RegionDto {
  const factory RegionDto({required String code, required String name}) =
      _RegionDto;

  const RegionDto._();

  factory RegionDto.fromJson(Map<String, dynamic> json) =>
      _$RegionDtoFromJson(json);

  Region toEntity() => Region(code: code, name: name);
}

@freezed
abstract class RatingDto with _$RatingDto {
  const factory RatingDto({required String average, required int count}) =
      _RatingDto;

  factory RatingDto.fromJson(Map<String, dynamic> json) =>
      _$RatingDtoFromJson(json);
}

@freezed
abstract class VariantsSummaryDto with _$VariantsSummaryDto {
  const factory VariantsSummaryDto({
    required int count,
    required List<RegionDto> regions,
    required bool hasCustomAmount,
  }) = _VariantsSummaryDto;

  factory VariantsSummaryDto.fromJson(Map<String, dynamic> json) =>
      _$VariantsSummaryDtoFromJson(json);
}

@freezed
abstract class DeliveryFieldOptionDto with _$DeliveryFieldOptionDto {
  const factory DeliveryFieldOptionDto({
    required String value,
    required String label,
  }) = _DeliveryFieldOptionDto;

  factory DeliveryFieldOptionDto.fromJson(Map<String, dynamic> json) =>
      _$DeliveryFieldOptionDtoFromJson(json);
}

@freezed
abstract class DeliveryFieldDto with _$DeliveryFieldDto {
  const factory DeliveryFieldDto({
    required String key,
    required String label,
    required String type,
    required bool required,
    String? helpText,
    int? maxLength,
    String? pattern,
    List<DeliveryFieldOptionDto>? options,
  }) = _DeliveryFieldDto;

  const DeliveryFieldDto._();

  factory DeliveryFieldDto.fromJson(Map<String, dynamic> json) =>
      _$DeliveryFieldDtoFromJson(json);

  DeliveryField toEntity() => DeliveryField(
    key: key,
    label: label,
    type: type,
    required: this.required,
    helpText: helpText,
    maxLength: maxLength,
    pattern: pattern,
    options: options == null
        ? null
        : [
            for (final option in options!)
              DeliveryFieldOption(value: option.value, label: option.label),
          ],
  );
}

@freezed
abstract class SeoDto with _$SeoDto {
  const factory SeoDto({
    String? title,
    String? description,
    String? robots,
    String? image,
  }) = _SeoDto;

  factory SeoDto.fromJson(Map<String, dynamic> json) => _$SeoDtoFromJson(json);
}

@freezed
abstract class VariantOfferDto with _$VariantOfferDto {
  const factory VariantOfferDto({
    required String publicId,
    required String title,
    required String sku,
    required bool isCustomAmount,
    required String availability,
    required bool purchasable,
    required bool priceMayVary,
    String? region,
    String? denominationValue,
    String? denominationCurrency,
    @NullableMoneyConverter() Money? price,
  }) = _VariantOfferDto;

  const VariantOfferDto._();

  factory VariantOfferDto.fromJson(Map<String, dynamic> json) =>
      _$VariantOfferDtoFromJson(json);

  VariantOffer toEntity() => VariantOffer(
    publicId: publicId,
    title: title,
    sku: sku,
    isCustomAmount: isCustomAmount,
    availability: availability,
    purchasable: purchasable,
    priceMayVary: priceMayVary,
    region: region,
    denominationValue: denominationValue,
    denominationCurrency: denominationCurrency,
    price: price,
  );
}

@freezed
abstract class ProductReviewDto with _$ProductReviewDto {
  const factory ProductReviewDto({
    required int id,
    required int rating,
    required String reviewer,
    required bool verifiedPurchase,
    required DateTime createdAt,
    String? title,
    String? body,
  }) = _ProductReviewDto;

  const ProductReviewDto._();

  factory ProductReviewDto.fromJson(Map<String, dynamic> json) =>
      _$ProductReviewDtoFromJson(json);

  ProductReview toEntity() => ProductReview(
    id: id,
    rating: rating,
    reviewer: reviewer,
    verifiedPurchase: verifiedPurchase,
    createdAt: createdAt,
    title: title,
    body: body,
  );
}
