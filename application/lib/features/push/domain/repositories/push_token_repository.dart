import 'package:application/core/network/result.dart';

/// `POST` and `DELETE /api/v1/mobile/push-token` (API_DOCUMENTATION.md 1h).
abstract interface class PushTokenRepository {
  /// Registers or replaces this device's token. [platform] is `android` or `ios`.
  Future<Result<void>> register({
    required String token,
    required String platform,
  });

  /// Removes this device's token (idempotent).
  Future<Result<void>> unregister();
}
