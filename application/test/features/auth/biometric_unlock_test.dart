import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/features/auth/data/biometric/plugin_biometric_key_store.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/auth/domain/usecases/biometric_unlock.dart';
import 'package:biometric_signature/biometric_signature.dart' as plugin;
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/fakes.dart';

const _prompt = BiometricPromptText(title: 'Confirm', cancel: 'Cancel');

void main() {
  late FakeBiometricKeyStore keys;
  late FakeBiometricAuthRepository server;
  late MemoryEnrolmentStore enrolment;
  late DisableBiometricUnlock disable;

  setUp(() {
    keys = FakeBiometricKeyStore(supportValue: BiometricSupport.available);
    server = FakeBiometricAuthRepository();
    enrolment = MemoryEnrolmentStore();
    disable = DisableBiometricUnlock(
      keys: keys,
      server: server,
      enrolment: enrolment,
    );
  });

  EnrolBiometricUnlock enrol() =>
      EnrolBiometricUnlock(keys: keys, server: server, enrolment: enrolment);

  group('EnrolBiometricUnlock', () {
    test(
      'creates the key, registers its public key and turns unlock on',
      () async {
        final outcome = await enrol()(
          prompt: _prompt,
          askPassword: (_) async => fail('no password needed'),
        );
        expect(outcome, isA<EnrolDone>());
        expect(server.registeredKeys, hasLength(1));
        expect(keys.hasKey, isTrue);
        expect(enrolment.enabled, isTrue);
      },
    );

    test(
      '423 asks for the password, confirms it and registers again once',
      () async {
        server.registerResults.add(const Err(RecentAuthRequiredFailure()));
        final asked = <Failure?>[];
        final outcome = await enrol()(
          prompt: _prompt,
          askPassword: (previous) async {
            asked.add(previous);
            return 'secret-password';
          },
        );
        expect(outcome, isA<EnrolDone>());
        expect(asked, [null]);
        expect(server.passwords, ['secret-password']);
        expect(server.registeredKeys, hasLength(2));
        expect(enrolment.enabled, isTrue);
      },
    );

    test('a wrong password is shown on the next prompt', () async {
      server.registerResults.add(const Err(RecentAuthRequiredFailure()));
      server.confirmPasswordResults.add(
        Err(validation({'password': 'The password is incorrect.'})),
      );
      final asked = <Failure?>[];
      final outcome = await enrol()(
        prompt: _prompt,
        askPassword: (previous) async {
          asked.add(previous);
          return asked.length == 1 ? 'wrong' : 'right';
        },
      );
      expect(outcome, isA<EnrolDone>());
      expect(asked.first, isNull);
      expect(asked.last, isA<ValidationFailure>());
      expect(server.passwords, ['wrong', 'right']);
    });

    test('a cancelled password sheet deletes the new key', () async {
      server.registerResults.add(const Err(RecentAuthRequiredFailure()));
      final outcome = await enrol()(
        prompt: _prompt,
        askPassword: (_) async => null,
      );
      expect(outcome, isA<EnrolCancelled>());
      expect(keys.hasKey, isFalse);
      expect(keys.deleteCalls, 1);
      expect(enrolment.enabled, isFalse);
    });

    test(
      'a refused registration deletes the key and reports the failure',
      () async {
        server.registerResults.add(const Err(NetworkFailure()));
        final outcome = await enrol()(
          prompt: _prompt,
          askPassword: (_) async => 'x',
        );
        expect(outcome, isA<EnrolFailed>());
        expect((outcome as EnrolFailed).failure, isA<NetworkFailure>());
        expect(keys.hasKey, isFalse);
        expect(enrolment.enabled, isFalse);
      },
    );

    test(
      'a cancelled prompt or missing biometrics never reach the server',
      () async {
        keys.createResult = const BiometricFailed(BiometricIssue.cancelled);
        expect(
          await enrol()(prompt: _prompt, askPassword: (_) async => 'x'),
          isA<EnrolCancelled>(),
        );
        keys.supportValue = BiometricSupport.notEnrolled;
        expect(
          await enrol()(prompt: _prompt, askPassword: (_) async => 'x'),
          isA<EnrolUnavailable>(),
        );
        expect(server.registeredKeys, isEmpty);
      },
    );
  });

  group('UnlockWithBiometric', () {
    UnlockWithBiometric unlock() =>
        UnlockWithBiometric(keys: keys, enrolment: enrolment, disable: disable);

    setUp(() {
      enrolment.enabled = true;
      keys.hasKey = true;
    });

    test('signs a fresh nonce: the platform releases the key only after biometrics', () async {
      expect(await unlock()(_prompt), BiometricCheck.confirmed);
      expect(await unlock()(_prompt), BiometricCheck.confirmed);
      expect(keys.signedPayloads, hasLength(2));
      expect(keys.signedPayloads.first, startsWith('boxcards-unlock.'));
      expect(keys.signedPayloads.first, isNot(keys.signedPayloads.last));
    });

    test(
      'changed biometrics turn unlock off and fall back to the password',
      () async {
        keys.signResults.add(const BiometricFailed(BiometricIssue.invalidated));
        expect(await unlock()(_prompt), BiometricCheck.usePassword);
        expect(enrolment.enabled, isFalse);
        expect(keys.deleteCalls, 1);
        expect(server.deleteCalls, 1);
      },
    );

    test('cancel keeps unlock on; lock-out uses the password', () async {
      keys.signResults
        ..add(const BiometricFailed(BiometricIssue.cancelled))
        ..add(const BiometricFailed(BiometricIssue.lockedOut));
      expect(await unlock()(_prompt), BiometricCheck.cancelled);
      expect(await unlock()(_prompt), BiometricCheck.usePassword);
      expect(enrolment.enabled, isTrue);
    });

    test('unlock off means the password', () async {
      enrolment.enabled = false;
      expect(await unlock()(_prompt), BiometricCheck.usePassword);
      expect(keys.signedPayloads, isEmpty);
    });
  });

  group('ConfirmRecentAuthWithBiometric (M-A14 re-auth)', () {
    ConfirmRecentAuthWithBiometric confirm() => ConfirmRecentAuthWithBiometric(
      keys: keys,
      server: server,
      enrolment: enrolment,
      disable: disable,
    );

    setUp(() {
      enrolment.enabled = true;
      keys.hasKey = true;
    });

    test('signs the server challenge and confirms it', () async {
      final result = await confirm()(_prompt);
      expect(result.check, BiometricCheck.confirmed);
      expect(keys.signedPayloads, ['boxcards-biometric-v1.key.token.random']);
      expect(
        server.confirmed.single.challenge,
        'boxcards-biometric-v1.key.token.random',
      );
      expect(server.confirmed.single.signature, 'MEUCIQDfakeSignature==');
    });

    test('not enrolled: use the password without calling the server', () async {
      enrolment.enabled = false;
      expect((await confirm()(_prompt)).check, BiometricCheck.usePassword);
      expect(server.confirmed, isEmpty);
    });

    test('404 biometric_not_registered turns unlock off locally', () async {
      server.challengeResult = const Err(NotFoundFailure());
      expect((await confirm()(_prompt)).check, BiometricCheck.usePassword);
      expect(enrolment.enabled, isFalse);
      expect(server.deleteCalls, 0);
    });

    test('changed biometrics revoke the key on both sides', () async {
      keys.signResults.add(const BiometricFailed(BiometricIssue.invalidated));
      expect((await confirm()(_prompt)).check, BiometricCheck.usePassword);
      expect(enrolment.enabled, isFalse);
      expect(server.deleteCalls, 1);
      expect(server.confirmed, isEmpty);
    });

    test(
      '423 biometric_locked: the server revoked the key; use the password',
      () async {
        server.confirmResult = const Err(BiometricLockedFailure());
        expect((await confirm()(_prompt)).check, BiometricCheck.usePassword);
        expect(enrolment.enabled, isFalse);
      },
    );

    test('a cancelled prompt and a bad signature are reported', () async {
      keys.signResults.add(const BiometricFailed(BiometricIssue.cancelled));
      expect((await confirm()(_prompt)).check, BiometricCheck.cancelled);
      server.confirmResult = const Err(DomainFailure('biometric_failed'));
      final failed = await confirm()(_prompt);
      expect(failed.check, BiometricCheck.failed);
      expect(failed.failure, isA<DomainFailure>());
      expect(enrolment.enabled, isTrue);
    });
  });

  group('PluginBiometricKeyStore', () {
    test('creates a strong-biometric ECDSA P-256 key invalidated by enrolment changes', () async {
      final api = _RecordingSignature();
      final store = PluginBiometricKeyStore(api);
      final created = await store.createKey(_prompt);
      expect(created, isA<BiometricDone<String>>());
      final config = api.keysConfig!;
      expect(config.signatureType, plugin.SignatureType.ecdsa);
      expect(config.enforceBiometric, isTrue);
      expect(config.setInvalidatedByBiometricEnrollment, isTrue);
      expect(config.useDeviceCredentials, isFalse);
      expect(config.requireAuthentication, isTrue);
      expect(api.alias, PluginBiometricKeyStore.keyAlias);

      final signed = await store.sign('payload', _prompt);
      expect((signed as BiometricDone<String>).value, 'c2lnbmF0dXJl');
      expect(api.signConfig!.allowDeviceCredentials, isFalse);
      expect(api.signedPayload, 'payload');
    });

    test('maps platform error codes', () {
      expect(
        PluginBiometricKeyStore.issueFor(plugin.BiometricError.userCanceled),
        BiometricIssue.cancelled,
      );
      expect(
        PluginBiometricKeyStore.issueFor(plugin.BiometricError.keyInvalidated),
        BiometricIssue.invalidated,
      );
      expect(
        PluginBiometricKeyStore.issueFor(plugin.BiometricError.lockedOut),
        BiometricIssue.lockedOut,
      );
      expect(
        PluginBiometricKeyStore.issueFor(plugin.BiometricError.notEnrolled),
        BiometricIssue.unavailable,
      );
      expect(
        PluginBiometricKeyStore.issueFor(plugin.BiometricError.unknown),
        BiometricIssue.failed,
      );
    });

    test('a thrown platform error is a failure, never an exception', () async {
      final store = PluginBiometricKeyStore(_ThrowingSignature());
      expect(await store.support(), BiometricSupport.unavailable);
      expect(await store.sign('x', _prompt), isA<BiometricFailed<String>>());
      await store.deleteKey();
    });
  });
}

class _RecordingSignature extends plugin.BiometricSignature {
  plugin.CreateKeysConfig? keysConfig;
  plugin.CreateSignatureConfig? signConfig;
  String? alias;
  String? signedPayload;

  @override
  Future<plugin.KeyCreationResult> createKeys({
    String? keyAlias,
    plugin.CreateKeysConfig? config,
    plugin.KeyFormat keyFormat = plugin.KeyFormat.base64,
    String? promptMessage,
  }) async {
    alias = keyAlias;
    keysConfig = config;
    return plugin.KeyCreationResult(
      code: plugin.BiometricError.success,
      publicKey: 'cHVibGlj',
    );
  }

  @override
  Future<plugin.SignatureResult> createSignature({
    required String payload,
    String? keyAlias,
    plugin.CreateSignatureConfig? config,
    plugin.SignatureFormat signatureFormat = plugin.SignatureFormat.base64,
    plugin.KeyFormat keyFormat = plugin.KeyFormat.base64,
    String? promptMessage,
  }) async {
    signConfig = config;
    signedPayload = payload;
    return plugin.SignatureResult(
      code: plugin.BiometricError.success,
      signature: 'c2lnbmF0dXJl',
    );
  }
}

class _ThrowingSignature extends plugin.BiometricSignature {
  @override
  Future<plugin.BiometricAvailability> biometricAuthAvailable({
    plugin.BiometricStrength biometricStrength =
        plugin.BiometricStrength.strong,
  }) => throw StateError('no plugin');

  @override
  Future<plugin.SignatureResult> createSignature({
    required String payload,
    String? keyAlias,
    plugin.CreateSignatureConfig? config,
    plugin.SignatureFormat signatureFormat = plugin.SignatureFormat.base64,
    plugin.KeyFormat keyFormat = plugin.KeyFormat.base64,
    String? promptMessage,
  }) => throw StateError('no plugin');

  @override
  Future<bool> deleteKeys({String? keyAlias}) => throw StateError('no plugin');
}
