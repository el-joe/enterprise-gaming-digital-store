import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/cart/cart_providers.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/features/orders/orders_providers.dart';
import 'package:application/features/payments/payment_flow.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'orders_controllers.g.dart';

/// Page size of the order list: `OrdersQuery`'s default (API_DOCUMENTATION.md 4c: 20, max 50).
const ordersPageSize = 20;

/// The status filter chips of 09.1, in order. `null` is "All". Each value is one order status
/// accepted by `GET /account/orders?status=`.
const orderStatusFilters = <String?>[
  null,
  'completed',
  'processing',
  'pending_payment',
  'partially_refunded',
  'refunded',
  'failed',
  'cancelled',
];

/// State of the Orders tab (09.1 to 09.3).
@immutable
final class OrdersListState {
  const OrdersListState({
    this.signedIn = true,
    this.status,
    this.content = const Loadable.loading(),
    this.page,
    this.loadingMore = false,
    this.moreFailure,
    this.refreshing = false,
  });

  /// Signed out: the tab offers sign-in and guest order access instead of a list.
  final bool signedIn;

  /// The selected status filter (null: all).
  final String? status;

  /// Every order loaded so far (pages appended).
  final Loadable<List<OrderSummary>> content;

  /// The last page's meta.
  final PageInfo? page;

  final bool loadingMore;

  /// A next page failed: the list stays, with a retry bar (no automatic retry loop).
  final Failure? moreFailure;

  final bool refreshing;

  List<OrderSummary> get orders => content.valueOrNull ?? const [];

  bool get hasMore => page?.hasNextPage ?? false;

  bool get isEmpty => content is Loaded<List<OrderSummary>> && orders.isEmpty;

  OrdersListState copyWith({
    bool? signedIn,
    String? Function()? status,
    Loadable<List<OrderSummary>>? content,
    PageInfo? Function()? page,
    bool? loadingMore,
    Failure? Function()? moreFailure,
    bool? refreshing,
  }) => OrdersListState(
    signedIn: signedIn ?? this.signedIn,
    status: status == null ? this.status : status(),
    content: content ?? this.content,
    page: page == null ? this.page : page(),
    loadingMore: loadingMore ?? this.loadingMore,
    moreFailure: moreFailure == null ? this.moreFailure : moreFailure(),
    refreshing: refreshing ?? this.refreshing,
  );
}

/// The Orders tab: `GET /account/orders` newest first, 20 per page, `meta` driven paging, a
/// status filter. A new sign-in, a sign-out and [CartRevision] (an order was placed or a payment
/// answered) reload it from page 1.
@riverpod
class OrdersListController extends _$OrdersListController {
  int _generation = 0;

  @override
  OrdersListState build() {
    final session = ref.watch(sessionProvider);
    ref.watch(cartRevisionProvider);
    if (session != SessionStatus.signedIn) {
      return const OrdersListState(signedIn: false);
    }
    unawaited(_loadFirst(null));
    return const OrdersListState();
  }

  Future<void> _loadFirst(String? status, {bool keep = false}) async {
    final generation = ++_generation;
    final result = await ref
        .read(ordersRepositoryProvider)
        .list(OrdersQuery(status: status));
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = switch (result) {
      Ok(:final value) => state.copyWith(
        content: Loadable.loaded(value.items),
        page: () => value.page,
        moreFailure: () => null,
        loadingMore: false,
        refreshing: false,
      ),
      Err(:final failure) =>
        keep && state.content is Loaded<List<OrderSummary>>
            ? state.copyWith(refreshing: false, moreFailure: () => failure)
            : state.copyWith(
                content: Loadable.failed(failure),
                page: () => null,
                refreshing: false,
              ),
    };
  }

  /// A status chip: restarts at page 1.
  Future<void> selectStatus(String? status) async {
    if (status == state.status || !state.signedIn) {
      return;
    }
    state = OrdersListState(status: status);
    await _loadFirst(status);
  }

  Future<void> retry() async {
    if (!state.signedIn) {
      return;
    }
    state = OrdersListState(status: state.status);
    await _loadFirst(state.status);
  }

  /// Pull to refresh: the list stays while page 1 reloads.
  Future<void> refresh() async {
    if (!state.signedIn) {
      return;
    }
    state = state.copyWith(refreshing: true);
    await _loadFirst(state.status, keep: true);
  }

  /// The next page, when there is one and none is loading. A failed page is kept as
  /// [OrdersListState.moreFailure] until the customer retries.
  Future<void> loadMore() async {
    final page = state.page;
    if (!state.signedIn ||
        state.loadingMore ||
        page == null ||
        !page.hasNextPage ||
        state.content is! Loaded<List<OrderSummary>>) {
      return;
    }
    final generation = _generation;
    state = state.copyWith(loadingMore: true, moreFailure: () => null);
    final result = await ref
        .read(ordersRepositoryProvider)
        .list(OrdersQuery(status: state.status, page: page.currentPage + 1));
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = switch (result) {
      Ok(:final value) => state.copyWith(
        content: Loadable.loaded([...state.orders, ...value.items]),
        page: () => value.page,
        loadingMore: false,
      ),
      Err(:final failure) => state.copyWith(
        loadingMore: false,
        moreFailure: () => failure,
      ),
    };
  }
}

/// Whether the order can be paid again from its detail: still `pending_payment` and the latest
/// attempt ended (or none exists). Retry-payment re-checks everything server side.
bool canRetryPayment(OrderDetail detail) {
  if (detail.summary.status != 'pending_payment') {
    return false;
  }
  final latest = latestPayment(detail);
  return latest == null ||
      const {'failed', 'cancelled', 'expired'}.contains(latest.status);
}

/// Whether a refund can be requested: paid (or partly refunded) and no refund pending or done in
/// full. The server decides (`refund_not_allowed`, `refund_request_open`, M-A17).
bool canRequestRefund(OrderDetail detail) =>
    const {
      'paid',
      'partially_refunded',
    }.contains(detail.summary.paymentStatus) &&
    detail.refundState != 'pending' &&
    detail.refundState != 'full';

/// The latest payment attempt, if any.
OrderPayment? latestPayment(OrderDetail detail) {
  OrderPayment? latest;
  for (final payment in detail.payments) {
    if (latest == null || payment.attempt > latest.attempt) {
      latest = payment;
    }
  }
  return latest;
}

/// The order detail (09.4, 09.5): `GET /account/orders/{id}`, never a code. [CartRevision] (a
/// payment answered) reloads it.
@riverpod
class OrderDetailController extends _$OrderDetailController {
  int _generation = 0;

  @override
  Loadable<OrderDetail> build(String order) {
    ref.watch(cartRevisionProvider);
    unawaited(_load());
    return const Loadable.loading();
  }

  Future<void> _load({bool keep = false}) async {
    final generation = ++_generation;
    final result = await ref.read(ordersRepositoryProvider).detail(order);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    switch (result) {
      case Ok(:final value):
        state = Loadable.loaded(value);
      case Err(:final failure):
        if (!keep || state is! Loaded<OrderDetail>) {
          state = Loadable.failed(failure);
        }
    }
  }

  Future<void> retry() async {
    state = const Loadable.loading();
    await _load();
  }

  Future<void> refresh() => _load(keep: true);

  /// "Retry payment": hands the order to the M-A13 payment flow, which starts a new attempt on
  /// the order's gateway; the page then opens the payment screen.
  void retryPayment() {
    final detail = state.valueOrNull;
    if (detail == null || !canRetryPayment(detail)) {
      return;
    }
    final latest = latestPayment(detail);
    unawaited(
      ref
          .read(paymentFlowProvider.notifier)
          .retryFromOrder(
            orderPublicId: order,
            orderNumber: detail.summary.number,
            paymentTotal: detail.summary.paymentTotal,
            gatewayLabel: latest?.provider,
            attempt: latest?.attempt,
          ),
    );
  }
}
