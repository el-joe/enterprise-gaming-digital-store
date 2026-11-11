/// Typed failures (MOBILE_APP_ROADMAP.md section 2.3). Presentation maps each to a localized
/// message; the server's `message` is kept only for logs and domain messages it owns.
sealed class Failure implements Exception {
  const Failure({this.requestId});

  /// `request_id` from the error envelope or the `X-Request-Id` header, when known.
  final String? requestId;

  /// Whether retrying the same request later can succeed without user action.
  bool get isRetryable => false;
}

/// No connection, DNS failure or TLS failure.
final class NetworkFailure extends Failure {
  const NetworkFailure();

  @override
  bool get isRetryable => true;
}

/// Connect, send or receive timeout.
final class TimeoutFailure extends Failure {
  const TimeoutFailure();

  @override
  bool get isRetryable => true;
}

/// 401 `unauthenticated` after the auth interceptor could not refresh the session, or another
/// 401 code such as `guest_checkout_disabled` (owner decision 4: sign in to check out).
final class UnauthenticatedFailure extends Failure {
  const UnauthenticatedFailure({this.code, super.requestId});

  /// The envelope `code` (`unauthenticated`, `guest_checkout_disabled`, ...), when sent.
  final String? code;
}

/// 403 `two_factor_required`.
final class TwoFactorRequiredFailure extends Failure {
  const TwoFactorRequiredFailure({super.requestId});
}

/// 403 `email_unverified`.
final class EmailUnverifiedFailure extends Failure {
  const EmailUnverifiedFailure({super.requestId});
}

/// 403 `reveal_denied`, `forbidden` and any other 403 code. Shown with a generic message.
final class ForbiddenFailure extends Failure {
  const ForbiddenFailure(this.code, {super.requestId});

  final String code;
}

/// 404.
final class NotFoundFailure extends Failure {
  const NotFoundFailure({super.requestId});
}

/// 409 (`idempotency_in_progress`, `idempotency_mismatch`, `order_not_payable`, `review_exists`, ...).
final class ConflictFailure extends Failure {
  const ConflictFailure(this.code, {this.message, super.requestId});

  final String code;
  final String? message;
}

/// 423 `recent_auth_required`: confirm the password, then retry once.
final class RecentAuthRequiredFailure extends Failure {
  const RecentAuthRequiredFailure({super.requestId});
}

/// 423 `biometric_locked` (OD-1, API_DOCUMENTATION.md 1g): the fifth bad signature revoked this
/// device's biometric key; the customer must confirm the password.
final class BiometricLockedFailure extends Failure {
  const BiometricLockedFailure({super.requestId});
}

/// 422 `validation_failed`. Keys use Laravel dot notation (`variants.0.price`).
final class ValidationFailure extends Failure {
  const ValidationFailure(this.fieldErrors, {this.message, super.requestId});

  final Map<String, List<String>> fieldErrors;
  final String? message;

  /// First message for [field], for binding beside a form field.
  String? firstErrorFor(String field) {
    final messages = fieldErrors[field];
    return messages == null || messages.isEmpty ? null : messages.first;
  }
}

/// 422 with a domain code (`price_changed`, `coupon_invalid`, `item_unavailable`, ...).
final class DomainFailure extends Failure {
  const DomainFailure(
    this.code, {
    this.message,
    this.errors = const {},
    super.requestId,
  });

  final String code;
  final String? message;
  final Map<String, List<String>> errors;
}

/// 429. [retryAfter] comes from the `Retry-After` header when present.
final class RateLimitedFailure extends Failure {
  const RateLimitedFailure({this.retryAfter, super.requestId});

  final Duration? retryAfter;

  @override
  bool get isRetryable => true;
}

/// 503 `maintenance`.
final class MaintenanceFailure extends Failure {
  const MaintenanceFailure({super.requestId});
}

/// Any other 5xx. Only the request id is shown, never the body.
final class ServerFailure extends Failure {
  const ServerFailure({required this.statusCode, super.requestId});

  final int statusCode;
}

/// A response that does not match the documented contract (for example money sent as a number).
final class DecodingFailure extends Failure {
  const DecodingFailure(this.reason, {super.requestId});

  final String reason;
}

/// The request was cancelled by the app.
final class CancelledFailure extends Failure {
  const CancelledFailure();
}

/// Anything outside the documented table (for example 400 or 419). Kept explicit so it is visible.
final class UnexpectedFailure extends Failure {
  const UnexpectedFailure({this.statusCode, this.code, super.requestId});

  final int? statusCode;
  final String? code;
}
