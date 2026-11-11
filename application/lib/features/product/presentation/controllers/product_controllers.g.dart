// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'product_controllers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The product page for a slug: loads the detail and the variant offers together, holds the
/// region and amount selection, the quantity and the delivery-field form, and adds to the cart
/// (`POST /storefront/cart/items`; a guest's `X-Cart-Token` is captured and sent back by the
/// cart token interceptor). A display currency or language change reloads it.

@ProviderFor(ProductController)
final productControllerProvider = ProductControllerFamily._();

/// The product page for a slug: loads the detail and the variant offers together, holds the
/// region and amount selection, the quantity and the delivery-field form, and adds to the cart
/// (`POST /storefront/cart/items`; a guest's `X-Cart-Token` is captured and sent back by the
/// cart token interceptor). A display currency or language change reloads it.
final class ProductControllerProvider
    extends $NotifierProvider<ProductController, ProductPageState> {
  /// The product page for a slug: loads the detail and the variant offers together, holds the
  /// region and amount selection, the quantity and the delivery-field form, and adds to the cart
  /// (`POST /storefront/cart/items`; a guest's `X-Cart-Token` is captured and sent back by the
  /// cart token interceptor). A display currency or language change reloads it.
  ProductControllerProvider._({
    required ProductControllerFamily super.from,
    required String super.argument,
  }) : super(
         retry: null,
         name: r'productControllerProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$productControllerHash();

  @override
  String toString() {
    return r'productControllerProvider'
        ''
        '($argument)';
  }

  @$internal
  @override
  ProductController create() => ProductController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ProductPageState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ProductPageState>(value),
    );
  }

  @override
  bool operator ==(Object other) {
    return other is ProductControllerProvider && other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$productControllerHash() => r'49cf37c58a41d557cf91b20d6cbfaf43a9fdcb2d';

/// The product page for a slug: loads the detail and the variant offers together, holds the
/// region and amount selection, the quantity and the delivery-field form, and adds to the cart
/// (`POST /storefront/cart/items`; a guest's `X-Cart-Token` is captured and sent back by the
/// cart token interceptor). A display currency or language change reloads it.

final class ProductControllerFamily extends $Family
    with
        $ClassFamilyOverride<
          ProductController,
          ProductPageState,
          ProductPageState,
          ProductPageState,
          String
        > {
  ProductControllerFamily._()
    : super(
        retry: null,
        name: r'productControllerProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// The product page for a slug: loads the detail and the variant offers together, holds the
  /// region and amount selection, the quantity and the delivery-field form, and adds to the cart
  /// (`POST /storefront/cart/items`; a guest's `X-Cart-Token` is captured and sent back by the
  /// cart token interceptor). A display currency or language change reloads it.

  ProductControllerProvider call(String slug) =>
      ProductControllerProvider._(argument: slug, from: this);

  @override
  String toString() => r'productControllerProvider';
}

/// The product page for a slug: loads the detail and the variant offers together, holds the
/// region and amount selection, the quantity and the delivery-field form, and adds to the cart
/// (`POST /storefront/cart/items`; a guest's `X-Cart-Token` is captured and sent back by the
/// cart token interceptor). A display currency or language change reloads it.

abstract class _$ProductController extends $Notifier<ProductPageState> {
  late final _$args = ref.$arg as String;
  String get slug => _$args;

  ProductPageState build(String slug);
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<ProductPageState, ProductPageState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<ProductPageState, ProductPageState>,
              ProductPageState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, () => build(_$args));
  }
}

/// Reviews of a product, 10 per page, appended by [loadMore] while `meta` has more pages.
/// Writing a review is M-A17.

@ProviderFor(ProductReviewsController)
final productReviewsControllerProvider = ProductReviewsControllerFamily._();

/// Reviews of a product, 10 per page, appended by [loadMore] while `meta` has more pages.
/// Writing a review is M-A17.
final class ProductReviewsControllerProvider
    extends $NotifierProvider<ProductReviewsController, ReviewsState> {
  /// Reviews of a product, 10 per page, appended by [loadMore] while `meta` has more pages.
  /// Writing a review is M-A17.
  ProductReviewsControllerProvider._({
    required ProductReviewsControllerFamily super.from,
    required String super.argument,
  }) : super(
         retry: null,
         name: r'productReviewsControllerProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$productReviewsControllerHash();

  @override
  String toString() {
    return r'productReviewsControllerProvider'
        ''
        '($argument)';
  }

  @$internal
  @override
  ProductReviewsController create() => ProductReviewsController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ReviewsState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ReviewsState>(value),
    );
  }

  @override
  bool operator ==(Object other) {
    return other is ProductReviewsControllerProvider &&
        other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$productReviewsControllerHash() =>
    r'502e36ae231399b90993897b5c937f89dad03f4b';

/// Reviews of a product, 10 per page, appended by [loadMore] while `meta` has more pages.
/// Writing a review is M-A17.

final class ProductReviewsControllerFamily extends $Family
    with
        $ClassFamilyOverride<
          ProductReviewsController,
          ReviewsState,
          ReviewsState,
          ReviewsState,
          String
        > {
  ProductReviewsControllerFamily._()
    : super(
        retry: null,
        name: r'productReviewsControllerProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// Reviews of a product, 10 per page, appended by [loadMore] while `meta` has more pages.
  /// Writing a review is M-A17.

  ProductReviewsControllerProvider call(String slug) =>
      ProductReviewsControllerProvider._(argument: slug, from: this);

  @override
  String toString() => r'productReviewsControllerProvider';
}

/// Reviews of a product, 10 per page, appended by [loadMore] while `meta` has more pages.
/// Writing a review is M-A17.

abstract class _$ProductReviewsController extends $Notifier<ReviewsState> {
  late final _$args = ref.$arg as String;
  String get slug => _$args;

  ReviewsState build(String slug);
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<ReviewsState, ReviewsState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<ReviewsState, ReviewsState>,
              ReviewsState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, () => build(_$args));
  }
}
