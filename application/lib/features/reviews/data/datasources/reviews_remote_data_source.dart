import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/api_envelope.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/features/reviews/data/models/review_models.dart';
import 'package:dio/dio.dart';

/// `/api/v1/mobile/account/reviews` (API_DOCUMENTATION.md 4b).
class ReviewsRemoteDataSource {
  ReviewsRemoteDataSource(this._dio);

  final Dio _dio;

  Future<ApiEnvelope<List<MyReviewDto>>> myReviews({
    required int page,
    required int perPage,
  }) async {
    final response = await _dio.get<Object?>(
      '/mobile/account/reviews',
      queryParameters: {'page': page, 'per_page': perPage},
    );
    return decodeEnvelope(
      response.data,
      (data) => listOf(data, MyReviewDto.fromJson),
    );
  }

  Future<List<ReviewableItemDto>> eligible() async {
    final response = await _dio.get<Object?>(
      '/mobile/account/reviews/eligible',
    );
    return decodeEnvelope(
      response.data,
      (data) => listAt(data, 'items', ReviewableItemDto.fromJson),
    ).data;
  }

  Future<MyReviewDto> submit(Map<String, Object?> body) async {
    final response = await _dio.post<Object?>(
      '/mobile/account/reviews',
      data: body,
      options: Options(extra: {RequestExtras.noRetry: true}),
    );
    return decodeEnvelope(
      response.data,
      (data) => MyReviewDto.fromJson(objectAt(data, 'review')),
    ).data;
  }

  /// `PATCH /reviews/{id}` (API_DOCUMENTATION.md 4d): the edit goes back to moderation.
  Future<MyReviewDto> update(int id, Map<String, Object?> body) async {
    final response = await _dio.patch<Object?>(
      '/mobile/account/reviews/$id',
      data: body,
      options: Options(extra: {RequestExtras.noRetry: true}),
    );
    return decodeEnvelope(
      response.data,
      (data) => MyReviewDto.fromJson(objectAt(data, 'review')),
    ).data;
  }
}
