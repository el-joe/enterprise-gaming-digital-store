import 'package:application/core/security/clipboard_guard.dart';
import 'package:application/core/security/secure_screen.dart';
import 'package:application/core/security/security_channel.dart';
import 'package:flutter/services.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  final calls = <MethodCall>[];

  setUp(() {
    calls.clear();
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(securityChannel, (call) async {
          calls.add(call);
          return true;
        });
  });

  tearDown(() {
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(securityChannel, null);
  });

  group('SecureScreen', () {
    testWidgets(
      'sets FLAG_SECURE while mounted on Android, reference counted',
      (tester) async {
        final controller = SecureScreenController(
          platform: () => TargetPlatform.android,
        );
        await tester.pumpWidget(
          SecureScreen(
            controller: controller,
            child: SecureScreen(
              controller: controller,
              child: const SizedBox(),
            ),
          ),
        );
        expect(calls.map((c) => c.arguments), [
          {'enabled': true},
        ]);

        await tester.pumpWidget(const SizedBox());
        expect(calls.map((c) => c.method), ['setSecure', 'setSecure']);
        expect(calls.last.arguments, {'enabled': false});
      },
    );

    test('is a no-op on iOS (handled in M-A14)', () async {
      final controller = SecureScreenController(
        platform: () => TargetPlatform.iOS,
      );
      expect(await controller.enable(), isFalse);
      await controller.disable();
      expect(calls, isEmpty);
    });
  });

  group('ClipboardGuard', () {
    testWidgets('Android copies a sensitive clip and clears it after 60 s', (
      tester,
    ) async {
      final guard = ClipboardGuard(platform: () => TargetPlatform.android);
      await guard.copy('CODE-1234');
      expect(calls.single.method, 'copySensitive');
      expect(calls.single.arguments, {'text': 'CODE-1234'});
      expect(guard.hasPendingClear, isTrue);

      await tester.pump(const Duration(seconds: 59));
      expect(calls, hasLength(1));
      await tester.pump(const Duration(seconds: 1));
      expect(calls.last.method, 'clearSensitive');
      expect(guard.hasPendingClear, isFalse);
    });

    testWidgets(
      'other platforms clear only if the clipboard still holds the copy',
      (tester) async {
        String? clipboard;
        TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
            .setMockMethodCallHandler(SystemChannels.platform, (call) async {
              if (call.method == 'Clipboard.setData') {
                clipboard =
                    (call.arguments as Map<Object?, Object?>)['text']
                        as String?;
                return null;
              }
              if (call.method == 'Clipboard.getData') {
                return {'text': clipboard};
              }
              return null;
            });
        addTearDown(
          () => TestDefaultBinaryMessengerBinding
              .instance
              .defaultBinaryMessenger
              .setMockMethodCallHandler(SystemChannels.platform, null),
        );

        final guard = ClipboardGuard(platform: () => TargetPlatform.iOS);
        await guard.copy('PIN-1');
        expect(clipboard, 'PIN-1');
        await tester.pump(const Duration(seconds: 60));
        await tester.pump();
        expect(clipboard, '');

        await guard.copy('PIN-2');
        clipboard = 'something the user copied';
        await guard.dispose();
        expect(clipboard, 'something the user copied');
        expect(calls, isEmpty);
      },
    );
  });
}
