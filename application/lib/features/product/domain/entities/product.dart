import 'package:application/core/money/money.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'product.freezed.dart';

/// `GET /storefront/catalog/products/{slug}` (API_DOCUMENTATION.md 2c).
@freezed
abstract class ProductDetail with _$ProductDetail {
  const factory ProductDetail({
    required ProductCard card,
    required List<MediaImage> images,
    required List<Region> regions,
    required ProductRating rating,
    required VariantsSummary variantsSummary,
    required List<DeliveryField> deliveryFields,
    required ProductSeo seo,
    required String displayCurrency,
    String? sku,

    /// Sanitized HTML.
    String? description,

    /// Plain text with line breaks.
    String? instructions,
    String? terms,
    int? deliveryTimeMinutes,
    int? maxQuantityPerOrder,

    /// `in_stock`, `limited`, `out_of_stock`, or null without active variants.
    String? availability,
  }) = _ProductDetail;
}

@freezed
abstract class Region with _$Region {
  const factory Region({required String code, required String name}) = _Region;
}

@freezed
abstract class ProductRating with _$ProductRating {
  /// [average] is a decimal string such as `4.50`.
  const factory ProductRating({required String average, required int count}) =
      _ProductRating;
}

@freezed
abstract class VariantsSummary with _$VariantsSummary {
  const factory VariantsSummary({
    required int count,
    required List<Region> regions,
    required bool hasCustomAmount,
  }) = _VariantsSummary;
}

/// What `POST /cart/items` validates `delivery_values` against (one server definition).
@freezed
abstract class DeliveryField with _$DeliveryField {
  const factory DeliveryField({
    required String key,
    required String label,

    /// `text`, `number`, `email` or `select`.
    required String type,
    required bool required,
    String? helpText,
    int? maxLength,

    /// Whole-value pattern, anchored by the server.
    String? pattern,
    List<DeliveryFieldOption>? options,
  }) = _DeliveryField;
}

@freezed
abstract class DeliveryFieldOption with _$DeliveryFieldOption {
  const factory DeliveryFieldOption({
    required String value,
    required String label,
  }) = _DeliveryFieldOption;
}

@freezed
abstract class ProductSeo with _$ProductSeo {
  const factory ProductSeo({
    String? title,
    String? description,
    String? robots,
    Uri? image,
  }) = _ProductSeo;
}

/// A variant offer of `GET .../{slug}/variants`.
@freezed
abstract class VariantOffer with _$VariantOffer {
  const factory VariantOffer({
    required String publicId,
    required String title,
    required String sku,
    required bool isCustomAmount,
    required String availability,
    required bool purchasable,
    required bool priceMayVary,
    String? region,

    /// Decimal string (`10.000000`).
    String? denominationValue,
    String? denominationCurrency,
    Money? price,
  }) = _VariantOffer;
}

@freezed
abstract class ProductVariants with _$ProductVariants {
  const factory ProductVariants({
    required List<VariantOffer> variants,
    required String displayCurrency,
  }) = _ProductVariants;
}

@freezed
abstract class ProductReview with _$ProductReview {
  const factory ProductReview({
    required int id,
    required int rating,
    required String reviewer,
    required bool verifiedPurchase,
    required DateTime createdAt,
    String? title,
    String? body,
  }) = _ProductReview;
}

@freezed
abstract class ProductReviewsPage with _$ProductReviewsPage {
  const factory ProductReviewsPage({
    required List<ProductReview> reviews,
    required PageInfo page,
    required String ratingAvg,
    required int ratingCount,
  }) = _ProductReviewsPage;
}
