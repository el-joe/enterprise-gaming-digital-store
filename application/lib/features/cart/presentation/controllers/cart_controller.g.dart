// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'cart_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The cart tab: reads the cart, changes quantities, removes lines, applies and removes a coupon.
/// Totals are always the server's. Every write updates the Cart tab badge
/// ([CartCount.updateFrom]). The display currency, the language, the session (sign-in adopts the
/// guest cart) and [CartRevision] (an order was placed) reload it.

@ProviderFor(CartController)
final cartControllerProvider = CartControllerProvider._();

/// The cart tab: reads the cart, changes quantities, removes lines, applies and removes a coupon.
/// Totals are always the server's. Every write updates the Cart tab badge
/// ([CartCount.updateFrom]). The display currency, the language, the session (sign-in adopts the
/// guest cart) and [CartRevision] (an order was placed) reload it.
final class CartControllerProvider
    extends $NotifierProvider<CartController, CartPageState> {
  /// The cart tab: reads the cart, changes quantities, removes lines, applies and removes a coupon.
  /// Totals are always the server's. Every write updates the Cart tab badge
  /// ([CartCount.updateFrom]). The display currency, the language, the session (sign-in adopts the
  /// guest cart) and [CartRevision] (an order was placed) reload it.
  CartControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'cartControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$cartControllerHash();

  @$internal
  @override
  CartController create() => CartController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(CartPageState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<CartPageState>(value),
    );
  }
}

String _$cartControllerHash() => r'36407cc69104d364adf334652c1278c9d5611db4';

/// The cart tab: reads the cart, changes quantities, removes lines, applies and removes a coupon.
/// Totals are always the server's. Every write updates the Cart tab badge
/// ([CartCount.updateFrom]). The display currency, the language, the session (sign-in adopts the
/// guest cart) and [CartRevision] (an order was placed) reload it.

abstract class _$CartController extends $Notifier<CartPageState> {
  CartPageState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<CartPageState, CartPageState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<CartPageState, CartPageState>,
              CartPageState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
