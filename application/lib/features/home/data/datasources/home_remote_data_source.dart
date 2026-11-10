import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/api_envelope.dart';
import 'package:application/features/home/data/models/home_models.dart';
import 'package:dio/dio.dart';

/// `GET /api/v1/mobile/storefront/home` (API_DOCUMENTATION.md 2c).
class HomeRemoteDataSource {
  HomeRemoteDataSource(this._dio);

  final Dio _dio;

  Future<ApiEnvelope<HomeDto>> fetch() async {
    final response = await _dio.get<Object?>('/mobile/storefront/home');
    return decodeEnvelope(
      response.data,
      (data) => HomeDto.fromJson(asJsonObject(data)),
    );
  }
}
