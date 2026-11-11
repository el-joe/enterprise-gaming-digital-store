import 'package:application/core/network/result.dart';
import 'package:application/core/router/routes.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../test/helpers/pump_app.dart';
import '../../test/helpers/wallet_fakes.dart';
import '../helpers/journey.dart';

/// Account -> Support -> new request (topic, subject, message) -> the thread -> reply.
void supportJourney(String lang) {
  final l10n = l10nFor(lang);

  testWidgets('support: create a request, then reply ($lang)', (tester) async {
    final h = await launchApp(
      tester,
      lang: lang,
      signedIn: true,
      config: [Ok(dollarConfig())],
    );

    await openTab(tester, l10n.navAccount);
    await tapVisible(tester, byKey('account-support'));
    expect(currentPath(h), Routes.accountSupport.path);

    await tapVisible(tester, byKey('ticket-new'));
    expect(currentPath(h), Routes.newTicket.path);

    await tapVisible(tester, byKey('ticket-category-account'));
    await tester.enterText(
      find.descendant(
        of: byKey('ticket-subject'),
        matching: find.byType(TextField),
      ),
      'Change my email',
    );
    await tester.enterText(
      find.descendant(
        of: byKey('ticket-body'),
        matching: find.byType(TextField),
      ),
      'I want to use my new address.',
    );
    await tapVisible(tester, byKey('ticket-submit'));
    expect(h.support.opened.single, {
      'category': 'account',
      'subject': 'Change my email',
      'body': 'I want to use my new address.',
      'order_public_id': null,
    });
    expect(currentPath(h), Routes.ticketLocation('01JF00000000000000000000T9'));

    // Reply in the thread: it is sent and shows at the end.
    await tester.enterText(byKey('ticket-reply'), 'Any news?');
    await tester.tap(byKey('ticket-send'));
    await tester.pumpAndSettle();
    expect(h.support.replies.single.body, 'Any news?');
    await scrollTo(tester, find.text('Any news?'));
    expect(find.text('Any news?'), findsOneWidget);
  });
}
