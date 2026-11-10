import 'dart:convert';
import 'dart:typed_data';

import 'package:dio/dio.dart';

/// Answers requests from a handler and records every request it saw.
class FakeHttpAdapter implements HttpClientAdapter {
  FakeHttpAdapter(this.handler);

  final Future<ResponseBody> Function(RequestOptions options, int callIndex)
  handler;

  final List<RequestOptions> requests = [];

  /// Header snapshot per request, taken when the request reached the adapter.
  final List<Map<String, Object?>> sentHeaders = [];

  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<Uint8List>? requestStream,
    Future<void>? cancelFuture,
  ) {
    requests.add(options);
    sentHeaders.add(Map<String, Object?>.of(options.headers));
    return handler(options, requests.length - 1);
  }

  @override
  void close({bool force = false}) {}
}

ResponseBody jsonBody(
  Object? body, {
  int status = 200,
  Map<String, List<String>> headers = const {},
}) {
  return ResponseBody.fromString(
    jsonEncode(body),
    status,
    headers: {
      Headers.contentTypeHeader: [Headers.jsonContentType],
      ...headers,
    },
  );
}

Map<String, Object?> errorEnvelope(String code, {String message = 'Error.'}) =>
    {'success': false, 'message': message, 'code': code, 'request_id': 'req-1'};

Map<String, Object?> okEnvelope([Object? data]) => {
  'success': true,
  'message': 'OK',
  'data': data ?? <String, Object?>{},
  'meta': <String, Object?>{},
};

Dio dioWith(
  FakeHttpAdapter adapter, {
  List<Interceptor> interceptors = const [],
}) {
  final dio = Dio(BaseOptions(baseUrl: 'https://api.test/api/v1'))
    ..httpClientAdapter = adapter;
  dio.interceptors.addAll(interceptors);
  return dio;
}
