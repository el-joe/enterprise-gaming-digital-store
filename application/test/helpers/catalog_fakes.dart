import 'dart:async';

import 'package:application/core/money/money.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/features/catalog/data/models/catalog_models.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/features/catalog/domain/repositories/catalog_repository.dart';
import 'package:application/features/home/data/models/home_models.dart';
import 'package:application/features/home/domain/entities/home.dart';
import 'package:application/features/home/domain/repositories/home_repository.dart';
import 'package:application/features/search/data/models/search_models.dart';
import 'package:application/features/search/domain/entities/search.dart';
import 'package:application/features/search/domain/repositories/search_repository.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:flutter/foundation.dart' show SynchronousFuture, immutable;
import 'package:flutter/painting.dart';

import 'fixtures.dart';

/// `home/home_<lang>` decoded through the real DTO.
HomePage fixtureHome([String language = 'en']) {
  final fixture = ApiFixture.load('home/home_$language');
  final meta = (fixture.body! as Map<String, dynamic>)['meta'] as Map;
  return HomeDto.fromJson(fixture.dataMap)
      .toEntity(displayCurrency: meta['display_currency'] as String);
}

/// The product cards of `catalog/products_<lang>`.
List<ProductCard> fixtureCards([String language = 'en']) => [
  for (final json
      in ApiFixture.load('catalog/products_$language').data! as List)
    ProductCardDto.fromJson(json as Map<String, dynamic>).toEntity(),
];

List<Platform> fixturePlatforms([String language = 'en']) => [
  for (final json
      in ApiFixture.load('catalog/platforms_$language').dataMap['platforms']
          as List)
    PlatformDto.fromJson(json as Map<String, dynamic>).toEntity(),
];

List<Category> fixtureCategories([String language = 'en']) => [
  for (final json
      in ApiFixture.load('catalog/categories_$language').dataMap['categories']
          as List)
    CategoryDto.fromJson(json as Map<String, dynamic>).toEntity(),
];

List<DisplayCurrencyOption> fixtureCurrencies() => [
  for (final json
      in ApiFixture.load('catalog/currencies').dataMap['currencies'] as List)
    DisplayCurrencyDto.fromJson(json as Map<String, dynamic>).toEntity(),
];

List<SearchSuggestion> fixtureSuggestions() => [
  for (final json
      in ApiFixture.load('search/suggest').dataMap['suggestions'] as List)
    SearchSuggestionDto.fromJson(json as Map<String, dynamic>).toEntity(),
];

List<String> fixturePopular() => [
  for (final term in ApiFixture.load('search/popular').dataMap['terms'] as List)
    term as String,
];

/// [count] cards modelled on the fixture's first card: names `Card <n>`, prices in
/// [currency] as server strings with a server `formatted` value.
List<ProductCard> generatedCards(
  int count, {
  int start = 1,
  String currency = 'USD',
}) {
  final template = fixtureCards().first;
  return [
    for (var n = start; n < start + count; n++)
      template.copyWith(
        publicId: 'card-$n',
        name: 'Card $n',
        slug: 'card-$n',
        image: null,
        priceFrom: Money.parse('$n.00', currency, formatted: '$currency$n.00'),
      ),
  ];
}

/// [HomeRepository] answering from [results] in order (the last repeats).
class FakeHomeRepository implements HomeRepository {
  FakeHomeRepository([List<Result<HomePage>>? results])
    : results = results ?? [Ok(fixtureHome())];

  List<Result<HomePage>> results;
  int calls = 0;

  /// When set, each fetch waits for it (to see the loading state).
  Completer<void>? gate;

  @override
  Future<Result<HomePage>> fetch() async {
    calls++;
    await gate?.future;
    final index = calls - 1;
    return results[index < results.length ? index : results.length - 1];
  }
}

/// [CatalogRepository] with a paged catalog of [total] generated cards (or [onProducts]).
class FakeCatalogRepository implements CatalogRepository {
  FakeCatalogRepository({this.total = 2, this.currency = 'USD'});

  int total;
  String currency;
  final queries = <CatalogQuery>[];
  Result<CatalogPage> Function(CatalogQuery query)? onProducts;
  Result<List<Platform>> platformsResult = Ok(fixturePlatforms());
  Result<List<Category>> categoriesResult = Ok(fixtureCategories());
  Result<List<DisplayCurrencyOption>> currenciesResult = Ok(
    fixtureCurrencies(),
  );
  int currencyCalls = 0;

  /// When set, product fetches wait for it.
  Completer<void>? gate;

  @override
  Future<Result<CatalogPage>> products(CatalogQuery query) async {
    queries.add(query);
    await gate?.future;
    final custom = onProducts;
    if (custom != null) {
      return custom(query);
    }
    final first = (query.page - 1) * query.perPage + 1;
    final count = (total - first + 1).clamp(0, query.perPage);
    final lastPage = total == 0
        ? 1
        : (total + query.perPage - 1) ~/ query.perPage;
    return Ok(
      CatalogPage(
        products: generatedCards(count, start: first, currency: currency),
        page: PageInfo(
          currentPage: query.page,
          perPage: query.perPage,
          total: total,
          lastPage: lastPage,
        ),
        displayCurrency: currency,
      ),
    );
  }

  @override
  Future<Result<List<Platform>>> platforms() async => platformsResult;

  @override
  Future<Result<List<Category>>> categories() async => categoriesResult;

  @override
  Future<Result<List<Country>>> countries() async => const Ok([]);

  @override
  Future<Result<List<DisplayCurrencyOption>>> currencies() async {
    currencyCalls++;
    return currenciesResult;
  }
}

class FakeSearchRepository implements SearchRepository {
  final suggestCalls = <({String query, bool commit})>[];
  Result<List<SearchSuggestion>> Function(String query) onSuggest = (_) =>
      Ok(fixtureSuggestions());
  Result<List<String>> popularResult = Ok(fixturePopular());

  @override
  Future<Result<List<SearchSuggestion>>> suggest(
    String query, {
    bool commit = false,
  }) async {
    suggestCalls.add((query: query, commit: commit));
    return onSuggest(query);
  }

  @override
  Future<Result<List<String>>> popular() async => popularResult;
}

/// An image that never finishes loading, so tiles show their token fallback deterministically
/// (no network, no plugin). Every requested URL is recorded in [requested].
class RecordingImages {
  final requested = <Uri>[];

  ImageProvider call(Uri url) {
    requested.add(url);
    return _PendingImage(url);
  }
}

@immutable
class _PendingImage extends ImageProvider<_PendingImage> {
  const _PendingImage(this.url);

  final Uri url;

  @override
  Future<_PendingImage> obtainKey(ImageConfiguration configuration) =>
      SynchronousFuture(this);

  @override
  ImageStreamCompleter loadImage(
    _PendingImage key,
    ImageDecoderCallback decode,
  ) => OneFrameImageStreamCompleter(Completer<ImageInfo>().future);

  @override
  bool operator ==(Object other) => other is _PendingImage && other.url == url;

  @override
  int get hashCode => url.hashCode;
}

const serverError = ServerFailure(
  statusCode: 500,
  requestId: '01JFREQUEST0000000000000RQ',
);
