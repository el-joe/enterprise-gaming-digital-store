import 'package:application/core/network/dio_client.dart';
import 'package:application/features/security/data/datasources/security_remote_data_source.dart';
import 'package:application/features/security/data/repositories/security_repository_impl.dart';
import 'package:application/features/security/domain/repositories/security_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'security_providers.g.dart';

@riverpod
SecurityRemoteDataSource securityRemoteDataSource(Ref ref) =>
    SecurityRemoteDataSource(ref.watch(apiClientProvider));

@riverpod
SecurityRepository securityRepository(Ref ref) =>
    SecurityRepositoryImpl(ref.watch(securityRemoteDataSourceProvider));
