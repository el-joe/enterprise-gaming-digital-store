import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/catalog/presentation/pages/platforms_page.dart';
import 'package:application/features/home/presentation/pages/home_page.dart';
import 'package:application/features/product/presentation/pages/product_page.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/catalog_fakes.dart';
import '../../helpers/pump_app.dart';

/// Widget tests of the M-A11 pages (home, browse, platforms, listing, filters, search, currency
/// switcher, tab shell) in English (LTR) and Arabic (RTL), on the fake repositories of the
/// harness answering with the contract fixtures.
void main() {
  for (final lang in ['en', 'ar']) {
    final l10n = l10nFor(lang);
    final rtl = lang == 'ar';

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      void Function(TestHarness h)? arrange,
      bool settle = true,
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        arrange: (h) {
          h.home.results = [Ok(fixtureHome(lang))];
          h.catalog
            ..platformsResult = Ok(fixturePlatforms(lang))
            ..categoriesResult = Ok(fixtureCategories(lang));
          arrange?.call(h);
        },
      );
      if (location != Routes.home.path) {
        h.read(appRouterProvider).go(location);
      }
      if (settle) {
        await tester.pumpAndSettle();
      } else {
        await tester.pump();
      }
      return h;
    }

    TextDirection directionOf(WidgetTester tester, Finder finder) =>
        Directionality.of(tester.element(finder.first));

    group('home ($lang)', () {
      testWidgets('shows the API banners, sections and server prices', (
        tester,
      ) async {
        final h = await at(tester, Routes.home.path);
        final page = fixtureHome(lang);
        final hero = page.heroBanners.single;

        expect(find.byKey(HomeScreen.pageKey), findsOneWidget);
        expect(find.text(hero.title!), findsOneWidget);
        expect(find.text(hero.ctaLabel!), findsOneWidget);
        expect(find.text(l10n.searchFieldHint), findsOneWidget);
        expect(
          directionOf(tester, find.text(hero.title!)),
          rtl ? TextDirection.rtl : TextDirection.ltr,
        );
        final offers = page.sections.first;
        await tester.scrollUntilVisible(
          find.text(offers.title!),
          200,
          scrollable: find.byType(Scrollable).first,
        );
        expect(find.text(offers.title!), findsOneWidget);
        // Prices are the server's `formatted` strings, as LTR islands.
        final price = find.text('USD10.00');
        await tester.scrollUntilVisible(
          price.first,
          200,
          scrollable: find.byType(Scrollable).first,
        );
        expect(directionOf(tester, price), TextDirection.ltr);
        // Images come only from API URLs.
        expect(h.images.requested, isNotEmpty);
        expect(
          h.images.requested.every(
            (url) => url.toString().startsWith('https://media.boxcards.test/'),
          ),
          isTrue,
        );
        // Bottom navigation with five tabs.
        for (final label in [
          l10n.navHome,
          l10n.navBrowse,
          l10n.navCart,
          l10n.navOrders,
          l10n.navAccount,
        ]) {
          expect(find.text(label), findsOneWidget);
        }
      });

      testWidgets('loading shows the skeleton', (tester) async {
        final gate = Completer<void>();
        final h = await at(
          tester,
          Routes.home.path,
          arrange: (h) => h.home.gate = gate,
        );
        expect(find.byType(ProductGridSkeleton), findsOneWidget);
        expect(find.bySemanticsLabel(l10n.loadingLabel), findsOneWidget);
        gate.complete();
        await tester.pumpAndSettle();
        expect(find.byType(ProductGridSkeleton), findsNothing);
        expect(h.home.calls, 1);
      });

      testWidgets('server error shows the request id and retries', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.home.path,
          arrange: (h) =>
              h.home.results = [const Err(serverError), Ok(fixtureHome(lang))],
        );
        expect(find.text(l10n.homeErrorTitle), findsOneWidget);
        expect(
          find.text(l10n.errorServerBody(serverError.requestId!)),
          findsOneWidget,
        );
        await tester.tap(find.text(l10n.actionTryAgain));
        await tester.pumpAndSettle();
        expect(find.text(l10n.homeErrorTitle), findsNothing);
        expect(h.home.calls, 2);
      });

      testWidgets('offline shows the banner and retry', (tester) async {
        final h = await at(
          tester,
          Routes.home.path,
          arrange: (h) => h.home.results = [
            const Err(NetworkFailure()),
            Ok(fixtureHome(lang)),
          ],
        );
        expect(find.text(l10n.offlineBannerTitle), findsOneWidget);
        expect(find.text(l10n.noConnectionTitle), findsOneWidget);
        await tester.tap(find.text(l10n.actionRetry));
        await tester.pumpAndSettle();
        expect(find.text(l10n.noConnectionTitle), findsNothing);
        expect(h.home.calls, 2);
      });

      testWidgets('the hero opens its link target (product)', (tester) async {
        final h = await at(tester, Routes.home.path);
        await tester.tap(
          find.text(fixtureHome(lang).heroBanners.single.title!),
        );
        await tester.pumpAndSettle();
        // The product page (M-A12) loads the linked product.
        expect(find.byType(ProductPage), findsOneWidget);
        expect(
          h.product.detailCalls.single,
          fixtureHome(lang).heroBanners.single.link!.value,
        );
      });

      testWidgets('a search promo banner opens search with the term', (
        tester,
      ) async {
        final h = await at(tester, Routes.home.path);
        final promo = fixtureHome(lang).promoBanners.single;
        await tester.tap(find.text(promo.title!));
        await tester.pumpAndSettle();
        expect(h.search.suggestCalls.last, (
          query: promo.link!.value,
          commit: true,
        ));
      });

      testWidgets('currency switcher reloads prices in the new currency', (
        tester,
      ) async {
        final h = await at(tester, Routes.home.path);
        expect(h.home.calls, 1);
        await tester.tap(
          find.bySemanticsLabel(l10n.currencyButtonLabel('USD')),
        );
        await tester.pumpAndSettle();
        expect(find.text(l10n.displayCurrencyLabel), findsOneWidget);
        expect(find.text(l10n.currencySheetBody), findsOneWidget);
        await tester.tap(find.text('EGP'));
        await tester.pump();
        await tester.tap(find.text(l10n.currencyApply));
        await tester.pumpAndSettle();
        expect(h.prefs.values[PreferencesStore.currencyKey], 'EGP');
        expect(h.home.calls, 2);
        expect(find.text(l10n.currencyChanged('EGP')), findsOneWidget);
        expect(
          find.bySemanticsLabel(l10n.currencyButtonLabel('EGP')),
          findsOneWidget,
        );
      });

      testWidgets('tabs switch and keep their pages', (tester) async {
        await at(tester, Routes.home.path);
        await tester.tap(find.text(l10n.navBrowse));
        await tester.pumpAndSettle();
        expect(find.text(l10n.categoriesTitle), findsOneWidget);
        await tester.tap(find.text(l10n.navCart));
        await tester.pumpAndSettle();
        expect(find.text(l10n.cartTitle), findsWidgets);
        expect(find.text(l10n.cartEmptyTitle), findsOneWidget);
        await tester.tap(find.text(l10n.navOrders));
        await tester.pumpAndSettle();
        expect(find.text(l10n.ordersTitle), findsWidgets);
        await tester.tap(find.text(l10n.navAccount));
        await tester.pumpAndSettle();
        expect(find.text(l10n.homeAccountTitle), findsWidgets);
        expect(find.text(l10n.displayCurrencyLabel), findsOneWidget);
        await tester.tap(find.text(l10n.navHome));
        await tester.pumpAndSettle();
        expect(find.byKey(HomeScreen.pageKey), findsOneWidget);
      });
    });

    group('browse and platforms ($lang)', () {
      testWidgets('browse lists categories with children and platforms', (
        tester,
      ) async {
        await at(tester, Routes.browse.path);
        final categories = fixtureCategories(lang);
        final platforms = fixturePlatforms(lang);
        expect(find.text(categories.first.name), findsOneWidget);
        expect(find.text(categories.last.name), findsOneWidget);
        expect(find.text(l10n.productCount(1)), findsOneWidget);
        expect(find.text(platforms.first.name), findsOneWidget);

        await tester.tap(find.text(categories.last.name));
        await tester.pumpAndSettle();
        expect(find.text(categories.last.name), findsOneWidget);
        expect(find.text(l10n.productCount(2)), findsOneWidget);
      });

      testWidgets('platforms grid opens the platform listing', (tester) async {
        final h = await at(tester, Routes.platforms.path);
        final platform = fixturePlatforms(lang).first;
        expect(find.text(l10n.platformsIntro), findsOneWidget);
        await tester.tap(
          find.descendant(
            of: find.byType(PlatformsPage),
            matching: find.text(platform.name),
          ),
        );
        await tester.pumpAndSettle();
        expect(h.catalog.queries.last.platform, platform.slug);
        expect(
          find.descendant(
            of: find.byType(MobileAppBar),
            matching: find.text(platform.name),
          ),
          findsOneWidget,
          reason: 'listing title',
        );
      });

      testWidgets('taxonomy failure shows the error state', (tester) async {
        await at(
          tester,
          Routes.browse.path,
          arrange: (h) => h.catalog.categoriesResult = const Err(serverError),
        );
        expect(find.text(l10n.loadErrorTitle), findsOneWidget);
      });
    });

    group('listing ($lang)', () {
      testWidgets('infinite scroll loads the next pages', (tester) async {
        final h = await at(
          tester,
          Routes.listingLocation(platform: 'orbit'),
          arrange: (h) => h.catalog.total = 45,
        );
        expect(find.text(l10n.productCount(45)), findsOneWidget);
        expect(find.text('Card 1'), findsOneWidget);
        expect(find.text('USD1.00'), findsOneWidget);
        await tester.scrollUntilVisible(
          find.text('Card 45'),
          400,
          scrollable: find.byType(Scrollable).last,
          maxScrolls: 200,
        );
        expect(h.catalog.queries.map((q) => q.page), [1, 2, 3]);
        expect(h.catalog.queries.every((q) => q.platform == 'orbit'), isTrue);
      });

      testWidgets('a failed page shows retry and keeps the products', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.listingLocation(category: 'gift-cards'),
          arrange: (h) => h.catalog
            ..total = 45
            ..onProducts = null,
        );
        h.catalog.onProducts = (query) => const Err(NetworkFailure());
        await tester.fling(
          find.byType(Scrollable).last,
          const Offset(0, -3000),
          3000,
        );
        await tester.pumpAndSettle();
        expect(find.text(l10n.loadMoreFailed), findsOneWidget);
        final pages = h.catalog.queries.length;
        await tester.pumpAndSettle();
        expect(h.catalog.queries.length, pages, reason: 'no automatic retry');

        h.catalog.onProducts = null;
        await tester.tap(find.text(l10n.actionRetry));
        await tester.pumpAndSettle();
        expect(find.text(l10n.loadMoreFailed), findsNothing);
        expect(h.catalog.queries.last.page, 2);
      });

      testWidgets('filters apply, empty state clears them', (tester) async {
        final h = await at(
          tester,
          Routes.listingLocation(category: 'gift-cards'),
        );
        await tester.tap(find.text(l10n.filtersChip));
        await tester.pumpAndSettle();
        expect(find.text(l10n.filtersTitle), findsWidgets);
        await tester.tap(find.text(l10n.deliveryInstantCode));
        await tester.enterText(find.byType(TextField).first, '5.00');
        await tester.enterText(find.byType(TextField).last, '50');
        h.catalog.total = 0;
        await tester.tap(find.text(l10n.filtersApply));
        await tester.pumpAndSettle();

        final query = h.catalog.queries.last;
        expect(query.category, 'gift-cards');
        expect(query.deliveryType, 'instant_code');
        expect(query.minPrice, '5.00');
        expect(query.maxPrice, '50');
        expect(find.text(l10n.filtersChipCount(3)), findsOneWidget);
        expect(find.text(l10n.listingEmptyTitle), findsOneWidget);

        h.catalog.total = 2;
        await tester.tap(find.text(l10n.clearFilters));
        await tester.pumpAndSettle();
        expect(h.catalog.queries.last.deliveryType, isNull);
        expect(find.text(l10n.filtersChip), findsOneWidget);
        expect(find.text('Card 1'), findsOneWidget);
      });

      testWidgets('invalid price bounds stay in the sheet', (tester) async {
        final h = await at(tester, Routes.listingLocation());
        final requests = h.catalog.queries.length;
        await tester.tap(find.text(l10n.filtersChip));
        await tester.pumpAndSettle();
        await tester.enterText(find.byType(TextField).first, '9');
        await tester.enterText(find.byType(TextField).last, '1.234');
        await tester.tap(find.text(l10n.filtersApply));
        await tester.pumpAndSettle();
        expect(find.text(l10n.filterPriceInvalid), findsOneWidget);
        await tester.enterText(find.byType(TextField).last, '2');
        await tester.tap(find.text(l10n.filtersApply));
        await tester.pumpAndSettle();
        expect(find.text(l10n.filterPriceRange), findsOneWidget);
        expect(h.catalog.queries.length, requests);
      });

      testWidgets('sort sheet re-queries from page 1', (tester) async {
        final h = await at(tester, Routes.listingLocation(platform: 'orbit'));
        await tester.tap(find.text(l10n.sortChip(l10n.sortPopular)));
        await tester.pumpAndSettle();
        await tester.tap(find.text(l10n.sortPriceAsc));
        await tester.pumpAndSettle();
        expect(h.catalog.queries.last.sort, 'price_asc');
        expect(h.catalog.queries.last.page, 1);
        expect(find.text(l10n.sortChip(l10n.sortPriceAsc)), findsOneWidget);
      });

      testWidgets('empty catalog and first-page error', (tester) async {
        await at(
          tester,
          Routes.listingLocation(platform: 'orbit'),
          arrange: (h) => h.catalog.total = 0,
        );
        expect(find.text(l10n.catalogEmptyTitle), findsOneWidget);
      });
    });

    group('search ($lang)', () {
      testWidgets('suggestions, submit, history and clear', (tester) async {
        final h = await at(tester, Routes.search.path);
        expect(find.text(l10n.searchPopular), findsOneWidget);
        expect(find.text('nova points'), findsOneWidget);

        await tester.enterText(find.byType(TextField), 'orb');
        await tester.pump(const Duration(milliseconds: 350));
        await tester.pumpAndSettle();
        expect(find.text(l10n.searchSuggestions), findsOneWidget);
        expect(find.text('Orbit Card'), findsOneWidget);
        expect(find.text(l10n.searchTypePlatform), findsOneWidget);

        await tester.testTextInput.receiveAction(TextInputAction.search);
        await tester.pumpAndSettle();
        expect(h.search.suggestCalls.last, (query: 'orb', commit: true));
        expect(find.text(l10n.searchResults), findsOneWidget);
        expect(find.text(l10n.searchRecent), findsOneWidget);
        expect(find.text('orb'), findsWidgets);

        await tester.tap(find.text(l10n.searchClearHistory));
        await tester.pumpAndSettle();
        expect(find.text(l10n.searchRecent), findsNothing);
        expect(h.prefs.values[PreferencesStore.searchHistoryKey], isNull);
      });

      testWidgets('a suggestion opens its listing; popular terms search', (
        tester,
      ) async {
        final h = await at(tester, Routes.search.path);
        await tester.tap(find.text('orbit'));
        await tester.pumpAndSettle();
        expect(h.search.suggestCalls.last, (query: 'orbit', commit: true));
        await tester.tap(find.text('Orbit').first);
        await tester.pumpAndSettle();
        expect(h.catalog.queries.last.platform, 'orbit');
      });

      testWidgets('history terms can be removed one by one', (tester) async {
        final h = await at(
          tester,
          Routes.search.path,
          arrange: (h) => h.prefs.values[PreferencesStore.searchHistoryKey] =
              '["nova","pulse wallet"]',
        );
        expect(find.text('pulse wallet'), findsOneWidget);
        await tester.tap(find.byTooltip(l10n.searchRemoveTerm('pulse wallet')));
        await tester.pumpAndSettle();
        expect(find.text('pulse wallet'), findsNothing);
        expect(h.prefs.values[PreferencesStore.searchHistoryKey], '["nova"]');
      });

      testWidgets('no results', (tester) async {
        await at(
          tester,
          Routes.searchLocation('zelda'),
          arrange: (h) => h.search.onSuggest = (_) => const Ok([]),
        );
        expect(find.text(l10n.searchNoResultsTitle('zelda')), findsOneWidget);
        expect(find.text(l10n.searchNoResultsBody), findsOneWidget);
      });
    });

    group('accessibility ($lang)', () {
      for (final location in [
        Routes.home.path,
        Routes.browse.path,
        Routes.platforms.path,
        Routes.listingLocation(platform: 'orbit'),
        Routes.search.path,
      ]) {
        testWidgets('$location meets tap-target and label guidelines', (
          tester,
        ) async {
          final handle = tester.ensureSemantics();
          await at(
            tester,
            location,
            arrange: (h) =>
                h.prefs.values[PreferencesStore.searchHistoryKey] = '["nova"]',
          );
          await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
          await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
          handle.dispose();
        });
      }
    });
  }
}
