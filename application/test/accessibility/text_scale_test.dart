import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/screens.dart';

/// M-A19: the key screens at 200% text (the largest Android font scale), in English and Arabic,
/// at the 360dp phone width: nothing overflows (a RenderFlex overflow fails the test) and no text
/// is cut vertically.
void main() {
  for (final lang in ['en', 'ar']) {
    group('200% text ($lang)', () {
      for (final screen in keyScreens) {
        testWidgets('$screen has no overflow or cut text', (tester) async {
          tester.platformDispatcher.textScaleFactorTestValue = 2;
          addTearDown(tester.platformDispatcher.clearTextScaleFactorTestValue);
          await openScreen(tester, screen, lang, size: const Size(360, 800));

          expect(tester.takeException(), isNull);
          // The scale really applies (only the app bar title is capped).
          final context = tester.element(find.byType(Scaffold).last);
          expect(MediaQuery.textScalerOf(context).scale(10), 20);

          // No text is cut: every paragraph got the height its lines need at its width
          // (one-line labels ellipsize horizontally, which their maxLines allows).
          for (final paragraph
              in tester.allRenderObjects.whereType<RenderParagraph>()) {
            if (!paragraph.hasSize || paragraph.size.isEmpty) {
              continue;
            }
            final needed = paragraph.getMaxIntrinsicHeight(
              paragraph.size.width,
            );
            expect(
              paragraph.size.height,
              greaterThanOrEqualTo(needed - 0.5),
              reason: '"${paragraph.text.toPlainText()}" is cut',
            );
          }
        });
      }
    });
  }

  testWidgets('the app bar title stops growing at 180% and stays one line', (
    tester,
  ) async {
    tester.platformDispatcher.textScaleFactorTestValue = 2;
    addTearDown(tester.platformDispatcher.clearTextScaleFactorTestValue);
    await openScreen(
      tester,
      keyScreens.firstWhere((s) => s.name == 'wallet'),
      'en',
    );
    final title = find.descendant(
      of: find.byType(MobileAppBar),
      matching: find.byType(Text),
    );
    final context = tester.element(title.first);
    expect(
      MediaQuery.textScalerOf(context).scale(10),
      closeTo(10 * MobileAppBar.titleMaxTextScale, 0.001),
    );
    expect(tester.getSize(title.first).height, lessThanOrEqualTo(48));
  });
}
