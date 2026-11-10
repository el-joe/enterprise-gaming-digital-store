import 'package:application/core/network/result.dart';
import 'package:application/features/home/data/datasources/home_remote_data_source.dart';
import 'package:application/features/home/domain/entities/home.dart';
import 'package:application/features/home/domain/repositories/home_repository.dart';
import 'package:application/shared/data/models/display_currency.dart';

class HomeRepositoryImpl implements HomeRepository {
  HomeRepositoryImpl(this._remote);

  final HomeRemoteDataSource _remote;

  @override
  Future<Result<HomePage>> fetch() => guardRequest(() async {
    final envelope = await _remote.fetch();
    return envelope.data.toEntity(displayCurrency: displayCurrencyOf(envelope));
  });
}
