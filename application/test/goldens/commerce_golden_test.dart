import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/features/checkout/presentation/controllers/checkout_controller.dart';
import 'package:application/features/payments/payment_flow.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/checkout_fakes.dart';
import '../helpers/fakes.dart';
import '../helpers/pump_app.dart';

/// Key M-A13 states in LTR (en) and RTL (ar), compared with the approved M-A8 frames (06.1 to
/// 06.5, 07.2 to 07.7, 08.1 to 08.4 and the 19.3 Android 360x800 checkout check) by review.
void main() {
  setUpAll(loadAppFonts);

  for (final lang in ['en', 'ar']) {
    final dir = lang == 'ar' ? 'rtl' : 'ltr';
    final l10n = l10nFor(lang);

    Future<void> snap(WidgetTester tester, String name) async {
      await precacheImages(tester);
      await expectLater(
        find.byType(MaterialApp),
        matchesGoldenFile('m_a13/${name}_$dir.png'),
      );
    }

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      void Function(TestHarness h)? arrange,
      Size size = const Size(390, 844),
      bool settle = true,
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: true,
        size: size,
        arrange: (h) {
          // Local wall-clock times, so "held until" and "last checked" do not depend on the
          // machine's time zone.
          h.now.value = DateTime(2026, 11, 11, 12);
          h.cart.getResult = Ok(fixtureCouponCart());
          arrange?.call(h);
        },
      );
      h.read(appRouterProvider).go(location);
      if (settle) {
        await tester.pumpAndSettle();
      } else {
        await tester.pump();
        await tester.pump(const Duration(milliseconds: 400));
      }
      return h;
    }

    Future<void> result(
      WidgetTester tester,
      String name,
      void Function(TestHarness h) script, {
      bool cancel = false,
    }) async {
      final h = await at(tester, Routes.home.path);
      script(h);
      unawaited(
        h
            .read(paymentFlowProvider.notifier)
            .start(
              orderPublicId: orderId,
              next: paypalNext,
              gatewayLabel: 'PayPal',
              orderNumber: 'BX-1001',
              paymentTotal: usd('31.50'),
              expiresAt: DateTime(2026, 11, 11, 12, 30),
            ),
      );
      await tester.pump();
      unawaited(
        h
            .read(paymentFlowProvider.notifier)
            .handleReturn(orderId, cancelled: cancel),
      );
      h.read(appRouterProvider).go(Routes.paymentLocation(orderId));
      await tester.pumpAndSettle();
      await snap(tester, name);
    }

    testWidgets('06.1 cart ($dir)', (tester) async {
      await at(tester, Routes.cart.path);
      await snap(tester, 'cart');
    });

    testWidgets('06.2 blocked line and coupon error ($dir)', (tester) async {
      await at(
        tester,
        Routes.cart.path,
        arrange: (h) {
          h.cart.getResult = Ok(cartWith(blockReason: 'not_sold_in_country'));
          h.cart.onApplyCoupon = (_) => const Err(
            DomainFailure(
              'coupon_invalid',
              errors: {
                'code': ['This coupon is for first orders only.'],
              },
            ),
          );
        },
      );
      await tester.enterText(find.byType(TextField).first, 'FIRST50');
      await tester.tap(find.text(l10n.cartCouponApply));
      await tester.pumpAndSettle();
      FocusManager.instance.primaryFocus?.unfocus();
      await tester.pumpAndSettle();
      await snap(tester, 'cart_blocked');
    });

    testWidgets('06.3 empty cart ($dir)', (tester) async {
      await at(
        tester,
        Routes.cart.path,
        arrange: (h) => h.cart.getResult = const Ok(null),
      );
      await snap(tester, 'cart_empty');
    });

    testWidgets('06.4 cart loading ($dir)', (tester) async {
      final h = await at(
        tester,
        Routes.cart.path,
        settle: false,
        arrange: (h) => h.cart.getGate = Completer<void>(),
      );
      await snap(tester, 'cart_loading');
      h.cart.getGate!.complete();
      await tester.pumpAndSettle();
    });

    testWidgets('06.5 cart error ($dir)', (tester) async {
      await at(
        tester,
        Routes.cart.path,
        arrange: (h) => h.cart.getResult = const Err(
          ServerFailure(
            statusCode: 500,
            requestId: '01JF00000000000000000000RQ',
          ),
        ),
      );
      await snap(tester, 'cart_error');
    });

    testWidgets('06.1 web checkout mode ($dir)', (tester) async {
      await at(
        tester,
        Routes.cart.path,
        arrange: (h) {
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
        },
      );
      await snap(tester, 'cart_web_checkout');
    });

    testWidgets('07.2 checkout ($dir)', (tester) async {
      await at(tester, Routes.checkout.path);
      await snap(tester, 'checkout');
    });

    testWidgets('07.2 checkout, summary ($dir)', (tester) async {
      await at(tester, Routes.checkout.path);
      await tester.drag(find.byType(Scrollable).first, const Offset(0, -600));
      await tester.pumpAndSettle();
      await snap(tester, 'checkout_summary');
    });

    testWidgets('07.3 payment notices ($dir)', (tester) async {
      await at(
        tester,
        Routes.checkout.path,
        arrange: (h) => h.checkout.onOptions = (_) => Ok([
          option('paypal', notice: 'charged_in_payment_currency'),
          option('paymob'),
          option(
            'wallet',
            available: false,
            notice: 'payment_currency_below_margin',
          ),
          option('stripe'),
        ]),
      );
      await snap(tester, 'checkout_notices');
    });

    testWidgets('07.4 country sheet ($dir)', (tester) async {
      await at(tester, Routes.checkout.path);
      await tester.tap(find.text('United States'));
      await tester.pumpAndSettle();
      await snap(tester, 'checkout_country');
    });

    testWidgets('07.5 price changed ($dir)', (tester) async {
      final h = await at(
        tester,
        Routes.checkout.path,
        arrange: (h) => h.checkout.placeResults.add(
          const Err(DomainFailure('price_changed')),
        ),
      );
      h.checkout.onValidate = (_) => Ok(cartWith(total: '32.10'));
      final notifier = h.read(checkoutControllerProvider.notifier)
        ..setAcceptTerms(accepted: true);
      await notifier.placeOrder();
      await tester.pumpAndSettle();
      await snap(tester, 'checkout_price_changed');
    });

    testWidgets('07.6 payment in progress ($dir)', (tester) async {
      final h = await at(tester, Routes.home.path);
      await h
          .read(paymentFlowProvider.notifier)
          .start(
            orderPublicId: orderId,
            next: paypalNext,
            gatewayLabel: 'PayPal',
            orderNumber: 'BX-1001',
            paymentTotal: usd('31.50'),
          );
      h.read(appRouterProvider).go(Routes.paymentLocation(orderId));
      await tester.pumpAndSettle();
      await snap(tester, 'payment_in_progress');
    });

    testWidgets('07.7 payment options error ($dir)', (tester) async {
      await at(
        tester,
        Routes.checkout.path,
        arrange: (h) => h.checkout.onOptions = (_) => const Err(
          ServerFailure(
            statusCode: 503,
            requestId: '01JF00000000000000000000RQ',
          ),
        ),
      );
      await snap(tester, 'checkout_error');
    });

    testWidgets('08.1 paid ($dir)', (tester) async {
      await result(
        tester,
        'result_paid',
        (h) => h.payments.captureResults = [Ok(paidStatus)],
      );
    });

    testWidgets('08.2 processing ($dir)', (tester) async {
      await result(
        tester,
        'result_processing',
        (h) => h.payments.statusResults = [
          Ok(orderStatus(status: 'payment_processing', payment: 'processing')),
        ],
      );
    });

    testWidgets('08.3 failed ($dir)', (tester) async {
      await result(
        tester,
        'result_failed',
        (h) => h.orders.detailResult = Ok(
          fixtureOrderDetail(payments: [attempt(2, 'cancelled')]),
        ),
        cancel: true,
      );
    });

    testWidgets('08.4 expired ($dir)', (tester) async {
      await result(
        tester,
        'result_expired',
        (h) => h.payments.captureResults = [
          Ok(orderStatus(status: 'cancelled', isFinal: true)),
        ],
      );
    });

    testWidgets('19.3 checkout at 360x800 ($dir)', (tester) async {
      await at(tester, Routes.checkout.path, size: const Size(360, 800));
      await snap(tester, 'checkout_360');
    });
  }
}
