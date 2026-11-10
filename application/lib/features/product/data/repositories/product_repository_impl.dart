import 'package:application/core/network/result.dart';
import 'package:application/features/product/data/datasources/product_remote_data_source.dart';
import 'package:application/features/product/domain/entities/product.dart';
import 'package:application/features/product/domain/repositories/product_repository.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';
import 'package:application/shared/data/models/display_currency.dart';

class ProductRepositoryImpl implements ProductRepository {
  ProductRepositoryImpl(this._remote);

  final ProductRemoteDataSource _remote;

  @override
  Future<Result<ProductDetail>> detail(String slug) => guardRequest(() async {
    final envelope = await _remote.detail(slug);
    return envelope.data.toEntity(displayCurrency: displayCurrencyOf(envelope));
  });

  @override
  Future<Result<ProductVariants>> variants(String slug, {String? country}) =>
      guardRequest(() async {
        final envelope = await _remote.variants(slug, country: country);
        return ProductVariants(
          variants: [for (final dto in envelope.data) dto.toEntity()],
          displayCurrency: displayCurrencyOf(envelope),
        );
      });

  @override
  Future<Result<ProductReviewsPage>> reviews(
    String slug, {
    int page = 1,
    int perPage = 20,
  }) => guardRequest(() async {
    final envelope = await _remote.reviews(slug, page: page, perPage: perPage);
    final average = envelope.meta['rating_avg'];
    final count = envelope.meta['rating_count'];
    if (average is! String || count is! int) {
      throw const FormatException('Reviews meta has no rating.');
    }
    return ProductReviewsPage(
      reviews: [for (final dto in envelope.data) dto.toEntity()],
      page: pageInfoOf(envelope),
      ratingAvg: average,
      ratingCount: count,
    );
  });
}
