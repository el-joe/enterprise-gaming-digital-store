// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'app_config.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$AppConfig {

 Map<String, AppVersionPolicy> get versions; bool get maintenanceEnabled; AppFeatures get features; List<String> get supportedLocales; String get defaultLocale; String get baseCurrency; List<AppCurrency> get currencies; Uri get mediaBaseUrl; AppLinks get links;/// Active gateways keyed by gateway key, each with its public identifiers only
/// (`stripe.publishable_key`, `paypal.client_id`, `paymob.public_key`).
 Map<String, Map<String, String?>> get paymentGateways; String? get maintenanceMessage; AppUpdate? get update;
/// Create a copy of AppConfig
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AppConfigCopyWith<AppConfig> get copyWith => _$AppConfigCopyWithImpl<AppConfig>(this as AppConfig, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as AppConfig;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AppConfig&&const DeepCollectionEquality().equals(other.versions, _this.versions)&&(identical(other.maintenanceEnabled, _this.maintenanceEnabled) || other.maintenanceEnabled == _this.maintenanceEnabled)&&(identical(other.features, _this.features) || other.features == _this.features)&&const DeepCollectionEquality().equals(other.supportedLocales, _this.supportedLocales)&&(identical(other.defaultLocale, _this.defaultLocale) || other.defaultLocale == _this.defaultLocale)&&(identical(other.baseCurrency, _this.baseCurrency) || other.baseCurrency == _this.baseCurrency)&&const DeepCollectionEquality().equals(other.currencies, _this.currencies)&&(identical(other.mediaBaseUrl, _this.mediaBaseUrl) || other.mediaBaseUrl == _this.mediaBaseUrl)&&(identical(other.links, _this.links) || other.links == _this.links)&&const DeepCollectionEquality().equals(other.paymentGateways, _this.paymentGateways)&&(identical(other.maintenanceMessage, _this.maintenanceMessage) || other.maintenanceMessage == _this.maintenanceMessage)&&(identical(other.update, _this.update) || other.update == _this.update));
}


@override
int get hashCode {
  final _this = this as AppConfig;
  return Object.hash(runtimeType,const DeepCollectionEquality().hash(_this.versions),_this.maintenanceEnabled,_this.features,const DeepCollectionEquality().hash(_this.supportedLocales),_this.defaultLocale,_this.baseCurrency,const DeepCollectionEquality().hash(_this.currencies),_this.mediaBaseUrl,_this.links,const DeepCollectionEquality().hash(_this.paymentGateways),_this.maintenanceMessage,_this.update);
}

@override
String toString() {
  final _this = this as AppConfig;
  return 'AppConfig(versions: ${_this.versions}, maintenanceEnabled: ${_this.maintenanceEnabled}, features: ${_this.features}, supportedLocales: ${_this.supportedLocales}, defaultLocale: ${_this.defaultLocale}, baseCurrency: ${_this.baseCurrency}, currencies: ${_this.currencies}, mediaBaseUrl: ${_this.mediaBaseUrl}, links: ${_this.links}, paymentGateways: ${_this.paymentGateways}, maintenanceMessage: ${_this.maintenanceMessage}, update: ${_this.update})';
}


}

/// @nodoc
abstract mixin class $AppConfigCopyWith<$Res>  {
  factory $AppConfigCopyWith(AppConfig value, $Res Function(AppConfig) _then) = _$AppConfigCopyWithImpl;
@useResult
$Res call({
 Map<String, AppVersionPolicy> versions, bool maintenanceEnabled, AppFeatures features, List<String> supportedLocales, String defaultLocale, String baseCurrency, List<AppCurrency> currencies, Uri mediaBaseUrl, AppLinks links, Map<String, Map<String, String?>> paymentGateways, String? maintenanceMessage, AppUpdate? update
});


$AppFeaturesCopyWith<$Res> get features;$AppLinksCopyWith<$Res> get links;$AppUpdateCopyWith<$Res>? get update;

}
/// @nodoc
class _$AppConfigCopyWithImpl<$Res>
    implements $AppConfigCopyWith<$Res> {
  _$AppConfigCopyWithImpl(this._self, this._then);

  final AppConfig _self;
  final $Res Function(AppConfig) _then;

/// Create a copy of AppConfig
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? versions = null,Object? maintenanceEnabled = null,Object? features = null,Object? supportedLocales = null,Object? defaultLocale = null,Object? baseCurrency = null,Object? currencies = null,Object? mediaBaseUrl = null,Object? links = null,Object? paymentGateways = null,Object? maintenanceMessage = freezed,Object? update = freezed,}) {
  return _then(AppConfig(
versions: null == versions ? _self.versions : versions // ignore: cast_nullable_to_non_nullable
as Map<String, AppVersionPolicy>,maintenanceEnabled: null == maintenanceEnabled ? _self.maintenanceEnabled : maintenanceEnabled // ignore: cast_nullable_to_non_nullable
as bool,features: null == features ? _self.features : features // ignore: cast_nullable_to_non_nullable
as AppFeatures,supportedLocales: null == supportedLocales ? _self.supportedLocales : supportedLocales // ignore: cast_nullable_to_non_nullable
as List<String>,defaultLocale: null == defaultLocale ? _self.defaultLocale : defaultLocale // ignore: cast_nullable_to_non_nullable
as String,baseCurrency: null == baseCurrency ? _self.baseCurrency : baseCurrency // ignore: cast_nullable_to_non_nullable
as String,currencies: null == currencies ? _self.currencies : currencies // ignore: cast_nullable_to_non_nullable
as List<AppCurrency>,mediaBaseUrl: null == mediaBaseUrl ? _self.mediaBaseUrl : mediaBaseUrl // ignore: cast_nullable_to_non_nullable
as Uri,links: null == links ? _self.links : links // ignore: cast_nullable_to_non_nullable
as AppLinks,paymentGateways: null == paymentGateways ? _self.paymentGateways : paymentGateways // ignore: cast_nullable_to_non_nullable
as Map<String, Map<String, String?>>,maintenanceMessage: freezed == maintenanceMessage ? _self.maintenanceMessage : maintenanceMessage // ignore: cast_nullable_to_non_nullable
as String?,update: freezed == update ? _self.update : update // ignore: cast_nullable_to_non_nullable
as AppUpdate?,
  ));
}
/// Create a copy of AppConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$AppFeaturesCopyWith<$Res> get features {
  
  return $AppFeaturesCopyWith<$Res>(_self.features, (value) {
    return _then(_self.copyWith(features: value));
  });
}/// Create a copy of AppConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$AppLinksCopyWith<$Res> get links {
  
  return $AppLinksCopyWith<$Res>(_self.links, (value) {
    return _then(_self.copyWith(links: value));
  });
}/// Create a copy of AppConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$AppUpdateCopyWith<$Res>? get update {
    if (_self.update == null) {
    return null;
  }

  return $AppUpdateCopyWith<$Res>(_self.update!, (value) {
    return _then(_self.copyWith(update: value));
  });
}
}


/// Adds pattern-matching-related methods to [AppConfig].
extension AppConfigPatterns on AppConfig {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AppConfig value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AppConfig() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AppConfig value)  $default,){
final _that = this;
switch (_that) {
case _AppConfig():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AppConfig value)?  $default,){
final _that = this;
switch (_that) {
case _AppConfig() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( Map<String, AppVersionPolicy> versions,  bool maintenanceEnabled,  AppFeatures features,  List<String> supportedLocales,  String defaultLocale,  String baseCurrency,  List<AppCurrency> currencies,  Uri mediaBaseUrl,  AppLinks links,  Map<String, Map<String, String?>> paymentGateways,  String? maintenanceMessage,  AppUpdate? update)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _AppConfig() when $default != null:
return $default(_that.versions,_that.maintenanceEnabled,_that.features,_that.supportedLocales,_that.defaultLocale,_that.baseCurrency,_that.currencies,_that.mediaBaseUrl,_that.links,_that.paymentGateways,_that.maintenanceMessage,_that.update);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( Map<String, AppVersionPolicy> versions,  bool maintenanceEnabled,  AppFeatures features,  List<String> supportedLocales,  String defaultLocale,  String baseCurrency,  List<AppCurrency> currencies,  Uri mediaBaseUrl,  AppLinks links,  Map<String, Map<String, String?>> paymentGateways,  String? maintenanceMessage,  AppUpdate? update)  $default,) {final _that = this;
switch (_that) {
case _AppConfig():
return $default(_that.versions,_that.maintenanceEnabled,_that.features,_that.supportedLocales,_that.defaultLocale,_that.baseCurrency,_that.currencies,_that.mediaBaseUrl,_that.links,_that.paymentGateways,_that.maintenanceMessage,_that.update);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( Map<String, AppVersionPolicy> versions,  bool maintenanceEnabled,  AppFeatures features,  List<String> supportedLocales,  String defaultLocale,  String baseCurrency,  List<AppCurrency> currencies,  Uri mediaBaseUrl,  AppLinks links,  Map<String, Map<String, String?>> paymentGateways,  String? maintenanceMessage,  AppUpdate? update)?  $default,) {final _that = this;
switch (_that) {
case _AppConfig() when $default != null:
return $default(_that.versions,_that.maintenanceEnabled,_that.features,_that.supportedLocales,_that.defaultLocale,_that.baseCurrency,_that.currencies,_that.mediaBaseUrl,_that.links,_that.paymentGateways,_that.maintenanceMessage,_that.update);case _:
  return null;

}
}

}

/// @nodoc


class _AppConfig extends AppConfig {
  const _AppConfig({required  Map<String, AppVersionPolicy> versions, required this.maintenanceEnabled, required this.features, required  List<String> supportedLocales, required this.defaultLocale, required this.baseCurrency, required  List<AppCurrency> currencies, required this.mediaBaseUrl, required this.links, required  Map<String, Map<String, String?>> paymentGateways, this.maintenanceMessage, this.update}): _versions = versions,_supportedLocales = supportedLocales,_currencies = currencies,_paymentGateways = paymentGateways,super._();
  

 final  Map<String, AppVersionPolicy> _versions;
@override Map<String, AppVersionPolicy> get versions {
  if (_versions is EqualUnmodifiableMapView) return _versions;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(_versions);
}

@override final  bool maintenanceEnabled;
@override final  AppFeatures features;
 final  List<String> _supportedLocales;
@override List<String> get supportedLocales {
  if (_supportedLocales is EqualUnmodifiableListView) return _supportedLocales;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_supportedLocales);
}

@override final  String defaultLocale;
@override final  String baseCurrency;
 final  List<AppCurrency> _currencies;
@override List<AppCurrency> get currencies {
  if (_currencies is EqualUnmodifiableListView) return _currencies;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_currencies);
}

@override final  Uri mediaBaseUrl;
@override final  AppLinks links;
/// Active gateways keyed by gateway key, each with its public identifiers only
/// (`stripe.publishable_key`, `paypal.client_id`, `paymob.public_key`).
 final  Map<String, Map<String, String?>> _paymentGateways;
/// Active gateways keyed by gateway key, each with its public identifiers only
/// (`stripe.publishable_key`, `paypal.client_id`, `paymob.public_key`).
@override Map<String, Map<String, String?>> get paymentGateways {
  if (_paymentGateways is EqualUnmodifiableMapView) return _paymentGateways;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(_paymentGateways);
}

@override final  String? maintenanceMessage;
@override final  AppUpdate? update;

/// Create a copy of AppConfig
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AppConfigCopyWith<_AppConfig> get copyWith => __$AppConfigCopyWithImpl<_AppConfig>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _AppConfig&&const DeepCollectionEquality().equals(other.versions, _versions)&&(identical(other.maintenanceEnabled, maintenanceEnabled) || other.maintenanceEnabled == maintenanceEnabled)&&(identical(other.features, features) || other.features == features)&&const DeepCollectionEquality().equals(other.supportedLocales, _supportedLocales)&&(identical(other.defaultLocale, defaultLocale) || other.defaultLocale == defaultLocale)&&(identical(other.baseCurrency, baseCurrency) || other.baseCurrency == baseCurrency)&&const DeepCollectionEquality().equals(other.currencies, _currencies)&&(identical(other.mediaBaseUrl, mediaBaseUrl) || other.mediaBaseUrl == mediaBaseUrl)&&(identical(other.links, links) || other.links == links)&&const DeepCollectionEquality().equals(other.paymentGateways, _paymentGateways)&&(identical(other.maintenanceMessage, maintenanceMessage) || other.maintenanceMessage == maintenanceMessage)&&(identical(other.update, update) || other.update == update));
}


@override
int get hashCode {
    return Object.hash(runtimeType,const DeepCollectionEquality().hash(_versions),maintenanceEnabled,features,const DeepCollectionEquality().hash(_supportedLocales),defaultLocale,baseCurrency,const DeepCollectionEquality().hash(_currencies),mediaBaseUrl,links,const DeepCollectionEquality().hash(_paymentGateways),maintenanceMessage,update);
}

@override
String toString() {
    return 'AppConfig(versions: $versions, maintenanceEnabled: $maintenanceEnabled, features: $features, supportedLocales: $supportedLocales, defaultLocale: $defaultLocale, baseCurrency: $baseCurrency, currencies: $currencies, mediaBaseUrl: $mediaBaseUrl, links: $links, paymentGateways: $paymentGateways, maintenanceMessage: $maintenanceMessage, update: $update)';
}


}

/// @nodoc
abstract mixin class _$AppConfigCopyWith<$Res> implements $AppConfigCopyWith<$Res> {
  factory _$AppConfigCopyWith(_AppConfig value, $Res Function(_AppConfig) _then) = __$AppConfigCopyWithImpl;
@override @useResult
$Res call({
 Map<String, AppVersionPolicy> versions, bool maintenanceEnabled, AppFeatures features, List<String> supportedLocales, String defaultLocale, String baseCurrency, List<AppCurrency> currencies, Uri mediaBaseUrl, AppLinks links, Map<String, Map<String, String?>> paymentGateways, String? maintenanceMessage, AppUpdate? update
});


@override $AppFeaturesCopyWith<$Res> get features;@override $AppLinksCopyWith<$Res> get links;@override $AppUpdateCopyWith<$Res>? get update;

}
/// @nodoc
class __$AppConfigCopyWithImpl<$Res>
    implements _$AppConfigCopyWith<$Res> {
  __$AppConfigCopyWithImpl(this._self, this._then);

  final _AppConfig _self;
  final $Res Function(_AppConfig) _then;

/// Create a copy of AppConfig
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? versions = null,Object? maintenanceEnabled = null,Object? features = null,Object? supportedLocales = null,Object? defaultLocale = null,Object? baseCurrency = null,Object? currencies = null,Object? mediaBaseUrl = null,Object? links = null,Object? paymentGateways = null,Object? maintenanceMessage = freezed,Object? update = freezed,}) {
  return _then(_AppConfig(
versions: null == versions ? _self._versions : versions // ignore: cast_nullable_to_non_nullable
as Map<String, AppVersionPolicy>,maintenanceEnabled: null == maintenanceEnabled ? _self.maintenanceEnabled : maintenanceEnabled // ignore: cast_nullable_to_non_nullable
as bool,features: null == features ? _self.features : features // ignore: cast_nullable_to_non_nullable
as AppFeatures,supportedLocales: null == supportedLocales ? _self._supportedLocales : supportedLocales // ignore: cast_nullable_to_non_nullable
as List<String>,defaultLocale: null == defaultLocale ? _self.defaultLocale : defaultLocale // ignore: cast_nullable_to_non_nullable
as String,baseCurrency: null == baseCurrency ? _self.baseCurrency : baseCurrency // ignore: cast_nullable_to_non_nullable
as String,currencies: null == currencies ? _self._currencies : currencies // ignore: cast_nullable_to_non_nullable
as List<AppCurrency>,mediaBaseUrl: null == mediaBaseUrl ? _self.mediaBaseUrl : mediaBaseUrl // ignore: cast_nullable_to_non_nullable
as Uri,links: null == links ? _self.links : links // ignore: cast_nullable_to_non_nullable
as AppLinks,paymentGateways: null == paymentGateways ? _self._paymentGateways : paymentGateways // ignore: cast_nullable_to_non_nullable
as Map<String, Map<String, String?>>,maintenanceMessage: freezed == maintenanceMessage ? _self.maintenanceMessage : maintenanceMessage // ignore: cast_nullable_to_non_nullable
as String?,update: freezed == update ? _self.update : update // ignore: cast_nullable_to_non_nullable
as AppUpdate?,
  ));
}

/// Create a copy of AppConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$AppFeaturesCopyWith<$Res> get features {
  
  return $AppFeaturesCopyWith<$Res>(_self.features, (value) {
    return _then(_self.copyWith(features: value));
  });
}/// Create a copy of AppConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$AppLinksCopyWith<$Res> get links {
  
  return $AppLinksCopyWith<$Res>(_self.links, (value) {
    return _then(_self.copyWith(links: value));
  });
}/// Create a copy of AppConfig
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$AppUpdateCopyWith<$Res>? get update {
    if (_self.update == null) {
    return null;
  }

  return $AppUpdateCopyWith<$Res>(_self.update!, (value) {
    return _then(_self.copyWith(update: value));
  });
}
}

/// @nodoc
mixin _$AppVersionPolicy {

 String get minSupportedVersion; String get latestVersion;
/// Create a copy of AppVersionPolicy
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AppVersionPolicyCopyWith<AppVersionPolicy> get copyWith => _$AppVersionPolicyCopyWithImpl<AppVersionPolicy>(this as AppVersionPolicy, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as AppVersionPolicy;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AppVersionPolicy&&(identical(other.minSupportedVersion, _this.minSupportedVersion) || other.minSupportedVersion == _this.minSupportedVersion)&&(identical(other.latestVersion, _this.latestVersion) || other.latestVersion == _this.latestVersion));
}


@override
int get hashCode {
  final _this = this as AppVersionPolicy;
  return Object.hash(runtimeType,_this.minSupportedVersion,_this.latestVersion);
}

@override
String toString() {
  final _this = this as AppVersionPolicy;
  return 'AppVersionPolicy(minSupportedVersion: ${_this.minSupportedVersion}, latestVersion: ${_this.latestVersion})';
}


}

/// @nodoc
abstract mixin class $AppVersionPolicyCopyWith<$Res>  {
  factory $AppVersionPolicyCopyWith(AppVersionPolicy value, $Res Function(AppVersionPolicy) _then) = _$AppVersionPolicyCopyWithImpl;
@useResult
$Res call({
 String minSupportedVersion, String latestVersion
});




}
/// @nodoc
class _$AppVersionPolicyCopyWithImpl<$Res>
    implements $AppVersionPolicyCopyWith<$Res> {
  _$AppVersionPolicyCopyWithImpl(this._self, this._then);

  final AppVersionPolicy _self;
  final $Res Function(AppVersionPolicy) _then;

/// Create a copy of AppVersionPolicy
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? minSupportedVersion = null,Object? latestVersion = null,}) {
  return _then(AppVersionPolicy(
minSupportedVersion: null == minSupportedVersion ? _self.minSupportedVersion : minSupportedVersion // ignore: cast_nullable_to_non_nullable
as String,latestVersion: null == latestVersion ? _self.latestVersion : latestVersion // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [AppVersionPolicy].
extension AppVersionPolicyPatterns on AppVersionPolicy {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AppVersionPolicy value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AppVersionPolicy() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AppVersionPolicy value)  $default,){
final _that = this;
switch (_that) {
case _AppVersionPolicy():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AppVersionPolicy value)?  $default,){
final _that = this;
switch (_that) {
case _AppVersionPolicy() when $default != null:
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
case _AppVersionPolicy() when $default != null:
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
case _AppVersionPolicy():
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
case _AppVersionPolicy() when $default != null:
return $default(_that.minSupportedVersion,_that.latestVersion);case _:
  return null;

}
}

}

/// @nodoc


class _AppVersionPolicy implements AppVersionPolicy {
  const _AppVersionPolicy({required this.minSupportedVersion, required this.latestVersion});
  

@override final  String minSupportedVersion;
@override final  String latestVersion;

/// Create a copy of AppVersionPolicy
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AppVersionPolicyCopyWith<_AppVersionPolicy> get copyWith => __$AppVersionPolicyCopyWithImpl<_AppVersionPolicy>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _AppVersionPolicy&&(identical(other.minSupportedVersion, minSupportedVersion) || other.minSupportedVersion == minSupportedVersion)&&(identical(other.latestVersion, latestVersion) || other.latestVersion == latestVersion));
}


@override
int get hashCode {
    return Object.hash(runtimeType,minSupportedVersion,latestVersion);
}

@override
String toString() {
    return 'AppVersionPolicy(minSupportedVersion: $minSupportedVersion, latestVersion: $latestVersion)';
}


}

/// @nodoc
abstract mixin class _$AppVersionPolicyCopyWith<$Res> implements $AppVersionPolicyCopyWith<$Res> {
  factory _$AppVersionPolicyCopyWith(_AppVersionPolicy value, $Res Function(_AppVersionPolicy) _then) = __$AppVersionPolicyCopyWithImpl;
@override @useResult
$Res call({
 String minSupportedVersion, String latestVersion
});




}
/// @nodoc
class __$AppVersionPolicyCopyWithImpl<$Res>
    implements _$AppVersionPolicyCopyWith<$Res> {
  __$AppVersionPolicyCopyWithImpl(this._self, this._then);

  final _AppVersionPolicy _self;
  final $Res Function(_AppVersionPolicy) _then;

/// Create a copy of AppVersionPolicy
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? minSupportedVersion = null,Object? latestVersion = null,}) {
  return _then(_AppVersionPolicy(
minSupportedVersion: null == minSupportedVersion ? _self.minSupportedVersion : minSupportedVersion // ignore: cast_nullable_to_non_nullable
as String,latestVersion: null == latestVersion ? _self.latestVersion : latestVersion // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc
mixin _$AppUpdate {

 String get platform; String get version; bool get required; bool get available;
/// Create a copy of AppUpdate
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AppUpdateCopyWith<AppUpdate> get copyWith => _$AppUpdateCopyWithImpl<AppUpdate>(this as AppUpdate, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as AppUpdate;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AppUpdate&&(identical(other.platform, _this.platform) || other.platform == _this.platform)&&(identical(other.version, _this.version) || other.version == _this.version)&&(identical(other.required, _this.required) || other.required == _this.required)&&(identical(other.available, _this.available) || other.available == _this.available));
}


@override
int get hashCode {
  final _this = this as AppUpdate;
  return Object.hash(runtimeType,_this.platform,_this.version,_this.required,_this.available);
}

@override
String toString() {
  final _this = this as AppUpdate;
  return 'AppUpdate(platform: ${_this.platform}, version: ${_this.version}, required: ${_this.required}, available: ${_this.available})';
}


}

/// @nodoc
abstract mixin class $AppUpdateCopyWith<$Res>  {
  factory $AppUpdateCopyWith(AppUpdate value, $Res Function(AppUpdate) _then) = _$AppUpdateCopyWithImpl;
@useResult
$Res call({
 String platform, String version, bool required, bool available
});




}
/// @nodoc
class _$AppUpdateCopyWithImpl<$Res>
    implements $AppUpdateCopyWith<$Res> {
  _$AppUpdateCopyWithImpl(this._self, this._then);

  final AppUpdate _self;
  final $Res Function(AppUpdate) _then;

/// Create a copy of AppUpdate
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? platform = null,Object? version = null,Object? required = null,Object? available = null,}) {
  return _then(AppUpdate(
platform: null == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as String,version: null == version ? _self.version : version // ignore: cast_nullable_to_non_nullable
as String,required: null == required ? _self.required : required // ignore: cast_nullable_to_non_nullable
as bool,available: null == available ? _self.available : available // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [AppUpdate].
extension AppUpdatePatterns on AppUpdate {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AppUpdate value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AppUpdate() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AppUpdate value)  $default,){
final _that = this;
switch (_that) {
case _AppUpdate():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AppUpdate value)?  $default,){
final _that = this;
switch (_that) {
case _AppUpdate() when $default != null:
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
case _AppUpdate() when $default != null:
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
case _AppUpdate():
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
case _AppUpdate() when $default != null:
return $default(_that.platform,_that.version,_that.required,_that.available);case _:
  return null;

}
}

}

/// @nodoc


class _AppUpdate implements AppUpdate {
  const _AppUpdate({required this.platform, required this.version, required this.required, required this.available});
  

@override final  String platform;
@override final  String version;
@override final  bool required;
@override final  bool available;

/// Create a copy of AppUpdate
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AppUpdateCopyWith<_AppUpdate> get copyWith => __$AppUpdateCopyWithImpl<_AppUpdate>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _AppUpdate&&(identical(other.platform, platform) || other.platform == platform)&&(identical(other.version, version) || other.version == version)&&(identical(other.required, required) || other.required == required)&&(identical(other.available, available) || other.available == available));
}


@override
int get hashCode {
    return Object.hash(runtimeType,platform,version,required,available);
}

@override
String toString() {
    return 'AppUpdate(platform: $platform, version: $version, required: $required, available: $available)';
}


}

/// @nodoc
abstract mixin class _$AppUpdateCopyWith<$Res> implements $AppUpdateCopyWith<$Res> {
  factory _$AppUpdateCopyWith(_AppUpdate value, $Res Function(_AppUpdate) _then) = __$AppUpdateCopyWithImpl;
@override @useResult
$Res call({
 String platform, String version, bool required, bool available
});




}
/// @nodoc
class __$AppUpdateCopyWithImpl<$Res>
    implements _$AppUpdateCopyWith<$Res> {
  __$AppUpdateCopyWithImpl(this._self, this._then);

  final _AppUpdate _self;
  final $Res Function(_AppUpdate) _then;

/// Create a copy of AppUpdate
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? platform = null,Object? version = null,Object? required = null,Object? available = null,}) {
  return _then(_AppUpdate(
platform: null == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as String,version: null == version ? _self.version : version // ignore: cast_nullable_to_non_nullable
as String,required: null == required ? _self.required : required // ignore: cast_nullable_to_non_nullable
as bool,available: null == available ? _self.available : available // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc
mixin _$AppFeatures {

 bool get checkoutEnabled; bool get guestCheckoutEnabled; bool get walletEnabled; bool get reviewsEnabled; bool get supportEnabled;
/// Create a copy of AppFeatures
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AppFeaturesCopyWith<AppFeatures> get copyWith => _$AppFeaturesCopyWithImpl<AppFeatures>(this as AppFeatures, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as AppFeatures;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AppFeatures&&(identical(other.checkoutEnabled, _this.checkoutEnabled) || other.checkoutEnabled == _this.checkoutEnabled)&&(identical(other.guestCheckoutEnabled, _this.guestCheckoutEnabled) || other.guestCheckoutEnabled == _this.guestCheckoutEnabled)&&(identical(other.walletEnabled, _this.walletEnabled) || other.walletEnabled == _this.walletEnabled)&&(identical(other.reviewsEnabled, _this.reviewsEnabled) || other.reviewsEnabled == _this.reviewsEnabled)&&(identical(other.supportEnabled, _this.supportEnabled) || other.supportEnabled == _this.supportEnabled));
}


@override
int get hashCode {
  final _this = this as AppFeatures;
  return Object.hash(runtimeType,_this.checkoutEnabled,_this.guestCheckoutEnabled,_this.walletEnabled,_this.reviewsEnabled,_this.supportEnabled);
}

@override
String toString() {
  final _this = this as AppFeatures;
  return 'AppFeatures(checkoutEnabled: ${_this.checkoutEnabled}, guestCheckoutEnabled: ${_this.guestCheckoutEnabled}, walletEnabled: ${_this.walletEnabled}, reviewsEnabled: ${_this.reviewsEnabled}, supportEnabled: ${_this.supportEnabled})';
}


}

/// @nodoc
abstract mixin class $AppFeaturesCopyWith<$Res>  {
  factory $AppFeaturesCopyWith(AppFeatures value, $Res Function(AppFeatures) _then) = _$AppFeaturesCopyWithImpl;
@useResult
$Res call({
 bool checkoutEnabled, bool guestCheckoutEnabled, bool walletEnabled, bool reviewsEnabled, bool supportEnabled
});




}
/// @nodoc
class _$AppFeaturesCopyWithImpl<$Res>
    implements $AppFeaturesCopyWith<$Res> {
  _$AppFeaturesCopyWithImpl(this._self, this._then);

  final AppFeatures _self;
  final $Res Function(AppFeatures) _then;

/// Create a copy of AppFeatures
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? checkoutEnabled = null,Object? guestCheckoutEnabled = null,Object? walletEnabled = null,Object? reviewsEnabled = null,Object? supportEnabled = null,}) {
  return _then(AppFeatures(
checkoutEnabled: null == checkoutEnabled ? _self.checkoutEnabled : checkoutEnabled // ignore: cast_nullable_to_non_nullable
as bool,guestCheckoutEnabled: null == guestCheckoutEnabled ? _self.guestCheckoutEnabled : guestCheckoutEnabled // ignore: cast_nullable_to_non_nullable
as bool,walletEnabled: null == walletEnabled ? _self.walletEnabled : walletEnabled // ignore: cast_nullable_to_non_nullable
as bool,reviewsEnabled: null == reviewsEnabled ? _self.reviewsEnabled : reviewsEnabled // ignore: cast_nullable_to_non_nullable
as bool,supportEnabled: null == supportEnabled ? _self.supportEnabled : supportEnabled // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [AppFeatures].
extension AppFeaturesPatterns on AppFeatures {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AppFeatures value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AppFeatures() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AppFeatures value)  $default,){
final _that = this;
switch (_that) {
case _AppFeatures():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AppFeatures value)?  $default,){
final _that = this;
switch (_that) {
case _AppFeatures() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool checkoutEnabled,  bool guestCheckoutEnabled,  bool walletEnabled,  bool reviewsEnabled,  bool supportEnabled)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _AppFeatures() when $default != null:
return $default(_that.checkoutEnabled,_that.guestCheckoutEnabled,_that.walletEnabled,_that.reviewsEnabled,_that.supportEnabled);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool checkoutEnabled,  bool guestCheckoutEnabled,  bool walletEnabled,  bool reviewsEnabled,  bool supportEnabled)  $default,) {final _that = this;
switch (_that) {
case _AppFeatures():
return $default(_that.checkoutEnabled,_that.guestCheckoutEnabled,_that.walletEnabled,_that.reviewsEnabled,_that.supportEnabled);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool checkoutEnabled,  bool guestCheckoutEnabled,  bool walletEnabled,  bool reviewsEnabled,  bool supportEnabled)?  $default,) {final _that = this;
switch (_that) {
case _AppFeatures() when $default != null:
return $default(_that.checkoutEnabled,_that.guestCheckoutEnabled,_that.walletEnabled,_that.reviewsEnabled,_that.supportEnabled);case _:
  return null;

}
}

}

/// @nodoc


class _AppFeatures implements AppFeatures {
  const _AppFeatures({required this.checkoutEnabled, required this.guestCheckoutEnabled, required this.walletEnabled, required this.reviewsEnabled, required this.supportEnabled});
  

@override final  bool checkoutEnabled;
@override final  bool guestCheckoutEnabled;
@override final  bool walletEnabled;
@override final  bool reviewsEnabled;
@override final  bool supportEnabled;

/// Create a copy of AppFeatures
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AppFeaturesCopyWith<_AppFeatures> get copyWith => __$AppFeaturesCopyWithImpl<_AppFeatures>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _AppFeatures&&(identical(other.checkoutEnabled, checkoutEnabled) || other.checkoutEnabled == checkoutEnabled)&&(identical(other.guestCheckoutEnabled, guestCheckoutEnabled) || other.guestCheckoutEnabled == guestCheckoutEnabled)&&(identical(other.walletEnabled, walletEnabled) || other.walletEnabled == walletEnabled)&&(identical(other.reviewsEnabled, reviewsEnabled) || other.reviewsEnabled == reviewsEnabled)&&(identical(other.supportEnabled, supportEnabled) || other.supportEnabled == supportEnabled));
}


@override
int get hashCode {
    return Object.hash(runtimeType,checkoutEnabled,guestCheckoutEnabled,walletEnabled,reviewsEnabled,supportEnabled);
}

@override
String toString() {
    return 'AppFeatures(checkoutEnabled: $checkoutEnabled, guestCheckoutEnabled: $guestCheckoutEnabled, walletEnabled: $walletEnabled, reviewsEnabled: $reviewsEnabled, supportEnabled: $supportEnabled)';
}


}

/// @nodoc
abstract mixin class _$AppFeaturesCopyWith<$Res> implements $AppFeaturesCopyWith<$Res> {
  factory _$AppFeaturesCopyWith(_AppFeatures value, $Res Function(_AppFeatures) _then) = __$AppFeaturesCopyWithImpl;
@override @useResult
$Res call({
 bool checkoutEnabled, bool guestCheckoutEnabled, bool walletEnabled, bool reviewsEnabled, bool supportEnabled
});




}
/// @nodoc
class __$AppFeaturesCopyWithImpl<$Res>
    implements _$AppFeaturesCopyWith<$Res> {
  __$AppFeaturesCopyWithImpl(this._self, this._then);

  final _AppFeatures _self;
  final $Res Function(_AppFeatures) _then;

/// Create a copy of AppFeatures
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? checkoutEnabled = null,Object? guestCheckoutEnabled = null,Object? walletEnabled = null,Object? reviewsEnabled = null,Object? supportEnabled = null,}) {
  return _then(_AppFeatures(
checkoutEnabled: null == checkoutEnabled ? _self.checkoutEnabled : checkoutEnabled // ignore: cast_nullable_to_non_nullable
as bool,guestCheckoutEnabled: null == guestCheckoutEnabled ? _self.guestCheckoutEnabled : guestCheckoutEnabled // ignore: cast_nullable_to_non_nullable
as bool,walletEnabled: null == walletEnabled ? _self.walletEnabled : walletEnabled // ignore: cast_nullable_to_non_nullable
as bool,reviewsEnabled: null == reviewsEnabled ? _self.reviewsEnabled : reviewsEnabled // ignore: cast_nullable_to_non_nullable
as bool,supportEnabled: null == supportEnabled ? _self.supportEnabled : supportEnabled // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc
mixin _$AppCurrency {

 String get name; CurrencyInfo get info;
/// Create a copy of AppCurrency
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AppCurrencyCopyWith<AppCurrency> get copyWith => _$AppCurrencyCopyWithImpl<AppCurrency>(this as AppCurrency, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as AppCurrency;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AppCurrency&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.info, _this.info) || other.info == _this.info));
}


@override
int get hashCode {
  final _this = this as AppCurrency;
  return Object.hash(runtimeType,_this.name,_this.info);
}

@override
String toString() {
  final _this = this as AppCurrency;
  return 'AppCurrency(name: ${_this.name}, info: ${_this.info})';
}


}

/// @nodoc
abstract mixin class $AppCurrencyCopyWith<$Res>  {
  factory $AppCurrencyCopyWith(AppCurrency value, $Res Function(AppCurrency) _then) = _$AppCurrencyCopyWithImpl;
@useResult
$Res call({
 String name, CurrencyInfo info
});




}
/// @nodoc
class _$AppCurrencyCopyWithImpl<$Res>
    implements $AppCurrencyCopyWith<$Res> {
  _$AppCurrencyCopyWithImpl(this._self, this._then);

  final AppCurrency _self;
  final $Res Function(AppCurrency) _then;

/// Create a copy of AppCurrency
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? name = null,Object? info = null,}) {
  return _then(AppCurrency(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,info: null == info ? _self.info : info // ignore: cast_nullable_to_non_nullable
as CurrencyInfo,
  ));
}

}


/// Adds pattern-matching-related methods to [AppCurrency].
extension AppCurrencyPatterns on AppCurrency {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AppCurrency value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AppCurrency() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AppCurrency value)  $default,){
final _that = this;
switch (_that) {
case _AppCurrency():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AppCurrency value)?  $default,){
final _that = this;
switch (_that) {
case _AppCurrency() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String name,  CurrencyInfo info)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _AppCurrency() when $default != null:
return $default(_that.name,_that.info);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String name,  CurrencyInfo info)  $default,) {final _that = this;
switch (_that) {
case _AppCurrency():
return $default(_that.name,_that.info);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String name,  CurrencyInfo info)?  $default,) {final _that = this;
switch (_that) {
case _AppCurrency() when $default != null:
return $default(_that.name,_that.info);case _:
  return null;

}
}

}

/// @nodoc


class _AppCurrency implements AppCurrency {
  const _AppCurrency({required this.name, required this.info});
  

@override final  String name;
@override final  CurrencyInfo info;

/// Create a copy of AppCurrency
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AppCurrencyCopyWith<_AppCurrency> get copyWith => __$AppCurrencyCopyWithImpl<_AppCurrency>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _AppCurrency&&(identical(other.name, name) || other.name == name)&&(identical(other.info, info) || other.info == info));
}


@override
int get hashCode {
    return Object.hash(runtimeType,name,info);
}

@override
String toString() {
    return 'AppCurrency(name: $name, info: $info)';
}


}

/// @nodoc
abstract mixin class _$AppCurrencyCopyWith<$Res> implements $AppCurrencyCopyWith<$Res> {
  factory _$AppCurrencyCopyWith(_AppCurrency value, $Res Function(_AppCurrency) _then) = __$AppCurrencyCopyWithImpl;
@override @useResult
$Res call({
 String name, CurrencyInfo info
});




}
/// @nodoc
class __$AppCurrencyCopyWithImpl<$Res>
    implements _$AppCurrencyCopyWith<$Res> {
  __$AppCurrencyCopyWithImpl(this._self, this._then);

  final _AppCurrency _self;
  final $Res Function(_AppCurrency) _then;

/// Create a copy of AppCurrency
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? name = null,Object? info = null,}) {
  return _then(_AppCurrency(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,info: null == info ? _self.info : info // ignore: cast_nullable_to_non_nullable
as CurrencyInfo,
  ));
}


}

/// @nodoc
mixin _$AppLinks {

 Uri? get support; Uri? get terms; Uri? get privacy; Uri? get refund;
/// Create a copy of AppLinks
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AppLinksCopyWith<AppLinks> get copyWith => _$AppLinksCopyWithImpl<AppLinks>(this as AppLinks, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as AppLinks;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AppLinks&&(identical(other.support, _this.support) || other.support == _this.support)&&(identical(other.terms, _this.terms) || other.terms == _this.terms)&&(identical(other.privacy, _this.privacy) || other.privacy == _this.privacy)&&(identical(other.refund, _this.refund) || other.refund == _this.refund));
}


@override
int get hashCode {
  final _this = this as AppLinks;
  return Object.hash(runtimeType,_this.support,_this.terms,_this.privacy,_this.refund);
}

@override
String toString() {
  final _this = this as AppLinks;
  return 'AppLinks(support: ${_this.support}, terms: ${_this.terms}, privacy: ${_this.privacy}, refund: ${_this.refund})';
}


}

/// @nodoc
abstract mixin class $AppLinksCopyWith<$Res>  {
  factory $AppLinksCopyWith(AppLinks value, $Res Function(AppLinks) _then) = _$AppLinksCopyWithImpl;
@useResult
$Res call({
 Uri? support, Uri? terms, Uri? privacy, Uri? refund
});




}
/// @nodoc
class _$AppLinksCopyWithImpl<$Res>
    implements $AppLinksCopyWith<$Res> {
  _$AppLinksCopyWithImpl(this._self, this._then);

  final AppLinks _self;
  final $Res Function(AppLinks) _then;

/// Create a copy of AppLinks
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? support = freezed,Object? terms = freezed,Object? privacy = freezed,Object? refund = freezed,}) {
  return _then(AppLinks(
support: freezed == support ? _self.support : support // ignore: cast_nullable_to_non_nullable
as Uri?,terms: freezed == terms ? _self.terms : terms // ignore: cast_nullable_to_non_nullable
as Uri?,privacy: freezed == privacy ? _self.privacy : privacy // ignore: cast_nullable_to_non_nullable
as Uri?,refund: freezed == refund ? _self.refund : refund // ignore: cast_nullable_to_non_nullable
as Uri?,
  ));
}

}


/// Adds pattern-matching-related methods to [AppLinks].
extension AppLinksPatterns on AppLinks {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AppLinks value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AppLinks() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AppLinks value)  $default,){
final _that = this;
switch (_that) {
case _AppLinks():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AppLinks value)?  $default,){
final _that = this;
switch (_that) {
case _AppLinks() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( Uri? support,  Uri? terms,  Uri? privacy,  Uri? refund)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _AppLinks() when $default != null:
return $default(_that.support,_that.terms,_that.privacy,_that.refund);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( Uri? support,  Uri? terms,  Uri? privacy,  Uri? refund)  $default,) {final _that = this;
switch (_that) {
case _AppLinks():
return $default(_that.support,_that.terms,_that.privacy,_that.refund);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( Uri? support,  Uri? terms,  Uri? privacy,  Uri? refund)?  $default,) {final _that = this;
switch (_that) {
case _AppLinks() when $default != null:
return $default(_that.support,_that.terms,_that.privacy,_that.refund);case _:
  return null;

}
}

}

/// @nodoc


class _AppLinks implements AppLinks {
  const _AppLinks({this.support, this.terms, this.privacy, this.refund});
  

@override final  Uri? support;
@override final  Uri? terms;
@override final  Uri? privacy;
@override final  Uri? refund;

/// Create a copy of AppLinks
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AppLinksCopyWith<_AppLinks> get copyWith => __$AppLinksCopyWithImpl<_AppLinks>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _AppLinks&&(identical(other.support, support) || other.support == support)&&(identical(other.terms, terms) || other.terms == terms)&&(identical(other.privacy, privacy) || other.privacy == privacy)&&(identical(other.refund, refund) || other.refund == refund));
}


@override
int get hashCode {
    return Object.hash(runtimeType,support,terms,privacy,refund);
}

@override
String toString() {
    return 'AppLinks(support: $support, terms: $terms, privacy: $privacy, refund: $refund)';
}


}

/// @nodoc
abstract mixin class _$AppLinksCopyWith<$Res> implements $AppLinksCopyWith<$Res> {
  factory _$AppLinksCopyWith(_AppLinks value, $Res Function(_AppLinks) _then) = __$AppLinksCopyWithImpl;
@override @useResult
$Res call({
 Uri? support, Uri? terms, Uri? privacy, Uri? refund
});




}
/// @nodoc
class __$AppLinksCopyWithImpl<$Res>
    implements _$AppLinksCopyWith<$Res> {
  __$AppLinksCopyWithImpl(this._self, this._then);

  final _AppLinks _self;
  final $Res Function(_AppLinks) _then;

/// Create a copy of AppLinks
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? support = freezed,Object? terms = freezed,Object? privacy = freezed,Object? refund = freezed,}) {
  return _then(_AppLinks(
support: freezed == support ? _self.support : support // ignore: cast_nullable_to_non_nullable
as Uri?,terms: freezed == terms ? _self.terms : terms // ignore: cast_nullable_to_non_nullable
as Uri?,privacy: freezed == privacy ? _self.privacy : privacy // ignore: cast_nullable_to_non_nullable
as Uri?,refund: freezed == refund ? _self.refund : refund // ignore: cast_nullable_to_non_nullable
as Uri?,
  ));
}


}

// dart format on
