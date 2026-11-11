// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'orders_controllers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The Orders tab: `GET /account/orders` newest first, 20 per page, `meta` driven paging, a
/// status filter. A new sign-in, a sign-out and [CartRevision] (an order was placed or a payment
/// answered) reload it from page 1.

@ProviderFor(OrdersListController)
final ordersListControllerProvider = OrdersListControllerProvider._();

/// The Orders tab: `GET /account/orders` newest first, 20 per page, `meta` driven paging, a
/// status filter. A new sign-in, a sign-out and [CartRevision] (an order was placed or a payment
/// answered) reload it from page 1.
final class OrdersListControllerProvider
    extends $NotifierProvider<OrdersListController, OrdersListState> {
  /// The Orders tab: `GET /account/orders` newest first, 20 per page, `meta` driven paging, a
  /// status filter. A new sign-in, a sign-out and [CartRevision] (an order was placed or a payment
  /// answered) reload it from page 1.
  OrdersListControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'ordersListControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$ordersListControllerHash();

  @$internal
  @override
  OrdersListController create() => OrdersListController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(OrdersListState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<OrdersListState>(value),
    );
  }
}

String _$ordersListControllerHash() =>
    r'b94d3625b37336bd8fa0138eb3fba619bf5f750a';

/// The Orders tab: `GET /account/orders` newest first, 20 per page, `meta` driven paging, a
/// status filter. A new sign-in, a sign-out and [CartRevision] (an order was placed or a payment
/// answered) reload it from page 1.

abstract class _$OrdersListController extends $Notifier<OrdersListState> {
  OrdersListState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<OrdersListState, OrdersListState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<OrdersListState, OrdersListState>,
              OrdersListState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// The order detail (09.4, 09.5): `GET /account/orders/{id}`, never a code. [CartRevision] (a
/// payment answered) reloads it.

@ProviderFor(OrderDetailController)
final orderDetailControllerProvider = OrderDetailControllerFamily._();

/// The order detail (09.4, 09.5): `GET /account/orders/{id}`, never a code. [CartRevision] (a
/// payment answered) reloads it.
final class OrderDetailControllerProvider
    extends $NotifierProvider<OrderDetailController, Loadable<OrderDetail>> {
  /// The order detail (09.4, 09.5): `GET /account/orders/{id}`, never a code. [CartRevision] (a
  /// payment answered) reloads it.
  OrderDetailControllerProvider._({
    required OrderDetailControllerFamily super.from,
    required String super.argument,
  }) : super(
         retry: null,
         name: r'orderDetailControllerProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$orderDetailControllerHash();

  @override
  String toString() {
    return r'orderDetailControllerProvider'
        ''
        '($argument)';
  }

  @$internal
  @override
  OrderDetailController create() => OrderDetailController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(Loadable<OrderDetail> value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<Loadable<OrderDetail>>(value),
    );
  }

  @override
  bool operator ==(Object other) {
    return other is OrderDetailControllerProvider && other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$orderDetailControllerHash() =>
    r'2809a15d24a599188f511c4e3470bdc4d0566237';

/// The order detail (09.4, 09.5): `GET /account/orders/{id}`, never a code. [CartRevision] (a
/// payment answered) reloads it.

final class OrderDetailControllerFamily extends $Family
    with
        $ClassFamilyOverride<
          OrderDetailController,
          Loadable<OrderDetail>,
          Loadable<OrderDetail>,
          Loadable<OrderDetail>,
          String
        > {
  OrderDetailControllerFamily._()
    : super(
        retry: null,
        name: r'orderDetailControllerProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// The order detail (09.4, 09.5): `GET /account/orders/{id}`, never a code. [CartRevision] (a
  /// payment answered) reloads it.

  OrderDetailControllerProvider call(String order) =>
      OrderDetailControllerProvider._(argument: order, from: this);

  @override
  String toString() => r'orderDetailControllerProvider';
}

/// The order detail (09.4, 09.5): `GET /account/orders/{id}`, never a code. [CartRevision] (a
/// payment answered) reloads it.

abstract class _$OrderDetailController
    extends $Notifier<Loadable<OrderDetail>> {
  late final _$args = ref.$arg as String;
  String get order => _$args;

  Loadable<OrderDetail> build(String order);
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<Loadable<OrderDetail>, Loadable<OrderDetail>>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<Loadable<OrderDetail>, Loadable<OrderDetail>>,
              Loadable<OrderDetail>,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, () => build(_$args));
  }
}
