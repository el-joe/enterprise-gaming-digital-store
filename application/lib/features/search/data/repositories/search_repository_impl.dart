import 'package:application/core/network/result.dart';
import 'package:application/features/search/data/datasources/search_remote_data_source.dart';
import 'package:application/features/search/domain/entities/search.dart';
import 'package:application/features/search/domain/repositories/search_repository.dart';

class SearchRepositoryImpl implements SearchRepository {
  SearchRepositoryImpl(this._remote);

  final SearchRemoteDataSource _remote;

  @override
  Future<Result<List<SearchSuggestion>>> suggest(
    String query, {
    bool commit = false,
  }) => guardRequest(
    () async => [
      for (final dto in await _remote.suggest(query, commit: commit))
        dto.toEntity(),
    ],
  );

  @override
  Future<Result<List<String>>> popular() => guardRequest(_remote.popular);
}
