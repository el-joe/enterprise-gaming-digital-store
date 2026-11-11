import 'dart:io';

import 'package:application/core/config/app_env.dart';
import 'package:application/core/network/dio_client.dart';
import 'package:application/core/network/interceptors/redacting_log_interceptor.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/code_reveal/code_reveal_providers.dart';
import 'package:application/features/code_reveal/data/datasources/code_reveal_remote_data_source.dart';
import 'package:application/features/code_reveal/domain/entities/revealed_code.dart';
import 'package:application/features/code_reveal/presentation/controllers/reveal_controller.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import '../../helpers/fake_http_adapter.dart';
import '../../helpers/fixtures.dart';
import '../../helpers/memory_stores.dart';
import '../../helpers/pump_app.dart';
import '../../helpers/repo_harness.dart';

const canary = 'FIXTURE-CODE-0000';
const target = RevealTarget.account(
  orderPublicId: '01jf0000000000000000000001',
  unitPublicId: '01JF0000000000000000000002',
);

void main() {
  test('the redacting log drops reveal request and response bodies', () async {
    final lines = <String>[];
    final h = Harness(
      stored: {
        SecureStore.accessTokenKey: 'a',
        SecureStore.refreshTokenKey: 'r',
      },
    );
    h.dio.interceptors.add(RedactingLogInterceptor(log: lines.add));
    h.serveFixture('code_reveal/reveal_ok');

    await h.dio.post<Object?>(
      '/mobile/account/orders/${target.orderPublicId}/units/${target.unitPublicId}/reveal',
    );
    h.serveFixture('code_reveal/guest_reveal_ok');
    await h.dio.post<Object?>(
      '/mobile/checkout/guest/orders/${target.orderPublicId}/units/${target.unitPublicId}/reveal',
    );

    expect(lines, isNotEmpty);
    expect(lines.join('\n'), isNot(contains(canary)));
    expect(lines.join('\n'), isNot(contains('FIXTURE-PIN')));
    expect(
      lines.where((l) => l.startsWith('<--')),
      everyElement(contains('body=[omitted]')),
    );
  });

  test('a reveal through the providers writes nothing to storage and leaves no provider alive', () async {
    final storage = memorySecureStorage({
      SecureStore.accessTokenKey: 'a',
      SecureStore.refreshTokenKey: 'r',
    });
    final container = ProviderContainer(
      overrides: [
        appEnvProvider.overrideWithValue(testEnv),
        preferencesStoreProvider.overrideWithValue(
          PreferencesStore(InMemoryPreferencesBackend()),
        ),
        secureStoreProvider.overrideWithValue(SecureStore(storage: storage)),
      ],
    );
    addTearDown(container.dispose);
    final reveal = ApiFixture.load('code_reveal/reveal_ok');
    container.read(apiClientProvider).httpClientAdapter = FakeHttpAdapter(
      (_, _) async => reveal.response(),
    );
    clearInteractions(storage);

    final subscription = container.listen(revealCodeProvider, (_, _) {});
    final result = await subscription.read()(
      target,
      askPassword: () async => null,
    );
    expect(okValue(result).code, canary);
    subscription.close();
    await container.pump();

    expect(container.read(revealCodeProvider.exists), isFalse);
    expect(container.read(codeRevealRepositoryProvider.exists), isFalse);
    expect(container.read(codeRevealRemoteDataSourceProvider.exists), isFalse);
    verifyNever(
      () => storage.write(
        key: any(named: 'key'),
        value: any(named: 'value'),
      ),
    );
  });

  test('no code reveal provider is kept alive or exposes a revealed code', () {
    final source = File('lib/features/code_reveal/code_reveal_providers.dart')
        .readAsStringSync();
    expect(source, isNot(contains('keepAlive')));
    expect(source, isNot(contains('RevealedCode ')));
    expect(source, isNot(contains('Future<RevealedCode>')));
  });

  test('a revealed code never prints its value', () {
    const code = RevealedCode(code: canary, pin: 'FIXTURE-PIN-0000');
    expect('$code', isNot(contains(canary)));
    expect('$code', isNot(contains('FIXTURE-PIN')));
  });

  group('M-A14 reveal screen', () {
    final presentation = Directory('lib/features/code_reveal/presentation')
        .listSync(recursive: true)
        .whereType<File>()
        .where(
          (f) =>
              f.path.endsWith('.dart') &&
              !f.path.endsWith('.g.dart') &&
              !f.path.endsWith('.freezed.dart'),
        )
        .toList();

    test('the screen controller is auto-disposed (never keep-alive)', () {
      final controller = File(
        'lib/features/code_reveal/presentation/controllers/reveal_controller.dart',
      ).readAsStringSync();
      expect(controller, contains('@riverpod\nclass RevealController'));
      expect(controller, isNot(contains('keepAlive')));
      final generated = File(
        'lib/features/code_reveal/presentation/controllers/reveal_controller.g.dart',
      ).readAsStringSync();
      expect(generated, contains('isAutoDispose: true'));
    });

    test('nothing in the reveal screen logs, stores, caches or reports', () {
      expect(presentation, isNotEmpty);
      for (final file in presentation) {
        final source = file.readAsStringSync();
        for (final banned in [
          'print(',
          'debugPrint(',
          'log(',
          'developer',
          'secure_store',
          'preferences_store',
          'shared_preferences',
          'flutter_secure_storage',
          'analytics',
          'crashlytics',
          'sentry',
          'cached_network_image',
          'path_provider',
          'dart:io',
        ]) {
          expect(
            source,
            isNot(contains(banned)),
            reason: '${file.path} must not use $banned',
          );
        }
      }
    });

    test('the screen state never prints the code', () {
      const state = RevealScreenState(
        phase: RevealPhase.revealed,
        code: RevealedCode(code: canary, pin: 'FIXTURE-PIN-0000'),
      );
      expect('$state', isNot(contains(canary)));
      expect('$state', isNot(contains('FIXTURE-PIN')));
    });

    test(
      'a reveal sends X-Device-Integrity, and the log keeps no code',
      () async {
        final lines = <String>[];
        final h = Harness(
          stored: {
            SecureStore.accessTokenKey: 'a',
            SecureStore.refreshTokenKey: 'r',
          },
        );
        h.dio.interceptors.add(RedactingLogInterceptor(log: lines.add));
        h.serveFixture('code_reveal/reveal_ok');
        final dto = await CodeRevealRemoteDataSource(h.dio).revealAccount(
          target.orderPublicId,
          target.unitPublicId,
          deviceIntegrity: 'compromised',
        );
        expect(dto.code, canary);
        expect(
          lines.join('\n'),
          contains('"X-Device-Integrity":"compromised"'),
        );
        expect(lines.join('\n'), isNot(contains(canary)));
      },
    );
  });
}
