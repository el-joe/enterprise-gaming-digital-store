import 'package:freezed_annotation/freezed_annotation.dart';

part 'reviews.freezed.dart';

/// A review written by the customer (API_DOCUMENTATION.md 4b); `pending` until moderated.
@freezed
abstract class MyReview with _$MyReview {
  const factory MyReview({
    required int id,
    required int orderItemId,
    required String productName,
    required String productSlug,
    required int rating,
    required String status,
    required DateTime createdAt,
    String? title,
    String? body,
  }) = _MyReview;
}

/// An item of a paid order with a delivered unit and no review yet.
@freezed
abstract class ReviewableItem with _$ReviewableItem {
  const factory ReviewableItem({
    required int orderItemId,
    required String orderPublicId,
    required String orderNumber,
    required String productName,
    required String productSlug,
  }) = _ReviewableItem;
}
