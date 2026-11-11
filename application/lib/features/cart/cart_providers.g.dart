// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'cart_providers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(cartRemoteDataSource)
final cartRemoteDataSourceProvider = CartRemoteDataSourceProvider._();

final class CartRemoteDataSourceProvider
    extends
        $FunctionalProvider<
          CartRemoteDataSource,
          CartRemoteDataSource,
          CartRemoteDataSource
        >
    with $Provider<CartRemoteDataSource> {
  CartRemoteDataSourceProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'cartRemoteDataSourceProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$cartRemoteDataSourceHash();

  @$internal
  @override
  $ProviderElement<CartRemoteDataSource> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  CartRemoteDataSource create(Ref ref) {
    return cartRemoteDataSource(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(CartRemoteDataSource value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<CartRemoteDataSource>(value),
    );
  }
}

String _$cartRemoteDataSourceHash() =>
    r'75458299387f71af52b9ff14dcba4b77e73dbf0c';

@ProviderFor(cartRepository)
final cartRepositoryProvider = CartRepositoryProvider._();

final class CartRepositoryProvider
    extends $FunctionalProvider<CartRepository, CartRepository, CartRepository>
    with $Provider<CartRepository> {
  CartRepositoryProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'cartRepositoryProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$cartRepositoryHash();

  @$internal
  @override
  $ProviderElement<CartRepository> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  CartRepository create(Ref ref) {
    return cartRepository(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(CartRepository value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<CartRepository>(value),
    );
  }
}

String _$cartRepositoryHash() => r'77af0ba4b202d244506e16b3a89c752e89c11e89';

/// Number of units in the cart, for the Cart tab badge (M-A12; the cart screen (M-A13) calls
/// [updateFrom] after each write).
///
/// Read from `GET /storefront/cart` when there is a cart to read (a signed-in customer, or a
/// guest holding a cart token), re-read when the session changes (sign-in adopts the guest cart,
/// sign-out leaves it), and set directly from the cart returned by every cart write
/// ([updateFrom]). A failed read keeps the last known count; the badge never blocks anything.

@ProviderFor(CartCount)
final cartCountProvider = CartCountProvider._();

/// Number of units in the cart, for the Cart tab badge (M-A12; the cart screen (M-A13) calls
/// [updateFrom] after each write).
///
/// Read from `GET /storefront/cart` when there is a cart to read (a signed-in customer, or a
/// guest holding a cart token), re-read when the session changes (sign-in adopts the guest cart,
/// sign-out leaves it), and set directly from the cart returned by every cart write
/// ([updateFrom]). A failed read keeps the last known count; the badge never blocks anything.
final class CartCountProvider extends $NotifierProvider<CartCount, int> {
  /// Number of units in the cart, for the Cart tab badge (M-A12; the cart screen (M-A13) calls
  /// [updateFrom] after each write).
  ///
  /// Read from `GET /storefront/cart` when there is a cart to read (a signed-in customer, or a
  /// guest holding a cart token), re-read when the session changes (sign-in adopts the guest cart,
  /// sign-out leaves it), and set directly from the cart returned by every cart write
  /// ([updateFrom]). A failed read keeps the last known count; the badge never blocks anything.
  CartCountProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'cartCountProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$cartCountHash();

  @$internal
  @override
  CartCount create() => CartCount();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(int value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<int>(value),
    );
  }
}

String _$cartCountHash() => r'addf54161e501d90fe297d4fda1e04392a64090e';

/// Number of units in the cart, for the Cart tab badge (M-A12; the cart screen (M-A13) calls
/// [updateFrom] after each write).
///
/// Read from `GET /storefront/cart` when there is a cart to read (a signed-in customer, or a
/// guest holding a cart token), re-read when the session changes (sign-in adopts the guest cart,
/// sign-out leaves it), and set directly from the cart returned by every cart write
/// ([updateFrom]). A failed read keeps the last known count; the badge never blocks anything.

abstract class _$CartCount extends $Notifier<int> {
  int build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<int, int>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<int, int>,
              int,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// Bumped when the cart changed outside the cart screen (an order was placed, a payment
/// finished, prices were re-quoted at checkout): the cart screen watches it and reloads.

@ProviderFor(CartRevision)
final cartRevisionProvider = CartRevisionProvider._();

/// Bumped when the cart changed outside the cart screen (an order was placed, a payment
/// finished, prices were re-quoted at checkout): the cart screen watches it and reloads.
final class CartRevisionProvider extends $NotifierProvider<CartRevision, int> {
  /// Bumped when the cart changed outside the cart screen (an order was placed, a payment
  /// finished, prices were re-quoted at checkout): the cart screen watches it and reloads.
  CartRevisionProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'cartRevisionProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$cartRevisionHash();

  @$internal
  @override
  CartRevision create() => CartRevision();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(int value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<int>(value),
    );
  }
}

String _$cartRevisionHash() => r'4906dd555ac8938c43796d4c012975bcfdfe7449';

/// Bumped when the cart changed outside the cart screen (an order was placed, a payment
/// finished, prices were re-quoted at checkout): the cart screen watches it and reloads.

abstract class _$CartRevision extends $Notifier<int> {
  int build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<int, int>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<int, int>,
              int,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// Checkout found new prices (`price_changed`) and the customer went back to the cart: the cart
/// shows "Prices were updated" until the next cart change (06.2).

@ProviderFor(CartPricesUpdated)
final cartPricesUpdatedProvider = CartPricesUpdatedProvider._();

/// Checkout found new prices (`price_changed`) and the customer went back to the cart: the cart
/// shows "Prices were updated" until the next cart change (06.2).
final class CartPricesUpdatedProvider
    extends $NotifierProvider<CartPricesUpdated, bool> {
  /// Checkout found new prices (`price_changed`) and the customer went back to the cart: the cart
  /// shows "Prices were updated" until the next cart change (06.2).
  CartPricesUpdatedProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'cartPricesUpdatedProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$cartPricesUpdatedHash();

  @$internal
  @override
  CartPricesUpdated create() => CartPricesUpdated();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(bool value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<bool>(value),
    );
  }
}

String _$cartPricesUpdatedHash() => r'bb26c2a0af351009f8f0789d041691cfd2f38f40';

/// Checkout found new prices (`price_changed`) and the customer went back to the cart: the cart
/// shows "Prices were updated" until the next cart change (06.2).

abstract class _$CartPricesUpdated extends $Notifier<bool> {
  bool build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<bool, bool>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<bool, bool>,
              bool,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
