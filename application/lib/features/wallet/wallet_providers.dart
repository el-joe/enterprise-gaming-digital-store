import 'package:application/core/network/dio_client.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/wallet/data/datasources/wallet_remote_data_source.dart';
import 'package:application/features/wallet/data/repositories/wallet_repository_impl.dart';
import 'package:application/features/wallet/domain/repositories/wallet_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'wallet_providers.g.dart';

@Riverpod(keepAlive: true)
WalletRemoteDataSource walletRemoteDataSource(Ref ref) =>
    WalletRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
WalletRepository walletRepository(Ref ref) =>
    WalletRepositoryImpl(ref.watch(walletRemoteDataSourceProvider));

/// `features.wallet_enabled` of `GET /app/config` (M-A15). Off: the Account tab hides the wallet
/// entry, the wallet screen shows "not available" and loads nothing, and checkout drops the
/// wallet option. Before the launch check has a configuration the wallet is shown (the server
/// still decides whether the wallet can pay).
@riverpod
bool walletEnabled(Ref ref) =>
    ref.watch(latestAppConfigProvider)?.features.walletEnabled ?? true;
