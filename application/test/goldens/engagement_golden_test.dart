import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/checkout_fakes.dart';
import '../helpers/engagement_fakes.dart';
import '../helpers/pump_app.dart';
import '../helpers/wallet_fakes.dart';

/// Key M-A17 states in LTR (en) and RTL (ar), compared with the approved M-A8 frames 13.1 to
/// 16.3 by review. Times are local and the clock is fixed, so the dates do not move.
void main() {
  setUpAll(loadAppFonts);

  for (final lang in ['en', 'ar']) {
    final dir = lang == 'ar' ? 'rtl' : 'ltr';

    Future<void> snap(WidgetTester tester, String name) async {
      await precacheImages(tester);
      await expectLater(
        find.byType(MaterialApp),
        matchesGoldenFile('m_a17/${name}_$dir.png'),
      );
    }

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      void Function(TestHarness h)? arrange,
      Size size = const Size(390, 844),
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: true,
        size: size,
        config: [Ok(dollarConfig())],
        arrange: (h) {
          h.now.value = DateTime(2026, 11, 11, 12);
          arrange?.call(h);
        },
      );
      h.read(appRouterProvider).go(location);
      await tester.pumpAndSettle();
      return h;
    }

    final drawnTickets = [
      ticket(at: DateTime(2026, 11, 10, 14, 45)),
      ticket(
        id: '01JF00000000000000000000T2',
        number: 'T-261102-0004',
        subject: 'Payment charged twice?',
        status: 'pending_customer',
        category: 'payment',
        order: null,
        at: DateTime(2026, 11, 3, 9),
      ),
      ticket(
        id: '01JF00000000000000000000T3',
        number: 'T-261020-0002',
        subject: 'Change my email',
        status: 'closed',
        category: 'account',
        order: null,
        at: DateTime(2026, 10, 21, 9),
      ),
    ];

    final drawnInbox = [
      inboxItem(
        id: 'n1',
        at: DateTime(2026, 11, 11, 11, 58),
        link: orderLink(orderId),
      ),
      inboxItem(
        id: 'n2',
        type: 'support_ticket_replied',
        category: 'support',
        title: 'Update on your support request T-261110-0001',
        body: 'Our support team has replied to your request.',
        at: DateTime(2026, 11, 11, 11),
        link: ticketLink(ticketId),
      ),
      inboxItem(
        id: 'n3',
        type: 'order_fulfilment_failed',
        title: 'Order BX-0997 could not be delivered',
        body: 'We are refunding it. Open the order for details.',
        at: DateTime(2026, 10, 28, 9),
        readAt: DateTime(2026, 10, 28, 10),
        link: orderLink(orderId2),
      ),
    ];

    group('M-A17 goldens ($dir)', () {
      testWidgets('13.1 my reviews', (tester) async {
        await at(
          tester,
          Routes.myReviews.path,
          arrange: (h) => h.reviews
            ..eligibleResult = Ok([reviewable(product: 'Orbit Card - 10 USD')])
            ..pages = {
              1: Ok(
                pagedOf([
                  myReview(),
                  myReview(
                    id: 2,
                    product: 'Nova Points 1000',
                    rating: 5,
                    status: 'approved',
                    body: 'Clear steps to redeem.',
                  ),
                ]),
              ),
            },
        );
        await snap(tester, 'my_reviews');
      });

      testWidgets('13.2 write a review', (tester) async {
        await at(
          tester,
          Routes.writeReviewLocation(1),
          arrange: (h) => h.reviews.eligibleResult = Ok([
            reviewable(product: 'Orbit Card - 10 USD'),
          ]),
        );
        await tester.tap(find.byKey(const ValueKey('review-star-4')));
        await tester.pumpAndSettle();
        await snap(tester, 'write_review');
      });

      testWidgets('13.3 review email unverified', (tester) async {
        final h = await at(
          tester,
          Routes.writeReviewLocation(1),
          arrange: (h) => h.reviews
            ..eligibleResult = Ok([reviewable()])
            ..writeResult = const Err(EmailUnverifiedFailure()),
        );
        await tester.tap(find.byKey(const ValueKey('review-star-5')));
        await tester.tap(find.byKey(const ValueKey('review-submit')));
        await tester.pumpAndSettle();
        expect(h.reviews.submits, hasLength(1));
        await snap(tester, 'review_unverified');
      });

      testWidgets('13.3 my reviews empty', (tester) async {
        await at(tester, Routes.myReviews.path);
        await snap(tester, 'my_reviews_empty');
      });

      testWidgets('14.1 support tickets', (tester) async {
        await at(
          tester,
          Routes.accountSupport.path,
          arrange: (h) =>
              h.support.ticketPages = {1: Ok(pagedOf(drawnTickets))},
        );
        await snap(tester, 'tickets');
      });

      testWidgets('14.2 new request', (tester) async {
        await at(tester, Routes.newTicketLocation(order: orderId));
        await snap(tester, 'new_ticket');
      });

      testWidgets('14.3 ticket thread', (tester) async {
        await at(
          tester,
          Routes.ticketLocation(ticketId),
          arrange: (h) => h.support.threads[ticketId] = Ok(
            ticket(
              messages: [
                message(
                  1,
                  'Nothing arrived.',
                  at: DateTime(2026, 11, 10, 10, 2),
                ),
                message(
                  2,
                  "Thanks, we're checking with the supplier. We'll reply within a few hours.",
                  from: 'staff',
                  at: DateTime(2026, 11, 10, 10, 20),
                ),
                message(3, 'Any news?', at: DateTime(2026, 11, 10, 14, 45)),
              ],
            ),
          ),
        );
        await snap(tester, 'ticket_thread');
      });

      testWidgets('14.4 ticket closed', (tester) async {
        await at(
          tester,
          Routes.ticketLocation(ticketId),
          arrange: (h) => h.support.threads[ticketId] = Ok(
            ticket(
              status: 'closed',
              messages: [message(1, 'Nothing arrived.')],
            ),
          ),
        );
        await snap(tester, 'ticket_closed');
      });

      testWidgets('14.4 tickets empty', (tester) async {
        await at(tester, Routes.accountSupport.path);
        await snap(tester, 'tickets_empty');
      });

      testWidgets('15.1 refund request', (tester) async {
        await at(
          tester,
          Routes.refundRequestLocation(orderId),
          arrange: (h) => h.support.ticketPages = {
            1: Ok(pagedOf([ticket()])),
          },
        );
        await snap(tester, 'refund_request');
      });

      testWidgets('15.2 refund request open', (tester) async {
        await at(
          tester,
          Routes.refundRequestLocation(orderId),
          arrange: (h) => h.support.refundResult = const Err(
            ConflictFailure('refund_request_open', message: 'Open.'),
          ),
        );
        await tester.enterText(
          find.descendant(
            of: find.byKey(const ValueKey('refund-reason')),
            matching: find.byType(TextField),
          ),
          'The second code shows as already used.',
        );
        await tester.tap(find.byKey(const ValueKey('refund-submit')));
        await tester.pumpAndSettle();
        await snap(tester, 'refund_open');
      });

      testWidgets('15.2 refund requests, sent', (tester) async {
        await at(
          tester,
          Uri(
            path: Routes.refundRequests.path,
            queryParameters: {'sent': '1'},
          ).toString(),
          arrange: (h) => h.support.refundPages = {
            1: Ok(
              pagedOf([
                refundRequest(at: DateTime(2026, 11, 10, 9)),
                refundRequest(
                  id: 2,
                  order: orderId2,
                  number: 'BX-0990',
                  status: 'completed',
                  reason: 'The code did not work.',
                  at: DateTime(2026, 11, 2, 9),
                ),
              ]),
            ),
          },
        );
        await snap(tester, 'refund_requests');
      });

      testWidgets('16.1 notifications', (tester) async {
        await at(
          tester,
          Routes.accountNotifications.path,
          arrange: (h) =>
              h.notifications.pages = {1: Ok(inboxPageOf(drawnInbox))},
        );
        await snap(tester, 'notifications');
      });

      testWidgets('16.2 notifications offline', (tester) async {
        await at(
          tester,
          Routes.accountNotifications.path,
          arrange: (h) =>
              h.notifications.pages = {1: const Err(NetworkFailure())},
        );
        await snap(tester, 'notifications_offline');
      });

      testWidgets('16.2 notifications empty', (tester) async {
        await at(tester, Routes.accountNotifications.path);
        await snap(tester, 'notifications_empty');
      });

      testWidgets('16.3 notification settings', (tester) async {
        await at(tester, Routes.notificationSettings.path);
        await snap(tester, 'notification_settings');
      });

      testWidgets('home header bell with unread badge', (tester) async {
        await at(
          tester,
          Routes.home.path,
          arrange: (h) => h.notifications.unreadResult = const Ok(2),
        );
        await snap(tester, 'home_bell');
      });

      testWidgets('17.1 hub with the M-A17 rows', (tester) async {
        await at(
          tester,
          Routes.account.path,
          arrange: (h) => h.notifications.unreadResult = const Ok(2),
        );
        await snap(tester, 'account_hub');
      });

      testWidgets('order detail with a refund request and a review entry', (
        tester,
      ) async {
        await at(
          tester,
          Routes.orderLocation(orderId),
          arrange: (h) {
            h.reviews.eligibleResult = Ok([reviewable()]);
            h.support.refundPages = {
              1: Ok(pagedOf([refundRequest()])),
            };
          },
        );
        await snap(tester, 'order_detail');
      });

      testWidgets('360 wide ticket thread', (tester) async {
        await at(
          tester,
          Routes.ticketLocation(ticketId),
          size: const Size(360, 800),
        );
        await snap(tester, 'ticket_thread_360');
      });
    });
  }
}
