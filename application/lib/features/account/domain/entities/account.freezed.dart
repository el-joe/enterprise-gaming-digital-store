// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'account.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$Profile {

 String get publicId; String get name; String get email; bool get emailVerified; bool get twoFactorEnabled; DateTime get createdAt;/// E.164, for example `+201001234567`.
 String? get phone;/// `en`, `ar` or null; the app sends it as `Accept-Language` after sign-in.
 String? get locale; String? get displayCurrency; String? get countryCode;
/// Create a copy of Profile
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ProfileCopyWith<Profile> get copyWith => _$ProfileCopyWithImpl<Profile>(this as Profile, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as Profile;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is Profile&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.email, _this.email) || other.email == _this.email)&&(identical(other.emailVerified, _this.emailVerified) || other.emailVerified == _this.emailVerified)&&(identical(other.twoFactorEnabled, _this.twoFactorEnabled) || other.twoFactorEnabled == _this.twoFactorEnabled)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.phone, _this.phone) || other.phone == _this.phone)&&(identical(other.locale, _this.locale) || other.locale == _this.locale)&&(identical(other.displayCurrency, _this.displayCurrency) || other.displayCurrency == _this.displayCurrency)&&(identical(other.countryCode, _this.countryCode) || other.countryCode == _this.countryCode));
}


@override
int get hashCode {
  final _this = this as Profile;
  return Object.hash(runtimeType,_this.publicId,_this.name,_this.email,_this.emailVerified,_this.twoFactorEnabled,_this.createdAt,_this.phone,_this.locale,_this.displayCurrency,_this.countryCode);
}

@override
String toString() {
  final _this = this as Profile;
  return 'Profile(publicId: ${_this.publicId}, name: ${_this.name}, email: ${_this.email}, emailVerified: ${_this.emailVerified}, twoFactorEnabled: ${_this.twoFactorEnabled}, createdAt: ${_this.createdAt}, phone: ${_this.phone}, locale: ${_this.locale}, displayCurrency: ${_this.displayCurrency}, countryCode: ${_this.countryCode})';
}


}

/// @nodoc
abstract mixin class $ProfileCopyWith<$Res>  {
  factory $ProfileCopyWith(Profile value, $Res Function(Profile) _then) = _$ProfileCopyWithImpl;
@useResult
$Res call({
 String publicId, String name, String email, bool emailVerified, bool twoFactorEnabled, DateTime createdAt, String? phone, String? locale, String? displayCurrency, String? countryCode
});




}
/// @nodoc
class _$ProfileCopyWithImpl<$Res>
    implements $ProfileCopyWith<$Res> {
  _$ProfileCopyWithImpl(this._self, this._then);

  final Profile _self;
  final $Res Function(Profile) _then;

/// Create a copy of Profile
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? name = null,Object? email = null,Object? emailVerified = null,Object? twoFactorEnabled = null,Object? createdAt = null,Object? phone = freezed,Object? locale = freezed,Object? displayCurrency = freezed,Object? countryCode = freezed,}) {
  return _then(Profile(
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


/// Adds pattern-matching-related methods to [Profile].
extension ProfilePatterns on Profile {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _Profile value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _Profile() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _Profile value)  $default,){
final _that = this;
switch (_that) {
case _Profile():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _Profile value)?  $default,){
final _that = this;
switch (_that) {
case _Profile() when $default != null:
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
case _Profile() when $default != null:
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
case _Profile():
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
case _Profile() when $default != null:
return $default(_that.publicId,_that.name,_that.email,_that.emailVerified,_that.twoFactorEnabled,_that.createdAt,_that.phone,_that.locale,_that.displayCurrency,_that.countryCode);case _:
  return null;

}
}

}

/// @nodoc


class _Profile implements Profile {
  const _Profile({required this.publicId, required this.name, required this.email, required this.emailVerified, required this.twoFactorEnabled, required this.createdAt, this.phone, this.locale, this.displayCurrency, this.countryCode});
  

@override final  String publicId;
@override final  String name;
@override final  String email;
@override final  bool emailVerified;
@override final  bool twoFactorEnabled;
@override final  DateTime createdAt;
/// E.164, for example `+201001234567`.
@override final  String? phone;
/// `en`, `ar` or null; the app sends it as `Accept-Language` after sign-in.
@override final  String? locale;
@override final  String? displayCurrency;
@override final  String? countryCode;

/// Create a copy of Profile
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ProfileCopyWith<_Profile> get copyWith => __$ProfileCopyWithImpl<_Profile>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _Profile&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.name, name) || other.name == name)&&(identical(other.email, email) || other.email == email)&&(identical(other.emailVerified, emailVerified) || other.emailVerified == emailVerified)&&(identical(other.twoFactorEnabled, twoFactorEnabled) || other.twoFactorEnabled == twoFactorEnabled)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.locale, locale) || other.locale == locale)&&(identical(other.displayCurrency, displayCurrency) || other.displayCurrency == displayCurrency)&&(identical(other.countryCode, countryCode) || other.countryCode == countryCode));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,name,email,emailVerified,twoFactorEnabled,createdAt,phone,locale,displayCurrency,countryCode);
}

@override
String toString() {
    return 'Profile(publicId: $publicId, name: $name, email: $email, emailVerified: $emailVerified, twoFactorEnabled: $twoFactorEnabled, createdAt: $createdAt, phone: $phone, locale: $locale, displayCurrency: $displayCurrency, countryCode: $countryCode)';
}


}

/// @nodoc
abstract mixin class _$ProfileCopyWith<$Res> implements $ProfileCopyWith<$Res> {
  factory _$ProfileCopyWith(_Profile value, $Res Function(_Profile) _then) = __$ProfileCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String name, String email, bool emailVerified, bool twoFactorEnabled, DateTime createdAt, String? phone, String? locale, String? displayCurrency, String? countryCode
});




}
/// @nodoc
class __$ProfileCopyWithImpl<$Res>
    implements _$ProfileCopyWith<$Res> {
  __$ProfileCopyWithImpl(this._self, this._then);

  final _Profile _self;
  final $Res Function(_Profile) _then;

/// Create a copy of Profile
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? name = null,Object? email = null,Object? emailVerified = null,Object? twoFactorEnabled = null,Object? createdAt = null,Object? phone = freezed,Object? locale = freezed,Object? displayCurrency = freezed,Object? countryCode = freezed,}) {
  return _then(_Profile(
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
mixin _$PasswordChangeResult {

 int get mobileFamilies; int get webSessions;
/// Create a copy of PasswordChangeResult
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PasswordChangeResultCopyWith<PasswordChangeResult> get copyWith => _$PasswordChangeResultCopyWithImpl<PasswordChangeResult>(this as PasswordChangeResult, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as PasswordChangeResult;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PasswordChangeResult&&(identical(other.mobileFamilies, _this.mobileFamilies) || other.mobileFamilies == _this.mobileFamilies)&&(identical(other.webSessions, _this.webSessions) || other.webSessions == _this.webSessions));
}


@override
int get hashCode {
  final _this = this as PasswordChangeResult;
  return Object.hash(runtimeType,_this.mobileFamilies,_this.webSessions);
}

@override
String toString() {
  final _this = this as PasswordChangeResult;
  return 'PasswordChangeResult(mobileFamilies: ${_this.mobileFamilies}, webSessions: ${_this.webSessions})';
}


}

/// @nodoc
abstract mixin class $PasswordChangeResultCopyWith<$Res>  {
  factory $PasswordChangeResultCopyWith(PasswordChangeResult value, $Res Function(PasswordChangeResult) _then) = _$PasswordChangeResultCopyWithImpl;
@useResult
$Res call({
 int mobileFamilies, int webSessions
});




}
/// @nodoc
class _$PasswordChangeResultCopyWithImpl<$Res>
    implements $PasswordChangeResultCopyWith<$Res> {
  _$PasswordChangeResultCopyWithImpl(this._self, this._then);

  final PasswordChangeResult _self;
  final $Res Function(PasswordChangeResult) _then;

/// Create a copy of PasswordChangeResult
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? mobileFamilies = null,Object? webSessions = null,}) {
  return _then(PasswordChangeResult(
mobileFamilies: null == mobileFamilies ? _self.mobileFamilies : mobileFamilies // ignore: cast_nullable_to_non_nullable
as int,webSessions: null == webSessions ? _self.webSessions : webSessions // ignore: cast_nullable_to_non_nullable
as int,
  ));
}

}


/// Adds pattern-matching-related methods to [PasswordChangeResult].
extension PasswordChangeResultPatterns on PasswordChangeResult {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PasswordChangeResult value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PasswordChangeResult() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PasswordChangeResult value)  $default,){
final _that = this;
switch (_that) {
case _PasswordChangeResult():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PasswordChangeResult value)?  $default,){
final _that = this;
switch (_that) {
case _PasswordChangeResult() when $default != null:
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
case _PasswordChangeResult() when $default != null:
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
case _PasswordChangeResult():
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
case _PasswordChangeResult() when $default != null:
return $default(_that.mobileFamilies,_that.webSessions);case _:
  return null;

}
}

}

/// @nodoc


class _PasswordChangeResult implements PasswordChangeResult {
  const _PasswordChangeResult({required this.mobileFamilies, required this.webSessions});
  

@override final  int mobileFamilies;
@override final  int webSessions;

/// Create a copy of PasswordChangeResult
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PasswordChangeResultCopyWith<_PasswordChangeResult> get copyWith => __$PasswordChangeResultCopyWithImpl<_PasswordChangeResult>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PasswordChangeResult&&(identical(other.mobileFamilies, mobileFamilies) || other.mobileFamilies == mobileFamilies)&&(identical(other.webSessions, webSessions) || other.webSessions == webSessions));
}


@override
int get hashCode {
    return Object.hash(runtimeType,mobileFamilies,webSessions);
}

@override
String toString() {
    return 'PasswordChangeResult(mobileFamilies: $mobileFamilies, webSessions: $webSessions)';
}


}

/// @nodoc
abstract mixin class _$PasswordChangeResultCopyWith<$Res> implements $PasswordChangeResultCopyWith<$Res> {
  factory _$PasswordChangeResultCopyWith(_PasswordChangeResult value, $Res Function(_PasswordChangeResult) _then) = __$PasswordChangeResultCopyWithImpl;
@override @useResult
$Res call({
 int mobileFamilies, int webSessions
});




}
/// @nodoc
class __$PasswordChangeResultCopyWithImpl<$Res>
    implements _$PasswordChangeResultCopyWith<$Res> {
  __$PasswordChangeResultCopyWithImpl(this._self, this._then);

  final _PasswordChangeResult _self;
  final $Res Function(_PasswordChangeResult) _then;

/// Create a copy of PasswordChangeResult
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? mobileFamilies = null,Object? webSessions = null,}) {
  return _then(_PasswordChangeResult(
mobileFamilies: null == mobileFamilies ? _self.mobileFamilies : mobileFamilies // ignore: cast_nullable_to_non_nullable
as int,webSessions: null == webSessions ? _self.webSessions : webSessions // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc
mixin _$Device {

 String get id;/// `mobile` or `web`.
 String get type; bool get current; String? get deviceName; String? get userAgent;/// Masked (`203.0.113.x`).
 String? get ip; DateTime? get createdAt; DateTime? get lastUsedAt; DateTime? get expiresAt;
/// Create a copy of Device
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DeviceCopyWith<Device> get copyWith => _$DeviceCopyWithImpl<Device>(this as Device, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as Device;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is Device&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.type, _this.type) || other.type == _this.type)&&(identical(other.current, _this.current) || other.current == _this.current)&&(identical(other.deviceName, _this.deviceName) || other.deviceName == _this.deviceName)&&(identical(other.userAgent, _this.userAgent) || other.userAgent == _this.userAgent)&&(identical(other.ip, _this.ip) || other.ip == _this.ip)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.lastUsedAt, _this.lastUsedAt) || other.lastUsedAt == _this.lastUsedAt)&&(identical(other.expiresAt, _this.expiresAt) || other.expiresAt == _this.expiresAt));
}


@override
int get hashCode {
  final _this = this as Device;
  return Object.hash(runtimeType,_this.id,_this.type,_this.current,_this.deviceName,_this.userAgent,_this.ip,_this.createdAt,_this.lastUsedAt,_this.expiresAt);
}

@override
String toString() {
  final _this = this as Device;
  return 'Device(id: ${_this.id}, type: ${_this.type}, current: ${_this.current}, deviceName: ${_this.deviceName}, userAgent: ${_this.userAgent}, ip: ${_this.ip}, createdAt: ${_this.createdAt}, lastUsedAt: ${_this.lastUsedAt}, expiresAt: ${_this.expiresAt})';
}


}

/// @nodoc
abstract mixin class $DeviceCopyWith<$Res>  {
  factory $DeviceCopyWith(Device value, $Res Function(Device) _then) = _$DeviceCopyWithImpl;
@useResult
$Res call({
 String id, String type, bool current, String? deviceName, String? userAgent, String? ip, DateTime? createdAt, DateTime? lastUsedAt, DateTime? expiresAt
});




}
/// @nodoc
class _$DeviceCopyWithImpl<$Res>
    implements $DeviceCopyWith<$Res> {
  _$DeviceCopyWithImpl(this._self, this._then);

  final Device _self;
  final $Res Function(Device) _then;

/// Create a copy of Device
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? type = null,Object? current = null,Object? deviceName = freezed,Object? userAgent = freezed,Object? ip = freezed,Object? createdAt = freezed,Object? lastUsedAt = freezed,Object? expiresAt = freezed,}) {
  return _then(Device(
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


/// Adds pattern-matching-related methods to [Device].
extension DevicePatterns on Device {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _Device value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _Device() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _Device value)  $default,){
final _that = this;
switch (_that) {
case _Device():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _Device value)?  $default,){
final _that = this;
switch (_that) {
case _Device() when $default != null:
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
case _Device() when $default != null:
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
case _Device():
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
case _Device() when $default != null:
return $default(_that.id,_that.type,_that.current,_that.deviceName,_that.userAgent,_that.ip,_that.createdAt,_that.lastUsedAt,_that.expiresAt);case _:
  return null;

}
}

}

/// @nodoc


class _Device implements Device {
  const _Device({required this.id, required this.type, required this.current, this.deviceName, this.userAgent, this.ip, this.createdAt, this.lastUsedAt, this.expiresAt});
  

@override final  String id;
/// `mobile` or `web`.
@override final  String type;
@override final  bool current;
@override final  String? deviceName;
@override final  String? userAgent;
/// Masked (`203.0.113.x`).
@override final  String? ip;
@override final  DateTime? createdAt;
@override final  DateTime? lastUsedAt;
@override final  DateTime? expiresAt;

/// Create a copy of Device
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DeviceCopyWith<_Device> get copyWith => __$DeviceCopyWithImpl<_Device>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _Device&&(identical(other.id, id) || other.id == id)&&(identical(other.type, type) || other.type == type)&&(identical(other.current, current) || other.current == current)&&(identical(other.deviceName, deviceName) || other.deviceName == deviceName)&&(identical(other.userAgent, userAgent) || other.userAgent == userAgent)&&(identical(other.ip, ip) || other.ip == ip)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.lastUsedAt, lastUsedAt) || other.lastUsedAt == lastUsedAt)&&(identical(other.expiresAt, expiresAt) || other.expiresAt == expiresAt));
}


@override
int get hashCode {
    return Object.hash(runtimeType,id,type,current,deviceName,userAgent,ip,createdAt,lastUsedAt,expiresAt);
}

@override
String toString() {
    return 'Device(id: $id, type: $type, current: $current, deviceName: $deviceName, userAgent: $userAgent, ip: $ip, createdAt: $createdAt, lastUsedAt: $lastUsedAt, expiresAt: $expiresAt)';
}


}

/// @nodoc
abstract mixin class _$DeviceCopyWith<$Res> implements $DeviceCopyWith<$Res> {
  factory _$DeviceCopyWith(_Device value, $Res Function(_Device) _then) = __$DeviceCopyWithImpl;
@override @useResult
$Res call({
 String id, String type, bool current, String? deviceName, String? userAgent, String? ip, DateTime? createdAt, DateTime? lastUsedAt, DateTime? expiresAt
});




}
/// @nodoc
class __$DeviceCopyWithImpl<$Res>
    implements _$DeviceCopyWith<$Res> {
  __$DeviceCopyWithImpl(this._self, this._then);

  final _Device _self;
  final $Res Function(_Device) _then;

/// Create a copy of Device
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? type = null,Object? current = null,Object? deviceName = freezed,Object? userAgent = freezed,Object? ip = freezed,Object? createdAt = freezed,Object? lastUsedAt = freezed,Object? expiresAt = freezed,}) {
  return _then(_Device(
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

/// @nodoc
mixin _$DeviceList {

 List<Device> get devices;/// False when the server's session driver cannot list browser sessions.
 bool get webSessionsListed;
/// Create a copy of DeviceList
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DeviceListCopyWith<DeviceList> get copyWith => _$DeviceListCopyWithImpl<DeviceList>(this as DeviceList, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as DeviceList;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DeviceList&&const DeepCollectionEquality().equals(other.devices, _this.devices)&&(identical(other.webSessionsListed, _this.webSessionsListed) || other.webSessionsListed == _this.webSessionsListed));
}


@override
int get hashCode {
  final _this = this as DeviceList;
  return Object.hash(runtimeType,const DeepCollectionEquality().hash(_this.devices),_this.webSessionsListed);
}

@override
String toString() {
  final _this = this as DeviceList;
  return 'DeviceList(devices: ${_this.devices}, webSessionsListed: ${_this.webSessionsListed})';
}


}

/// @nodoc
abstract mixin class $DeviceListCopyWith<$Res>  {
  factory $DeviceListCopyWith(DeviceList value, $Res Function(DeviceList) _then) = _$DeviceListCopyWithImpl;
@useResult
$Res call({
 List<Device> devices, bool webSessionsListed
});




}
/// @nodoc
class _$DeviceListCopyWithImpl<$Res>
    implements $DeviceListCopyWith<$Res> {
  _$DeviceListCopyWithImpl(this._self, this._then);

  final DeviceList _self;
  final $Res Function(DeviceList) _then;

/// Create a copy of DeviceList
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? devices = null,Object? webSessionsListed = null,}) {
  return _then(DeviceList(
devices: null == devices ? _self.devices : devices // ignore: cast_nullable_to_non_nullable
as List<Device>,webSessionsListed: null == webSessionsListed ? _self.webSessionsListed : webSessionsListed // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [DeviceList].
extension DeviceListPatterns on DeviceList {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _DeviceList value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _DeviceList() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _DeviceList value)  $default,){
final _that = this;
switch (_that) {
case _DeviceList():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _DeviceList value)?  $default,){
final _that = this;
switch (_that) {
case _DeviceList() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<Device> devices,  bool webSessionsListed)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _DeviceList() when $default != null:
return $default(_that.devices,_that.webSessionsListed);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<Device> devices,  bool webSessionsListed)  $default,) {final _that = this;
switch (_that) {
case _DeviceList():
return $default(_that.devices,_that.webSessionsListed);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<Device> devices,  bool webSessionsListed)?  $default,) {final _that = this;
switch (_that) {
case _DeviceList() when $default != null:
return $default(_that.devices,_that.webSessionsListed);case _:
  return null;

}
}

}

/// @nodoc


class _DeviceList implements DeviceList {
  const _DeviceList({required  List<Device> devices, required this.webSessionsListed}): _devices = devices;
  

 final  List<Device> _devices;
@override List<Device> get devices {
  if (_devices is EqualUnmodifiableListView) return _devices;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_devices);
}

/// False when the server's session driver cannot list browser sessions.
@override final  bool webSessionsListed;

/// Create a copy of DeviceList
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DeviceListCopyWith<_DeviceList> get copyWith => __$DeviceListCopyWithImpl<_DeviceList>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _DeviceList&&const DeepCollectionEquality().equals(other.devices, _devices)&&(identical(other.webSessionsListed, webSessionsListed) || other.webSessionsListed == webSessionsListed));
}


@override
int get hashCode {
    return Object.hash(runtimeType,const DeepCollectionEquality().hash(_devices),webSessionsListed);
}

@override
String toString() {
    return 'DeviceList(devices: $devices, webSessionsListed: $webSessionsListed)';
}


}

/// @nodoc
abstract mixin class _$DeviceListCopyWith<$Res> implements $DeviceListCopyWith<$Res> {
  factory _$DeviceListCopyWith(_DeviceList value, $Res Function(_DeviceList) _then) = __$DeviceListCopyWithImpl;
@override @useResult
$Res call({
 List<Device> devices, bool webSessionsListed
});




}
/// @nodoc
class __$DeviceListCopyWithImpl<$Res>
    implements _$DeviceListCopyWith<$Res> {
  __$DeviceListCopyWithImpl(this._self, this._then);

  final _DeviceList _self;
  final $Res Function(_DeviceList) _then;

/// Create a copy of DeviceList
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? devices = null,Object? webSessionsListed = null,}) {
  return _then(_DeviceList(
devices: null == devices ? _self._devices : devices // ignore: cast_nullable_to_non_nullable
as List<Device>,webSessionsListed: null == webSessionsListed ? _self.webSessionsListed : webSessionsListed // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

// dart format on
