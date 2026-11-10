// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'catalog_refs.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$MediaImage {

 String get path; Uri get url; String? get alt; int? get width; int? get height; Map<String, Uri> get formats;
/// Create a copy of MediaImage
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MediaImageCopyWith<MediaImage> get copyWith => _$MediaImageCopyWithImpl<MediaImage>(this as MediaImage, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as MediaImage;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MediaImage&&(identical(other.path, _this.path) || other.path == _this.path)&&(identical(other.url, _this.url) || other.url == _this.url)&&(identical(other.alt, _this.alt) || other.alt == _this.alt)&&(identical(other.width, _this.width) || other.width == _this.width)&&(identical(other.height, _this.height) || other.height == _this.height)&&const DeepCollectionEquality().equals(other.formats, _this.formats));
}


@override
int get hashCode {
  final _this = this as MediaImage;
  return Object.hash(runtimeType,_this.path,_this.url,_this.alt,_this.width,_this.height,const DeepCollectionEquality().hash(_this.formats));
}

@override
String toString() {
  final _this = this as MediaImage;
  return 'MediaImage(path: ${_this.path}, url: ${_this.url}, alt: ${_this.alt}, width: ${_this.width}, height: ${_this.height}, formats: ${_this.formats})';
}


}

/// @nodoc
abstract mixin class $MediaImageCopyWith<$Res>  {
  factory $MediaImageCopyWith(MediaImage value, $Res Function(MediaImage) _then) = _$MediaImageCopyWithImpl;
@useResult
$Res call({
 String path, Uri url, String? alt, int? width, int? height, Map<String, Uri> formats
});




}
/// @nodoc
class _$MediaImageCopyWithImpl<$Res>
    implements $MediaImageCopyWith<$Res> {
  _$MediaImageCopyWithImpl(this._self, this._then);

  final MediaImage _self;
  final $Res Function(MediaImage) _then;

/// Create a copy of MediaImage
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? path = null,Object? url = null,Object? alt = freezed,Object? width = freezed,Object? height = freezed,Object? formats = null,}) {
  return _then(MediaImage(
path: null == path ? _self.path : path // ignore: cast_nullable_to_non_nullable
as String,url: null == url ? _self.url : url // ignore: cast_nullable_to_non_nullable
as Uri,alt: freezed == alt ? _self.alt : alt // ignore: cast_nullable_to_non_nullable
as String?,width: freezed == width ? _self.width : width // ignore: cast_nullable_to_non_nullable
as int?,height: freezed == height ? _self.height : height // ignore: cast_nullable_to_non_nullable
as int?,formats: null == formats ? _self.formats : formats // ignore: cast_nullable_to_non_nullable
as Map<String, Uri>,
  ));
}

}


/// Adds pattern-matching-related methods to [MediaImage].
extension MediaImagePatterns on MediaImage {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _MediaImage value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _MediaImage() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _MediaImage value)  $default,){
final _that = this;
switch (_that) {
case _MediaImage():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _MediaImage value)?  $default,){
final _that = this;
switch (_that) {
case _MediaImage() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String path,  Uri url,  String? alt,  int? width,  int? height,  Map<String, Uri> formats)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _MediaImage() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String path,  Uri url,  String? alt,  int? width,  int? height,  Map<String, Uri> formats)  $default,) {final _that = this;
switch (_that) {
case _MediaImage():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String path,  Uri url,  String? alt,  int? width,  int? height,  Map<String, Uri> formats)?  $default,) {final _that = this;
switch (_that) {
case _MediaImage() when $default != null:
return $default(_that.path,_that.url,_that.alt,_that.width,_that.height,_that.formats);case _:
  return null;

}
}

}

/// @nodoc


class _MediaImage implements MediaImage {
  const _MediaImage({required this.path, required this.url, this.alt, this.width, this.height,  Map<String, Uri> formats = const <String, Uri>{}}): _formats = formats;
  

@override final  String path;
@override final  Uri url;
@override final  String? alt;
@override final  int? width;
@override final  int? height;
 final  Map<String, Uri> _formats;
@override@JsonKey() Map<String, Uri> get formats {
  if (_formats is EqualUnmodifiableMapView) return _formats;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(_formats);
}


/// Create a copy of MediaImage
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$MediaImageCopyWith<_MediaImage> get copyWith => __$MediaImageCopyWithImpl<_MediaImage>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _MediaImage&&(identical(other.path, path) || other.path == path)&&(identical(other.url, url) || other.url == url)&&(identical(other.alt, alt) || other.alt == alt)&&(identical(other.width, width) || other.width == width)&&(identical(other.height, height) || other.height == height)&&const DeepCollectionEquality().equals(other.formats, _formats));
}


@override
int get hashCode {
    return Object.hash(runtimeType,path,url,alt,width,height,const DeepCollectionEquality().hash(_formats));
}

@override
String toString() {
    return 'MediaImage(path: $path, url: $url, alt: $alt, width: $width, height: $height, formats: $formats)';
}


}

/// @nodoc
abstract mixin class _$MediaImageCopyWith<$Res> implements $MediaImageCopyWith<$Res> {
  factory _$MediaImageCopyWith(_MediaImage value, $Res Function(_MediaImage) _then) = __$MediaImageCopyWithImpl;
@override @useResult
$Res call({
 String path, Uri url, String? alt, int? width, int? height, Map<String, Uri> formats
});




}
/// @nodoc
class __$MediaImageCopyWithImpl<$Res>
    implements _$MediaImageCopyWith<$Res> {
  __$MediaImageCopyWithImpl(this._self, this._then);

  final _MediaImage _self;
  final $Res Function(_MediaImage) _then;

/// Create a copy of MediaImage
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? path = null,Object? url = null,Object? alt = freezed,Object? width = freezed,Object? height = freezed,Object? formats = null,}) {
  return _then(_MediaImage(
path: null == path ? _self.path : path // ignore: cast_nullable_to_non_nullable
as String,url: null == url ? _self.url : url // ignore: cast_nullable_to_non_nullable
as Uri,alt: freezed == alt ? _self.alt : alt // ignore: cast_nullable_to_non_nullable
as String?,width: freezed == width ? _self.width : width // ignore: cast_nullable_to_non_nullable
as int?,height: freezed == height ? _self.height : height // ignore: cast_nullable_to_non_nullable
as int?,formats: null == formats ? _self._formats : formats // ignore: cast_nullable_to_non_nullable
as Map<String, Uri>,
  ));
}


}

/// @nodoc
mixin _$LinkTarget {

 String get type; String get value; Uri get url;
/// Create a copy of LinkTarget
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$LinkTargetCopyWith<LinkTarget> get copyWith => _$LinkTargetCopyWithImpl<LinkTarget>(this as LinkTarget, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as LinkTarget;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is LinkTarget&&(identical(other.type, _this.type) || other.type == _this.type)&&(identical(other.value, _this.value) || other.value == _this.value)&&(identical(other.url, _this.url) || other.url == _this.url));
}


@override
int get hashCode {
  final _this = this as LinkTarget;
  return Object.hash(runtimeType,_this.type,_this.value,_this.url);
}

@override
String toString() {
  final _this = this as LinkTarget;
  return 'LinkTarget(type: ${_this.type}, value: ${_this.value}, url: ${_this.url})';
}


}

/// @nodoc
abstract mixin class $LinkTargetCopyWith<$Res>  {
  factory $LinkTargetCopyWith(LinkTarget value, $Res Function(LinkTarget) _then) = _$LinkTargetCopyWithImpl;
@useResult
$Res call({
 String type, String value, Uri url
});




}
/// @nodoc
class _$LinkTargetCopyWithImpl<$Res>
    implements $LinkTargetCopyWith<$Res> {
  _$LinkTargetCopyWithImpl(this._self, this._then);

  final LinkTarget _self;
  final $Res Function(LinkTarget) _then;

/// Create a copy of LinkTarget
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? type = null,Object? value = null,Object? url = null,}) {
  return _then(LinkTarget(
type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,value: null == value ? _self.value : value // ignore: cast_nullable_to_non_nullable
as String,url: null == url ? _self.url : url // ignore: cast_nullable_to_non_nullable
as Uri,
  ));
}

}


/// Adds pattern-matching-related methods to [LinkTarget].
extension LinkTargetPatterns on LinkTarget {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _LinkTarget value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _LinkTarget() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _LinkTarget value)  $default,){
final _that = this;
switch (_that) {
case _LinkTarget():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _LinkTarget value)?  $default,){
final _that = this;
switch (_that) {
case _LinkTarget() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String type,  String value,  Uri url)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _LinkTarget() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String type,  String value,  Uri url)  $default,) {final _that = this;
switch (_that) {
case _LinkTarget():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String type,  String value,  Uri url)?  $default,) {final _that = this;
switch (_that) {
case _LinkTarget() when $default != null:
return $default(_that.type,_that.value,_that.url);case _:
  return null;

}
}

}

/// @nodoc


class _LinkTarget implements LinkTarget {
  const _LinkTarget({required this.type, required this.value, required this.url});
  

@override final  String type;
@override final  String value;
@override final  Uri url;

/// Create a copy of LinkTarget
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$LinkTargetCopyWith<_LinkTarget> get copyWith => __$LinkTargetCopyWithImpl<_LinkTarget>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _LinkTarget&&(identical(other.type, type) || other.type == type)&&(identical(other.value, value) || other.value == value)&&(identical(other.url, url) || other.url == url));
}


@override
int get hashCode {
    return Object.hash(runtimeType,type,value,url);
}

@override
String toString() {
    return 'LinkTarget(type: $type, value: $value, url: $url)';
}


}

/// @nodoc
abstract mixin class _$LinkTargetCopyWith<$Res> implements $LinkTargetCopyWith<$Res> {
  factory _$LinkTargetCopyWith(_LinkTarget value, $Res Function(_LinkTarget) _then) = __$LinkTargetCopyWithImpl;
@override @useResult
$Res call({
 String type, String value, Uri url
});




}
/// @nodoc
class __$LinkTargetCopyWithImpl<$Res>
    implements _$LinkTargetCopyWith<$Res> {
  __$LinkTargetCopyWithImpl(this._self, this._then);

  final _LinkTarget _self;
  final $Res Function(_LinkTarget) _then;

/// Create a copy of LinkTarget
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? type = null,Object? value = null,Object? url = null,}) {
  return _then(_LinkTarget(
type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,value: null == value ? _self.value : value // ignore: cast_nullable_to_non_nullable
as String,url: null == url ? _self.url : url // ignore: cast_nullable_to_non_nullable
as Uri,
  ));
}


}

/// @nodoc
mixin _$NamedSlug {

 String get name; String get slug;
/// Create a copy of NamedSlug
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$NamedSlugCopyWith<NamedSlug> get copyWith => _$NamedSlugCopyWithImpl<NamedSlug>(this as NamedSlug, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as NamedSlug;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is NamedSlug&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug));
}


@override
int get hashCode {
  final _this = this as NamedSlug;
  return Object.hash(runtimeType,_this.name,_this.slug);
}

@override
String toString() {
  final _this = this as NamedSlug;
  return 'NamedSlug(name: ${_this.name}, slug: ${_this.slug})';
}


}

/// @nodoc
abstract mixin class $NamedSlugCopyWith<$Res>  {
  factory $NamedSlugCopyWith(NamedSlug value, $Res Function(NamedSlug) _then) = _$NamedSlugCopyWithImpl;
@useResult
$Res call({
 String name, String slug
});




}
/// @nodoc
class _$NamedSlugCopyWithImpl<$Res>
    implements $NamedSlugCopyWith<$Res> {
  _$NamedSlugCopyWithImpl(this._self, this._then);

  final NamedSlug _self;
  final $Res Function(NamedSlug) _then;

/// Create a copy of NamedSlug
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? name = null,Object? slug = null,}) {
  return _then(NamedSlug(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [NamedSlug].
extension NamedSlugPatterns on NamedSlug {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _NamedSlug value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _NamedSlug() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _NamedSlug value)  $default,){
final _that = this;
switch (_that) {
case _NamedSlug():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _NamedSlug value)?  $default,){
final _that = this;
switch (_that) {
case _NamedSlug() when $default != null:
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
case _NamedSlug() when $default != null:
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
case _NamedSlug():
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
case _NamedSlug() when $default != null:
return $default(_that.name,_that.slug);case _:
  return null;

}
}

}

/// @nodoc


class _NamedSlug implements NamedSlug {
  const _NamedSlug({required this.name, required this.slug});
  

@override final  String name;
@override final  String slug;

/// Create a copy of NamedSlug
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$NamedSlugCopyWith<_NamedSlug> get copyWith => __$NamedSlugCopyWithImpl<_NamedSlug>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _NamedSlug&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug));
}


@override
int get hashCode {
    return Object.hash(runtimeType,name,slug);
}

@override
String toString() {
    return 'NamedSlug(name: $name, slug: $slug)';
}


}

/// @nodoc
abstract mixin class _$NamedSlugCopyWith<$Res> implements $NamedSlugCopyWith<$Res> {
  factory _$NamedSlugCopyWith(_NamedSlug value, $Res Function(_NamedSlug) _then) = __$NamedSlugCopyWithImpl;
@override @useResult
$Res call({
 String name, String slug
});




}
/// @nodoc
class __$NamedSlugCopyWithImpl<$Res>
    implements _$NamedSlugCopyWith<$Res> {
  __$NamedSlugCopyWithImpl(this._self, this._then);

  final _NamedSlug _self;
  final $Res Function(_NamedSlug) _then;

/// Create a copy of NamedSlug
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? name = null,Object? slug = null,}) {
  return _then(_NamedSlug(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc
mixin _$ProductCard {

 String get publicId; String get name; String get slug; String get deliveryType; bool get priceMayVary; bool get isFeatured; String? get shortDescription; NamedSlug? get platform; NamedSlug? get category; MediaImage? get image; Money? get priceFrom;/// Decimal string, for example `4.50`.
 String? get ratingAvg; LinkTarget? get link;
/// Create a copy of ProductCard
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ProductCardCopyWith<ProductCard> get copyWith => _$ProductCardCopyWithImpl<ProductCard>(this as ProductCard, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as ProductCard;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ProductCard&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug)&&(identical(other.deliveryType, _this.deliveryType) || other.deliveryType == _this.deliveryType)&&(identical(other.priceMayVary, _this.priceMayVary) || other.priceMayVary == _this.priceMayVary)&&(identical(other.isFeatured, _this.isFeatured) || other.isFeatured == _this.isFeatured)&&(identical(other.shortDescription, _this.shortDescription) || other.shortDescription == _this.shortDescription)&&(identical(other.platform, _this.platform) || other.platform == _this.platform)&&(identical(other.category, _this.category) || other.category == _this.category)&&(identical(other.image, _this.image) || other.image == _this.image)&&(identical(other.priceFrom, _this.priceFrom) || other.priceFrom == _this.priceFrom)&&(identical(other.ratingAvg, _this.ratingAvg) || other.ratingAvg == _this.ratingAvg)&&(identical(other.link, _this.link) || other.link == _this.link));
}


@override
int get hashCode {
  final _this = this as ProductCard;
  return Object.hash(runtimeType,_this.publicId,_this.name,_this.slug,_this.deliveryType,_this.priceMayVary,_this.isFeatured,_this.shortDescription,_this.platform,_this.category,_this.image,_this.priceFrom,_this.ratingAvg,_this.link);
}

@override
String toString() {
  final _this = this as ProductCard;
  return 'ProductCard(publicId: ${_this.publicId}, name: ${_this.name}, slug: ${_this.slug}, deliveryType: ${_this.deliveryType}, priceMayVary: ${_this.priceMayVary}, isFeatured: ${_this.isFeatured}, shortDescription: ${_this.shortDescription}, platform: ${_this.platform}, category: ${_this.category}, image: ${_this.image}, priceFrom: ${_this.priceFrom}, ratingAvg: ${_this.ratingAvg}, link: ${_this.link})';
}


}

/// @nodoc
abstract mixin class $ProductCardCopyWith<$Res>  {
  factory $ProductCardCopyWith(ProductCard value, $Res Function(ProductCard) _then) = _$ProductCardCopyWithImpl;
@useResult
$Res call({
 String publicId, String name, String slug, String deliveryType, bool priceMayVary, bool isFeatured, String? shortDescription, NamedSlug? platform, NamedSlug? category, MediaImage? image, Money? priceFrom, String? ratingAvg, LinkTarget? link
});


$NamedSlugCopyWith<$Res>? get platform;$NamedSlugCopyWith<$Res>? get category;$MediaImageCopyWith<$Res>? get image;$LinkTargetCopyWith<$Res>? get link;

}
/// @nodoc
class _$ProductCardCopyWithImpl<$Res>
    implements $ProductCardCopyWith<$Res> {
  _$ProductCardCopyWithImpl(this._self, this._then);

  final ProductCard _self;
  final $Res Function(ProductCard) _then;

/// Create a copy of ProductCard
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? name = null,Object? slug = null,Object? deliveryType = null,Object? priceMayVary = null,Object? isFeatured = null,Object? shortDescription = freezed,Object? platform = freezed,Object? category = freezed,Object? image = freezed,Object? priceFrom = freezed,Object? ratingAvg = freezed,Object? link = freezed,}) {
  return _then(ProductCard(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,deliveryType: null == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String,priceMayVary: null == priceMayVary ? _self.priceMayVary : priceMayVary // ignore: cast_nullable_to_non_nullable
as bool,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,shortDescription: freezed == shortDescription ? _self.shortDescription : shortDescription // ignore: cast_nullable_to_non_nullable
as String?,platform: freezed == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as NamedSlug?,category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as NamedSlug?,image: freezed == image ? _self.image : image // ignore: cast_nullable_to_non_nullable
as MediaImage?,priceFrom: freezed == priceFrom ? _self.priceFrom : priceFrom // ignore: cast_nullable_to_non_nullable
as Money?,ratingAvg: freezed == ratingAvg ? _self.ratingAvg : ratingAvg // ignore: cast_nullable_to_non_nullable
as String?,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTarget?,
  ));
}
/// Create a copy of ProductCard
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$NamedSlugCopyWith<$Res>? get platform {
    if (_self.platform == null) {
    return null;
  }

  return $NamedSlugCopyWith<$Res>(_self.platform!, (value) {
    return _then(_self.copyWith(platform: value));
  });
}/// Create a copy of ProductCard
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$NamedSlugCopyWith<$Res>? get category {
    if (_self.category == null) {
    return null;
  }

  return $NamedSlugCopyWith<$Res>(_self.category!, (value) {
    return _then(_self.copyWith(category: value));
  });
}/// Create a copy of ProductCard
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageCopyWith<$Res>? get image {
    if (_self.image == null) {
    return null;
  }

  return $MediaImageCopyWith<$Res>(_self.image!, (value) {
    return _then(_self.copyWith(image: value));
  });
}/// Create a copy of ProductCard
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetCopyWith<$Res>? get link {
    if (_self.link == null) {
    return null;
  }

  return $LinkTargetCopyWith<$Res>(_self.link!, (value) {
    return _then(_self.copyWith(link: value));
  });
}
}


/// Adds pattern-matching-related methods to [ProductCard].
extension ProductCardPatterns on ProductCard {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ProductCard value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ProductCard() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ProductCard value)  $default,){
final _that = this;
switch (_that) {
case _ProductCard():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ProductCard value)?  $default,){
final _that = this;
switch (_that) {
case _ProductCard() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String name,  String slug,  String deliveryType,  bool priceMayVary,  bool isFeatured,  String? shortDescription,  NamedSlug? platform,  NamedSlug? category,  MediaImage? image,  Money? priceFrom,  String? ratingAvg,  LinkTarget? link)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ProductCard() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String name,  String slug,  String deliveryType,  bool priceMayVary,  bool isFeatured,  String? shortDescription,  NamedSlug? platform,  NamedSlug? category,  MediaImage? image,  Money? priceFrom,  String? ratingAvg,  LinkTarget? link)  $default,) {final _that = this;
switch (_that) {
case _ProductCard():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String name,  String slug,  String deliveryType,  bool priceMayVary,  bool isFeatured,  String? shortDescription,  NamedSlug? platform,  NamedSlug? category,  MediaImage? image,  Money? priceFrom,  String? ratingAvg,  LinkTarget? link)?  $default,) {final _that = this;
switch (_that) {
case _ProductCard() when $default != null:
return $default(_that.publicId,_that.name,_that.slug,_that.deliveryType,_that.priceMayVary,_that.isFeatured,_that.shortDescription,_that.platform,_that.category,_that.image,_that.priceFrom,_that.ratingAvg,_that.link);case _:
  return null;

}
}

}

/// @nodoc


class _ProductCard implements ProductCard {
  const _ProductCard({required this.publicId, required this.name, required this.slug, required this.deliveryType, required this.priceMayVary, required this.isFeatured, this.shortDescription, this.platform, this.category, this.image, this.priceFrom, this.ratingAvg, this.link});
  

@override final  String publicId;
@override final  String name;
@override final  String slug;
@override final  String deliveryType;
@override final  bool priceMayVary;
@override final  bool isFeatured;
@override final  String? shortDescription;
@override final  NamedSlug? platform;
@override final  NamedSlug? category;
@override final  MediaImage? image;
@override final  Money? priceFrom;
/// Decimal string, for example `4.50`.
@override final  String? ratingAvg;
@override final  LinkTarget? link;

/// Create a copy of ProductCard
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ProductCardCopyWith<_ProductCard> get copyWith => __$ProductCardCopyWithImpl<_ProductCard>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ProductCard&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug)&&(identical(other.deliveryType, deliveryType) || other.deliveryType == deliveryType)&&(identical(other.priceMayVary, priceMayVary) || other.priceMayVary == priceMayVary)&&(identical(other.isFeatured, isFeatured) || other.isFeatured == isFeatured)&&(identical(other.shortDescription, shortDescription) || other.shortDescription == shortDescription)&&(identical(other.platform, platform) || other.platform == platform)&&(identical(other.category, category) || other.category == category)&&(identical(other.image, image) || other.image == image)&&(identical(other.priceFrom, priceFrom) || other.priceFrom == priceFrom)&&(identical(other.ratingAvg, ratingAvg) || other.ratingAvg == ratingAvg)&&(identical(other.link, link) || other.link == link));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,name,slug,deliveryType,priceMayVary,isFeatured,shortDescription,platform,category,image,priceFrom,ratingAvg,link);
}

@override
String toString() {
    return 'ProductCard(publicId: $publicId, name: $name, slug: $slug, deliveryType: $deliveryType, priceMayVary: $priceMayVary, isFeatured: $isFeatured, shortDescription: $shortDescription, platform: $platform, category: $category, image: $image, priceFrom: $priceFrom, ratingAvg: $ratingAvg, link: $link)';
}


}

/// @nodoc
abstract mixin class _$ProductCardCopyWith<$Res> implements $ProductCardCopyWith<$Res> {
  factory _$ProductCardCopyWith(_ProductCard value, $Res Function(_ProductCard) _then) = __$ProductCardCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String name, String slug, String deliveryType, bool priceMayVary, bool isFeatured, String? shortDescription, NamedSlug? platform, NamedSlug? category, MediaImage? image, Money? priceFrom, String? ratingAvg, LinkTarget? link
});


@override $NamedSlugCopyWith<$Res>? get platform;@override $NamedSlugCopyWith<$Res>? get category;@override $MediaImageCopyWith<$Res>? get image;@override $LinkTargetCopyWith<$Res>? get link;

}
/// @nodoc
class __$ProductCardCopyWithImpl<$Res>
    implements _$ProductCardCopyWith<$Res> {
  __$ProductCardCopyWithImpl(this._self, this._then);

  final _ProductCard _self;
  final $Res Function(_ProductCard) _then;

/// Create a copy of ProductCard
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? name = null,Object? slug = null,Object? deliveryType = null,Object? priceMayVary = null,Object? isFeatured = null,Object? shortDescription = freezed,Object? platform = freezed,Object? category = freezed,Object? image = freezed,Object? priceFrom = freezed,Object? ratingAvg = freezed,Object? link = freezed,}) {
  return _then(_ProductCard(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,deliveryType: null == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String,priceMayVary: null == priceMayVary ? _self.priceMayVary : priceMayVary // ignore: cast_nullable_to_non_nullable
as bool,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,shortDescription: freezed == shortDescription ? _self.shortDescription : shortDescription // ignore: cast_nullable_to_non_nullable
as String?,platform: freezed == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as NamedSlug?,category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as NamedSlug?,image: freezed == image ? _self.image : image // ignore: cast_nullable_to_non_nullable
as MediaImage?,priceFrom: freezed == priceFrom ? _self.priceFrom : priceFrom // ignore: cast_nullable_to_non_nullable
as Money?,ratingAvg: freezed == ratingAvg ? _self.ratingAvg : ratingAvg // ignore: cast_nullable_to_non_nullable
as String?,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTarget?,
  ));
}

/// Create a copy of ProductCard
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$NamedSlugCopyWith<$Res>? get platform {
    if (_self.platform == null) {
    return null;
  }

  return $NamedSlugCopyWith<$Res>(_self.platform!, (value) {
    return _then(_self.copyWith(platform: value));
  });
}/// Create a copy of ProductCard
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$NamedSlugCopyWith<$Res>? get category {
    if (_self.category == null) {
    return null;
  }

  return $NamedSlugCopyWith<$Res>(_self.category!, (value) {
    return _then(_self.copyWith(category: value));
  });
}/// Create a copy of ProductCard
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageCopyWith<$Res>? get image {
    if (_self.image == null) {
    return null;
  }

  return $MediaImageCopyWith<$Res>(_self.image!, (value) {
    return _then(_self.copyWith(image: value));
  });
}/// Create a copy of ProductCard
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetCopyWith<$Res>? get link {
    if (_self.link == null) {
    return null;
  }

  return $LinkTargetCopyWith<$Res>(_self.link!, (value) {
    return _then(_self.copyWith(link: value));
  });
}
}

/// @nodoc
mixin _$PageInfo {

 int get currentPage; int get perPage; int get total; int get lastPage;
/// Create a copy of PageInfo
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PageInfoCopyWith<PageInfo> get copyWith => _$PageInfoCopyWithImpl<PageInfo>(this as PageInfo, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as PageInfo;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PageInfo&&(identical(other.currentPage, _this.currentPage) || other.currentPage == _this.currentPage)&&(identical(other.perPage, _this.perPage) || other.perPage == _this.perPage)&&(identical(other.total, _this.total) || other.total == _this.total)&&(identical(other.lastPage, _this.lastPage) || other.lastPage == _this.lastPage));
}


@override
int get hashCode {
  final _this = this as PageInfo;
  return Object.hash(runtimeType,_this.currentPage,_this.perPage,_this.total,_this.lastPage);
}

@override
String toString() {
  final _this = this as PageInfo;
  return 'PageInfo(currentPage: ${_this.currentPage}, perPage: ${_this.perPage}, total: ${_this.total}, lastPage: ${_this.lastPage})';
}


}

/// @nodoc
abstract mixin class $PageInfoCopyWith<$Res>  {
  factory $PageInfoCopyWith(PageInfo value, $Res Function(PageInfo) _then) = _$PageInfoCopyWithImpl;
@useResult
$Res call({
 int currentPage, int perPage, int total, int lastPage
});




}
/// @nodoc
class _$PageInfoCopyWithImpl<$Res>
    implements $PageInfoCopyWith<$Res> {
  _$PageInfoCopyWithImpl(this._self, this._then);

  final PageInfo _self;
  final $Res Function(PageInfo) _then;

/// Create a copy of PageInfo
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? currentPage = null,Object? perPage = null,Object? total = null,Object? lastPage = null,}) {
  return _then(PageInfo(
currentPage: null == currentPage ? _self.currentPage : currentPage // ignore: cast_nullable_to_non_nullable
as int,perPage: null == perPage ? _self.perPage : perPage // ignore: cast_nullable_to_non_nullable
as int,total: null == total ? _self.total : total // ignore: cast_nullable_to_non_nullable
as int,lastPage: null == lastPage ? _self.lastPage : lastPage // ignore: cast_nullable_to_non_nullable
as int,
  ));
}

}


/// Adds pattern-matching-related methods to [PageInfo].
extension PageInfoPatterns on PageInfo {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PageInfo value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PageInfo() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PageInfo value)  $default,){
final _that = this;
switch (_that) {
case _PageInfo():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PageInfo value)?  $default,){
final _that = this;
switch (_that) {
case _PageInfo() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int currentPage,  int perPage,  int total,  int lastPage)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PageInfo() when $default != null:
return $default(_that.currentPage,_that.perPage,_that.total,_that.lastPage);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int currentPage,  int perPage,  int total,  int lastPage)  $default,) {final _that = this;
switch (_that) {
case _PageInfo():
return $default(_that.currentPage,_that.perPage,_that.total,_that.lastPage);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int currentPage,  int perPage,  int total,  int lastPage)?  $default,) {final _that = this;
switch (_that) {
case _PageInfo() when $default != null:
return $default(_that.currentPage,_that.perPage,_that.total,_that.lastPage);case _:
  return null;

}
}

}

/// @nodoc


class _PageInfo extends PageInfo {
  const _PageInfo({required this.currentPage, required this.perPage, required this.total, required this.lastPage}): super._();
  

@override final  int currentPage;
@override final  int perPage;
@override final  int total;
@override final  int lastPage;

/// Create a copy of PageInfo
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PageInfoCopyWith<_PageInfo> get copyWith => __$PageInfoCopyWithImpl<_PageInfo>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PageInfo&&(identical(other.currentPage, currentPage) || other.currentPage == currentPage)&&(identical(other.perPage, perPage) || other.perPage == perPage)&&(identical(other.total, total) || other.total == total)&&(identical(other.lastPage, lastPage) || other.lastPage == lastPage));
}


@override
int get hashCode {
    return Object.hash(runtimeType,currentPage,perPage,total,lastPage);
}

@override
String toString() {
    return 'PageInfo(currentPage: $currentPage, perPage: $perPage, total: $total, lastPage: $lastPage)';
}


}

/// @nodoc
abstract mixin class _$PageInfoCopyWith<$Res> implements $PageInfoCopyWith<$Res> {
  factory _$PageInfoCopyWith(_PageInfo value, $Res Function(_PageInfo) _then) = __$PageInfoCopyWithImpl;
@override @useResult
$Res call({
 int currentPage, int perPage, int total, int lastPage
});




}
/// @nodoc
class __$PageInfoCopyWithImpl<$Res>
    implements _$PageInfoCopyWith<$Res> {
  __$PageInfoCopyWithImpl(this._self, this._then);

  final _PageInfo _self;
  final $Res Function(_PageInfo) _then;

/// Create a copy of PageInfo
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? currentPage = null,Object? perPage = null,Object? total = null,Object? lastPage = null,}) {
  return _then(_PageInfo(
currentPage: null == currentPage ? _self.currentPage : currentPage // ignore: cast_nullable_to_non_nullable
as int,perPage: null == perPage ? _self.perPage : perPage // ignore: cast_nullable_to_non_nullable
as int,total: null == total ? _self.total : total // ignore: cast_nullable_to_non_nullable
as int,lastPage: null == lastPage ? _self.lastPage : lastPage // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

// dart format on
