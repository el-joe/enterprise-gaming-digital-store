import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/features/code_reveal/data/models/reveal_models.dart';
import 'package:dio/dio.dart';

/// Confirm-password and reveal (API_DOCUMENTATION.md 4a, 1c). Reveal bodies are decoded in memory
/// only: the requests are marked sensitive (the redacting log drops them, as it drops every
/// `/reveal` path), never retried, and no HTTP cache exists in the client.
class CodeRevealRemoteDataSource {
  CodeRevealRemoteDataSource(this._dio);

  final Dio _dio;

  static final _sensitive = Options(
    extra: {RequestExtras.sensitive: true, RequestExtras.noRetry: true},
    headers: {'Cache-Control': 'no-store'},
  );

  Future<void> confirmPassword(String password) async {
    await _dio.post<Object?>(
      '/mobile/account/confirm-password',
      data: {'password': password},
      options: _sensitive,
    );
  }

  Future<RevealedCodeDto> revealAccount(String order, String unit) =>
      _reveal('/mobile/account/orders/$order/units/$unit/reveal');

  Future<RevealedCodeDto> revealGuest(String order, String unit) =>
      _reveal('/mobile/checkout/guest/orders/$order/units/$unit/reveal');

  Future<RevealedCodeDto> _reveal(String path) async {
    final response = await _dio.post<Object?>(path, options: _sensitive);
    return decodeEnvelope(
      response.data,
      (data) => RevealedCodeDto.fromJson(asJsonObject(data)),
    ).data;
  }
}
