import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/app_router.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/pump_app.dart';

void main() {
  setUpAll(loadAppFonts);

  for (final languageCode in ['en', 'ar']) {
    final direction = languageCode == 'ar' ? 'rtl' : 'ltr';

    testWidgets('token shell home ($direction)', (tester) async {
      await pumpApp(tester, languageCode: languageCode);
      await precacheImages(tester);
      expect(
        Directionality.of(tester.element(find.byType(Scaffold).first)),
        languageCode == 'ar' ? TextDirection.rtl : TextDirection.ltr,
      );
      await expectLater(
        find.byType(MaterialApp),
        matchesGoldenFile('shell_home_$direction.png'),
      );
    });

    testWidgets('token shell notice ($direction)', (tester) async {
      final container = await pumpApp(tester, languageCode: languageCode);
      container.read(appRouterProvider).go('/missing');
      await tester.pumpAndSettle();
      await precacheImages(tester);
      expect(
        find.text(lookupAppLocalizations(Locale(languageCode)).notFoundTitle),
        findsOneWidget,
      );
      await expectLater(
        find.byType(MaterialApp),
        matchesGoldenFile('shell_not_found_$direction.png'),
      );
    });
  }
}
