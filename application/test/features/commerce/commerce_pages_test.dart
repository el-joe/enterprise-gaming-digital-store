import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/auth/presentation/pages/login_page.dart';
import 'package:application/features/cart/presentation/pages/cart_page.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/checkout/presentation/pages/checkout_page.dart';
import 'package:application/features/payments/payment_flow.dart';
import 'package:application/features/payments/presentation/pages/payment_page.dart';
import 'package:application/shared/domain/entities/payment_next.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/fakes.dart';
import '../../helpers/pump_app.dart';

/// The LRI/PDI isolates around amounts inside sentences.
String plain(String text) => text.replaceAll(RegExp('[\\u2066\\u2069]'), '');

/// Scrolls the page's list until [finder] is built and visible.
Future<void> reveal(WidgetTester tester, Finder finder) =>
    tester.scrollUntilVisible(
      finder,
      200,
      scrollable: find
          .descendant(
            of: find.byType(ListView),
            matching: find.byType(Scrollable),
          )
          .first,
    );

Finder textContaining(String part) => find.byWidgetPredicate(
  (widget) => widget is Text && plain(widget.data ?? '').contains(part),
);

void main() {
  for (final lang in ['en', 'ar']) {
    final l10n = l10nFor(lang);

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      bool signedIn = true,
      Size size = const Size(390, 844),
      bool settle = true,
      void Function(TestHarness h)? arrange,
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: signedIn,
        size: size,
        arrange: (h) {
          h.cart.getResult = Ok(cartWith());
          arrange?.call(h);
        },
      );
      h.read(appRouterProvider).go(location);
      if (settle) {
        await tester.pumpAndSettle();
      } else {
        await tester.pump();
      }
      return h;
    }

    void webMode(TestHarness h) {
      final config = fixtureAppConfig();
      h.appConfig.results
        ..clear()
        ..add(
          Ok(
            config.copyWith(
              features: config.features.copyWith(webCheckoutRedirect: true),
            ),
          ),
        );
    }

    group('cart ($lang)', () {
      testWidgets('06.1 lines, applied coupon and server totals', (
        tester,
      ) async {
        await at(
          tester,
          Routes.cart.path,
          arrange: (h) => h.cart.getResult = Ok(fixtureCouponCart()),
        );
        final cart = fixtureCouponCart();
        expect(find.byType(CartPage), findsOneWidget);
        expect(find.text(cart.items.first.productName), findsWidgets);
        expect(find.text(l10n.cartCouponApplied('SAVE5')), findsOneWidget);
        expect(find.text('USD20.00'), findsOneWidget);
        expect(find.text('USD15.00'), findsWidgets);
        expect(find.text('-USD5.00'), findsOneWidget);
        final total = tester.widget<Text>(find.text('USD15.00').last);
        expect(total.textDirection, TextDirection.ltr);
        expect(find.text(l10n.cartCheckout), findsOneWidget);
      });

      testWidgets('quantity and remove call the cart and update the count', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.cart.path,
          arrange: (h) =>
              h.cart.onUpdate = (_, quantity) =>
                  Ok(cartWith(quantity: quantity)),
        );
        await tester.tap(find.byTooltip(l10n.increaseQuantity));
        await tester.pumpAndSettle();
        expect(h.cart.updates.single.quantity, 2);
        expect(find.text(l10n.cartTitleCount(2)), findsOneWidget);
        h.cart.onRemove = (_) => Ok(cartWith().copyWith(items: const []));
        await tester.tap(
          find.byTooltip(
            l10n.cartRemoveItem(cartWith().items.single.productName),
          ),
        );
        await tester.pumpAndSettle();
        expect(find.text(l10n.cartEmptyTitle), findsOneWidget);
      });

      testWidgets('06.2 blocked line, refused coupon, checkout disabled', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.cart.path,
          arrange: (h) {
            h.cart.getResult = Ok(cartWith(blockReason: 'not_sold_in_country'));
            h.cart.onApplyCoupon = (_) => const Err(
              DomainFailure(
                'coupon_invalid',
                message: 'This coupon cannot be used.',
                errors: {
                  'code': ['This coupon is for first orders only.'],
                },
              ),
            );
          },
        );
        expect(find.text(l10n.blockNotSoldInCountry), findsOneWidget);
        expect(find.text(l10n.cartCheckoutBlocked), findsOneWidget);
        await tester.enterText(find.byType(TextField).first, 'FIRST50');
        await tester.tap(find.text(l10n.cartCouponApply));
        await tester.pumpAndSettle();
        expect(h.cart.coupons, ['FIRST50']);
        expect(
          find.text('This coupon is for first orders only.'),
          findsOneWidget,
        );
        await tester.tap(find.text(l10n.cartCheckout));
        await tester.pumpAndSettle();
        expect(find.byType(CheckoutPage), findsNothing);
      });

      testWidgets('06.3 empty cart browses the store', (tester) async {
        await at(
          tester,
          Routes.cart.path,
          arrange: (h) => h.cart.getResult = const Ok(null),
        );
        expect(find.text(l10n.cartEmptyTitle), findsOneWidget);
        await tester.tap(find.text(l10n.cartBrowseStore));
        await tester.pumpAndSettle();
        expect(find.text(l10n.categoriesTitle), findsWidgets);
      });

      testWidgets('06.4 loading skeleton', (tester) async {
        final h = await at(
          tester,
          Routes.cart.path,
          settle: false,
          arrange: (h) => h.cart.getGate = Completer<void>(),
        );
        await tester.pump();
        expect(find.byType(SkeletonBlock), findsWidgets);
        h.cart.getGate!.complete();
        await tester.pumpAndSettle();
      });

      testWidgets('06.5 error with request id, then retry', (tester) async {
        final h = await at(
          tester,
          Routes.cart.path,
          arrange: (h) => h.cart.getResult = const Err(
            ServerFailure(statusCode: 500, requestId: '01JREQ'),
          ),
        );
        expect(find.text(l10n.cartLoadFailedTitle), findsOneWidget);
        expect(textContaining('01JREQ'), findsOneWidget);
        h.cart.getResult = Ok(cartWith());
        await tester.tap(find.text(l10n.actionTryAgain));
        await tester.pumpAndSettle();
        expect(find.text(l10n.cartCheckout), findsOneWidget);
      });

      testWidgets('offline', (tester) async {
        await at(
          tester,
          Routes.cart.path,
          arrange: (h) => h.cart.getResult = const Err(NetworkFailure()),
        );
        expect(find.text(l10n.offlineBannerTitle), findsOneWidget);
      });

      testWidgets('a guest is asked to sign in and comes back to checkout', (
        tester,
      ) async {
        await at(
          tester,
          Routes.cart.path,
          signedIn: false,
          arrange: (h) =>
              h.secure[SecureStore.cartTokenKey] = 'fixture-cart-token',
        );
        expect(find.text(l10n.cartSignInHint), findsOneWidget);
        await tester.tap(find.text(l10n.cartCheckout));
        await tester.pumpAndSettle();
        expect(find.byType(LoginPage), findsOneWidget);
      });

      testWidgets('web checkout mode opens boxcards.shop (owner decision 3)', (
        tester,
      ) async {
        final h = await at(tester, Routes.cart.path, arrange: webMode);
        expect(find.text(l10n.cartCheckoutWeb), findsOneWidget);
        await tester.tap(find.text(l10n.cartCheckoutWeb));
        await tester.pumpAndSettle();
        expect(h.native.opened.single, fixtureAppConfig().links.webCheckout);
        expect(find.byType(CheckoutPage), findsNothing);
      });
    });

    group('checkout ($lang)', () {
      testWidgets(
        '07.2 country, options, charge notice and the charged amount',
        (tester) async {
          await at(tester, Routes.checkout.path);
          expect(find.byType(CheckoutPage), findsOneWidget);
          expect(find.text('United States'), findsOneWidget);
          expect(find.text('Paymob'), findsOneWidget);
          expect(find.text('PayPal'), findsOneWidget);
          expect(find.text(l10n.optionNotInApp), findsOneWidget);
          expect(
            find.text(l10n.checkoutChargeNoticeTitle('Paymob', 'EGP')),
            findsOneWidget,
          );
          // Display total and the exact charge (owner decision 1).
          await reveal(tester, find.byKey(const ValueKey('charged-line')));
          expect(find.text(r'$31.50'), findsWidgets);
          expect(
            textContaining(
              plain(l10n.checkoutChargedLine('E£1530.00', 'EGP', 'Paymob')),
            ),
            findsOneWidget,
          );
          expect(
            textContaining(plain(l10n.checkoutPay('E£1530.00'))),
            findsOneWidget,
          );
        },
      );

      testWidgets('pay with PayPal opens the provider and shows 07.6', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.checkout.path,
          arrange: (h) => h.checkout.placeResults.add(
            Ok(PlaceOrderResponseFake.withNext(paypalNext)),
          ),
        );
        await tester.tap(find.text('PayPal'));
        await tester.pumpAndSettle();
        expect(find.text(l10n.checkoutContinueTo('PayPal')), findsOneWidget);
        await tester.tap(find.text(l10n.checkoutContinueTo('PayPal')));
        await tester.pumpAndSettle();
        // Terms first.
        await reveal(tester, find.text(l10n.checkoutTermsRequired));
        expect(find.text(l10n.checkoutTermsRequired), findsOneWidget);
        expect(h.checkout.placed, isEmpty);
        await reveal(tester, find.text(l10n.checkoutAcceptTerms));
        await tester.tap(find.text(l10n.checkoutAcceptTerms));
        await tester.pumpAndSettle();
        await tester.tap(find.text(l10n.checkoutContinueTo('PayPal')));
        await tester.pumpAndSettle();
        expect(find.byType(PaymentPage), findsOneWidget);
        expect(find.text(l10n.paymentInProgressTitle), findsOneWidget);
        expect(find.text(l10n.paymentReturnTo('PayPal')), findsOneWidget);
        expect(find.text(l10n.paymentStatusPaid), findsNothing);
        expect(h.native.opened, hasLength(1));
      });

      testWidgets('07.3 unavailable options keep their reason', (tester) async {
        await at(
          tester,
          Routes.checkout.path,
          arrange: (h) => h.checkout.onOptions = (_) => Ok([
            option('paypal'),
            option(
              'wallet',
              available: false,
              notice: 'payment_currency_below_margin',
            ),
            option('stripe'),
          ]),
        );
        expect(find.text(l10n.optionBelowMargin), findsOneWidget);
        expect(find.text(l10n.optionNotInApp), findsOneWidget);
        expect(find.text(l10n.checkoutSomeUnavailableTitle), findsOneWidget);
      });

      testWidgets('07.4 country sheet: search and choose', (tester) async {
        final h = await at(tester, Routes.checkout.path);
        await tester.tap(find.text('United States'));
        await tester.pumpAndSettle();
        await tester.enterText(find.byType(TextField).last, 'egy');
        await tester.pumpAndSettle();
        expect(find.text('United States'), findsOneWidget);
        await tester.tap(find.text('Egypt').last);
        await tester.pumpAndSettle();
        expect(h.checkout.validated.last, 'EG');
      });

      testWidgets('07.5 price changed: both totals, back to the cart', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.checkout.path,
          arrange: (h) => h.checkout.placeResults.add(
            const Err(DomainFailure('price_changed')),
          ),
        );
        h.checkout.onValidate = (_) => Ok(cartWith(total: '32.10'));
        await reveal(tester, find.text(l10n.checkoutAcceptTerms));
        await tester.tap(find.text(l10n.checkoutAcceptTerms));
        await tester.pump();
        await tester.tap(textContaining(plain(l10n.checkoutPay('E£1530.00'))));
        await tester.pumpAndSettle();
        expect(find.text(l10n.checkoutPriceChangedTitle), findsOneWidget);
        expect(
          textContaining(
            plain(l10n.checkoutPriceChangedBody(r'$32.10', r'$31.50')),
          ),
          findsOneWidget,
        );
        await tester.tap(find.text(l10n.checkoutBackToCart));
        await tester.pumpAndSettle();
        expect(find.byType(CartPage), findsOneWidget);
        expect(find.text(l10n.cartPricesUpdatedTitle), findsOneWidget);
      });

      testWidgets('07.7 payment options failure and retry', (tester) async {
        final h = await at(
          tester,
          Routes.checkout.path,
          arrange: (h) =>
              h.checkout.onOptions = (_) =>
                  const Err(ServerFailure(statusCode: 503)),
        );
        expect(find.text(l10n.checkoutQuoteFailedTitle), findsOneWidget);
        h.checkout.onOptions = (_) => Ok([option('paypal')]);
        await tester.tap(find.text(l10n.actionTryAgain));
        await tester.pumpAndSettle();
        expect(find.text('PayPal'), findsOneWidget);
      });

      testWidgets('idempotency in progress is explained', (tester) async {
        await at(
          tester,
          Routes.checkout.path,
          arrange: (h) => h.checkout.placeResults.add(
            const Err(ConflictFailure('idempotency_in_progress')),
          ),
        );
        await reveal(tester, find.text(l10n.checkoutAcceptTerms));
        await tester.tap(find.text(l10n.checkoutAcceptTerms));
        await tester.pump();
        await tester.tap(textContaining(plain(l10n.checkoutPay('E£1530.00'))));
        await tester.pumpAndSettle();
        await reveal(tester, find.text(l10n.checkoutInProgressTitle));
        expect(find.text(l10n.checkoutInProgressTitle), findsOneWidget);
      });

      testWidgets('a guest opening checkout is sent to sign in', (
        tester,
      ) async {
        final h = await at(tester, Routes.checkout.path, signedIn: false);
        expect(find.byType(LoginPage), findsOneWidget);
        expect(h.checkout.validated, isEmpty);
      });

      testWidgets('web checkout mode shows the boxcards.shop state', (
        tester,
      ) async {
        final h = await at(tester, Routes.checkout.path, arrange: webMode);
        expect(find.text(l10n.checkoutWebTitle), findsOneWidget);
        await tester.tap(find.text(l10n.cartCheckoutWeb));
        await tester.pumpAndSettle();
        expect(h.native.opened, [fixtureAppConfig().links.webCheckout]);
        expect(h.checkout.validated, isEmpty);
      });
    });

    group('payment results ($lang)', () {
      Future<TestHarness> result(
        WidgetTester tester,
        void Function(TestHarness h) script, {
        bool cancel = false,
      }) async {
        final h = await at(tester, Routes.home.path);
        script(h);
        unawaited(
          h
              .read(paymentFlowProvider.notifier)
              .handleReturn(orderId, cancelled: cancel),
        );
        h.read(appRouterProvider).go(Routes.paymentLocation(orderId));
        await tester.pumpAndSettle();
        return h;
      }

      testWidgets('08.1 paid', (tester) async {
        await result(
          tester,
          (h) => h.payments.verifyResults = [Ok(paidStatus)],
        );
        expect(find.text(l10n.paymentPaidTitle), findsOneWidget);
        expect(find.text(l10n.paymentStatusPaid), findsOneWidget);
        expect(find.text('FIXTURE-0001'), findsOneWidget);
        await tester.tap(find.text(l10n.paymentKeepShopping));
        await tester.pumpAndSettle();
        expect(find.byType(PaymentPage), findsNothing);
      });

      testWidgets('08.2 still processing, check again', (tester) async {
        final h = await result(tester, (h) {
          h.payments.statusResults = [
            Ok(
              orderStatus(status: 'payment_processing', payment: 'processing'),
            ),
          ];
        });
        expect(find.text(l10n.paymentProcessingTitle), findsOneWidget);
        final before = h.payments.count('verify');
        await tester.tap(find.text(l10n.paymentCheckAgain));
        await tester.pumpAndSettle();
        expect(h.payments.count('verify'), before + 1);
      });

      testWidgets('08.3 failed (cancel link) and retry payment', (
        tester,
      ) async {
        final h = await result(tester, (h) {}, cancel: true);
        expect(find.text(l10n.paymentFailedTitle), findsOneWidget);
        expect(textContaining(l10n.paymentCancelledBody), findsOneWidget);
        h.payments.retryResults = [Ok(redirectNext)];
        await tester.tap(find.text(l10n.paymentTryAgain));
        await tester.pumpAndSettle();
        expect(h.payments.calls.last, 'retry:$orderId');
        expect(find.text(l10n.paymentInProgressTitle), findsOneWidget);
      });

      testWidgets('08.4 expired, back to the cart', (tester) async {
        await result(
          tester,
          (h) => h.payments.verifyResults = [
            Ok(orderStatus(status: 'cancelled', isFinal: true)),
          ],
        );
        expect(find.text(l10n.paymentExpiredTitle), findsOneWidget);
        expect(find.text(l10n.paymentStatusExpired), findsOneWidget);
        await tester.tap(find.text(l10n.paymentReturnToCart));
        await tester.pumpAndSettle();
        expect(find.byType(CartPage), findsOneWidget);
      });

      testWidgets('close forgets a final result and goes home', (tester) async {
        final h = await result(tester, (h) {
          h.secure[SecureStore.pendingCheckoutKey] =
              '{"idempotency_key":"key-1","body_hash":"0","order_public_id":"$orderId"}';
          h.payments.verifyResults = [Ok(paidStatus)];
        });
        await tester.tap(find.byTooltip(l10n.actionClose));
        await tester.pumpAndSettle();
        expect(find.byType(PaymentPage), findsNothing);
        expect(h.secure.containsKey(SecureStore.pendingCheckoutKey), isFalse);
      });
    });

    group('accessibility ($lang)', () {
      for (final location in [Routes.cart.path, Routes.checkout.path]) {
        testWidgets('$location meets tap-target and label guidelines', (
          tester,
        ) async {
          final handle = tester.ensureSemantics();
          await at(tester, location);
          await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
          await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
          handle.dispose();
        });
      }

      testWidgets('payment result meets tap-target and label guidelines', (
        tester,
      ) async {
        final handle = tester.ensureSemantics();
        final h = await at(tester, Routes.home.path);
        h.payments.verifyResults = [Ok(paidStatus)];
        unawaited(h.read(paymentFlowProvider.notifier).resume(orderId));
        h.read(appRouterProvider).go(Routes.paymentLocation(orderId));
        await tester.pumpAndSettle();
        await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
        await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
        handle.dispose();
      });
    });

    testWidgets('19.3 checkout at 360x800 has no overflow ($lang)', (
      tester,
    ) async {
      await at(tester, Routes.checkout.path, size: const Size(360, 800));
      expect(tester.takeException(), isNull);
      expect(find.byType(CheckoutPage), findsOneWidget);
    });
  }
}

/// Place-order answers for page tests.
abstract final class PlaceOrderResponseFake {
  static PlaceOrderResponse withNext(PaymentNext next) => PlaceOrderResponse(
    order: placed(),
    duplicate: false,
    replayed: false,
    next: next,
  );
}
