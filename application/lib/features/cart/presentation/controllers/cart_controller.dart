import 'dart:async';

import 'package:application/core/money/pricing_context.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/cart/cart_providers.dart';
import 'package:application/shared/domain/entities/cart.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'cart_controller.g.dart';

/// The request limit of `quantity` on cart lines (API_DOCUMENTATION.md 3a: 1 to 10).
const maxLineQuantity = 10;

/// Line block reasons that come from pricing (stale or missing exchange rate): the price is being
/// updated rather than the item being unavailable.
const pricingBlockReasons = {'rate_stale', 'rate_missing'};

/// State of the cart tab (06.1 to 06.5).
@immutable
final class CartPageState {
  const CartPageState({
    this.content = const Loadable.loading(),
    this.busyLines = const {},
    this.lineErrors = const {},
    this.couponBusy = false,
    this.couponError,
    this.actionFailure,
    this.actionFailureCount = 0,
  });

  /// The cart; a loaded null is "no cart" (empty).
  final Loadable<Cart?> content;

  /// Lines with a quantity change or removal in flight.
  final Set<int> busyLines;

  /// The server's `errors.quantity` for a line (for example the per-order limit).
  final Map<int, String> lineErrors;

  final bool couponBusy;

  /// Why the coupon was refused: the server's text (`coupon_invalid`, `coupon.error`).
  final String? couponError;

  /// The last cart write that failed for another reason (snackbar on each increment).
  final Failure? actionFailure;
  final int actionFailureCount;

  Cart? get cart => content.valueOrNull;

  /// No cart, or a cart without lines.
  bool get isEmpty {
    final cart = this.cart;
    return content is Loaded<Cart?> && (cart == null || cart.items.isEmpty);
  }

  /// Checkout is offered only for a cart the server calls ready (no blocked line).
  bool get canCheckout =>
      (cart?.checkoutReady ?? false) &&
      (cart?.items.isNotEmpty ?? false) &&
      busyLines.isEmpty;

  /// A line is priced on a stale or missing exchange rate (06.2 "rate_stale").
  bool get pricesUpdating =>
      cart?.items.any(
        (line) => pricingBlockReasons.contains(line.blockReason),
      ) ??
      false;

  CartPageState copyWith({
    Loadable<Cart?>? content,
    Set<int>? busyLines,
    Map<int, String>? lineErrors,
    bool? couponBusy,
    String? Function()? couponError,
    Failure? actionFailure,
    int? actionFailureCount,
  }) => CartPageState(
    content: content ?? this.content,
    busyLines: busyLines ?? this.busyLines,
    lineErrors: lineErrors ?? this.lineErrors,
    couponBusy: couponBusy ?? this.couponBusy,
    couponError: couponError == null ? this.couponError : couponError(),
    actionFailure: actionFailure ?? this.actionFailure,
    actionFailureCount: actionFailureCount ?? this.actionFailureCount,
  );
}

/// The cart tab: reads the cart, changes quantities, removes lines, applies and removes a coupon.
/// Totals are always the server's. Every write updates the Cart tab badge
/// ([CartCount.updateFrom]). The display currency, the language, the session (sign-in adopts the
/// guest cart) and [CartRevision] (an order was placed) reload it.
@riverpod
class CartController extends _$CartController {
  int _generation = 0;

  @override
  CartPageState build() {
    ref
      ..watch(pricingContextProvider)
      ..watch(sessionProvider)
      ..watch(cartRevisionProvider);
    unawaited(_load(initial: true));
    return const CartPageState();
  }

  Future<void> retry() async {
    state = const CartPageState();
    await _load();
  }

  /// Pull to refresh: keeps the cart on screen while it reloads.
  Future<void> refresh() => _load(keepContent: true);

  Future<void> setQuantity(CartLine line, int quantity) async {
    final next = quantity.clamp(1, maxLineQuantity);
    if (next == line.quantity || state.busyLines.contains(line.id)) {
      return;
    }
    await _write(
      line.id,
      () =>
          ref.read(cartRepositoryProvider).updateItem(line.id, quantity: next),
    );
  }

  Future<void> remove(CartLine line) async {
    if (state.busyLines.contains(line.id)) {
      return;
    }
    await _write(
      line.id,
      () => ref.read(cartRepositoryProvider).removeItem(line.id),
    );
  }

  Future<void> applyCoupon(String code) async {
    final trimmed = code.trim();
    if (trimmed.isEmpty || state.couponBusy) {
      return;
    }
    final generation = _generation;
    state = state.copyWith(couponBusy: true, couponError: () => null);
    final result = await ref.read(cartRepositoryProvider).applyCoupon(trimmed);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    switch (result) {
      case Ok(:final value):
        _took(value);
        final coupon = value.coupon;
        state = state.copyWith(
          couponBusy: false,
          couponError: () =>
              coupon != null && !coupon.applied ? coupon.error : null,
        );
      case Err(failure: DomainFailure(:final errors, :final message)):
        state = state.copyWith(
          couponBusy: false,
          couponError: () => _first(errors['code']) ?? message,
        );
      case Err(failure: ValidationFailure(:final fieldErrors, :final message)):
        state = state.copyWith(
          couponBusy: false,
          couponError: () => _first(fieldErrors['code']) ?? message,
        );
      case Err(:final failure):
        state = state.copyWith(couponBusy: false);
        _failed(failure);
    }
  }

  Future<void> removeCoupon() async {
    if (state.couponBusy) {
      return;
    }
    final generation = _generation;
    state = state.copyWith(couponBusy: true, couponError: () => null);
    final result = await ref.read(cartRepositoryProvider).removeCoupon();
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = state.copyWith(couponBusy: false);
    switch (result) {
      case Ok(:final value):
        _took(value);
      case Err(:final failure):
        _failed(failure);
    }
  }

  /// Web checkout mode (owner decision 3): opens `links.web_checkout` in the system browser.
  /// False when nothing could open it.
  Future<bool> openWebCheckout() async {
    final url = ref.read(latestAppConfigProvider)?.links.webCheckout;
    return url != null &&
        await ref.read(nativeBridgeProvider).openExternalUrl(url);
  }

  /// The "Prices were updated" notice was read.
  void dismissPricesUpdated() =>
      ref.read(cartPricesUpdatedProvider.notifier).clear();

  Future<void> _write(int lineId, Future<Result<Cart>> Function() call) async {
    final generation = _generation;
    state = state.copyWith(
      busyLines: {...state.busyLines, lineId},
      lineErrors: Map.of(state.lineErrors)..remove(lineId),
    );
    final result = await call();
    if (!ref.mounted || generation != _generation) {
      return;
    }
    final busy = {...state.busyLines}..remove(lineId);
    switch (result) {
      case Ok(:final value):
        state = state.copyWith(busyLines: busy);
        _took(value);
      case Err(failure: ValidationFailure(:final fieldErrors))
          when _first(fieldErrors['quantity']) != null:
        state = state.copyWith(
          busyLines: busy,
          lineErrors: {
            ...state.lineErrors,
            lineId: _first(fieldErrors['quantity'])!,
          },
        );
      case Err(failure: NotFoundFailure()):
        // The line is gone (changed on another device): show the cart as it is now.
        state = state.copyWith(busyLines: busy);
        await _load(keepContent: true);
      case Err(:final failure):
        state = state.copyWith(busyLines: busy);
        _failed(failure);
    }
  }

  /// A cart returned by a write: shown, counted on the badge, and the price notice cleared.
  void _took(Cart cart) {
    ref.read(cartCountProvider.notifier).updateFrom(cart);
    ref.read(cartPricesUpdatedProvider.notifier).clear();
    state = state.copyWith(content: Loaded(cart));
  }

  void _failed(Failure failure) => state = state.copyWith(
    actionFailure: failure,
    actionFailureCount: state.actionFailureCount + 1,
  );

  Future<void> _load({bool keepContent = false, bool initial = false}) async {
    final generation = ++_generation;
    if (!keepContent && !initial) {
      state = state.copyWith(content: const Loadable.loading());
    }
    final session = ref.read(sessionProvider);
    if (session == SessionStatus.signedOut &&
        await ref.read(secureStoreProvider).readCartToken() == null) {
      if (ref.mounted && generation == _generation) {
        state = state.copyWith(content: const Loaded(null));
      }
      return;
    }
    final result = await ref.read(cartRepositoryProvider).get();
    if (!ref.mounted || generation != _generation) {
      return;
    }
    switch (result) {
      case Ok(:final value):
        ref.read(cartCountProvider.notifier).updateFrom(value);
        state = state.copyWith(content: Loaded(value));
      case Err(:final failure):
        if (keepContent && state.cart != null) {
          _failed(failure);
        } else {
          state = state.copyWith(content: Failed(failure));
        }
    }
  }

  static String? _first(List<String>? messages) =>
      messages == null || messages.isEmpty ? null : messages.first;
}
