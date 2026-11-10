import 'package:flutter/foundation.dart';

/// A verified app link the app knows how to handle. Anything else is ignored.
@immutable
sealed class DeepLink {
  const DeepLink();
}

/// `https://<host>/app/payments/return/{order}` (gap G8). Every query parameter added by a
/// provider (for example Paymob's `success`) is ignored: the app always verifies with the server.
final class PaymentReturnLink extends DeepLink {
  const PaymentReturnLink(this.orderPublicId);

  final String orderPublicId;

  @override
  bool operator ==(Object other) =>
      other is PaymentReturnLink && other.orderPublicId == orderPublicId;

  @override
  int get hashCode => orderPublicId.hashCode;
}

/// Signed guest access link `/api/v1/checkout/guest/orders/{order}/access?expires=&signature=`.
/// The full [signedUri] is kept because the signature covers it.
final class GuestOrderAccessLink extends DeepLink {
  const GuestOrderAccessLink(this.orderPublicId, this.signedUri);

  final String orderPublicId;
  final Uri signedUri;

  @override
  bool operator ==(Object other) =>
      other is GuestOrderAccessLink &&
      other.orderPublicId == orderPublicId &&
      other.signedUri == signedUri;

  @override
  int get hashCode => Object.hash(orderPublicId, signedUri);
}

/// Signed email verification link `/email/verify/{id}/{hash}?expires=&signature=`.
final class VerifyEmailLink extends DeepLink {
  const VerifyEmailLink(this.userId, this.hash, this.signedUri);

  final String userId;
  final String hash;
  final Uri signedUri;

  @override
  bool operator ==(Object other) =>
      other is VerifyEmailLink &&
      other.userId == userId &&
      other.hash == hash &&
      other.signedUri == signedUri;

  @override
  int get hashCode => Object.hash(userId, hash, signedUri);
}

/// Password reset link `/reset-password/{token}?email=`.
final class ResetPasswordLink extends DeepLink {
  const ResetPasswordLink(this.token, this.email);

  final String token;
  final String? email;

  @override
  bool operator ==(Object other) =>
      other is ResetPasswordLink &&
      other.token == token &&
      other.email == email;

  @override
  int get hashCode => Object.hash(token, email);
}

final _segment = RegExp(r'^[A-Za-z0-9_-]{1,128}$');

/// Parses an incoming link. Only `https` links on [appLinkHost] are accepted; signed links must
/// carry `expires` and `signature`. Returns null for anything else.
DeepLink? parseDeepLink(Uri uri, {required String appLinkHost}) {
  if (uri.scheme != 'https' ||
      uri.host.toLowerCase() != appLinkHost.toLowerCase()) {
    return null;
  }
  if (uri.hasPort && uri.port != 443) {
    return null;
  }
  final s = uri.pathSegments.where((part) => part.isNotEmpty).toList();
  bool valid(Iterable<String> parts) => parts.every(_segment.hasMatch);
  bool signed() =>
      (uri.queryParameters['expires']?.isNotEmpty ?? false) &&
      (uri.queryParameters['signature']?.isNotEmpty ?? false);

  if (s.length == 4 &&
      s[0] == 'app' &&
      s[1] == 'payments' &&
      s[2] == 'return' &&
      valid([s[3]])) {
    return PaymentReturnLink(s[3]);
  }
  if (s.length == 7 &&
      s[0] == 'api' &&
      s[1] == 'v1' &&
      s[2] == 'checkout' &&
      s[3] == 'guest' &&
      s[4] == 'orders' &&
      s[6] == 'access' &&
      valid([s[5]]) &&
      signed()) {
    return GuestOrderAccessLink(s[5], uri);
  }
  if (s.length == 4 &&
      s[0] == 'email' &&
      s[1] == 'verify' &&
      valid([s[2], s[3]]) &&
      signed()) {
    return VerifyEmailLink(s[2], s[3], uri);
  }
  if (s.length == 2 && s[0] == 'reset-password' && valid([s[1]])) {
    final email = uri.queryParameters['email'];
    return ResetPasswordLink(
      s[1],
      email == null || email.isEmpty ? null : email,
    );
  }
  return null;
}
