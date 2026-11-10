import 'package:freezed_annotation/freezed_annotation.dart';

part 'search.freezed.dart';

/// One instant suggestion: [type] is a game, platform, product or SKU match.
@freezed
abstract class SearchSuggestion with _$SearchSuggestion {
  const factory SearchSuggestion({
    required String type,
    required String title,
    required String slug,
  }) = _SearchSuggestion;
}
