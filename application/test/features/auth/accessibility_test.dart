import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/pump_app.dart';

/// 48dp touch targets and labelled controls on the M-A10 screens (MOBILE_APP_ROADMAP.md M-A8
/// accessibility notes), in both directions.
void main() {
  const screens = [
    Routes.login,
    Routes.register,
    Routes.forgotPassword,
    Routes.onboardingLanguage,
    Routes.onboardingCurrency,
  ];

  for (final lang in ['en', 'ar']) {
    for (final route in screens) {
      testWidgets(
        '${route.name} meets tap-target and label guidelines ($lang)',
        (tester) async {
          final handle = tester.ensureSemantics();
          final h = await pumpHarness(tester, languageCode: lang);
          h.read(appRouterProvider).go(route.path);
          await tester.pumpAndSettle();
          await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
          await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
          handle.dispose();
        },
      );
    }
  }
}
