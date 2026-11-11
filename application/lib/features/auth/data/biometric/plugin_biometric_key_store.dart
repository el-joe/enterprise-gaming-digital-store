import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/auth/domain/repositories/biometric_repositories.dart';
import 'package:biometric_signature/biometric_signature.dart' as plugin;

/// [BiometricKeyStore] on `biometric_signature` (README "Biometric unlock"): an ECDSA P-256 key in
/// the Android Keystore (StrongBox when present) or the iOS Secure Enclave, created with
/// `setUserAuthenticationRequired` / biometric access control, usable only after a **strong**
/// biometric prompt (no device-credential fallback) and invalidated when the enrolled biometrics
/// change (`setInvalidatedByBiometricEnrollment` / `.biometryCurrentSet`).
class PluginBiometricKeyStore implements BiometricKeyStore {
  PluginBiometricKeyStore([plugin.BiometricSignature? api])
    : _api = api ?? plugin.BiometricSignature();

  /// One key per install; replaced on every enrolment.
  static const keyAlias = 'shop.boxcards.app.unlock';

  final plugin.BiometricSignature _api;

  @override
  Future<BiometricSupport> support() async {
    try {
      final availability = await _api.biometricAuthAvailable();
      if (availability.canAuthenticate ?? false) {
        return BiometricSupport.available;
      }
      final hasHardware = (availability.availableBiometrics ?? const []).any(
        (type) => type != null && type != plugin.BiometricType.unavailable,
      );
      return hasHardware || availability.hasEnrolledBiometrics == false
          ? BiometricSupport.notEnrolled
          : BiometricSupport.unavailable;
    } on Object {
      return BiometricSupport.unavailable;
    }
  }

  @override
  Future<BiometricResult<String>> createKey(BiometricPromptText prompt) async {
    try {
      final result = await _api.createKeys(
        keyAlias: keyAlias,
        promptMessage: prompt.title,
        config: plugin.CreateKeysConfig(
          signatureType: plugin.SignatureType.ecdsa,
          enforceBiometric: true,
          setInvalidatedByBiometricEnrollment: true,
          useDeviceCredentials: false,
          enableDecryption: false,
          requireAuthentication: true,
          cancelButtonText: prompt.cancel,
        ),
      );
      final key = result.publicKey;
      if (result.code == plugin.BiometricError.success &&
          key != null &&
          key.isNotEmpty) {
        return BiometricDone(key);
      }
      return BiometricFailed(issueFor(result.code));
    } on Object {
      return const BiometricFailed(BiometricIssue.failed);
    }
  }

  @override
  Future<BiometricResult<String>> sign(
    String payload,
    BiometricPromptText prompt,
  ) async {
    try {
      final result = await _api.createSignature(
        payload: payload,
        keyAlias: keyAlias,
        promptMessage: prompt.title,
        config: plugin.CreateSignatureConfig(
          allowDeviceCredentials: false,
          cancelButtonText: prompt.cancel,
        ),
      );
      final signature = result.signature;
      if (result.code == plugin.BiometricError.success &&
          signature != null &&
          signature.isNotEmpty) {
        return BiometricDone(signature);
      }
      return BiometricFailed(issueFor(result.code));
    } on Object {
      return const BiometricFailed(BiometricIssue.failed);
    }
  }

  @override
  Future<void> deleteKey() async {
    try {
      await _api.deleteKeys(keyAlias: keyAlias);
    } on Object {
      // Nothing to do: a missing key is the wanted state.
    }
  }

  /// Maps the plugin's error codes to the domain's.
  static BiometricIssue issueFor(plugin.BiometricError? code) => switch (code) {
    plugin.BiometricError.userCanceled ||
    plugin.BiometricError.systemCanceled => BiometricIssue.cancelled,
    plugin.BiometricError.keyInvalidated ||
    plugin.BiometricError.keyNotFound => BiometricIssue.invalidated,
    plugin.BiometricError.lockedOut ||
    plugin.BiometricError.lockedOutPermanent => BiometricIssue.lockedOut,
    plugin.BiometricError.notAvailable ||
    plugin.BiometricError.notEnrolled ||
    plugin.BiometricError.notSupported ||
    plugin.BiometricError.passcodeNotSet => BiometricIssue.unavailable,
    _ => BiometricIssue.failed,
  };
}
