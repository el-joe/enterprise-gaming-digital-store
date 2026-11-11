// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'checkout_providers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(checkoutRemoteDataSource)
final checkoutRemoteDataSourceProvider = CheckoutRemoteDataSourceProvider._();

final class CheckoutRemoteDataSourceProvider
    extends
        $FunctionalProvider<
          CheckoutRemoteDataSource,
          CheckoutRemoteDataSource,
          CheckoutRemoteDataSource
        >
    with $Provider<CheckoutRemoteDataSource> {
  CheckoutRemoteDataSourceProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'checkoutRemoteDataSourceProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$checkoutRemoteDataSourceHash();

  @$internal
  @override
  $ProviderElement<CheckoutRemoteDataSource> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  CheckoutRemoteDataSource create(Ref ref) {
    return checkoutRemoteDataSource(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(CheckoutRemoteDataSource value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<CheckoutRemoteDataSource>(value),
    );
  }
}

String _$checkoutRemoteDataSourceHash() =>
    r'626069abb66143fa63feb4d0eea3fc4b3cc2a2e8';

@ProviderFor(checkoutRepository)
final checkoutRepositoryProvider = CheckoutRepositoryProvider._();

final class CheckoutRepositoryProvider
    extends
        $FunctionalProvider<
          CheckoutRepository,
          CheckoutRepository,
          CheckoutRepository
        >
    with $Provider<CheckoutRepository> {
  CheckoutRepositoryProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'checkoutRepositoryProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$checkoutRepositoryHash();

  @$internal
  @override
  $ProviderElement<CheckoutRepository> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  CheckoutRepository create(Ref ref) {
    return checkoutRepository(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(CheckoutRepository value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<CheckoutRepository>(value),
    );
  }
}

String _$checkoutRepositoryHash() =>
    r'f10ee333518bb47ca936204e5e561183d9f986d8';

@ProviderFor(pendingCheckoutStore)
final pendingCheckoutStoreProvider = PendingCheckoutStoreProvider._();

final class PendingCheckoutStoreProvider
    extends
        $FunctionalProvider<
          PendingCheckoutStore,
          PendingCheckoutStore,
          PendingCheckoutStore
        >
    with $Provider<PendingCheckoutStore> {
  PendingCheckoutStoreProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'pendingCheckoutStoreProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$pendingCheckoutStoreHash();

  @$internal
  @override
  $ProviderElement<PendingCheckoutStore> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  PendingCheckoutStore create(Ref ref) {
    return pendingCheckoutStore(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(PendingCheckoutStore value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<PendingCheckoutStore>(value),
    );
  }
}

String _$pendingCheckoutStoreHash() =>
    r'6fe46cc4cdbd236ab97115b1c5e80ebf17494bfb';

@ProviderFor(placeOrder)
final placeOrderProvider = PlaceOrderProvider._();

final class PlaceOrderProvider
    extends $FunctionalProvider<PlaceOrder, PlaceOrder, PlaceOrder>
    with $Provider<PlaceOrder> {
  PlaceOrderProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'placeOrderProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$placeOrderHash();

  @$internal
  @override
  $ProviderElement<PlaceOrder> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  PlaceOrder create(Ref ref) {
    return placeOrder(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(PlaceOrder value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<PlaceOrder>(value),
    );
  }
}

String _$placeOrderHash() => r'a01a618a3016e12fe8e0e92c632cf33b40721a55';

@ProviderFor(checkoutMode)
final checkoutModeProvider = CheckoutModeProvider._();

final class CheckoutModeProvider
    extends $FunctionalProvider<CheckoutMode, CheckoutMode, CheckoutMode>
    with $Provider<CheckoutMode> {
  CheckoutModeProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'checkoutModeProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$checkoutModeHash();

  @$internal
  @override
  $ProviderElement<CheckoutMode> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  CheckoutMode create(Ref ref) {
    return checkoutMode(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(CheckoutMode value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<CheckoutMode>(value),
    );
  }
}

String _$checkoutModeHash() => r'551170891cfc60129630c39856835b5d37894b96';
