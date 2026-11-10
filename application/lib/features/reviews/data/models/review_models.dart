import 'package:application/features/reviews/domain/entities/reviews.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'review_models.freezed.dart';
part 'review_models.g.dart';

@freezed
abstract class ReviewProductDto with _$ReviewProductDto {
  const factory ReviewProductDto({required String name, required String slug}) =
      _ReviewProductDto;

  factory ReviewProductDto.fromJson(Map<String, dynamic> json) =>
      _$ReviewProductDtoFromJson(json);
}

@freezed
abstract class MyReviewDto with _$MyReviewDto {
  const factory MyReviewDto({
    required int id,
    required int orderItemId,
    required ReviewProductDto product,
    required int rating,
    required String status,
    required DateTime createdAt,
    String? title,
    String? body,
  }) = _MyReviewDto;

  const MyReviewDto._();

  factory MyReviewDto.fromJson(Map<String, dynamic> json) =>
      _$MyReviewDtoFromJson(json);

  MyReview toEntity() => MyReview(
    id: id,
    orderItemId: orderItemId,
    productName: product.name,
    productSlug: product.slug,
    rating: rating,
    status: status,
    createdAt: createdAt,
    title: title,
    body: body,
  );
}

@freezed
abstract class ReviewOrderRefDto with _$ReviewOrderRefDto {
  const factory ReviewOrderRefDto({
    required String publicId,
    required String orderNumber,
  }) = _ReviewOrderRefDto;

  factory ReviewOrderRefDto.fromJson(Map<String, dynamic> json) =>
      _$ReviewOrderRefDtoFromJson(json);
}

@freezed
abstract class ReviewableItemDto with _$ReviewableItemDto {
  const factory ReviewableItemDto({
    required int orderItemId,
    required ReviewOrderRefDto order,
    required ReviewProductDto product,
  }) = _ReviewableItemDto;

  const ReviewableItemDto._();

  factory ReviewableItemDto.fromJson(Map<String, dynamic> json) =>
      _$ReviewableItemDtoFromJson(json);

  ReviewableItem toEntity() => ReviewableItem(
    orderItemId: orderItemId,
    orderPublicId: order.publicId,
    orderNumber: order.orderNumber,
    productName: product.name,
    productSlug: product.slug,
  );
}
