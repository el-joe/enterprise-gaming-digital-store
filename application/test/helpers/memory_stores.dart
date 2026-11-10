import 'package:application/core/storage/preferences_store.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:mocktail/mocktail.dart';

class MockSecureStorage extends Mock implements FlutterSecureStorage {}

/// A mocked [FlutterSecureStorage] backed by [data].
MockSecureStorage memorySecureStorage([Map<String, String>? data]) {
  final values = data ?? <String, String>{};
  final storage = MockSecureStorage();
  when(() => storage.read(key: any(named: 'key'))).thenAnswer(
    (invocation) async => values[invocation.namedArguments[#key] as String],
  );
  when(
    () => storage.write(
      key: any(named: 'key'),
      value: any(named: 'value'),
    ),
  ).thenAnswer((invocation) async {
    final key = invocation.namedArguments[#key] as String;
    final value = invocation.namedArguments[#value] as String?;
    if (value == null) {
      values.remove(key);
    } else {
      values[key] = value;
    }
  });
  when(() => storage.delete(key: any(named: 'key')))
      .thenAnswer((invocation) async {
        values.remove(invocation.namedArguments[#key] as String);
      });
  when(storage.deleteAll).thenAnswer((_) async => values.clear());
  return storage;
}

class InMemoryPreferencesBackend implements PreferencesBackend {
  InMemoryPreferencesBackend([Map<String, Object>? values])
    : values = values ?? {};

  final Map<String, Object> values;

  @override
  bool? getBool(String key) => values[key] as bool?;

  @override
  String? getString(String key) => values[key] as String?;

  @override
  Future<void> remove(String key) async => values.remove(key);

  @override
  Future<void> setBool(String key, {required bool value}) async =>
      values[key] = value;

  @override
  Future<void> setString(String key, String value) async => values[key] = value;
}
