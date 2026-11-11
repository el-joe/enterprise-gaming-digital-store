import 'package:application/core/security/clipboard_guard.dart';
import 'package:application/core/security/secure_screen.dart';
import 'package:application/core/security/security_channel.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  final calls = <MethodCall>[];
  final messenger =
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger;

  setUp(() {
    calls.clear();
    messenger.setMockMethodCallHandler(securityChannel, (call) async {
      calls.add(call);
      return true;
    });
  });

  tearDown(() {
    messenger.setMockMethodCallHandler(securityChannel, null);
  });

  group('ScreenSecurity', () {
    test(
      'Android: setSecure on for the first holder, off after the last',
      () async {
        final security = ScreenSecurity(platform: () => TargetPlatform.android);
        await security.enable();
        await security.enable();
        expect(calls.map((c) => c.arguments), [
          {'enabled': true},
        ]);
        await security.disable();
        expect(calls, hasLength(1));
        await security.disable();
        expect(calls.map((c) => c.method), ['setSecure', 'setSecure']);
        expect(calls.last.arguments, {'enabled': false});
        expect(security.isOn, isFalse);
        // Capture detection is iOS only.
        expect(await security.isCaptured(), isFalse);
        expect(await security.captureChanges.isEmpty, isTrue);
      },
    );

    test(
      'iOS: setSecure drives the privacy overlay; capture is read and streamed',
      () async {
        final security = ScreenSecurity(platform: () => TargetPlatform.iOS);
        await security.enable();
        expect(calls.single.method, 'setSecure');
        expect(await security.isCaptured(), isTrue);

        messenger.setMockStreamHandler(
          securityCaptureChannel,
          MockStreamHandler.inline(
            onListen: (_, sink) {
              sink
                ..success(true)
                ..success(false);
            },
          ),
        );
        addTearDown(
          () => messenger.setMockStreamHandler(securityCaptureChannel, null),
        );
        expect(await security.captureChanges.take(2).toList(), [true, false]);
        await security.disable();
        expect(calls.last.arguments, {'enabled': false});
      },
    );

    test('other platforms do nothing', () async {
      final security = ScreenSecurity(platform: () => TargetPlatform.linux);
      await security.enable();
      await security.disable();
      expect(calls, isEmpty);
    });

    test('a missing native side never throws', () async {
      messenger.setMockMethodCallHandler(securityChannel, null);
      final security = ScreenSecurity(platform: () => TargetPlatform.android);
      await security.enable();
      await security.disable();
    });
  });

  group('ClipboardGuard', () {
    testWidgets('Android copies a sensitive clip and clears it after 60 s', (
      tester,
    ) async {
      final guard = ClipboardGuard(platform: () => TargetPlatform.android);
      await guard.copy('CANARY-1234');
      expect(calls.single.method, 'copySensitive');
      expect(calls.single.arguments, {'text': 'CANARY-1234'});
      expect(guard.hasPendingClear, isTrue);

      await tester.pump(const Duration(seconds: 59));
      expect(calls, hasLength(1));
      await tester.pump(const Duration(seconds: 1));
      expect(calls.last.method, 'clearSensitive');
      expect(guard.hasPendingClear, isFalse);
    });

    testWidgets(
      'iOS writes an empty string after 60 s without reading the clipboard',
      (tester) async {
        {
          final platformCalls = <String>[];
          String? clipboard;
          messenger.setMockMethodCallHandler(SystemChannels.platform, (
            call,
          ) async {
            platformCalls.add(call.method);
            if (call.method == 'Clipboard.setData') {
              clipboard =
                  (call.arguments as Map<Object?, Object?>)['text'] as String?;
            }
            return null;
          });
          addTearDown(
            () => messenger.setMockMethodCallHandler(
              SystemChannels.platform,
              null,
            ),
          );

          final guard = ClipboardGuard(platform: () => TargetPlatform.iOS);
          await guard.copy('CANARY-PIN');
          expect(clipboard, 'CANARY-PIN');
          await tester.pump(const Duration(seconds: 60));
          await tester.pump();
          expect(clipboard, '');
          expect(platformCalls, isNot(contains('Clipboard.getData')));
          expect(calls, isEmpty);
        }
      },
    );

    test('a clear that fell due in the background runs on resume', () async {
      var now = DateTime.utc(2026, 11, 11, 12);
      final guard = ClipboardGuard(
        platform: () => TargetPlatform.android,
        clock: () => now,
      );
      await guard.copy('CANARY-1');
      await guard.resumed();
      expect(calls.map((c) => c.method), ['copySensitive']);

      now = now.add(const Duration(seconds: 61));
      await guard.resumed();
      expect(calls.last.method, 'clearSensitive');
      expect(guard.hasPendingClear, isFalse);
      guard.dispose();
    });
  });
}
