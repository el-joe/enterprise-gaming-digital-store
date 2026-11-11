import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/launch/presentation/controllers/startup_controller.dart';
import 'package:application/features/payments/domain/usecases/resolve_payment.dart';
import 'package:application/features/payments/payment_flow.dart';
import 'package:application/features/product/presentation/pages/product_page.dart';
import 'package:application/shared/domain/entities/payment_next.dart';
import 'package:flutter_riverpod/misc.dart' show ProviderListenable;
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/pump_app.dart';

Future<void> settle() async {
  for (var i = 0; i < 300; i++) {
    await Future<void>.delayed(Duration.zero);
  }
}

void keep<T>(TestHarness h, ProviderListenable<T> provider) {
  final sub = h.container.listen(provider, (_, _) {});
  addTearDown(sub.close);
}

/// The pending checkout of an order placed before a restart.
String pendingFor(String order) =>
    '{"idempotency_key":"key-1","body_hash":"0123456789abcdef","order_public_id":"$order"}';

void main() {
  TestHarness flowHarness({
    bool signedIn = true,
    Map<String, String> secureData = const {},
  }) {
    final h = buildHarness(signedIn: signedIn, secureData: secureData);
    addTearDown(h.dispose);
    keep(h, paymentFlowProvider);
    return h;
  }

  PaymentFlowState stateOf(TestHarness h) => h.read(paymentFlowProvider);
  PaymentFlow flowOf(TestHarness h) => h.read(paymentFlowProvider.notifier);

  group('payment flow', () {
    test(
      'wallet: no provider page; paid only once the status says so',
      () async {
        final h = flowHarness(
          secureData: {SecureStore.pendingCheckoutKey: pendingFor(orderId)},
        );
        h.payments.statusResults = [Ok(paidStatus)];
        await flowOf(h).start(orderPublicId: orderId, next: walletNext);
        await settle();
        expect(h.native.opened, isEmpty);
        // Paid at placement from the balance: ResolvePayment reads the status directly.
        expect(h.payments.calls, ['status:$orderId']);
        expect(stateOf(h).phase, PaymentPhase.paid);
        // A final answer clears the pending checkout.
        expect(h.secure.containsKey(SecureStore.pendingCheckoutKey), isFalse);
      },
    );

    test(
      'PayPal opens the approve page and waits; nothing is checked yet',
      () async {
        final h = flowHarness();
        await flowOf(h).start(orderPublicId: orderId, next: paypalNext);
        expect(h.native.opened.single, (paypalNext as PayPalNext).approveUrl);
        expect(stateOf(h).phase, PaymentPhase.awaitingProvider);
        expect(stateOf(h).providerUrl, isNotNull);
        expect(h.payments.calls, isEmpty);
      },
    );

    test(
      'a page that cannot be opened is reported and can be reopened',
      () async {
        final h = flowHarness();
        h.native.opens = false;
        await flowOf(h).start(orderPublicId: orderId, next: redirectNext);
        expect(stateOf(h).openFailed, isTrue);
        h.native.opens = true;
        await flowOf(h).reopenProvider();
        expect(stateOf(h).openFailed, isFalse);
        expect(h.native.opened, hasLength(2));
      },
    );

    test(
      'the PayPal return link captures on the server and paid comes from it',
      () async {
        final h = flowHarness();
        h.payments.captureResults = [Ok(paidStatus)];
        await flowOf(h).start(orderPublicId: orderId, next: paypalNext);
        await flowOf(h).handleReturn(orderId, cancelled: false);
        await settle();
        expect(h.payments.calls.first, 'capture:$orderId');
        expect(stateOf(h).phase, PaymentPhase.paid);
      },
    );

    test(
      'a return link never marks paid by itself (server says unpaid)',
      () async {
        final h = flowHarness();
        h.payments
          ..verifyResults = [Ok(orderStatus())]
          ..statusResults = [Ok(orderStatus())];
        h.orders.detailResult = Ok(
          fixtureOrderDetail(payments: [attempt(1, 'processing')]),
        );
        await flowOf(h).start(orderPublicId: orderId, next: redirectNext);
        await flowOf(h).handleReturn(orderId, cancelled: false);
        await settle();
        expect(h.payments.calls.first, 'verify:$orderId');
        expect(stateOf(h).phase, isNot(PaymentPhase.paid));
        expect(stateOf(h).phase, PaymentPhase.processing);
      },
    );

    test('polling: 2 s, 3 s, 5 s then every 10 s, "still processing" after 2 minutes', () async {
      final h = flowHarness();
      final start = h.now.value;
      h.payments
        ..verifyResults = [
          Ok(orderStatus(status: 'payment_processing', payment: 'processing')),
        ]
        ..statusResults = [
          Ok(orderStatus(status: 'payment_processing', payment: 'processing')),
        ];
      await flowOf(h).resume(orderId);
      await settle();
      expect(stateOf(h).phase, PaymentPhase.processing);
      final polls = h.payments.count('status');
      // 2 + 3 + 5 = 10 s, then 10 s steps while within 120 s: 11 more.
      expect(polls, 3 + (120 - 10) ~/ 10);
      expect(h.now.value.difference(start), const Duration(seconds: 120));
      expect(stateOf(h).lastCheckedAt, h.now.value);
      expect(ResolvePayment.budget, const Duration(minutes: 2));
    });

    test('polling ends early on paid', () async {
      final h = flowHarness();
      h.payments
        ..verifyResults = [Ok(orderStatus())]
        ..statusResults = [
          Ok(orderStatus()),
          Ok(orderStatus()),
          Ok(paidStatus),
        ];
      await flowOf(h).resume(orderId);
      await settle();
      expect(h.payments.count('status'), 3);
      expect(stateOf(h).phase, PaymentPhase.paid);
    });

    test('a failed latest attempt after polling offers retry', () async {
      final h = flowHarness();
      h.payments.statusResults = [Ok(orderStatus())];
      h.orders.detailResult = Ok(
        fixtureOrderDetail(payments: [attempt(2, 'failed')]),
      );
      await flowOf(h).resume(orderId);
      await settle();
      expect(stateOf(h).phase, PaymentPhase.failed);
      expect(stateOf(h).retryable, isTrue);
      expect(stateOf(h).attempt, 2);
    });

    test(
      'final answers: cancelled order is expired, failed order is failed',
      () async {
        final h = flowHarness();
        h.payments.verifyResults = [
          Ok(orderStatus(status: 'cancelled', isFinal: true)),
        ];
        await flowOf(h).resume(orderId);
        await settle();
        expect(stateOf(h).phase, PaymentPhase.expired);

        final f = flowHarness();
        f.payments.verifyResults = [
          Ok(orderStatus(status: 'failed', isFinal: true)),
        ];
        await flowOf(f).resume(orderId);
        await settle();
        expect(stateOf(f).phase, PaymentPhase.failed);
        expect(stateOf(f).retryable, isFalse);
      },
    );

    test('the cancel link reads the status once and offers retry', () async {
      final h = flowHarness();
      await flowOf(h).start(orderPublicId: orderId, next: paypalNext);
      await flowOf(h).handleReturn(orderId, cancelled: true);
      await settle();
      expect(h.payments.calls, ['status:$orderId']);
      expect(stateOf(h).phase, PaymentPhase.failed);
      expect(stateOf(h).cancelledByCustomer, isTrue);
      expect(stateOf(h).retryable, isTrue);
    });

    test('retry payment starts a new attempt and opens its page', () async {
      final h = flowHarness();
      await flowOf(h).handleReturn(orderId, cancelled: true);
      await settle();
      h.payments.retryResults = [Ok(redirectNext)];
      await flowOf(h).retryPayment();
      expect(h.payments.calls.last, 'retry:$orderId');
      expect(stateOf(h).phase, PaymentPhase.awaitingProvider);
      expect(
        h.native.opened.single,
        (redirectNext as RedirectNext).redirectUrl,
      );
    });

    test(
      'retry: order_expired is expired, attempts exhausted is final',
      () async {
        final h = flowHarness();
        await flowOf(h).handleReturn(orderId, cancelled: true);
        await settle();
        h.payments.retryResults = [
          const Err(ConflictFailure('payment_attempts_exhausted')),
        ];
        await flowOf(h).retryPayment();
        expect(stateOf(h).phase, PaymentPhase.failed);
        expect(stateOf(h).attemptsExhausted, isTrue);
        expect(stateOf(h).retryable, isFalse);

        final e = flowHarness();
        await flowOf(e).handleReturn(orderId, cancelled: true);
        await settle();
        e.payments.retryResults = [const Err(ConflictFailure('order_expired'))];
        await flowOf(e).retryPayment();
        await settle();
        expect(stateOf(e).phase, PaymentPhase.expired);
      },
    );

    test('Stripe (no native SDK in this app) is not launched', () async {
      final h = flowHarness();
      await flowOf(h).start(
        orderPublicId: orderId,
        next: const PaymentNext.stripe(
          paymentPublicId: 'p',
          gateway: 'stripe',
          clientSecret: 'fixture-client-secret',
        ),
      );
      expect(stateOf(h).phase, PaymentPhase.unsupported);
      expect(h.native.opened, isEmpty);
    });

    test('closing a result forgets the pending checkout', () async {
      final h = flowHarness(
        secureData: {SecureStore.pendingCheckoutKey: pendingFor(orderId)},
      );
      await flowOf(h).handleReturn(orderId, cancelled: true);
      await settle();
      expect(h.secure.containsKey(SecureStore.pendingCheckoutKey), isTrue);
      await flowOf(h).close();
      expect(h.secure.containsKey(SecureStore.pendingCheckoutKey), isFalse);
      expect(stateOf(h).orderPublicId, isNull);
    });

    test(
      'a transport failure while confirming keeps the screen and reports it',
      () async {
        final h = flowHarness();
        h.payments.verifyResults = [const Err(NotFoundFailure())];
        await flowOf(h).start(orderPublicId: orderId, next: paypalNext);
        h.payments.captureResults = [const Err(NotFoundFailure())];
        await flowOf(h).checkStatus();
        await settle();
        expect(stateOf(h).phase, PaymentPhase.awaitingProvider);
        expect(stateOf(h).failure, isA<NotFoundFailure>());
      },
    );
  });

  group('resume after a restart', () {
    test(
      'the launch resumes the pending order at its payment screen',
      () async {
        final h = flowHarness(
          secureData: {SecureStore.pendingCheckoutKey: pendingFor(orderId)},
        );
        h.payments.verifyResults = [Ok(paidStatus)];
        keep(h, startupControllerProvider);
        await h.read(startupControllerProvider.notifier).run();
        await settle();
        final ready = h.read(startupControllerProvider) as StartupReady;
        expect(ready.next, Routes.paymentLocation(orderId));
        expect(h.payments.calls.first, 'verify:$orderId');
        expect(stateOf(h).phase, PaymentPhase.paid);
      },
    );

    test('signed out or nothing placed: no resume', () async {
      final out = flowHarness(
        signedIn: false,
        secureData: {SecureStore.pendingCheckoutKey: pendingFor(orderId)},
      );
      expect(
        await out.read(paymentLaunchProvider.notifier).resumeOrder(),
        isNull,
      );
      final none = flowHarness();
      expect(
        await none.read(paymentLaunchProvider.notifier).resumeOrder(),
        isNull,
      );
      expect(none.payments.calls, isEmpty);
    });
  });

  group('app links', () {
    String path(TestHarness h) =>
        h.read(appRouterProvider).routerDelegate.currentConfiguration.uri.path;

    testWidgets(
      'a payment return link verifies with the server and opens the screen',
      (tester) async {
        final h = await pumpHarness(tester, signedIn: true);
        expect(path(h), Routes.home.path);
        h.payments
          ..verifyResults = [Ok(orderStatus())]
          ..statusResults = [Ok(orderStatus())];
        h.links.open(
          'https://boxcards.test/app/payments/return/$orderId?attempt=01JF00000000000000000000P1&success=true',
        );
        await tester.pumpAndSettle();
        expect(path(h), Routes.paymentLocation(orderId));
        expect(h.payments.calls.first, 'verify:$orderId');
        // `success=true` in the query proves nothing.
        expect(h.read(paymentFlowProvider).phase, isNot(PaymentPhase.paid));
      },
    );

    testWidgets('a link that arrives during the launch check waits for it', (
      tester,
    ) async {
      final h = await pumpHarness(
        tester,
        settle: false,
        arrange: (h) => h.appConfig.gate = Completer<void>(),
      );
      await tester.pump();
      expect(path(h), Routes.splash.path);
      h.links.open('https://boxcards.test/products/orbit-card');
      await tester.pump();
      expect(path(h), Routes.splash.path);
      h.appConfig.gate!.complete();
      await tester.pumpAndSettle();
      expect(find.byType(ProductPage), findsOneWidget);
    });

    testWidgets('another host or scheme is ignored', (tester) async {
      final h = await pumpHarness(tester, signedIn: true);
      h.links
        ..open('https://evil.example/app/payments/return/$orderId')
        ..open('http://boxcards.test/app/payments/return/$orderId');
      await tester.pumpAndSettle();
      expect(path(h), Routes.home.path);
      expect(h.payments.calls, isEmpty);
    });

    testWidgets('the cancel link is handled as a cancel', (tester) async {
      final h = await pumpHarness(tester, signedIn: true);
      h.links.open('https://boxcards.test/app/payments/cancel/$orderId');
      await tester.pumpAndSettle();
      expect(h.payments.calls, ['status:$orderId']);
      expect(h.read(paymentFlowProvider).cancelledByCustomer, isTrue);
    });

    testWidgets(
      'signed out, a return link asks to sign in and verifies nothing',
      (tester) async {
        final h = await pumpHarness(tester);
        h.links.open('https://boxcards.test/app/payments/return/$orderId');
        await tester.pumpAndSettle();
        expect(path(h), Routes.login.path);
        expect(h.payments.calls, isEmpty);
      },
    );
  });
}
