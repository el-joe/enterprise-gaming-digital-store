import 'package:application/core/network/result.dart';
import 'package:application/features/wallet/domain/entities/wallet.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';

abstract interface class WalletRepository {
  Future<Result<Wallet>> wallet();

  Future<Result<Paged<WalletTransaction>>> transactions({
    int page = 1,
    int perPage = 20,
  });
}
