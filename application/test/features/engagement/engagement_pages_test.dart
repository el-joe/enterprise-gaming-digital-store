import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';
import 'package:application/features/support/domain/entities/support.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/engagement_fakes.dart';
import '../../helpers/pump_app.dart';
import '../../helpers/wallet_fakes.dart';

/// M-A17 screens in `en` and `ar`: reviews, support, refund requests and notifications, every
/// state, through the real router and the harness fakes.
void main() {
  for (final lang in ['en', 'ar']) {
    final l10n = l10nFor(lang);

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      bool signedIn = true,
      AppConfig? config,
      void Function(TestHarness h)? arrange,
      Size size = const Size(390, 844),
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: signedIn,
        size: size,
        config: [Ok(config ?? dollarConfig())],
        arrange: arrange,
        settle: false,
      );
      await tester.pumpAndSettle();
      h.read(appRouterProvider).go(location);
      await tester.pumpAndSettle();
      return h;
    }

    String path(TestHarness h) => h.read(appRouterProvider).state.uri.path;
    Finder key(String value) => find.byKey(ValueKey(value));

    Future<void> tapKey(WidgetTester tester, String value) async {
      await tester.ensureVisible(key(value));
      await tester.pumpAndSettle();
      await tester.tap(key(value));
      await tester.pumpAndSettle();
    }

    group('reviews ($lang)', () {
      testWidgets('my reviews: ready to review, statuses, edit', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.myReviews.path,
          arrange: (h) => h.reviews
            ..eligibleResult = Ok([reviewable()])
            ..pages = {
              1: Ok(
                pagedOf([
                  myReview(),
                  myReview(
                    id: 2,
                    product: 'Nova Points 1000',
                    status: 'approved',
                    rating: 5,
                  ),
                  myReview(id: 3, product: 'Zenith Pass', status: 'rejected'),
                ]),
              ),
            },
        );
        expect(find.text(l10n.reviewsReadyTitle), findsOneWidget);
        expect(find.text(l10n.reviewsYoursTitle), findsOneWidget);
        expect(find.text(l10n.reviewStatusPending), findsOneWidget);
        expect(find.text(l10n.reviewStatusApproved), findsOneWidget);
        expect(find.text(l10n.reviewStatusRejected), findsOneWidget);

        await tapKey(tester, 'review-edit-2');
        expect(path(h), Routes.editReviewLocation(2));
        expect(find.text(l10n.reviewEditTitle), findsOneWidget);
        expect(find.text(l10n.reviewEditNoticeTitle), findsOneWidget);
        await tester.tap(key('review-star-3'));
        await tester.pumpAndSettle();
        await tapKey(tester, 'review-submit');
        expect(h.reviews.updates.single['rating'], 3);
        expect(find.text(l10n.reviewUpdated), findsOneWidget);
        expect(path(h), Routes.myReviews.path);
      });

      testWidgets('write a review: rating required, then submitted', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.myReviews.path,
          arrange: (h) => h.reviews.eligibleResult = Ok([reviewable()]),
        );
        await tapKey(tester, 'reviewable-1');
        expect(path(h), Routes.writeReviewLocation(1));
        expect(find.text('Orbit Card'), findsOneWidget);
        await tapKey(tester, 'review-submit');
        expect(find.text(l10n.reviewRatingRequired), findsOneWidget);
        expect(h.reviews.submits, isEmpty);

        await tester.tap(key('review-star-4'));
        await tester.pumpAndSettle();
        await tester.enterText(
          find.descendant(
            of: key('review-title'),
            matching: find.byType(TextField),
          ),
          'Fast and easy',
        );
        await tapKey(tester, 'review-submit');
        expect(h.reviews.submits.single['rating'], 4);
        expect(h.reviews.submits.single['title'], 'Fast and easy');
        expect(find.text(l10n.reviewSubmitted), findsOneWidget);
      });

      testWidgets(
        'errors: email unverified, already reviewed, 422 beside the field',
        (tester) async {
          final h = await at(
            tester,
            Routes.writeReviewLocation(1),
            arrange: (h) => h.reviews.eligibleResult = Ok([reviewable()]),
          );
          await tester.tap(key('review-star-5'));
          await tester.pumpAndSettle();

          h.reviews.writeResult = const Err(EmailUnverifiedFailure());
          await tapKey(tester, 'review-submit');
          expect(find.text(l10n.reviewEmailUnverifiedTitle), findsOneWidget);
          expect(find.text(l10n.revealVerifyAction), findsOneWidget);

          h.reviews.writeResult = const Err(
            ConflictFailure('review_exists', message: 'Already reviewed.'),
          );
          await tapKey(tester, 'review-submit');
          expect(find.text(l10n.reviewExistsTitle), findsOneWidget);

          h.reviews.writeResult = const Err(
            ValidationFailure({
              'body': ['The body is too long.'],
            }),
          );
          await tapKey(tester, 'review-submit');
          expect(find.text('The body is too long.'), findsOneWidget);
          expect(path(h), Routes.writeReviewLocation(1));
        },
      );

      testWidgets('an item that is not eligible, and the empty list', (
        tester,
      ) async {
        await at(tester, Routes.writeReviewLocation(9));
        expect(find.text(l10n.reviewNotEligibleTitle), findsOneWidget);
        await tester.tap(find.text(l10n.myReviewsTitle));
        await tester.pumpAndSettle();
        expect(find.text(l10n.myReviewsEmptyTitle), findsOneWidget);
      });

      testWidgets('my reviews offline and retry', (tester) async {
        final h = await at(
          tester,
          Routes.myReviews.path,
          arrange: (h) => h.reviews.pages = {1: const Err(NetworkFailure())},
        );
        expect(find.text(l10n.offlineTitle), findsOneWidget);
        h.reviews.pages = {
          1: Ok(pagedOf([myReview()])),
        };
        await tester.tap(find.text(l10n.actionRetry));
        await tester.pumpAndSettle();
        expect(find.text(l10n.reviewStatusPending), findsOneWidget);
      });

      testWidgets(
        'product page: "Write a review" only for an eligible purchase',
        (tester) async {
          final h = await at(
            tester,
            Routes.productLocation('orbit-card'),
            arrange: (h) => h.reviews.eligibleResult = Ok([reviewable()]),
          );
          await tester.dragUntilVisible(
            key('product-write-review'),
            find.byType(ListView).first,
            const Offset(0, -300),
          );
          await tester.pumpAndSettle();
          await tester.tap(key('product-write-review'));
          await tester.pumpAndSettle();
          expect(path(h), Routes.writeReviewLocation(1));
        },
      );

      testWidgets('product page without an eligible purchase has no entry', (
        tester,
      ) async {
        await at(tester, Routes.productLocation('orbit-card'));
        expect(key('product-write-review'), findsNothing);
      });

      testWidgets('order detail: a delivered line gets "Write a review"', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.orderLocation(orderId),
          arrange: (h) => h.reviews.eligibleResult = Ok([reviewable()]),
        );
        await tapKey(tester, 'order-line-review-1');
        expect(path(h), Routes.writeReviewLocation(1));
      });
    });

    group('support ($lang)', () {
      testWidgets('tickets: statuses, open a thread, reply', (tester) async {
        final h = await at(
          tester,
          Routes.accountSupport.path,
          arrange: (h) {
            h.support.ticketPages = {
              1: Ok(
                pagedOf([
                  ticket(),
                  ticket(
                    id: '01JF00000000000000000000T2',
                    number: 'T-261102-0004',
                    subject: 'Payment charged twice?',
                    status: 'pending_customer',
                    category: 'payment',
                    order: null,
                  ),
                  ticket(
                    id: '01JF00000000000000000000T3',
                    number: 'T-261020-0002',
                    subject: 'Change my email',
                    status: 'closed',
                    category: 'account',
                    order: null,
                  ),
                ]),
              ),
            };
            h.support.threads[ticketId] = Ok(
              ticket(
                messages: [
                  message(1, 'Nothing arrived.'),
                  message(2, 'We are checking.', from: 'staff'),
                ],
              ),
            );
          },
        );
        expect(find.text(l10n.ticketStatusWaiting), findsOneWidget);
        expect(find.text(l10n.ticketStatusAnswered), findsOneWidget);
        expect(find.text(l10n.ticketStatusClosed), findsOneWidget);

        await tapKey(tester, 'ticket-$ticketId');
        expect(path(h), Routes.ticketLocation(ticketId));
        expect(find.text('Nothing arrived.'), findsOneWidget);
        expect(find.text('We are checking.'), findsOneWidget);
        await tester.enterText(key('ticket-reply'), 'Any news?');
        await tester.tap(key('ticket-send'));
        await tester.pumpAndSettle();
        expect(h.support.replies.single.body, 'Any news?');
        expect(find.text('Any news?'), findsOneWidget);
      });

      testWidgets('reply to a closed ticket: closed state and new request', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.ticketLocation(ticketId),
          arrange: (h) {
            h.support.threads[ticketId] = Ok(ticket(status: 'resolved'));
            h.support.replyResult = const Err(
              ConflictFailure('ticket_closed', message: 'Closed.'),
            );
          },
        );
        await tester.enterText(key('ticket-reply'), 'Hello?');
        await tester.tap(key('ticket-send'));
        await tester.pumpAndSettle();
        expect(find.text(l10n.ticketClosedTitle), findsOneWidget);
        expect(key('ticket-reply'), findsNothing);
        await tapKey(tester, 'ticket-closed-new');
        expect(path(h), Routes.newTicket.path);
      });

      testWidgets('new request: topic, order picker, checks, then the thread', (
        tester,
      ) async {
        final h = await at(tester, Routes.newTicketLocation());
        await tapKey(tester, 'ticket-submit');
        expect(find.text(l10n.fieldRequired), findsNWidgets(2));
        expect(h.support.opened, isEmpty);

        await tapKey(tester, 'ticket-category-payment');
        await tapKey(tester, 'ticket-order');
        await tester.tap(find.textContaining('BX-1003'));
        await tester.pumpAndSettle();
        await tester.enterText(
          find.descendant(
            of: key('ticket-subject'),
            matching: find.byType(TextField),
          ),
          'Charged twice',
        );
        await tester.enterText(
          find.descendant(
            of: key('ticket-body'),
            matching: find.byType(TextField),
          ),
          'I see two charges.',
        );
        await tapKey(tester, 'ticket-submit');
        expect(h.support.opened.single, {
          'category': 'payment',
          'subject': 'Charged twice',
          'body': 'I see two charges.',
          'order_public_id': orderId2,
        });
        expect(path(h), Routes.ticketLocation('01JF00000000000000000000T9'));
      });

      testWidgets(
        'new request: another customer\'s order is refused beside the picker',
        (tester) async {
          final h = await at(
            tester,
            Routes.newTicketLocation(order: '01JFOTHER0000000000000000X'),
            arrange: (h) => h.support.openResult = const Err(
              ValidationFailure({
                'order_public_id': ['This order was not found.'],
              }),
            ),
          );
          expect(find.text(l10n.ticketOrderLinked), findsOneWidget);
          await tester.enterText(
            find.descendant(
              of: key('ticket-subject'),
              matching: find.byType(TextField),
            ),
            'Code missing',
          );
          await tester.enterText(
            find.descendant(
              of: key('ticket-body'),
              matching: find.byType(TextField),
            ),
            'Nothing arrived.',
          );
          await tapKey(tester, 'ticket-submit');
          expect(find.text('This order was not found.'), findsOneWidget);
          expect(path(h), Routes.newTicket.path);
        },
      );

      testWidgets('order detail "Get help" opens a request about the order', (
        tester,
      ) async {
        final h = await at(tester, Routes.orderLocation(orderId));
        await tester.ensureVisible(find.text(l10n.orderGetHelp));
        await tester.tap(find.text(l10n.orderGetHelp));
        await tester.pumpAndSettle();
        expect(path(h), Routes.newTicket.path);
        expect(
          h.read(appRouterProvider).state.uri.queryParameters['order'],
          orderId,
        );
      });

      testWidgets('signed out: sign in and the web help page', (tester) async {
        final h = await at(tester, Routes.accountSupport.path, signedIn: false);
        expect(find.text(l10n.supportSignedOutTitle), findsOneWidget);
        expect(h.support.ticketPageCalls, isEmpty);
        await tapKey(tester, 'support-web-help');
        expect(h.native.opened, isNotEmpty);
      });

      testWidgets('empty, error with request id and retry', (tester) async {
        final h = await at(
          tester,
          Routes.accountSupport.path,
          arrange: (h) => h.support.ticketPages = {
            1: const Err(
              ServerFailure(
                statusCode: 500,
                requestId: '01JFREQ0000000000000000000',
              ),
            ),
          },
        );
        expect(find.text(l10n.ticketsLoadError), findsOneWidget);
        h.support.ticketPages = {1: Ok(pagedOf(const <SupportTicket>[]))};
        await tester.tap(find.text(l10n.actionRetry));
        await tester.pumpAndSettle();
        expect(find.text(l10n.ticketsEmptyTitle), findsOneWidget);
        expect(key('ticket-new'), findsOneWidget);
      });
    });

    group('refund requests ($lang)', () {
      testWidgets('form from the order detail, sent, then the list', (
        tester,
      ) async {
        final h = await at(tester, Routes.orderLocation(orderId));
        await tester.ensureVisible(find.text(l10n.orderRequestRefund));
        await tester.tap(find.text(l10n.orderRequestRefund));
        await tester.pumpAndSettle();
        expect(path(h), Routes.refundRequestLocation(orderId));
        expect(find.text(l10n.refundHowTitle), findsOneWidget);

        await tester.enterText(
          find.descendant(
            of: key('refund-reason'),
            matching: find.byType(TextField),
          ),
          'too short',
        );
        await tapKey(tester, 'refund-submit');
        expect(find.text(l10n.refundReasonTooShort(10)), findsOneWidget);

        h.support.refundPages = {
          1: Ok(pagedOf([refundRequest()])),
        };
        await tester.enterText(
          find.descendant(
            of: key('refund-reason'),
            matching: find.byType(TextField),
          ),
          'The second code shows as already used.',
        );
        await tapKey(tester, 'refund-submit');
        expect(h.support.refunds.single['order_public_id'], orderId);
        expect(path(h), Routes.refundRequests.path);
        expect(find.text(l10n.refundSentTitle), findsOneWidget);
        expect(find.text(l10n.refundStatusRequested), findsOneWidget);
      });

      testWidgets('409 open, 409 not allowed and 422 foreign order banners', (
        tester,
      ) async {
        final h = await at(tester, Routes.refundRequestLocation(orderId));
        await tester.enterText(
          find.descendant(
            of: key('refund-reason'),
            matching: find.byType(TextField),
          ),
          'The code was already redeemed.',
        );
        h.support.refundResult = const Err(
          ConflictFailure('refund_request_open', message: 'Open.'),
        );
        await tapKey(tester, 'refund-submit');
        expect(find.text(l10n.refundOpenTitle), findsOneWidget);

        h.support.refundResult = const Err(
          ConflictFailure(
            'refund_not_allowed',
            message: 'Only a paid order can be refunded.',
          ),
        );
        await tapKey(tester, 'refund-submit');
        expect(find.text(l10n.refundNotAllowedTitle), findsOneWidget);
        expect(find.text('Only a paid order can be refunded.'), findsOneWidget);

        h.support.refundResult = const Err(
          ValidationFailure({
            'order_public_id': ['This order was not found.'],
          }),
        );
        await tapKey(tester, 'refund-submit');
        expect(find.text(l10n.refundOrderRefusedTitle), findsOneWidget);
        expect(find.text('This order was not found.'), findsOneWidget);
      });

      testWidgets('order detail shows an open request and hides the button', (
        tester,
      ) async {
        await at(
          tester,
          Routes.orderLocation(orderId),
          arrange: (h) => h.support.refundPages = {
            1: Ok(pagedOf([refundRequest()])),
          },
        );
        expect(
          find.text(l10n.orderRefundRequestStatus(l10n.refundStatusRequested)),
          findsOneWidget,
        );
        expect(find.text(l10n.orderRequestRefund), findsNothing);
      });

      testWidgets('the Account hub opens the list (empty)', (tester) async {
        final h = await at(tester, Routes.account.path);
        await tapKey(tester, 'account-refunds');
        expect(path(h), Routes.refundRequests.path);
        expect(find.text(l10n.refundRequestsEmptyTitle), findsOneWidget);
      });
    });

    group('notifications ($lang)', () {
      testWidgets('home bell with the unread badge opens the inbox', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.home.path,
          arrange: (h) {
            h.notifications.unreadResult = const Ok(2);
            h.notifications.pages = {1: Ok(inboxPageOf(fixtureInbox()))};
          },
        );
        expect(
          find.bySemanticsLabel(l10n.notificationBellUnread(2)),
          findsOneWidget,
        );
        await tester.tap(key('home-notifications'));
        await tester.pumpAndSettle();
        expect(path(h), Routes.accountNotifications.path);
        expect(find.text(l10n.notificationsUnreadCount(2)), findsOneWidget);
      });

      testWidgets('tap marks read and opens the ticket; back returns', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.accountNotifications.path,
          arrange: (h) {
            h.notifications.pages = {1: Ok(inboxPageOf(fixtureInbox()))};
            h.notifications.unreadResult = const Ok(2);
          },
        );
        final first = fixtureInbox().first;
        await tester.tap(key('notification-${first.id}'));
        await tester.pumpAndSettle();
        expect(h.notifications.markedRead, [first.id]);
        expect(path(h), Routes.ticketLocation(first.link!.value));
        await tester.tap(find.byTooltip(l10n.actionBack).first);
        await tester.pumpAndSettle();
        expect(path(h), Routes.accountNotifications.path);
        expect(find.text(l10n.notificationsUnreadCount(1)), findsOneWidget);
      });

      testWidgets('an order notification opens the order detail', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.accountNotifications.path,
          arrange: (h) =>
              h.notifications.pages = {1: Ok(inboxPageOf(fixtureInbox()))},
        );
        final last = fixtureInbox().last;
        await tester.tap(key('notification-${last.id}'));
        await tester.pumpAndSettle();
        expect(path(h), Routes.orderLocation(last.link!.value));
      });

      testWidgets('mark all read', (tester) async {
        final h = await at(
          tester,
          Routes.accountNotifications.path,
          arrange: (h) =>
              h.notifications.pages = {1: Ok(inboxPageOf(fixtureInbox()))},
        );
        await tester.tap(key('notifications-mark-all'));
        await tester.pumpAndSettle();
        expect(h.notifications.markAllCalls, 1);
        expect(find.text(l10n.notificationsAllRead), findsOneWidget);
        expect(
          find.byKey(ValueKey('unread-dot-${fixtureInbox().first.id}')),
          findsNothing,
        );
      });

      testWidgets('empty and offline', (tester) async {
        final h = await at(
          tester,
          Routes.accountNotifications.path,
          arrange: (h) =>
              h.notifications.pages = {1: const Err(NetworkFailure())},
        );
        expect(find.text(l10n.offlineTitle), findsOneWidget);
        h.notifications.pages = {1: Ok(inboxPageOf(const []))};
        await tester.tap(find.text(l10n.actionRetry));
        await tester.pumpAndSettle();
        expect(find.text(l10n.notificationsEmptyTitle), findsOneWidget);
      });

      testWidgets('settings: required locked on, a switch saves', (
        tester,
      ) async {
        final h = await at(tester, Routes.accountNotifications.path);
        await tapKey(tester, 'notifications-settings');
        expect(path(h), Routes.notificationSettings.path);
        expect(find.text(l10n.preferenceRequired), findsNWidgets(2));
        expect(find.text(l10n.preferencePushTitle), findsOneWidget);
        await tapKey(tester, 'preference-orders-mail');
        expect(h.notifications.updates, isEmpty);
        await tapKey(tester, 'preference-marketing-mail');
        expect(h.notifications.updates.single.single.enabled, isTrue);
      });

      testWidgets('settings: a refused save shows the server message', (
        tester,
      ) async {
        await at(
          tester,
          Routes.notificationSettings.path,
          arrange: (h) => h.notifications.updateResult = const Err(
            DomainFailure(
              'notification_preference_mandatory',
              message: 'This notification cannot be turned off.',
            ),
          ),
        );
        await tapKey(tester, 'preference-support-mail');
        expect(
          find.text('This notification cannot be turned off.'),
          findsOneWidget,
        );
      });

      testWidgets('tap targets and labels', (tester) async {
        final handle = tester.ensureSemantics();
        await at(
          tester,
          Routes.accountNotifications.path,
          arrange: (h) =>
              h.notifications.pages = {1: Ok(inboxPageOf(fixtureInbox()))},
        );
        await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
        await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
        handle.dispose();
      });
    });
  }
}
