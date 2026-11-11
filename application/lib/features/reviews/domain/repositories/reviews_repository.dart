import 'package:application/core/network/result.dart';
import 'package:application/features/reviews/domain/entities/reviews.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';

abstract interface class ReviewsRepository {
  Future<Result<Paged<MyReview>>> myReviews({int page = 1, int perPage = 20});

  Future<Result<List<ReviewableItem>>> eligible();

  /// 403 `email_unverified`, 422 `review_not_eligible`, 409 `review_exists`.
  Future<Result<MyReview>> submit({
    required int orderItemId,
    required int rating,
    String? title,
    String? body,
  });

  /// `PATCH /reviews/{id}`: the review returns to `pending` (moderation). 404 for an unknown id
  /// or another customer's review.
  Future<Result<MyReview>> update(
    int id, {
    required int rating,
    String? title,
    String? body,
  });
}
