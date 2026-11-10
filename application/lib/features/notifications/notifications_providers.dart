import 'package:application/core/network/dio_client.dart';
import 'package:application/features/notifications/data/datasources/notifications_remote_data_source.dart';
import 'package:application/features/notifications/data/repositories/notifications_repository_impl.dart';
import 'package:application/features/notifications/domain/repositories/notifications_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'notifications_providers.g.dart';

@Riverpod(keepAlive: true)
NotificationsRemoteDataSource notificationsRemoteDataSource(Ref ref) =>
    NotificationsRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
NotificationsRepository notificationsRepository(Ref ref) =>
    NotificationsRepositoryImpl(
      ref.watch(notificationsRemoteDataSourceProvider),
    );
