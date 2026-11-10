// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'search_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_SearchSuggestionDto _$SearchSuggestionDtoFromJson(Map<String, dynamic> json) =>
    _SearchSuggestionDto(
      type: json['type'] as String,
      title: json['title'] as String,
      slug: json['slug'] as String,
    );

Map<String, dynamic> _$SearchSuggestionDtoToJson(
  _SearchSuggestionDto instance,
) => <String, dynamic>{
  'type': instance.type,
  'title': instance.title,
  'slug': instance.slug,
};
