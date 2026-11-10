import 'package:application/core/network/result.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/cart/data/datasources/cart_remote_data_source.dart';
import 'package:application/features/cart/domain/repositories/cart_repository.dart';
import 'package:application/shared/data/models/cart_models.dart';
import 'package:application/shared/domain/entities/cart.dart';

class CartRepositoryImpl implements CartRepository {
  CartRepositoryImpl(this._remote, this._store);

  final CartRemoteDataSource _remote;
  final SecureStore _store;

  /// After a successful call made with a bearer token, a stored guest cart token has been
  /// adopted by the account (or the account kept its own cart), so it is dropped.
  Future<T> _afterCall<T>(Future<T> Function() call) async {
    final result = await call();
    if (await _store.hasSession() && await _store.readCartToken() != null) {
      await _store.deleteCartToken();
    }
    return result;
  }

  Future<Result<Cart>> _cart(Future<CartDto> Function() call) =>
      guardRequest(() async => (await _afterCall(call)).toEntity());

  @override
  Future<Result<Cart?>> get() =>
      guardRequest(() async => (await _afterCall(_remote.get))?.toEntity());

  @override
  Future<Result<Cart>> addItem({
    required String variantPublicId,
    required int quantity,
    Map<String, String> deliveryValues = const {},
  }) => _cart(
    () => _remote.addItem({
      'variant_id': variantPublicId,
      'quantity': quantity,
      if (deliveryValues.isNotEmpty) 'delivery_values': deliveryValues,
    }),
  );

  @override
  Future<Result<Cart>> updateItem(int itemId, {required int quantity}) =>
      _cart(() => _remote.updateItem(itemId, quantity));

  @override
  Future<Result<Cart>> removeItem(int itemId) =>
      _cart(() => _remote.removeItem(itemId));

  @override
  Future<Result<Cart>> applyCoupon(String code) =>
      _cart(() => _remote.applyCoupon(code));

  @override
  Future<Result<Cart>> removeCoupon() => _cart(_remote.removeCoupon);
}
