import 'package:application/core/network/dio_client.dart';
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
