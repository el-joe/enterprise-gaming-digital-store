import 'package:application/core/network/result.dart';
import 'package:application/features/cart/presentation/pages/cart_page.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/checkout/presentation/pages/checkout_page.dart';
import 'package:application/features/payments/presentation/pages/payment_page.dart';
import 'package:application/features/product/presentation/pages/product_page.dart';
import 'package:application/shared/domain/entities/payment_next.dart';
import 'package:application/shared/widgets/product_tile.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../test/helpers/catalog_fakes.dart';
import '../../test/helpers/checkout_fakes.dart';
import '../../test/helpers/product_fakes.dart';
import '../../test/helpers/pump_app.dart';
import '../helpers/journey.dart';

/// Browse -> product -> add to cart -> cart -> checkout -> PayPal hand-off -> the provider's
/// return link -> paid result. Run in English and in Arabic (RTL).
void shoppingJourney(String lang) {
  final l10n = l10nFor(lang);

  testWidgets('browse, add to cart, check out, pay, paid result ($lang)', (
    tester,
  ) async {
    final h = await launchApp(
      tester,
      lang: lang,
      signedIn: true,
      arrange: (h) {
        h.home.results = [Ok(fixtureHome(lang))];
        h.product
          ..onDetail = ((_) => Ok(fixtureProduct(lang)))
          ..onVariants = ((_) => Ok(fixtureVariants(lang)))
          ..onReviews = ((_) => Ok(fixtureReviews(lang)));
        h.cart
          ..getResult = Ok(cartWith())
          ..onAdd = ((_) => Ok(cartWith()));
        h.checkout.placeResults.add(
          Ok(PlaceOrderResponseFake.withNext(paypalNext)),
        );
      },
    );

    // Browse: the first product of the home grid.
    final tile = find.byType(ProductTile).first;
    await scrollTo(tester, tile);
    await tester.tap(tile);
    await tester.pumpAndSettle();
    expect(find.byType(ProductPage), findsOneWidget);

    // Product: the delivery field, then add to cart (announced by the snackbar).
    await tester.enterText(find.byType(TextField).first, '4817 2290 31');
    await tester.pump();
    await tester.tap(find.text(l10n.addToCart));
    await tester.pumpAndSettle();
    expect(h.cart.adds, hasLength(1));
    expect(find.text(l10n.addedToCart), findsOneWidget);
    await tester.tap(find.text(l10n.viewCart));
    await tester.pumpAndSettle();
    expect(find.byType(CartPage), findsOneWidget);

    // Cart -> checkout.
    await tapButton(tester, l10n.cartCheckout);
    expect(find.byType(CheckoutPage), findsOneWidget);
    expect(find.text('PayPal'), findsOneWidget);

    // Checkout: PayPal, the terms, then the hand-off to the provider.
    await tester.tap(find.text('PayPal'));
    await tester.pumpAndSettle();
    await scrollTo(tester, find.text(l10n.checkoutAcceptTerms));
    await tester.tap(find.text(l10n.checkoutAcceptTerms));
    await tester.pumpAndSettle();
    await tester.tap(find.text(l10n.checkoutContinueTo('PayPal')));
    await tester.pumpAndSettle();
    expect(h.checkout.placed, hasLength(1));
    expect(find.byType(PaymentPage), findsOneWidget);
    expect(find.text(l10n.paymentInProgressTitle), findsOneWidget);
    expect(h.native.opened.single.host, 'www.sandbox.paypal.com');

    // The provider sends the customer back through the verified app link; the app asks the
    // server, which says paid.
    h.payments
      ..captureResults = [Ok(paidStatus)]
      ..verifyResults = [Ok(paidStatus)]
      ..statusResults = [Ok(paidStatus)];
    h.links.open('https://boxcards.test/app/payments/return/$orderId');
    await tester.pumpAndSettle();
    expect(find.text(l10n.paymentPaidTitle), findsOneWidget);
    expect(find.text(l10n.paymentStatusPaid), findsOneWidget);
  });
}

/// Place-order answers for the journey (the same shape as the widget tests use).
abstract final class PlaceOrderResponseFake {
  static PlaceOrderResponse withNext(PaymentNext next) => PlaceOrderResponse(
    order: placed(),
    duplicate: false,
    replayed: false,
    next: next,
  );
}
