import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../test/helpers/checkout_fakes.dart';
import '../../test/helpers/pump_app.dart';
import '../../test/helpers/reveal_fakes.dart';
import '../helpers/journey.dart';

/// Orders -> order detail -> code screen -> reveal -> confirm-password sheet (recent auth) ->
/// code shown -> hides itself after 60 seconds.
void revealJourney(String lang) {
  final l10n = l10nFor(lang);

  testWidgets('orders, reveal with re-auth, auto-hide ($lang)', (tester) async {
    final h = await launchApp(
      tester,
      lang: lang,
      signedIn: true,
      preferences: const {PreferencesStore.integrityWarningShownKey: true},
      arrange: (h) => h.reveal.reveals = [
        const Err(RecentAuthRequiredFailure()),
        Ok(canaryReveal()),
      ],
    );

    await openTab(tester, l10n.navOrders);
    await tester.tap(find.text('BX-1004'));
    await tester.pumpAndSettle();
    expect(currentPath(h), Routes.orderLocation(orderId));

    // The unit shows only its mask; it opens the protected code screen.
    await tapVisible(tester, find.text('****0000'));
    expect(currentPath(h), Routes.codeRevealLocation(orderId, fixtureUnitId));
    expect(find.text(canaryCode), findsNothing);
    expect(h.security.secureCalls, [true]);

    // Reveal: the server wants a recent sign-in, the sheet asks for the password, then the
    // reveal is retried once and the code shows.
    await tester.tap(find.text(l10n.revealButton));
    await tester.pumpAndSettle();
    expect(find.text(l10n.reauthTitle), findsOneWidget);
    await tester.enterText(find.byType(EditableText).last, 'right-pass');
    await tester.tap(find.text(l10n.reauthConfirm));
    await tester.pumpAndSettle();
    expect(find.text(l10n.reauthTitle), findsNothing);
    expect(find.text(canaryCode), findsOneWidget);
    expect(h.reveal.calls, ['reveal', 'confirm:right-pass', 'reveal']);

    // Auto-hide: the screen's clock moves 60 s on; the next countdown tick (real time on the
    // device) hides the code.
    h.now.value = h.now.value.add(const Duration(seconds: 60));
    await tester.pump(const Duration(seconds: 2));
    await tester.pumpAndSettle();
    expect(find.text(canaryCode), findsNothing);
    expect(find.text(l10n.revealButton), findsOneWidget);

    // Leaving the screen drops the screen protection.
    await tester.tap(find.byTooltip(l10n.actionBack).first);
    await tester.pumpAndSettle();
    expect(h.security.secureCalls.last, isFalse);
  });
}
