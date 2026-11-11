import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/features/product/domain/entities/product.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/catalog_fakes.dart';
import '../helpers/product_fakes.dart';
import '../helpers/pump_app.dart';

/// Key M-A12 states in LTR (en) and RTL (ar), compared with the approved M-A8 frames (05.1 to
/// 05.6 and the 19.2 Android 360x800 check) by review. Images never load here (the fake loader
/// keeps them pending), so the art shows its plain-text tile with the empty Logo Slot (dev
/// flavor), as drawn.
void main() {
  setUpAll(loadAppFonts);
  const slug = 'orbit-card';

  for (final lang in ['en', 'ar']) {
    final dir = lang == 'ar' ? 'rtl' : 'ltr';
    final l10n = l10nFor(lang);

    Future<void> snap(WidgetTester tester, String name) async {
      await precacheImages(tester);
      await expectLater(
        find.byType(MaterialApp),
        matchesGoldenFile('m_a12/${name}_$dir.png'),
      );
    }

    Future<TestHarness> open(
      WidgetTester tester, {
      void Function(TestHarness h)? arrange,
      Size size = const Size(390, 844),
      bool settle = true,
      String location = '/products/$slug',
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        size: size,
        arrange: (h) {
          h.home.results = [Ok(fixtureHome(lang))];
          h.product
            ..onDetail = ((_) => Ok(fixtureProduct(lang)))
            ..onVariants = ((_) => Ok(_variants(lang)))
            ..onReviews = ((_) => Ok(fixtureReviews(lang)));
          arrange?.call(h);
        },
      );
      unawaited(h.read(appRouterProvider).push(location));
      if (settle) {
        await tester.pumpAndSettle();
      } else {
        await tester.pump();
        await tester.pump(const Duration(milliseconds: 400));
      }
      return h;
    }

    testWidgets('05.1 product ($dir)', (tester) async {
      await open(tester);
      await snap(tester, 'product');
    });

    testWidgets('05.2 details and reviews ($dir)', (tester) async {
      await open(tester);
      await tester.drag(find.byType(Scrollable).first, const Offset(0, -560));
      await tester.pumpAndSettle();
      await snap(tester, 'product_details');
    });

    testWidgets('05.3 price may vary and field errors ($dir)', (tester) async {
      await open(
        tester,
        arrange: (h) {
          h.product.onVariants = (_) => Ok(
            ProductVariants(
              variants: [
                for (final v in _variants(lang).variants)
                  v.copyWith(priceMayVary: true),
              ],
              displayCurrency: 'USD',
            ),
          );
          h.cart.onAdd = (_) => Err(
            ValidationFailure({
              'delivery_values.player_id': [
                if (lang == 'ar')
                  'تنسيق معرف اللاعب غير صحيح.'
                else
                  'Player ID has an invalid format.',
              ],
            }),
          );
        },
      );
      await tester.enterText(find.byType(TextField), '48-17');
      await tester.tap(find.text(l10n.addToCart));
      await tester.pumpAndSettle();
      FocusManager.instance.primaryFocus?.unfocus();
      await tester.pumpAndSettle();
      await snap(tester, 'product_errors');
    });

    testWidgets('05.3 nothing can be bought ($dir)', (tester) async {
      await open(
        tester,
        arrange: (h) => h.product.onVariants = (_) => Ok(
          ProductVariants(
            variants: [
              for (final v in _variants(lang).variants)
                v.copyWith(availability: 'out_of_stock'),
            ],
            displayCurrency: 'USD',
          ),
        ),
      );
      await snap(tester, 'product_unavailable');
    });

    testWidgets('05.4 added to cart ($dir)', (tester) async {
      await open(tester);
      await tester.enterText(find.byType(TextField), '4817 2290 31');
      FocusManager.instance.primaryFocus?.unfocus();
      await tester.tap(find.text(l10n.addToCart));
      await tester.pumpAndSettle();
      await snap(tester, 'product_added');
    });

    testWidgets('05.5 loading ($dir)', (tester) async {
      final gate = Completer<void>();
      await open(tester, settle: false, arrange: (h) => h.product.gate = gate);
      await snap(tester, 'product_loading');
      gate.complete();
      await tester.pumpAndSettle();
    });

    testWidgets('05.6 not found ($dir)', (tester) async {
      await open(
        tester,
        arrange: (h) =>
            h.product.onDetail = (_) => const Err(NotFoundFailure()),
      );
      await snap(tester, 'product_not_found');
    });

    testWidgets('product offline ($dir)', (tester) async {
      await open(
        tester,
        arrange: (h) => h.product.onDetail = (_) => const Err(NetworkFailure()),
      );
      await snap(tester, 'product_offline');
    });

    testWidgets('reviews list ($dir)', (tester) async {
      await open(
        tester,
        location: Routes.productReviewsLocation(slug),
        arrange: (h) => h.product.onReviews = (_) => Ok(
          fixtureReviews(lang).copyWith(
            page: const PageInfo(
              currentPage: 1,
              perPage: 10,
              total: 2,
              lastPage: 1,
            ),
          ),
        ),
      );
      await snap(tester, 'product_reviews');
    });

    testWidgets('bottom nav cart count ($dir)', (tester) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: true,
        arrange: (h) {
          h.home.results = [Ok(fixtureHome(lang))];
          h.cart.getResult = Ok(
            fixtureCart().copyWith(
              items: [fixtureCart().items.single.copyWith(quantity: 2)],
            ),
          );
        },
      );
      h.read(appRouterProvider).go(Routes.cart.path);
      await tester.pumpAndSettle();
      await snap(tester, 'cart_badge');
    });

    testWidgets('19.2 product at 360x800 ($dir)', (tester) async {
      await open(tester, size: const Size(360, 800));
      await snap(tester, 'product_360');
    });
  }
}

/// The fixture's offers plus a sold-out 50 in the US, as drawn in 05.1 (three amounts with one
/// sold out).
ProductVariants _variants(String lang) {
  final fixture = fixtureVariants(lang).variants;
  final us = fixture.first;
  return ProductVariants(
    variants: [
      us,
      us.copyWith(
        publicId: '01jf0000000000000000000003',
        sku: 'ORB-25-US',
        title: '25 USD',
        denominationValue: '25.000000',
        price: fixture.last.price,
      ),
      us.copyWith(
        publicId: '01jf0000000000000000000004',
        sku: 'ORB-50-US',
        title: '50 USD',
        denominationValue: '50.000000',
        availability: 'out_of_stock',
        purchasable: false,
        price: null,
      ),
      fixture.last,
    ],
    displayCurrency: 'USD',
  );
}
