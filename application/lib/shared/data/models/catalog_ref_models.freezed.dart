// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'catalog_ref_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$MediaImageDto {

 String get path; String get url; String? get alt; int? get width; int? get height; Map<String, String> get formats;
/// Create a copy of MediaImageDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<MediaImageDto> get copyWith => _$MediaImageDtoCopyWithImpl<MediaImageDto>(this as MediaImageDto, _$identity);

  /// Serializes this MediaImageDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as MediaImageDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MediaImageDto&&(identical(other.path, _this.path) || other.path == _this.path)&&(identical(other.url, _this.url) || other.url == _this.url)&&(identical(other.alt, _this.alt) || other.alt == _this.alt)&&(identical(other.width, _this.width) || other.width == _this.width)&&(identical(other.height, _this.height) || other.height == _this.height)&&const DeepCollectionEquality().equals(other.formats, _this.formats));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as MediaImageDto;
  return Object.hash(runtimeType,_this.path,_this.url,_this.alt,_this.width,_this.height,const DeepCollectionEquality().hash(_this.formats));
}

@override
String toString() {
  final _this = this as MediaImageDto;
  return 'MediaImageDto(path: ${_this.path}, url: ${_this.url}, alt: ${_this.alt}, width: ${_this.width}, height: ${_this.height}, formats: ${_this.formats})';
}


}

/// @nodoc
abstract mixin class $MediaImageDtoCopyWith<$Res>  {
  factory $MediaImageDtoCopyWith(MediaImageDto value, $Res Function(MediaImageDto) _then) = _$MediaImageDtoCopyWithImpl;
@useResult
$Res call({
 String path, String url, String? alt, int? width, int? height, Map<String, String> formats
});




}
/// @nodoc
class _$MediaImageDtoCopyWithImpl<$Res>
    implements $MediaImageDtoCopyWith<$Res> {
  _$MediaImageDtoCopyWithImpl(this._self, this._then);

  final MediaImageDto _self;
  final $Res Function(MediaImageDto) _then;

/// Create a copy of MediaImageDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? path = null,Object? url = null,Object? alt = freezed,Object? width = freezed,Object? height = freezed,Object? formats = null,}) {
  return _then(MediaImageDto(
path: null == path ? _self.path : path // ignore: cast_nullable_to_non_nullable
as String,url: null == url ? _self.url : url // ignore: cast_nullable_to_non_nullable
as String,alt: freezed == alt ? _self.alt : alt // ignore: cast_nullable_to_non_nullable
as String?,width: freezed == width ? _self.width : width // ignore: cast_nullable_to_non_nullable
as int?,height: freezed == height ? _self.height : height // ignore: cast_nullable_to_non_nullable
as int?,formats: null == formats ? _self.formats : formats // ignore: cast_nullable_to_non_nullable
as Map<String, String>,
  ));
}

}


/// Adds pattern-matching-related methods to [MediaImageDto].
extension MediaImageDtoPatterns on MediaImageDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _MediaImageDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _MediaImageDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _MediaImageDto value)  $default,){
final _that = this;
switch (_that) {
case _MediaImageDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _MediaImageDto value)?  $default,){
final _that = this;
switch (_that) {
case _MediaImageDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String path,  String url,  String? alt,  int? width,  int? height,  Map<String, String> formats)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _MediaImageDto() when $default != null:
return $default(_that.path,_that.url,_that.alt,_that.width,_that.height,_that.formats);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String path,  String url,  String? alt,  int? width,  int? height,  Map<String, String> formats)  $default,) {final _that = this;
switch (_that) {
case _MediaImageDto():
return $default(_that.path,_that.url,_that.alt,_that.width,_that.height,_that.formats);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String path,  String url,  String? alt,  int? width,  int? height,  Map<String, String> formats)?  $default,) {final _that = this;
switch (_that) {
case _MediaImageDto() when $default != null:
return $default(_that.path,_that.url,_that.alt,_that.width,_that.height,_that.formats);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _MediaImageDto extends MediaImageDto {
  const _MediaImageDto({required this.path, required this.url, this.alt, this.width, this.height,  Map<String, String> formats = const <String, String>{}}): _formats = formats,super._();
  factory _MediaImageDto.fromJson(Map<String, dynamic> json) => _$MediaImageDtoFromJson(json);

@override final  String path;
@override final  String url;
@override final  String? alt;
@override final  int? width;
@override final  int? height;
 final  Map<String, String> _formats;
@override@JsonKey() Map<String, String> get formats {
  if (_formats is EqualUnmodifiableMapView) return _formats;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(_formats);
}


/// Create a copy of MediaImageDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$MediaImageDtoCopyWith<_MediaImageDto> get copyWith => __$MediaImageDtoCopyWithImpl<_MediaImageDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$MediaImageDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _MediaImageDto&&(identical(other.path, path) || other.path == path)&&(identical(other.url, url) || other.url == url)&&(identical(other.alt, alt) || other.alt == alt)&&(identical(other.width, width) || other.width == width)&&(identical(other.height, height) || other.height == height)&&const DeepCollectionEquality().equals(other.formats, _formats));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,path,url,alt,width,height,const DeepCollectionEquality().hash(_formats));
}

@override
String toString() {
    return 'MediaImageDto(path: $path, url: $url, alt: $alt, width: $width, height: $height, formats: $formats)';
}


}

/// @nodoc
abstract mixin class _$MediaImageDtoCopyWith<$Res> implements $MediaImageDtoCopyWith<$Res> {
  factory _$MediaImageDtoCopyWith(_MediaImageDto value, $Res Function(_MediaImageDto) _then) = __$MediaImageDtoCopyWithImpl;
@override @useResult
$Res call({
 String path, String url, String? alt, int? width, int? height, Map<String, String> formats
});




}
/// @nodoc
class __$MediaImageDtoCopyWithImpl<$Res>
    implements _$MediaImageDtoCopyWith<$Res> {
  __$MediaImageDtoCopyWithImpl(this._self, this._then);

  final _MediaImageDto _self;
  final $Res Function(_MediaImageDto) _then;

/// Create a copy of MediaImageDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? path = null,Object? url = null,Object? alt = freezed,Object? width = freezed,Object? height = freezed,Object? formats = null,}) {
  return _then(_MediaImageDto(
path: null == path ? _self.path : path // ignore: cast_nullable_to_non_nullable
as String,url: null == url ? _self.url : url // ignore: cast_nullable_to_non_nullable
as String,alt: freezed == alt ? _self.alt : alt // ignore: cast_nullable_to_non_nullable
as String?,width: freezed == width ? _self.width : width // ignore: cast_nullable_to_non_nullable
as int?,height: freezed == height ? _self.height : height // ignore: cast_nullable_to_non_nullable
as int?,formats: null == formats ? _self._formats : formats // ignore: cast_nullable_to_non_nullable
as Map<String, String>,
  ));
}


}


/// @nodoc
mixin _$LinkTargetDto {

 String get type; String get value; String get url;
/// Create a copy of LinkTargetDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$LinkTargetDtoCopyWith<LinkTargetDto> get copyWith => _$LinkTargetDtoCopyWithImpl<LinkTargetDto>(this as LinkTargetDto, _$identity);

  /// Serializes this LinkTargetDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as LinkTargetDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is LinkTargetDto&&(identical(other.type, _this.type) || other.type == _this.type)&&(identical(other.value, _this.value) || other.value == _this.value)&&(identical(other.url, _this.url) || other.url == _this.url));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as LinkTargetDto;
  return Object.hash(runtimeType,_this.type,_this.value,_this.url);
}

@override
String toString() {
  final _this = this as LinkTargetDto;
  return 'LinkTargetDto(type: ${_this.type}, value: ${_this.value}, url: ${_this.url})';
}


}

/// @nodoc
abstract mixin class $LinkTargetDtoCopyWith<$Res>  {
  factory $LinkTargetDtoCopyWith(LinkTargetDto value, $Res Function(LinkTargetDto) _then) = _$LinkTargetDtoCopyWithImpl;
@useResult
$Res call({
 String type, String value, String url
});




}
/// @nodoc
class _$LinkTargetDtoCopyWithImpl<$Res>
    implements $LinkTargetDtoCopyWith<$Res> {
  _$LinkTargetDtoCopyWithImpl(this._self, this._then);

  final LinkTargetDto _self;
  final $Res Function(LinkTargetDto) _then;

/// Create a copy of LinkTargetDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? type = null,Object? value = null,Object? url = null,}) {
  return _then(LinkTargetDto(
type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,value: null == value ? _self.value : value // ignore: cast_nullable_to_non_nullable
as String,url: null == url ? _self.url : url // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [LinkTargetDto].
extension LinkTargetDtoPatterns on LinkTargetDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _LinkTargetDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _LinkTargetDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _LinkTargetDto value)  $default,){
final _that = this;
switch (_that) {
case _LinkTargetDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _LinkTargetDto value)?  $default,){
final _that = this;
switch (_that) {
case _LinkTargetDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String type,  String value,  String url)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _LinkTargetDto() when $default != null:
return $default(_that.type,_that.value,_that.url);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String type,  String value,  String url)  $default,) {final _that = this;
switch (_that) {
case _LinkTargetDto():
return $default(_that.type,_that.value,_that.url);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String type,  String value,  String url)?  $default,) {final _that = this;
switch (_that) {
case _LinkTargetDto() when $default != null:
return $default(_that.type,_that.value,_that.url);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _LinkTargetDto extends LinkTargetDto {
  const _LinkTargetDto({required this.type, required this.value, required this.url}): super._();
  factory _LinkTargetDto.fromJson(Map<String, dynamic> json) => _$LinkTargetDtoFromJson(json);

@override final  String type;
@override final  String value;
@override final  String url;

/// Create a copy of LinkTargetDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$LinkTargetDtoCopyWith<_LinkTargetDto> get copyWith => __$LinkTargetDtoCopyWithImpl<_LinkTargetDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$LinkTargetDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _LinkTargetDto&&(identical(other.type, type) || other.type == type)&&(identical(other.value, value) || other.value == value)&&(identical(other.url, url) || other.url == url));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,type,value,url);
}

@override
String toString() {
    return 'LinkTargetDto(type: $type, value: $value, url: $url)';
}


}

/// @nodoc
abstract mixin class _$LinkTargetDtoCopyWith<$Res> implements $LinkTargetDtoCopyWith<$Res> {
  factory _$LinkTargetDtoCopyWith(_LinkTargetDto value, $Res Function(_LinkTargetDto) _then) = __$LinkTargetDtoCopyWithImpl;
@override @useResult
$Res call({
 String type, String value, String url
});




}
/// @nodoc
class __$LinkTargetDtoCopyWithImpl<$Res>
    implements _$LinkTargetDtoCopyWith<$Res> {
  __$LinkTargetDtoCopyWithImpl(this._self, this._then);

  final _LinkTargetDto _self;
  final $Res Function(_LinkTargetDto) _then;

/// Create a copy of LinkTargetDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? type = null,Object? value = null,Object? url = null,}) {
  return _then(_LinkTargetDto(
type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,value: null == value ? _self.value : value // ignore: cast_nullable_to_non_nullable
as String,url: null == url ? _self.url : url // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}


/// @nodoc
mixin _$NamedSlugDto {

 String get name; String get slug;
/// Create a copy of NamedSlugDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$NamedSlugDtoCopyWith<NamedSlugDto> get copyWith => _$NamedSlugDtoCopyWithImpl<NamedSlugDto>(this as NamedSlugDto, _$identity);

  /// Serializes this NamedSlugDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as NamedSlugDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is NamedSlugDto&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as NamedSlugDto;
  return Object.hash(runtimeType,_this.name,_this.slug);
}

@override
String toString() {
  final _this = this as NamedSlugDto;
  return 'NamedSlugDto(name: ${_this.name}, slug: ${_this.slug})';
}


}

/// @nodoc
abstract mixin class $NamedSlugDtoCopyWith<$Res>  {
  factory $NamedSlugDtoCopyWith(NamedSlugDto value, $Res Function(NamedSlugDto) _then) = _$NamedSlugDtoCopyWithImpl;
@useResult
$Res call({
 String name, String slug
});




}
/// @nodoc
class _$NamedSlugDtoCopyWithImpl<$Res>
    implements $NamedSlugDtoCopyWith<$Res> {
  _$NamedSlugDtoCopyWithImpl(this._self, this._then);

  final NamedSlugDto _self;
  final $Res Function(NamedSlugDto) _then;

/// Create a copy of NamedSlugDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? name = null,Object? slug = null,}) {
  return _then(NamedSlugDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [NamedSlugDto].
extension NamedSlugDtoPatterns on NamedSlugDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _NamedSlugDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _NamedSlugDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _NamedSlugDto value)  $default,){
final _that = this;
switch (_that) {
case _NamedSlugDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _NamedSlugDto value)?  $default,){
final _that = this;
switch (_that) {
case _NamedSlugDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String name,  String slug)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _NamedSlugDto() when $default != null:
return $default(_that.name,_that.slug);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String name,  String slug)  $default,) {final _that = this;
switch (_that) {
case _NamedSlugDto():
return $default(_that.name,_that.slug);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String name,  String slug)?  $default,) {final _that = this;
switch (_that) {
case _NamedSlugDto() when $default != null:
return $default(_that.name,_that.slug);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _NamedSlugDto extends NamedSlugDto {
  const _NamedSlugDto({required this.name, required this.slug}): super._();
  factory _NamedSlugDto.fromJson(Map<String, dynamic> json) => _$NamedSlugDtoFromJson(json);

@override final  String name;
@override final  String slug;

/// Create a copy of NamedSlugDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$NamedSlugDtoCopyWith<_NamedSlugDto> get copyWith => __$NamedSlugDtoCopyWithImpl<_NamedSlugDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$NamedSlugDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _NamedSlugDto&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,name,slug);
}

@override
String toString() {
    return 'NamedSlugDto(name: $name, slug: $slug)';
}


}

/// @nodoc
abstract mixin class _$NamedSlugDtoCopyWith<$Res> implements $NamedSlugDtoCopyWith<$Res> {
  factory _$NamedSlugDtoCopyWith(_NamedSlugDto value, $Res Function(_NamedSlugDto) _then) = __$NamedSlugDtoCopyWithImpl;
@override @useResult
$Res call({
 String name, String slug
});




}
/// @nodoc
class __$NamedSlugDtoCopyWithImpl<$Res>
    implements _$NamedSlugDtoCopyWith<$Res> {
  __$NamedSlugDtoCopyWithImpl(this._self, this._then);

  final _NamedSlugDto _self;
  final $Res Function(_NamedSlugDto) _then;

/// Create a copy of NamedSlugDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? name = null,Object? slug = null,}) {
  return _then(_NamedSlugDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}


/// @nodoc
mixin _$ProductCardDto {

 String get publicId; String get name; String get slug; String get deliveryType; bool get priceMayVary; bool get isFeatured; String? get shortDescription; NamedSlugDto? get platform; NamedSlugDto? get category; MediaImageDto? get image;@NullableMoneyConverter() Money? get priceFrom; String? get ratingAvg; LinkTargetDto? get link;
/// Create a copy of ProductCardDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ProductCardDtoCopyWith<ProductCardDto> get copyWith => _$ProductCardDtoCopyWithImpl<ProductCardDto>(this as ProductCardDto, _$identity);

  /// Serializes this ProductCardDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as ProductCardDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ProductCardDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug)&&(identical(other.deliveryType, _this.deliveryType) || other.deliveryType == _this.deliveryType)&&(identical(other.priceMayVary, _this.priceMayVary) || other.priceMayVary == _this.priceMayVary)&&(identical(other.isFeatured, _this.isFeatured) || other.isFeatured == _this.isFeatured)&&(identical(other.shortDescription, _this.shortDescription) || other.shortDescription == _this.shortDescription)&&(identical(other.platform, _this.platform) || other.platform == _this.platform)&&(identical(other.category, _this.category) || other.category == _this.category)&&(identical(other.image, _this.image) || other.image == _this.image)&&(identical(other.priceFrom, _this.priceFrom) || other.priceFrom == _this.priceFrom)&&(identical(other.ratingAvg, _this.ratingAvg) || other.ratingAvg == _this.ratingAvg)&&(identical(other.link, _this.link) || other.link == _this.link));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as ProductCardDto;
  return Object.hash(runtimeType,_this.publicId,_this.name,_this.slug,_this.deliveryType,_this.priceMayVary,_this.isFeatured,_this.shortDescription,_this.platform,_this.category,_this.image,_this.priceFrom,_this.ratingAvg,_this.link);
}

@override
String toString() {
  final _this = this as ProductCardDto;
  return 'ProductCardDto(publicId: ${_this.publicId}, name: ${_this.name}, slug: ${_this.slug}, deliveryType: ${_this.deliveryType}, priceMayVary: ${_this.priceMayVary}, isFeatured: ${_this.isFeatured}, shortDescription: ${_this.shortDescription}, platform: ${_this.platform}, category: ${_this.category}, image: ${_this.image}, priceFrom: ${_this.priceFrom}, ratingAvg: ${_this.ratingAvg}, link: ${_this.link})';
}


}

/// @nodoc
abstract mixin class $ProductCardDtoCopyWith<$Res>  {
  factory $ProductCardDtoCopyWith(ProductCardDto value, $Res Function(ProductCardDto) _then) = _$ProductCardDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String name, String slug, String deliveryType, bool priceMayVary, bool isFeatured, String? shortDescription, NamedSlugDto? platform, NamedSlugDto? category, MediaImageDto? image,@NullableMoneyConverter() Money? priceFrom, String? ratingAvg, LinkTargetDto? link
});


$NamedSlugDtoCopyWith<$Res>? get platform;$NamedSlugDtoCopyWith<$Res>? get category;$MediaImageDtoCopyWith<$Res>? get image;$LinkTargetDtoCopyWith<$Res>? get link;

}
/// @nodoc
class _$ProductCardDtoCopyWithImpl<$Res>
    implements $ProductCardDtoCopyWith<$Res> {
  _$ProductCardDtoCopyWithImpl(this._self, this._then);

  final ProductCardDto _self;
  final $Res Function(ProductCardDto) _then;

/// Create a copy of ProductCardDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? name = null,Object? slug = null,Object? deliveryType = null,Object? priceMayVary = null,Object? isFeatured = null,Object? shortDescription = freezed,Object? platform = freezed,Object? category = freezed,Object? image = freezed,Object? priceFrom = freezed,Object? ratingAvg = freezed,Object? link = freezed,}) {
  return _then(ProductCardDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,deliveryType: null == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String,priceMayVary: null == priceMayVary ? _self.priceMayVary : priceMayVary // ignore: cast_nullable_to_non_nullable
as bool,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,shortDescription: freezed == shortDescription ? _self.shortDescription : shortDescription // ignore: cast_nullable_to_non_nullable
as String?,platform: freezed == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as NamedSlugDto?,category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as NamedSlugDto?,image: freezed == image ? _self.image : image // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,priceFrom: freezed == priceFrom ? _self.priceFrom : priceFrom // ignore: cast_nullable_to_non_nullable
as Money?,ratingAvg: freezed == ratingAvg ? _self.ratingAvg : ratingAvg // ignore: cast_nullable_to_non_nullable
as String?,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTargetDto?,
  ));
}
/// Create a copy of ProductCardDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$NamedSlugDtoCopyWith<$Res>? get platform {
    if (_self.platform == null) {
    return null;
  }

  return $NamedSlugDtoCopyWith<$Res>(_self.platform!, (value) {
    return _then(_self.copyWith(platform: value));
  });
}/// Create a copy of ProductCardDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$NamedSlugDtoCopyWith<$Res>? get category {
    if (_self.category == null) {
    return null;
  }

  return $NamedSlugDtoCopyWith<$Res>(_self.category!, (value) {
    return _then(_self.copyWith(category: value));
  });
}/// Create a copy of ProductCardDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get image {
    if (_self.image == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.image!, (value) {
    return _then(_self.copyWith(image: value));
  });
}/// Create a copy of ProductCardDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetDtoCopyWith<$Res>? get link {
    if (_self.link == null) {
    return null;
  }

  return $LinkTargetDtoCopyWith<$Res>(_self.link!, (value) {
    return _then(_self.copyWith(link: value));
  });
}
}


/// Adds pattern-matching-related methods to [ProductCardDto].
extension ProductCardDtoPatterns on ProductCardDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ProductCardDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ProductCardDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ProductCardDto value)  $default,){
final _that = this;
switch (_that) {
case _ProductCardDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ProductCardDto value)?  $default,){
final _that = this;
switch (_that) {
case _ProductCardDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String name,  String slug,  String deliveryType,  bool priceMayVary,  bool isFeatured,  String? shortDescription,  NamedSlugDto? platform,  NamedSlugDto? category,  MediaImageDto? image, @NullableMoneyConverter()  Money? priceFrom,  String? ratingAvg,  LinkTargetDto? link)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ProductCardDto() when $default != null:
return $default(_that.publicId,_that.name,_that.slug,_that.deliveryType,_that.priceMayVary,_that.isFeatured,_that.shortDescription,_that.platform,_that.category,_that.image,_that.priceFrom,_that.ratingAvg,_that.link);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String name,  String slug,  String deliveryType,  bool priceMayVary,  bool isFeatured,  String? shortDescription,  NamedSlugDto? platform,  NamedSlugDto? category,  MediaImageDto? image, @NullableMoneyConverter()  Money? priceFrom,  String? ratingAvg,  LinkTargetDto? link)  $default,) {final _that = this;
switch (_that) {
case _ProductCardDto():
return $default(_that.publicId,_that.name,_that.slug,_that.deliveryType,_that.priceMayVary,_that.isFeatured,_that.shortDescription,_that.platform,_that.category,_that.image,_that.priceFrom,_that.ratingAvg,_that.link);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String name,  String slug,  String deliveryType,  bool priceMayVary,  bool isFeatured,  String? shortDescription,  NamedSlugDto? platform,  NamedSlugDto? category,  MediaImageDto? image, @NullableMoneyConverter()  Money? priceFrom,  String? ratingAvg,  LinkTargetDto? link)?  $default,) {final _that = this;
switch (_that) {
case _ProductCardDto() when $default != null:
return $default(_that.publicId,_that.name,_that.slug,_that.deliveryType,_that.priceMayVary,_that.isFeatured,_that.shortDescription,_that.platform,_that.category,_that.image,_that.priceFrom,_that.ratingAvg,_that.link);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _ProductCardDto extends ProductCardDto {
  const _ProductCardDto({required this.publicId, required this.name, required this.slug, required this.deliveryType, required this.priceMayVary, required this.isFeatured, this.shortDescription, this.platform, this.category, this.image, @NullableMoneyConverter() this.priceFrom, this.ratingAvg, this.link}): super._();
  factory _ProductCardDto.fromJson(Map<String, dynamic> json) => _$ProductCardDtoFromJson(json);

@override final  String publicId;
@override final  String name;
@override final  String slug;
@override final  String deliveryType;
@override final  bool priceMayVary;
@override final  bool isFeatured;
@override final  String? shortDescription;
@override final  NamedSlugDto? platform;
@override final  NamedSlugDto? category;
@override final  MediaImageDto? image;
@override@NullableMoneyConverter() final  Money? priceFrom;
@override final  String? ratingAvg;
@override final  LinkTargetDto? link;

/// Create a copy of ProductCardDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ProductCardDtoCopyWith<_ProductCardDto> get copyWith => __$ProductCardDtoCopyWithImpl<_ProductCardDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$ProductCardDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ProductCardDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug)&&(identical(other.deliveryType, deliveryType) || other.deliveryType == deliveryType)&&(identical(other.priceMayVary, priceMayVary) || other.priceMayVary == priceMayVary)&&(identical(other.isFeatured, isFeatured) || other.isFeatured == isFeatured)&&(identical(other.shortDescription, shortDescription) || other.shortDescription == shortDescription)&&(identical(other.platform, platform) || other.platform == platform)&&(identical(other.category, category) || other.category == category)&&(identical(other.image, image) || other.image == image)&&(identical(other.priceFrom, priceFrom) || other.priceFrom == priceFrom)&&(identical(other.ratingAvg, ratingAvg) || other.ratingAvg == ratingAvg)&&(identical(other.link, link) || other.link == link));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,name,slug,deliveryType,priceMayVary,isFeatured,shortDescription,platform,category,image,priceFrom,ratingAvg,link);
}

@override
String toString() {
    return 'ProductCardDto(publicId: $publicId, name: $name, slug: $slug, deliveryType: $deliveryType, priceMayVary: $priceMayVary, isFeatured: $isFeatured, shortDescription: $shortDescription, platform: $platform, category: $category, image: $image, priceFrom: $priceFrom, ratingAvg: $ratingAvg, link: $link)';
}


}

/// @nodoc
abstract mixin class _$ProductCardDtoCopyWith<$Res> implements $ProductCardDtoCopyWith<$Res> {
  factory _$ProductCardDtoCopyWith(_ProductCardDto value, $Res Function(_ProductCardDto) _then) = __$ProductCardDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String name, String slug, String deliveryType, bool priceMayVary, bool isFeatured, String? shortDescription, NamedSlugDto? platform, NamedSlugDto? category, MediaImageDto? image,@NullableMoneyConverter() Money? priceFrom, String? ratingAvg, LinkTargetDto? link
});


@override $NamedSlugDtoCopyWith<$Res>? get platform;@override $NamedSlugDtoCopyWith<$Res>? get category;@override $MediaImageDtoCopyWith<$Res>? get image;@override $LinkTargetDtoCopyWith<$Res>? get link;

}
/// @nodoc
class __$ProductCardDtoCopyWithImpl<$Res>
    implements _$ProductCardDtoCopyWith<$Res> {
  __$ProductCardDtoCopyWithImpl(this._self, this._then);

  final _ProductCardDto _self;
  final $Res Function(_ProductCardDto) _then;

/// Create a copy of ProductCardDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? name = null,Object? slug = null,Object? deliveryType = null,Object? priceMayVary = null,Object? isFeatured = null,Object? shortDescription = freezed,Object? platform = freezed,Object? category = freezed,Object? image = freezed,Object? priceFrom = freezed,Object? ratingAvg = freezed,Object? link = freezed,}) {
  return _then(_ProductCardDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,deliveryType: null == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String,priceMayVary: null == priceMayVary ? _self.priceMayVary : priceMayVary // ignore: cast_nullable_to_non_nullable
as bool,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,shortDescription: freezed == shortDescription ? _self.shortDescription : shortDescription // ignore: cast_nullable_to_non_nullable
as String?,platform: freezed == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as NamedSlugDto?,category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as NamedSlugDto?,image: freezed == image ? _self.image : image // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,priceFrom: freezed == priceFrom ? _self.priceFrom : priceFrom // ignore: cast_nullable_to_non_nullable
as Money?,ratingAvg: freezed == ratingAvg ? _self.ratingAvg : ratingAvg // ignore: cast_nullable_to_non_nullable
as String?,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTargetDto?,
  ));
}

/// Create a copy of ProductCardDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$NamedSlugDtoCopyWith<$Res>? get platform {
    if (_self.platform == null) {
    return null;
  }

  return $NamedSlugDtoCopyWith<$Res>(_self.platform!, (value) {
    return _then(_self.copyWith(platform: value));
  });
}/// Create a copy of ProductCardDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$NamedSlugDtoCopyWith<$Res>? get category {
    if (_self.category == null) {
    return null;
  }

  return $NamedSlugDtoCopyWith<$Res>(_self.category!, (value) {
    return _then(_self.copyWith(category: value));
  });
}/// Create a copy of ProductCardDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get image {
    if (_self.image == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.image!, (value) {
    return _then(_self.copyWith(image: value));
  });
}/// Create a copy of ProductCardDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetDtoCopyWith<$Res>? get link {
    if (_self.link == null) {
    return null;
  }

  return $LinkTargetDtoCopyWith<$Res>(_self.link!, (value) {
    return _then(_self.copyWith(link: value));
  });
}
}

// dart format on
