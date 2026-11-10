import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'guards.g.dart';

enum SessionStatus { signedOut, signedIn }

/// Whether the store can be used by this build (`GET /app/config`, gap G9; 503 `maintenance`).
enum AppAvailability { available, maintenance, updateRequired }

/// Everything the guards decide on.
@immutable
final class GuardState {
  const GuardState({
    required this.session,
    required this.availability,
    this.emailVerified,
  });

  final SessionStatus session;
  final AppAvailability availability;

  /// Null until the profile is loaded; only `false` sends the customer to verification.
  final bool? emailVerified;
}

/// Returns where to send the user instead of [location], or null to stay.
///
/// Order: update required, maintenance, sign-in for protected routes, email verification, and
/// leaving sign-in once signed in.
String? redirectFor({
  required GuardState state,
  required Uri location,
  required RouteAccess access,
}) {
  final path = location.path;

  switch (state.availability) {
    case AppAvailability.updateRequired:
      return path == Routes.updateRequired.path
          ? null
          : Routes.updateRequired.path;
    case AppAvailability.maintenance:
      return path == Routes.maintenance.path ? null : Routes.maintenance.path;
    case AppAvailability.available:
      if (path == Routes.updateRequired.path ||
          path == Routes.maintenance.path) {
        return Routes.home.path;
      }
  }

  final signedIn = state.session == SessionStatus.signedIn;

  if (access != RouteAccess.public && !signedIn) {
    return Uri(
      path: Routes.login.path,
      queryParameters: {Routes.fromParameter: location.toString()},
    ).toString();
  }

  if (access == RouteAccess.verifiedEmail && state.emailVerified == false) {
    return Routes.verifyEmail.path;
  }

  if (path == Routes.login.path && signedIn) {
    return safeReturnLocation(location.queryParameters[Routes.fromParameter]);
  }

  return null;
}

/// Accepts only in-app absolute paths as a return location, so a crafted link cannot send the
/// user to another host after sign-in.
String safeReturnLocation(String? from) {
  if (from == null || !from.startsWith('/') || from.startsWith('//')) {
    return Routes.home.path;
  }
  final parsed = Uri.tryParse(from);
  if (parsed == null || parsed.hasScheme || parsed.hasAuthority) {
    return Routes.home.path;
  }
  if (parsed.path == Routes.login.path) {
    return Routes.home.path;
  }
  return from;
}

/// Session at launch, read from secure storage in `bootstrap.dart`.
@Riverpod(keepAlive: true)
SessionStatus initialSession(Ref ref) => SessionStatus.signedOut;

@Riverpod(keepAlive: true)
class Session extends _$Session {
  @override
  SessionStatus build() => ref.watch(initialSessionProvider);

  void markSignedIn() => state = SessionStatus.signedIn;

  /// Called after the tokens were already removed (failed refresh).
  void markSignedOut() => state = SessionStatus.signedOut;

  /// Removes the tokens and signs out locally.
  Future<void> signOut() async {
    await ref.read(secureStoreProvider).clearTokens();
    state = SessionStatus.signedOut;
  }
}

@Riverpod(keepAlive: true)
class EmailVerification extends _$EmailVerification {
  @override
  bool? build() => null;

  void update({required bool verified}) => state = verified;
}

@Riverpod(keepAlive: true)
class Availability extends _$Availability {
  @override
  AppAvailability build() => AppAvailability.available;

  void update(AppAvailability availability) => state = availability;
}
