import 'package:application/core/network/result.dart';
import 'package:application/features/search/domain/entities/search.dart';

abstract interface class SearchRepository {
  /// [query] is 2 to 100 characters. Pass [commit] on submit so the term counts as a search.
  Future<Result<List<SearchSuggestion>>> suggest(
    String query, {
    bool commit = false,
  });

  Future<Result<List<String>>> popular();
}
