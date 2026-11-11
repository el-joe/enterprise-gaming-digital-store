import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/cart/presentation/pages/cart_page.dart';
import 'package:application/features/home/presentation/pages/home_page.dart';
import 'package:application/features/product/domain/delivery_field_rules.dart';
import 'package:application/features/product/domain/entities/product.dart';
import 'package:application/features/product/presentation/pages/product_page.dart';
import 'package:application/features/product/presentation/pages/product_reviews_page.dart';
import 'package:application/features/product/presentation/widgets/product_widgets.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/catalog_fakes.dart';
import '../../helpers/product_fakes.dart';
import '../../helpers/pump_app.dart';

/// Widget tests of the M-A12 product page (05.1 to 05.6) and the reviews page in English (LTR)
/// and Arabic (RTL), on the fake repositories answering with the contract fixtures.
void main() {
  const slug = 'orbit-card';

  for (final lang in ['en', 'ar']) {
    final l10n = l10nFor(lang);
    final rtl = lang == 'ar';

    /// Opens the product from home (so back returns there), on the fixtures of [lang].
    Future<TestHarness> open(
      WidgetTester tester, {
      void Function(TestHarness h)? arrange,
      bool settle = true,
      Size size = const Size(390, 844),
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        size: size,
        arrange: (h) {
          h.home.results = [Ok(fixtureHome(lang))];
          h.product
            ..onDetail = ((_) => Ok(fixtureProduct(lang)))
            ..onVariants = ((_) => Ok(fixtureVariants(lang)))
            ..onReviews = ((_) => Ok(fixtureReviews(lang)));
          arrange?.call(h);
        },
      );
      unawaited(h.read(appRouterProvider).push(Routes.productLocation(slug)));
      if (settle) {
        await tester.pumpAndSettle();
      } else {
        await tester.pump();
      }
      return h;
    }

    Finder richText(String text) => find.text(text, findRichText: true);

    Future<void> scrollTo(WidgetTester tester, Finder finder) async {
      await tester.scrollUntilVisible(
        finder,
        200,
        scrollable: find.byType(Scrollable).first,
      );
      await tester.ensureVisible(finder);
      await tester.pumpAndSettle();
    }

    Future<void> enterPlayerId(WidgetTester tester, String value) async {
      await tester.enterText(find.byType(TextField).first, value);
      await tester.pump();
    }

    Future<void> tapAdd(WidgetTester tester) async {
      await tester.tap(find.text(l10n.addToCart));
      await tester.pumpAndSettle();
    }

    group('product page ($lang)', () {
      testWidgets(
        '05.1: art, title, region, amounts, field and server prices',
        (tester) async {
          final h = await open(tester);
          final product = fixtureProduct(lang);
          expect(find.byType(ProductPage), findsOneWidget);
          expect(h.product.detailCalls, [slug]);
          expect(h.product.variantCalls, [slug]);

          expect(find.text(product.card.name), findsOneWidget);
          expect(
            find.text(
              '${product.card.category!.name} · ${product.card.platform!.name}',
            ),
            findsOneWidget,
          );
          expect(find.text(l10n.deliveryTime(5)), findsOneWidget);
          expect(find.text('(${l10n.productReviewsCount(2)})'), findsOneWidget);

          // Region chips from the variants, named by the product's translated regions.
          expect(find.text(l10n.productRegion), findsOneWidget);
          for (final region in product.regions) {
            expect(find.text(region.name), findsOneWidget);
          }

          // The US amount only, with the server's formatted price as an LTR isolate.
          final us = fixtureVariants(lang).variants.first;
          expect(find.text(us.title), findsOneWidget);
          expect(
            find.text(l10n.productPay('\u2066USD10.00\u2069')),
            findsOneWidget,
          );

          // Footer: the price as an LTR island, never mirrored.
          final price = find.descendant(
            of: find.byType(ProductFooter),
            matching: find.text('USD10.00'),
          );
          expect(price, findsOneWidget);
          expect(Directionality.of(tester.element(price)), TextDirection.ltr);
          expect(
            Directionality.of(tester.element(find.text(product.card.name))),
            rtl ? TextDirection.rtl : TextDirection.ltr,
          );

          // The delivery field generated from the definition.
          expect(
            find.text(product.deliveryFields.single.label),
            findsOneWidget,
          );

          // API image only.
          expect(h.images.requested, contains(product.images.single.url));
        },
      );

      testWidgets(
        '05.2: description (HTML as text), instructions, terms, reviews',
        (tester) async {
          await open(tester);
          final product = fixtureProduct(lang);
          final description = rtl ? 'اشحن محفظتك.' : 'Load your wallet.';
          await scrollTo(tester, richText(description));
          expect(find.text(l10n.aboutProduct), findsOneWidget);
          await scrollTo(tester, find.text(product.instructions!));
          await scrollTo(tester, find.text(product.terms!));
          await scrollTo(tester, find.text(l10n.reviewsTitle));
          for (final review in fixtureReviews(lang).reviews) {
            await scrollTo(tester, find.text(review.title!));
          }
          expect(find.text('4.5'), findsWidgets);
          expect(find.text(l10n.seeAllReviews), findsNothing); // 2 reviews only
        },
      );

      testWidgets('05.5: skeleton while loading', (tester) async {
        final gate = Completer<void>();
        await open(
          tester,
          settle: false,
          arrange: (h) => h.product.gate = gate,
        );
        await tester.pump(const Duration(milliseconds: 400));
        expect(find.byType(ProductSkeleton), findsOneWidget);
        expect(find.bySemanticsLabel(l10n.loadingLabel), findsOneWidget);
        expect(find.text(l10n.addToCart), findsNothing);
        gate.complete();
        await tester.pumpAndSettle();
        expect(find.byType(ProductSkeleton), findsNothing);
      });

      testWidgets('server error with the request id, then retry', (
        tester,
      ) async {
        var fail = true;
        final h = await open(
          tester,
          arrange: (h) =>
              h.product.onDetail = (_) =>
                  fail ? const Err(serverError) : Ok(fixtureProduct(lang)),
        );
        expect(find.text(l10n.productLoadErrorTitle), findsOneWidget);
        expect(
          find.text(l10n.errorServerBody(serverError.requestId!)),
          findsOneWidget,
        );
        fail = false;
        await tester.tap(find.text(l10n.actionTryAgain));
        await tester.pumpAndSettle();
        expect(find.text(fixtureProduct(lang).card.name), findsOneWidget);
        expect(h.product.detailCalls, hasLength(2));
      });

      testWidgets('offline: banner and retry', (tester) async {
        await open(
          tester,
          arrange: (h) =>
              h.product.onVariants = (_) => const Err(NetworkFailure()),
        );
        expect(find.text(l10n.offlineBannerTitle), findsOneWidget);
        expect(find.text(l10n.noConnectionTitle), findsOneWidget);
        expect(find.text(l10n.actionRetry), findsOneWidget);
      });

      testWidgets('05.6: not found, back to the store', (tester) async {
        await open(
          tester,
          arrange: (h) =>
              h.product.onDetail = (_) => const Err(NotFoundFailure()),
        );
        expect(find.text(l10n.productNotFoundTitle), findsOneWidget);
        expect(find.text(l10n.productNotFoundBody), findsOneWidget);
        await tester.tap(find.text(l10n.backToStore));
        await tester.pumpAndSettle();
        expect(find.byType(HomeScreen), findsOneWidget);
      });

      testWidgets(
        'unavailable: every option blocked, reasons shown, add disabled',
        (tester) async {
          await open(
            tester,
            arrange: (h) => h.product.onVariants = (_) => Ok(
              ProductVariants(
                variants: [
                  offer('a', availability: 'out_of_stock'),
                  offer(
                    'b',
                    title: '25 USD',
                    value: '25.000000',
                    customAmount: true,
                  ),
                  offer(
                    'c',
                    title: '50 USD',
                    value: '50.000000',
                    purchasable: false,
                    price: null,
                  ),
                ],
                displayCurrency: 'USD',
              ),
            ),
          );
          expect(find.text(l10n.productUnavailableTitle), findsOneWidget);
          expect(find.text(l10n.variantSoldOut), findsOneWidget);
          expect(find.text(l10n.variantCustomAmount), findsOneWidget);
          expect(find.text(l10n.variantUnavailable), findsWidgets);
          final button = tester.widget<FilledButton>(
            find.ancestor(
              of: find.text(l10n.addToCart),
              matching: find.byType(FilledButton),
            ),
          );
          expect(button.onPressed, isNull);
          // A blocked amount is announced as disabled with its reason.
          final semantics = tester.getSemantics(
            find.bySemanticsLabel(
              l10n.variantSemantics('10 USD', l10n.variantSoldOut),
            ),
          );
          expect(semantics, isSemantics(isEnabled: false));
        },
      );

      testWidgets('05.3: price may vary notice', (tester) async {
        await open(
          tester,
          arrange: (h) => h.product.onVariants = (_) => Ok(
            ProductVariants(
              variants: [offer('a', priceMayVary: true)],
              displayCurrency: 'USD',
            ),
          ),
        );
        expect(find.text(l10n.priceMayVaryTitle), findsOneWidget);
        expect(find.text(l10n.priceMayVaryBody), findsOneWidget);
        expect(
          find.text(l10n.productRegion),
          findsNothing,
        ); // one region: no selector
      });

      testWidgets('region switch shows that region\'s amounts and "few left"', (
        tester,
      ) async {
        await open(tester);
        final global = fixtureVariants(lang).variants.last;
        await tester.tap(find.text(fixtureProduct(lang).regions.last.name));
        await tester.pumpAndSettle();
        expect(find.text(global.title), findsOneWidget);
        expect(find.text(l10n.variantLimited), findsOneWidget);
        expect(
          find.descendant(
            of: find.byType(ProductFooter),
            matching: find.text('USD25.50'),
          ),
          findsOneWidget,
        );
      });

      testWidgets('quantity stepper: price × n, limit note, buttons disable', (
        tester,
      ) async {
        await open(
          tester,
          arrange: (h) =>
              h.product.onDetail = (_) =>
                  Ok(fixtureProduct(lang).copyWith(maxQuantityPerOrder: 2)),
        );
        await tester.tap(find.byTooltip(l10n.increaseQuantity));
        await tester.pumpAndSettle();
        expect(
          find.text(l10n.priceTimesQuantity('USD10.00', 2)),
          findsOneWidget,
        );
        expect(find.text(l10n.quantityLimit(2)), findsOneWidget);
        expect(
          find.bySemanticsLabel(l10n.quantitySemantics(2)),
          findsOneWidget,
        );
        final plus = tester.widget<IconButton>(
          find.ancestor(
            of: find.byIcon(Icons.add),
            matching: find.byType(IconButton),
          ),
        );
        expect(plus.onPressed, isNull);
      });

      testWidgets(
        'local validation: an empty required field blocks the request',
        (tester) async {
          final h = await open(tester);
          await tapAdd(tester);
          final label = fixtureProduct(lang).deliveryFields.single.label;
          expect(find.text(l10n.deliveryFieldRequired(label)), findsOneWidget);
          expect(h.cart.adds, isEmpty);
          await enterPlayerId(tester, '4817');
          expect(find.text(l10n.deliveryFieldRequired(label)), findsNothing);
        },
      );

      testWidgets('422: the server\'s delivery_values error beside the field', (
        tester,
      ) async {
        const message = 'Player ID has an invalid format.';
        final h = await open(
          tester,
          arrange: (h) => h.cart.onAdd = (_) => const Err(
            ValidationFailure({
              'delivery_values.player_id': [message],
            }),
          ),
        );
        await enterPlayerId(tester, '48-17');
        await tapAdd(tester);
        expect(h.cart.adds.single.deliveryValues, {'player_id': '48-17'});
        expect(find.text(message), findsOneWidget);
        expect(find.text(l10n.addFailedTitle), findsNothing);
      });

      testWidgets('422 item_unavailable: banner with the server text', (
        tester,
      ) async {
        await open(
          tester,
          arrange: (h) => h.cart.onAdd = (_) => const Err(
            DomainFailure(
              'item_unavailable',
              message: 'This product is out of stock.',
              errors: {
                'variant_id': ['This product is out of stock.'],
              },
            ),
          ),
        );
        await enterPlayerId(tester, '4817');
        await tapAdd(tester);
        await tester.drag(find.byType(Scrollable).first, const Offset(0, 400));
        await tester.pumpAndSettle();
        expect(find.text(l10n.addFailedTitle), findsOneWidget);
        expect(find.text('This product is out of stock.'), findsOneWidget);
      });

      testWidgets(
        '05.4: added, snackbar, View cart opens the Cart tab with the badge',
        (tester) async {
          final h = await open(tester);
          await enterPlayerId(tester, '4817 2290 31');
          await tapAdd(tester);
          expect(h.cart.adds, hasLength(1));
          expect(find.text(l10n.addedToCart), findsOneWidget);
          // The guest now holds a cart token and the server has the cart (M-A13 cart tab).
          h.secure[SecureStore.cartTokenKey] = 'fixture-cart-token';
          h.cart.getResult = Ok(fixtureCart());
          await tester.tap(find.text(l10n.viewCart));
          await tester.pumpAndSettle();
          expect(find.byType(ProductPage), findsNothing);
          expect(find.byType(CartPage), findsOneWidget);
          expect(find.text(l10n.cartTitleCount(1)), findsOneWidget);
          expect(
            find.bySemanticsLabel(
              '${l10n.navCart}, ${l10n.cartBadgeSemantics(1)}',
            ),
            findsOneWidget,
          );
        },
      );

      testWidgets('number fields: digit keyboard, Western digits', (
        tester,
      ) async {
        final h = await open(
          tester,
          arrange: (h) => h.product.onDetail = (_) => Ok(
            fixtureProduct(lang).copyWith(
              deliveryFields: [
                const DeliveryField(
                  key: 'server_id',
                  label: 'Server ID',
                  type: DeliveryFieldType.number,
                  required: true,
                ),
              ],
            ),
          ),
        );
        final field = tester.widget<TextField>(find.byType(TextField));
        expect(field.keyboardType, TextInputType.number);
        expect(field.textDirection, TextDirection.ltr);
        await tester.enterText(find.byType(TextField), '١٢٣٤');
        await tester.pump();
        expect(find.text('1234'), findsOneWidget);
        await tapAdd(tester);
        expect(h.cart.adds.single.deliveryValues, {'server_id': '1234'});
      });

      testWidgets('select fields open a sheet of options', (tester) async {
        final h = await open(
          tester,
          arrange: (h) => h.product.onDetail = (_) => Ok(
            fixtureProduct(lang).copyWith(
              deliveryFields: [
                const DeliveryField(
                  key: 'zone',
                  label: 'Zone',
                  type: DeliveryFieldType.select,
                  required: true,
                  options: [
                    DeliveryFieldOption(value: 'eu', label: 'Europe'),
                    DeliveryFieldOption(value: 'na', label: 'North America'),
                  ],
                ),
              ],
            ),
          ),
        );
        expect(find.text(l10n.selectPlaceholder), findsOneWidget);
        await tester.tap(find.text(l10n.selectPlaceholder));
        await tester.pumpAndSettle();
        await tester.tap(find.text('North America'));
        await tester.pumpAndSettle();
        expect(find.text('North America'), findsOneWidget);
        await tapAdd(tester);
        expect(h.cart.adds.single.deliveryValues, {'zone': 'na'});
      });

      testWidgets('See all reviews opens the paged list (infinite scroll)', (
        tester,
      ) async {
        ProductReviewsPage page(int n) {
          final base = fixtureReviews(lang);
          return base.copyWith(
            reviews: [
              for (var i = 0; i < 10; i++)
                base.reviews.first.copyWith(
                  id: n * 100 + i,
                  title: 'Review $n-$i',
                ),
            ],
            page: PageInfo(currentPage: n, perPage: 10, total: 20, lastPage: 2),
          );
        }

        final h = await open(
          tester,
          arrange: (h) => h.product.onReviews = (n) => Ok(page(n)),
        );
        await scrollTo(tester, find.text(l10n.seeAllReviews));
        await tester.tap(find.text(l10n.seeAllReviews));
        await tester.pumpAndSettle();
        expect(find.byType(ProductReviewsScreen), findsOneWidget);
        await tester.scrollUntilVisible(
          find.text('Review 2-9'),
          300,
          scrollable: find.byType(Scrollable).last,
        );
        expect(h.product.reviewCalls.map((c) => c.page), [1, 2]);
        await tester.tap(find.byTooltip(l10n.actionBack).last);
        await tester.pumpAndSettle();
        expect(find.byType(ProductPage), findsOneWidget);
      });

      testWidgets('reviews: empty and failed', (tester) async {
        var fail = true;
        await open(
          tester,
          arrange: (h) => h.product.onReviews = (_) => fail
              ? const Err(NetworkFailure())
              : Ok(
                  fixtureReviews(lang)
                      .copyWith(reviews: const [], ratingCount: 0),
                ),
        );
        await scrollTo(tester, find.text(l10n.reviewsLoadFailed));
        fail = false;
        await tester.tap(find.text(l10n.actionRetry));
        await tester.pumpAndSettle();
        await scrollTo(tester, find.text(l10n.reviewsEmptyTitle));
      });

      testWidgets(
        'deep link location /products/:slug opens the page directly',
        (tester) async {
          final h = await pumpHarness(
            tester,
            languageCode: lang,
            arrange: (h) =>
                h.product.onDetail = (_) => Ok(fixtureProduct(lang)),
          );
          h.read(appRouterProvider).go(Routes.productLocation(slug));
          await tester.pumpAndSettle();
          expect(find.byType(ProductPage), findsOneWidget);
          // Back without history goes home.
          await tester.tap(find.byTooltip(l10n.actionBack));
          await tester.pumpAndSettle();
          expect(find.byType(HomeScreen), findsOneWidget);
        },
      );

      testWidgets('360 wide: nothing overflows', (tester) async {
        await open(tester, size: const Size(360, 800));
        expect(tester.takeException(), isNull);
        expect(find.text(l10n.addToCart), findsOneWidget);
      });

      testWidgets('meets tap-target and label guidelines', (tester) async {
        final handle = tester.ensureSemantics();
        await open(tester);
        await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
        await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
        handle.dispose();
      });

      testWidgets('reviews page meets tap-target and label guidelines', (
        tester,
      ) async {
        final handle = tester.ensureSemantics();
        final h = await open(tester);
        unawaited(
          h.read(appRouterProvider).push(Routes.productReviewsLocation(slug)),
        );
        await tester.pumpAndSettle();
        expect(find.byType(ProductReviewsScreen), findsOneWidget);
        await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
        await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
        handle.dispose();
      });
    });
  }
}
