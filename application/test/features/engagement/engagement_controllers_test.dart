import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/link_targets.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/notifications/domain/entities/notifications.dart';
import 'package:application/features/notifications/notification_badge.dart';
import 'package:application/features/notifications/presentation/controllers/notifications_controllers.dart';
import 'package:application/features/reviews/presentation/controllers/reviews_controllers.dart';
import 'package:application/features/reviews/review_eligibility.dart';
import 'package:application/features/support/presentation/controllers/support_controllers.dart';
import 'package:application/features/support/refund_status.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:flutter_riverpod/misc.dart' show ProviderListenable;
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/engagement_fakes.dart';
import '../../helpers/fakes.dart';
import '../../helpers/pump_app.dart';

Future<void> settle() async {
  for (var i = 0; i < 100; i++) {
    await Future<void>.delayed(Duration.zero);
  }
}

void keep<T>(TestHarness h, ProviderListenable<T> provider) {
  final sub = h.container.listen(provider, (_, _) {});
  addTearDown(sub.close);
}

ValidationFailure validation(String field, String message) =>
    ValidationFailure({
      field: [message],
    }, message: message);

void main() {
  TestHarness harness({bool signedIn = true}) {
    final h = buildHarness(signedIn: signedIn);
    addTearDown(h.dispose);
    return h;
  }

  void configure(TestHarness h, {bool reviews = true, bool support = true}) {
    final config = fixtureAppConfig();
    h
        .read(latestAppConfigProvider.notifier)
        .set(
          config.copyWith(
            features: config.features.copyWith(
              reviewsEnabled: reviews,
              supportEnabled: support,
            ),
          ),
        );
  }

  group('review eligibility (verified purchase gating)', () {
    test(
      'the product and the order line only get the API-listed item',
      () async {
        final h = harness();
        h.reviews.eligibleResult = Ok(fixtureReviewable());
        keep(h, eligibleReviewForProductProvider('orbit-card'));
        keep(h, eligibleReviewForProductProvider('nova-points'));
        keep(h, eligibleReviewForOrderItemProvider(1));
        keep(h, eligibleReviewForOrderItemProvider(2));
        await settle();
        expect(
          h.read(eligibleReviewForProductProvider('orbit-card'))?.orderItemId,
          1,
        );
        expect(h.read(eligibleReviewForProductProvider('nova-points')), isNull);
        expect(
          h.read(eligibleReviewForOrderItemProvider(1))?.orderNumber,
          'FIXTURE-0001',
        );
        expect(h.read(eligibleReviewForOrderItemProvider(2)), isNull);
        expect(h.reviews.eligibleCalls, 1);
      },
    );

    test(
      'signed out or reviews_enabled off: nothing is asked or shown',
      () async {
        final out = harness(signedIn: false);
        out.reviews.eligibleResult = Ok(fixtureReviewable());
        keep(out, eligibleReviewForProductProvider('orbit-card'));
        await settle();
        expect(
          out.read(eligibleReviewForProductProvider('orbit-card')),
          isNull,
        );
        expect(out.reviews.eligibleCalls, 0);

        final off = harness();
        off.reviews.eligibleResult = Ok(fixtureReviewable());
        configure(off, reviews: false);
        keep(off, eligibleReviewForProductProvider('orbit-card'));
        await settle();
        expect(
          off.read(eligibleReviewForProductProvider('orbit-card')),
          isNull,
        );
        expect(off.reviews.eligibleCalls, 0);
      },
    );

    test('a failed read shows no entry', () async {
      final h = harness();
      h.reviews.eligibleResult = const Err(NetworkFailure());
      keep(h, eligibleReviewForProductProvider('orbit-card'));
      await settle();
      expect(h.read(eligibleReviewForProductProvider('orbit-card')), isNull);
    });
  });

  group('review form', () {
    ReviewFormControllerProvider newReview([int item = 1]) =>
        reviewFormControllerProvider(orderItemId: item);

    test(
      'create: subject from the eligible list, rating first, then sent',
      () async {
        final h = harness();
        h.reviews.eligibleResult = Ok(fixtureReviewable());
        keep(h, newReview());
        await settle();
        final subject = h.read(newReview()).subject.valueOrNull!;
        expect(subject.productName, 'Orbit Card');
        expect(subject.orderNumber, 'FIXTURE-0001');
        expect(subject.editing, isFalse);

        final controller = h.read(newReview().notifier);
        expect(await controller.submit(title: 'x', body: 'y'), isNull);
        expect(
          h.read(newReview()).problems['rating'],
          ReviewFieldProblem.ratingRequired,
        );
        expect(h.reviews.submits, isEmpty);

        controller.setRating(5);
        final eligibleBefore = h.reviews.eligibleCalls;
        keep(h, reviewEligibilityProvider);
        final saved = await controller.submit(title: '  Instant ', body: '   ');
        await settle();
        expect(saved?.status, 'pending');
        expect(h.reviews.submits.single, {
          'order_item_id': 1,
          'rating': 5,
          'title': 'Instant',
          'body': null,
        });
        // The item leaves the eligible list: the list is read again.
        expect(h.reviews.eligibleCalls, greaterThan(eligibleBefore));
      },
    );

    test('an item the API does not list cannot be reviewed', () async {
      final h = harness();
      h.reviews.eligibleResult = Ok(fixtureReviewable());
      keep(h, newReview(7));
      await settle();
      expect(
        h.read(newReview(7)).subject,
        isA<Failed<ReviewSubject>>().having(
          (f) => f.failure,
          'failure',
          isA<NotFoundFailure>(),
        ),
      );
      expect(
        await h.read(newReview(7).notifier).submit(title: '', body: ''),
        isNull,
      );
      expect(h.reviews.submits, isEmpty);
    });

    test(
      '422 field errors beside the fields, domain codes in the banner',
      () async {
        final h = harness();
        h.reviews.eligibleResult = Ok(fixtureReviewable());
        keep(h, newReview());
        await settle();
        final controller = h.read(newReview().notifier)..setRating(4);

        h.reviews.writeResult = Err(
          validation(
            'body',
            'The body may not be greater than 5000 characters.',
          ),
        );
        await controller.submit(title: '', body: 'x');
        expect(
          h.read(newReview()).serverError('body'),
          'The body may not be greater than 5000 characters.',
        );
        expect(h.read(newReview()).bannerFailure, isNull);

        controller.edited();
        expect(h.read(newReview()).failure, isNull);

        h.reviews.writeResult = const Err(
          DomainFailure(
            'review_not_eligible',
            message: 'Only items you bought and received can be reviewed.',
            errors: {
              'order_item_id': [
                'Only items you bought and received can be reviewed.',
              ],
            },
          ),
        );
        await controller.submit(title: '', body: '');
        expect(h.read(newReview()).bannerFailure, isA<DomainFailure>());

        h.reviews.writeResult = const Err(EmailUnverifiedFailure());
        await controller.submit(title: '', body: '');
        expect(
          h.read(newReview()).bannerFailure,
          isA<EmailUnverifiedFailure>(),
        );

        h.reviews.writeResult = const Err(
          ConflictFailure('review_exists', message: 'Already reviewed.'),
        );
        await controller.submit(title: '', body: '');
        expect(
          (h.read(newReview()).bannerFailure! as ConflictFailure).code,
          'review_exists',
        );
        expect(h.read(newReview()).saving, isFalse);
      },
    );

    test('edit: found in my reviews, PATCH with the new rating', () async {
      final h = harness();
      h.reviews.pages = {
        1: Ok(pagedOf([myReview(id: 3)], lastPage: 2)),
        2: Ok(
          pagedOf(
            [myReview(id: 8, rating: 2, title: 'Slow')],
            page: 2,
            lastPage: 2,
          ),
        ),
      };
      final provider = reviewFormControllerProvider(reviewId: 8);
      keep(h, provider);
      await settle();
      final state = h.read(provider);
      expect(state.subject.valueOrNull?.editing, isTrue);
      expect(state.rating, 2);
      expect(h.reviews.pageCalls, [1, 2]);

      h.read(provider.notifier).setRating(5);
      final saved = await h
          .read(provider.notifier)
          .submit(title: 'Slow', body: 'Fine in the end.');
      expect(saved?.status, 'pending');
      expect(h.reviews.updates.single, {
        'id': 8,
        'rating': 5,
        'title': 'Slow',
        'body': 'Fine in the end.',
      });
      expect(h.reviews.submits, isEmpty);
    });

    test('edit of a review that is not the customer\'s is not found', () async {
      final h = harness();
      h.reviews.pages = {
        1: Ok(pagedOf([myReview(id: 3)])),
      };
      final provider = reviewFormControllerProvider(reviewId: 99);
      keep(h, provider);
      await settle();
      expect(h.read(provider).subject, isA<Failed<ReviewSubject>>());
    });

    test('my reviews: pages through meta, signed out loads nothing', () async {
      final h = harness();
      h.reviews.pages = {
        1: Ok(pagedOf([myReview()], lastPage: 2)),
        2: Ok(
          pagedOf([myReview(id: 2, status: 'approved')], page: 2, lastPage: 2),
        ),
      };
      keep(h, myReviewsControllerProvider);
      await settle();
      await h.read(myReviewsControllerProvider.notifier).loadMore();
      expect(h.read(myReviewsControllerProvider).items.map((r) => r.id), [
        1,
        2,
      ]);
      expect(h.read(myReviewsControllerProvider).hasMore, isFalse);

      final out = harness(signedIn: false);
      keep(out, myReviewsControllerProvider);
      await settle();
      expect(out.reviews.pageCalls, isEmpty);
    });
  });

  group('support tickets', () {
    test(
      'create: topic, linked order, subject and message; local checks first',
      () async {
        final h = harness();
        final provider = newTicketControllerProvider(order: orderId);
        keep(h, provider);
        await settle();
        expect(h.read(provider).category, 'order');
        expect(h.read(provider).orderPublicId, orderId);
        expect(h.read(provider).orders, isA<Loaded<Object?>>());
        expect(h.read(provider).selectedOrder?.publicId, orderId);

        final controller = h.read(provider.notifier);
        expect(await controller.submit(subject: 'ab', body: ''), isNull);
        expect(h.read(provider).problems, {
          'subject': FormProblem.tooShort,
          'body': FormProblem.required,
        });
        expect(h.support.opened, isEmpty);

        controller
          ..setCategory('payment')
          ..setCategory('nonsense');
        final ticket = await controller.submit(
          subject: ' Code missing ',
          body: 'Nothing arrived.',
        );
        expect(ticket, isNotNull);
        expect(h.support.opened.single, {
          'category': 'payment',
          'subject': 'Code missing',
          'body': 'Nothing arrived.',
          'order_public_id': orderId,
        });
      },
    );

    test(
      '422 for another customer\'s order shows beside the order picker',
      () async {
        final h = harness();
        final provider = newTicketControllerProvider(
          order: '01JFOTHER0000000000000000X',
        );
        keep(h, provider);
        await settle();
        h.support.openResult = Err(
          validation('order_public_id', 'This order was not found.'),
        );
        await h
            .read(provider.notifier)
            .submit(subject: 'Code missing', body: 'Nothing arrived.');
        expect(
          h.read(provider).serverError('order_public_id'),
          'This order was not found.',
        );
        expect(h.read(provider).bannerFailure, isNull);
        // Unlinking the order clears the error.
        h.read(provider.notifier).setOrder(null);
        expect(h.read(provider).failure, isNull);
        expect(h.read(provider).orderPublicId, isNull);
      },
    );

    test(
      'reply: appended, composer cleared; empty reply is a local check',
      () async {
        final h = harness();
        final provider = ticketThreadControllerProvider(ticketId);
        h.support.threads[ticketId] = Ok(
          ticket(messages: [message(1, 'Nothing arrived.')]),
        );
        keep(h, provider);
        await settle();
        final controller = h.read(provider.notifier);
        expect(await controller.reply('   '), isFalse);
        expect(h.read(provider).problem, FormProblem.required);
        expect(h.support.replies, isEmpty);

        expect(await controller.reply(' Any news? '), isTrue);
        expect(h.support.replies.single, (ticket: ticketId, body: 'Any news?'));
        expect(
          h.read(provider).ticket.valueOrNull!.messages.map((m) => m.body),
          ['Nothing arrived.', 'Any news?'],
        );
        expect(h.read(provider).closed, isFalse);
      },
    );

    test('409 ticket_closed turns the thread into the closed state', () async {
      final h = harness();
      final provider = ticketThreadControllerProvider(ticketId);
      h.support.threads[ticketId] = Ok(ticket(status: 'resolved'));
      keep(h, provider);
      await settle();
      h.support.replyResult = const Err(
        ConflictFailure(
          'ticket_closed',
          message: 'This ticket is closed. Please open a new one.',
        ),
      );
      expect(await h.read(provider.notifier).reply('Hello?'), isFalse);
      expect(h.read(provider).closed, isTrue);
      expect(h.read(provider).bannerFailure, isNull);
      // No further replies are sent once closed.
      expect(await h.read(provider.notifier).reply('Again'), isFalse);
      expect(h.support.replies, hasLength(1));
    });

    test('a ticket loaded as closed is closed', () async {
      final h = harness();
      final provider = ticketThreadControllerProvider(ticketId);
      h.support.threads[ticketId] = Ok(ticket(status: 'closed'));
      keep(h, provider);
      await settle();
      expect(h.read(provider).closed, isTrue);
    });

    test(
      'tickets list pages; support_enabled off or signed out loads nothing',
      () async {
        final h = harness();
        h.support.ticketPages = {
          1: Ok(pagedOf([ticket()], lastPage: 2)),
          2: const Err(NetworkFailure()),
        };
        keep(h, ticketsControllerProvider);
        await settle();
        await h.read(ticketsControllerProvider.notifier).loadMore();
        expect(
          h.read(ticketsControllerProvider).moreFailure,
          isA<NetworkFailure>(),
        );
        expect(h.read(ticketsControllerProvider).items, hasLength(1));

        final off = harness();
        configure(off, support: false);
        keep(off, ticketsControllerProvider);
        await settle();
        expect(off.support.ticketPageCalls, isEmpty);
      },
    );
  });

  group('refund requests', () {
    RefundRequestFormControllerProvider form() =>
        refundRequestFormControllerProvider(orderId);

    test(
      'create: order loaded, reason checked, ticket linked, then sent',
      () async {
        final h = harness();
        h.support.ticketPages = {
          1: Ok(
            pagedOf([
              ticket(),
              ticket(id: '01JF00000000000000000000T2', order: null),
              ticket(id: '01JF00000000000000000000T3', status: 'closed'),
            ]),
          ),
        };
        keep(h, form());
        await settle();
        final data = h.read(form()).data.valueOrNull!;
        expect(data.order.summary.publicId, isNotEmpty);
        // Only open tickets about this order can be linked.
        expect(data.tickets.map((t) => t.publicId), [ticketId]);

        final controller = h.read(form().notifier);
        expect(await controller.submit(reason: 'short'), isNull);
        expect(h.read(form()).problem, FormProblem.tooShort);
        controller.setTicket(ticketId);
        final request = await controller.submit(
          reason: '  The code was already redeemed.  ',
        );
        expect(request?.status, 'requested');
        expect(h.support.refunds.single, {
          'order_public_id': orderId,
          'reason': 'The code was already redeemed.',
          'support_ticket_public_id': ticketId,
        });
      },
    );

    test(
      '422 for a foreign or ineligible order, 409 open and not allowed',
      () async {
        final h = harness();
        keep(h, form());
        await settle();
        final controller = h.read(form().notifier);
        const reason = 'The code was already redeemed.';

        h.support.refundResult = Err(
          validation('order_public_id', 'This order was not found.'),
        );
        expect(await controller.submit(reason: reason), isNull);
        final foreign = h.read(form()).bannerFailure! as ValidationFailure;
        expect(
          foreign.firstErrorFor('order_public_id'),
          'This order was not found.',
        );

        h.support.refundResult = const Err(
          ConflictFailure('refund_request_open', message: 'Already open.'),
        );
        await controller.submit(reason: reason);
        expect(
          (h.read(form()).bannerFailure! as ConflictFailure).code,
          'refund_request_open',
        );

        h.support.refundResult = const Err(
          ConflictFailure(
            'refund_not_allowed',
            message: 'Only a paid order can be refunded.',
          ),
        );
        await controller.submit(reason: reason);
        expect(
          (h.read(form()).bannerFailure! as ConflictFailure).code,
          'refund_not_allowed',
        );

        h.support.refundResult = Err(
          validation('reason', 'The reason must be at least 10 characters.'),
        );
        await controller.submit(reason: reason);
        expect(h.read(form()).serverError('reason'), isNotNull);
        expect(h.read(form()).bannerFailure, isNull);
      },
    );

    test('the order detail finds the order\'s latest request', () async {
      final h = harness();
      h.support.refundPages = {
        1: Ok(
          pagedOf([
            refundRequest(id: 2, order: orderId2, number: 'BX-1003'),
            refundRequest(),
          ]),
        ),
      };
      keep(h, orderRefundRequestProvider(orderId));
      keep(h, orderRefundRequestProvider('01JFNONE00000000000000000X'));
      await settle();
      expect(h.read(orderRefundRequestProvider(orderId)).value?.id, 1);
      expect(
        h.read(orderRefundRequestProvider('01JFNONE00000000000000000X')).value,
        isNull,
      );
      expect(h.support.refundPageCalls.first.perPage, 50);
      expect(openRefundRequestStatuses, containsAll(['requested', 'approved']));
    });

    test('a failed list leaves the order detail without a request', () async {
      final h = harness();
      h.support.refundPages = {1: const Err(NetworkFailure())};
      keep(h, orderRefundRequestProvider(orderId));
      await settle();
      expect(h.read(orderRefundRequestProvider(orderId)).value, isNull);
    });
  });

  group('notifications', () {
    test('inbox pages through meta and sets the unread badge', () async {
      final h = harness();
      h.notifications.pages = {
        1: Ok(inboxPageOf(fixtureInbox(), lastPage: 2, unread: 3)),
        2: Ok(
          inboxPageOf(
            [inboxItem(id: 'n3', readAt: DateTime.utc(2026, 11, 3))],
            page: 2,
            lastPage: 2,
            unread: 2,
          ),
        ),
      };
      keep(h, notificationsInboxControllerProvider);
      keep(h, unreadNotificationsProvider);
      await settle();
      expect(h.read(notificationsInboxControllerProvider).items, hasLength(2));
      expect(h.read(unreadNotificationsProvider), 3);
      await h.read(notificationsInboxControllerProvider.notifier).loadMore();
      expect(h.read(notificationsInboxControllerProvider).items, hasLength(3));
      expect(h.read(unreadNotificationsProvider), 2);
      expect(h.notifications.pageCalls, [1, 2]);
      // The last page: no more requests.
      await h.read(notificationsInboxControllerProvider.notifier).loadMore();
      expect(h.notifications.pageCalls, [1, 2]);
    });

    test(
      'unread badge: read at sign-in, 0 signed out, kept on failure',
      () async {
        final out = harness(signedIn: false);
        keep(out, unreadNotificationsProvider);
        await settle();
        expect(out.read(unreadNotificationsProvider), 0);
        expect(out.notifications.unreadCalls, 0);

        final h = harness();
        h.notifications.unreadResult = const Ok(4);
        keep(h, unreadNotificationsProvider);
        await settle();
        expect(h.read(unreadNotificationsProvider), 4);
        h.notifications.unreadResult = const Err(NetworkFailure());
        await h.read(unreadNotificationsProvider.notifier).refresh();
        expect(h.read(unreadNotificationsProvider), 4);

        h.read(sessionProvider.notifier).markSignedOut();
        await settle();
        expect(h.read(unreadNotificationsProvider), 0);
      },
    );

    test(
      'open marks an unread notification read and leads to its target',
      () async {
        final h = harness();
        final items = fixtureInbox();
        h.notifications.pages = {1: Ok(inboxPageOf(items))};
        keep(h, notificationsInboxControllerProvider);
        keep(h, unreadNotificationsProvider);
        await settle();
        expect(h.read(unreadNotificationsProvider), 2);
        final controller = h.read(
          notificationsInboxControllerProvider.notifier,
        );

        final ticketDestination = controller.open(items.first);
        expect(
          ticketDestination,
          InAppDestination(Routes.ticketLocation(items.first.link!.value)),
        );
        await settle();
        expect(h.notifications.markedRead, [items.first.id]);
        expect(
          h.read(notificationsInboxControllerProvider).items.first.isRead,
          isTrue,
        );
        expect(h.read(unreadNotificationsProvider), 1);

        expect(
          controller.open(items.last),
          InAppDestination(Routes.orderLocation(items.last.link!.value)),
        );
        await settle();
        expect(h.read(unreadNotificationsProvider), 0);

        // Already read: no second call.
        controller.open(
          h.read(notificationsInboxControllerProvider).items.first,
        );
        await settle();
        expect(h.notifications.markedRead, hasLength(2));
      },
    );

    test('a failed mark read keeps the item unread and the badge', () async {
      final h = harness();
      final items = fixtureInbox();
      h.notifications
        ..pages = {1: Ok(inboxPageOf(items))}
        ..markReadResults[items.first.id] = const Err(NotFoundFailure());
      keep(h, notificationsInboxControllerProvider);
      keep(h, unreadNotificationsProvider);
      await settle();
      h.read(notificationsInboxControllerProvider.notifier).open(items.first);
      await settle();
      expect(
        h.read(notificationsInboxControllerProvider).items.first.isRead,
        isFalse,
      );
      expect(h.read(unreadNotificationsProvider), 2);
    });

    test(
      'mark all read: every item read, badge 0; a failure is returned',
      () async {
        final h = harness();
        h.notifications.pages = {1: Ok(inboxPageOf(fixtureInbox()))};
        keep(h, notificationsInboxControllerProvider);
        keep(h, unreadNotificationsProvider);
        keep(h, markAllReadProvider);
        await settle();
        expect(await h.read(markAllReadProvider.notifier).run(), isNull);
        expect(h.notifications.markAllCalls, 1);
        expect(h.read(unreadNotificationsProvider), 0);
        expect(
          h
              .read(notificationsInboxControllerProvider)
              .items
              .every((n) => n.isRead),
          isTrue,
        );
        expect(h.read(markAllReadProvider), isFalse);

        h.notifications.markAllResult = const Err(NetworkFailure());
        expect(
          await h.read(markAllReadProvider.notifier).run(),
          isA<NetworkFailure>(),
        );
      },
    );

    test('link targets: ticket and order are in-app, others go nowhere', () {
      expect(
        destinationFor(ticketLink('01JFT')),
        InAppDestination(Routes.ticketLocation('01JFT')),
      );
      expect(
        destinationFor(orderLink('01JFO')),
        InAppDestination(Routes.orderLocation('01JFO')),
      );
      expect(
        destinationFor(
          LinkTarget(
            type: 'ticket',
            value: ' ',
            url: Uri.parse('https://x.test'),
          ),
        ),
        isNull,
      );
    });

    test('notification age: just now, minutes, hours, date', () {
      final now = DateTime.utc(2026, 11, 11, 12);
      expect(
        notificationAge(now, now.add(const Duration(seconds: 20))),
        isA<JustNow>(),
      );
      expect(
        (notificationAge(
          now,
          now.subtract(const Duration(minutes: 2)),
        ) as MinutesAgo).minutes,
        2,
      );
      expect(
        (notificationAge(
          now,
          now.subtract(const Duration(hours: 5)),
        ) as HoursAgo).hours,
        5,
      );
      expect(
        notificationAge(now, now.subtract(const Duration(days: 2))),
        isA<OnDate>(),
      );
    });
  });

  group('notification preferences', () {
    test(
      'save one switch: PATCH with the cell, the server matrix kept',
      () async {
        final h = harness();
        keep(h, notificationPreferencesControllerProvider);
        await settle();
        final controller = h.read(
          notificationPreferencesControllerProvider.notifier,
        );
        expect(
          h
              .read(notificationPreferencesControllerProvider)
              .cell('marketing', 'mail')
              ?.enabled,
          isFalse,
        );
        expect(await controller.toggle('marketing', 'mail', true), isNull);
        expect(
          h.notifications.updates.single.single,
          const PreferenceChange(
            category: 'marketing',
            channel: 'mail',
            enabled: true,
          ),
        );
        expect(
          h
              .read(notificationPreferencesControllerProvider)
              .cell('marketing', 'mail')
              ?.enabled,
          isTrue,
        );
        expect(
          h.read(notificationPreferencesControllerProvider).saving,
          isEmpty,
        );
      },
    );

    test('mandatory cells are never switched off', () async {
      final h = harness();
      keep(h, notificationPreferencesControllerProvider);
      await settle();
      await h
          .read(notificationPreferencesControllerProvider.notifier)
          .toggle('orders', 'mail', false);
      expect(h.notifications.updates, isEmpty);
      expect(
        h
            .read(notificationPreferencesControllerProvider)
            .cell('orders', 'mail')
            ?.enabled,
        isTrue,
      );
    });

    test(
      'a refused save puts the switch back and returns the failure',
      () async {
        final h = harness();
        keep(h, notificationPreferencesControllerProvider);
        await settle();
        h.notifications.updateResult = const Err(
          DomainFailure(
            'notification_preference_mandatory',
            message: 'This notification cannot be turned off.',
          ),
        );
        final failure = await h
            .read(notificationPreferencesControllerProvider.notifier)
            .toggle('support', 'mail', false);
        expect(failure, isA<DomainFailure>());
        expect(
          h
              .read(notificationPreferencesControllerProvider)
              .cell('support', 'mail')
              ?.enabled,
          isTrue,
        );
      },
    );

    test('a failed load until retry', () async {
      final h = harness();
      h.notifications.preferencesResult = const Err(NetworkFailure());
      keep(h, notificationPreferencesControllerProvider);
      await settle();
      expect(
        h.read(notificationPreferencesControllerProvider).preferences,
        isA<Failed<List<NotificationPreference>>>(),
      );
      h.notifications.preferencesResult = Ok(fixturePreferences());
      await h.read(notificationPreferencesControllerProvider.notifier).retry();
      expect(
        h.read(notificationPreferencesControllerProvider).preferences,
        isA<Loaded<List<NotificationPreference>>>(),
      );
    });
  });
}
