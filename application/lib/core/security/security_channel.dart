import 'package:flutter/services.dart';

/// Method channel implemented by `android/app/src/main/kotlin/shop/boxcards/app/SecurityChannel.kt`
/// and `ios/Runner/AppDelegate.swift` (`SecurityChannel`).
const securityChannel = MethodChannel('shop.boxcards.app/security');

/// iOS screen capture events (`UIScreen.capturedDidChangeNotification`): `true` while the screen
/// is recorded or mirrored. Implemented in `ios/Runner/AppDelegate.swift`; Android has none
/// (`FLAG_SECURE` already blanks recordings).
const securityCaptureChannel = EventChannel(
  'shop.boxcards.app/security/capture',
);
