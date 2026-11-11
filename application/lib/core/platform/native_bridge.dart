import 'package:application/core/security/security_channel.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'native_bridge.g.dart';

/// Small native services behind the app's own channel (`SecurityChannel.kt`, `AppDelegate.swift`)
/// instead of extra plugins: the device integrity signal, the device name for sign-in, and opening
/// an https page or the store listing in the system apps.
class NativeBridge {
  NativeBridge({this._channel = securityChannel});

  final MethodChannel _channel;

  /// `ok`, `compromised` or `unknown` (any failure). Owner decision 5: a signal, never a block.
  Future<String> deviceIntegrity() async {
    try {
      final value = await _channel.invokeMethod<String>('deviceIntegrity');
      return value == 'ok' || value == 'compromised' ? value! : 'unknown';
    } on Object {
      return 'unknown';
    }
  }

  /// A readable device name for the sign-in (`device_name`, 1 to 100 printable characters).
  Future<String> deviceName() async {
    String? name;
    try {
      name = await _channel.invokeMethod<String>('deviceName');
    } on Object {
      name = null;
    }
    final cleaned = (name ?? '').replaceAll(RegExp(r'[^\x20-\x7E]'), '').trim();
    if (cleaned.isNotEmpty) {
      return cleaned.length > 100 ? cleaned.substring(0, 100) : cleaned;
    }
    return defaultTargetPlatform == TargetPlatform.iOS ? 'iPhone' : 'Android';
  }

  /// Opens an https [url] outside the app. Returns false when nothing could open it.
  Future<bool> openExternalUrl(Uri url) async {
    if (url.scheme != 'https') {
      return false;
    }
    try {
      return await _channel.invokeMethod<bool>('openExternalUrl', {
            'url': url.toString(),
          }) ??
          false;
    } on Object {
      return false;
    }
  }

  /// Opens this app's store listing (force update). False where no listing is known (iOS until the
  /// App Store id exists).
  Future<bool> openStoreListing() async {
    try {
      return await _channel.invokeMethod<bool>('openStoreListing') ?? false;
    } on Object {
      return false;
    }
  }
}

@Riverpod(keepAlive: true)
NativeBridge nativeBridge(Ref ref) => NativeBridge();

/// `device_name` sent on login and register.
@Riverpod(keepAlive: true)
Future<String> deviceName(Ref ref) =>
    ref.watch(nativeBridgeProvider).deviceName();
