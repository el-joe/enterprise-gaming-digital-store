import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/security/device_integrity.dart';
import 'package:application/core/security/security_channel.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  final calls = <MethodCall>[];

  void answer(Object? Function(MethodCall call) handler) {
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(securityChannel, (call) async {
          calls.add(call);
          return handler(call);
        });
  }

  setUp(calls.clear);
  tearDown(
    () => TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(securityChannel, null),
  );

  test('device integrity: ok, compromised, anything else unknown', () async {
    final bridge = NativeBridge();
    answer((_) => 'compromised');
    expect(await bridge.deviceIntegrity(), 'compromised');
    answer((_) => 'weird');
    expect(await bridge.deviceIntegrity(), 'unknown');
    answer((_) => throw PlatformException(code: 'x'));
    expect(await bridge.deviceIntegrity(), 'unknown');
    expect(DeviceIntegrity.parse('compromised').headerValue, 'compromised');
    expect(DeviceIntegrity.parse('nope'), DeviceIntegrity.unknown);
  });

  test(
    'device name is printable, at most 100 characters, with a fallback',
    () async {
      final bridge = NativeBridge();
      answer((_) => '  Google Pixel 9\u0000 ');
      expect(await bridge.deviceName(), 'Google Pixel 9');
      answer((_) => 'x' * 150);
      expect(await bridge.deviceName(), hasLength(100));
      answer((_) => null);
      expect(await bridge.deviceName(), isNotEmpty);
    },
  );

  test('opens https pages only', () async {
    final bridge = NativeBridge();
    answer((_) => true);
    expect(
      await bridge.openExternalUrl(
        Uri.parse('https://boxcards.test/forgot-password'),
      ),
      isTrue,
    );
    expect(calls.single.method, 'openExternalUrl');
    expect(
      (calls.single.arguments as Map<Object?, Object?>)['url'],
      'https://boxcards.test/forgot-password',
    );
    expect(
      await bridge.openExternalUrl(Uri.parse('http://evil.test')),
      isFalse,
    );
    expect(
      await bridge.openExternalUrl(Uri.parse('javascript:alert(1)')),
      isFalse,
    );
    expect(calls, hasLength(1));
    expect(await bridge.openStoreListing(), isTrue);
  });
}
