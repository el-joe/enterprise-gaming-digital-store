import 'dart:async';

import 'package:application/core/money/money.dart';
import 'package:application/core/network/result.dart';
import 'package:application/features/cart/domain/repositories/cart_repository.dart';
import 'package:application/features/product/data/models/product_models.dart';
import 'package:application/features/product/domain/entities/product.dart';
import 'package:application/features/product/domain/repositories/product_repository.dart';
import 'package:application/shared/data/models/cart_models.dart';
import 'package:application/shared/domain/entities/cart.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';

import 'fixtures.dart';

/// `product/detail_<lang>` decoded through the real DTO.
ProductDetail fixtureProduct([String language = 'en']) {
  final fixture = ApiFixture.load('product/detail_$language');
  final meta = (fixture.body! as Map<String, dynamic>)['meta'] as Map;
  return ProductDetailDto.fromJson(
    fixture.dataMap['product'] as Map<String, dynamic>,
  ).toEntity(displayCurrency: meta['display_currency'] as String);
}

/// `product/variants_<lang>`: 10 USD in the US (in stock) and 25 USD global (limited).
ProductVariants fixtureVariants([String language = 'en']) => ProductVariants(
  variants: [
    for (final json
        in ApiFixture.load('product/variants_$language').dataMap['variants']
            as List)
      VariantOfferDto.fromJson(json as Map<String, dynamic>).toEntity(),
  ],
  displayCurrency: 'USD',
);

/// `product/reviews_<lang>`: two reviews, average 4.50.
ProductReviewsPage fixtureReviews([String language = 'en']) {
  final fixture = ApiFixture.load('product/reviews_$language');
  final meta = (fixture.body! as Map<String, dynamic>)['meta'] as Map;
  return ProductReviewsPage(
    reviews: [
      for (final json in fixture.data! as List)
        ProductReviewDto.fromJson(json as Map<String, dynamic>).toEntity(),
    ],
    page: PageInfo(
      currentPage: meta['current_page'] as int,
      perPage: meta['per_page'] as int,
      total: meta['total'] as int,
      lastPage: meta['last_page'] as int,
    ),
    ratingAvg: meta['rating_avg'] as String,
    ratingCount: meta['rating_count'] as int,
  );
}

/// `cart/add_item_created`: one line of quantity 1.
Cart fixtureCart() => CartDto.fromJson(
  ApiFixture.load('cart/add_item_created').dataMap['cart']
      as Map<String, dynamic>,
).toEntity();

/// A variant offer modelled on the fixture's first one.
VariantOffer offer(
  String id, {
  String? region = 'US',
  String title = '10 USD',
  String value = '10.000000',
  String availability = 'in_stock',
  bool purchasable = true,
  bool priceMayVary = false,
  bool customAmount = false,
  String? price = '10.00',
}) => fixtureVariants().variants.first.copyWith(
  publicId: id,
  region: region,
  title: title,
  denominationValue: value,
  availability: availability,
  purchasable: purchasable,
  priceMayVary: priceMayVary,
  isCustomAmount: customAmount,
  price: price == null
      ? null
      : Money.parse(price, 'USD', formatted: 'USD$price'),
);

/// [ProductRepository] answering with the contract fixtures unless told otherwise.
class FakeProductRepository implements ProductRepository {
  Result<ProductDetail> Function(String slug) onDetail = (_) =>
      Ok(fixtureProduct());
  Result<ProductVariants> Function(String slug) onVariants = (_) =>
      Ok(fixtureVariants());

  /// Pages of reviews by page number; missing pages repeat the last result.
  Result<ProductReviewsPage> Function(int page) onReviews = (_) =>
      Ok(fixtureReviews());

  final detailCalls = <String>[];
  final variantCalls = <String>[];
  final reviewCalls = <({String slug, int page, int perPage})>[];

  /// When set, detail and variant fetches wait for it (loading state).
  Completer<void>? gate;

  @override
  Future<Result<ProductDetail>> detail(String slug) async {
    detailCalls.add(slug);
    await gate?.future;
    return onDetail(slug);
  }

  @override
  Future<Result<ProductVariants>> variants(
    String slug, {
    String? country,
  }) async {
    variantCalls.add(slug);
    await gate?.future;
    return onVariants(slug);
  }

  @override
  Future<Result<ProductReviewsPage>> reviews(
    String slug, {
    int page = 1,
    int perPage = 20,
  }) async {
    reviewCalls.add((slug: slug, page: page, perPage: perPage));
    return onReviews(page);
  }
}

/// One recorded add-to-cart call.
typedef AddCall = ({
  String variant,
  int quantity,
  Map<String, String> deliveryValues,
});

/// [CartRepository] for the product page and the cart badge.
class FakeCartRepository implements CartRepository {
  Result<Cart?> getResult = const Ok(null);
  Result<Cart> Function(AddCall call) onAdd = (_) => Ok(fixtureCart());
  final adds = <AddCall>[];
  int getCalls = 0;

  /// When set, add-to-cart waits for it (busy state).
  Completer<void>? addGate;

  @override
  Future<Result<Cart?>> get() async {
    getCalls++;
    return getResult;
  }

  @override
  Future<Result<Cart>> addItem({
    required String variantPublicId,
    required int quantity,
    Map<String, String> deliveryValues = const {},
  }) async {
    final call = (
      variant: variantPublicId,
      quantity: quantity,
      deliveryValues: deliveryValues,
    );
    adds.add(call);
    await addGate?.future;
    return onAdd(call);
  }

  @override
  Future<Result<Cart>> updateItem(int itemId, {required int quantity}) =>
      throw UnimplementedError('M-A13');

  @override
  Future<Result<Cart>> removeItem(int itemId) =>
      throw UnimplementedError('M-A13');

  @override
  Future<Result<Cart>> applyCoupon(String code) =>
      throw UnimplementedError('M-A13');

  @override
  Future<Result<Cart>> removeCoupon() => throw UnimplementedError('M-A13');
}
