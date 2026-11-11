import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/cart/cart_providers.dart';
import 'package:application/features/product/domain/delivery_field_rules.dart';
import 'package:application/features/product/domain/entities/product.dart';
import 'package:application/features/product/presentation/controllers/product_controllers.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:flutter_riverpod/misc.dart' show ProviderListenable;
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/catalog_fakes.dart';
import '../../helpers/fixtures.dart';
import '../../helpers/product_fakes.dart';
import '../../helpers/pump_app.dart';

/// Lets queued fake-repository futures complete.
Future<void> settle() async {
  for (var i = 0; i < 5; i++) {
    await Future<void>.delayed(Duration.zero);
  }
}

void keep<T>(TestHarness h, ProviderListenable<T> provider) {
  final sub = h.container.listen(provider, (_, _) {});
  addTearDown(sub.close);
}

const slug = 'orbit-card';

/// The `cart/add_item_validation_failed` fixture as the error mapper builds it.
ValidationFailure fixtureAddValidationFailure() {
  final body =
      ApiFixture.load('cart/add_item_validation_failed').body!
          as Map<String, dynamic>;
  return ValidationFailure(
    {
      for (final entry in (body['errors'] as Map<String, dynamic>).entries)
        entry.key: (entry.value as List).cast<String>(),
    },
    message: body['message'] as String,
    requestId: body['request_id'] as String,
  );
}

void main() {
  Future<(TestHarness, ProductController Function())> loaded({
    void Function(TestHarness h)? arrange,
    bool signedIn = false,
  }) async {
    final h = buildHarness(signedIn: signedIn);
    addTearDown(h.dispose);
    arrange?.call(h);
    keep(h, productControllerProvider(slug));
    keep(h, cartCountProvider);
    await settle();
    return (h, () => h.read(productControllerProvider(slug).notifier));
  }

  ProductPageState stateOf(TestHarness h) =>
      h.read(productControllerProvider(slug));

  group('loading', () {
    test(
      'loads the detail and the variants together and selects the first offer',
      () async {
        final (h, _) = await loaded();
        final state = stateOf(h);
        expect(state.content, isA<Loaded<ProductOffer>>());
        expect(h.product.detailCalls, [slug]);
        expect(h.product.variantCalls, [slug]);
        expect(state.region, 'US');
        expect(state.variantId, fixtureVariants().variants.first.publicId);
        expect(state.showRegions, isTrue);
        expect(state.amounts.map((v) => v.sku), ['ORB-10-US']);
        expect(state.maxQuantity, 10);
        expect(state.canAdd, isTrue);
        expect(state.priceMayVary, isFalse);
        expect(state.unavailable, isFalse);
      },
    );

    test(
      'a 404 or a failed variants call fails the page with its typed failure',
      () async {
        final (h, controller) = await loaded(
          arrange: (h) =>
              h.product.onDetail = (_) => const Err(NotFoundFailure()),
        );
        expect((stateOf(h).content as Failed).failure, isA<NotFoundFailure>());

        h.product
          ..onDetail = ((_) => Ok(fixtureProduct()))
          ..onVariants = ((_) => const Err(serverError));
        await controller().retry();
        expect((stateOf(h).content as Failed).failure, same(serverError));

        h.product.onVariants = (_) => Ok(fixtureVariants());
        await controller().retry();
        expect(stateOf(h).content, isA<Loaded<ProductOffer>>());
      },
    );

    test('a display currency change reloads the product', () async {
      final (h, _) = await loaded();
      await h.read(displayCurrencyProvider.notifier).select('EGP');
      await settle();
      expect(h.product.detailCalls, [slug, slug]);
      expect(h.product.variantCalls, [slug, slug]);
    });

    test(
      'nothing to buy: unavailable, nothing selected, add disabled',
      () async {
        final (h, _) = await loaded(
          arrange: (h) => h.product.onVariants = (_) => Ok(
            ProductVariants(
              variants: [
                offer('a', availability: 'out_of_stock'),
                offer('b', region: 'GL', purchasable: false, price: null),
              ],
              displayCurrency: 'USD',
            ),
          ),
        );
        final state = stateOf(h);
        expect(state.unavailable, isTrue);
        expect(state.variantId, isNull);
        expect(state.canAdd, isFalse);
      },
    );

    test('price_may_vary of the product or of the selected variant', () async {
      final (h, controller) = await loaded(
        arrange: (h) => h.product.onVariants = (_) => Ok(
          ProductVariants(
            variants: [
              offer('a'),
              offer('b', region: 'GL', priceMayVary: true),
            ],
            displayCurrency: 'USD',
          ),
        ),
      );
      expect(stateOf(h).priceMayVary, isFalse);
      controller().selectRegion('GL');
      expect(stateOf(h).priceMayVary, isTrue);

      final (h2, _) = await loaded(
        arrange: (h) => h.product.onDetail = (_) => Ok(
          fixtureProduct().copyWith(
            card: fixtureProduct().card.copyWith(priceMayVary: true),
          ),
        ),
      );
      expect(stateOf(h2).priceMayVary, isTrue);
    });
  });

  group('variant selection', () {
    Future<(TestHarness, ProductController Function())> matrix() => loaded(
      arrange: (h) => h.product.onVariants = (_) => Ok(
        ProductVariants(
          variants: [
            offer('us-10'),
            offer('us-25', title: '25 USD', value: '25.000000'),
            offer(
              'us-50',
              title: '50 USD',
              value: '50.000000',
              availability: 'out_of_stock',
            ),
            offer('gl-10', region: 'GL'),
            offer('gl-25', region: 'GL', title: '25 USD', value: '25.000000'),
          ],
          displayCurrency: 'USD',
        ),
      ),
    );

    test(
      'selects amounts of the region; blocked amounts are refused',
      () async {
        final (h, controller) = await matrix();
        expect(stateOf(h).amounts.map((v) => v.publicId), [
          'us-10',
          'us-25',
          'us-50',
        ]);
        controller().selectVariant('us-25');
        expect(stateOf(h).variantId, 'us-25');
        controller().selectVariant('us-50'); // sold out
        expect(stateOf(h).variantId, 'us-25');
        controller().selectVariant('gl-10'); // other region
        expect(stateOf(h).variantId, 'us-25');
      },
    );

    test('switching region keeps the denomination', () async {
      final (h, controller) = await matrix();
      controller().selectVariant('us-25');
      controller().selectRegion('GL');
      expect(stateOf(h).region, 'GL');
      expect(stateOf(h).variantId, 'gl-25');
      expect(stateOf(h).amounts.map((v) => v.publicId), ['gl-10', 'gl-25']);
    });
  });

  group('quantity', () {
    test('stays between 1 and the per-order limit', () async {
      final (h, controller) = await loaded(
        arrange: (h) =>
            h.product.onDetail = (_) =>
                Ok(fixtureProduct().copyWith(maxQuantityPerOrder: 3)),
      );
      controller().decrement();
      expect(stateOf(h).quantity, 1);
      for (var i = 0; i < 5; i++) {
        controller().increment();
      }
      expect(stateOf(h).quantity, 3);
      controller().setQuantity(0);
      expect(stateOf(h).quantity, 1);
    });
  });

  group('delivery fields', () {
    test('local checks run only after the first add attempt, then as the customer types', () async {
      final (h, controller) = await loaded();
      controller().setValue('player_id', '');
      expect(stateOf(h).fieldIssues, isEmpty);

      expect(await controller().addToCart(), isFalse);
      expect(h.cart.adds, isEmpty);
      expect(stateOf(h).fieldIssues, {
        'player_id': const LocalFieldIssue(DeliveryFieldError.required),
      });

      controller().setValue('player_id', '4817');
      expect(stateOf(h).fieldIssues, isEmpty);
      controller().setValue('player_id', '  ');
      expect(
        stateOf(h).fieldIssues['player_id'],
        const LocalFieldIssue(DeliveryFieldError.required),
      );
    });

    test(
      'number fields store Western digits; unknown keys are ignored',
      () async {
        final number = fixtureProduct().copyWith(
          deliveryFields: [
            const DeliveryField(
              key: 'server_id',
              label: 'Server ID',
              type: DeliveryFieldType.number,
              required: true,
            ),
          ],
        );
        final (h, controller) = await loaded(
          arrange: (h) => h.product.onDetail = (_) => Ok(number),
        );
        controller().setValue('server_id', '١٢٣٤');
        controller().setValue('other', 'x');
        expect(stateOf(h).values, {'server_id': '1234'});
      },
    );
  });

  group('add to cart', () {
    test('success: sends the variant, quantity and trimmed values; updates the cart badge', () async {
      final (h, controller) = await loaded();
      expect(h.read(cartCountProvider), 0);
      controller()
        ..setValue('player_id', '  4817 2290 31 ')
        ..increment();

      expect(await controller().addToCart(), isTrue);
      final add = h.cart.adds.single;
      expect(add.variant, fixtureVariants().variants.first.publicId);
      expect(add.quantity, 2);
      expect(add.deliveryValues, {'player_id': '4817 2290 31'});
      final state = stateOf(h);
      expect(state.addedCount, 1);
      expect(state.adding, isFalse);
      expect(state.addFailure, isNull);
      expect(h.read(cartCountProvider), 1); // the fixture cart has one unit
    });

    test(
      '422 validation_failed: delivery_values.{key} beside the field',
      () async {
        final (h, controller) = await loaded(
          arrange: (h) => h.cart.onAdd = (_) => const Err(
            ValidationFailure({
              'delivery_values.player_id': ['Player ID has an invalid format.'],
            }),
          ),
        );
        controller().setValue('player_id', '48-17');
        expect(await controller().addToCart(), isFalse);
        final state = stateOf(h);
        expect(state.fieldIssues, {
          'player_id': const ServerFieldIssue(
            'Player ID has an invalid format.',
          ),
        });
        expect(state.addFailure, isNull, reason: 'field errors need no banner');
        expect(state.addedCount, 0);

        // Typing clears the server's error for that field.
        controller().setValue('player_id', '4817229031');
        expect(stateOf(h).fieldIssues, isEmpty);
      },
    );

    test('422 fixture (quantity and variant_id): quantity beside the stepper, the rest in the banner', () async {
      final failure = fixtureAddValidationFailure();
      final (h, controller) = await loaded(
        arrange: (h) => h.cart.onAdd = (_) => Err(failure),
      );
      controller().setValue('player_id', '4817');
      expect(await controller().addToCart(), isFalse);
      final state = stateOf(h);
      expect(state.quantityError, 'The quantity field must be at least 1.');
      expect(state.addFailure, same(failure));
      expect(state.addFailureText, 'The variant id field is required.');

      // Changing the quantity clears its error; choosing again clears the banner.
      controller().increment();
      expect(stateOf(h).quantityError, isNull);
      controller().selectRegion('GL');
      expect(stateOf(h).addFailure, isNull);
    });

    test('422 item_unavailable (out of stock) shows the server text', () async {
      final (h, controller) = await loaded(
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
      controller().setValue('player_id', '4817');
      await controller().addToCart();
      expect(stateOf(h).addFailureText, 'This product is out of stock.');
      expect(stateOf(h).addFailure, isA<DomainFailure>());
    });

    test('per-order limit counting the cart (422 quantity)', () async {
      final (h, controller) = await loaded(
        arrange: (h) => h.cart.onAdd = (_) => const Err(
          ValidationFailure({
            'quantity': ['You can buy at most 10 of this product per order.'],
          }),
        ),
      );
      controller().setValue('player_id', '4817');
      await controller().addToCart();
      expect(
        stateOf(h).quantityError,
        'You can buy at most 10 of this product per order.',
      );
      expect(stateOf(h).addFailure, isNull);
    });

    test('offline: the typed failure for the banner, nothing added', () async {
      final (h, controller) = await loaded(
        arrange: (h) => h.cart.onAdd = (_) => const Err(NetworkFailure()),
      );
      controller().setValue('player_id', '4817');
      await controller().addToCart();
      expect(stateOf(h).addFailure, isA<NetworkFailure>());
      expect(stateOf(h).addFailureText, isNull);
      expect(h.read(cartCountProvider), 0);
    });

    test('a second tap while adding is ignored', () async {
      final (h, controller) = await loaded();
      controller().setValue('player_id', '4817');
      final first = controller().addToCart();
      expect(stateOf(h).adding, isTrue);
      expect(stateOf(h).canAdd, isFalse);
      expect(await controller().addToCart(), isFalse);
      expect(await first, isTrue);
      expect(h.cart.adds, hasLength(1));
    });
  });

  group('cart badge count', () {
    test('a guest without a cart token is not asked for a cart', () async {
      final h = buildHarness();
      addTearDown(h.dispose);
      keep(h, cartCountProvider);
      await settle();
      expect(h.cart.getCalls, 0);
      expect(h.read(cartCountProvider), 0);
    });

    test(
      'a guest with a cart token, and a signed-in customer, read the cart',
      () async {
        final h = buildHarness();
        addTearDown(h.dispose);
        h.secure[SecureStore.cartTokenKey] = 'guest-token';
        h.cart.getResult = Ok(
          fixtureCart().copyWith(
            items: [
              fixtureCart().items.single.copyWith(quantity: 2),
              fixtureCart().items.single.copyWith(id: 2, quantity: 3),
            ],
          ),
        );
        keep(h, cartCountProvider);
        await settle();
        expect(h.read(cartCountProvider), 5);
        expect(unitsIn(null), 0);

        // Signing in re-reads (the server adopts the guest cart).
        h.read(sessionProvider.notifier).markSignedIn();
        await settle();
        expect(h.cart.getCalls, 2);
      },
    );

    test('a failed read keeps the last count', () async {
      final h = buildHarness(signedIn: true);
      addTearDown(h.dispose);
      h.cart.getResult = Ok(fixtureCart());
      keep(h, cartCountProvider);
      await settle();
      expect(h.read(cartCountProvider), 1);
      h.cart.getResult = const Err(NetworkFailure());
      await h.read(cartCountProvider.notifier).refresh();
      expect(h.read(cartCountProvider), 1);
    });
  });

  group('reviews', () {
    ProductReviewsPage page(int number, {int last = 3}) {
      final base = fixtureReviews();
      return base.copyWith(
        reviews: [
          for (final r in base.reviews) r.copyWith(id: number * 10 + r.id),
        ],
        page: PageInfo(
          currentPage: number,
          perPage: 10,
          total: last * 2,
          lastPage: last,
        ),
      );
    }

    test(
      'first page with the rating summary, then pages until the last',
      () async {
        final h = buildHarness();
        addTearDown(h.dispose);
        h.product.onReviews = (n) => Ok(page(n));
        keep(h, productReviewsControllerProvider(slug));
        await settle();
        var state = h.read(productReviewsControllerProvider(slug));
        expect(state.loading, isFalse);
        expect(state.ratingAvg, '4.50');
        expect(state.ratingCount, 2);
        expect(state.items, hasLength(2));
        expect(h.product.reviewCalls.single, (
          slug: slug,
          page: 1,
          perPage: 10,
        ));

        final controller = h.read(
          productReviewsControllerProvider(slug).notifier,
        );
        await controller.loadMore();
        await controller.loadMore();
        await controller.loadMore(); // past the last page: nothing
        state = h.read(productReviewsControllerProvider(slug));
        expect(state.items, hasLength(6));
        expect(state.hasMore, isFalse);
        expect(h.product.reviewCalls.map((c) => c.page), [1, 2, 3]);
      },
    );

    test(
      'a failed next page keeps the items and retries only when asked',
      () async {
        final h = buildHarness();
        addTearDown(h.dispose);
        h.product.onReviews = (n) =>
            n == 2 ? const Err(NetworkFailure()) : Ok(page(n));
        keep(h, productReviewsControllerProvider(slug));
        await settle();
        final controller = h.read(
          productReviewsControllerProvider(slug).notifier,
        );
        await controller.loadMore();
        var state = h.read(productReviewsControllerProvider(slug));
        expect(state.items, hasLength(2));
        expect(state.loadMoreFailure, isA<NetworkFailure>());

        h.product.onReviews = (n) => Ok(page(n));
        await controller.loadMore();
        state = h.read(productReviewsControllerProvider(slug));
        expect(state.items, hasLength(4));
        expect(state.loadMoreFailure, isNull);
      },
    );

    test('a failed first page fails, retry loads', () async {
      final h = buildHarness();
      addTearDown(h.dispose);
      h.product.onReviews = (_) => const Err(serverError);
      keep(h, productReviewsControllerProvider(slug));
      await settle();
      expect(
        h.read(productReviewsControllerProvider(slug)).failure,
        same(serverError),
      );
      h.product.onReviews = (_) => Ok(fixtureReviews());
      await h.read(productReviewsControllerProvider(slug).notifier).retry();
      expect(
        h.read(productReviewsControllerProvider(slug)).items,
        hasLength(2),
      );
    });
  });
}
