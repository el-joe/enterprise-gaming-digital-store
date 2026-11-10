import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/api_envelope.dart';
import 'package:application/features/product/data/models/product_models.dart';
import 'package:dio/dio.dart';

/// Product page, variants and reviews (API_DOCUMENTATION.md 2a, 2b, 2c).
class ProductRemoteDataSource {
  ProductRemoteDataSource(this._dio);

  static const base = '/mobile/storefront/catalog/products';

  final Dio _dio;

  Future<ApiEnvelope<ProductDetailDto>> detail(String slug) async {
    final response = await _dio.get<Object?>(
      '$base/${Uri.encodeComponent(slug)}',
    );
    return decodeEnvelope(
      response.data,
      (data) => ProductDetailDto.fromJson(objectAt(data, 'product')),
    );
  }

  Future<ApiEnvelope<List<VariantOfferDto>>> variants(
    String slug, {
    String? country,
  }) async {
    final response = await _dio.get<Object?>(
      '$base/${Uri.encodeComponent(slug)}/variants',
      queryParameters: {'country': ?country},
    );
    return decodeEnvelope(
      response.data,
      (data) => listAt(data, 'variants', VariantOfferDto.fromJson),
    );
  }

  Future<ApiEnvelope<List<ProductReviewDto>>> reviews(
    String slug, {
    required int page,
    required int perPage,
  }) async {
    final response = await _dio.get<Object?>(
      '$base/${Uri.encodeComponent(slug)}/reviews',
      queryParameters: {'page': page, 'per_page': perPage},
    );
    return decodeEnvelope(
      response.data,
      (data) => listOf(data, ProductReviewDto.fromJson),
    );
  }
}
