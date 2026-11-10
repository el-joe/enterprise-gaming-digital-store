import 'package:application/core/network/api_decoding.dart';
import 'package:application/shared/data/models/cart_models.dart';
import 'package:dio/dio.dart';

/// `/api/v1/mobile/storefront/cart` (API_DOCUMENTATION.md 1c, 3a).
class CartRemoteDataSource {
  CartRemoteDataSource(this._dio);

  static const base = '/mobile/storefront/cart';

  final Dio _dio;

  static CartDto? _cartOrNull(Object? data) {
    final cart = asJsonObject(data)['cart'];
    return cart == null ? null : CartDto.fromJson(asJsonObject(cart));
  }

  static CartDto _cart(Object? data) =>
      CartDto.fromJson(objectAt(data, 'cart'));

  Future<CartDto?> get() async {
    final response = await _dio.get<Object?>(base);
    return decodeEnvelope(response.data, _cartOrNull).data;
  }

  Future<CartDto> addItem(Map<String, Object?> body) async {
    final response = await _dio.post<Object?>('$base/items', data: body);
    return decodeEnvelope(response.data, _cart).data;
  }

  Future<CartDto> updateItem(int itemId, int quantity) async {
    final response = await _dio.patch<Object?>(
      '$base/items/$itemId',
      data: {'quantity': quantity},
    );
    return decodeEnvelope(response.data, _cart).data;
  }

  Future<CartDto> removeItem(int itemId) async {
    final response = await _dio.delete<Object?>('$base/items/$itemId');
    return decodeEnvelope(response.data, _cart).data;
  }

  Future<CartDto> applyCoupon(String code) async {
    final response = await _dio.post<Object?>(
      '$base/coupon',
      data: {'code': code},
    );
    return decodeEnvelope(response.data, _cart).data;
  }

  Future<CartDto> removeCoupon() async {
    final response = await _dio.delete<Object?>('$base/coupon');
    return decodeEnvelope(response.data, _cart).data;
  }
}
