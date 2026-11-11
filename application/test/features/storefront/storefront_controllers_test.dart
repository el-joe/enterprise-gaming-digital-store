import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/link_targets.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/features/catalog/presentation/controllers/catalog_controllers.dart';
import 'package:application/features/catalog/presentation/controllers/currency_switcher_controller.dart';
import 'package:application/features/home/domain/entities/home.dart';
import 'package:application/features/home/presentation/controllers/home_controller.dart';
import 'package:application/features/search/data/search_history_store.dart';
import 'package:application/features/search/presentation/controllers/search_controller.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:flutter_riverpod/misc.dart' show ProviderListenable;
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import '../../helpers/catalog_fakes.dart';
import '../../helpers/fakes.dart';
import '../../helpers/pump_app.dart';

/// Lets queued fake-repository futures complete.
Future<void> settle() async {
  for (var i = 0; i < 5; i++) {
    await Future<void>.delayed(Duration.zero);
  }
}

/// Keeps an auto-dispose provider alive for the test.
void keep<T>(TestHarness h, ProviderListenable<T> provider) {
  final sub = h.container.listen(provider, (_, _) {});
  addTearDown(sub.close);
}

void main() {
  setUpAll(() => registerFallbackValue(const ProfileUpdate()));

  group('home', () {
    test(
      'loads the server page, fails with a typed failure and retries',
      () async {
        final h = buildHarness();
        addTearDown(h.dispose);
        h.home.results = [const Err(serverError), Ok(fixtureHome())];
        keep(h, homeControllerProvider);

        expect(h.read(homeControllerProvider), isA<Loading<HomePage>>());
        await settle();
        final failed = h.read(homeControllerProvider) as Failed<HomePage>;
        expect(failed.failure.requestId, serverError.requestId);

        await h.read(homeControllerProvider.notifier).retry();
        final page = h.read(homeControllerProvider).valueOrNull!;
        expect(page.heroBanners.single.title, 'Orbit week');
        expect(h.home.calls, 2);
      },
    );

    test('visible sections drop unknown items and empty sections', () {
      final page = fixtureHome();
      final keys = visibleSections(page).map((s) => s.key);
      // `trust_indicators` has no items in the fixture.
      expect(keys, ['special_offers', 'best_sellers', 'customer_reviews']);
      final extended = page.copyWith(
        sections: [
          const HomeSection(
            key: 'future',
            source: 'manual',
            items: [HomeItem.unknown('video')],
          ),
          ...page.sections,
        ],
      );
      expect(
        visibleSections(extended).map((s) => s.key),
        isNot(contains('future')),
      );
    });
  });

  group('currency change invalidates priced data', () {
    test(
      'home, listing and the X-Currency choice reload from page 1',
      () async {
        final h = buildHarness();
        addTearDown(h.dispose);
        h.catalog.total = 45;
        const base = CatalogQuery(category: 'gift-cards');
        keep(h, homeControllerProvider);
        keep(h, listingControllerProvider(base));
        await settle();
        await h.read(listingControllerProvider(base).notifier).loadMore();
        expect(h.read(listingControllerProvider(base)).items, hasLength(40));
        expect(h.home.calls, 1);

        h.catalog.currency = 'EGP';
        final switcher = h.container.listen(
          currencySwitcherControllerProvider,
          (_, _) {},
        );
        addTearDown(switcher.close);
        h.read(currencySwitcherControllerProvider.notifier).choose('EGP');
        expect(
          await h.read(currencySwitcherControllerProvider.notifier).apply(),
          isTrue,
        );
        await settle();

        expect(h.read(displayCurrencyProvider), 'EGP');
        expect(h.prefs.values[PreferencesStore.currencyKey], 'EGP');
        expect(h.home.calls, 2, reason: 'home refetched in the new currency');
        final listing = h.read(listingControllerProvider(base));
        expect(h.catalog.queries.last.page, 1);
        expect(h.catalog.queries.last.category, 'gift-cards');
        expect(listing.items, hasLength(20));
        expect(listing.displayCurrency, 'EGP');
        expect(listing.items.first.priceFrom!.currency, 'EGP');
      },
    );

    test(
      'signed-in customers save the currency on the profile first',
      () async {
        final h = buildHarness(signedIn: true);
        addTearDown(h.dispose);
        keep(h, homeControllerProvider);
        keep(h, currencySwitcherControllerProvider);
        await settle();
        when(() => h.account.updateProfile(any()))
            .thenAnswer((_) async => const Err(NetworkFailure()));
        final controller = h.read(currencySwitcherControllerProvider.notifier)
          ..choose('EGP');
        expect(await controller.apply(), isFalse);
        expect(
          h.read(currencySwitcherControllerProvider).failure,
          isA<NetworkFailure>(),
        );
        expect(h.read(displayCurrencyProvider), isNull);
        expect(h.home.calls, 1, reason: 'nothing reloads when the save fails');
      },
    );

    test(
      'options come from the currencies endpoint, else the app config',
      () async {
        final h = buildHarness();
        addTearDown(h.dispose);
        keep(h, currencySwitcherControllerProvider);
        await settle();
        final options = h.read(currencySwitcherControllerProvider).options;
        expect(options.valueOrNull!.map((o) => o.info.code), ['EGP', 'USD']);
        expect(h.catalog.currencyCalls, 1);

        h.catalog.currenciesResult = const Err(NetworkFailure());
        await h.read(currencySwitcherControllerProvider.notifier).retry();
        expect(
          h.read(currencySwitcherControllerProvider).options,
          isA<Failed<List<DisplayCurrencyOption>>>(),
          reason: 'no app config was loaded in this container',
        );
        h.read(latestAppConfigProvider.notifier).set(fixtureAppConfig());
        await h.read(currencySwitcherControllerProvider.notifier).retry();
        expect(
          h
              .read(currencySwitcherControllerProvider)
              .options
              .valueOrNull!
              .map((o) => o.info.code),
          ['USD'],
        );
      },
    );
  });

  group('listing pagination', () {
    const base = CatalogQuery(platform: 'orbit');

    test('pages follow meta until the last page', () async {
      final h = buildHarness();
      addTearDown(h.dispose);
      h.catalog.total = 45;
      keep(h, listingControllerProvider(base));
      final controller = h.read(listingControllerProvider(base).notifier);
      expect(h.read(listingControllerProvider(base)).loading, isTrue);
      await settle();

      var state = h.read(listingControllerProvider(base));
      expect(state.items, hasLength(20));
      expect(state.total, 45);
      expect(state.hasMore, isTrue);

      await controller.loadMore();
      await controller.loadMore();
      state = h.read(listingControllerProvider(base));
      expect(state.items, hasLength(45));
      expect(state.items.last.name, 'Card 45');
      expect(state.hasMore, isFalse);
      expect(h.catalog.queries.map((q) => q.page), [1, 2, 3]);
      expect(h.catalog.queries.every((q) => q.platform == 'orbit'), isTrue);

      await controller.loadMore();
      expect(
        h.catalog.queries,
        hasLength(3),
        reason: 'no request past the last page',
      );
    });

    test('a second load-more while one is running is ignored', () async {
      final h = buildHarness();
      addTearDown(h.dispose);
      h.catalog.total = 60;
      keep(h, listingControllerProvider(base));
      await settle();
      final controller = h.read(listingControllerProvider(base).notifier);
      final first = controller.loadMore();
      final second = controller.loadMore();
      await Future.wait([first, second]);
      expect(h.catalog.queries.map((q) => q.page), [1, 2]);
    });

    test('a failed page keeps the items and retries the same page', () async {
      final h = buildHarness();
      addTearDown(h.dispose);
      h.catalog.total = 45;
      keep(h, listingControllerProvider(base));
      await settle();
      final controller = h.read(listingControllerProvider(base).notifier);

      h.catalog.onProducts = (_) => const Err(NetworkFailure());
      await controller.loadMore();
      var state = h.read(listingControllerProvider(base));
      expect(state.items, hasLength(20));
      expect(state.loadMoreFailure, isA<NetworkFailure>());

      h.catalog.onProducts = null;
      await controller.loadMore();
      state = h.read(listingControllerProvider(base));
      expect(state.items, hasLength(40));
      expect(state.loadMoreFailure, isNull);
      expect(h.catalog.queries.map((q) => q.page), [1, 2, 2]);
    });

    test('filters restart at page 1 with decimal-string bounds', () async {
      final h = buildHarness();
      addTearDown(h.dispose);
      h.catalog.total = 45;
      keep(h, listingControllerProvider(base));
      await settle();
      final controller = h.read(listingControllerProvider(base).notifier);
      await controller.loadMore();
      await controller.applyFilters(
        deliveryType: CatalogDelivery.instantCode,
        minPrice: '5.00',
        maxPrice: '50',
        sort: CatalogSort.priceAsc,
      );
      final query = h.catalog.queries.last;
      expect(query.page, 1);
      expect(query.platform, 'orbit');
      expect(query.deliveryType, 'instant_code');
      expect(query.minPrice, '5.00');
      expect(query.maxPrice, '50');
      expect(query.sort, 'price_asc');
      expect(
        activeFilterCount(h.read(listingControllerProvider(base)).query),
        3,
      );

      await controller.clearFilters();
      final cleared = h.catalog.queries.last;
      expect(cleared.deliveryType, isNull);
      expect(cleared.minPrice, isNull);
      expect(cleared.sort, 'price_asc', reason: 'sort is not a filter');
    });

    test('first-page failure and empty results', () async {
      final h = buildHarness();
      addTearDown(h.dispose);
      h.catalog.onProducts = (_) => const Err(serverError);
      keep(h, listingControllerProvider(base));
      await settle();
      expect(h.read(listingControllerProvider(base)).failure, serverError);

      h.catalog
        ..onProducts = null
        ..total = 0;
      await h.read(listingControllerProvider(base).notifier).retry();
      expect(h.read(listingControllerProvider(base)).isEmpty, isTrue);
    });

    test('price bounds are validated as decimals', () {
      expect(validatePriceRange('', ''), isNull);
      expect(validatePriceRange('5', '50.5'), isNull);
      expect(validatePriceRange('5.123', ''), PriceInputError.invalidMin);
      expect(validatePriceRange('', '-1'), PriceInputError.invalidMax);
      expect(validatePriceRange('abc', ''), PriceInputError.invalidMin);
      expect(validatePriceRange('10', '9.99'), PriceInputError.range);
      expect(validatePriceRange('10.10', '10.1'), isNull);
    });

    test('listing title uses translated names, then the slug', () async {
      final h = buildHarness();
      addTearDown(h.dispose);
      final provider = listingTitleProvider(
        category: 'gift-cards',
        platform: 'orbit',
      );
      keep(h, provider);
      expect(h.read(provider), 'gift-cards · orbit');
      await settle();
      expect(h.read(provider), 'Gift Cards · Orbit');
    });

    test('categories group under their parents', () {
      final groups = groupCategories(fixtureCategories());
      expect(groups.single.parent.slug, 'gift-cards');
      expect(groups.single.children.single.slug, 'top-ups');
    });
  });

  group('search', () {
    test('suggestions are debounced; short queries send nothing', () async {
      final h = buildHarness();
      addTearDown(h.dispose);
      keep(h, storeSearchControllerProvider);
      final controller = h.read(storeSearchControllerProvider.notifier);
      await settle();
      expect(h.read(storeSearchControllerProvider).popular.valueOrNull, [
        'orbit',
        'nova points',
      ]);

      controller.onQueryChanged('o');
      expect(h.read(storeSearchControllerProvider).suggestions, isNull);
      controller
        ..onQueryChanged('or')
        ..onQueryChanged('orb');
      await Future<void>.delayed(StoreSearchController.debounce * 2);
      await settle();
      expect(h.search.suggestCalls, [(query: 'orb', commit: false)]);
      final state = h.read(storeSearchControllerProvider);
      expect(state.suggestions!.valueOrNull!.map((s) => s.title), [
        'Orbit',
        'Orbit Card',
      ]);
      expect(state.history, isEmpty, reason: 'typing is not a search');
    });

    test(
      'submit commits the term and records it in the local history',
      () async {
        final h = buildHarness();
        addTearDown(h.dispose);
        keep(h, storeSearchControllerProvider);
        h.search.onSuggest = (_) => const Ok([]);
        final controller = h.read(storeSearchControllerProvider.notifier);
        await controller.submit('  zelda ');
        final state = h.read(storeSearchControllerProvider);
        expect(h.search.suggestCalls.last, (query: 'zelda', commit: true));
        expect(state.noResults, isTrue);
        expect(state.history, ['zelda']);
        expect(h.prefs.values[PreferencesStore.searchHistoryKey], '["zelda"]');
        expect(h.secure, isEmpty, reason: 'history is not secure storage');
      },
    );

    test(
      'history is newest first, deduplicated, capped, removable and clearable',
      () async {
        final h = buildHarness();
        addTearDown(h.dispose);
        final store = h.read(searchHistoryStoreProvider);
        for (var n = 1; n <= 12; n++) {
          await store.add('term $n');
        }
        await store.add('TERM 5');
        final history = store.read();
        expect(history, hasLength(SearchHistoryStore.capacity));
        expect(history.first, 'TERM 5');
        expect(history.where((t) => t.toLowerCase() == 'term 5'), hasLength(1));
        expect(history, isNot(contains('term 1')));

        expect(await store.remove('term 12'), isNot(contains('term 12')));
        await store.clear();
        expect(store.read(), isEmpty);
        expect(
          h.prefs.values.containsKey(PreferencesStore.searchHistoryKey),
          isFalse,
        );
      },
    );

    test('a corrupt stored history reads as empty', () {
      final h = buildHarness(
        preferences: const {PreferencesStore.searchHistoryKey: '{not json'},
      );
      addTearDown(h.dispose);
      expect(h.read(searchHistoryStoreProvider).read(), isEmpty);
    });
  });

  group('link targets (LinkTargetResolver)', () {
    LinkTarget link(String type, String value) => LinkTarget(
      type: type,
      value: value,
      url: Uri.parse('https://boxcards.test/x'),
    );

    test('map to app locations or the browser', () {
      expect(
        destinationFor(link('product', 'orbit-card')),
        const InAppDestination('/products/orbit-card'),
      );
      expect(
        destinationFor(link('category', 'gift-cards')),
        InAppDestination(Routes.listingLocation(category: 'gift-cards')),
      );
      expect(
        destinationFor(link('platform', 'orbit')),
        const InAppDestination('/browse/products?platform=orbit'),
      );
      expect(
        destinationFor(link('search', 'steam wallet')),
        const InAppDestination('/search?q=steam+wallet'),
      );
      expect(
        destinationFor(link('url', 'https://example.com/promo')),
        ExternalDestination(Uri.parse('https://example.com/promo')),
      );
      expect(
        destinationFor(link('order', '01JF')),
        const InAppDestination('/orders'),
      );
    });

    test('unsafe or unknown targets lead nowhere', () {
      expect(destinationFor(link('url', 'http://example.com')), isNull);
      expect(destinationFor(link('url', 'javascript:alert(1)')), isNull);
      expect(destinationFor(link('video', 'x')), isNull);
      expect(destinationFor(link('product', '  ')), isNull);
    });

    test('suggestions open the product, the listing or a text search', () {
      expect(
        suggestionLocation(
          type: 'product',
          slug: 'orbit-card',
          title: 'Orbit Card',
        ),
        '/products/orbit-card',
      );
      expect(
        suggestionLocation(
          type: 'gaming_platform',
          slug: 'orbit',
          title: 'Orbit',
        ),
        '/browse/products?platform=orbit',
      );
      expect(
        suggestionLocation(type: 'category', slug: 'top-ups', title: 'Top-ups'),
        '/browse/products?category=top-ups',
      );
      expect(
        suggestionLocation(type: 'attribute', slug: 'x', title: 'Zelda'),
        '/search?q=Zelda',
      );
    });
  });
}
