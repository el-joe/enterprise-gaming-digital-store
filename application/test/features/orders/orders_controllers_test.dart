import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/deep_links.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/link_inbox.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/cart/cart_providers.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/features/orders/presentation/controllers/guest_order_controllers.dart';
import 'package:application/features/orders/presentation/controllers/orders_controllers.dart';
import 'package:application/features/payments/payment_flow.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/pump_app.dart';

Future<void> settle() async {
  for (var i = 0; i < 100; i++) {
    await Future<void>.delayed(Duration.zero);
  }
}

OrderSummary numbered(int n) => fixtureOrderSummary(
  id: '01JF00000000000000000${n.toString().padLeft(5, '0')}',
  number: 'BX-$n',
);

const signedLink =
    'https://boxcards.test/api/v1/checkout/guest/orders/$orderId/access?expires=1&signature=abc';

void main() {
  TestHarness harness({bool signedIn = true}) {
    final h = buildHarness(signedIn: signedIn);
    addTearDown(h.dispose);
    return h;
  }

  group('order list', () {
    test('pages through meta: appends, stops at the last page', () async {
      final h = harness();
      h.orders.pages = {
        1: Ok(pageOf([for (var i = 1; i <= 20; i++) numbered(i)], lastPage: 2)),
        2: Ok(pageOf([numbered(21)], page: 2, lastPage: 2)),
      };
      final sub = h.container.listen(ordersListControllerProvider, (_, _) {});
      addTearDown(sub.close);
      await settle();
      final c = h.read(ordersListControllerProvider.notifier);
      expect(h.read(ordersListControllerProvider).orders, hasLength(20));
      expect(h.read(ordersListControllerProvider).hasMore, isTrue);

      await c.loadMore();
      expect(h.read(ordersListControllerProvider).orders, hasLength(21));
      expect(h.read(ordersListControllerProvider).hasMore, isFalse);
      await c.loadMore();
      expect(h.orders.queries.map((q) => q.page), [1, 2]);
    });

    test(
      'a failed next page keeps the list and is not retried by itself',
      () async {
        final h = harness();
        h.orders.pages = {
          1: Ok(pageOf([numbered(1)], lastPage: 3)),
          2: const Err(NetworkFailure()),
        };
        final sub = h.container.listen(ordersListControllerProvider, (_, _) {});
        addTearDown(sub.close);
        await settle();
        await h.read(ordersListControllerProvider.notifier).loadMore();
        final state = h.read(ordersListControllerProvider);
        expect(state.orders, hasLength(1));
        expect(state.moreFailure, isA<NetworkFailure>());
        expect(h.orders.queries, hasLength(2));
      },
    );

    test('a status chip restarts at page 1 with the filter', () async {
      final h = harness();
      final sub = h.container.listen(ordersListControllerProvider, (_, _) {});
      addTearDown(sub.close);
      await settle();
      await h
          .read(ordersListControllerProvider.notifier)
          .selectStatus('refunded');
      expect(h.orders.queries.last.status, 'refunded');
      expect(h.orders.queries.last.page, 1);
      expect(h.read(ordersListControllerProvider).isEmpty, isFalse);
    });

    test('first page failure, offline, then retry', () async {
      final h = harness();
      h.orders.pages = {1: const Err(NetworkFailure())};
      final sub = h.container.listen(ordersListControllerProvider, (_, _) {});
      addTearDown(sub.close);
      await settle();
      expect(
        h.read(ordersListControllerProvider).content,
        isA<Failed<List<OrderSummary>>>(),
      );
      h.orders.pages = {
        1: Ok(pageOf([numbered(1)])),
      };
      await h.read(ordersListControllerProvider.notifier).retry();
      expect(h.read(ordersListControllerProvider).orders, hasLength(1));
    });

    test(
      'signed out shows no list and loads nothing; a placement reloads',
      () async {
        final out = harness(signedIn: false);
        final sub = out.container.listen(
          ordersListControllerProvider,
          (_, _) {},
        );
        addTearDown(sub.close);
        await settle();
        expect(out.read(ordersListControllerProvider).signedIn, isFalse);
        expect(out.orders.queries, isEmpty);

        final h = harness();
        final sub2 = h.container.listen(
          ordersListControllerProvider,
          (_, _) {},
        );
        addTearDown(sub2.close);
        await settle();
        h.read(cartRevisionProvider.notifier).bump();
        await settle();
        expect(h.orders.queries, hasLength(2));
      },
    );
  });

  group('order detail', () {
    test('retry payment is offered for an unpaid order whose attempt ended, '
        'and goes through the M-A13 flow', () async {
      final h = harness();
      final base = fixtureOrderDetail(payments: [attempt(1, 'failed')]);
      h.orders.detailResult = Ok(
        base.copyWith(
          summary: base.summary.copyWith(
            status: 'pending_payment',
            paymentStatus: 'unpaid',
          ),
        ),
      );
      final provider = orderDetailControllerProvider(orderId);
      final sub = h.container.listen(provider, (_, _) {});
      addTearDown(sub.close);
      final flowSub = h.container.listen(paymentFlowProvider, (_, _) {});
      addTearDown(flowSub.close);
      await settle();
      final detail = h.read(provider).valueOrNull!;
      expect(canRetryPayment(detail), isTrue);
      expect(canRequestRefund(detail), isFalse);

      h.read(provider.notifier).retryPayment();
      await settle();
      expect(h.payments.calls.first, 'retry:$orderId');
      expect(h.read(paymentFlowProvider).orderPublicId, orderId);
    });

    test('a paid order offers a refund request, never a retry', () {
      final detail = fixtureOrderDetail();
      expect(canRetryPayment(detail), isFalse);
      expect(canRequestRefund(detail), isTrue);
      expect(
        canRequestRefund(detail.copyWith(refundState: 'pending')),
        isFalse,
      );
    });

    test('404 stays a failure', () async {
      final h = harness();
      h.orders.detailResult = const Err(NotFoundFailure());
      final provider = orderDetailControllerProvider(orderId);
      final sub = h.container.listen(provider, (_, _) {});
      addTearDown(sub.close);
      await settle();
      expect(h.read(provider), isA<Failed<OrderDetail>>());
    });
  });

  group('guest order access', () {
    TestHarness guest() {
      final h = harness(signedIn: false);
      return h;
    }

    test('link -> email -> code -> order', () async {
      final h = guest();
      h.read(guestLinkInboxProvider).put(orderId, Uri.parse(signedLink));
      final provider = guestOrderControllerProvider(orderId);
      final sub = h.container.listen(provider, (_, _) {});
      addTearDown(sub.close);
      await settle();
      expect(h.read(provider).step, GuestStep.email);
      expect(
        h.read(guestLinkInboxProvider).peek(orderId),
        isNull,
        reason: 'taken once',
      );

      final c = h.read(provider.notifier);
      await c.sendCode(' sara@example.test ');
      expect(h.read(provider).step, GuestStep.code);
      expect(h.read(provider).resendAt, h.now.value.add(guestOtpResendAfter));

      await c.resendCode();
      expect(
        h.checkout.guestCalls.where((call) => call.startsWith('send:')),
        hasLength(1),
        reason: 'resend waits for the cooldown',
      );

      await c.verifyCode('123456');
      await settle();
      expect(h.read(provider).step, GuestStep.order);
      expect(h.checkout.guestCalls, [
        'link:/api/v1/checkout/guest/orders/$orderId/access',
        'send:sara@example.test',
        'verify:123456',
      ]);
      expect(h.orders.guestDetails, [orderId]);
    });

    test('otp_invalid stays on the code step with the server text', () async {
      final h = guest();
      h.checkout.verifyOtpResult = const Err(
        DomainFailure('otp_invalid', message: 'The code is not valid.'),
      );
      h.read(guestLinkInboxProvider).put(orderId, Uri.parse(signedLink));
      final provider = guestOrderControllerProvider(orderId);
      final sub = h.container.listen(provider, (_, _) {});
      addTearDown(sub.close);
      await settle();
      final c = h.read(provider.notifier);
      await c.sendCode('sara@example.test');
      await c.verifyCode('000000');
      expect(h.read(provider).step, GuestStep.code);
      expect(h.read(provider).codeError, 'The code is not valid.');
    });

    test('an expired link is 11.5', () async {
      final h = guest();
      h.checkout.linkResult = const Err(ForbiddenFailure('forbidden'));
      h.read(guestLinkInboxProvider).put(orderId, Uri.parse(signedLink));
      final provider = guestOrderControllerProvider(orderId);
      final sub = h.container.listen(provider, (_, _) {});
      addTearDown(sub.close);
      await settle();
      expect(h.read(provider).step, GuestStep.linkExpired);
    });

    test('a verified link with the OTP already done opens the order', () async {
      final h = guest();
      h.checkout.linkResult = const Ok(
        GuestLinkAccess(orderPublicId: orderId, otpRequired: false),
      );
      h.read(guestLinkInboxProvider).put(orderId, Uri.parse(signedLink));
      final provider = guestOrderControllerProvider(orderId);
      final sub = h.container.listen(provider, (_, _) {});
      addTearDown(sub.close);
      await settle();
      expect(h.read(provider).step, GuestStep.order);
    });

    test(
      'without a link, a stored token is tried; none means "open the email"',
      () async {
        final h = guest();
        h.orders.guestResult = const Err(NotFoundFailure());
        final provider = guestOrderControllerProvider(orderId);
        final sub = h.container.listen(provider, (_, _) {});
        addTearDown(sub.close);
        await settle();
        expect(h.read(provider).step, GuestStep.needLink);
      },
    );

    test('the paste page accepts only a signed guest link on the store host', () {
      final h = guest();
      final entry = h.read(guestLinkEntryProvider.notifier);
      expect(
        entry.submit(
          'https://evil.test/api/v1/checkout/guest/orders/$orderId/access?expires=1&signature=abc',
        ),
        isNull,
      );
      expect(h.read(guestLinkEntryProvider).invalid, isTrue);
      expect(entry.submit('BX-1004'), isNull);
      expect(entry.submit(signedLink), orderId);
      expect(
        h.read(guestLinkInboxProvider).peek(orderId),
        Uri.parse(signedLink),
      );
    });
  });

  group('app links (M-A14)', () {
    test('the parser knows the customer and guest delivery links', () {
      expect(
        parseDeepLink(
          Uri.parse('https://boxcards.test/account/orders/$orderId'),
          appLinkHost: 'boxcards.test',
        ),
        const AccountOrderLink(orderId),
      );
      expect(
        parseDeepLink(
          Uri.parse('https://boxcards.test/ar/account/orders/$orderId'),
          appLinkHost: 'boxcards.test',
        ),
        const AccountOrderLink(orderId),
      );
      expect(
        parseDeepLink(Uri.parse(signedLink), appLinkHost: 'boxcards.test'),
        isA<GuestOrderAccessLink>(),
      );
    });

    testWidgets('a customer order link opens the order detail', (tester) async {
      final h = await pumpHarness(tester, signedIn: true);
      h.links.open('https://boxcards.test/account/orders/$orderId');
      await tester.pumpAndSettle();
      final location = h
          .read(appRouterProvider)
          .routerDelegate
          .currentConfiguration
          .uri
          .path;
      expect(location, Routes.orderLocation(orderId));
      expect(h.orders.details, contains(orderId));
    });

    testWidgets('signed out, the order link asks to sign in first', (
      tester,
    ) async {
      final h = await pumpHarness(tester);
      h.links.open('https://boxcards.test/account/orders/$orderId');
      await tester.pumpAndSettle();
      final uri = h
          .read(appRouterProvider)
          .routerDelegate
          .currentConfiguration
          .uri;
      expect(uri.path, Routes.login.path);
      expect(
        uri.queryParameters[Routes.fromParameter],
        Routes.orderLocation(orderId),
      );
      expect(h.read(sessionProvider), SessionStatus.signedOut);
    });

    testWidgets('a guest delivery link opens guest access with the link', (
      tester,
    ) async {
      final h = await pumpHarness(tester);
      h.links.open(signedLink);
      await tester.pumpAndSettle();
      expect(
        h.read(appRouterProvider).routerDelegate.currentConfiguration.uri.path,
        Routes.guestOrderLocation(orderId),
      );
      expect(h.checkout.guestCalls.first, startsWith('link:'));
    });
  });
}
