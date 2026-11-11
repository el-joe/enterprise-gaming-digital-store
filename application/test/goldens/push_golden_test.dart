import 'dart:async';

import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/features/payments/payment_flow.dart';
import 'package:application/features/push/domain/entities/push.dart';
import 'package:application/features/push/presentation/widgets/push_permission_sheet.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/checkout_fakes.dart';
import '../helpers/pump_app.dart';

/// M-A18: the push explainer sheet over the paid result, in LTR (en) and RTL (ar). No Figma frame
/// exists for it (MOBILE_APP_ROADMAP.md: push has no screens beyond the settings note); it is built
/// from the integrity warning sheet's tokens. PNGs are written by the final run with
/// `--update-goldens` (test/goldens/m_a18/).
void main() {
  setUpAll(loadAppFonts);

  for (final lang in ['en', 'ar']) {
    final dir = lang == 'ar' ? 'rtl' : 'ltr';

    testWidgets('push explainer after the first paid order ($dir)', (
      tester,
    ) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: true,
        arrange: (h) => h.push
          ..available = true
          ..permissionValue = PushPermission.notDetermined,
      );
      h.payments.verifyResults = [Ok(paidStatus)];
      unawaited(
        h
            .read(paymentFlowProvider.notifier)
            .handleReturn(orderId, cancelled: false),
      );
      h.read(appRouterProvider).go(Routes.paymentLocation(orderId));
      await tester.pumpAndSettle();
      expect(find.byType(PushPermissionSheet), findsOneWidget);

      await precacheImages(tester);
      await expectLater(
        find.byType(MaterialApp),
        matchesGoldenFile('m_a18/push_explainer_$dir.png'),
      );
    });

    testWidgets('push explainer at 200% text ($dir)', (tester) async {
      tester.platformDispatcher.textScaleFactorTestValue = 2;
      addTearDown(tester.platformDispatcher.clearTextScaleFactorTestValue);
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: true,
        arrange: (h) => h.push
          ..available = true
          ..permissionValue = PushPermission.notDetermined,
      );
      h.payments.verifyResults = [Ok(paidStatus)];
      unawaited(
        h
            .read(paymentFlowProvider.notifier)
            .handleReturn(orderId, cancelled: false),
      );
      h.read(appRouterProvider).go(Routes.paymentLocation(orderId));
      await tester.pumpAndSettle();

      expect(tester.takeException(), isNull, reason: 'no overflow at 200%');
      await expectLater(
        find.byType(MaterialApp),
        matchesGoldenFile('m_a18/push_explainer_text200_$dir.png'),
      );
    });
  }
}
