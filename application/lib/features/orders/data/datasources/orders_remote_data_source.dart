import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/api_envelope.dart';
import 'package:application/features/orders/data/models/order_models.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:dio/dio.dart';

/// Account orders and the guest order detail (API_DOCUMENTATION.md 4c).
class OrdersRemoteDataSource {
  OrdersRemoteDataSource(this._dio);

  final Dio _dio;

  Future<ApiEnvelope<List<OrderSummaryDto>>> list(OrdersQuery query) async {
    final response = await _dio.get<Object?>(
      '/mobile/account/orders',
      queryParameters: {
        'page': query.page,
        'per_page': query.perPage,
        'status': ?query.status,
        'from': ?query.from,
        'to': ?query.to,
      },
    );
    return decodeEnvelope(
      response.data,
      (data) => listOf(data, OrderSummaryDto.fromJson),
    );
  }

  Future<OrderDetailDto> detail(String orderPublicId) async {
    final response = await _dio.get<Object?>(
      '/mobile/account/orders/$orderPublicId',
    );
    return decodeEnvelope(
      response.data,
      (data) => OrderDetailDto.fromJson(objectAt(data, 'order')),
    ).data;
  }

  Future<ApiEnvelope<OrderDetailDto>> guestDetail(String orderPublicId) async {
    final response = await _dio.get<Object?>(
      '/mobile/checkout/guest/orders/$orderPublicId',
    );
    return decodeEnvelope(
      response.data,
      (data) => OrderDetailDto.fromJson(objectAt(data, 'order')),
    );
  }
}
