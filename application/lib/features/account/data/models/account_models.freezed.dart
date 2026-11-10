// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'account_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$ProfileDto {

 String get publicId; String get name; String get email; bool get emailVerified; bool get twoFactorEnabled; DateTime get createdAt; String? get phone; String? get locale; String? get displayCurrency; String? get countryCode;
/// Create a copy of ProfileDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ProfileDtoCopyWith<ProfileDto> get copyWith => _$ProfileDtoCopyWithImpl<ProfileDto>(this as ProfileDto, _$identity);

  /// Serializes this ProfileDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as ProfileDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ProfileDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.email, _this.email) || other.email == _this.email)&&(identical(other.emailVerified, _this.emailVerified) || other.emailVerified == _this.emailVerified)&&(identical(other.twoFactorEnabled, _this.twoFactorEnabled) || other.twoFactorEnabled == _this.twoFactorEnabled)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.phone, _this.phone) || other.phone == _this.phone)&&(identical(other.locale, _this.locale) || other.locale == _this.locale)&&(identical(other.displayCurrency, _this.displayCurrency) || other.displayCurrency == _this.displayCurrency)&&(identical(other.countryCode, _this.countryCode) || other.countryCode == _this.countryCode));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as ProfileDto;
  return Object.hash(runtimeType,_this.publicId,_this.name,_this.email,_this.emailVerified,_this.twoFactorEnabled,_this.createdAt,_this.phone,_this.locale,_this.displayCurrency,_this.countryCode);
}

@override
String toString() {
  final _this = this as ProfileDto;
  return 'ProfileDto(publicId: ${_this.publicId}, name: ${_this.name}, email: ${_this.email}, emailVerified: ${_this.emailVerified}, twoFactorEnabled: ${_this.twoFactorEnabled}, createdAt: ${_this.createdAt}, phone: ${_this.phone}, locale: ${_this.locale}, displayCurrency: ${_this.displayCurrency}, countryCode: ${_this.countryCode})';
}


}

/// @nodoc
abstract mixin class $ProfileDtoCopyWith<$Res>  {
  factory $ProfileDtoCopyWith(ProfileDto value, $Res Function(ProfileDto) _then) = _$ProfileDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String name, String email, bool emailVerified, bool twoFactorEnabled, DateTime createdAt, String? phone, String? locale, String? displayCurrency, String? countryCode
});




}
/// @nodoc
class _$ProfileDtoCopyWithImpl<$Res>
    implements $ProfileDtoCopyWith<$Res> {
  _$ProfileDtoCopyWithImpl(this._self, this._then);

  final ProfileDto _self;
  final $Res Function(ProfileDto) _then;

/// Create a copy of ProfileDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? name = null,Object? email = null,Object? emailVerified = null,Object? twoFactorEnabled = null,Object? createdAt = null,Object? phone = freezed,Object? locale = freezed,Object? displayCurrency = freezed,Object? countryCode = freezed,}) {
  return _then(ProfileDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,emailVerified: null == emailVerified ? _self.emailVerified : emailVerified // ignore: cast_nullable_to_non_nullable
as bool,twoFactorEnabled: null == twoFactorEnabled ? _self.twoFactorEnabled : twoFactorEnabled // ignore: cast_nullable_to_non_nullable
as bool,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,phone: freezed == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String?,locale: freezed == locale ? _self.locale : locale // ignore: cast_nullable_to_non_nullable
as String?,displayCurrency: freezed == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String?,countryCode: freezed == countryCode ? _self.countryCode : countryCode // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [ProfileDto].
extension ProfileDtoPatterns on ProfileDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ProfileDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ProfileDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ProfileDto value)  $default,){
final _that = this;
switch (_that) {
case _ProfileDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ProfileDto value)?  $default,){
final _that = this;
switch (_that) {
case _ProfileDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String name,  String email,  bool emailVerified,  bool twoFactorEnabled,  DateTime createdAt,  String? phone,  String? locale,  String? displayCurrency,  String? countryCode)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ProfileDto() when $default != null:
return $default(_that.publicId,_that.name,_that.email,_that.emailVerified,_that.twoFactorEnabled,_that.createdAt,_that.phone,_that.locale,_that.displayCurrency,_that.countryCode);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String name,  String email,  bool emailVerified,  bool twoFactorEnabled,  DateTime createdAt,  String? phone,  String? locale,  String? displayCurrency,  String? countryCode)  $default,) {final _that = this;
switch (_that) {
case _ProfileDto():
return $default(_that.publicId,_that.name,_that.email,_that.emailVerified,_that.twoFactorEnabled,_that.createdAt,_that.phone,_that.locale,_that.displayCurrency,_that.countryCode);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String name,  String email,  bool emailVerified,  bool twoFactorEnabled,  DateTime createdAt,  String? phone,  String? locale,  String? displayCurrency,  String? countryCode)?  $default,) {final _that = this;
switch (_that) {
case _ProfileDto() when $default != null:
return $default(_that.publicId,_that.name,_that.email,_that.emailVerified,_that.twoFactorEnabled,_that.createdAt,_that.phone,_that.locale,_that.displayCurrency,_that.countryCode);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _ProfileDto extends ProfileDto {
  const _ProfileDto({required this.publicId, required this.name, required this.email, required this.emailVerified, required this.twoFactorEnabled, required this.createdAt, this.phone, this.locale, this.displayCurrency, this.countryCode}): super._();
  factory _ProfileDto.fromJson(Map<String, dynamic> json) => _$ProfileDtoFromJson(json);

@override final  String publicId;
@override final  String name;
@override final  String email;
@override final  bool emailVerified;
@override final  bool twoFactorEnabled;
@override final  DateTime createdAt;
@override final  String? phone;
@override final  String? locale;
@override final  String? displayCurrency;
@override final  String? countryCode;

/// Create a copy of ProfileDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ProfileDtoCopyWith<_ProfileDto> get copyWith => __$ProfileDtoCopyWithImpl<_ProfileDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$ProfileDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ProfileDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.name, name) || other.name == name)&&(identical(other.email, email) || other.email == email)&&(identical(other.emailVerified, emailVerified) || other.emailVerified == emailVerified)&&(identical(other.twoFactorEnabled, twoFactorEnabled) || other.twoFactorEnabled == twoFactorEnabled)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.locale, locale) || other.locale == locale)&&(identical(other.displayCurrency, displayCurrency) || other.displayCurrency == displayCurrency)&&(identical(other.countryCode, countryCode) || other.countryCode == countryCode));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,name,email,emailVerified,twoFactorEnabled,createdAt,phone,locale,displayCurrency,countryCode);
}

@override
String toString() {
    return 'ProfileDto(publicId: $publicId, name: $name, email: $email, emailVerified: $emailVerified, twoFactorEnabled: $twoFactorEnabled, createdAt: $createdAt, phone: $phone, locale: $locale, displayCurrency: $displayCurrency, countryCode: $countryCode)';
}


}

/// @nodoc
abstract mixin class _$ProfileDtoCopyWith<$Res> implements $ProfileDtoCopyWith<$Res> {
  factory _$ProfileDtoCopyWith(_ProfileDto value, $Res Function(_ProfileDto) _then) = __$ProfileDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String name, String email, bool emailVerified, bool twoFactorEnabled, DateTime createdAt, String? phone, String? locale, String? displayCurrency, String? countryCode
});




}
/// @nodoc
class __$ProfileDtoCopyWithImpl<$Res>
    implements _$ProfileDtoCopyWith<$Res> {
  __$ProfileDtoCopyWithImpl(this._self, this._then);

  final _ProfileDto _self;
  final $Res Function(_ProfileDto) _then;

/// Create a copy of ProfileDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? name = null,Object? email = null,Object? emailVerified = null,Object? twoFactorEnabled = null,Object? createdAt = null,Object? phone = freezed,Object? locale = freezed,Object? displayCurrency = freezed,Object? countryCode = freezed,}) {
  return _then(_ProfileDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,emailVerified: null == emailVerified ? _self.emailVerified : emailVerified // ignore: cast_nullable_to_non_nullable
as bool,twoFactorEnabled: null == twoFactorEnabled ? _self.twoFactorEnabled : twoFactorEnabled // ignore: cast_nullable_to_non_nullable
as bool,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,phone: freezed == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String?,locale: freezed == locale ? _self.locale : locale // ignore: cast_nullable_to_non_nullable
as String?,displayCurrency: freezed == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String?,countryCode: freezed == countryCode ? _self.countryCode : countryCode // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$SignedOutDto {

 int get mobileFamilies; int get webSessions;
/// Create a copy of SignedOutDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SignedOutDtoCopyWith<SignedOutDto> get copyWith => _$SignedOutDtoCopyWithImpl<SignedOutDto>(this as SignedOutDto, _$identity);

  /// Serializes this SignedOutDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as SignedOutDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SignedOutDto&&(identical(other.mobileFamilies, _this.mobileFamilies) || other.mobileFamilies == _this.mobileFamilies)&&(identical(other.webSessions, _this.webSessions) || other.webSessions == _this.webSessions));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as SignedOutDto;
  return Object.hash(runtimeType,_this.mobileFamilies,_this.webSessions);
}

@override
String toString() {
  final _this = this as SignedOutDto;
  return 'SignedOutDto(mobileFamilies: ${_this.mobileFamilies}, webSessions: ${_this.webSessions})';
}


}

/// @nodoc
abstract mixin class $SignedOutDtoCopyWith<$Res>  {
  factory $SignedOutDtoCopyWith(SignedOutDto value, $Res Function(SignedOutDto) _then) = _$SignedOutDtoCopyWithImpl;
@useResult
$Res call({
 int mobileFamilies, int webSessions
});




}
/// @nodoc
class _$SignedOutDtoCopyWithImpl<$Res>
    implements $SignedOutDtoCopyWith<$Res> {
  _$SignedOutDtoCopyWithImpl(this._self, this._then);

  final SignedOutDto _self;
  final $Res Function(SignedOutDto) _then;

/// Create a copy of SignedOutDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? mobileFamilies = null,Object? webSessions = null,}) {
  return _then(SignedOutDto(
mobileFamilies: null == mobileFamilies ? _self.mobileFamilies : mobileFamilies // ignore: cast_nullable_to_non_nullable
as int,webSessions: null == webSessions ? _self.webSessions : webSessions // ignore: cast_nullable_to_non_nullable
as int,
  ));
}

}


/// Adds pattern-matching-related methods to [SignedOutDto].
extension SignedOutDtoPatterns on SignedOutDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _SignedOutDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _SignedOutDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _SignedOutDto value)  $default,){
final _that = this;
switch (_that) {
case _SignedOutDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _SignedOutDto value)?  $default,){
final _that = this;
switch (_that) {
case _SignedOutDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int mobileFamilies,  int webSessions)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _SignedOutDto() when $default != null:
return $default(_that.mobileFamilies,_that.webSessions);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int mobileFamilies,  int webSessions)  $default,) {final _that = this;
switch (_that) {
case _SignedOutDto():
return $default(_that.mobileFamilies,_that.webSessions);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int mobileFamilies,  int webSessions)?  $default,) {final _that = this;
switch (_that) {
case _SignedOutDto() when $default != null:
return $default(_that.mobileFamilies,_that.webSessions);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _SignedOutDto implements SignedOutDto {
  const _SignedOutDto({required this.mobileFamilies, required this.webSessions});
  factory _SignedOutDto.fromJson(Map<String, dynamic> json) => _$SignedOutDtoFromJson(json);

@override final  int mobileFamilies;
@override final  int webSessions;

/// Create a copy of SignedOutDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$SignedOutDtoCopyWith<_SignedOutDto> get copyWith => __$SignedOutDtoCopyWithImpl<_SignedOutDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$SignedOutDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _SignedOutDto&&(identical(other.mobileFamilies, mobileFamilies) || other.mobileFamilies == mobileFamilies)&&(identical(other.webSessions, webSessions) || other.webSessions == webSessions));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,mobileFamilies,webSessions);
}

@override
String toString() {
    return 'SignedOutDto(mobileFamilies: $mobileFamilies, webSessions: $webSessions)';
}


}

/// @nodoc
abstract mixin class _$SignedOutDtoCopyWith<$Res> implements $SignedOutDtoCopyWith<$Res> {
  factory _$SignedOutDtoCopyWith(_SignedOutDto value, $Res Function(_SignedOutDto) _then) = __$SignedOutDtoCopyWithImpl;
@override @useResult
$Res call({
 int mobileFamilies, int webSessions
});




}
/// @nodoc
class __$SignedOutDtoCopyWithImpl<$Res>
    implements _$SignedOutDtoCopyWith<$Res> {
  __$SignedOutDtoCopyWithImpl(this._self, this._then);

  final _SignedOutDto _self;
  final $Res Function(_SignedOutDto) _then;

/// Create a copy of SignedOutDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? mobileFamilies = null,Object? webSessions = null,}) {
  return _then(_SignedOutDto(
mobileFamilies: null == mobileFamilies ? _self.mobileFamilies : mobileFamilies // ignore: cast_nullable_to_non_nullable
as int,webSessions: null == webSessions ? _self.webSessions : webSessions // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}


/// @nodoc
mixin _$DeviceDto {

 String get id; String get type; bool get current; String? get deviceName; String? get userAgent; String? get ip; DateTime? get createdAt; DateTime? get lastUsedAt; DateTime? get expiresAt;
/// Create a copy of DeviceDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DeviceDtoCopyWith<DeviceDto> get copyWith => _$DeviceDtoCopyWithImpl<DeviceDto>(this as DeviceDto, _$identity);

  /// Serializes this DeviceDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as DeviceDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DeviceDto&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.type, _this.type) || other.type == _this.type)&&(identical(other.current, _this.current) || other.current == _this.current)&&(identical(other.deviceName, _this.deviceName) || other.deviceName == _this.deviceName)&&(identical(other.userAgent, _this.userAgent) || other.userAgent == _this.userAgent)&&(identical(other.ip, _this.ip) || other.ip == _this.ip)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.lastUsedAt, _this.lastUsedAt) || other.lastUsedAt == _this.lastUsedAt)&&(identical(other.expiresAt, _this.expiresAt) || other.expiresAt == _this.expiresAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as DeviceDto;
  return Object.hash(runtimeType,_this.id,_this.type,_this.current,_this.deviceName,_this.userAgent,_this.ip,_this.createdAt,_this.lastUsedAt,_this.expiresAt);
}

@override
String toString() {
  final _this = this as DeviceDto;
  return 'DeviceDto(id: ${_this.id}, type: ${_this.type}, current: ${_this.current}, deviceName: ${_this.deviceName}, userAgent: ${_this.userAgent}, ip: ${_this.ip}, createdAt: ${_this.createdAt}, lastUsedAt: ${_this.lastUsedAt}, expiresAt: ${_this.expiresAt})';
}


}

/// @nodoc
abstract mixin class $DeviceDtoCopyWith<$Res>  {
  factory $DeviceDtoCopyWith(DeviceDto value, $Res Function(DeviceDto) _then) = _$DeviceDtoCopyWithImpl;
@useResult
$Res call({
 String id, String type, bool current, String? deviceName, String? userAgent, String? ip, DateTime? createdAt, DateTime? lastUsedAt, DateTime? expiresAt
});




}
/// @nodoc
class _$DeviceDtoCopyWithImpl<$Res>
    implements $DeviceDtoCopyWith<$Res> {
  _$DeviceDtoCopyWithImpl(this._self, this._then);

  final DeviceDto _self;
  final $Res Function(DeviceDto) _then;

/// Create a copy of DeviceDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? type = null,Object? current = null,Object? deviceName = freezed,Object? userAgent = freezed,Object? ip = freezed,Object? createdAt = freezed,Object? lastUsedAt = freezed,Object? expiresAt = freezed,}) {
  return _then(DeviceDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,current: null == current ? _self.current : current // ignore: cast_nullable_to_non_nullable
as bool,deviceName: freezed == deviceName ? _self.deviceName : deviceName // ignore: cast_nullable_to_non_nullable
as String?,userAgent: freezed == userAgent ? _self.userAgent : userAgent // ignore: cast_nullable_to_non_nullable
as String?,ip: freezed == ip ? _self.ip : ip // ignore: cast_nullable_to_non_nullable
as String?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,lastUsedAt: freezed == lastUsedAt ? _self.lastUsedAt : lastUsedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,expiresAt: freezed == expiresAt ? _self.expiresAt : expiresAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [DeviceDto].
extension DeviceDtoPatterns on DeviceDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _DeviceDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _DeviceDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _DeviceDto value)  $default,){
final _that = this;
switch (_that) {
case _DeviceDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _DeviceDto value)?  $default,){
final _that = this;
switch (_that) {
case _DeviceDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String type,  bool current,  String? deviceName,  String? userAgent,  String? ip,  DateTime? createdAt,  DateTime? lastUsedAt,  DateTime? expiresAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _DeviceDto() when $default != null:
return $default(_that.id,_that.type,_that.current,_that.deviceName,_that.userAgent,_that.ip,_that.createdAt,_that.lastUsedAt,_that.expiresAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String type,  bool current,  String? deviceName,  String? userAgent,  String? ip,  DateTime? createdAt,  DateTime? lastUsedAt,  DateTime? expiresAt)  $default,) {final _that = this;
switch (_that) {
case _DeviceDto():
return $default(_that.id,_that.type,_that.current,_that.deviceName,_that.userAgent,_that.ip,_that.createdAt,_that.lastUsedAt,_that.expiresAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String type,  bool current,  String? deviceName,  String? userAgent,  String? ip,  DateTime? createdAt,  DateTime? lastUsedAt,  DateTime? expiresAt)?  $default,) {final _that = this;
switch (_that) {
case _DeviceDto() when $default != null:
return $default(_that.id,_that.type,_that.current,_that.deviceName,_that.userAgent,_that.ip,_that.createdAt,_that.lastUsedAt,_that.expiresAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _DeviceDto extends DeviceDto {
  const _DeviceDto({required this.id, required this.type, required this.current, this.deviceName, this.userAgent, this.ip, this.createdAt, this.lastUsedAt, this.expiresAt}): super._();
  factory _DeviceDto.fromJson(Map<String, dynamic> json) => _$DeviceDtoFromJson(json);

@override final  String id;
@override final  String type;
@override final  bool current;
@override final  String? deviceName;
@override final  String? userAgent;
@override final  String? ip;
@override final  DateTime? createdAt;
@override final  DateTime? lastUsedAt;
@override final  DateTime? expiresAt;

/// Create a copy of DeviceDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DeviceDtoCopyWith<_DeviceDto> get copyWith => __$DeviceDtoCopyWithImpl<_DeviceDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$DeviceDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _DeviceDto&&(identical(other.id, id) || other.id == id)&&(identical(other.type, type) || other.type == type)&&(identical(other.current, current) || other.current == current)&&(identical(other.deviceName, deviceName) || other.deviceName == deviceName)&&(identical(other.userAgent, userAgent) || other.userAgent == userAgent)&&(identical(other.ip, ip) || other.ip == ip)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.lastUsedAt, lastUsedAt) || other.lastUsedAt == lastUsedAt)&&(identical(other.expiresAt, expiresAt) || other.expiresAt == expiresAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,id,type,current,deviceName,userAgent,ip,createdAt,lastUsedAt,expiresAt);
}

@override
String toString() {
    return 'DeviceDto(id: $id, type: $type, current: $current, deviceName: $deviceName, userAgent: $userAgent, ip: $ip, createdAt: $createdAt, lastUsedAt: $lastUsedAt, expiresAt: $expiresAt)';
}


}

/// @nodoc
abstract mixin class _$DeviceDtoCopyWith<$Res> implements $DeviceDtoCopyWith<$Res> {
  factory _$DeviceDtoCopyWith(_DeviceDto value, $Res Function(_DeviceDto) _then) = __$DeviceDtoCopyWithImpl;
@override @useResult
$Res call({
 String id, String type, bool current, String? deviceName, String? userAgent, String? ip, DateTime? createdAt, DateTime? lastUsedAt, DateTime? expiresAt
});




}
/// @nodoc
class __$DeviceDtoCopyWithImpl<$Res>
    implements _$DeviceDtoCopyWith<$Res> {
  __$DeviceDtoCopyWithImpl(this._self, this._then);

  final _DeviceDto _self;
  final $Res Function(_DeviceDto) _then;

/// Create a copy of DeviceDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? type = null,Object? current = null,Object? deviceName = freezed,Object? userAgent = freezed,Object? ip = freezed,Object? createdAt = freezed,Object? lastUsedAt = freezed,Object? expiresAt = freezed,}) {
  return _then(_DeviceDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,current: null == current ? _self.current : current // ignore: cast_nullable_to_non_nullable
as bool,deviceName: freezed == deviceName ? _self.deviceName : deviceName // ignore: cast_nullable_to_non_nullable
as String?,userAgent: freezed == userAgent ? _self.userAgent : userAgent // ignore: cast_nullable_to_non_nullable
as String?,ip: freezed == ip ? _self.ip : ip // ignore: cast_nullable_to_non_nullable
as String?,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,lastUsedAt: freezed == lastUsedAt ? _self.lastUsedAt : lastUsedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,expiresAt: freezed == expiresAt ? _self.expiresAt : expiresAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}

// dart format on
