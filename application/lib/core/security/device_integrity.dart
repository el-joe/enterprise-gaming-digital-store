import 'package:application/core/platform/native_bridge.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'device_integrity.g.dart';

/// Root or jailbreak signal (owner decision 5: warn once, never block). Sent with reveal requests
/// as `X-Device-Integrity` from M-A14.
enum DeviceIntegrity {
  ok,
  compromised,
  unknown;

  /// The `X-Device-Integrity` header value (API_DOCUMENTATION.md 1g).
  String get headerValue => name;

  static DeviceIntegrity parse(String value) => switch (value) {
    'ok' => ok,
    'compromised' => compromised,
    _ => unknown,
  };
}

/// Read once per launch from the native check.
@Riverpod(keepAlive: true)
Future<DeviceIntegrity> deviceIntegrity(Ref ref) async => DeviceIntegrity.parse(
  await ref.watch(nativeBridgeProvider).deviceIntegrity(),
);
