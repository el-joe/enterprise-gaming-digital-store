import 'package:application/core/network/result.dart';
import 'package:application/core/router/routes.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../test/helpers/engagement_fakes.dart';
import '../../test/helpers/pump_app.dart';
import '../../test/helpers/wallet_fakes.dart';
import '../helpers/journey.dart';

/// Home bell with the unread count -> inbox -> a ticket notification opens its thread (marked
/// read) -> back -> an order notification opens the order.
void notificationsJourney(String lang) {
  final l10n = l10nFor(lang);

  testWidgets('notifications: open the targets ($lang)', (tester) async {
    final inbox = fixtureInbox();
    final h = await launchApp(
      tester,
      lang: lang,
      signedIn: true,
      config: [Ok(dollarConfig())],
      arrange: (h) {
        h.notifications.unreadResult = const Ok(2);
        h.notifications.pages = {1: Ok(inboxPageOf(inbox))};
      },
    );

    expect(
      find.bySemanticsLabel(l10n.notificationBellUnread(2)),
      findsOneWidget,
    );
    await tester.tap(byKey('home-notifications'));
    await tester.pumpAndSettle();
    expect(currentPath(h), Routes.accountNotifications.path);
    expect(find.text(l10n.notificationsUnreadCount(2)), findsOneWidget);

    final ticketNotice = inbox.first;
    await tapVisible(tester, byKey('notification-${ticketNotice.id}'));
    expect(h.notifications.markedRead, [ticketNotice.id]);
    expect(currentPath(h), Routes.ticketLocation(ticketNotice.link!.value));

    await tester.tap(find.byTooltip(l10n.actionBack).first);
    await tester.pumpAndSettle();
    expect(currentPath(h), Routes.accountNotifications.path);

    final orderNotice = inbox.last;
    await tapVisible(tester, byKey('notification-${orderNotice.id}'));
    expect(currentPath(h), Routes.orderLocation(orderNotice.link!.value));
  });
}
