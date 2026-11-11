import 'package:application/features/push/domain/entities/push.dart';

/// The device's push service (Firebase Cloud Messaging, owner decision 9), behind an interface so
/// the app runs and is tested without Firebase.
abstract interface class PushMessaging {
  /// Starts the service. False when push is not available in this build (no
  /// `google-services.json` / `GoogleService-Info.plist` for the flavor) or on this device; every
  /// other call then does nothing.
  Future<bool> start();

  /// The current system permission, without asking.
  Future<PushPermission> permission();

  /// Shows the system prompt (Android 13+, iOS) when the system still allows it.
  Future<PushPermission> requestPermission();

  /// This installation's FCM registration token, or null.
  Future<String?> token();

  /// A new token after FCM rotated it.
  Stream<String> get tokenRefreshes;

  /// Messages that arrive while the app is in the foreground (the system shows nothing then).
  Stream<PushPayload> get foregroundMessages;

  /// A notification the user tapped while the app was in the background.
  Stream<PushPayload> get openedMessages;

  /// The notification whose tap launched the app from terminated, once.
  Future<PushPayload?> initialMessage();

  /// Forgets this installation's token (sign-out), so the next account gets a fresh one.
  Future<void> deleteToken();
}
