import 'dart:async';

import 'package:application/core/security/security_channel.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:flutter/widgets.dart';

/// Screen capture protection for sensitive screens (MOBILE_APP_ROADMAP.md 6.3).
///
/// Android: `FLAG_SECURE` through the app's own platform channel (blocks screenshots, screen
/// recording and the recents thumbnail). iOS cannot block screenshots; the capture-detection and
/// app-switcher obscuring for iOS is delivered with the code reveal milestone (M-A14), so on iOS
/// and other platforms [enable] returns false and nothing changes.
class SecureScreenController {
  SecureScreenController({
    this._channel = securityChannel,
    TargetPlatform Function()? platform,
  }) : _platform = platform ?? (() => defaultTargetPlatform);

  final MethodChannel _channel;
  final TargetPlatform Function() _platform;
  int _holders = 0;

  bool get isSupported => _platform() == TargetPlatform.android;

  /// Turns protection on. Nested screens are reference counted.
  Future<bool> enable() async {
    if (!isSupported) {
      return false;
    }
    _holders++;
    if (_holders == 1) {
      await _channel.invokeMethod<bool>('setSecure', {'enabled': true});
    }
    return true;
  }

  /// Releases one [enable]; protection turns off when the last holder leaves.
  Future<void> disable() async {
    if (!isSupported || _holders == 0) {
      return;
    }
    _holders--;
    if (_holders == 0) {
      await _channel.invokeMethod<bool>('setSecure', {'enabled': false});
    }
  }
}

/// Shared controller so nested secure screens count correctly.
final secureScreenController = SecureScreenController();

/// Protects its subtree from capture while mounted.
class SecureScreen extends StatefulWidget {
  const SecureScreen({required this.child, this.controller, super.key});

  final Widget child;
  final SecureScreenController? controller;

  @override
  State<SecureScreen> createState() => _SecureScreenState();
}

class _SecureScreenState extends State<SecureScreen> {
  late final SecureScreenController _controller =
      widget.controller ?? secureScreenController;

  @override
  void initState() {
    super.initState();
    unawaited(_controller.enable());
  }

  @override
  void dispose() {
    unawaited(_controller.disable());
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => widget.child;
}
