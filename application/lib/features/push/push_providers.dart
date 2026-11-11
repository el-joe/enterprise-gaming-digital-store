import 'package:application/core/network/dio_client.dart';
import 'package:application/features/push/data/datasources/push_remote_data_source.dart';
import 'package:application/features/push/data/messaging/firebase_push_messaging.dart';
import 'package:application/features/push/data/repositories/push_token_repository_impl.dart';
import 'package:application/features/push/domain/push_messaging.dart';
import 'package:application/features/push/domain/repositories/push_token_repository.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'push_providers.g.dart';

@Riverpod(keepAlive: true)
PushMessaging pushMessaging(Ref ref) => FirebasePushMessaging();

@Riverpod(keepAlive: true)
PushRemoteDataSource pushRemoteDataSource(Ref ref) =>
    PushRemoteDataSource(ref.watch(apiClientProvider));

@Riverpod(keepAlive: true)
PushTokenRepository pushTokenRepository(Ref ref) =>
    PushTokenRepositoryImpl(ref.watch(pushRemoteDataSourceProvider));
