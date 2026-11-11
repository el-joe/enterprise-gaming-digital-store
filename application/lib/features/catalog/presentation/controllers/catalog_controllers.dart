import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/pricing_context.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/catalog/catalog_providers.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:decimal/decimal.dart';
import 'package:flutter/foundation.dart' show immutable;
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'catalog_controllers.g.dart';

/// Platforms (04.1, 04.8), translated by `Accept-Language`.
@riverpod
class PlatformsController extends _$PlatformsController {
  @override
  Loadable<List<Platform>> build() {
    ref.watch(appLocaleProvider);
    unawaited(_load());
    return const Loadable.loading();
  }

  Future<void> retry() async {
    state = const Loadable.loading();
    await _load();
  }

  Future<void> _load() async {
    final result = await ref.read(catalogRepositoryProvider).platforms();
    if (ref.mounted) {
      state = result.fold(Loadable.loaded, Loadable.failed);
    }
  }
}

/// Categories (04.8), parents first as the API sends them.
@riverpod
class CategoriesController extends _$CategoriesController {
  @override
  Loadable<List<Category>> build() {
    ref.watch(appLocaleProvider);
    unawaited(_load());
    return const Loadable.loading();
  }

  Future<void> retry() async {
    state = const Loadable.loading();
    await _load();
  }

  Future<void> _load() async {
    final result = await ref.read(catalogRepositoryProvider).categories();
    if (ref.mounted) {
      state = result.fold(Loadable.loaded, Loadable.failed);
    }
  }
}

/// A top-level category with its children, for the Browse tab.
typedef CategoryGroup = ({Category parent, List<Category> children});

/// Groups [categories] under their parents; children whose parent is missing become groups.
List<CategoryGroup> groupCategories(List<Category> categories) {
  final slugs = {for (final category in categories) category.slug};
  return [
    for (final category in categories)
      if (category.parent == null || !slugs.contains(category.parent))
        (
          parent: category,
          children: [
            for (final child in categories)
              if (child.parent == category.slug) child,
          ],
        ),
  ];
}

/// The listing title: category and platform names joined with " · " (04.2), from the loaded
/// taxonomies; the slug until they load.
@riverpod
String listingTitle(Ref ref, {String? category, String? platform}) {
  String? find<T>(
    Loadable<List<T>> loadable,
    String slug,
    String Function(T) slugOf,
    String Function(T) nameOf,
  ) {
    for (final item in loadable.valueOrNull ?? <T>[]) {
      if (slugOf(item) == slug) {
        return nameOf(item);
      }
    }
    return null;
  }

  final parts = <String>[
    if (category != null)
      find<Category>(
            ref.watch(categoriesControllerProvider),
            category,
            (c) => c.slug,
            (c) => c.name,
          ) ??
          category,
    if (platform != null)
      find<Platform>(
            ref.watch(platformsControllerProvider),
            platform,
            (p) => p.slug,
            (p) => p.name,
          ) ??
          platform,
  ];
  return parts.join(' · ');
}

/// Sort keys of `GET /storefront/catalog/products`.
abstract final class CatalogSort {
  static const popular = 'popular';
  static const priceAsc = 'price_asc';
  static const priceDesc = 'price_desc';
  static const newest = 'newest';
  static const name = 'name';

  static const all = [popular, priceAsc, priceDesc, newest, name];
}

/// Delivery types offered as filters (the server accepts every `DeliveryType`).
abstract final class CatalogDelivery {
  static const instantCode = 'instant_code';
  static const directTopup = 'direct_topup';

  static const filters = [instantCode, directTopup];
}

/// A price bound as typed: empty, or a non-negative decimal with at most 2 fraction digits.
/// Bounds are sent as decimal strings in the display currency, never as numbers.
final _priceInput = RegExp(r'^\d{1,10}(\.\d{1,2})?$');

bool isValidPriceInput(String text) =>
    text.trim().isEmpty || _priceInput.hasMatch(text.trim());

/// Null for an empty bound, else the trimmed text.
String? priceBound(String text) => text.trim().isEmpty ? null : text.trim();

/// Why typed price bounds cannot be applied.
enum PriceInputError { invalidMin, invalidMax, range }

/// Checks the typed bounds as decimals (never floating point): each empty or a valid amount,
/// and the minimum not above the maximum.
PriceInputError? validatePriceRange(String min, String max) {
  if (!isValidPriceInput(min)) {
    return PriceInputError.invalidMin;
  }
  if (!isValidPriceInput(max)) {
    return PriceInputError.invalidMax;
  }
  final low = priceBound(min);
  final high = priceBound(max);
  if (low != null && high != null && Decimal.parse(low) > Decimal.parse(high)) {
    return PriceInputError.range;
  }
  return null;
}

/// Number of active filters shown on the "Filters · n" chip (sort has its own chip).
int activeFilterCount(CatalogQuery query) => [
  query.deliveryType,
  query.region,
  query.minPrice,
  query.maxPrice,
].where((value) => value != null).length;

/// State of a product listing with infinite scroll (04.2, 04.4, 04.5).
@immutable
final class ListingState {
  const ListingState({
    required this.query,
    this.items = const [],
    this.page,
    this.loading = true,
    this.failure,
    this.loadingMore = false,
    this.loadMoreFailure,
    this.displayCurrency,
  });

  /// Filters in force, including the base category or platform.
  final CatalogQuery query;
  final List<ProductCard> items;

  /// Meta of the last page loaded.
  final PageInfo? page;

  /// The first page is loading.
  final bool loading;

  /// The first page failed.
  final Failure? failure;
  final bool loadingMore;

  /// The next page failed (04.5 snackbar with retry); loaded items stay.
  final Failure? loadMoreFailure;

  /// `meta.display_currency` of the last page.
  final String? displayCurrency;

  bool get hasMore => page?.hasNextPage ?? false;

  bool get isEmpty => !loading && failure == null && items.isEmpty;

  int get total => page?.total ?? items.length;

  ListingState copyWith({
    CatalogQuery? query,
    List<ProductCard>? items,
    PageInfo? page,
    bool? loading,
    Failure? Function()? failure,
    bool? loadingMore,
    Failure? Function()? loadMoreFailure,
    String? displayCurrency,
  }) => ListingState(
    query: query ?? this.query,
    items: items ?? this.items,
    page: page ?? this.page,
    loading: loading ?? this.loading,
    failure: failure == null ? this.failure : failure(),
    loadingMore: loadingMore ?? this.loadingMore,
    loadMoreFailure: loadMoreFailure == null
        ? this.loadMoreFailure
        : loadMoreFailure(),
    displayCurrency: displayCurrency ?? this.displayCurrency,
  );
}

/// The listing for a base query (category, platform, sort from the route). Pages are fetched
/// with `page` and `per_page`; [loadMore] appends the next page while `meta` says one exists.
/// A currency or language change ([pricingContextProvider]) starts again from page 1.
@riverpod
class ListingController extends _$ListingController {
  int _generation = 0;

  @override
  ListingState build(CatalogQuery base) {
    ref.watch(pricingContextProvider);
    final query = base.copyWith(page: 1);
    unawaited(_loadFirst(query, reset: false));
    return ListingState(query: query);
  }

  /// Applies filters from the sheet; the base category and platform are kept.
  Future<void> applyFilters({
    String? deliveryType,
    String? minPrice,
    String? maxPrice,
    String? sort,
  }) {
    final query = state.query.copyWith(
      deliveryType: deliveryType,
      minPrice: minPrice,
      maxPrice: maxPrice,
      sort: sort,
      page: 1,
    );
    return _loadFirst(query);
  }

  Future<void> clearFilters() => _loadFirst(
    state.query.copyWith(
      deliveryType: null,
      region: null,
      minPrice: null,
      maxPrice: null,
      page: 1,
    ),
  );

  Future<void> retry() => _loadFirst(state.query.copyWith(page: 1));

  /// Loads the next page unless one is loading, the last page is reached or the first page is
  /// not loaded.
  Future<void> loadMore() async {
    final current = state;
    if (current.loading ||
        current.loadingMore ||
        current.failure != null ||
        !current.hasMore) {
      return;
    }
    final generation = _generation;
    final next = current.query.copyWith(
      page: (current.page?.currentPage ?? 1) + 1,
    );
    state = current.copyWith(loadingMore: true, loadMoreFailure: () => null);
    final result = await ref.read(catalogRepositoryProvider).products(next);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = switch (result) {
      Ok(:final value) => state.copyWith(
        items: [...state.items, ...value.products],
        page: value.page,
        loadingMore: false,
        displayCurrency: value.displayCurrency,
      ),
      Err(:final failure) => state.copyWith(
        loadingMore: false,
        loadMoreFailure: () => failure,
      ),
    };
  }

  Future<void> _loadFirst(CatalogQuery query, {bool reset = true}) async {
    final generation = ++_generation;
    if (reset) {
      state = ListingState(query: query);
    }
    final result = await ref.read(catalogRepositoryProvider).products(query);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = switch (result) {
      Ok(:final value) => ListingState(
        query: query,
        items: value.products,
        page: value.page,
        loading: false,
        displayCurrency: value.displayCurrency,
      ),
      Err(:final failure) => ListingState(
        query: query,
        loading: false,
        failure: failure,
      ),
    };
  }
}
