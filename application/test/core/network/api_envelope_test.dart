import 'package:application/core/money/money.dart';
import 'package:application/core/network/api_envelope.dart';
import 'package:application/core/network/api_error.dart';
import 'package:application/core/network/error_mapper.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('decodes data and pagination meta', () {
    final envelope = ApiEnvelope<List<String>>.fromJson({
      'success': true,
      'message': 'OK',
      'data': ['a', 'b'],
      'meta': {'current_page': 1, 'per_page': 20, 'total': 41, 'last_page': 3},
    }, (data) => (data! as List<Object?>).cast<String>());
    expect(envelope.data, ['a', 'b']);
    expect(envelope.message, 'OK');
    expect(
      envelope.page,
      const PageMeta(currentPage: 1, perPage: 20, total: 41, lastPage: 3),
    );
    expect(envelope.page!.hasNextPage, isTrue);
  });

  test('accepts Laravel empty meta [] and missing meta', () {
    final a = ApiEnvelope<Object?>.fromJson({
      'success': true,
      'data': null,
      'meta': <Object?>[],
    }, (d) => d);
    final b = ApiEnvelope<Object?>.fromJson({
      'success': true,
      'data': null,
    }, (d) => d);
    expect(a.meta, isEmpty);
    expect(b.page, isNull);
  });

  test('rejects error envelopes and non-objects', () {
    expect(
      () => ApiEnvelope<Object?>.fromJson({
        'success': false,
        'code': 'x',
      }, (d) => d),
      throwsFormatException,
    );
    expect(
      () => ApiEnvelope<Object?>.fromJson('html', (d) => d),
      throwsFormatException,
    );
  });

  test(
    'a numeric money amount inside data becomes a DecodingFailure',
    () async {
      final result = await guardRequest(
        () async => ApiEnvelope<Money>.fromJson({
          'success': true,
          'data': {'amount': 10.5, 'currency': 'USD'},
        }, Money.fromJson),
      );
      expect(result.failureOrNull, isA<DecodingFailure>());
      expect(result.isOk, isFalse);
    },
  );

  test('Result folds both ways', () async {
    final ok = await guardRequest(() async => 3);
    expect(ok.fold((v) => v * 2, (_) => -1), 6);
    expect(ok.valueOrNull, 3);
    final err = await guardRequest<int>(
      () async => throw const FormatException('x'),
    );
    expect(err.fold((v) => v, (f) => f is DecodingFailure ? -1 : 0), -1);
    expect(mapError(err.failureOrNull!), same(err.failureOrNull));
  });

  test('ApiError parses the error envelope and ignores other bodies', () {
    final error = ApiError.tryParse({
      'success': false,
      'message': 'The given data was invalid.',
      'code': 'validation_failed',
      'errors': {
        'name': ['Required.'],
      },
      'request_id': '01JC',
    });
    expect(error, isNotNull);
    expect(error!.errors['name'], ['Required.']);
    expect(error.requestId, '01JC');
    expect(ApiError.tryParse('<html>'), isNull);
    expect(ApiError.tryParse({'success': true, 'code': 'x'}), isNull);
    final malformed = ApiError.tryParse({
      'success': false,
      'code': 'x',
      'errors': 'oops',
    });
    expect(malformed?.code, 'x');
    expect(malformed?.errors, isEmpty);
  });
}
