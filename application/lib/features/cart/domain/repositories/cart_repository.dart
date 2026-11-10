import 'package:application/core/network/result.dart';
import 'package:application/shared/domain/entities/cart.dart';

/// The stateless cart (API_DOCUMENTATION.md 1c, 3a). A guest is identified by `X-Cart-Token`
/// (captured from the first add-to-cart by the cart token interceptor); a signed-in customer by
/// the bearer token, whose first cart call adopts a stored guest cart.
abstract interface class CartRepository {
  /// The cart, or null when there is none.
  Future<Result<Cart?>> get();

  Future<Result<Cart>> addItem({
    required String variantPublicId,
    required int quantity,
    Map<String, String> deliveryValues = const {},
  });

  Future<Result<Cart>> updateItem(int itemId, {required int quantity});

  Future<Result<Cart>> removeItem(int itemId);

  /// 422 `coupon_invalid` with the reason in the message.
  Future<Result<Cart>> applyCoupon(String code);

  Future<Result<Cart>> removeCoupon();
}
