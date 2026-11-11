import 'package:application/core/network/result.dart';
import 'package:application/features/push/data/datasources/push_remote_data_source.dart';
import 'package:application/features/push/domain/repositories/push_token_repository.dart';

class PushTokenRepositoryImpl implements PushTokenRepository {
  PushTokenRepositoryImpl(this._remote);

  final PushRemoteDataSource _remote;

  @override
  Future<Result<void>> register({
    required String token,
    required String platform,
  }) => guardRequest(() async {
    await _remote.register(token: token, platform: platform);
  });

  @override
  Future<Result<void>> unregister() => guardRequest(_remote.unregister);
}
