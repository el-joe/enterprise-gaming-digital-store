import 'package:application/core/network/failure.dart';
import 'package:flutter/foundation.dart';

/// Checks done on the device before calling the server (the server stays authoritative).
enum LocalFieldIssue {
  required,
  invalidEmail,
  passwordMismatch,
  termsRequired,
  otpIncomplete,
  codeInvalid,
}

/// The message beside one field: the server's (already localized by `Accept-Language`) or a
/// local check the page translates.
@immutable
final class FieldIssue {
  const FieldIssue.server(String this.serverMessage) : local = null;

  const FieldIssue.local(LocalFieldIssue this.local) : serverMessage = null;

  final String? serverMessage;
  final LocalFieldIssue? local;

  @override
  bool operator ==(Object other) =>
      other is FieldIssue &&
      other.serverMessage == serverMessage &&
      other.local == local;

  @override
  int get hashCode => Object.hash(serverMessage, local);
}

/// State shared by the auth forms: submitting, errors per field (dot keys of a 422), a failure
/// for the banner, the rate-limit deadline and the expired two-factor challenge.
@immutable
final class AuthFormState {
  const AuthFormState({
    this.submitting = false,
    this.fieldErrors = const {},
    this.failure,
    this.retryAt,
    this.expired = false,
  });

  /// Maps a failed submit: field errors beside their fields, 429 to a deadline (60 s when the
  /// server sent no `Retry-After`), anything else to the banner.
  factory AuthFormState.fromFailure(
    Failure failure,
    DateTime now, {
    Set<String> fields = const {},
  }) {
    switch (failure) {
      case ValidationFailure(:final fieldErrors):
        final mapped = <String, FieldIssue>{
          for (final entry in fieldErrors.entries)
            if (entry.value.isNotEmpty && fields.contains(entry.key))
              entry.key: FieldIssue.server(entry.value.first),
        };
        return AuthFormState(
          fieldErrors: mapped,
          failure: mapped.isEmpty ? failure : null,
        );
      case RateLimitedFailure(:final retryAfter):
        return AuthFormState(
          failure: failure,
          retryAt: now.add(retryAfter ?? const Duration(seconds: 60)),
        );
      default:
        return AuthFormState(failure: failure);
    }
  }

  final bool submitting;
  final Map<String, FieldIssue> fieldErrors;
  final Failure? failure;
  final DateTime? retryAt;
  final bool expired;

  bool isRateLimited(DateTime now) => retryAt != null && now.isBefore(retryAt!);

  AuthFormState copyWith({
    bool? submitting,
    Map<String, FieldIssue>? fieldErrors,
    Failure? Function()? failure,
    DateTime? Function()? retryAt,
    bool? expired,
  }) => AuthFormState(
    submitting: submitting ?? this.submitting,
    fieldErrors: fieldErrors ?? this.fieldErrors,
    failure: failure == null ? this.failure : failure(),
    retryAt: retryAt == null ? this.retryAt : retryAt(),
    expired: expired ?? this.expired,
  );
}

final _email = RegExp(r'^[^\s@]+@[^\s@]+\.[^\s@]+$');

/// Format checks only; the server validates for real.
bool looksLikeEmail(String value) => _email.hasMatch(value.trim());
