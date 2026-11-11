// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'push_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$PushTokenDto {

/// `android` or `ios`.
 String get platform; DateTime get registeredAt;
/// Create a copy of PushTokenDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PushTokenDtoCopyWith<PushTokenDto> get copyWith => _$PushTokenDtoCopyWithImpl<PushTokenDto>(this as PushTokenDto, _$identity);

  /// Serializes this PushTokenDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as PushTokenDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PushTokenDto&&(identical(other.platform, _this.platform) || other.platform == _this.platform)&&(identical(other.registeredAt, _this.registeredAt) || other.registeredAt == _this.registeredAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as PushTokenDto;
  return Object.hash(runtimeType,_this.platform,_this.registeredAt);
}

@override
String toString() {
  final _this = this as PushTokenDto;
  return 'PushTokenDto(platform: ${_this.platform}, registeredAt: ${_this.registeredAt})';
}


}

/// @nodoc
abstract mixin class $PushTokenDtoCopyWith<$Res>  {
  factory $PushTokenDtoCopyWith(PushTokenDto value, $Res Function(PushTokenDto) _then) = _$PushTokenDtoCopyWithImpl;
@useResult
$Res call({
 String platform, DateTime registeredAt
});




}
/// @nodoc
class _$PushTokenDtoCopyWithImpl<$Res>
    implements $PushTokenDtoCopyWith<$Res> {
  _$PushTokenDtoCopyWithImpl(this._self, this._then);

  final PushTokenDto _self;
  final $Res Function(PushTokenDto) _then;

/// Create a copy of PushTokenDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? platform = null,Object? registeredAt = null,}) {
  return _then(PushTokenDto(
platform: null == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as String,registeredAt: null == registeredAt ? _self.registeredAt : registeredAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}

}


/// Adds pattern-matching-related methods to [PushTokenDto].
extension PushTokenDtoPatterns on PushTokenDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PushTokenDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PushTokenDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PushTokenDto value)  $default,){
final _that = this;
switch (_that) {
case _PushTokenDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PushTokenDto value)?  $default,){
final _that = this;
switch (_that) {
case _PushTokenDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String platform,  DateTime registeredAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PushTokenDto() when $default != null:
return $default(_that.platform,_that.registeredAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String platform,  DateTime registeredAt)  $default,) {final _that = this;
switch (_that) {
case _PushTokenDto():
return $default(_that.platform,_that.registeredAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String platform,  DateTime registeredAt)?  $default,) {final _that = this;
switch (_that) {
case _PushTokenDto() when $default != null:
return $default(_that.platform,_that.registeredAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _PushTokenDto implements PushTokenDto {
  const _PushTokenDto({required this.platform, required this.registeredAt});
  factory _PushTokenDto.fromJson(Map<String, dynamic> json) => _$PushTokenDtoFromJson(json);

/// `android` or `ios`.
@override final  String platform;
@override final  DateTime registeredAt;

/// Create a copy of PushTokenDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PushTokenDtoCopyWith<_PushTokenDto> get copyWith => __$PushTokenDtoCopyWithImpl<_PushTokenDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$PushTokenDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PushTokenDto&&(identical(other.platform, platform) || other.platform == platform)&&(identical(other.registeredAt, registeredAt) || other.registeredAt == registeredAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,platform,registeredAt);
}

@override
String toString() {
    return 'PushTokenDto(platform: $platform, registeredAt: $registeredAt)';
}


}

/// @nodoc
abstract mixin class _$PushTokenDtoCopyWith<$Res> implements $PushTokenDtoCopyWith<$Res> {
  factory _$PushTokenDtoCopyWith(_PushTokenDto value, $Res Function(_PushTokenDto) _then) = __$PushTokenDtoCopyWithImpl;
@override @useResult
$Res call({
 String platform, DateTime registeredAt
});




}
/// @nodoc
class __$PushTokenDtoCopyWithImpl<$Res>
    implements _$PushTokenDtoCopyWith<$Res> {
  __$PushTokenDtoCopyWithImpl(this._self, this._then);

  final _PushTokenDto _self;
  final $Res Function(_PushTokenDto) _then;

/// Create a copy of PushTokenDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? platform = null,Object? registeredAt = null,}) {
  return _then(_PushTokenDto(
platform: null == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as String,registeredAt: null == registeredAt ? _self.registeredAt : registeredAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}

// dart format on
