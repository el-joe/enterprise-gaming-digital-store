import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/api_envelope.dart';
import 'package:application/features/catalog/data/models/catalog_models.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';
import 'package:dio/dio.dart';

/// Storefront discovery under `/api/v1/mobile/storefront` (API_DOCUMENTATION.md 2a, 2c).
class CatalogRemoteDataSource {
  CatalogRemoteDataSource(this._dio);

  static const base = '/mobile/storefront';

  final Dio _dio;

  Future<ApiEnvelope<List<ProductCardDto>>> products(CatalogQuery query) async {
    final response = await _dio.get<Object?>(
      '$base/catalog/products',
      queryParameters: {
        'page': query.page,
        'per_page': query.perPage,
        'category': ?query.category,
        'platform': ?query.platform,
        'region': ?query.region,
        'delivery_type': ?query.deliveryType,
        'min_price': ?query.minPrice,
        'max_price': ?query.maxPrice,
        'sort': ?query.sort,
      },
    );
    return decodeEnvelope(
      response.data,
      (data) => listOf(data, ProductCardDto.fromJson),
    );
  }

  Future<List<PlatformDto>> platforms() async {
    final response = await _dio.get<Object?>('$base/platforms');
    return decodeEnvelope(
      response.data,
      (data) => listAt(data, 'platforms', PlatformDto.fromJson),
    ).data;
  }

  Future<List<CategoryDto>> categories() async {
    final response = await _dio.get<Object?>('$base/categories');
    return decodeEnvelope(
      response.data,
      (data) => listAt(data, 'categories', CategoryDto.fromJson),
    ).data;
  }

  Future<List<CountryDto>> countries() async {
    final response = await _dio.get<Object?>('$base/countries');
    return decodeEnvelope(
      response.data,
      (data) => listAt(data, 'countries', CountryDto.fromJson),
    ).data;
  }

  Future<List<DisplayCurrencyDto>> currencies() async {
    final response = await _dio.get<Object?>('$base/currencies');
    return decodeEnvelope(
      response.data,
      (data) => listAt(data, 'currencies', DisplayCurrencyDto.fromJson),
    ).data;
  }
}
