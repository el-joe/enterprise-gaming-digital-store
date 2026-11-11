/// Biometric unlock and re-auth (owner decision 6, ADR-040). Pure Dart: the platform key store
/// and the server are behind the contracts in `domain/repositories`.
library;

/// Whether this device can hold a biometric-gated key (a strong biometric is enrolled).
enum BiometricSupport { available, notEnrolled, unavailable }

/// Why a biometric key operation did not produce a result.
enum BiometricIssue {
  /// The customer dismissed the prompt.
  cancelled,

  /// The key is gone: biometrics changed (`setInvalidatedByBiometricEnrollment` /
  /// `.biometryCurrentSet`) or it was never created. Falls back to the password.
  invalidated,

  /// Too many failed attempts; the platform blocks biometrics for a while.
  lockedOut,

  /// No usable biometric hardware or enrolment.
  unavailable,

  /// Anything else the platform reported.
  failed,
}

/// Outcome of a key operation: a value or a [BiometricIssue].
sealed class BiometricResult<T> {
  const BiometricResult();
}

final class BiometricDone<T> extends BiometricResult<T> {
  const BiometricDone(this.value);

  final T value;
}

final class BiometricFailed<T> extends BiometricResult<T> {
  const BiometricFailed(this.issue);

  final BiometricIssue issue;
}

/// The texts of the system biometric prompt, localized by the caller.
final class BiometricPromptText {
  const BiometricPromptText({required this.title, required this.cancel});

  final String title;
  final String cancel;
}
