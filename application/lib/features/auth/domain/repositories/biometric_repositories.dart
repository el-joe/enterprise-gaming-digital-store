import 'package:application/core/network/result.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';

/// The ECDSA P-256 key held in the Android Keystore / iOS Secure Enclave, usable only after a
/// strong biometric prompt and invalidated when the enrolled biometrics change.
abstract interface class BiometricKeyStore {
  Future<BiometricSupport> support();

  /// Creates (or replaces) the key after a prompt; returns the public key as base64 DER
  /// SubjectPublicKeyInfo.
  Future<BiometricResult<String>> createKey(BiometricPromptText prompt);

  /// Signs the UTF-8 bytes of [payload] (ECDSA with SHA-256) after a prompt; returns the base64
  /// DER signature.
  Future<BiometricResult<String>> sign(
    String payload,
    BiometricPromptText prompt,
  );

  Future<void> deleteKey();
}

/// `/mobile/auth/biometric-*` and `confirm-password` (API_DOCUMENTATION.md 1g).
abstract interface class BiometricAuthRepository {
  /// 423 `recent_auth_required` until the password was confirmed on this token.
  Future<Result<void>> registerKey(String publicKey);

  Future<Result<void>> deleteKey();

  /// A single-use challenge (60 s). 404 when this device has no live key.
  Future<Result<String>> challenge();

  /// Sets the recent-auth window on the current token (as confirm-password does).
  Future<Result<void>> confirm({
    required String challenge,
    required String signature,
  });

  Future<Result<void>> confirmPassword(String password);
}

/// Whether biometric unlock is on for this sign-in. Kept in secure storage, wiped on sign-out.
abstract interface class BiometricEnrolmentStore {
  Future<bool> isEnabled();

  Future<void> setEnabled({required bool enabled});
}
