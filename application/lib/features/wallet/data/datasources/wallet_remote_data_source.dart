import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/api_envelope.dart';
import 'package:application/features/wallet/data/models/wallet_models.dart';
import 'package:dio/dio.dart';

/// `GET /api/v1/mobile/account/wallet[/transactions]` (API_DOCUMENTATION.md 4b).
class WalletRemoteDataSource {
  WalletRemoteDataSource(this._dio);

  final Dio _dio;

  Future<WalletDto> wallet() async {
    final response = await _dio.get<Object?>('/mobile/account/wallet');
    return decodeEnvelope(
      response.data,
      (data) => WalletDto.fromJson(objectAt(data, 'wallet')),
    ).data;
  }

  Future<ApiEnvelope<List<WalletTransactionDto>>> transactions({
    required int page,
    required int perPage,
  }) async {
    final response = await _dio.get<Object?>(
      '/mobile/account/wallet/transactions',
      queryParameters: {'page': page, 'per_page': perPage},
    );
    return decodeEnvelope(
      response.data,
      (data) => listOf(data, WalletTransactionDto.fromJson),
    );
  }
}
