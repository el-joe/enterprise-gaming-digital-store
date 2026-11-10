// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'search.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$SearchSuggestion {

 String get type; String get title; String get slug;
/// Create a copy of SearchSuggestion
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SearchSuggestionCopyWith<SearchSuggestion> get copyWith => _$SearchSuggestionCopyWithImpl<SearchSuggestion>(this as SearchSuggestion, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as SearchSuggestion;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SearchSuggestion&&(identical(other.type, _this.type) || other.type == _this.type)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.slug, _this.slug) || other.slug == _this.slug));
}


@override
int get hashCode {
  final _this = this as SearchSuggestion;
  return Object.hash(runtimeType,_this.type,_this.title,_this.slug);
}

@override
String toString() {
  final _this = this as SearchSuggestion;
  return 'SearchSuggestion(type: ${_this.type}, title: ${_this.title}, slug: ${_this.slug})';
}


}

/// @nodoc
abstract mixin class $SearchSuggestionCopyWith<$Res>  {
  factory $SearchSuggestionCopyWith(SearchSuggestion value, $Res Function(SearchSuggestion) _then) = _$SearchSuggestionCopyWithImpl;
@useResult
$Res call({
 String type, String title, String slug
});




}
/// @nodoc
class _$SearchSuggestionCopyWithImpl<$Res>
    implements $SearchSuggestionCopyWith<$Res> {
  _$SearchSuggestionCopyWithImpl(this._self, this._then);

  final SearchSuggestion _self;
  final $Res Function(SearchSuggestion) _then;

/// Create a copy of SearchSuggestion
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? type = null,Object? title = null,Object? slug = null,}) {
  return _then(SearchSuggestion(
type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [SearchSuggestion].
extension SearchSuggestionPatterns on SearchSuggestion {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _SearchSuggestion value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _SearchSuggestion() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _SearchSuggestion value)  $default,){
final _that = this;
switch (_that) {
case _SearchSuggestion():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _SearchSuggestion value)?  $default,){
final _that = this;
switch (_that) {
case _SearchSuggestion() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String type,  String title,  String slug)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _SearchSuggestion() when $default != null:
return $default(_that.type,_that.title,_that.slug);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String type,  String title,  String slug)  $default,) {final _that = this;
switch (_that) {
case _SearchSuggestion():
return $default(_that.type,_that.title,_that.slug);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String type,  String title,  String slug)?  $default,) {final _that = this;
switch (_that) {
case _SearchSuggestion() when $default != null:
return $default(_that.type,_that.title,_that.slug);case _:
  return null;

}
}

}

/// @nodoc


class _SearchSuggestion implements SearchSuggestion {
  const _SearchSuggestion({required this.type, required this.title, required this.slug});
  

@override final  String type;
@override final  String title;
@override final  String slug;

/// Create a copy of SearchSuggestion
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$SearchSuggestionCopyWith<_SearchSuggestion> get copyWith => __$SearchSuggestionCopyWithImpl<_SearchSuggestion>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _SearchSuggestion&&(identical(other.type, type) || other.type == type)&&(identical(other.title, title) || other.title == title)&&(identical(other.slug, slug) || other.slug == slug));
}


@override
int get hashCode {
    return Object.hash(runtimeType,type,title,slug);
}

@override
String toString() {
    return 'SearchSuggestion(type: $type, title: $title, slug: $slug)';
}


}

/// @nodoc
abstract mixin class _$SearchSuggestionCopyWith<$Res> implements $SearchSuggestionCopyWith<$Res> {
  factory _$SearchSuggestionCopyWith(_SearchSuggestion value, $Res Function(_SearchSuggestion) _then) = __$SearchSuggestionCopyWithImpl;
@override @useResult
$Res call({
 String type, String title, String slug
});




}
/// @nodoc
class __$SearchSuggestionCopyWithImpl<$Res>
    implements _$SearchSuggestionCopyWith<$Res> {
  __$SearchSuggestionCopyWithImpl(this._self, this._then);

  final _SearchSuggestion _self;
  final $Res Function(_SearchSuggestion) _then;

/// Create a copy of SearchSuggestion
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? type = null,Object? title = null,Object? slug = null,}) {
  return _then(_SearchSuggestion(
type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

// dart format on
