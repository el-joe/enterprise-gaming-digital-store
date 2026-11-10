import 'package:application/core/network/result.dart';
import 'package:application/features/product/domain/entities/product.dart';

abstract interface class ProductRepository {
  Future<Result<ProductDetail>> detail(String slug);

  /// [country] narrows purchasability (`not_sold_in_country`).
  Future<Result<ProductVariants>> variants(String slug, {String? country});

  Future<Result<ProductReviewsPage>> reviews(
    String slug, {
    int page = 1,
    int perPage = 20,
  });
}
