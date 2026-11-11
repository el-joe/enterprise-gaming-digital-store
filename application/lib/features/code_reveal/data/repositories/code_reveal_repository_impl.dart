import 'package:application/core/network/result.dart';
import 'package:application/features/code_reveal/data/datasources/code_reveal_remote_data_source.dart';
import 'package:application/features/code_reveal/domain/entities/revealed_code.dart';
import 'package:application/features/code_reveal/domain/repositories/code_reveal_repository.dart';

class CodeRevealRepositoryImpl implements CodeRevealRepository {
  CodeRevealRepositoryImpl(this._remote);

  final CodeRevealRemoteDataSource _remote;

  @override
  Future<Result<void>> confirmPassword(String password) =>
      guardRequest(() => _remote.confirmPassword(password));

  @override
  Future<Result<RevealedCode>> reveal(
    RevealTarget target, {
    String? deviceIntegrity,
  }) => guardRequest(() async {
    final dto = target.guest
        ? await _remote.revealGuest(
            target.orderPublicId,
            target.unitPublicId,
            deviceIntegrity: deviceIntegrity,
          )
        : await _remote.revealAccount(
            target.orderPublicId,
            target.unitPublicId,
            deviceIntegrity: deviceIntegrity,
          );
    return dto.toEntity();
  });
}
