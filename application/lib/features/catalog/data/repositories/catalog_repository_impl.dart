import 'package:application/core/network/result.dart';
import 'package:application/features/catalog/data/datasources/catalog_remote_data_source.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/features/catalog/domain/repositories/catalog_repository.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';
import 'package:application/shared/data/models/display_currency.dart';

class CatalogRepositoryImpl implements CatalogRepository {
  CatalogRepositoryImpl(this._remote);

  final CatalogRemoteDataSource _remote;

  @override
  Future<Result<CatalogPage>> products(CatalogQuery query) =>
      guardRequest(() async {
        final envelope = await _remote.products(query);
        return CatalogPage(
          products: [for (final card in envelope.data) card.toEntity()],
          page: pageInfoOf(envelope),
          displayCurrency: displayCurrencyOf(envelope),
        );
      });

  @override
  Future<Result<List<Platform>>> platforms() => guardRequest(
    () async => [for (final dto in await _remote.platforms()) dto.toEntity()],
  );

  @override
  Future<Result<List<Category>>> categories() => guardRequest(
    () async => [for (final dto in await _remote.categories()) dto.toEntity()],
  );

  @override
  Future<Result<List<Country>>> countries() => guardRequest(
    () async => [for (final dto in await _remote.countries()) dto.toEntity()],
  );

  @override
  Future<Result<List<DisplayCurrencyOption>>> currencies() => guardRequest(
    () async => [for (final dto in await _remote.currencies()) dto.toEntity()],
  );
}
