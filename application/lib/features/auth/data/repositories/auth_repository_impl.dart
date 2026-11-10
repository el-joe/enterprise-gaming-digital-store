import 'package:application/core/network/failure.dart';
import 'package:application/core/network/interceptors/auth_interceptor.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/auth/data/datasources/auth_remote_data_source.dart';
import 'package:application/features/auth/data/models/auth_models.dart';
import 'package:application/features/auth/domain/entities/auth_entities.dart';
import 'package:application/features/auth/domain/repositories/auth_repository.dart';

class AuthRepositoryImpl implements AuthRepository {
  AuthRepositoryImpl({
    required this._remote,
    required this._store,
    required this._refresher,
    required this._onSignedIn,
    required this._onSignedOut,
  });

  final AuthRemoteDataSource _remote;
  final SecureStore _store;
  final TokenRefresher _refresher;
  final void Function() _onSignedIn;
  final void Function() _onSignedOut;

  Future<AuthUser> _signIn(AuthSessionDto session) async {
    await _store.writeTokens(session.tokens.toAuthTokens());
    _onSignedIn();
    return session.user.toEntity();
  }

  @override
  Future<Result<AuthUser>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String deviceName,
  }) => guardRequest(() async {
    final session = await _remote.register({
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'device_name': deviceName,
    });
    return _signIn(session);
  });

  @override
  Future<Result<LoginOutcome>> login({
    required String email,
    required String password,
    required String deviceName,
  }) => guardRequest(() async {
    final response = await _remote.login(
      email: email,
      password: password,
      deviceName: deviceName,
    );
    final challenge = response.challenge;
    if (response.twoFactorRequired && challenge != null) {
      return LoginOutcome.twoFactorRequired(
        challengeToken: challenge.token,
        expiresAt: challenge.expiresAt,
      );
    }
    final tokens = response.tokens;
    final user = response.user;
    if (tokens == null || user == null) {
      throw const FormatException(
        'Login response has neither tokens nor challenge.',
      );
    }
    return LoginOutcome.signedIn(
      await _signIn(AuthSessionDto(tokens: tokens, user: user)),
    );
  });

  @override
  Future<Result<AuthUser>> completeTwoFactor({
    required String challengeToken,
    String? code,
    String? recoveryCode,
  }) {
    if ((code == null) == (recoveryCode == null)) {
      throw ArgumentError('Pass exactly one of code and recoveryCode.');
    }
    return guardRequest(() async {
      final session = await _remote.twoFactorChallenge(
        challengeToken: challengeToken,
        code: code,
        recoveryCode: recoveryCode,
      );
      return _signIn(session);
    });
  }

  @override
  Future<Result<void>> refresh() => guardRequest(() async {
    final current = await _store.readTokens();
    if (current == null) {
      throw const UnauthenticatedFailure();
    }
    final tokens = await _refresher(current.refreshToken);
    if (tokens == null) {
      await _store.clearTokens();
      _onSignedOut();
      throw const UnauthenticatedFailure();
    }
    await _store.writeTokens(tokens);
  });

  @override
  Future<Result<AuthUser>> me() => guardRequest(() async {
    final user = await _remote.me();
    return user.toEntity();
  });

  @override
  Future<Result<void>> logout() => _signOut(_remote.logout);

  @override
  Future<Result<void>> logoutAll() => _signOut(_remote.logoutAll);

  Future<Result<void>> _signOut(Future<void> Function() revoke) async {
    final result = await guardRequest(revoke);
    await _store.wipe();
    _onSignedOut();
    return result;
  }

  @override
  Future<bool> hasSession() => _store.hasSession();
}
