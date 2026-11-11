import 'dart:async';

import 'package:application/core/network/dio_client.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/cart/data/datasources/cart_remote_data_source.dart';
import 'package:application/features/cart/data/repositories/cart_repository_impl.dart';
import 'package:application/features/cart/domain/repositories/cart_repository.dart';
import 'package:application/shared/domain/entities/cart.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'cart_providers.g.dart';

@Riverpod(keepAlive: true)
CartRemoteDataSource cartRemoteDataSource(Ref ref) =>
    CartRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
CartRepository cartRepository(Ref ref) => CartRepositoryImpl(
  ref.watch(cartRemoteDataSourceProvider),
  ref.watch(secureStoreProvider),
);

/// Number of units in the cart, for the Cart tab badge (M-A12; the cart screen (M-A13) calls
/// [updateFrom] after each write).
///
/// Read from `GET /storefront/cart` when there is a cart to read (a signed-in customer, or a
/// guest holding a cart token), re-read when the session changes (sign-in adopts the guest cart,
/// sign-out leaves it), and set directly from the cart returned by every cart write
/// ([updateFrom]). A failed read keeps the last known count; the badge never blocks anything.
@Riverpod(keepAlive: true)
class CartCount extends _$CartCount {
  int _generation = 0;

  @override
  int build() {
    final session = ref.watch(sessionProvider);
    unawaited(_load(session));
    return 0;
  }

  /// Re-reads the cart (for example after an order was placed).
  Future<void> refresh() => _load(ref.read(sessionProvider));

  /// The count of [cart] as returned by a cart write.
  void updateFrom(Cart? cart) {
    _generation++;
    state = unitsIn(cart);
  }

  Future<void> _load(SessionStatus session) async {
    final generation = ++_generation;
    final store = ref.read(secureStoreProvider);
    if (session == SessionStatus.signedOut &&
        await store.readCartToken() == null) {
      return;
    }
    final result = await ref.read(cartRepositoryProvider).get();
    if (!ref.mounted || generation != _generation) {
      return;
    }
    if (result case Ok(:final value)) {
      state = unitsIn(value);
    }
  }
}

/// Bumped when the cart changed outside the cart screen (an order was placed, a payment
/// finished, prices were re-quoted at checkout): the cart screen watches it and reloads.
@Riverpod(keepAlive: true)
class CartRevision extends _$CartRevision {
  @override
  int build() => 0;

  void bump() => state++;
}

/// Checkout found new prices (`price_changed`) and the customer went back to the cart: the cart
/// shows "Prices were updated" until the next cart change (06.2).
@Riverpod(keepAlive: true)
class CartPricesUpdated extends _$CartPricesUpdated {
  @override
  bool build() => false;

  void flag() => state = true;

  void clear() => state = false;
}

/// Units across the cart's lines (0 without a cart).
int unitsIn(Cart? cart) =>
    cart?.items.fold<int>(0, (sum, line) => sum + line.quantity) ?? 0;
