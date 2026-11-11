import 'package:application/core/network/result.dart';
import 'package:application/features/reviews/data/datasources/reviews_remote_data_source.dart';
import 'package:application/features/reviews/domain/entities/reviews.dart';
import 'package:application/features/reviews/domain/repositories/reviews_repository.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';

class ReviewsRepositoryImpl implements ReviewsRepository {
  ReviewsRepositoryImpl(this._remote);

  final ReviewsRemoteDataSource _remote;

  @override
  Future<Result<Paged<MyReview>>> myReviews({int page = 1, int perPage = 20}) =>
      guardRequest(() async {
        final envelope = await _remote.myReviews(page: page, perPage: perPage);
        return Paged(
          items: [for (final dto in envelope.data) dto.toEntity()],
          page: pageInfoOf(envelope),
        );
      });

  @override
  Future<Result<List<ReviewableItem>>> eligible() => guardRequest(
    () async => [for (final dto in await _remote.eligible()) dto.toEntity()],
  );

  @override
  Future<Result<MyReview>> submit({
    required int orderItemId,
    required int rating,
    String? title,
    String? body,
  }) => guardRequest(
    () async => (await _remote.submit({
      'order_item_id': orderItemId,
      'rating': rating,
      'title': ?title,
      'body': ?body,
    })).toEntity(),
  );

  @override
  Future<Result<MyReview>> update(
    int id, {
    required int rating,
    String? title,
    String? body,
  }) => guardRequest(
    () async => (await _remote.update(id, {
      'rating': rating,
      'title': title,
      'body': body,
    })).toEntity(),
  );
}
