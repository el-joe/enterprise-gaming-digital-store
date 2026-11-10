import 'package:application/core/network/result.dart';
import 'package:application/features/wallet/data/datasources/wallet_remote_data_source.dart';
import 'package:application/features/wallet/domain/entities/wallet.dart';
import 'package:application/features/wallet/domain/repositories/wallet_repository.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';

class WalletRepositoryImpl implements WalletRepository {
  WalletRepositoryImpl(this._remote);

  final WalletRemoteDataSource _remote;

  @override
  Future<Result<Wallet>> wallet() =>
      guardRequest(() async => (await _remote.wallet()).toEntity());

  @override
  Future<Result<Paged<WalletTransaction>>> transactions({
    int page = 1,
    int perPage = 20,
  }) => guardRequest(() async {
    final envelope = await _remote.transactions(page: page, perPage: perPage);
    return Paged(
      items: [for (final dto in envelope.data) dto.toEntity()],
      page: pageInfoOf(envelope),
    );
  });
}
