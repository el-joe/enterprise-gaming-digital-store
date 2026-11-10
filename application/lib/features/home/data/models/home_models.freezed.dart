// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'home_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$HomeDto {

 List<HomeSectionDto> get sections; HomeBannersDto get banners;
/// Create a copy of HomeDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeDtoCopyWith<HomeDto> get copyWith => _$HomeDtoCopyWithImpl<HomeDto>(this as HomeDto, _$identity);

  /// Serializes this HomeDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as HomeDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeDto&&const DeepCollectionEquality().equals(other.sections, _this.sections)&&(identical(other.banners, _this.banners) || other.banners == _this.banners));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as HomeDto;
  return Object.hash(runtimeType,const DeepCollectionEquality().hash(_this.sections),_this.banners);
}

@override
String toString() {
  final _this = this as HomeDto;
  return 'HomeDto(sections: ${_this.sections}, banners: ${_this.banners})';
}


}

/// @nodoc
abstract mixin class $HomeDtoCopyWith<$Res>  {
  factory $HomeDtoCopyWith(HomeDto value, $Res Function(HomeDto) _then) = _$HomeDtoCopyWithImpl;
@useResult
$Res call({
 List<HomeSectionDto> sections, HomeBannersDto banners
});


$HomeBannersDtoCopyWith<$Res> get banners;

}
/// @nodoc
class _$HomeDtoCopyWithImpl<$Res>
    implements $HomeDtoCopyWith<$Res> {
  _$HomeDtoCopyWithImpl(this._self, this._then);

  final HomeDto _self;
  final $Res Function(HomeDto) _then;

/// Create a copy of HomeDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? sections = null,Object? banners = null,}) {
  return _then(HomeDto(
sections: null == sections ? _self.sections : sections // ignore: cast_nullable_to_non_nullable
as List<HomeSectionDto>,banners: null == banners ? _self.banners : banners // ignore: cast_nullable_to_non_nullable
as HomeBannersDto,
  ));
}
/// Create a copy of HomeDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$HomeBannersDtoCopyWith<$Res> get banners {
  
  return $HomeBannersDtoCopyWith<$Res>(_self.banners, (value) {
    return _then(_self.copyWith(banners: value));
  });
}
}


/// Adds pattern-matching-related methods to [HomeDto].
extension HomeDtoPatterns on HomeDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomeDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomeDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomeDto value)  $default,){
final _that = this;
switch (_that) {
case _HomeDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomeDto value)?  $default,){
final _that = this;
switch (_that) {
case _HomeDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<HomeSectionDto> sections,  HomeBannersDto banners)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomeDto() when $default != null:
return $default(_that.sections,_that.banners);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<HomeSectionDto> sections,  HomeBannersDto banners)  $default,) {final _that = this;
switch (_that) {
case _HomeDto():
return $default(_that.sections,_that.banners);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<HomeSectionDto> sections,  HomeBannersDto banners)?  $default,) {final _that = this;
switch (_that) {
case _HomeDto() when $default != null:
return $default(_that.sections,_that.banners);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _HomeDto extends HomeDto {
  const _HomeDto({required  List<HomeSectionDto> sections, required this.banners}): _sections = sections,super._();
  factory _HomeDto.fromJson(Map<String, dynamic> json) => _$HomeDtoFromJson(json);

 final  List<HomeSectionDto> _sections;
@override List<HomeSectionDto> get sections {
  if (_sections is EqualUnmodifiableListView) return _sections;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_sections);
}

@override final  HomeBannersDto banners;

/// Create a copy of HomeDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomeDtoCopyWith<_HomeDto> get copyWith => __$HomeDtoCopyWithImpl<_HomeDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$HomeDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomeDto&&const DeepCollectionEquality().equals(other.sections, _sections)&&(identical(other.banners, banners) || other.banners == banners));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,const DeepCollectionEquality().hash(_sections),banners);
}

@override
String toString() {
    return 'HomeDto(sections: $sections, banners: $banners)';
}


}

/// @nodoc
abstract mixin class _$HomeDtoCopyWith<$Res> implements $HomeDtoCopyWith<$Res> {
  factory _$HomeDtoCopyWith(_HomeDto value, $Res Function(_HomeDto) _then) = __$HomeDtoCopyWithImpl;
@override @useResult
$Res call({
 List<HomeSectionDto> sections, HomeBannersDto banners
});


@override $HomeBannersDtoCopyWith<$Res> get banners;

}
/// @nodoc
class __$HomeDtoCopyWithImpl<$Res>
    implements _$HomeDtoCopyWith<$Res> {
  __$HomeDtoCopyWithImpl(this._self, this._then);

  final _HomeDto _self;
  final $Res Function(_HomeDto) _then;

/// Create a copy of HomeDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? sections = null,Object? banners = null,}) {
  return _then(_HomeDto(
sections: null == sections ? _self._sections : sections // ignore: cast_nullable_to_non_nullable
as List<HomeSectionDto>,banners: null == banners ? _self.banners : banners // ignore: cast_nullable_to_non_nullable
as HomeBannersDto,
  ));
}

/// Create a copy of HomeDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$HomeBannersDtoCopyWith<$Res> get banners {
  
  return $HomeBannersDtoCopyWith<$Res>(_self.banners, (value) {
    return _then(_self.copyWith(banners: value));
  });
}
}


/// @nodoc
mixin _$HomeBannersDto {

 List<BannerDto> get hero; List<BannerDto> get homePromo;
/// Create a copy of HomeBannersDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeBannersDtoCopyWith<HomeBannersDto> get copyWith => _$HomeBannersDtoCopyWithImpl<HomeBannersDto>(this as HomeBannersDto, _$identity);

  /// Serializes this HomeBannersDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as HomeBannersDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeBannersDto&&const DeepCollectionEquality().equals(other.hero, _this.hero)&&const DeepCollectionEquality().equals(other.homePromo, _this.homePromo));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as HomeBannersDto;
  return Object.hash(runtimeType,const DeepCollectionEquality().hash(_this.hero),const DeepCollectionEquality().hash(_this.homePromo));
}

@override
String toString() {
  final _this = this as HomeBannersDto;
  return 'HomeBannersDto(hero: ${_this.hero}, homePromo: ${_this.homePromo})';
}


}

/// @nodoc
abstract mixin class $HomeBannersDtoCopyWith<$Res>  {
  factory $HomeBannersDtoCopyWith(HomeBannersDto value, $Res Function(HomeBannersDto) _then) = _$HomeBannersDtoCopyWithImpl;
@useResult
$Res call({
 List<BannerDto> hero, List<BannerDto> homePromo
});




}
/// @nodoc
class _$HomeBannersDtoCopyWithImpl<$Res>
    implements $HomeBannersDtoCopyWith<$Res> {
  _$HomeBannersDtoCopyWithImpl(this._self, this._then);

  final HomeBannersDto _self;
  final $Res Function(HomeBannersDto) _then;

/// Create a copy of HomeBannersDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? hero = null,Object? homePromo = null,}) {
  return _then(HomeBannersDto(
hero: null == hero ? _self.hero : hero // ignore: cast_nullable_to_non_nullable
as List<BannerDto>,homePromo: null == homePromo ? _self.homePromo : homePromo // ignore: cast_nullable_to_non_nullable
as List<BannerDto>,
  ));
}

}


/// Adds pattern-matching-related methods to [HomeBannersDto].
extension HomeBannersDtoPatterns on HomeBannersDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomeBannersDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomeBannersDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomeBannersDto value)  $default,){
final _that = this;
switch (_that) {
case _HomeBannersDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomeBannersDto value)?  $default,){
final _that = this;
switch (_that) {
case _HomeBannersDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<BannerDto> hero,  List<BannerDto> homePromo)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomeBannersDto() when $default != null:
return $default(_that.hero,_that.homePromo);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<BannerDto> hero,  List<BannerDto> homePromo)  $default,) {final _that = this;
switch (_that) {
case _HomeBannersDto():
return $default(_that.hero,_that.homePromo);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<BannerDto> hero,  List<BannerDto> homePromo)?  $default,) {final _that = this;
switch (_that) {
case _HomeBannersDto() when $default != null:
return $default(_that.hero,_that.homePromo);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _HomeBannersDto implements HomeBannersDto {
  const _HomeBannersDto({ List<BannerDto> hero = const <BannerDto>[],  List<BannerDto> homePromo = const <BannerDto>[]}): _hero = hero,_homePromo = homePromo;
  factory _HomeBannersDto.fromJson(Map<String, dynamic> json) => _$HomeBannersDtoFromJson(json);

 final  List<BannerDto> _hero;
@override@JsonKey() List<BannerDto> get hero {
  if (_hero is EqualUnmodifiableListView) return _hero;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_hero);
}

 final  List<BannerDto> _homePromo;
@override@JsonKey() List<BannerDto> get homePromo {
  if (_homePromo is EqualUnmodifiableListView) return _homePromo;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_homePromo);
}


/// Create a copy of HomeBannersDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomeBannersDtoCopyWith<_HomeBannersDto> get copyWith => __$HomeBannersDtoCopyWithImpl<_HomeBannersDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$HomeBannersDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomeBannersDto&&const DeepCollectionEquality().equals(other.hero, _hero)&&const DeepCollectionEquality().equals(other.homePromo, _homePromo));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,const DeepCollectionEquality().hash(_hero),const DeepCollectionEquality().hash(_homePromo));
}

@override
String toString() {
    return 'HomeBannersDto(hero: $hero, homePromo: $homePromo)';
}


}

/// @nodoc
abstract mixin class _$HomeBannersDtoCopyWith<$Res> implements $HomeBannersDtoCopyWith<$Res> {
  factory _$HomeBannersDtoCopyWith(_HomeBannersDto value, $Res Function(_HomeBannersDto) _then) = __$HomeBannersDtoCopyWithImpl;
@override @useResult
$Res call({
 List<BannerDto> hero, List<BannerDto> homePromo
});




}
/// @nodoc
class __$HomeBannersDtoCopyWithImpl<$Res>
    implements _$HomeBannersDtoCopyWith<$Res> {
  __$HomeBannersDtoCopyWithImpl(this._self, this._then);

  final _HomeBannersDto _self;
  final $Res Function(_HomeBannersDto) _then;

/// Create a copy of HomeBannersDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? hero = null,Object? homePromo = null,}) {
  return _then(_HomeBannersDto(
hero: null == hero ? _self._hero : hero // ignore: cast_nullable_to_non_nullable
as List<BannerDto>,homePromo: null == homePromo ? _self._homePromo : homePromo // ignore: cast_nullable_to_non_nullable
as List<BannerDto>,
  ));
}


}


/// @nodoc
mixin _$HomeSectionDto {

 String get key; String get source; List<HomeItemDto> get items; String? get title; String? get subtitle; String? get rule;
/// Create a copy of HomeSectionDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeSectionDtoCopyWith<HomeSectionDto> get copyWith => _$HomeSectionDtoCopyWithImpl<HomeSectionDto>(this as HomeSectionDto, _$identity);

  /// Serializes this HomeSectionDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as HomeSectionDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeSectionDto&&(identical(other.key, _this.key) || other.key == _this.key)&&(identical(other.source, _this.source) || other.source == _this.source)&&const DeepCollectionEquality().equals(other.items, _this.items)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.subtitle, _this.subtitle) || other.subtitle == _this.subtitle)&&(identical(other.rule, _this.rule) || other.rule == _this.rule));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as HomeSectionDto;
  return Object.hash(runtimeType,_this.key,_this.source,const DeepCollectionEquality().hash(_this.items),_this.title,_this.subtitle,_this.rule);
}

@override
String toString() {
  final _this = this as HomeSectionDto;
  return 'HomeSectionDto(key: ${_this.key}, source: ${_this.source}, items: ${_this.items}, title: ${_this.title}, subtitle: ${_this.subtitle}, rule: ${_this.rule})';
}


}

/// @nodoc
abstract mixin class $HomeSectionDtoCopyWith<$Res>  {
  factory $HomeSectionDtoCopyWith(HomeSectionDto value, $Res Function(HomeSectionDto) _then) = _$HomeSectionDtoCopyWithImpl;
@useResult
$Res call({
 String key, String source, List<HomeItemDto> items, String? title, String? subtitle, String? rule
});




}
/// @nodoc
class _$HomeSectionDtoCopyWithImpl<$Res>
    implements $HomeSectionDtoCopyWith<$Res> {
  _$HomeSectionDtoCopyWithImpl(this._self, this._then);

  final HomeSectionDto _self;
  final $Res Function(HomeSectionDto) _then;

/// Create a copy of HomeSectionDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? key = null,Object? source = null,Object? items = null,Object? title = freezed,Object? subtitle = freezed,Object? rule = freezed,}) {
  return _then(HomeSectionDto(
key: null == key ? _self.key : key // ignore: cast_nullable_to_non_nullable
as String,source: null == source ? _self.source : source // ignore: cast_nullable_to_non_nullable
as String,items: null == items ? _self.items : items // ignore: cast_nullable_to_non_nullable
as List<HomeItemDto>,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,subtitle: freezed == subtitle ? _self.subtitle : subtitle // ignore: cast_nullable_to_non_nullable
as String?,rule: freezed == rule ? _self.rule : rule // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [HomeSectionDto].
extension HomeSectionDtoPatterns on HomeSectionDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomeSectionDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomeSectionDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomeSectionDto value)  $default,){
final _that = this;
switch (_that) {
case _HomeSectionDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomeSectionDto value)?  $default,){
final _that = this;
switch (_that) {
case _HomeSectionDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String key,  String source,  List<HomeItemDto> items,  String? title,  String? subtitle,  String? rule)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomeSectionDto() when $default != null:
return $default(_that.key,_that.source,_that.items,_that.title,_that.subtitle,_that.rule);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String key,  String source,  List<HomeItemDto> items,  String? title,  String? subtitle,  String? rule)  $default,) {final _that = this;
switch (_that) {
case _HomeSectionDto():
return $default(_that.key,_that.source,_that.items,_that.title,_that.subtitle,_that.rule);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String key,  String source,  List<HomeItemDto> items,  String? title,  String? subtitle,  String? rule)?  $default,) {final _that = this;
switch (_that) {
case _HomeSectionDto() when $default != null:
return $default(_that.key,_that.source,_that.items,_that.title,_that.subtitle,_that.rule);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _HomeSectionDto extends HomeSectionDto {
  const _HomeSectionDto({required this.key, required this.source, required  List<HomeItemDto> items, this.title, this.subtitle, this.rule}): _items = items,super._();
  factory _HomeSectionDto.fromJson(Map<String, dynamic> json) => _$HomeSectionDtoFromJson(json);

@override final  String key;
@override final  String source;
 final  List<HomeItemDto> _items;
@override List<HomeItemDto> get items {
  if (_items is EqualUnmodifiableListView) return _items;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_items);
}

@override final  String? title;
@override final  String? subtitle;
@override final  String? rule;

/// Create a copy of HomeSectionDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomeSectionDtoCopyWith<_HomeSectionDto> get copyWith => __$HomeSectionDtoCopyWithImpl<_HomeSectionDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$HomeSectionDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomeSectionDto&&(identical(other.key, key) || other.key == key)&&(identical(other.source, source) || other.source == source)&&const DeepCollectionEquality().equals(other.items, _items)&&(identical(other.title, title) || other.title == title)&&(identical(other.subtitle, subtitle) || other.subtitle == subtitle)&&(identical(other.rule, rule) || other.rule == rule));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,key,source,const DeepCollectionEquality().hash(_items),title,subtitle,rule);
}

@override
String toString() {
    return 'HomeSectionDto(key: $key, source: $source, items: $items, title: $title, subtitle: $subtitle, rule: $rule)';
}


}

/// @nodoc
abstract mixin class _$HomeSectionDtoCopyWith<$Res> implements $HomeSectionDtoCopyWith<$Res> {
  factory _$HomeSectionDtoCopyWith(_HomeSectionDto value, $Res Function(_HomeSectionDto) _then) = __$HomeSectionDtoCopyWithImpl;
@override @useResult
$Res call({
 String key, String source, List<HomeItemDto> items, String? title, String? subtitle, String? rule
});




}
/// @nodoc
class __$HomeSectionDtoCopyWithImpl<$Res>
    implements _$HomeSectionDtoCopyWith<$Res> {
  __$HomeSectionDtoCopyWithImpl(this._self, this._then);

  final _HomeSectionDto _self;
  final $Res Function(_HomeSectionDto) _then;

/// Create a copy of HomeSectionDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? key = null,Object? source = null,Object? items = null,Object? title = freezed,Object? subtitle = freezed,Object? rule = freezed,}) {
  return _then(_HomeSectionDto(
key: null == key ? _self.key : key // ignore: cast_nullable_to_non_nullable
as String,source: null == source ? _self.source : source // ignore: cast_nullable_to_non_nullable
as String,items: null == items ? _self._items : items // ignore: cast_nullable_to_non_nullable
as List<HomeItemDto>,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,subtitle: freezed == subtitle ? _self.subtitle : subtitle // ignore: cast_nullable_to_non_nullable
as String?,rule: freezed == rule ? _self.rule : rule // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$HomeItemDto {

 String get type; Map<String, dynamic> get data;
/// Create a copy of HomeItemDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeItemDtoCopyWith<HomeItemDto> get copyWith => _$HomeItemDtoCopyWithImpl<HomeItemDto>(this as HomeItemDto, _$identity);

  /// Serializes this HomeItemDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as HomeItemDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeItemDto&&(identical(other.type, _this.type) || other.type == _this.type)&&const DeepCollectionEquality().equals(other.data, _this.data));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as HomeItemDto;
  return Object.hash(runtimeType,_this.type,const DeepCollectionEquality().hash(_this.data));
}

@override
String toString() {
  final _this = this as HomeItemDto;
  return 'HomeItemDto(type: ${_this.type}, data: ${_this.data})';
}


}

/// @nodoc
abstract mixin class $HomeItemDtoCopyWith<$Res>  {
  factory $HomeItemDtoCopyWith(HomeItemDto value, $Res Function(HomeItemDto) _then) = _$HomeItemDtoCopyWithImpl;
@useResult
$Res call({
 String type, Map<String, dynamic> data
});




}
/// @nodoc
class _$HomeItemDtoCopyWithImpl<$Res>
    implements $HomeItemDtoCopyWith<$Res> {
  _$HomeItemDtoCopyWithImpl(this._self, this._then);

  final HomeItemDto _self;
  final $Res Function(HomeItemDto) _then;

/// Create a copy of HomeItemDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? type = null,Object? data = null,}) {
  return _then(HomeItemDto(
type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,data: null == data ? _self.data : data // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>,
  ));
}

}


/// Adds pattern-matching-related methods to [HomeItemDto].
extension HomeItemDtoPatterns on HomeItemDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomeItemDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomeItemDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomeItemDto value)  $default,){
final _that = this;
switch (_that) {
case _HomeItemDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomeItemDto value)?  $default,){
final _that = this;
switch (_that) {
case _HomeItemDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String type,  Map<String, dynamic> data)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomeItemDto() when $default != null:
return $default(_that.type,_that.data);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String type,  Map<String, dynamic> data)  $default,) {final _that = this;
switch (_that) {
case _HomeItemDto():
return $default(_that.type,_that.data);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String type,  Map<String, dynamic> data)?  $default,) {final _that = this;
switch (_that) {
case _HomeItemDto() when $default != null:
return $default(_that.type,_that.data);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _HomeItemDto extends HomeItemDto {
  const _HomeItemDto({required this.type, required  Map<String, dynamic> data}): _data = data,super._();
  factory _HomeItemDto.fromJson(Map<String, dynamic> json) => _$HomeItemDtoFromJson(json);

@override final  String type;
 final  Map<String, dynamic> _data;
@override Map<String, dynamic> get data {
  if (_data is EqualUnmodifiableMapView) return _data;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(_data);
}


/// Create a copy of HomeItemDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomeItemDtoCopyWith<_HomeItemDto> get copyWith => __$HomeItemDtoCopyWithImpl<_HomeItemDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$HomeItemDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomeItemDto&&(identical(other.type, type) || other.type == type)&&const DeepCollectionEquality().equals(other.data, _data));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,type,const DeepCollectionEquality().hash(_data));
}

@override
String toString() {
    return 'HomeItemDto(type: $type, data: $data)';
}


}

/// @nodoc
abstract mixin class _$HomeItemDtoCopyWith<$Res> implements $HomeItemDtoCopyWith<$Res> {
  factory _$HomeItemDtoCopyWith(_HomeItemDto value, $Res Function(_HomeItemDto) _then) = __$HomeItemDtoCopyWithImpl;
@override @useResult
$Res call({
 String type, Map<String, dynamic> data
});




}
/// @nodoc
class __$HomeItemDtoCopyWithImpl<$Res>
    implements _$HomeItemDtoCopyWith<$Res> {
  __$HomeItemDtoCopyWithImpl(this._self, this._then);

  final _HomeItemDto _self;
  final $Res Function(_HomeItemDto) _then;

/// Create a copy of HomeItemDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? type = null,Object? data = null,}) {
  return _then(_HomeItemDto(
type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,data: null == data ? _self._data : data // ignore: cast_nullable_to_non_nullable
as Map<String, dynamic>,
  ));
}


}


/// @nodoc
mixin _$HomeTaxonDto {

 String get name; String get slug; LinkTargetDto get link; MediaImageDto? get logo; MediaImageDto? get banner;
/// Create a copy of HomeTaxonDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeTaxonDtoCopyWith<HomeTaxonDto> get copyWith => _$HomeTaxonDtoCopyWithImpl<HomeTaxonDto>(this as HomeTaxonDto, _$identity);

  /// Serializes this HomeTaxonDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as HomeTaxonDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeTaxonDto&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug)&&(identical(other.link, _this.link) || other.link == _this.link)&&(identical(other.logo, _this.logo) || other.logo == _this.logo)&&(identical(other.banner, _this.banner) || other.banner == _this.banner));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as HomeTaxonDto;
  return Object.hash(runtimeType,_this.name,_this.slug,_this.link,_this.logo,_this.banner);
}

@override
String toString() {
  final _this = this as HomeTaxonDto;
  return 'HomeTaxonDto(name: ${_this.name}, slug: ${_this.slug}, link: ${_this.link}, logo: ${_this.logo}, banner: ${_this.banner})';
}


}

/// @nodoc
abstract mixin class $HomeTaxonDtoCopyWith<$Res>  {
  factory $HomeTaxonDtoCopyWith(HomeTaxonDto value, $Res Function(HomeTaxonDto) _then) = _$HomeTaxonDtoCopyWithImpl;
@useResult
$Res call({
 String name, String slug, LinkTargetDto link, MediaImageDto? logo, MediaImageDto? banner
});


$LinkTargetDtoCopyWith<$Res> get link;$MediaImageDtoCopyWith<$Res>? get logo;$MediaImageDtoCopyWith<$Res>? get banner;

}
/// @nodoc
class _$HomeTaxonDtoCopyWithImpl<$Res>
    implements $HomeTaxonDtoCopyWith<$Res> {
  _$HomeTaxonDtoCopyWithImpl(this._self, this._then);

  final HomeTaxonDto _self;
  final $Res Function(HomeTaxonDto) _then;

/// Create a copy of HomeTaxonDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? name = null,Object? slug = null,Object? link = null,Object? logo = freezed,Object? banner = freezed,}) {
  return _then(HomeTaxonDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,link: null == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTargetDto,logo: freezed == logo ? _self.logo : logo // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,banner: freezed == banner ? _self.banner : banner // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,
  ));
}
/// Create a copy of HomeTaxonDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetDtoCopyWith<$Res> get link {
  
  return $LinkTargetDtoCopyWith<$Res>(_self.link, (value) {
    return _then(_self.copyWith(link: value));
  });
}/// Create a copy of HomeTaxonDto
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
}/// Create a copy of HomeTaxonDto
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


/// Adds pattern-matching-related methods to [HomeTaxonDto].
extension HomeTaxonDtoPatterns on HomeTaxonDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomeTaxonDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomeTaxonDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomeTaxonDto value)  $default,){
final _that = this;
switch (_that) {
case _HomeTaxonDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomeTaxonDto value)?  $default,){
final _that = this;
switch (_that) {
case _HomeTaxonDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String name,  String slug,  LinkTargetDto link,  MediaImageDto? logo,  MediaImageDto? banner)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomeTaxonDto() when $default != null:
return $default(_that.name,_that.slug,_that.link,_that.logo,_that.banner);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String name,  String slug,  LinkTargetDto link,  MediaImageDto? logo,  MediaImageDto? banner)  $default,) {final _that = this;
switch (_that) {
case _HomeTaxonDto():
return $default(_that.name,_that.slug,_that.link,_that.logo,_that.banner);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String name,  String slug,  LinkTargetDto link,  MediaImageDto? logo,  MediaImageDto? banner)?  $default,) {final _that = this;
switch (_that) {
case _HomeTaxonDto() when $default != null:
return $default(_that.name,_that.slug,_that.link,_that.logo,_that.banner);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _HomeTaxonDto extends HomeTaxonDto {
  const _HomeTaxonDto({required this.name, required this.slug, required this.link, this.logo, this.banner}): super._();
  factory _HomeTaxonDto.fromJson(Map<String, dynamic> json) => _$HomeTaxonDtoFromJson(json);

@override final  String name;
@override final  String slug;
@override final  LinkTargetDto link;
@override final  MediaImageDto? logo;
@override final  MediaImageDto? banner;

/// Create a copy of HomeTaxonDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomeTaxonDtoCopyWith<_HomeTaxonDto> get copyWith => __$HomeTaxonDtoCopyWithImpl<_HomeTaxonDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$HomeTaxonDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomeTaxonDto&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug)&&(identical(other.link, link) || other.link == link)&&(identical(other.logo, logo) || other.logo == logo)&&(identical(other.banner, banner) || other.banner == banner));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,name,slug,link,logo,banner);
}

@override
String toString() {
    return 'HomeTaxonDto(name: $name, slug: $slug, link: $link, logo: $logo, banner: $banner)';
}


}

/// @nodoc
abstract mixin class _$HomeTaxonDtoCopyWith<$Res> implements $HomeTaxonDtoCopyWith<$Res> {
  factory _$HomeTaxonDtoCopyWith(_HomeTaxonDto value, $Res Function(_HomeTaxonDto) _then) = __$HomeTaxonDtoCopyWithImpl;
@override @useResult
$Res call({
 String name, String slug, LinkTargetDto link, MediaImageDto? logo, MediaImageDto? banner
});


@override $LinkTargetDtoCopyWith<$Res> get link;@override $MediaImageDtoCopyWith<$Res>? get logo;@override $MediaImageDtoCopyWith<$Res>? get banner;

}
/// @nodoc
class __$HomeTaxonDtoCopyWithImpl<$Res>
    implements _$HomeTaxonDtoCopyWith<$Res> {
  __$HomeTaxonDtoCopyWithImpl(this._self, this._then);

  final _HomeTaxonDto _self;
  final $Res Function(_HomeTaxonDto) _then;

/// Create a copy of HomeTaxonDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? name = null,Object? slug = null,Object? link = null,Object? logo = freezed,Object? banner = freezed,}) {
  return _then(_HomeTaxonDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,link: null == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTargetDto,logo: freezed == logo ? _self.logo : logo // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,banner: freezed == banner ? _self.banner : banner // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,
  ));
}

/// Create a copy of HomeTaxonDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetDtoCopyWith<$Res> get link {
  
  return $LinkTargetDtoCopyWith<$Res>(_self.link, (value) {
    return _then(_self.copyWith(link: value));
  });
}/// Create a copy of HomeTaxonDto
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
}/// Create a copy of HomeTaxonDto
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
mixin _$BannerImagesDto {

 MediaImageDto? get desktop; MediaImageDto? get mobile;
/// Create a copy of BannerImagesDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BannerImagesDtoCopyWith<BannerImagesDto> get copyWith => _$BannerImagesDtoCopyWithImpl<BannerImagesDto>(this as BannerImagesDto, _$identity);

  /// Serializes this BannerImagesDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as BannerImagesDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BannerImagesDto&&(identical(other.desktop, _this.desktop) || other.desktop == _this.desktop)&&(identical(other.mobile, _this.mobile) || other.mobile == _this.mobile));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as BannerImagesDto;
  return Object.hash(runtimeType,_this.desktop,_this.mobile);
}

@override
String toString() {
  final _this = this as BannerImagesDto;
  return 'BannerImagesDto(desktop: ${_this.desktop}, mobile: ${_this.mobile})';
}


}

/// @nodoc
abstract mixin class $BannerImagesDtoCopyWith<$Res>  {
  factory $BannerImagesDtoCopyWith(BannerImagesDto value, $Res Function(BannerImagesDto) _then) = _$BannerImagesDtoCopyWithImpl;
@useResult
$Res call({
 MediaImageDto? desktop, MediaImageDto? mobile
});


$MediaImageDtoCopyWith<$Res>? get desktop;$MediaImageDtoCopyWith<$Res>? get mobile;

}
/// @nodoc
class _$BannerImagesDtoCopyWithImpl<$Res>
    implements $BannerImagesDtoCopyWith<$Res> {
  _$BannerImagesDtoCopyWithImpl(this._self, this._then);

  final BannerImagesDto _self;
  final $Res Function(BannerImagesDto) _then;

/// Create a copy of BannerImagesDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? desktop = freezed,Object? mobile = freezed,}) {
  return _then(BannerImagesDto(
desktop: freezed == desktop ? _self.desktop : desktop // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,mobile: freezed == mobile ? _self.mobile : mobile // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,
  ));
}
/// Create a copy of BannerImagesDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get desktop {
    if (_self.desktop == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.desktop!, (value) {
    return _then(_self.copyWith(desktop: value));
  });
}/// Create a copy of BannerImagesDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get mobile {
    if (_self.mobile == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.mobile!, (value) {
    return _then(_self.copyWith(mobile: value));
  });
}
}


/// Adds pattern-matching-related methods to [BannerImagesDto].
extension BannerImagesDtoPatterns on BannerImagesDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _BannerImagesDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _BannerImagesDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _BannerImagesDto value)  $default,){
final _that = this;
switch (_that) {
case _BannerImagesDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _BannerImagesDto value)?  $default,){
final _that = this;
switch (_that) {
case _BannerImagesDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( MediaImageDto? desktop,  MediaImageDto? mobile)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _BannerImagesDto() when $default != null:
return $default(_that.desktop,_that.mobile);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( MediaImageDto? desktop,  MediaImageDto? mobile)  $default,) {final _that = this;
switch (_that) {
case _BannerImagesDto():
return $default(_that.desktop,_that.mobile);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( MediaImageDto? desktop,  MediaImageDto? mobile)?  $default,) {final _that = this;
switch (_that) {
case _BannerImagesDto() when $default != null:
return $default(_that.desktop,_that.mobile);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _BannerImagesDto implements BannerImagesDto {
  const _BannerImagesDto({this.desktop, this.mobile});
  factory _BannerImagesDto.fromJson(Map<String, dynamic> json) => _$BannerImagesDtoFromJson(json);

@override final  MediaImageDto? desktop;
@override final  MediaImageDto? mobile;

/// Create a copy of BannerImagesDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$BannerImagesDtoCopyWith<_BannerImagesDto> get copyWith => __$BannerImagesDtoCopyWithImpl<_BannerImagesDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$BannerImagesDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _BannerImagesDto&&(identical(other.desktop, desktop) || other.desktop == desktop)&&(identical(other.mobile, mobile) || other.mobile == mobile));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,desktop,mobile);
}

@override
String toString() {
    return 'BannerImagesDto(desktop: $desktop, mobile: $mobile)';
}


}

/// @nodoc
abstract mixin class _$BannerImagesDtoCopyWith<$Res> implements $BannerImagesDtoCopyWith<$Res> {
  factory _$BannerImagesDtoCopyWith(_BannerImagesDto value, $Res Function(_BannerImagesDto) _then) = __$BannerImagesDtoCopyWithImpl;
@override @useResult
$Res call({
 MediaImageDto? desktop, MediaImageDto? mobile
});


@override $MediaImageDtoCopyWith<$Res>? get desktop;@override $MediaImageDtoCopyWith<$Res>? get mobile;

}
/// @nodoc
class __$BannerImagesDtoCopyWithImpl<$Res>
    implements _$BannerImagesDtoCopyWith<$Res> {
  __$BannerImagesDtoCopyWithImpl(this._self, this._then);

  final _BannerImagesDto _self;
  final $Res Function(_BannerImagesDto) _then;

/// Create a copy of BannerImagesDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? desktop = freezed,Object? mobile = freezed,}) {
  return _then(_BannerImagesDto(
desktop: freezed == desktop ? _self.desktop : desktop // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,mobile: freezed == mobile ? _self.mobile : mobile // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,
  ));
}

/// Create a copy of BannerImagesDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get desktop {
    if (_self.desktop == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.desktop!, (value) {
    return _then(_self.copyWith(desktop: value));
  });
}/// Create a copy of BannerImagesDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get mobile {
    if (_self.mobile == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.mobile!, (value) {
    return _then(_self.copyWith(mobile: value));
  });
}
}


/// @nodoc
mixin _$BannerDto {

 int get id; String get placement; BannerImagesDto get image; String? get title; String? get subtitle; String? get ctaLabel; LinkTargetDto? get link; DateTime? get startsAt; DateTime? get endsAt;
/// Create a copy of BannerDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$BannerDtoCopyWith<BannerDto> get copyWith => _$BannerDtoCopyWithImpl<BannerDto>(this as BannerDto, _$identity);

  /// Serializes this BannerDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as BannerDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is BannerDto&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.placement, _this.placement) || other.placement == _this.placement)&&(identical(other.image, _this.image) || other.image == _this.image)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.subtitle, _this.subtitle) || other.subtitle == _this.subtitle)&&(identical(other.ctaLabel, _this.ctaLabel) || other.ctaLabel == _this.ctaLabel)&&(identical(other.link, _this.link) || other.link == _this.link)&&(identical(other.startsAt, _this.startsAt) || other.startsAt == _this.startsAt)&&(identical(other.endsAt, _this.endsAt) || other.endsAt == _this.endsAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as BannerDto;
  return Object.hash(runtimeType,_this.id,_this.placement,_this.image,_this.title,_this.subtitle,_this.ctaLabel,_this.link,_this.startsAt,_this.endsAt);
}

@override
String toString() {
  final _this = this as BannerDto;
  return 'BannerDto(id: ${_this.id}, placement: ${_this.placement}, image: ${_this.image}, title: ${_this.title}, subtitle: ${_this.subtitle}, ctaLabel: ${_this.ctaLabel}, link: ${_this.link}, startsAt: ${_this.startsAt}, endsAt: ${_this.endsAt})';
}


}

/// @nodoc
abstract mixin class $BannerDtoCopyWith<$Res>  {
  factory $BannerDtoCopyWith(BannerDto value, $Res Function(BannerDto) _then) = _$BannerDtoCopyWithImpl;
@useResult
$Res call({
 int id, String placement, BannerImagesDto image, String? title, String? subtitle, String? ctaLabel, LinkTargetDto? link, DateTime? startsAt, DateTime? endsAt
});


$BannerImagesDtoCopyWith<$Res> get image;$LinkTargetDtoCopyWith<$Res>? get link;

}
/// @nodoc
class _$BannerDtoCopyWithImpl<$Res>
    implements $BannerDtoCopyWith<$Res> {
  _$BannerDtoCopyWithImpl(this._self, this._then);

  final BannerDto _self;
  final $Res Function(BannerDto) _then;

/// Create a copy of BannerDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? placement = null,Object? image = null,Object? title = freezed,Object? subtitle = freezed,Object? ctaLabel = freezed,Object? link = freezed,Object? startsAt = freezed,Object? endsAt = freezed,}) {
  return _then(BannerDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,placement: null == placement ? _self.placement : placement // ignore: cast_nullable_to_non_nullable
as String,image: null == image ? _self.image : image // ignore: cast_nullable_to_non_nullable
as BannerImagesDto,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,subtitle: freezed == subtitle ? _self.subtitle : subtitle // ignore: cast_nullable_to_non_nullable
as String?,ctaLabel: freezed == ctaLabel ? _self.ctaLabel : ctaLabel // ignore: cast_nullable_to_non_nullable
as String?,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTargetDto?,startsAt: freezed == startsAt ? _self.startsAt : startsAt // ignore: cast_nullable_to_non_nullable
as DateTime?,endsAt: freezed == endsAt ? _self.endsAt : endsAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}
/// Create a copy of BannerDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$BannerImagesDtoCopyWith<$Res> get image {
  
  return $BannerImagesDtoCopyWith<$Res>(_self.image, (value) {
    return _then(_self.copyWith(image: value));
  });
}/// Create a copy of BannerDto
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


/// Adds pattern-matching-related methods to [BannerDto].
extension BannerDtoPatterns on BannerDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _BannerDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _BannerDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _BannerDto value)  $default,){
final _that = this;
switch (_that) {
case _BannerDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _BannerDto value)?  $default,){
final _that = this;
switch (_that) {
case _BannerDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  String placement,  BannerImagesDto image,  String? title,  String? subtitle,  String? ctaLabel,  LinkTargetDto? link,  DateTime? startsAt,  DateTime? endsAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _BannerDto() when $default != null:
return $default(_that.id,_that.placement,_that.image,_that.title,_that.subtitle,_that.ctaLabel,_that.link,_that.startsAt,_that.endsAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  String placement,  BannerImagesDto image,  String? title,  String? subtitle,  String? ctaLabel,  LinkTargetDto? link,  DateTime? startsAt,  DateTime? endsAt)  $default,) {final _that = this;
switch (_that) {
case _BannerDto():
return $default(_that.id,_that.placement,_that.image,_that.title,_that.subtitle,_that.ctaLabel,_that.link,_that.startsAt,_that.endsAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  String placement,  BannerImagesDto image,  String? title,  String? subtitle,  String? ctaLabel,  LinkTargetDto? link,  DateTime? startsAt,  DateTime? endsAt)?  $default,) {final _that = this;
switch (_that) {
case _BannerDto() when $default != null:
return $default(_that.id,_that.placement,_that.image,_that.title,_that.subtitle,_that.ctaLabel,_that.link,_that.startsAt,_that.endsAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _BannerDto extends BannerDto {
  const _BannerDto({required this.id, required this.placement, required this.image, this.title, this.subtitle, this.ctaLabel, this.link, this.startsAt, this.endsAt}): super._();
  factory _BannerDto.fromJson(Map<String, dynamic> json) => _$BannerDtoFromJson(json);

@override final  int id;
@override final  String placement;
@override final  BannerImagesDto image;
@override final  String? title;
@override final  String? subtitle;
@override final  String? ctaLabel;
@override final  LinkTargetDto? link;
@override final  DateTime? startsAt;
@override final  DateTime? endsAt;

/// Create a copy of BannerDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$BannerDtoCopyWith<_BannerDto> get copyWith => __$BannerDtoCopyWithImpl<_BannerDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$BannerDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _BannerDto&&(identical(other.id, id) || other.id == id)&&(identical(other.placement, placement) || other.placement == placement)&&(identical(other.image, image) || other.image == image)&&(identical(other.title, title) || other.title == title)&&(identical(other.subtitle, subtitle) || other.subtitle == subtitle)&&(identical(other.ctaLabel, ctaLabel) || other.ctaLabel == ctaLabel)&&(identical(other.link, link) || other.link == link)&&(identical(other.startsAt, startsAt) || other.startsAt == startsAt)&&(identical(other.endsAt, endsAt) || other.endsAt == endsAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,id,placement,image,title,subtitle,ctaLabel,link,startsAt,endsAt);
}

@override
String toString() {
    return 'BannerDto(id: $id, placement: $placement, image: $image, title: $title, subtitle: $subtitle, ctaLabel: $ctaLabel, link: $link, startsAt: $startsAt, endsAt: $endsAt)';
}


}

/// @nodoc
abstract mixin class _$BannerDtoCopyWith<$Res> implements $BannerDtoCopyWith<$Res> {
  factory _$BannerDtoCopyWith(_BannerDto value, $Res Function(_BannerDto) _then) = __$BannerDtoCopyWithImpl;
@override @useResult
$Res call({
 int id, String placement, BannerImagesDto image, String? title, String? subtitle, String? ctaLabel, LinkTargetDto? link, DateTime? startsAt, DateTime? endsAt
});


@override $BannerImagesDtoCopyWith<$Res> get image;@override $LinkTargetDtoCopyWith<$Res>? get link;

}
/// @nodoc
class __$BannerDtoCopyWithImpl<$Res>
    implements _$BannerDtoCopyWith<$Res> {
  __$BannerDtoCopyWithImpl(this._self, this._then);

  final _BannerDto _self;
  final $Res Function(_BannerDto) _then;

/// Create a copy of BannerDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? placement = null,Object? image = null,Object? title = freezed,Object? subtitle = freezed,Object? ctaLabel = freezed,Object? link = freezed,Object? startsAt = freezed,Object? endsAt = freezed,}) {
  return _then(_BannerDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,placement: null == placement ? _self.placement : placement // ignore: cast_nullable_to_non_nullable
as String,image: null == image ? _self.image : image // ignore: cast_nullable_to_non_nullable
as BannerImagesDto,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,subtitle: freezed == subtitle ? _self.subtitle : subtitle // ignore: cast_nullable_to_non_nullable
as String?,ctaLabel: freezed == ctaLabel ? _self.ctaLabel : ctaLabel // ignore: cast_nullable_to_non_nullable
as String?,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTargetDto?,startsAt: freezed == startsAt ? _self.startsAt : startsAt // ignore: cast_nullable_to_non_nullable
as DateTime?,endsAt: freezed == endsAt ? _self.endsAt : endsAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

/// Create a copy of BannerDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$BannerImagesDtoCopyWith<$Res> get image {
  
  return $BannerImagesDtoCopyWith<$Res>(_self.image, (value) {
    return _then(_self.copyWith(image: value));
  });
}/// Create a copy of BannerDto
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


/// @nodoc
mixin _$HomeFaqDto {

 int get id; String get question; String get answer;
/// Create a copy of HomeFaqDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeFaqDtoCopyWith<HomeFaqDto> get copyWith => _$HomeFaqDtoCopyWithImpl<HomeFaqDto>(this as HomeFaqDto, _$identity);

  /// Serializes this HomeFaqDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as HomeFaqDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeFaqDto&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.question, _this.question) || other.question == _this.question)&&(identical(other.answer, _this.answer) || other.answer == _this.answer));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as HomeFaqDto;
  return Object.hash(runtimeType,_this.id,_this.question,_this.answer);
}

@override
String toString() {
  final _this = this as HomeFaqDto;
  return 'HomeFaqDto(id: ${_this.id}, question: ${_this.question}, answer: ${_this.answer})';
}


}

/// @nodoc
abstract mixin class $HomeFaqDtoCopyWith<$Res>  {
  factory $HomeFaqDtoCopyWith(HomeFaqDto value, $Res Function(HomeFaqDto) _then) = _$HomeFaqDtoCopyWithImpl;
@useResult
$Res call({
 int id, String question, String answer
});




}
/// @nodoc
class _$HomeFaqDtoCopyWithImpl<$Res>
    implements $HomeFaqDtoCopyWith<$Res> {
  _$HomeFaqDtoCopyWithImpl(this._self, this._then);

  final HomeFaqDto _self;
  final $Res Function(HomeFaqDto) _then;

/// Create a copy of HomeFaqDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? question = null,Object? answer = null,}) {
  return _then(HomeFaqDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,question: null == question ? _self.question : question // ignore: cast_nullable_to_non_nullable
as String,answer: null == answer ? _self.answer : answer // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [HomeFaqDto].
extension HomeFaqDtoPatterns on HomeFaqDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomeFaqDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomeFaqDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomeFaqDto value)  $default,){
final _that = this;
switch (_that) {
case _HomeFaqDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomeFaqDto value)?  $default,){
final _that = this;
switch (_that) {
case _HomeFaqDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  String question,  String answer)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomeFaqDto() when $default != null:
return $default(_that.id,_that.question,_that.answer);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  String question,  String answer)  $default,) {final _that = this;
switch (_that) {
case _HomeFaqDto():
return $default(_that.id,_that.question,_that.answer);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  String question,  String answer)?  $default,) {final _that = this;
switch (_that) {
case _HomeFaqDto() when $default != null:
return $default(_that.id,_that.question,_that.answer);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _HomeFaqDto extends HomeFaqDto {
  const _HomeFaqDto({required this.id, required this.question, required this.answer}): super._();
  factory _HomeFaqDto.fromJson(Map<String, dynamic> json) => _$HomeFaqDtoFromJson(json);

@override final  int id;
@override final  String question;
@override final  String answer;

/// Create a copy of HomeFaqDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomeFaqDtoCopyWith<_HomeFaqDto> get copyWith => __$HomeFaqDtoCopyWithImpl<_HomeFaqDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$HomeFaqDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomeFaqDto&&(identical(other.id, id) || other.id == id)&&(identical(other.question, question) || other.question == question)&&(identical(other.answer, answer) || other.answer == answer));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,id,question,answer);
}

@override
String toString() {
    return 'HomeFaqDto(id: $id, question: $question, answer: $answer)';
}


}

/// @nodoc
abstract mixin class _$HomeFaqDtoCopyWith<$Res> implements $HomeFaqDtoCopyWith<$Res> {
  factory _$HomeFaqDtoCopyWith(_HomeFaqDto value, $Res Function(_HomeFaqDto) _then) = __$HomeFaqDtoCopyWithImpl;
@override @useResult
$Res call({
 int id, String question, String answer
});




}
/// @nodoc
class __$HomeFaqDtoCopyWithImpl<$Res>
    implements _$HomeFaqDtoCopyWith<$Res> {
  __$HomeFaqDtoCopyWithImpl(this._self, this._then);

  final _HomeFaqDto _self;
  final $Res Function(_HomeFaqDto) _then;

/// Create a copy of HomeFaqDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? question = null,Object? answer = null,}) {
  return _then(_HomeFaqDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,question: null == question ? _self.question : question // ignore: cast_nullable_to_non_nullable
as String,answer: null == answer ? _self.answer : answer // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}


/// @nodoc
mixin _$HomeReviewProductDto {

 String get name; String get slug; LinkTargetDto? get link;
/// Create a copy of HomeReviewProductDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeReviewProductDtoCopyWith<HomeReviewProductDto> get copyWith => _$HomeReviewProductDtoCopyWithImpl<HomeReviewProductDto>(this as HomeReviewProductDto, _$identity);

  /// Serializes this HomeReviewProductDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as HomeReviewProductDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeReviewProductDto&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug)&&(identical(other.link, _this.link) || other.link == _this.link));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as HomeReviewProductDto;
  return Object.hash(runtimeType,_this.name,_this.slug,_this.link);
}

@override
String toString() {
  final _this = this as HomeReviewProductDto;
  return 'HomeReviewProductDto(name: ${_this.name}, slug: ${_this.slug}, link: ${_this.link})';
}


}

/// @nodoc
abstract mixin class $HomeReviewProductDtoCopyWith<$Res>  {
  factory $HomeReviewProductDtoCopyWith(HomeReviewProductDto value, $Res Function(HomeReviewProductDto) _then) = _$HomeReviewProductDtoCopyWithImpl;
@useResult
$Res call({
 String name, String slug, LinkTargetDto? link
});


$LinkTargetDtoCopyWith<$Res>? get link;

}
/// @nodoc
class _$HomeReviewProductDtoCopyWithImpl<$Res>
    implements $HomeReviewProductDtoCopyWith<$Res> {
  _$HomeReviewProductDtoCopyWithImpl(this._self, this._then);

  final HomeReviewProductDto _self;
  final $Res Function(HomeReviewProductDto) _then;

/// Create a copy of HomeReviewProductDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? name = null,Object? slug = null,Object? link = freezed,}) {
  return _then(HomeReviewProductDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTargetDto?,
  ));
}
/// Create a copy of HomeReviewProductDto
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


/// Adds pattern-matching-related methods to [HomeReviewProductDto].
extension HomeReviewProductDtoPatterns on HomeReviewProductDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomeReviewProductDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomeReviewProductDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomeReviewProductDto value)  $default,){
final _that = this;
switch (_that) {
case _HomeReviewProductDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomeReviewProductDto value)?  $default,){
final _that = this;
switch (_that) {
case _HomeReviewProductDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String name,  String slug,  LinkTargetDto? link)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomeReviewProductDto() when $default != null:
return $default(_that.name,_that.slug,_that.link);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String name,  String slug,  LinkTargetDto? link)  $default,) {final _that = this;
switch (_that) {
case _HomeReviewProductDto():
return $default(_that.name,_that.slug,_that.link);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String name,  String slug,  LinkTargetDto? link)?  $default,) {final _that = this;
switch (_that) {
case _HomeReviewProductDto() when $default != null:
return $default(_that.name,_that.slug,_that.link);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _HomeReviewProductDto implements HomeReviewProductDto {
  const _HomeReviewProductDto({required this.name, required this.slug, this.link});
  factory _HomeReviewProductDto.fromJson(Map<String, dynamic> json) => _$HomeReviewProductDtoFromJson(json);

@override final  String name;
@override final  String slug;
@override final  LinkTargetDto? link;

/// Create a copy of HomeReviewProductDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomeReviewProductDtoCopyWith<_HomeReviewProductDto> get copyWith => __$HomeReviewProductDtoCopyWithImpl<_HomeReviewProductDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$HomeReviewProductDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomeReviewProductDto&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug)&&(identical(other.link, link) || other.link == link));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,name,slug,link);
}

@override
String toString() {
    return 'HomeReviewProductDto(name: $name, slug: $slug, link: $link)';
}


}

/// @nodoc
abstract mixin class _$HomeReviewProductDtoCopyWith<$Res> implements $HomeReviewProductDtoCopyWith<$Res> {
  factory _$HomeReviewProductDtoCopyWith(_HomeReviewProductDto value, $Res Function(_HomeReviewProductDto) _then) = __$HomeReviewProductDtoCopyWithImpl;
@override @useResult
$Res call({
 String name, String slug, LinkTargetDto? link
});


@override $LinkTargetDtoCopyWith<$Res>? get link;

}
/// @nodoc
class __$HomeReviewProductDtoCopyWithImpl<$Res>
    implements _$HomeReviewProductDtoCopyWith<$Res> {
  __$HomeReviewProductDtoCopyWithImpl(this._self, this._then);

  final _HomeReviewProductDto _self;
  final $Res Function(_HomeReviewProductDto) _then;

/// Create a copy of HomeReviewProductDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? name = null,Object? slug = null,Object? link = freezed,}) {
  return _then(_HomeReviewProductDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTargetDto?,
  ));
}

/// Create a copy of HomeReviewProductDto
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


/// @nodoc
mixin _$HomeReviewDto {

 int get id; int get rating; String get reviewer; bool get verifiedPurchase; DateTime get createdAt; HomeReviewProductDto get product; String? get title; String? get body;
/// Create a copy of HomeReviewDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeReviewDtoCopyWith<HomeReviewDto> get copyWith => _$HomeReviewDtoCopyWithImpl<HomeReviewDto>(this as HomeReviewDto, _$identity);

  /// Serializes this HomeReviewDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as HomeReviewDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeReviewDto&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.rating, _this.rating) || other.rating == _this.rating)&&(identical(other.reviewer, _this.reviewer) || other.reviewer == _this.reviewer)&&(identical(other.verifiedPurchase, _this.verifiedPurchase) || other.verifiedPurchase == _this.verifiedPurchase)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.product, _this.product) || other.product == _this.product)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.body, _this.body) || other.body == _this.body));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as HomeReviewDto;
  return Object.hash(runtimeType,_this.id,_this.rating,_this.reviewer,_this.verifiedPurchase,_this.createdAt,_this.product,_this.title,_this.body);
}

@override
String toString() {
  final _this = this as HomeReviewDto;
  return 'HomeReviewDto(id: ${_this.id}, rating: ${_this.rating}, reviewer: ${_this.reviewer}, verifiedPurchase: ${_this.verifiedPurchase}, createdAt: ${_this.createdAt}, product: ${_this.product}, title: ${_this.title}, body: ${_this.body})';
}


}

/// @nodoc
abstract mixin class $HomeReviewDtoCopyWith<$Res>  {
  factory $HomeReviewDtoCopyWith(HomeReviewDto value, $Res Function(HomeReviewDto) _then) = _$HomeReviewDtoCopyWithImpl;
@useResult
$Res call({
 int id, int rating, String reviewer, bool verifiedPurchase, DateTime createdAt, HomeReviewProductDto product, String? title, String? body
});


$HomeReviewProductDtoCopyWith<$Res> get product;

}
/// @nodoc
class _$HomeReviewDtoCopyWithImpl<$Res>
    implements $HomeReviewDtoCopyWith<$Res> {
  _$HomeReviewDtoCopyWithImpl(this._self, this._then);

  final HomeReviewDto _self;
  final $Res Function(HomeReviewDto) _then;

/// Create a copy of HomeReviewDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? rating = null,Object? reviewer = null,Object? verifiedPurchase = null,Object? createdAt = null,Object? product = null,Object? title = freezed,Object? body = freezed,}) {
  return _then(HomeReviewDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as int,reviewer: null == reviewer ? _self.reviewer : reviewer // ignore: cast_nullable_to_non_nullable
as String,verifiedPurchase: null == verifiedPurchase ? _self.verifiedPurchase : verifiedPurchase // ignore: cast_nullable_to_non_nullable
as bool,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,product: null == product ? _self.product : product // ignore: cast_nullable_to_non_nullable
as HomeReviewProductDto,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,body: freezed == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}
/// Create a copy of HomeReviewDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$HomeReviewProductDtoCopyWith<$Res> get product {
  
  return $HomeReviewProductDtoCopyWith<$Res>(_self.product, (value) {
    return _then(_self.copyWith(product: value));
  });
}
}


/// Adds pattern-matching-related methods to [HomeReviewDto].
extension HomeReviewDtoPatterns on HomeReviewDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomeReviewDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomeReviewDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomeReviewDto value)  $default,){
final _that = this;
switch (_that) {
case _HomeReviewDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomeReviewDto value)?  $default,){
final _that = this;
switch (_that) {
case _HomeReviewDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  int rating,  String reviewer,  bool verifiedPurchase,  DateTime createdAt,  HomeReviewProductDto product,  String? title,  String? body)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomeReviewDto() when $default != null:
return $default(_that.id,_that.rating,_that.reviewer,_that.verifiedPurchase,_that.createdAt,_that.product,_that.title,_that.body);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  int rating,  String reviewer,  bool verifiedPurchase,  DateTime createdAt,  HomeReviewProductDto product,  String? title,  String? body)  $default,) {final _that = this;
switch (_that) {
case _HomeReviewDto():
return $default(_that.id,_that.rating,_that.reviewer,_that.verifiedPurchase,_that.createdAt,_that.product,_that.title,_that.body);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  int rating,  String reviewer,  bool verifiedPurchase,  DateTime createdAt,  HomeReviewProductDto product,  String? title,  String? body)?  $default,) {final _that = this;
switch (_that) {
case _HomeReviewDto() when $default != null:
return $default(_that.id,_that.rating,_that.reviewer,_that.verifiedPurchase,_that.createdAt,_that.product,_that.title,_that.body);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _HomeReviewDto extends HomeReviewDto {
  const _HomeReviewDto({required this.id, required this.rating, required this.reviewer, required this.verifiedPurchase, required this.createdAt, required this.product, this.title, this.body}): super._();
  factory _HomeReviewDto.fromJson(Map<String, dynamic> json) => _$HomeReviewDtoFromJson(json);

@override final  int id;
@override final  int rating;
@override final  String reviewer;
@override final  bool verifiedPurchase;
@override final  DateTime createdAt;
@override final  HomeReviewProductDto product;
@override final  String? title;
@override final  String? body;

/// Create a copy of HomeReviewDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomeReviewDtoCopyWith<_HomeReviewDto> get copyWith => __$HomeReviewDtoCopyWithImpl<_HomeReviewDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$HomeReviewDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomeReviewDto&&(identical(other.id, id) || other.id == id)&&(identical(other.rating, rating) || other.rating == rating)&&(identical(other.reviewer, reviewer) || other.reviewer == reviewer)&&(identical(other.verifiedPurchase, verifiedPurchase) || other.verifiedPurchase == verifiedPurchase)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.product, product) || other.product == product)&&(identical(other.title, title) || other.title == title)&&(identical(other.body, body) || other.body == body));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,id,rating,reviewer,verifiedPurchase,createdAt,product,title,body);
}

@override
String toString() {
    return 'HomeReviewDto(id: $id, rating: $rating, reviewer: $reviewer, verifiedPurchase: $verifiedPurchase, createdAt: $createdAt, product: $product, title: $title, body: $body)';
}


}

/// @nodoc
abstract mixin class _$HomeReviewDtoCopyWith<$Res> implements $HomeReviewDtoCopyWith<$Res> {
  factory _$HomeReviewDtoCopyWith(_HomeReviewDto value, $Res Function(_HomeReviewDto) _then) = __$HomeReviewDtoCopyWithImpl;
@override @useResult
$Res call({
 int id, int rating, String reviewer, bool verifiedPurchase, DateTime createdAt, HomeReviewProductDto product, String? title, String? body
});


@override $HomeReviewProductDtoCopyWith<$Res> get product;

}
/// @nodoc
class __$HomeReviewDtoCopyWithImpl<$Res>
    implements _$HomeReviewDtoCopyWith<$Res> {
  __$HomeReviewDtoCopyWithImpl(this._self, this._then);

  final _HomeReviewDto _self;
  final $Res Function(_HomeReviewDto) _then;

/// Create a copy of HomeReviewDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? rating = null,Object? reviewer = null,Object? verifiedPurchase = null,Object? createdAt = null,Object? product = null,Object? title = freezed,Object? body = freezed,}) {
  return _then(_HomeReviewDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as int,reviewer: null == reviewer ? _self.reviewer : reviewer // ignore: cast_nullable_to_non_nullable
as String,verifiedPurchase: null == verifiedPurchase ? _self.verifiedPurchase : verifiedPurchase // ignore: cast_nullable_to_non_nullable
as bool,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,product: null == product ? _self.product : product // ignore: cast_nullable_to_non_nullable
as HomeReviewProductDto,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,body: freezed == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

/// Create a copy of HomeReviewDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$HomeReviewProductDtoCopyWith<$Res> get product {
  
  return $HomeReviewProductDtoCopyWith<$Res>(_self.product, (value) {
    return _then(_self.copyWith(product: value));
  });
}
}

// dart format on
