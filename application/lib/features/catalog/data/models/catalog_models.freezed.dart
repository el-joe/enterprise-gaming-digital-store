// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'catalog_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$PlatformDto {

 String get name; String get slug; int get productCount; bool get isFeatured; String? get description; MediaImageDto? get logo; MediaImageDto? get banner;
/// Create a copy of PlatformDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PlatformDtoCopyWith<PlatformDto> get copyWith => _$PlatformDtoCopyWithImpl<PlatformDto>(this as PlatformDto, _$identity);

  /// Serializes this PlatformDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as PlatformDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PlatformDto&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug)&&(identical(other.productCount, _this.productCount) || other.productCount == _this.productCount)&&(identical(other.isFeatured, _this.isFeatured) || other.isFeatured == _this.isFeatured)&&(identical(other.description, _this.description) || other.description == _this.description)&&(identical(other.logo, _this.logo) || other.logo == _this.logo)&&(identical(other.banner, _this.banner) || other.banner == _this.banner));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as PlatformDto;
  return Object.hash(runtimeType,_this.name,_this.slug,_this.productCount,_this.isFeatured,_this.description,_this.logo,_this.banner);
}

@override
String toString() {
  final _this = this as PlatformDto;
  return 'PlatformDto(name: ${_this.name}, slug: ${_this.slug}, productCount: ${_this.productCount}, isFeatured: ${_this.isFeatured}, description: ${_this.description}, logo: ${_this.logo}, banner: ${_this.banner})';
}


}

/// @nodoc
abstract mixin class $PlatformDtoCopyWith<$Res>  {
  factory $PlatformDtoCopyWith(PlatformDto value, $Res Function(PlatformDto) _then) = _$PlatformDtoCopyWithImpl;
@useResult
$Res call({
 String name, String slug, int productCount, bool isFeatured, String? description, MediaImageDto? logo, MediaImageDto? banner
});


$MediaImageDtoCopyWith<$Res>? get logo;$MediaImageDtoCopyWith<$Res>? get banner;

}
/// @nodoc
class _$PlatformDtoCopyWithImpl<$Res>
    implements $PlatformDtoCopyWith<$Res> {
  _$PlatformDtoCopyWithImpl(this._self, this._then);

  final PlatformDto _self;
  final $Res Function(PlatformDto) _then;

/// Create a copy of PlatformDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? name = null,Object? slug = null,Object? productCount = null,Object? isFeatured = null,Object? description = freezed,Object? logo = freezed,Object? banner = freezed,}) {
  return _then(PlatformDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,productCount: null == productCount ? _self.productCount : productCount // ignore: cast_nullable_to_non_nullable
as int,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,logo: freezed == logo ? _self.logo : logo // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,banner: freezed == banner ? _self.banner : banner // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,
  ));
}
/// Create a copy of PlatformDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get logo {
    if (_self.logo == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.logo!, (value) {
    return _then(_self.copyWith(logo: value));
  });
}/// Create a copy of PlatformDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get banner {
    if (_self.banner == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.banner!, (value) {
    return _then(_self.copyWith(banner: value));
  });
}
}


/// Adds pattern-matching-related methods to [PlatformDto].
extension PlatformDtoPatterns on PlatformDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PlatformDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PlatformDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PlatformDto value)  $default,){
final _that = this;
switch (_that) {
case _PlatformDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PlatformDto value)?  $default,){
final _that = this;
switch (_that) {
case _PlatformDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String name,  String slug,  int productCount,  bool isFeatured,  String? description,  MediaImageDto? logo,  MediaImageDto? banner)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PlatformDto() when $default != null:
return $default(_that.name,_that.slug,_that.productCount,_that.isFeatured,_that.description,_that.logo,_that.banner);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String name,  String slug,  int productCount,  bool isFeatured,  String? description,  MediaImageDto? logo,  MediaImageDto? banner)  $default,) {final _that = this;
switch (_that) {
case _PlatformDto():
return $default(_that.name,_that.slug,_that.productCount,_that.isFeatured,_that.description,_that.logo,_that.banner);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String name,  String slug,  int productCount,  bool isFeatured,  String? description,  MediaImageDto? logo,  MediaImageDto? banner)?  $default,) {final _that = this;
switch (_that) {
case _PlatformDto() when $default != null:
return $default(_that.name,_that.slug,_that.productCount,_that.isFeatured,_that.description,_that.logo,_that.banner);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _PlatformDto extends PlatformDto {
  const _PlatformDto({required this.name, required this.slug, required this.productCount, required this.isFeatured, this.description, this.logo, this.banner}): super._();
  factory _PlatformDto.fromJson(Map<String, dynamic> json) => _$PlatformDtoFromJson(json);

@override final  String name;
@override final  String slug;
@override final  int productCount;
@override final  bool isFeatured;
@override final  String? description;
@override final  MediaImageDto? logo;
@override final  MediaImageDto? banner;

/// Create a copy of PlatformDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PlatformDtoCopyWith<_PlatformDto> get copyWith => __$PlatformDtoCopyWithImpl<_PlatformDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$PlatformDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PlatformDto&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug)&&(identical(other.productCount, productCount) || other.productCount == productCount)&&(identical(other.isFeatured, isFeatured) || other.isFeatured == isFeatured)&&(identical(other.description, description) || other.description == description)&&(identical(other.logo, logo) || other.logo == logo)&&(identical(other.banner, banner) || other.banner == banner));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,name,slug,productCount,isFeatured,description,logo,banner);
}

@override
String toString() {
    return 'PlatformDto(name: $name, slug: $slug, productCount: $productCount, isFeatured: $isFeatured, description: $description, logo: $logo, banner: $banner)';
}


}

/// @nodoc
abstract mixin class _$PlatformDtoCopyWith<$Res> implements $PlatformDtoCopyWith<$Res> {
  factory _$PlatformDtoCopyWith(_PlatformDto value, $Res Function(_PlatformDto) _then) = __$PlatformDtoCopyWithImpl;
@override @useResult
$Res call({
 String name, String slug, int productCount, bool isFeatured, String? description, MediaImageDto? logo, MediaImageDto? banner
});


@override $MediaImageDtoCopyWith<$Res>? get logo;@override $MediaImageDtoCopyWith<$Res>? get banner;

}
/// @nodoc
class __$PlatformDtoCopyWithImpl<$Res>
    implements _$PlatformDtoCopyWith<$Res> {
  __$PlatformDtoCopyWithImpl(this._self, this._then);

  final _PlatformDto _self;
  final $Res Function(_PlatformDto) _then;

/// Create a copy of PlatformDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? name = null,Object? slug = null,Object? productCount = null,Object? isFeatured = null,Object? description = freezed,Object? logo = freezed,Object? banner = freezed,}) {
  return _then(_PlatformDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,productCount: null == productCount ? _self.productCount : productCount // ignore: cast_nullable_to_non_nullable
as int,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,logo: freezed == logo ? _self.logo : logo // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,banner: freezed == banner ? _self.banner : banner // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,
  ));
}

/// Create a copy of PlatformDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get logo {
    if (_self.logo == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.logo!, (value) {
    return _then(_self.copyWith(logo: value));
  });
}/// Create a copy of PlatformDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get banner {
    if (_self.banner == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.banner!, (value) {
    return _then(_self.copyWith(banner: value));
  });
}
}


/// @nodoc
mixin _$CategoryDto {

 String get name; String get slug; int get productCount; String? get description; String? get parent; MediaImageDto? get banner;
/// Create a copy of CategoryDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CategoryDtoCopyWith<CategoryDto> get copyWith => _$CategoryDtoCopyWithImpl<CategoryDto>(this as CategoryDto, _$identity);

  /// Serializes this CategoryDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as CategoryDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CategoryDto&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug)&&(identical(other.productCount, _this.productCount) || other.productCount == _this.productCount)&&(identical(other.description, _this.description) || other.description == _this.description)&&(identical(other.parent, _this.parent) || other.parent == _this.parent)&&(identical(other.banner, _this.banner) || other.banner == _this.banner));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as CategoryDto;
  return Object.hash(runtimeType,_this.name,_this.slug,_this.productCount,_this.description,_this.parent,_this.banner);
}

@override
String toString() {
  final _this = this as CategoryDto;
  return 'CategoryDto(name: ${_this.name}, slug: ${_this.slug}, productCount: ${_this.productCount}, description: ${_this.description}, parent: ${_this.parent}, banner: ${_this.banner})';
}


}

/// @nodoc
abstract mixin class $CategoryDtoCopyWith<$Res>  {
  factory $CategoryDtoCopyWith(CategoryDto value, $Res Function(CategoryDto) _then) = _$CategoryDtoCopyWithImpl;
@useResult
$Res call({
 String name, String slug, int productCount, String? description, String? parent, MediaImageDto? banner
});


$MediaImageDtoCopyWith<$Res>? get banner;

}
/// @nodoc
class _$CategoryDtoCopyWithImpl<$Res>
    implements $CategoryDtoCopyWith<$Res> {
  _$CategoryDtoCopyWithImpl(this._self, this._then);

  final CategoryDto _self;
  final $Res Function(CategoryDto) _then;

/// Create a copy of CategoryDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? name = null,Object? slug = null,Object? productCount = null,Object? description = freezed,Object? parent = freezed,Object? banner = freezed,}) {
  return _then(CategoryDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,productCount: null == productCount ? _self.productCount : productCount // ignore: cast_nullable_to_non_nullable
as int,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,parent: freezed == parent ? _self.parent : parent // ignore: cast_nullable_to_non_nullable
as String?,banner: freezed == banner ? _self.banner : banner // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,
  ));
}
/// Create a copy of CategoryDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get banner {
    if (_self.banner == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.banner!, (value) {
    return _then(_self.copyWith(banner: value));
  });
}
}


/// Adds pattern-matching-related methods to [CategoryDto].
extension CategoryDtoPatterns on CategoryDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CategoryDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CategoryDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CategoryDto value)  $default,){
final _that = this;
switch (_that) {
case _CategoryDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CategoryDto value)?  $default,){
final _that = this;
switch (_that) {
case _CategoryDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String name,  String slug,  int productCount,  String? description,  String? parent,  MediaImageDto? banner)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CategoryDto() when $default != null:
return $default(_that.name,_that.slug,_that.productCount,_that.description,_that.parent,_that.banner);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String name,  String slug,  int productCount,  String? description,  String? parent,  MediaImageDto? banner)  $default,) {final _that = this;
switch (_that) {
case _CategoryDto():
return $default(_that.name,_that.slug,_that.productCount,_that.description,_that.parent,_that.banner);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String name,  String slug,  int productCount,  String? description,  String? parent,  MediaImageDto? banner)?  $default,) {final _that = this;
switch (_that) {
case _CategoryDto() when $default != null:
return $default(_that.name,_that.slug,_that.productCount,_that.description,_that.parent,_that.banner);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CategoryDto extends CategoryDto {
  const _CategoryDto({required this.name, required this.slug, required this.productCount, this.description, this.parent, this.banner}): super._();
  factory _CategoryDto.fromJson(Map<String, dynamic> json) => _$CategoryDtoFromJson(json);

@override final  String name;
@override final  String slug;
@override final  int productCount;
@override final  String? description;
@override final  String? parent;
@override final  MediaImageDto? banner;

/// Create a copy of CategoryDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CategoryDtoCopyWith<_CategoryDto> get copyWith => __$CategoryDtoCopyWithImpl<_CategoryDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CategoryDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CategoryDto&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug)&&(identical(other.productCount, productCount) || other.productCount == productCount)&&(identical(other.description, description) || other.description == description)&&(identical(other.parent, parent) || other.parent == parent)&&(identical(other.banner, banner) || other.banner == banner));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,name,slug,productCount,description,parent,banner);
}

@override
String toString() {
    return 'CategoryDto(name: $name, slug: $slug, productCount: $productCount, description: $description, parent: $parent, banner: $banner)';
}


}

/// @nodoc
abstract mixin class _$CategoryDtoCopyWith<$Res> implements $CategoryDtoCopyWith<$Res> {
  factory _$CategoryDtoCopyWith(_CategoryDto value, $Res Function(_CategoryDto) _then) = __$CategoryDtoCopyWithImpl;
@override @useResult
$Res call({
 String name, String slug, int productCount, String? description, String? parent, MediaImageDto? banner
});


@override $MediaImageDtoCopyWith<$Res>? get banner;

}
/// @nodoc
class __$CategoryDtoCopyWithImpl<$Res>
    implements _$CategoryDtoCopyWith<$Res> {
  __$CategoryDtoCopyWithImpl(this._self, this._then);

  final _CategoryDto _self;
  final $Res Function(_CategoryDto) _then;

/// Create a copy of CategoryDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? name = null,Object? slug = null,Object? productCount = null,Object? description = freezed,Object? parent = freezed,Object? banner = freezed,}) {
  return _then(_CategoryDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,productCount: null == productCount ? _self.productCount : productCount // ignore: cast_nullable_to_non_nullable
as int,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,parent: freezed == parent ? _self.parent : parent // ignore: cast_nullable_to_non_nullable
as String?,banner: freezed == banner ? _self.banner : banner // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,
  ));
}

/// Create a copy of CategoryDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get banner {
    if (_self.banner == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.banner!, (value) {
    return _then(_self.copyWith(banner: value));
  });
}
}


/// @nodoc
mixin _$CountryDto {

 String get code; String get name; String? get dialCode; String? get defaultCurrency;
/// Create a copy of CountryDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CountryDtoCopyWith<CountryDto> get copyWith => _$CountryDtoCopyWithImpl<CountryDto>(this as CountryDto, _$identity);

  /// Serializes this CountryDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as CountryDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CountryDto&&(identical(other.code, _this.code) || other.code == _this.code)&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.dialCode, _this.dialCode) || other.dialCode == _this.dialCode)&&(identical(other.defaultCurrency, _this.defaultCurrency) || other.defaultCurrency == _this.defaultCurrency));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as CountryDto;
  return Object.hash(runtimeType,_this.code,_this.name,_this.dialCode,_this.defaultCurrency);
}

@override
String toString() {
  final _this = this as CountryDto;
  return 'CountryDto(code: ${_this.code}, name: ${_this.name}, dialCode: ${_this.dialCode}, defaultCurrency: ${_this.defaultCurrency})';
}


}

/// @nodoc
abstract mixin class $CountryDtoCopyWith<$Res>  {
  factory $CountryDtoCopyWith(CountryDto value, $Res Function(CountryDto) _then) = _$CountryDtoCopyWithImpl;
@useResult
$Res call({
 String code, String name, String? dialCode, String? defaultCurrency
});




}
/// @nodoc
class _$CountryDtoCopyWithImpl<$Res>
    implements $CountryDtoCopyWith<$Res> {
  _$CountryDtoCopyWithImpl(this._self, this._then);

  final CountryDto _self;
  final $Res Function(CountryDto) _then;

/// Create a copy of CountryDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? code = null,Object? name = null,Object? dialCode = freezed,Object? defaultCurrency = freezed,}) {
  return _then(CountryDto(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,dialCode: freezed == dialCode ? _self.dialCode : dialCode // ignore: cast_nullable_to_non_nullable
as String?,defaultCurrency: freezed == defaultCurrency ? _self.defaultCurrency : defaultCurrency // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [CountryDto].
extension CountryDtoPatterns on CountryDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CountryDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CountryDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CountryDto value)  $default,){
final _that = this;
switch (_that) {
case _CountryDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CountryDto value)?  $default,){
final _that = this;
switch (_that) {
case _CountryDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String code,  String name,  String? dialCode,  String? defaultCurrency)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CountryDto() when $default != null:
return $default(_that.code,_that.name,_that.dialCode,_that.defaultCurrency);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String code,  String name,  String? dialCode,  String? defaultCurrency)  $default,) {final _that = this;
switch (_that) {
case _CountryDto():
return $default(_that.code,_that.name,_that.dialCode,_that.defaultCurrency);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String code,  String name,  String? dialCode,  String? defaultCurrency)?  $default,) {final _that = this;
switch (_that) {
case _CountryDto() when $default != null:
return $default(_that.code,_that.name,_that.dialCode,_that.defaultCurrency);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CountryDto extends CountryDto {
  const _CountryDto({required this.code, required this.name, this.dialCode, this.defaultCurrency}): super._();
  factory _CountryDto.fromJson(Map<String, dynamic> json) => _$CountryDtoFromJson(json);

@override final  String code;
@override final  String name;
@override final  String? dialCode;
@override final  String? defaultCurrency;

/// Create a copy of CountryDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CountryDtoCopyWith<_CountryDto> get copyWith => __$CountryDtoCopyWithImpl<_CountryDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CountryDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CountryDto&&(identical(other.code, code) || other.code == code)&&(identical(other.name, name) || other.name == name)&&(identical(other.dialCode, dialCode) || other.dialCode == dialCode)&&(identical(other.defaultCurrency, defaultCurrency) || other.defaultCurrency == defaultCurrency));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,code,name,dialCode,defaultCurrency);
}

@override
String toString() {
    return 'CountryDto(code: $code, name: $name, dialCode: $dialCode, defaultCurrency: $defaultCurrency)';
}


}

/// @nodoc
abstract mixin class _$CountryDtoCopyWith<$Res> implements $CountryDtoCopyWith<$Res> {
  factory _$CountryDtoCopyWith(_CountryDto value, $Res Function(_CountryDto) _then) = __$CountryDtoCopyWithImpl;
@override @useResult
$Res call({
 String code, String name, String? dialCode, String? defaultCurrency
});




}
/// @nodoc
class __$CountryDtoCopyWithImpl<$Res>
    implements _$CountryDtoCopyWith<$Res> {
  __$CountryDtoCopyWithImpl(this._self, this._then);

  final _CountryDto _self;
  final $Res Function(_CountryDto) _then;

/// Create a copy of CountryDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? code = null,Object? name = null,Object? dialCode = freezed,Object? defaultCurrency = freezed,}) {
  return _then(_CountryDto(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,dialCode: freezed == dialCode ? _self.dialCode : dialCode // ignore: cast_nullable_to_non_nullable
as String?,defaultCurrency: freezed == defaultCurrency ? _self.defaultCurrency : defaultCurrency // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$DisplayCurrencyDto {

 String get code; String get name; String get symbol; int get exponent;
/// Create a copy of DisplayCurrencyDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DisplayCurrencyDtoCopyWith<DisplayCurrencyDto> get copyWith => _$DisplayCurrencyDtoCopyWithImpl<DisplayCurrencyDto>(this as DisplayCurrencyDto, _$identity);

  /// Serializes this DisplayCurrencyDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as DisplayCurrencyDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DisplayCurrencyDto&&(identical(other.code, _this.code) || other.code == _this.code)&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.symbol, _this.symbol) || other.symbol == _this.symbol)&&(identical(other.exponent, _this.exponent) || other.exponent == _this.exponent));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as DisplayCurrencyDto;
  return Object.hash(runtimeType,_this.code,_this.name,_this.symbol,_this.exponent);
}

@override
String toString() {
  final _this = this as DisplayCurrencyDto;
  return 'DisplayCurrencyDto(code: ${_this.code}, name: ${_this.name}, symbol: ${_this.symbol}, exponent: ${_this.exponent})';
}


}

/// @nodoc
abstract mixin class $DisplayCurrencyDtoCopyWith<$Res>  {
  factory $DisplayCurrencyDtoCopyWith(DisplayCurrencyDto value, $Res Function(DisplayCurrencyDto) _then) = _$DisplayCurrencyDtoCopyWithImpl;
@useResult
$Res call({
 String code, String name, String symbol, int exponent
});




}
/// @nodoc
class _$DisplayCurrencyDtoCopyWithImpl<$Res>
    implements $DisplayCurrencyDtoCopyWith<$Res> {
  _$DisplayCurrencyDtoCopyWithImpl(this._self, this._then);

  final DisplayCurrencyDto _self;
  final $Res Function(DisplayCurrencyDto) _then;

/// Create a copy of DisplayCurrencyDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? code = null,Object? name = null,Object? symbol = null,Object? exponent = null,}) {
  return _then(DisplayCurrencyDto(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,symbol: null == symbol ? _self.symbol : symbol // ignore: cast_nullable_to_non_nullable
as String,exponent: null == exponent ? _self.exponent : exponent // ignore: cast_nullable_to_non_nullable
as int,
  ));
}

}


/// Adds pattern-matching-related methods to [DisplayCurrencyDto].
extension DisplayCurrencyDtoPatterns on DisplayCurrencyDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _DisplayCurrencyDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _DisplayCurrencyDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _DisplayCurrencyDto value)  $default,){
final _that = this;
switch (_that) {
case _DisplayCurrencyDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _DisplayCurrencyDto value)?  $default,){
final _that = this;
switch (_that) {
case _DisplayCurrencyDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String code,  String name,  String symbol,  int exponent)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _DisplayCurrencyDto() when $default != null:
return $default(_that.code,_that.name,_that.symbol,_that.exponent);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String code,  String name,  String symbol,  int exponent)  $default,) {final _that = this;
switch (_that) {
case _DisplayCurrencyDto():
return $default(_that.code,_that.name,_that.symbol,_that.exponent);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String code,  String name,  String symbol,  int exponent)?  $default,) {final _that = this;
switch (_that) {
case _DisplayCurrencyDto() when $default != null:
return $default(_that.code,_that.name,_that.symbol,_that.exponent);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _DisplayCurrencyDto extends DisplayCurrencyDto {
  const _DisplayCurrencyDto({required this.code, required this.name, required this.symbol, required this.exponent}): super._();
  factory _DisplayCurrencyDto.fromJson(Map<String, dynamic> json) => _$DisplayCurrencyDtoFromJson(json);

@override final  String code;
@override final  String name;
@override final  String symbol;
@override final  int exponent;

/// Create a copy of DisplayCurrencyDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DisplayCurrencyDtoCopyWith<_DisplayCurrencyDto> get copyWith => __$DisplayCurrencyDtoCopyWithImpl<_DisplayCurrencyDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$DisplayCurrencyDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _DisplayCurrencyDto&&(identical(other.code, code) || other.code == code)&&(identical(other.name, name) || other.name == name)&&(identical(other.symbol, symbol) || other.symbol == symbol)&&(identical(other.exponent, exponent) || other.exponent == exponent));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,code,name,symbol,exponent);
}

@override
String toString() {
    return 'DisplayCurrencyDto(code: $code, name: $name, symbol: $symbol, exponent: $exponent)';
}


}

/// @nodoc
abstract mixin class _$DisplayCurrencyDtoCopyWith<$Res> implements $DisplayCurrencyDtoCopyWith<$Res> {
  factory _$DisplayCurrencyDtoCopyWith(_DisplayCurrencyDto value, $Res Function(_DisplayCurrencyDto) _then) = __$DisplayCurrencyDtoCopyWithImpl;
@override @useResult
$Res call({
 String code, String name, String symbol, int exponent
});




}
/// @nodoc
class __$DisplayCurrencyDtoCopyWithImpl<$Res>
    implements _$DisplayCurrencyDtoCopyWith<$Res> {
  __$DisplayCurrencyDtoCopyWithImpl(this._self, this._then);

  final _DisplayCurrencyDto _self;
  final $Res Function(_DisplayCurrencyDto) _then;

/// Create a copy of DisplayCurrencyDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? code = null,Object? name = null,Object? symbol = null,Object? exponent = null,}) {
  return _then(_DisplayCurrencyDto(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,symbol: null == symbol ? _self.symbol : symbol // ignore: cast_nullable_to_non_nullable
as String,exponent: null == exponent ? _self.exponent : exponent // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

// dart format on
