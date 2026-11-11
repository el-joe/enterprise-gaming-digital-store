import 'package:application/core/network/failure.dart';
import 'package:application/core/network/interceptors/currency_interceptor.dart';
import 'package:application/core/network/interceptors/locale_interceptor.dart';
import 'package:application/features/app_config/data/datasources/app_config_remote_data_source.dart';
import 'package:application/features/app_config/data/repositories/app_config_repository_impl.dart';
import 'package:application/features/catalog/data/datasources/catalog_remote_data_source.dart';
import 'package:application/features/catalog/data/repositories/catalog_repository_impl.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/features/home/data/datasources/home_remote_data_source.dart';
import 'package:application/features/home/data/repositories/home_repository_impl.dart';
import 'package:application/features/home/domain/entities/home.dart';
import 'package:application/features/product/data/datasources/product_remote_data_source.dart';
import 'package:application/features/product/data/repositories/product_repository_impl.dart';
import 'package:application/features/search/data/datasources/search_remote_data_source.dart';
import 'package:application/features/search/data/repositories/search_repository_impl.dart';
import 'package:decimal/decimal.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/repo_harness.dart';

void main() {
  group('AppConfigRepository', () {
    test('reads the public configuration and the update verdict', () async {
      final h = Harness()..serveFixture('app_config/config_ok');
      final repo = AppConfigRepositoryImpl(AppConfigRemoteDataSource(h.dio));

      final config = okValue(
        await repo.fetch(platform: 'android', version: '1.1.0'),
      );

      expect(config.update?.required, isTrue);
      expect(config.versions['android']?.minSupportedVersion, '1.2.0');
      expect(config.features.checkoutEnabled, isTrue);
      expect(config.features.guestCheckoutEnabled, isFalse);
      expect(config.features.webCheckoutRedirect, isFalse);
      expect(
        config.links.webCheckout,
        Uri.parse('https://boxcards.test/checkout'),
      );
      expect(config.stripePublishableKey, 'pk_test_fixture');
      expect(config.isGatewayActive('paypal'), isFalse);
      expect(
        config.links.terms,
        Uri.parse('https://boxcards.test/pages/terms'),
      );
      expect(config.currencies.single.info.exponent, 2);
    });

    test('reads the Arabic configuration without an update verdict', () async {
      final h = Harness(languageCode: 'ar')
        ..serveFixture('app_config/config_ar');
      final repo = AppConfigRepositoryImpl(AppConfigRemoteDataSource(h.dio));

      final config = okValue(await repo.fetch());

      expect(config.update, isNull);
      expect(h.adapter.sentHeaders.single[LocaleInterceptor.header], 'ar');
    });

    test('maps a bad version to a validation failure', () async {
      final h = Harness()..serveFixture('app_config/config_validation_failed');
      final repo = AppConfigRepositoryImpl(AppConfigRemoteDataSource(h.dio));

      final failure = failureOf<ValidationFailure>(
        await repo.fetch(platform: 'windows', version: '1'),
      );
      expect(failure.fieldErrors, isNotEmpty);
    });
  });

  group('HomeRepository', () {
    for (final locale in ['en', 'ar']) {
      test('reads sections and banners ($locale)', () async {
        final h = Harness(languageCode: locale)
          ..serveFixture('home/home_$locale');
        final repo = HomeRepositoryImpl(HomeRemoteDataSource(h.dio));

        final home = okValue(await repo.fetch());

        expect(home.displayCurrency, 'USD');
        expect(home.heroBanners.single.link?.type, 'product');
        expect(home.promoBanners.single.mobileImage, isNull);
        final offers = home.sections.first;
        expect(offers.items.map((item) => item.runtimeType), [
          HomeProductItem,
          HomePlatformItem,
          HomeCategoryItem,
          HomeFaqItem,
        ]);
        final product = (offers.items.first as HomeProductItem).product;
        expect(product.priceFrom?.amountText, '10.00');
        expect(product.name, locale == 'ar' ? 'بطاقة أوربت' : 'Orbit Card');
        expect(home.sections[2].items.single, isA<HomeReviewItem>());
        expect(home.sections.last.items, isEmpty);
      });
    }
  });

  group('CatalogRepository', () {
    CatalogRepositoryImpl repoOn(Harness h) =>
        CatalogRepositoryImpl(CatalogRemoteDataSource(h.dio));

    for (final locale in ['en', 'ar']) {
      test(
        'lists products, platforms, categories and countries ($locale)',
        () async {
          final h = Harness(languageCode: locale);
          final repo = repoOn(h);

          h.serveFixture('catalog/products_$locale');
          final page = okValue(
            await repo.products(const CatalogQuery(sort: 'popular')),
          );
          expect(page.products, hasLength(2));
          expect(page.page.hasNextPage, isFalse);
          expect(page.products.first.priceFrom!.amount, Decimal.parse('10'));

          h.serveFixture('catalog/platforms_$locale');
          final platforms = okValue(await repo.platforms());
          expect(platforms.first.logo?.url.isAbsolute, isTrue);

          h.serveFixture('catalog/categories_$locale');
          final categories = okValue(await repo.categories());
          expect(categories.last.parent, 'gift-cards');

          h.serveFixture('catalog/countries_$locale');
          final countries = okValue(await repo.countries());
          expect(countries.single.code, 'US');
        },
      );
    }

    test('prices the listing in the X-Currency currency', () async {
      final h = Harness(currency: 'EGP')
        ..serveFixture('catalog/products_filtered_egp');

      final page = okValue(
        await repoOn(h)
            .products(const CatalogQuery(platform: 'orbit', sort: 'price_asc')),
      );

      expect(page.displayCurrency, 'EGP');
      expect(page.products.single.priceFrom?.currency, 'EGP');
      expect(h.adapter.sentHeaders.single[CurrencyInterceptor.header], 'EGP');
    });

    test('lists the active display currencies', () async {
      final h = Harness()..serveFixture('catalog/currencies');
      final currencies = okValue(await repoOn(h).currencies());
      expect(currencies.map((c) => c.info.code), ['EGP', 'USD']);
    });

    test('maps an inactive X-Currency to a validation failure', () async {
      final h = Harness(currency: 'XXX')
        ..serveFixture('catalog/currency_invalid');
      final failure = failureOf<ValidationFailure>(
        await repoOn(h).products(const CatalogQuery()),
      );
      expect(failure.firstErrorFor('currency'), isNotNull);
    });

    test('maps an unknown sort to a validation failure', () async {
      final h = Harness()..serveFixture('catalog/products_validation_failed');
      failureOf<ValidationFailure>(
        await repoOn(h).products(const CatalogQuery(sort: 'cheapest')),
      );
    });
  });

  group('ProductRepository', () {
    ProductRepositoryImpl repoOn(Harness h) =>
        ProductRepositoryImpl(ProductRemoteDataSource(h.dio));

    for (final locale in ['en', 'ar']) {
      test('reads the detail, variants and reviews ($locale)', () async {
        final h = Harness(languageCode: locale);
        final repo = repoOn(h);

        h.serveFixture('product/detail_$locale');
        final detail = okValue(await repo.detail('orbit-card'));
        expect(detail.card.slug, 'orbit-card');
        expect(detail.deliveryFields.single.key, 'player_id');
        expect(detail.deliveryFields.single.required, isTrue);
        expect(detail.rating.average, '4.50');
        expect(detail.variantsSummary.count, 2);

        h.serveFixture('product/variants_$locale');
        final variants = okValue(
          await repo.variants('orbit-card', country: 'US'),
        );
        expect(variants.variants.first.price?.amountText, '10.00');
        expect(variants.variants.last.denominationValue, '25.000000');

        h.serveFixture('product/reviews_$locale');
        final reviews = okValue(await repo.reviews('orbit-card', perPage: 10));
        expect(reviews.ratingCount, 2);
        expect(reviews.reviews, hasLength(2));
      });
    }

    test('maps an unknown product to not found', () async {
      final h = Harness()..serveFixture('product/detail_not_found');
      failureOf<NotFoundFailure>(await repoOn(h).detail('no-such-card'));
    });
  });

  group('SearchRepository', () {
    SearchRepositoryImpl repoOn(Harness h) =>
        SearchRepositoryImpl(SearchRemoteDataSource(h.dio));

    test('suggests and lists popular terms', () async {
      final h = Harness()..serveFixture('search/suggest');
      final suggestions = okValue(await repoOn(h).suggest('orb'));
      expect(suggestions.map((s) => s.slug), ['orbit', 'orbit-card']);

      h.serveFixture('search/popular');
      expect(okValue(await repoOn(h).popular()), ['orbit', 'nova points']);
    });

    test('maps a short query to a validation failure', () async {
      final h = Harness()..serveFixture('search/suggest_validation_failed');
      failureOf<ValidationFailure>(await repoOn(h).suggest('o'));
    });
  });
}
