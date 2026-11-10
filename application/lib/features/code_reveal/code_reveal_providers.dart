import 'package:application/core/network/dio_client.dart';
import 'package:application/features/code_reveal/data/datasources/code_reveal_remote_data_source.dart';
import 'package:application/features/code_reveal/data/repositories/code_reveal_repository_impl.dart';
import 'package:application/features/code_reveal/domain/repositories/code_reveal_repository.dart';
import 'package:application/features/code_reveal/domain/usecases/reveal_code.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'code_reveal_providers.g.dart';

// Every provider here is auto-disposed and none holds a revealed code: the code lives only in the
// reveal screen's widget state (M-A14), which clears it on dispose, on background and on timeout.

@riverpod
CodeRevealRemoteDataSource codeRevealRemoteDataSource(Ref ref) =>
    CodeRevealRemoteDataSource(ref.watch(apiClientProvider));

@riverpod
CodeRevealRepository codeRevealRepository(Ref ref) =>
    CodeRevealRepositoryImpl(ref.watch(codeRevealRemoteDataSourceProvider));

@riverpod
RevealCode revealCode(Ref ref) =>
    RevealCode(ref.watch(codeRevealRepositoryProvider));
