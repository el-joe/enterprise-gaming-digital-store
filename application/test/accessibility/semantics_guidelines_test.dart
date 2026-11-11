import 'package:flutter_test/flutter_test.dart';

import '../helpers/screens.dart';

/// M-A19 semantics sweep: every main page of M-A10 to M-A17, in English (LTR) and Arabic (RTL),
/// meets the Android 48dp tap target, labelled tap target and text contrast guidelines.
void main() {
  for (final lang in ['en', 'ar']) {
    group('guidelines ($lang)', () {
      for (final screen in mainScreens) {
        testWidgets('$screen: tap targets, labels and text contrast', (
          tester,
        ) async {
          final handle = tester.ensureSemantics();
          await openScreen(tester, screen, lang);
          await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
          await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
          await expectLater(tester, meetsGuideline(textContrastGuideline));
          handle.dispose();
        });
      }
    });
  }
}
