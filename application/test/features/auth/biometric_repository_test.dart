import 'package:application/core/network/failure.dart';
import 'package:application/features/auth/data/datasources/biometric_remote_data_source.dart';
import 'package:application/features/auth/data/repositories/biometric_auth_repository_impl.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/repo_harness.dart';

/// The biometric endpoints (API_DOCUMENTATION.md 1g) against the backend contract fixtures.
void main() {
  late Harness harness;
  late BiometricAuthRepositoryImpl repository;

  setUp(() {
    harness = Harness(
      stored: {'auth.access_token': 'access', 'auth.refresh_token': 'refresh'},
    );
    repository = BiometricAuthRepositoryImpl(
      BiometricRemoteDataSource(harness.dio),
    );
  });

  test('registers the key; 423 until the password was confirmed', () async {
    harness.serveFixture('auth/biometric_key_registered');
    okValue(await repository.registerKey('MFkw...'));
    expect(harness.adapter.requests.single.data, {
      'public_key': 'MFkw...',
      'algorithm': 'ES256',
    });

    harness.serveFixture('auth/biometric_key_recent_auth_required');
    failureOf<RecentAuthRequiredFailure>(await repository.registerKey('MFkw'));
  });

  test('issues a challenge; 404 when this device has no key', () async {
    harness.serveFixture('auth/biometric_challenge');
    expect(okValue(await repository.challenge()), 'fixture-challenge');

    harness.serveFixture('auth/biometric_not_registered');
    failureOf<NotFoundFailure>(await repository.challenge());
  });

  test('confirms a signature; bad signatures and used challenges are domain failures', () async {
    harness.serveFixture('auth/biometric_confirmed');
    okValue(await repository.confirm(challenge: 'c', signature: 's'));
    expect(harness.adapter.requests.single.data, {
      'challenge': 'c',
      'signature': 's',
    });

    harness.serveFixture('auth/biometric_failed');
    expect(
      failureOf<DomainFailure>(
        await repository.confirm(challenge: 'c', signature: 's'),
      ).code,
      'biometric_failed',
    );

    harness.serveFixture('auth/biometric_challenge_invalid');
    expect(
      failureOf<DomainFailure>(
        await repository.confirm(challenge: 'c', signature: 's'),
      ).code,
      'biometric_challenge_invalid',
    );
  });

  test('deletes the key', () async {
    harness.serveFixture('auth/biometric_key_deleted');
    okValue(await repository.deleteKey());
  });
}
