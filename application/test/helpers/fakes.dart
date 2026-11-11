import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/features/account/domain/repositories/account_repository.dart';
import 'package:application/features/app_config/data/models/app_config_models.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';
import 'package:application/features/app_config/domain/repositories/app_config_repository.dart';
import 'package:application/features/auth/domain/entities/auth_entities.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/auth/domain/repositories/auth_repository.dart';
import 'package:application/features/auth/domain/repositories/biometric_repositories.dart';
import 'package:mocktail/mocktail.dart';

import 'fixtures.dart';

/// `app_config/config_ok` decoded through the real DTO.
AppConfig fixtureAppConfig({
  bool maintenance = false,
  bool updateRequired = false,
  String? maintenanceMessage,
}) {
  final body =
      ApiFixture.load('app_config/config_ok').body as Map<String, dynamic>;
  final data = body['data'] as Map<String, dynamic>;
  final config = AppConfigDto.fromJson(data['config'] as Map<String, dynamic>)
      .toEntity();
  return config.copyWith(
    maintenanceEnabled: maintenance,
    maintenanceMessage: maintenanceMessage,
    update: AppUpdate(
      platform: 'android',
      version: '1.0.0',
      required: updateRequired,
      available: updateRequired,
    ),
  );
}

/// Answers `GET /app/config` from [results] in order (the last one repeats).
class FakeAppConfigRepository implements AppConfigRepository {
  FakeAppConfigRepository([List<Result<AppConfig>>? results])
    : results = results ?? [Ok(fixtureAppConfig())];

  final List<Result<AppConfig>> results;
  final calls = <({String? platform, String? version})>[];

  /// When set, each fetch waits for it (to see the loading state).
  Completer<void>? gate;

  @override
  Future<Result<AppConfig>> fetch({String? platform, String? version}) async {
    calls.add((platform: platform, version: version));
    await gate?.future;
    final index = calls.length - 1;
    return results[index < results.length ? index : results.length - 1];
  }
}

const testUser = AuthUser(
  publicId: '01JFTESTUSER0000000000000',
  name: 'Sara Khalil',
  email: 'sara.k@example.com',
  type: 'customer',
  emailVerified: true,
  twoFactorEnabled: false,
);

/// [AuthRepository] with scripted answers. Successful sign-ins call [onSignedIn] (the real
/// repository flips the session after storing the tokens).
class FakeAuthRepository implements AuthRepository {
  FakeAuthRepository({this.onSignedIn, this.onSignedOut});

  void Function()? onSignedIn;
  void Function()? onSignedOut;

  Result<LoginOutcome> loginResult = const Ok(LoginOutcome.signedIn(testUser));
  Result<AuthUser> twoFactorResult = const Ok(testUser);
  Result<AuthUser> registerResult = Ok(testUser.copyWith(emailVerified: false));
  Result<AuthUser> meResult = const Ok(testUser);
  Result<void> logoutResult = const Ok(null);

  final loginCalls = <({String email, String password, String deviceName})>[];
  final twoFactorCalls = <({String token, String? code, String? recovery})>[];
  final registerCalls = <Map<String, String>>[];
  int logoutCalls = 0;
  int meCalls = 0;

  Result<T> _signIn<T>(Result<T> result) {
    if (result.isOk) {
      onSignedIn?.call();
    }
    return result;
  }

  @override
  Future<Result<LoginOutcome>> login({
    required String email,
    required String password,
    required String deviceName,
  }) async {
    loginCalls.add((email: email, password: password, deviceName: deviceName));
    final result = loginResult;
    if (result case Ok(value: LoginSignedIn())) {
      onSignedIn?.call();
    }
    return result;
  }

  @override
  Future<Result<AuthUser>> completeTwoFactor({
    required String challengeToken,
    String? code,
    String? recoveryCode,
  }) async {
    twoFactorCalls.add((
      token: challengeToken,
      code: code,
      recovery: recoveryCode,
    ));
    return _signIn(twoFactorResult);
  }

  @override
  Future<Result<AuthUser>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String deviceName,
  }) async {
    registerCalls.add({
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'device_name': deviceName,
    });
    return _signIn(registerResult);
  }

  @override
  Future<Result<AuthUser>> me() async {
    meCalls++;
    return meResult;
  }

  @override
  Future<Result<void>> logout() async {
    logoutCalls++;
    onSignedOut?.call();
    return logoutResult;
  }

  @override
  Future<Result<void>> logoutAll() => logout();

  @override
  Future<Result<void>> refresh() async => const Ok(null);

  @override
  Future<bool> hasSession() async => false;
}

class MockAccountRepository extends Mock implements AccountRepository {}

/// Scripted [BiometricKeyStore] standing in for the Keystore / Secure Enclave plugin.
class FakeBiometricKeyStore implements BiometricKeyStore {
  FakeBiometricKeyStore({this.supportValue = BiometricSupport.unavailable});

  BiometricSupport supportValue;
  BiometricResult<String> createResult = const BiometricDone(
    'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEfakePublicKeyBase64DerSpki==',
  );
  final signResults = <BiometricResult<String>>[];
  final signedPayloads = <String>[];
  final prompts = <String>[];
  bool hasKey = false;
  int deleteCalls = 0;

  @override
  Future<BiometricSupport> support() async => supportValue;

  @override
  Future<BiometricResult<String>> createKey(BiometricPromptText prompt) async {
    prompts.add(prompt.title);
    if (createResult is BiometricDone<String>) {
      hasKey = true;
    }
    return createResult;
  }

  @override
  Future<BiometricResult<String>> sign(
    String payload,
    BiometricPromptText prompt,
  ) async {
    prompts.add(prompt.title);
    signedPayloads.add(payload);
    if (!hasKey) {
      return const BiometricFailed(BiometricIssue.invalidated);
    }
    if (signResults.isEmpty) {
      return const BiometricDone('MEUCIQDfakeSignature==');
    }
    return signResults.removeAt(0);
  }

  @override
  Future<void> deleteKey() async {
    deleteCalls++;
    hasKey = false;
  }
}

/// Scripted server side of biometric re-auth.
class FakeBiometricAuthRepository implements BiometricAuthRepository {
  final registerResults = <Result<void>>[];
  Result<String> challengeResult = const Ok(
    'boxcards-biometric-v1.key.token.random',
  );
  Result<void> confirmResult = const Ok(null);
  final confirmPasswordResults = <Result<void>>[];
  final registeredKeys = <String>[];
  final confirmed = <({String challenge, String signature})>[];
  final passwords = <String>[];
  int deleteCalls = 0;

  @override
  Future<Result<void>> registerKey(String publicKey) async {
    registeredKeys.add(publicKey);
    return registerResults.isEmpty
        ? const Ok(null)
        : registerResults.removeAt(0);
  }

  @override
  Future<Result<void>> deleteKey() async {
    deleteCalls++;
    return const Ok(null);
  }

  @override
  Future<Result<String>> challenge() async => challengeResult;

  @override
  Future<Result<void>> confirm({
    required String challenge,
    required String signature,
  }) async {
    confirmed.add((challenge: challenge, signature: signature));
    return confirmResult;
  }

  @override
  Future<Result<void>> confirmPassword(String password) async {
    passwords.add(password);
    return confirmPasswordResults.isEmpty
        ? const Ok(null)
        : confirmPasswordResults.removeAt(0);
  }
}

class MemoryEnrolmentStore implements BiometricEnrolmentStore {
  bool enabled = false;

  @override
  Future<bool> isEnabled() async => enabled;

  @override
  Future<void> setEnabled({required bool enabled}) async =>
      this.enabled = enabled;
}

/// [NativeBridge] without a platform channel.
class FakeNativeBridge extends NativeBridge {
  FakeNativeBridge({this.integrity = 'ok', this.opens = true});

  String integrity;
  bool opens;
  final opened = <Uri>[];
  int storeOpens = 0;

  @override
  Future<String> deviceIntegrity() async => integrity;

  @override
  Future<String> deviceName() async => 'Pixel 9';

  @override
  Future<bool> openExternalUrl(Uri url) async {
    opened.add(url);
    return opens;
  }

  @override
  Future<bool> openStoreListing() async {
    storeOpens++;
    return opens;
  }
}

/// A 422 `validation_failed` with field errors.
ValidationFailure validation(Map<String, String> errors) => ValidationFailure({
  for (final entry in errors.entries) entry.key: [entry.value],
});
