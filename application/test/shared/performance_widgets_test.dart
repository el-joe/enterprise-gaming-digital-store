import 'package:application/core/theme/app_theme.dart';
import 'package:application/shared/widgets/fit_amount.dart';
import 'package:application/shared/widgets/network_image_box.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

/// M-A19 performance and text-scaling helpers.
void main() {
  group('NetworkImageBox decode width', () {
    test('rounds up to 64 px steps so similar slots share a decode', () {
      expect(NetworkImageBox.decodeWidthFor(1), 64);
      expect(NetworkImageBox.decodeWidthFor(500), 512);
      expect(NetworkImageBox.decodeWidthFor(512), 512);
      expect(NetworkImageBox.decodeWidthFor(513), 576);
      // Two grid tiles a few pixels apart decode once.
      expect(
        NetworkImageBox.decodeWidthFor(489.6),
        NetworkImageBox.decodeWidthFor(495),
      );
    });

    test('never decodes wider than a full-width 3x phone image', () {
      expect(NetworkImageBox.decodeWidthFor(4000), 1536);
    });
  });

  group('FitAmount', () {
    Future<void> pumpRow(WidgetTester tester, double scale) async {
      tester.platformDispatcher.textScaleFactorTestValue = scale;
      addTearDown(tester.platformDispatcher.clearTextScaleFactorTestValue);
      await tester.pumpWidget(
        MaterialApp(
          theme: AppTheme.dark(arabic: false),
          home: const Scaffold(
            body: SizedBox(
              width: 300,
              child: Row(
                children: [
                  Expanded(child: Text('Total')),
                  FitAmount(
                    child: Text(
                      'USD 12.00',
                      key: ValueKey('amount'),
                      textDirection: TextDirection.ltr,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      );
    }

    testWidgets('keeps its natural size at the default text size', (
      tester,
    ) async {
      await pumpRow(tester, 1);
      final text = tester.getSize(find.byKey(const ValueKey('amount')));
      final box = tester.getSize(find.byType(FitAmount));
      expect(box.width, text.width);
    });

    testWidgets('scales down at 200% instead of overflowing', (tester) async {
      await pumpRow(tester, 2);
      expect(tester.takeException(), isNull);
      expect(
        tester.getSize(find.byType(FitAmount)).width,
        lessThanOrEqualTo(200),
      );
      expect(find.text('USD 12.00'), findsOneWidget);
    });
  });
}
