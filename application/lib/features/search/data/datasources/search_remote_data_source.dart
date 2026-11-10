import 'package:application/core/network/api_decoding.dart';
import 'package:application/features/search/data/models/search_models.dart';
import 'package:dio/dio.dart';

/// `GET /api/v1/mobile/storefront/search/{suggest,popular}` (API_DOCUMENTATION.md 2a). The locale
/// follows `Accept-Language`.
class SearchRemoteDataSource {
  SearchRemoteDataSource(this._dio);

  final Dio _dio;

  Future<List<SearchSuggestionDto>> suggest(
    String query, {
    bool commit = false,
  }) async {
    final response = await _dio.get<Object?>(
      '/mobile/storefront/search/suggest',
      queryParameters: {'q': query, if (commit) 'commit': 1},
    );
    return decodeEnvelope(
      response.data,
      (data) => listAt(data, 'suggestions', SearchSuggestionDto.fromJson),
    ).data;
  }

  Future<List<String>> popular() async {
    final response = await _dio.get<Object?>(
      '/mobile/storefront/search/popular',
    );
    return decodeEnvelope(response.data, (data) {
      final terms = asJsonObject(data)['terms'];
      if (terms is! List) {
        throw const FormatException('terms must be a list.');
      }
      return [for (final term in terms) term as String];
    }).data;
  }
}
