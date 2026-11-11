import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/features/catalog/presentation/controllers/catalog_controllers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/catalog_fakes.dart';
import '../helpers/pump_app.dart';

/// Key M-A11 states in LTR (en) and RTL (ar), compared with the approved M-A8 frames (03.x home,
/// 04.x catalog and search, 04.8 browse) by review. Images never load here (the fake loader keeps
/// them pending), so tiles show their token fallback: the plain-text tile with the empty Logo
/// Slot (dev flavor) of the Figma frames.
void main() {
  setUpAll(loadAppFonts);

  for (final lang in ['en', 'ar']) {
    final dir = lang == 'ar' ? 'rtl' : 'ltr';
    final l10n = l10nFor(lang);

    Future<void> snap(WidgetTester tester, String name) async {
      await precacheImages(tester);
      await expectLater(
        find.byType(MaterialApp),
        matchesGoldenFile('m_a11/${name}_$dir.png'),
      );
    }

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      void Function(TestHarness h)? arrange,
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        arrange: (h) {
          h.home.results = [Ok(fixtureHome(lang))];
          h.catalog
            ..platformsResult = Ok(fixturePlatforms(lang))
            ..categoriesResult = Ok(fixtureCategories(lang))
            ..total = 24;
          arrange?.call(h);
        },
      );
      if (location != Routes.home.path) {
        h.read(appRouterProvider).go(location);
      }
      await tester.pumpAndSettle();
      return h;
    }

    testWidgets('03.1 home ($dir)', (tester) async {
      await at(tester, Routes.home.path);
      await snap(tester, 'home');
    });

    testWidgets('03.1 home, featured ($dir)', (tester) async {
      await at(tester, Routes.home.path);
      await tester.drag(find.byType(Scrollable).first, const Offset(0, -420));
      await tester.pumpAndSettle();
      await snap(tester, 'home_sections');
    });

    testWidgets('03.2 home loading ($dir)', (tester) async {
      final gate = Completer<void>();
      await at(tester, Routes.home.path, arrange: (h) => h.home.gate = gate);
      await snap(tester, 'home_loading');
      gate.complete();
      await tester.pumpAndSettle();
    });

    testWidgets('03.3 home error ($dir)', (tester) async {
      await at(
        tester,
        Routes.home.path,
        arrange: (h) => h.home.results = [const Err(serverError)],
      );
      await snap(tester, 'home_error');
    });

    testWidgets('03.4 home offline ($dir)', (tester) async {
      await at(
        tester,
        Routes.home.path,
        arrange: (h) => h.home.results = [const Err(NetworkFailure())],
      );
      await snap(tester, 'home_offline');
    });

    testWidgets('03.5 currency sheet ($dir)', (tester) async {
      await at(tester, Routes.home.path);
      await tester.tap(find.bySemanticsLabel(l10n.currencyButtonLabel('USD')));
      await tester.pumpAndSettle();
      await tester.tap(find.text('EGP'));
      await tester.pumpAndSettle();
      await snap(tester, 'currency_sheet');
    });

    testWidgets('04.1 platforms ($dir)', (tester) async {
      await at(tester, Routes.platforms.path);
      await snap(tester, 'platforms');
    });

    testWidgets('04.2 listing ($dir)', (tester) async {
      await at(
        tester,
        Routes.listingLocation(category: 'gift-cards', platform: 'orbit'),
      );
      await snap(tester, 'listing');
    });

    testWidgets('04.3 filters sheet ($dir)', (tester) async {
      await at(tester, Routes.listingLocation(platform: 'orbit'));
      await tester.tap(find.text(l10n.filtersChip));
      await tester.pumpAndSettle();
      await tester.tap(find.text(l10n.deliveryInstantCode));
      await tester.tap(find.text(l10n.sortPriceAsc));
      await tester.enterText(find.byType(TextField).first, '5.00');
      await tester.enterText(find.byType(TextField).last, '50.00');
      FocusManager.instance.primaryFocus?.unfocus();
      await tester.pumpAndSettle();
      await snap(tester, 'filters_sheet');
    });

    testWidgets('04.4 listing empty ($dir)', (tester) async {
      final h = await at(
        tester,
        Routes.listingLocation(category: 'gift-cards', platform: 'orbit'),
      );
      h.catalog.total = 0;
      await h
          .read(
            listingControllerProvider(
              const CatalogQuery(category: 'gift-cards', platform: 'orbit'),
            ).notifier,
          )
          .applyFilters(
            deliveryType: CatalogDelivery.instantCode,
            sort: CatalogSort.priceAsc,
          );
      await tester.pumpAndSettle();
      await snap(tester, 'listing_empty');
    });

    testWidgets('04.5 listing load-more error ($dir)', (tester) async {
      final h = await at(
        tester,
        Routes.listingLocation(platform: 'orbit'),
        arrange: (h) => h.catalog.total = 45,
      );
      h.catalog.onProducts = (_) => const Err(NetworkFailure());
      await tester.fling(
        find.byType(Scrollable).last,
        const Offset(0, -3000),
        3000,
      );
      await tester.pumpAndSettle();
      await snap(tester, 'listing_load_more_error');
    });

    testWidgets('04.6 search ($dir)', (tester) async {
      await at(
        tester,
        Routes.search.path,
        arrange: (h) => h.prefs.values[PreferencesStore.searchHistoryKey] =
            '["nova","pulse wallet"]',
      );
      await tester.enterText(find.byType(TextField), 'orbit');
      await tester.pump(const Duration(milliseconds: 350));
      FocusManager.instance.primaryFocus?.unfocus();
      await tester.pumpAndSettle();
      await snap(tester, 'search');
    });

    testWidgets('04.7 search no results ($dir)', (tester) async {
      await at(
        tester,
        Routes.searchLocation('zelda'),
        arrange: (h) => h.search.onSuggest = (_) => const Ok([]),
      );
      await snap(tester, 'search_no_results');
    });

    testWidgets('04.8 browse ($dir)', (tester) async {
      await at(tester, Routes.browse.path);
      await snap(tester, 'browse');
    });
  }
}
