import 'package:application/core/network/dio_client.dart';
import 'package:application/features/support/data/datasources/support_remote_data_source.dart';
import 'package:application/features/support/data/repositories/support_repository_impl.dart';
import 'package:application/features/support/domain/repositories/support_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'support_providers.g.dart';

@Riverpod(keepAlive: true)
SupportRemoteDataSource supportRemoteDataSource(Ref ref) =>
    SupportRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
SupportRepository supportRepository(Ref ref) =>
    SupportRepositoryImpl(ref.watch(supportRemoteDataSourceProvider));
