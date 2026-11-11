import 'package:application/core/network/dio_client.dart';
import 'package:application/features/code_reveal/data/datasources/code_reveal_remote_data_source.dart';
import 'package:application/features/code_reveal/data/repositories/code_reveal_repository_impl.dart';
import 'package:application/features/code_reveal/domain/repositories/code_reveal_repository.dart';
import 'package:application/features/code_reveal/domain/usecases/reveal_code.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'code_reveal_providers.g.dart';

// Every provider here is auto-disposed and none holds a revealed code. Since M-A14 the code lives
// only in the state of the code screen's auto-disposed `RevealController`
// (presentation/controllers), which drops it on hide, on background, on capture, after 60 s and
// when the screen is left.

@riverpod
CodeRevealRemoteDataSource codeRevealRemoteDataSource(Ref ref) =>
    CodeRevealRemoteDataSource(ref.watch(apiClientProvider));

@riverpod
CodeRevealRepository codeRevealRepository(Ref ref) =>
    CodeRevealRepositoryImpl(ref.watch(codeRevealRemoteDataSourceProvider));

@riverpod
RevealCode revealCode(Ref ref) =>
    RevealCode(ref.watch(codeRevealRepositoryProvider));
