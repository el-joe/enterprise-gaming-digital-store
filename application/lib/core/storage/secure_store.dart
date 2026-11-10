import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'secure_store.g.dart';

/// Access and refresh tokens issued by the mobile auth endpoints (gap G1).
final class AuthTokens {
  const AuthTokens({required this.accessToken, required this.refreshToken});

  final String accessToken;
  final String refreshToken;
}

/// The only place tokens live (MOBILE_APP_ROADMAP.md 6.1): Keychain on iOS
/// (`first_unlock_this_device`, never synced) and Keystore-backed storage on Android.
class SecureStore {
  const SecureStore({this._storage = _defaultStorage});

  static const _defaultStorage = FlutterSecureStorage(
    iOptions: IOSOptions(
      accessibility: KeychainAccessibility.first_unlock_this_device,
    ),
  );

  static const accessTokenKey = 'auth.access_token';
  static const refreshTokenKey = 'auth.refresh_token';
  static const cartTokenKey = 'cart.token';
  static const guestOrderTokenPrefix = 'guest_order.token.';

  final FlutterSecureStorage _storage;

  Future<String?> readAccessToken() => _storage.read(key: accessTokenKey);

  Future<String?> readRefreshToken() => _storage.read(key: refreshTokenKey);

  Future<AuthTokens?> readTokens() async {
    final access = await readAccessToken();
    final refresh = await readRefreshToken();
    if (access == null || refresh == null) {
      return null;
    }
    return AuthTokens(accessToken: access, refreshToken: refresh);
  }

  Future<bool> hasSession() async => await readTokens() != null;

  Future<void> writeTokens(AuthTokens tokens) async {
    await _storage.write(key: accessTokenKey, value: tokens.accessToken);
    await _storage.write(key: refreshTokenKey, value: tokens.refreshToken);
  }

  Future<void> clearTokens() async {
    await _storage.delete(key: accessTokenKey);
    await _storage.delete(key: refreshTokenKey);
  }

  Future<String?> readCartToken() => _storage.read(key: cartTokenKey);

  Future<void> writeCartToken(String token) =>
      _storage.write(key: cartTokenKey, value: token);

  Future<void> deleteCartToken() => _storage.delete(key: cartTokenKey);

  Future<String?> readGuestOrderToken(String orderPublicId) =>
      _storage.read(key: '$guestOrderTokenPrefix$orderPublicId');

  Future<void> writeGuestOrderToken(String orderPublicId, String token) =>
      _storage.write(key: '$guestOrderTokenPrefix$orderPublicId', value: token);

  Future<void> deleteGuestOrderToken(String orderPublicId) =>
      _storage.delete(key: '$guestOrderTokenPrefix$orderPublicId');

  /// Removes everything this app stored (sign out of the device).
  Future<void> wipe() => _storage.deleteAll();
}

@Riverpod(keepAlive: true)
SecureStore secureStore(Ref ref) => const SecureStore();
