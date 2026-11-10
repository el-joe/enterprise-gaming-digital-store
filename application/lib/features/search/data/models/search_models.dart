import 'package:application/features/search/domain/entities/search.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'search_models.freezed.dart';
part 'search_models.g.dart';

@freezed
abstract class SearchSuggestionDto with _$SearchSuggestionDto {
  const factory SearchSuggestionDto({
    required String type,
    required String title,
    required String slug,
  }) = _SearchSuggestionDto;

  const SearchSuggestionDto._();

  factory SearchSuggestionDto.fromJson(Map<String, dynamic> json) =>
      _$SearchSuggestionDtoFromJson(json);

  SearchSuggestion toEntity() =>
      SearchSuggestion(type: type, title: title, slug: slug);
}
