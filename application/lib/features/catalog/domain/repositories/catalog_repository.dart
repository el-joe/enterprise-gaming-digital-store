import 'package:application/core/network/result.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';

abstract interface class CatalogRepository {
  Future<Result<CatalogPage>> products(CatalogQuery query);

  Future<Result<List<Platform>>> platforms();

  Future<Result<List<Category>>> categories();

  Future<Result<List<Country>>> countries();

  Future<Result<List<DisplayCurrencyOption>>> currencies();
}
