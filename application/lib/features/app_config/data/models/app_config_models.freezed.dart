// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'app_config_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$AppConfigDto {

 Map<String, AppVersionPolicyDto> get versions; MaintenanceDto get maintenance; AppFeaturesDto get features; LocalesDto get locales; CurrenciesDto get currencies; MediaDto get media; LinksDto get links;@JsonKey(fromJson: _gatewaysFromJson) Map<String, Map<String, String?>> get paymentGateways; AppUpdateDto? get update;
/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AppConfigDtoCopyWith<AppConfigDto> get copyWith => _$AppConfigDtoCopyWithImpl<AppConfigDto>(this as AppConfigDto, _$identity);

  /// Serializes this AppConfigDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as AppConfigDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AppConfigDto&&const DeepCollectionEquality().equals(other.versions, _this.versions)&&(identical(other.maintenance, _this.maintenance) || other.maintenance == _this.maintenance)&&(identical(other.features, _this.features) || other.features == _this.features)&&(identical(other.locales, _this.locales) || other.locales == _this.locales)&&(identical(other.currencies, _this.currencies) || other.currencies == _this.currencies)&&(identical(other.media, _this.media) || other.media == _this.media)&&(identical(other.links, _this.links) || other.links == _this.links)&&const DeepCollectionEquality().equals(other.paymentGateways, _this.paymentGateways)&&(identical(other.update, _this.update) || other.update == _this.update));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as AppConfigDto;
  return Object.hash(runtimeType,const DeepCollectionEquality().hash(_this.versions),_this.maintenance,_this.features,_this.locales,_this.currencies,_this.media,_this.links,const DeepCollectionEquality().hash(_this.paymentGateways),_this.update);
}

@override
String toString() {
  final _this = this as AppConfigDto;
  return 'AppConfigDto(versions: ${_this.versions}, maintenance: ${_this.maintenance}, features: ${_this.features}, locales: ${_this.locales}, currencies: ${_this.currencies}, media: ${_this.media}, links: ${_this.links}, paymentGateways: ${_this.paymentGateways}, update: ${_this.update})';
}


}

/// @nodoc
abstract mixin class $AppConfigDtoCopyWith<$Res>  {
  factory $AppConfigDtoCopyWith(AppConfigDto value, $Res Function(AppConfigDto) _then) = _$AppConfigDtoCopyWithImpl;
@useResult
$Res call({
 Map<String, AppVersionPolicyDto> versions, MaintenanceDto maintenance, AppFeaturesDto features, LocalesDto locales, CurrenciesDto currencies, MediaDto media, LinksDto links,@JsonKey(fromJson: _gatewaysFromJson) Map<String, Map<String, String?>> paymentGateways, AppUpdateDto? update
});


$MaintenanceDtoCopyWith<$Res> get maintenance;$AppFeaturesDtoCopyWith<$Res> get features;$LocalesDtoCopyWith<$Res> get locales;$CurrenciesDtoCopyWith<$Res> get currencies;$MediaDtoCopyWith<$Res> get media;$LinksDtoCopyWith<$Res> get links;$AppUpdateDtoCopyWith<$Res>? get update;

}
/// @nodoc
class _$AppConfigDtoCopyWithImpl<$Res>
    implements $AppConfigDtoCopyWith<$Res> {
  _$AppConfigDtoCopyWithImpl(this._self, this._then);

  final AppConfigDto _self;
  final $Res Function(AppConfigDto) _then;

/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? versions = null,Object? maintenance = null,Object? features = null,Object? locales = null,Object? currencies = null,Object? media = null,Object? links = null,Object? paymentGateways = null,Object? update = freezed,}) {
  return _then(AppConfigDto(
versions: null == versions ? _self.versions : versions // ignore: cast_nullable_to_non_nullable
as Map<String, AppVersionPolicyDto>,maintenance: null == maintenance ? _self.maintenance : maintenance // ignore: cast_nullable_to_non_nullable
as MaintenanceDto,features: null == features ? _self.features : features // ignore: cast_nullable_to_non_nullable
as AppFeaturesDto,locales: null == locales ? _self.locales : locales // ignore: cast_nullable_to_non_nullable
as LocalesDto,currencies: null == currencies ? _self.currencies : currencies // ignore: cast_nullable_to_non_nullable
as CurrenciesDto,media: null == media ? _self.media : media // ignore: cast_nullable_to_non_nullable
as MediaDto,links: null == links ? _self.links : links // ignore: cast_nullable_to_non_nullable
as LinksDto,paymentGateways: null == paymentGateways ? _self.paymentGateways : paymentGateways // ignore: cast_nullable_to_non_nullable
as Map<String, Map<String, String?>>,update: freezed == update ? _self.update : update // ignore: cast_nullable_to_non_nullable
as AppUpdateDto?,
  ));
}
/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MaintenanceDtoCopyWith<$Res> get maintenance {
  
  return $MaintenanceDtoCopyWith<$Res>(_self.maintenance, (value) {
    return _then(_self.copyWith(maintenance: value));
  });
}/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$AppFeaturesDtoCopyWith<$Res> get features {
  
  return $AppFeaturesDtoCopyWith<$Res>(_self.features, (value) {
    return _then(_self.copyWith(features: value));
  });
}/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LocalesDtoCopyWith<$Res> get locales {
  
  return $LocalesDtoCopyWith<$Res>(_self.locales, (value) {
    return _then(_self.copyWith(locales: value));
  });
}/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CurrenciesDtoCopyWith<$Res> get currencies {
  
  return $CurrenciesDtoCopyWith<$Res>(_self.currencies, (value) {
    return _then(_self.copyWith(currencies: value));
  });
}/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaDtoCopyWith<$Res> get media {
  
  return $MediaDtoCopyWith<$Res>(_self.media, (value) {
    return _then(_self.copyWith(media: value));
  });
}/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinksDtoCopyWith<$Res> get links {
  
  return $LinksDtoCopyWith<$Res>(_self.links, (value) {
    return _then(_self.copyWith(links: value));
  });
}/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$AppUpdateDtoCopyWith<$Res>? get update {
    if (_self.update == null) {
    return null;
  }

  return $AppUpdateDtoCopyWith<$Res>(_self.update!, (value) {
    return _then(_self.copyWith(update: value));
  });
}
}


/// Adds pattern-matching-related methods to [AppConfigDto].
extension AppConfigDtoPatterns on AppConfigDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AppConfigDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AppConfigDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AppConfigDto value)  $default,){
final _that = this;
switch (_that) {
case _AppConfigDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AppConfigDto value)?  $default,){
final _that = this;
switch (_that) {
case _AppConfigDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( Map<String, AppVersionPolicyDto> versions,  MaintenanceDto maintenance,  AppFeaturesDto features,  LocalesDto locales,  CurrenciesDto currencies,  MediaDto media,  LinksDto links, @JsonKey(fromJson: _gatewaysFromJson)  Map<String, Map<String, String?>> paymentGateways,  AppUpdateDto? update)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _AppConfigDto() when $default != null:
return $default(_that.versions,_that.maintenance,_that.features,_that.locales,_that.currencies,_that.media,_that.links,_that.paymentGateways,_that.update);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( Map<String, AppVersionPolicyDto> versions,  MaintenanceDto maintenance,  AppFeaturesDto features,  LocalesDto locales,  CurrenciesDto currencies,  MediaDto media,  LinksDto links, @JsonKey(fromJson: _gatewaysFromJson)  Map<String, Map<String, String?>> paymentGateways,  AppUpdateDto? update)  $default,) {final _that = this;
switch (_that) {
case _AppConfigDto():
return $default(_that.versions,_that.maintenance,_that.features,_that.locales,_that.currencies,_that.media,_that.links,_that.paymentGateways,_that.update);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( Map<String, AppVersionPolicyDto> versions,  MaintenanceDto maintenance,  AppFeaturesDto features,  LocalesDto locales,  CurrenciesDto currencies,  MediaDto media,  LinksDto links, @JsonKey(fromJson: _gatewaysFromJson)  Map<String, Map<String, String?>> paymentGateways,  AppUpdateDto? update)?  $default,) {final _that = this;
switch (_that) {
case _AppConfigDto() when $default != null:
return $default(_that.versions,_that.maintenance,_that.features,_that.locales,_that.currencies,_that.media,_that.links,_that.paymentGateways,_that.update);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _AppConfigDto extends AppConfigDto {
  const _AppConfigDto({required  Map<String, AppVersionPolicyDto> versions, required this.maintenance, required this.features, required this.locales, required this.currencies, required this.media, required this.links, @JsonKey(fromJson: _gatewaysFromJson) required  Map<String, Map<String, String?>> paymentGateways, this.update}): _versions = versions,_paymentGateways = paymentGateways,super._();
  factory _AppConfigDto.fromJson(Map<String, dynamic> json) => _$AppConfigDtoFromJson(json);

 final  Map<String, AppVersionPolicyDto> _versions;
@override Map<String, AppVersionPolicyDto> get versions {
  if (_versions is EqualUnmodifiableMapView) return _versions;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(_versions);
}

@override final  MaintenanceDto maintenance;
@override final  AppFeaturesDto features;
@override final  LocalesDto locales;
@override final  CurrenciesDto currencies;
@override final  MediaDto media;
@override final  LinksDto links;
 final  Map<String, Map<String, String?>> _paymentGateways;
@override@JsonKey(fromJson: _gatewaysFromJson) Map<String, Map<String, String?>> get paymentGateways {
  if (_paymentGateways is EqualUnmodifiableMapView) return _paymentGateways;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(_paymentGateways);
}

@override final  AppUpdateDto? update;

/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AppConfigDtoCopyWith<_AppConfigDto> get copyWith => __$AppConfigDtoCopyWithImpl<_AppConfigDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$AppConfigDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _AppConfigDto&&const DeepCollectionEquality().equals(other.versions, _versions)&&(identical(other.maintenance, maintenance) || other.maintenance == maintenance)&&(identical(other.features, features) || other.features == features)&&(identical(other.locales, locales) || other.locales == locales)&&(identical(other.currencies, currencies) || other.currencies == currencies)&&(identical(other.media, media) || other.media == media)&&(identical(other.links, links) || other.links == links)&&const DeepCollectionEquality().equals(other.paymentGateways, _paymentGateways)&&(identical(other.update, update) || other.update == update));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,const DeepCollectionEquality().hash(_versions),maintenance,features,locales,currencies,media,links,const DeepCollectionEquality().hash(_paymentGateways),update);
}

@override
String toString() {
    return 'AppConfigDto(versions: $versions, maintenance: $maintenance, features: $features, locales: $locales, currencies: $currencies, media: $media, links: $links, paymentGateways: $paymentGateways, update: $update)';
}


}

/// @nodoc
abstract mixin class _$AppConfigDtoCopyWith<$Res> implements $AppConfigDtoCopyWith<$Res> {
  factory _$AppConfigDtoCopyWith(_AppConfigDto value, $Res Function(_AppConfigDto) _then) = __$AppConfigDtoCopyWithImpl;
@override @useResult
$Res call({
 Map<String, AppVersionPolicyDto> versions, MaintenanceDto maintenance, AppFeaturesDto features, LocalesDto locales, CurrenciesDto currencies, MediaDto media, LinksDto links,@JsonKey(fromJson: _gatewaysFromJson) Map<String, Map<String, String?>> paymentGateways, AppUpdateDto? update
});


@override $MaintenanceDtoCopyWith<$Res> get maintenance;@override $AppFeaturesDtoCopyWith<$Res> get features;@override $LocalesDtoCopyWith<$Res> get locales;@override $CurrenciesDtoCopyWith<$Res> get currencies;@override $MediaDtoCopyWith<$Res> get media;@override $LinksDtoCopyWith<$Res> get links;@override $AppUpdateDtoCopyWith<$Res>? get update;

}
/// @nodoc
class __$AppConfigDtoCopyWithImpl<$Res>
    implements _$AppConfigDtoCopyWith<$Res> {
  __$AppConfigDtoCopyWithImpl(this._self, this._then);

  final _AppConfigDto _self;
  final $Res Function(_AppConfigDto) _then;

/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? versions = null,Object? maintenance = null,Object? features = null,Object? locales = null,Object? currencies = null,Object? media = null,Object? links = null,Object? paymentGateways = null,Object? update = freezed,}) {
  return _then(_AppConfigDto(
versions: null == versions ? _self._versions : versions // ignore: cast_nullable_to_non_nullable
as Map<String, AppVersionPolicyDto>,maintenance: null == maintenance ? _self.maintenance : maintenance // ignore: cast_nullable_to_non_nullable
as MaintenanceDto,features: null == features ? _self.features : features // ignore: cast_nullable_to_non_nullable
as AppFeaturesDto,locales: null == locales ? _self.locales : locales // ignore: cast_nullable_to_non_nullable
as LocalesDto,currencies: null == currencies ? _self.currencies : currencies // ignore: cast_nullable_to_non_nullable
as CurrenciesDto,media: null == media ? _self.media : media // ignore: cast_nullable_to_non_nullable
as MediaDto,links: null == links ? _self.links : links // ignore: cast_nullable_to_non_nullable
as LinksDto,paymentGateways: null == paymentGateways ? _self._paymentGateways : paymentGateways // ignore: cast_nullable_to_non_nullable
as Map<String, Map<String, String?>>,update: freezed == update ? _self.update : update // ignore: cast_nullable_to_non_nullable
as AppUpdateDto?,
  ));
}

/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MaintenanceDtoCopyWith<$Res> get maintenance {
  
  return $MaintenanceDtoCopyWith<$Res>(_self.maintenance, (value) {
    return _then(_self.copyWith(maintenance: value));
  });
}/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$AppFeaturesDtoCopyWith<$Res> get features {
  
  return $AppFeaturesDtoCopyWith<$Res>(_self.features, (value) {
    return _then(_self.copyWith(features: value));
  });
}/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LocalesDtoCopyWith<$Res> get locales {
  
  return $LocalesDtoCopyWith<$Res>(_self.locales, (value) {
    return _then(_self.copyWith(locales: value));
  });
}/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CurrenciesDtoCopyWith<$Res> get currencies {
  
  return $CurrenciesDtoCopyWith<$Res>(_self.currencies, (value) {
    return _then(_self.copyWith(currencies: value));
  });
}/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaDtoCopyWith<$Res> get media {
  
  return $MediaDtoCopyWith<$Res>(_self.media, (value) {
    return _then(_self.copyWith(media: value));
  });
}/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinksDtoCopyWith<$Res> get links {
  
  return $LinksDtoCopyWith<$Res>(_self.links, (value) {
    return _then(_self.copyWith(links: value));
  });
}/// Create a copy of AppConfigDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$AppUpdateDtoCopyWith<$Res>? get update {
    if (_self.update == null) {
    return null;
  }

  return $AppUpdateDtoCopyWith<$Res>(_self.update!, (value) {
    return _then(_self.copyWith(update: value));
  });
}
}


/// @nodoc
mixin _$AppVersionPolicyDto {

 String get minSupportedVersion; String get latestVersion;
/// Create a copy of AppVersionPolicyDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AppVersionPolicyDtoCopyWith<AppVersionPolicyDto> get copyWith => _$AppVersionPolicyDtoCopyWithImpl<AppVersionPolicyDto>(this as AppVersionPolicyDto, _$identity);

  /// Serializes this AppVersionPolicyDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as AppVersionPolicyDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AppVersionPolicyDto&&(identical(other.minSupportedVersion, _this.minSupportedVersion) || other.minSupportedVersion == _this.minSupportedVersion)&&(identical(other.latestVersion, _this.latestVersion) || other.latestVersion == _this.latestVersion));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as AppVersionPolicyDto;
  return Object.hash(runtimeType,_this.minSupportedVersion,_this.latestVersion);
}

@override
String toString() {
  final _this = this as AppVersionPolicyDto;
  return 'AppVersionPolicyDto(minSupportedVersion: ${_this.minSupportedVersion}, latestVersion: ${_this.latestVersion})';
}


}

/// @nodoc
abstract mixin class $AppVersionPolicyDtoCopyWith<$Res>  {
  factory $AppVersionPolicyDtoCopyWith(AppVersionPolicyDto value, $Res Function(AppVersionPolicyDto) _then) = _$AppVersionPolicyDtoCopyWithImpl;
@useResult
$Res call({
 String minSupportedVersion, String latestVersion
});




}
/// @nodoc
class _$AppVersionPolicyDtoCopyWithImpl<$Res>
    implements $AppVersionPolicyDtoCopyWith<$Res> {
  _$AppVersionPolicyDtoCopyWithImpl(this._self, this._then);

  final AppVersionPolicyDto _self;
  final $Res Function(AppVersionPolicyDto) _then;

/// Create a copy of AppVersionPolicyDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? minSupportedVersion = null,Object? latestVersion = null,}) {
  return _then(AppVersionPolicyDto(
minSupportedVersion: null == minSupportedVersion ? _self.minSupportedVersion : minSupportedVersion // ignore: cast_nullable_to_non_nullable
as String,latestVersion: null == latestVersion ? _self.latestVersion : latestVersion // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [AppVersionPolicyDto].
extension AppVersionPolicyDtoPatterns on AppVersionPolicyDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AppVersionPolicyDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AppVersionPolicyDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AppVersionPolicyDto value)  $default,){
final _that = this;
switch (_that) {
case _AppVersionPolicyDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AppVersionPolicyDto value)?  $default,){
final _that = this;
switch (_that) {
case _AppVersionPolicyDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String minSupportedVersion,  String latestVersion)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _AppVersionPolicyDto() when $default != null:
return $default(_that.minSupportedVersion,_that.latestVersion);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String minSupportedVersion,  String latestVersion)  $default,) {final _that = this;
switch (_that) {
case _AppVersionPolicyDto():
return $default(_that.minSupportedVersion,_that.latestVersion);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String minSupportedVersion,  String latestVersion)?  $default,) {final _that = this;
switch (_that) {
case _AppVersionPolicyDto() when $default != null:
return $default(_that.minSupportedVersion,_that.latestVersion);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _AppVersionPolicyDto extends AppVersionPolicyDto {
  const _AppVersionPolicyDto({required this.minSupportedVersion, required this.latestVersion}): super._();
  factory _AppVersionPolicyDto.fromJson(Map<String, dynamic> json) => _$AppVersionPolicyDtoFromJson(json);

@override final  String minSupportedVersion;
@override final  String latestVersion;

/// Create a copy of AppVersionPolicyDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AppVersionPolicyDtoCopyWith<_AppVersionPolicyDto> get copyWith => __$AppVersionPolicyDtoCopyWithImpl<_AppVersionPolicyDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$AppVersionPolicyDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _AppVersionPolicyDto&&(identical(other.minSupportedVersion, minSupportedVersion) || other.minSupportedVersion == minSupportedVersion)&&(identical(other.latestVersion, latestVersion) || other.latestVersion == latestVersion));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,minSupportedVersion,latestVersion);
}

@override
String toString() {
    return 'AppVersionPolicyDto(minSupportedVersion: $minSupportedVersion, latestVersion: $latestVersion)';
}


}

/// @nodoc
abstract mixin class _$AppVersionPolicyDtoCopyWith<$Res> implements $AppVersionPolicyDtoCopyWith<$Res> {
  factory _$AppVersionPolicyDtoCopyWith(_AppVersionPolicyDto value, $Res Function(_AppVersionPolicyDto) _then) = __$AppVersionPolicyDtoCopyWithImpl;
@override @useResult
$Res call({
 String minSupportedVersion, String latestVersion
});




}
/// @nodoc
class __$AppVersionPolicyDtoCopyWithImpl<$Res>
    implements _$AppVersionPolicyDtoCopyWith<$Res> {
  __$AppVersionPolicyDtoCopyWithImpl(this._self, this._then);

  final _AppVersionPolicyDto _self;
  final $Res Function(_AppVersionPolicyDto) _then;

/// Create a copy of AppVersionPolicyDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? minSupportedVersion = null,Object? latestVersion = null,}) {
  return _then(_AppVersionPolicyDto(
minSupportedVersion: null == minSupportedVersion ? _self.minSupportedVersion : minSupportedVersion // ignore: cast_nullable_to_non_nullable
as String,latestVersion: null == latestVersion ? _self.latestVersion : latestVersion // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}


/// @nodoc
mixin _$AppUpdateDto {

 String get platform; String get version; bool get required; bool get available;
/// Create a copy of AppUpdateDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AppUpdateDtoCopyWith<AppUpdateDto> get copyWith => _$AppUpdateDtoCopyWithImpl<AppUpdateDto>(this as AppUpdateDto, _$identity);

  /// Serializes this AppUpdateDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as AppUpdateDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AppUpdateDto&&(identical(other.platform, _this.platform) || other.platform == _this.platform)&&(identical(other.version, _this.version) || other.version == _this.version)&&(identical(other.required, _this.required) || other.required == _this.required)&&(identical(other.available, _this.available) || other.available == _this.available));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as AppUpdateDto;
  return Object.hash(runtimeType,_this.platform,_this.version,_this.required,_this.available);
}

@override
String toString() {
  final _this = this as AppUpdateDto;
  return 'AppUpdateDto(platform: ${_this.platform}, version: ${_this.version}, required: ${_this.required}, available: ${_this.available})';
}


}

/// @nodoc
abstract mixin class $AppUpdateDtoCopyWith<$Res>  {
  factory $AppUpdateDtoCopyWith(AppUpdateDto value, $Res Function(AppUpdateDto) _then) = _$AppUpdateDtoCopyWithImpl;
@useResult
$Res call({
 String platform, String version, bool required, bool available
});




}
/// @nodoc
class _$AppUpdateDtoCopyWithImpl<$Res>
    implements $AppUpdateDtoCopyWith<$Res> {
  _$AppUpdateDtoCopyWithImpl(this._self, this._then);

  final AppUpdateDto _self;
  final $Res Function(AppUpdateDto) _then;

/// Create a copy of AppUpdateDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? platform = null,Object? version = null,Object? required = null,Object? available = null,}) {
  return _then(AppUpdateDto(
platform: null == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as String,version: null == version ? _self.version : version // ignore: cast_nullable_to_non_nullable
as String,required: null == required ? _self.required : required // ignore: cast_nullable_to_non_nullable
as bool,available: null == available ? _self.available : available // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [AppUpdateDto].
extension AppUpdateDtoPatterns on AppUpdateDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AppUpdateDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AppUpdateDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AppUpdateDto value)  $default,){
final _that = this;
switch (_that) {
case _AppUpdateDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AppUpdateDto value)?  $default,){
final _that = this;
switch (_that) {
case _AppUpdateDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String platform,  String version,  bool required,  bool available)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _AppUpdateDto() when $default != null:
return $default(_that.platform,_that.version,_that.required,_that.available);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String platform,  String version,  bool required,  bool available)  $default,) {final _that = this;
switch (_that) {
case _AppUpdateDto():
return $default(_that.platform,_that.version,_that.required,_that.available);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String platform,  String version,  bool required,  bool available)?  $default,) {final _that = this;
switch (_that) {
case _AppUpdateDto() when $default != null:
return $default(_that.platform,_that.version,_that.required,_that.available);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _AppUpdateDto extends AppUpdateDto {
  const _AppUpdateDto({required this.platform, required this.version, required this.required, required this.available}): super._();
  factory _AppUpdateDto.fromJson(Map<String, dynamic> json) => _$AppUpdateDtoFromJson(json);

@override final  String platform;
@override final  String version;
@override final  bool required;
@override final  bool available;

/// Create a copy of AppUpdateDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AppUpdateDtoCopyWith<_AppUpdateDto> get copyWith => __$AppUpdateDtoCopyWithImpl<_AppUpdateDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$AppUpdateDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _AppUpdateDto&&(identical(other.platform, platform) || other.platform == platform)&&(identical(other.version, version) || other.version == version)&&(identical(other.required, required) || other.required == required)&&(identical(other.available, available) || other.available == available));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,platform,version,required,available);
}

@override
String toString() {
    return 'AppUpdateDto(platform: $platform, version: $version, required: $required, available: $available)';
}


}

/// @nodoc
abstract mixin class _$AppUpdateDtoCopyWith<$Res> implements $AppUpdateDtoCopyWith<$Res> {
  factory _$AppUpdateDtoCopyWith(_AppUpdateDto value, $Res Function(_AppUpdateDto) _then) = __$AppUpdateDtoCopyWithImpl;
@override @useResult
$Res call({
 String platform, String version, bool required, bool available
});




}
/// @nodoc
class __$AppUpdateDtoCopyWithImpl<$Res>
    implements _$AppUpdateDtoCopyWith<$Res> {
  __$AppUpdateDtoCopyWithImpl(this._self, this._then);

  final _AppUpdateDto _self;
  final $Res Function(_AppUpdateDto) _then;

/// Create a copy of AppUpdateDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? platform = null,Object? version = null,Object? required = null,Object? available = null,}) {
  return _then(_AppUpdateDto(
platform: null == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as String,version: null == version ? _self.version : version // ignore: cast_nullable_to_non_nullable
as String,required: null == required ? _self.required : required // ignore: cast_nullable_to_non_nullable
as bool,available: null == available ? _self.available : available // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}


/// @nodoc
mixin _$MaintenanceDto {

 bool get enabled; String? get message;
/// Create a copy of MaintenanceDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MaintenanceDtoCopyWith<MaintenanceDto> get copyWith => _$MaintenanceDtoCopyWithImpl<MaintenanceDto>(this as MaintenanceDto, _$identity);

  /// Serializes this MaintenanceDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as MaintenanceDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MaintenanceDto&&(identical(other.enabled, _this.enabled) || other.enabled == _this.enabled)&&(identical(other.message, _this.message) || other.message == _this.message));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as MaintenanceDto;
  return Object.hash(runtimeType,_this.enabled,_this.message);
}

@override
String toString() {
  final _this = this as MaintenanceDto;
  return 'MaintenanceDto(enabled: ${_this.enabled}, message: ${_this.message})';
}


}

/// @nodoc
abstract mixin class $MaintenanceDtoCopyWith<$Res>  {
  factory $MaintenanceDtoCopyWith(MaintenanceDto value, $Res Function(MaintenanceDto) _then) = _$MaintenanceDtoCopyWithImpl;
@useResult
$Res call({
 bool enabled, String? message
});




}
/// @nodoc
class _$MaintenanceDtoCopyWithImpl<$Res>
    implements $MaintenanceDtoCopyWith<$Res> {
  _$MaintenanceDtoCopyWithImpl(this._self, this._then);

  final MaintenanceDto _self;
  final $Res Function(MaintenanceDto) _then;

/// Create a copy of MaintenanceDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? enabled = null,Object? message = freezed,}) {
  return _then(MaintenanceDto(
enabled: null == enabled ? _self.enabled : enabled // ignore: cast_nullable_to_non_nullable
as bool,message: freezed == message ? _self.message : message // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [MaintenanceDto].
extension MaintenanceDtoPatterns on MaintenanceDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _MaintenanceDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _MaintenanceDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _MaintenanceDto value)  $default,){
final _that = this;
switch (_that) {
case _MaintenanceDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _MaintenanceDto value)?  $default,){
final _that = this;
switch (_that) {
case _MaintenanceDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool enabled,  String? message)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _MaintenanceDto() when $default != null:
return $default(_that.enabled,_that.message);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool enabled,  String? message)  $default,) {final _that = this;
switch (_that) {
case _MaintenanceDto():
return $default(_that.enabled,_that.message);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool enabled,  String? message)?  $default,) {final _that = this;
switch (_that) {
case _MaintenanceDto() when $default != null:
return $default(_that.enabled,_that.message);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _MaintenanceDto implements MaintenanceDto {
  const _MaintenanceDto({required this.enabled, this.message});
  factory _MaintenanceDto.fromJson(Map<String, dynamic> json) => _$MaintenanceDtoFromJson(json);

@override final  bool enabled;
@override final  String? message;

/// Create a copy of MaintenanceDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$MaintenanceDtoCopyWith<_MaintenanceDto> get copyWith => __$MaintenanceDtoCopyWithImpl<_MaintenanceDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$MaintenanceDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _MaintenanceDto&&(identical(other.enabled, enabled) || other.enabled == enabled)&&(identical(other.message, message) || other.message == message));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,enabled,message);
}

@override
String toString() {
    return 'MaintenanceDto(enabled: $enabled, message: $message)';
}


}

/// @nodoc
abstract mixin class _$MaintenanceDtoCopyWith<$Res> implements $MaintenanceDtoCopyWith<$Res> {
  factory _$MaintenanceDtoCopyWith(_MaintenanceDto value, $Res Function(_MaintenanceDto) _then) = __$MaintenanceDtoCopyWithImpl;
@override @useResult
$Res call({
 bool enabled, String? message
});




}
/// @nodoc
class __$MaintenanceDtoCopyWithImpl<$Res>
    implements _$MaintenanceDtoCopyWith<$Res> {
  __$MaintenanceDtoCopyWithImpl(this._self, this._then);

  final _MaintenanceDto _self;
  final $Res Function(_MaintenanceDto) _then;

/// Create a copy of MaintenanceDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? enabled = null,Object? message = freezed,}) {
  return _then(_MaintenanceDto(
enabled: null == enabled ? _self.enabled : enabled // ignore: cast_nullable_to_non_nullable
as bool,message: freezed == message ? _self.message : message // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$AppFeaturesDto {

 bool get checkoutEnabled; bool get guestCheckoutEnabled; bool get walletEnabled; bool get reviewsEnabled; bool get supportEnabled; bool get webCheckoutRedirect;
/// Create a copy of AppFeaturesDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AppFeaturesDtoCopyWith<AppFeaturesDto> get copyWith => _$AppFeaturesDtoCopyWithImpl<AppFeaturesDto>(this as AppFeaturesDto, _$identity);

  /// Serializes this AppFeaturesDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as AppFeaturesDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AppFeaturesDto&&(identical(other.checkoutEnabled, _this.checkoutEnabled) || other.checkoutEnabled == _this.checkoutEnabled)&&(identical(other.guestCheckoutEnabled, _this.guestCheckoutEnabled) || other.guestCheckoutEnabled == _this.guestCheckoutEnabled)&&(identical(other.walletEnabled, _this.walletEnabled) || other.walletEnabled == _this.walletEnabled)&&(identical(other.reviewsEnabled, _this.reviewsEnabled) || other.reviewsEnabled == _this.reviewsEnabled)&&(identical(other.supportEnabled, _this.supportEnabled) || other.supportEnabled == _this.supportEnabled)&&(identical(other.webCheckoutRedirect, _this.webCheckoutRedirect) || other.webCheckoutRedirect == _this.webCheckoutRedirect));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as AppFeaturesDto;
  return Object.hash(runtimeType,_this.checkoutEnabled,_this.guestCheckoutEnabled,_this.walletEnabled,_this.reviewsEnabled,_this.supportEnabled,_this.webCheckoutRedirect);
}

@override
String toString() {
  final _this = this as AppFeaturesDto;
  return 'AppFeaturesDto(checkoutEnabled: ${_this.checkoutEnabled}, guestCheckoutEnabled: ${_this.guestCheckoutEnabled}, walletEnabled: ${_this.walletEnabled}, reviewsEnabled: ${_this.reviewsEnabled}, supportEnabled: ${_this.supportEnabled}, webCheckoutRedirect: ${_this.webCheckoutRedirect})';
}


}

/// @nodoc
abstract mixin class $AppFeaturesDtoCopyWith<$Res>  {
  factory $AppFeaturesDtoCopyWith(AppFeaturesDto value, $Res Function(AppFeaturesDto) _then) = _$AppFeaturesDtoCopyWithImpl;
@useResult
$Res call({
 bool checkoutEnabled, bool guestCheckoutEnabled, bool walletEnabled, bool reviewsEnabled, bool supportEnabled, bool webCheckoutRedirect
});




}
/// @nodoc
class _$AppFeaturesDtoCopyWithImpl<$Res>
    implements $AppFeaturesDtoCopyWith<$Res> {
  _$AppFeaturesDtoCopyWithImpl(this._self, this._then);

  final AppFeaturesDto _self;
  final $Res Function(AppFeaturesDto) _then;

/// Create a copy of AppFeaturesDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? checkoutEnabled = null,Object? guestCheckoutEnabled = null,Object? walletEnabled = null,Object? reviewsEnabled = null,Object? supportEnabled = null,Object? webCheckoutRedirect = null,}) {
  return _then(AppFeaturesDto(
checkoutEnabled: null == checkoutEnabled ? _self.checkoutEnabled : checkoutEnabled // ignore: cast_nullable_to_non_nullable
as bool,guestCheckoutEnabled: null == guestCheckoutEnabled ? _self.guestCheckoutEnabled : guestCheckoutEnabled // ignore: cast_nullable_to_non_nullable
as bool,walletEnabled: null == walletEnabled ? _self.walletEnabled : walletEnabled // ignore: cast_nullable_to_non_nullable
as bool,reviewsEnabled: null == reviewsEnabled ? _self.reviewsEnabled : reviewsEnabled // ignore: cast_nullable_to_non_nullable
as bool,supportEnabled: null == supportEnabled ? _self.supportEnabled : supportEnabled // ignore: cast_nullable_to_non_nullable
as bool,webCheckoutRedirect: null == webCheckoutRedirect ? _self.webCheckoutRedirect : webCheckoutRedirect // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [AppFeaturesDto].
extension AppFeaturesDtoPatterns on AppFeaturesDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AppFeaturesDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AppFeaturesDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AppFeaturesDto value)  $default,){
final _that = this;
switch (_that) {
case _AppFeaturesDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AppFeaturesDto value)?  $default,){
final _that = this;
switch (_that) {
case _AppFeaturesDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool checkoutEnabled,  bool guestCheckoutEnabled,  bool walletEnabled,  bool reviewsEnabled,  bool supportEnabled,  bool webCheckoutRedirect)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _AppFeaturesDto() when $default != null:
return $default(_that.checkoutEnabled,_that.guestCheckoutEnabled,_that.walletEnabled,_that.reviewsEnabled,_that.supportEnabled,_that.webCheckoutRedirect);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool checkoutEnabled,  bool guestCheckoutEnabled,  bool walletEnabled,  bool reviewsEnabled,  bool supportEnabled,  bool webCheckoutRedirect)  $default,) {final _that = this;
switch (_that) {
case _AppFeaturesDto():
return $default(_that.checkoutEnabled,_that.guestCheckoutEnabled,_that.walletEnabled,_that.reviewsEnabled,_that.supportEnabled,_that.webCheckoutRedirect);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool checkoutEnabled,  bool guestCheckoutEnabled,  bool walletEnabled,  bool reviewsEnabled,  bool supportEnabled,  bool webCheckoutRedirect)?  $default,) {final _that = this;
switch (_that) {
case _AppFeaturesDto() when $default != null:
return $default(_that.checkoutEnabled,_that.guestCheckoutEnabled,_that.walletEnabled,_that.reviewsEnabled,_that.supportEnabled,_that.webCheckoutRedirect);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _AppFeaturesDto extends AppFeaturesDto {
  const _AppFeaturesDto({required this.checkoutEnabled, required this.guestCheckoutEnabled, required this.walletEnabled, required this.reviewsEnabled, required this.supportEnabled, this.webCheckoutRedirect = false}): super._();
  factory _AppFeaturesDto.fromJson(Map<String, dynamic> json) => _$AppFeaturesDtoFromJson(json);

@override final  bool checkoutEnabled;
@override final  bool guestCheckoutEnabled;
@override final  bool walletEnabled;
@override final  bool reviewsEnabled;
@override final  bool supportEnabled;
@override@JsonKey() final  bool webCheckoutRedirect;

/// Create a copy of AppFeaturesDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AppFeaturesDtoCopyWith<_AppFeaturesDto> get copyWith => __$AppFeaturesDtoCopyWithImpl<_AppFeaturesDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$AppFeaturesDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _AppFeaturesDto&&(identical(other.checkoutEnabled, checkoutEnabled) || other.checkoutEnabled == checkoutEnabled)&&(identical(other.guestCheckoutEnabled, guestCheckoutEnabled) || other.guestCheckoutEnabled == guestCheckoutEnabled)&&(identical(other.walletEnabled, walletEnabled) || other.walletEnabled == walletEnabled)&&(identical(other.reviewsEnabled, reviewsEnabled) || other.reviewsEnabled == reviewsEnabled)&&(identical(other.supportEnabled, supportEnabled) || other.supportEnabled == supportEnabled)&&(identical(other.webCheckoutRedirect, webCheckoutRedirect) || other.webCheckoutRedirect == webCheckoutRedirect));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,checkoutEnabled,guestCheckoutEnabled,walletEnabled,reviewsEnabled,supportEnabled,webCheckoutRedirect);
}

@override
String toString() {
    return 'AppFeaturesDto(checkoutEnabled: $checkoutEnabled, guestCheckoutEnabled: $guestCheckoutEnabled, walletEnabled: $walletEnabled, reviewsEnabled: $reviewsEnabled, supportEnabled: $supportEnabled, webCheckoutRedirect: $webCheckoutRedirect)';
}


}

/// @nodoc
abstract mixin class _$AppFeaturesDtoCopyWith<$Res> implements $AppFeaturesDtoCopyWith<$Res> {
  factory _$AppFeaturesDtoCopyWith(_AppFeaturesDto value, $Res Function(_AppFeaturesDto) _then) = __$AppFeaturesDtoCopyWithImpl;
@override @useResult
$Res call({
 bool checkoutEnabled, bool guestCheckoutEnabled, bool walletEnabled, bool reviewsEnabled, bool supportEnabled, bool webCheckoutRedirect
});




}
/// @nodoc
class __$AppFeaturesDtoCopyWithImpl<$Res>
    implements _$AppFeaturesDtoCopyWith<$Res> {
  __$AppFeaturesDtoCopyWithImpl(this._self, this._then);

  final _AppFeaturesDto _self;
  final $Res Function(_AppFeaturesDto) _then;

/// Create a copy of AppFeaturesDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? checkoutEnabled = null,Object? guestCheckoutEnabled = null,Object? walletEnabled = null,Object? reviewsEnabled = null,Object? supportEnabled = null,Object? webCheckoutRedirect = null,}) {
  return _then(_AppFeaturesDto(
checkoutEnabled: null == checkoutEnabled ? _self.checkoutEnabled : checkoutEnabled // ignore: cast_nullable_to_non_nullable
as bool,guestCheckoutEnabled: null == guestCheckoutEnabled ? _self.guestCheckoutEnabled : guestCheckoutEnabled // ignore: cast_nullable_to_non_nullable
as bool,walletEnabled: null == walletEnabled ? _self.walletEnabled : walletEnabled // ignore: cast_nullable_to_non_nullable
as bool,reviewsEnabled: null == reviewsEnabled ? _self.reviewsEnabled : reviewsEnabled // ignore: cast_nullable_to_non_nullable
as bool,supportEnabled: null == supportEnabled ? _self.supportEnabled : supportEnabled // ignore: cast_nullable_to_non_nullable
as bool,webCheckoutRedirect: null == webCheckoutRedirect ? _self.webCheckoutRedirect : webCheckoutRedirect // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}


/// @nodoc
mixin _$LocalesDto {

 List<String> get supported;@JsonKey(name: 'default') String get defaultLocale;
/// Create a copy of LocalesDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$LocalesDtoCopyWith<LocalesDto> get copyWith => _$LocalesDtoCopyWithImpl<LocalesDto>(this as LocalesDto, _$identity);

  /// Serializes this LocalesDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as LocalesDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is LocalesDto&&const DeepCollectionEquality().equals(other.supported, _this.supported)&&(identical(other.defaultLocale, _this.defaultLocale) || other.defaultLocale == _this.defaultLocale));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as LocalesDto;
  return Object.hash(runtimeType,const DeepCollectionEquality().hash(_this.supported),_this.defaultLocale);
}

@override
String toString() {
  final _this = this as LocalesDto;
  return 'LocalesDto(supported: ${_this.supported}, defaultLocale: ${_this.defaultLocale})';
}


}

/// @nodoc
abstract mixin class $LocalesDtoCopyWith<$Res>  {
  factory $LocalesDtoCopyWith(LocalesDto value, $Res Function(LocalesDto) _then) = _$LocalesDtoCopyWithImpl;
@useResult
$Res call({
 List<String> supported,@JsonKey(name: 'default') String defaultLocale
});




}
/// @nodoc
class _$LocalesDtoCopyWithImpl<$Res>
    implements $LocalesDtoCopyWith<$Res> {
  _$LocalesDtoCopyWithImpl(this._self, this._then);

  final LocalesDto _self;
  final $Res Function(LocalesDto) _then;

/// Create a copy of LocalesDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? supported = null,Object? defaultLocale = null,}) {
  return _then(LocalesDto(
supported: null == supported ? _self.supported : supported // ignore: cast_nullable_to_non_nullable
as List<String>,defaultLocale: null == defaultLocale ? _self.defaultLocale : defaultLocale // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [LocalesDto].
extension LocalesDtoPatterns on LocalesDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _LocalesDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _LocalesDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _LocalesDto value)  $default,){
final _that = this;
switch (_that) {
case _LocalesDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _LocalesDto value)?  $default,){
final _that = this;
switch (_that) {
case _LocalesDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<String> supported, @JsonKey(name: 'default')  String defaultLocale)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _LocalesDto() when $default != null:
return $default(_that.supported,_that.defaultLocale);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<String> supported, @JsonKey(name: 'default')  String defaultLocale)  $default,) {final _that = this;
switch (_that) {
case _LocalesDto():
return $default(_that.supported,_that.defaultLocale);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<String> supported, @JsonKey(name: 'default')  String defaultLocale)?  $default,) {final _that = this;
switch (_that) {
case _LocalesDto() when $default != null:
return $default(_that.supported,_that.defaultLocale);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _LocalesDto implements LocalesDto {
  const _LocalesDto({required  List<String> supported, @JsonKey(name: 'default') required this.defaultLocale}): _supported = supported;
  factory _LocalesDto.fromJson(Map<String, dynamic> json) => _$LocalesDtoFromJson(json);

 final  List<String> _supported;
@override List<String> get supported {
  if (_supported is EqualUnmodifiableListView) return _supported;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_supported);
}

@override@JsonKey(name: 'default') final  String defaultLocale;

/// Create a copy of LocalesDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$LocalesDtoCopyWith<_LocalesDto> get copyWith => __$LocalesDtoCopyWithImpl<_LocalesDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$LocalesDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _LocalesDto&&const DeepCollectionEquality().equals(other.supported, _supported)&&(identical(other.defaultLocale, defaultLocale) || other.defaultLocale == defaultLocale));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,const DeepCollectionEquality().hash(_supported),defaultLocale);
}

@override
String toString() {
    return 'LocalesDto(supported: $supported, defaultLocale: $defaultLocale)';
}


}

/// @nodoc
abstract mixin class _$LocalesDtoCopyWith<$Res> implements $LocalesDtoCopyWith<$Res> {
  factory _$LocalesDtoCopyWith(_LocalesDto value, $Res Function(_LocalesDto) _then) = __$LocalesDtoCopyWithImpl;
@override @useResult
$Res call({
 List<String> supported,@JsonKey(name: 'default') String defaultLocale
});




}
/// @nodoc
class __$LocalesDtoCopyWithImpl<$Res>
    implements _$LocalesDtoCopyWith<$Res> {
  __$LocalesDtoCopyWithImpl(this._self, this._then);

  final _LocalesDto _self;
  final $Res Function(_LocalesDto) _then;

/// Create a copy of LocalesDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? supported = null,Object? defaultLocale = null,}) {
  return _then(_LocalesDto(
supported: null == supported ? _self._supported : supported // ignore: cast_nullable_to_non_nullable
as List<String>,defaultLocale: null == defaultLocale ? _self.defaultLocale : defaultLocale // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}


/// @nodoc
mixin _$CurrencyDto {

 String get code; String get name; String get symbol; int get exponent;
/// Create a copy of CurrencyDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CurrencyDtoCopyWith<CurrencyDto> get copyWith => _$CurrencyDtoCopyWithImpl<CurrencyDto>(this as CurrencyDto, _$identity);

  /// Serializes this CurrencyDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as CurrencyDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CurrencyDto&&(identical(other.code, _this.code) || other.code == _this.code)&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.symbol, _this.symbol) || other.symbol == _this.symbol)&&(identical(other.exponent, _this.exponent) || other.exponent == _this.exponent));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as CurrencyDto;
  return Object.hash(runtimeType,_this.code,_this.name,_this.symbol,_this.exponent);
}

@override
String toString() {
  final _this = this as CurrencyDto;
  return 'CurrencyDto(code: ${_this.code}, name: ${_this.name}, symbol: ${_this.symbol}, exponent: ${_this.exponent})';
}


}

/// @nodoc
abstract mixin class $CurrencyDtoCopyWith<$Res>  {
  factory $CurrencyDtoCopyWith(CurrencyDto value, $Res Function(CurrencyDto) _then) = _$CurrencyDtoCopyWithImpl;
@useResult
$Res call({
 String code, String name, String symbol, int exponent
});




}
/// @nodoc
class _$CurrencyDtoCopyWithImpl<$Res>
    implements $CurrencyDtoCopyWith<$Res> {
  _$CurrencyDtoCopyWithImpl(this._self, this._then);

  final CurrencyDto _self;
  final $Res Function(CurrencyDto) _then;

/// Create a copy of CurrencyDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? code = null,Object? name = null,Object? symbol = null,Object? exponent = null,}) {
  return _then(CurrencyDto(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,symbol: null == symbol ? _self.symbol : symbol // ignore: cast_nullable_to_non_nullable
as String,exponent: null == exponent ? _self.exponent : exponent // ignore: cast_nullable_to_non_nullable
as int,
  ));
}

}


/// Adds pattern-matching-related methods to [CurrencyDto].
extension CurrencyDtoPatterns on CurrencyDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CurrencyDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CurrencyDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CurrencyDto value)  $default,){
final _that = this;
switch (_that) {
case _CurrencyDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CurrencyDto value)?  $default,){
final _that = this;
switch (_that) {
case _CurrencyDto() when $default != null:
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
case _CurrencyDto() when $default != null:
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
case _CurrencyDto():
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
case _CurrencyDto() when $default != null:
return $default(_that.code,_that.name,_that.symbol,_that.exponent);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CurrencyDto implements CurrencyDto {
  const _CurrencyDto({required this.code, required this.name, required this.symbol, required this.exponent});
  factory _CurrencyDto.fromJson(Map<String, dynamic> json) => _$CurrencyDtoFromJson(json);

@override final  String code;
@override final  String name;
@override final  String symbol;
@override final  int exponent;

/// Create a copy of CurrencyDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CurrencyDtoCopyWith<_CurrencyDto> get copyWith => __$CurrencyDtoCopyWithImpl<_CurrencyDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CurrencyDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CurrencyDto&&(identical(other.code, code) || other.code == code)&&(identical(other.name, name) || other.name == name)&&(identical(other.symbol, symbol) || other.symbol == symbol)&&(identical(other.exponent, exponent) || other.exponent == exponent));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,code,name,symbol,exponent);
}

@override
String toString() {
    return 'CurrencyDto(code: $code, name: $name, symbol: $symbol, exponent: $exponent)';
}


}

/// @nodoc
abstract mixin class _$CurrencyDtoCopyWith<$Res> implements $CurrencyDtoCopyWith<$Res> {
  factory _$CurrencyDtoCopyWith(_CurrencyDto value, $Res Function(_CurrencyDto) _then) = __$CurrencyDtoCopyWithImpl;
@override @useResult
$Res call({
 String code, String name, String symbol, int exponent
});




}
/// @nodoc
class __$CurrencyDtoCopyWithImpl<$Res>
    implements _$CurrencyDtoCopyWith<$Res> {
  __$CurrencyDtoCopyWithImpl(this._self, this._then);

  final _CurrencyDto _self;
  final $Res Function(_CurrencyDto) _then;

/// Create a copy of CurrencyDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? code = null,Object? name = null,Object? symbol = null,Object? exponent = null,}) {
  return _then(_CurrencyDto(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,symbol: null == symbol ? _self.symbol : symbol // ignore: cast_nullable_to_non_nullable
as String,exponent: null == exponent ? _self.exponent : exponent // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}


/// @nodoc
mixin _$CurrenciesDto {

 String get base; List<CurrencyDto> get supported;
/// Create a copy of CurrenciesDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CurrenciesDtoCopyWith<CurrenciesDto> get copyWith => _$CurrenciesDtoCopyWithImpl<CurrenciesDto>(this as CurrenciesDto, _$identity);

  /// Serializes this CurrenciesDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as CurrenciesDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CurrenciesDto&&(identical(other.base, _this.base) || other.base == _this.base)&&const DeepCollectionEquality().equals(other.supported, _this.supported));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as CurrenciesDto;
  return Object.hash(runtimeType,_this.base,const DeepCollectionEquality().hash(_this.supported));
}

@override
String toString() {
  final _this = this as CurrenciesDto;
  return 'CurrenciesDto(base: ${_this.base}, supported: ${_this.supported})';
}


}

/// @nodoc
abstract mixin class $CurrenciesDtoCopyWith<$Res>  {
  factory $CurrenciesDtoCopyWith(CurrenciesDto value, $Res Function(CurrenciesDto) _then) = _$CurrenciesDtoCopyWithImpl;
@useResult
$Res call({
 String base, List<CurrencyDto> supported
});




}
/// @nodoc
class _$CurrenciesDtoCopyWithImpl<$Res>
    implements $CurrenciesDtoCopyWith<$Res> {
  _$CurrenciesDtoCopyWithImpl(this._self, this._then);

  final CurrenciesDto _self;
  final $Res Function(CurrenciesDto) _then;

/// Create a copy of CurrenciesDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? base = null,Object? supported = null,}) {
  return _then(CurrenciesDto(
base: null == base ? _self.base : base // ignore: cast_nullable_to_non_nullable
as String,supported: null == supported ? _self.supported : supported // ignore: cast_nullable_to_non_nullable
as List<CurrencyDto>,
  ));
}

}


/// Adds pattern-matching-related methods to [CurrenciesDto].
extension CurrenciesDtoPatterns on CurrenciesDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CurrenciesDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CurrenciesDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CurrenciesDto value)  $default,){
final _that = this;
switch (_that) {
case _CurrenciesDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CurrenciesDto value)?  $default,){
final _that = this;
switch (_that) {
case _CurrenciesDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String base,  List<CurrencyDto> supported)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CurrenciesDto() when $default != null:
return $default(_that.base,_that.supported);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String base,  List<CurrencyDto> supported)  $default,) {final _that = this;
switch (_that) {
case _CurrenciesDto():
return $default(_that.base,_that.supported);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String base,  List<CurrencyDto> supported)?  $default,) {final _that = this;
switch (_that) {
case _CurrenciesDto() when $default != null:
return $default(_that.base,_that.supported);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CurrenciesDto implements CurrenciesDto {
  const _CurrenciesDto({required this.base, required  List<CurrencyDto> supported}): _supported = supported;
  factory _CurrenciesDto.fromJson(Map<String, dynamic> json) => _$CurrenciesDtoFromJson(json);

@override final  String base;
 final  List<CurrencyDto> _supported;
@override List<CurrencyDto> get supported {
  if (_supported is EqualUnmodifiableListView) return _supported;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_supported);
}


/// Create a copy of CurrenciesDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CurrenciesDtoCopyWith<_CurrenciesDto> get copyWith => __$CurrenciesDtoCopyWithImpl<_CurrenciesDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CurrenciesDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CurrenciesDto&&(identical(other.base, base) || other.base == base)&&const DeepCollectionEquality().equals(other.supported, _supported));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,base,const DeepCollectionEquality().hash(_supported));
}

@override
String toString() {
    return 'CurrenciesDto(base: $base, supported: $supported)';
}


}

/// @nodoc
abstract mixin class _$CurrenciesDtoCopyWith<$Res> implements $CurrenciesDtoCopyWith<$Res> {
  factory _$CurrenciesDtoCopyWith(_CurrenciesDto value, $Res Function(_CurrenciesDto) _then) = __$CurrenciesDtoCopyWithImpl;
@override @useResult
$Res call({
 String base, List<CurrencyDto> supported
});




}
/// @nodoc
class __$CurrenciesDtoCopyWithImpl<$Res>
    implements _$CurrenciesDtoCopyWith<$Res> {
  __$CurrenciesDtoCopyWithImpl(this._self, this._then);

  final _CurrenciesDto _self;
  final $Res Function(_CurrenciesDto) _then;

/// Create a copy of CurrenciesDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? base = null,Object? supported = null,}) {
  return _then(_CurrenciesDto(
base: null == base ? _self.base : base // ignore: cast_nullable_to_non_nullable
as String,supported: null == supported ? _self._supported : supported // ignore: cast_nullable_to_non_nullable
as List<CurrencyDto>,
  ));
}


}


/// @nodoc
mixin _$MediaDto {

 String get baseUrl;
/// Create a copy of MediaDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MediaDtoCopyWith<MediaDto> get copyWith => _$MediaDtoCopyWithImpl<MediaDto>(this as MediaDto, _$identity);

  /// Serializes this MediaDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as MediaDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MediaDto&&(identical(other.baseUrl, _this.baseUrl) || other.baseUrl == _this.baseUrl));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as MediaDto;
  return Object.hash(runtimeType,_this.baseUrl);
}

@override
String toString() {
  final _this = this as MediaDto;
  return 'MediaDto(baseUrl: ${_this.baseUrl})';
}


}

/// @nodoc
abstract mixin class $MediaDtoCopyWith<$Res>  {
  factory $MediaDtoCopyWith(MediaDto value, $Res Function(MediaDto) _then) = _$MediaDtoCopyWithImpl;
@useResult
$Res call({
 String baseUrl
});




}
/// @nodoc
class _$MediaDtoCopyWithImpl<$Res>
    implements $MediaDtoCopyWith<$Res> {
  _$MediaDtoCopyWithImpl(this._self, this._then);

  final MediaDto _self;
  final $Res Function(MediaDto) _then;

/// Create a copy of MediaDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? baseUrl = null,}) {
  return _then(MediaDto(
baseUrl: null == baseUrl ? _self.baseUrl : baseUrl // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [MediaDto].
extension MediaDtoPatterns on MediaDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _MediaDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _MediaDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _MediaDto value)  $default,){
final _that = this;
switch (_that) {
case _MediaDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _MediaDto value)?  $default,){
final _that = this;
switch (_that) {
case _MediaDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String baseUrl)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _MediaDto() when $default != null:
return $default(_that.baseUrl);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String baseUrl)  $default,) {final _that = this;
switch (_that) {
case _MediaDto():
return $default(_that.baseUrl);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String baseUrl)?  $default,) {final _that = this;
switch (_that) {
case _MediaDto() when $default != null:
return $default(_that.baseUrl);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _MediaDto implements MediaDto {
  const _MediaDto({required this.baseUrl});
  factory _MediaDto.fromJson(Map<String, dynamic> json) => _$MediaDtoFromJson(json);

@override final  String baseUrl;

/// Create a copy of MediaDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$MediaDtoCopyWith<_MediaDto> get copyWith => __$MediaDtoCopyWithImpl<_MediaDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$MediaDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _MediaDto&&(identical(other.baseUrl, baseUrl) || other.baseUrl == baseUrl));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,baseUrl);
}

@override
String toString() {
    return 'MediaDto(baseUrl: $baseUrl)';
}


}

/// @nodoc
abstract mixin class _$MediaDtoCopyWith<$Res> implements $MediaDtoCopyWith<$Res> {
  factory _$MediaDtoCopyWith(_MediaDto value, $Res Function(_MediaDto) _then) = __$MediaDtoCopyWithImpl;
@override @useResult
$Res call({
 String baseUrl
});




}
/// @nodoc
class __$MediaDtoCopyWithImpl<$Res>
    implements _$MediaDtoCopyWith<$Res> {
  __$MediaDtoCopyWithImpl(this._self, this._then);

  final _MediaDto _self;
  final $Res Function(_MediaDto) _then;

/// Create a copy of MediaDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? baseUrl = null,}) {
  return _then(_MediaDto(
baseUrl: null == baseUrl ? _self.baseUrl : baseUrl // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}


/// @nodoc
mixin _$LinksDto {

 String? get support; String? get terms; String? get privacy; String? get refund; String? get webCheckout;
/// Create a copy of LinksDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$LinksDtoCopyWith<LinksDto> get copyWith => _$LinksDtoCopyWithImpl<LinksDto>(this as LinksDto, _$identity);

  /// Serializes this LinksDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as LinksDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is LinksDto&&(identical(other.support, _this.support) || other.support == _this.support)&&(identical(other.terms, _this.terms) || other.terms == _this.terms)&&(identical(other.privacy, _this.privacy) || other.privacy == _this.privacy)&&(identical(other.refund, _this.refund) || other.refund == _this.refund)&&(identical(other.webCheckout, _this.webCheckout) || other.webCheckout == _this.webCheckout));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as LinksDto;
  return Object.hash(runtimeType,_this.support,_this.terms,_this.privacy,_this.refund,_this.webCheckout);
}

@override
String toString() {
  final _this = this as LinksDto;
  return 'LinksDto(support: ${_this.support}, terms: ${_this.terms}, privacy: ${_this.privacy}, refund: ${_this.refund}, webCheckout: ${_this.webCheckout})';
}


}

/// @nodoc
abstract mixin class $LinksDtoCopyWith<$Res>  {
  factory $LinksDtoCopyWith(LinksDto value, $Res Function(LinksDto) _then) = _$LinksDtoCopyWithImpl;
@useResult
$Res call({
 String? support, String? terms, String? privacy, String? refund, String? webCheckout
});




}
/// @nodoc
class _$LinksDtoCopyWithImpl<$Res>
    implements $LinksDtoCopyWith<$Res> {
  _$LinksDtoCopyWithImpl(this._self, this._then);

  final LinksDto _self;
  final $Res Function(LinksDto) _then;

/// Create a copy of LinksDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? support = freezed,Object? terms = freezed,Object? privacy = freezed,Object? refund = freezed,Object? webCheckout = freezed,}) {
  return _then(LinksDto(
support: freezed == support ? _self.support : support // ignore: cast_nullable_to_non_nullable
as String?,terms: freezed == terms ? _self.terms : terms // ignore: cast_nullable_to_non_nullable
as String?,privacy: freezed == privacy ? _self.privacy : privacy // ignore: cast_nullable_to_non_nullable
as String?,refund: freezed == refund ? _self.refund : refund // ignore: cast_nullable_to_non_nullable
as String?,webCheckout: freezed == webCheckout ? _self.webCheckout : webCheckout // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [LinksDto].
extension LinksDtoPatterns on LinksDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _LinksDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _LinksDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _LinksDto value)  $default,){
final _that = this;
switch (_that) {
case _LinksDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _LinksDto value)?  $default,){
final _that = this;
switch (_that) {
case _LinksDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String? support,  String? terms,  String? privacy,  String? refund,  String? webCheckout)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _LinksDto() when $default != null:
return $default(_that.support,_that.terms,_that.privacy,_that.refund,_that.webCheckout);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String? support,  String? terms,  String? privacy,  String? refund,  String? webCheckout)  $default,) {final _that = this;
switch (_that) {
case _LinksDto():
return $default(_that.support,_that.terms,_that.privacy,_that.refund,_that.webCheckout);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String? support,  String? terms,  String? privacy,  String? refund,  String? webCheckout)?  $default,) {final _that = this;
switch (_that) {
case _LinksDto() when $default != null:
return $default(_that.support,_that.terms,_that.privacy,_that.refund,_that.webCheckout);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _LinksDto implements LinksDto {
  const _LinksDto({this.support, this.terms, this.privacy, this.refund, this.webCheckout});
  factory _LinksDto.fromJson(Map<String, dynamic> json) => _$LinksDtoFromJson(json);

@override final  String? support;
@override final  String? terms;
@override final  String? privacy;
@override final  String? refund;
@override final  String? webCheckout;

/// Create a copy of LinksDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$LinksDtoCopyWith<_LinksDto> get copyWith => __$LinksDtoCopyWithImpl<_LinksDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$LinksDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _LinksDto&&(identical(other.support, support) || other.support == support)&&(identical(other.terms, terms) || other.terms == terms)&&(identical(other.privacy, privacy) || other.privacy == privacy)&&(identical(other.refund, refund) || other.refund == refund)&&(identical(other.webCheckout, webCheckout) || other.webCheckout == webCheckout));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,support,terms,privacy,refund,webCheckout);
}

@override
String toString() {
    return 'LinksDto(support: $support, terms: $terms, privacy: $privacy, refund: $refund, webCheckout: $webCheckout)';
}


}

/// @nodoc
abstract mixin class _$LinksDtoCopyWith<$Res> implements $LinksDtoCopyWith<$Res> {
  factory _$LinksDtoCopyWith(_LinksDto value, $Res Function(_LinksDto) _then) = __$LinksDtoCopyWithImpl;
@override @useResult
$Res call({
 String? support, String? terms, String? privacy, String? refund, String? webCheckout
});




}
/// @nodoc
class __$LinksDtoCopyWithImpl<$Res>
    implements _$LinksDtoCopyWith<$Res> {
  __$LinksDtoCopyWithImpl(this._self, this._then);

  final _LinksDto _self;
  final $Res Function(_LinksDto) _then;

/// Create a copy of LinksDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? support = freezed,Object? terms = freezed,Object? privacy = freezed,Object? refund = freezed,Object? webCheckout = freezed,}) {
  return _then(_LinksDto(
support: freezed == support ? _self.support : support // ignore: cast_nullable_to_non_nullable
as String?,terms: freezed == terms ? _self.terms : terms // ignore: cast_nullable_to_non_nullable
as String?,privacy: freezed == privacy ? _self.privacy : privacy // ignore: cast_nullable_to_non_nullable
as String?,refund: freezed == refund ? _self.refund : refund // ignore: cast_nullable_to_non_nullable
as String?,webCheckout: freezed == webCheckout ? _self.webCheckout : webCheckout // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

// dart format on
