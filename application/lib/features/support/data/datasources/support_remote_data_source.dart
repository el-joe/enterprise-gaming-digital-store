import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/api_envelope.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/features/support/data/models/support_models.dart';
import 'package:dio/dio.dart';

/// Tickets and refund requests under `/api/v1/mobile/account` (API_DOCUMENTATION.md 4b).
class SupportRemoteDataSource {
  SupportRemoteDataSource(this._dio);

  final Dio _dio;

  static final _write = Options(extra: {RequestExtras.noRetry: true});

  static SupportTicketDto _ticket(Object? data) =>
      SupportTicketDto.fromJson(objectAt(data, 'ticket'));

  Future<ApiEnvelope<List<SupportTicketDto>>> tickets({
    required int page,
    required int perPage,
  }) async {
    final response = await _dio.get<Object?>(
      '/mobile/account/tickets',
      queryParameters: {'page': page, 'per_page': perPage},
    );
    return decodeEnvelope(
      response.data,
      (data) => listOf(data, SupportTicketDto.fromJson),
    );
  }

  Future<SupportTicketDto> ticket(String publicId) async {
    final response = await _dio.get<Object?>(
      '/mobile/account/tickets/$publicId',
    );
    return decodeEnvelope(response.data, _ticket).data;
  }

  Future<SupportTicketDto> openTicket(Map<String, Object?> body) async {
    final response = await _dio.post<Object?>(
      '/mobile/account/tickets',
      data: body,
      options: _write,
    );
    return decodeEnvelope(response.data, _ticket).data;
  }

  Future<SupportTicketDto> reply(String publicId, String body) async {
    final response = await _dio.post<Object?>(
      '/mobile/account/tickets/$publicId/messages',
      data: {'body': body},
      options: _write,
    );
    return decodeEnvelope(response.data, _ticket).data;
  }

  Future<ApiEnvelope<List<RefundRequestDto>>> refundRequests({
    required int page,
    required int perPage,
  }) async {
    final response = await _dio.get<Object?>(
      '/mobile/account/refund-requests',
      queryParameters: {'page': page, 'per_page': perPage},
    );
    return decodeEnvelope(
      response.data,
      (data) => listOf(data, RefundRequestDto.fromJson),
    );
  }

  Future<RefundRequestDto> requestRefund(Map<String, Object?> body) async {
    final response = await _dio.post<Object?>(
      '/mobile/account/refund-requests',
      data: body,
      options: _write,
    );
    return decodeEnvelope(
      response.data,
      (data) => RefundRequestDto.fromJson(objectAt(data, 'refund_request')),
    ).data;
  }
}
