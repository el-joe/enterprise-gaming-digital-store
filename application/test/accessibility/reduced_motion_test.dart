import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/app_theme.dart';
import 'package:application/core/theme/tokens/motion.dart';
import 'package:application/features/payments/payment_flow.dart';
import 'package:application/shared/widgets/busy_indicator.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/checkout_fakes.dart';
import '../helpers/pump_app.dart';
import '../helpers/reveal_fakes.dart';
import '../helpers/screens.dart';

/// M-A19 reduced motion: Android "Remove animations" (`disableAnimations`) and iOS "Reduce
/// Motion" (`reduceMotion`, reported separately by Flutter) both stop the app's own motion.
void main() {
  const features = {
    'Android remove animations': FakeAccessibilityFeatures(
      disableAnimations: true,
    ),
    'iOS reduce motion': FakeAccessibilityFeatures(reduceMotion: true),
  };

  for (final entry in features.entries) {
    group(entry.key, () {
      void reduce(WidgetTester tester) {
        tester.platformDispatcher.accessibilityFeaturesTestValue = entry.value;
        addTearDown(
          tester.platformDispatcher.clearAccessibilityFeaturesTestValue,
        );
      }

      testWidgets('motion tokens resolve to zero', (tester) async {
        reduce(tester);
        late bool reduced;
        late Duration duration;
        await tester.pumpWidget(
          MediaQuery.fromView(
            view: tester.view,
            child: Builder(
              builder: (context) {
                reduced = MotionTokens.reduced(context);
                duration = MotionTokens.resolve(context, MotionTokens.slow);
                return const SizedBox();
              },
            ),
          ),
        );
        expect(reduced, isTrue);
        expect(duration, Duration.zero);
      });

      testWidgets('a pushed page cross-fades in place, no slide or zoom', (
        tester,
      ) async {
        reduce(tester);
        final h = await pumpHarness(tester, signedIn: true);
        h.read(appRouterProvider).go(Routes.account.path);
        await tester.pumpAndSettle();
        h.read(appRouterProvider).push<void>(Routes.wallet.path).ignore();
        await tester.pump();
        await tester.pump(const Duration(milliseconds: 50));
        // Mid-transition the incoming page sits at its final place, fading in.
        final scaffold = find.byType(Scaffold).last;
        expect(tester.getTopLeft(scaffold), Offset.zero);
        expect(
          find.ancestor(of: scaffold, matching: find.byType(FadeTransition)),
          findsWidgets,
        );
        await tester.pumpAndSettle();
      });

      testWidgets('busy indicators are still', (tester) async {
        reduce(tester);
        await tester.pumpWidget(
          MaterialApp(
            theme: AppTheme.dark(arabic: false),
            locale: const Locale('en'),
            localizationsDelegates: AppLocalizations.localizationsDelegates,
            supportedLocales: AppLocales.supported,
            home: const Scaffold(body: Center(child: BusyIndicator())),
          ),
        );
        expect(find.byType(CircularProgressIndicator), findsNothing);
        expect(find.byIcon(Icons.hourglass_empty), findsOneWidget);
        // Nothing keeps scheduling frames.
        await tester.pumpAndSettle();
      });

      testWidgets('a payment still being confirmed shows no spinner', (
        tester,
      ) async {
        reduce(tester);
        final gate = Completer<void>();
        final h = await openScreen(
          tester,
          ScreenCase(
            'payment confirming',
            mainScreens.firstWhere((s) => s.name == 'payment result').location,
            before: (h) {
              h.payments.verifyGate = gate;
              unawaited(h.read(paymentFlowProvider.notifier).resume(orderId));
            },
          ),
          'en',
        );
        // Settling at all proves nothing spins; the status icon has no progress ring.
        expect(find.byType(CircularProgressIndicator), findsNothing);
        expect(h.payments.count('verify'), 1);
        gate.complete();
        await tester.pumpAndSettle();
      });
    });
  }

  testWidgets('without reduced motion the spinner runs', (tester) async {
    await tester.pumpWidget(
      MaterialApp(
        theme: AppTheme.dark(arabic: false),
        localizationsDelegates: AppLocalizations.localizationsDelegates,
        supportedLocales: AppLocales.supported,
        home: const Scaffold(body: Center(child: BusyIndicator())),
      ),
    );
    expect(find.byType(CircularProgressIndicator), findsOneWidget);
  });

  testWidgets('revealing announces "Code shown" once; hiding announces it', (
    tester,
  ) async {
    final l10n = l10nFor('en');
    await openScreen(
      tester,
      mainScreens.firstWhere((s) => s.name == 'code reveal'),
      'en',
    );
    tester.takeAnnouncements();
    await tester.tap(find.text(l10n.revealButton));
    await tester.pumpAndSettle();
    expect(find.text(canaryCode), findsOneWidget);
    expect(tester.takeAnnouncements(), [
      isAccessibilityAnnouncement(l10n.revealShownSemantics),
    ]);
    await tester.tap(find.text(l10n.revealHide));
    await tester.pumpAndSettle();
    expect(tester.takeAnnouncements(), [
      isAccessibilityAnnouncement(l10n.revealHidden),
    ]);
  });
}
