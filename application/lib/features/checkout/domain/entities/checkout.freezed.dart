// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'checkout.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$PaymentOption {

 String get gateway; String get label; List<PaymentMethodOption> get methods; bool get available; String get paymentCurrency; Money get paymentAmount; String get displayCurrency; Money get displayAmount;/// null, `charged_in_payment_currency` (show it) or `payment_currency_below_margin`.
 String? get notice;
/// Create a copy of PaymentOption
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PaymentOptionCopyWith<PaymentOption> get copyWith => _$PaymentOptionCopyWithImpl<PaymentOption>(this as PaymentOption, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as PaymentOption;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PaymentOption&&(identical(other.gateway, _this.gateway) || other.gateway == _this.gateway)&&(identical(other.label, _this.label) || other.label == _this.label)&&const DeepCollectionEquality().equals(other.methods, _this.methods)&&(identical(other.available, _this.available) || other.available == _this.available)&&(identical(other.paymentCurrency, _this.paymentCurrency) || other.paymentCurrency == _this.paymentCurrency)&&(identical(other.paymentAmount, _this.paymentAmount) || other.paymentAmount == _this.paymentAmount)&&(identical(other.displayCurrency, _this.displayCurrency) || other.displayCurrency == _this.displayCurrency)&&(identical(other.displayAmount, _this.displayAmount) || other.displayAmount == _this.displayAmount)&&(identical(other.notice, _this.notice) || other.notice == _this.notice));
}


@override
int get hashCode {
  final _this = this as PaymentOption;
  return Object.hash(runtimeType,_this.gateway,_this.label,const DeepCollectionEquality().hash(_this.methods),_this.available,_this.paymentCurrency,_this.paymentAmount,_this.displayCurrency,_this.displayAmount,_this.notice);
}

@override
String toString() {
  final _this = this as PaymentOption;
  return 'PaymentOption(gateway: ${_this.gateway}, label: ${_this.label}, methods: ${_this.methods}, available: ${_this.available}, paymentCurrency: ${_this.paymentCurrency}, paymentAmount: ${_this.paymentAmount}, displayCurrency: ${_this.displayCurrency}, displayAmount: ${_this.displayAmount}, notice: ${_this.notice})';
}


}

/// @nodoc
abstract mixin class $PaymentOptionCopyWith<$Res>  {
  factory $PaymentOptionCopyWith(PaymentOption value, $Res Function(PaymentOption) _then) = _$PaymentOptionCopyWithImpl;
@useResult
$Res call({
 String gateway, String label, List<PaymentMethodOption> methods, bool available, String paymentCurrency, Money paymentAmount, String displayCurrency, Money displayAmount, String? notice
});




}
/// @nodoc
class _$PaymentOptionCopyWithImpl<$Res>
    implements $PaymentOptionCopyWith<$Res> {
  _$PaymentOptionCopyWithImpl(this._self, this._then);

  final PaymentOption _self;
  final $Res Function(PaymentOption) _then;

/// Create a copy of PaymentOption
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? gateway = null,Object? label = null,Object? methods = null,Object? available = null,Object? paymentCurrency = null,Object? paymentAmount = null,Object? displayCurrency = null,Object? displayAmount = null,Object? notice = freezed,}) {
  return _then(PaymentOption(
gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,methods: null == methods ? _self.methods : methods // ignore: cast_nullable_to_non_nullable
as List<PaymentMethodOption>,available: null == available ? _self.available : available // ignore: cast_nullable_to_non_nullable
as bool,paymentCurrency: null == paymentCurrency ? _self.paymentCurrency : paymentCurrency // ignore: cast_nullable_to_non_nullable
as String,paymentAmount: null == paymentAmount ? _self.paymentAmount : paymentAmount // ignore: cast_nullable_to_non_nullable
as Money,displayCurrency: null == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String,displayAmount: null == displayAmount ? _self.displayAmount : displayAmount // ignore: cast_nullable_to_non_nullable
as Money,notice: freezed == notice ? _self.notice : notice // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [PaymentOption].
extension PaymentOptionPatterns on PaymentOption {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PaymentOption value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PaymentOption() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PaymentOption value)  $default,){
final _that = this;
switch (_that) {
case _PaymentOption():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PaymentOption value)?  $default,){
final _that = this;
switch (_that) {
case _PaymentOption() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String gateway,  String label,  List<PaymentMethodOption> methods,  bool available,  String paymentCurrency,  Money paymentAmount,  String displayCurrency,  Money displayAmount,  String? notice)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PaymentOption() when $default != null:
return $default(_that.gateway,_that.label,_that.methods,_that.available,_that.paymentCurrency,_that.paymentAmount,_that.displayCurrency,_that.displayAmount,_that.notice);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String gateway,  String label,  List<PaymentMethodOption> methods,  bool available,  String paymentCurrency,  Money paymentAmount,  String displayCurrency,  Money displayAmount,  String? notice)  $default,) {final _that = this;
switch (_that) {
case _PaymentOption():
return $default(_that.gateway,_that.label,_that.methods,_that.available,_that.paymentCurrency,_that.paymentAmount,_that.displayCurrency,_that.displayAmount,_that.notice);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String gateway,  String label,  List<PaymentMethodOption> methods,  bool available,  String paymentCurrency,  Money paymentAmount,  String displayCurrency,  Money displayAmount,  String? notice)?  $default,) {final _that = this;
switch (_that) {
case _PaymentOption() when $default != null:
return $default(_that.gateway,_that.label,_that.methods,_that.available,_that.paymentCurrency,_that.paymentAmount,_that.displayCurrency,_that.displayAmount,_that.notice);case _:
  return null;

}
}

}

/// @nodoc


class _PaymentOption implements PaymentOption {
  const _PaymentOption({required this.gateway, required this.label, required  List<PaymentMethodOption> methods, required this.available, required this.paymentCurrency, required this.paymentAmount, required this.displayCurrency, required this.displayAmount, this.notice}): _methods = methods;
  

@override final  String gateway;
@override final  String label;
 final  List<PaymentMethodOption> _methods;
@override List<PaymentMethodOption> get methods {
  if (_methods is EqualUnmodifiableListView) return _methods;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_methods);
}

@override final  bool available;
@override final  String paymentCurrency;
@override final  Money paymentAmount;
@override final  String displayCurrency;
@override final  Money displayAmount;
/// null, `charged_in_payment_currency` (show it) or `payment_currency_below_margin`.
@override final  String? notice;

/// Create a copy of PaymentOption
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PaymentOptionCopyWith<_PaymentOption> get copyWith => __$PaymentOptionCopyWithImpl<_PaymentOption>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PaymentOption&&(identical(other.gateway, gateway) || other.gateway == gateway)&&(identical(other.label, label) || other.label == label)&&const DeepCollectionEquality().equals(other.methods, _methods)&&(identical(other.available, available) || other.available == available)&&(identical(other.paymentCurrency, paymentCurrency) || other.paymentCurrency == paymentCurrency)&&(identical(other.paymentAmount, paymentAmount) || other.paymentAmount == paymentAmount)&&(identical(other.displayCurrency, displayCurrency) || other.displayCurrency == displayCurrency)&&(identical(other.displayAmount, displayAmount) || other.displayAmount == displayAmount)&&(identical(other.notice, notice) || other.notice == notice));
}


@override
int get hashCode {
    return Object.hash(runtimeType,gateway,label,const DeepCollectionEquality().hash(_methods),available,paymentCurrency,paymentAmount,displayCurrency,displayAmount,notice);
}

@override
String toString() {
    return 'PaymentOption(gateway: $gateway, label: $label, methods: $methods, available: $available, paymentCurrency: $paymentCurrency, paymentAmount: $paymentAmount, displayCurrency: $displayCurrency, displayAmount: $displayAmount, notice: $notice)';
}


}

/// @nodoc
abstract mixin class _$PaymentOptionCopyWith<$Res> implements $PaymentOptionCopyWith<$Res> {
  factory _$PaymentOptionCopyWith(_PaymentOption value, $Res Function(_PaymentOption) _then) = __$PaymentOptionCopyWithImpl;
@override @useResult
$Res call({
 String gateway, String label, List<PaymentMethodOption> methods, bool available, String paymentCurrency, Money paymentAmount, String displayCurrency, Money displayAmount, String? notice
});




}
/// @nodoc
class __$PaymentOptionCopyWithImpl<$Res>
    implements _$PaymentOptionCopyWith<$Res> {
  __$PaymentOptionCopyWithImpl(this._self, this._then);

  final _PaymentOption _self;
  final $Res Function(_PaymentOption) _then;

/// Create a copy of PaymentOption
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? gateway = null,Object? label = null,Object? methods = null,Object? available = null,Object? paymentCurrency = null,Object? paymentAmount = null,Object? displayCurrency = null,Object? displayAmount = null,Object? notice = freezed,}) {
  return _then(_PaymentOption(
gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,methods: null == methods ? _self._methods : methods // ignore: cast_nullable_to_non_nullable
as List<PaymentMethodOption>,available: null == available ? _self.available : available // ignore: cast_nullable_to_non_nullable
as bool,paymentCurrency: null == paymentCurrency ? _self.paymentCurrency : paymentCurrency // ignore: cast_nullable_to_non_nullable
as String,paymentAmount: null == paymentAmount ? _self.paymentAmount : paymentAmount // ignore: cast_nullable_to_non_nullable
as Money,displayCurrency: null == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String,displayAmount: null == displayAmount ? _self.displayAmount : displayAmount // ignore: cast_nullable_to_non_nullable
as Money,notice: freezed == notice ? _self.notice : notice // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc
mixin _$PaymentMethodOption {

 String get code; String get label;
/// Create a copy of PaymentMethodOption
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PaymentMethodOptionCopyWith<PaymentMethodOption> get copyWith => _$PaymentMethodOptionCopyWithImpl<PaymentMethodOption>(this as PaymentMethodOption, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as PaymentMethodOption;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PaymentMethodOption&&(identical(other.code, _this.code) || other.code == _this.code)&&(identical(other.label, _this.label) || other.label == _this.label));
}


@override
int get hashCode {
  final _this = this as PaymentMethodOption;
  return Object.hash(runtimeType,_this.code,_this.label);
}

@override
String toString() {
  final _this = this as PaymentMethodOption;
  return 'PaymentMethodOption(code: ${_this.code}, label: ${_this.label})';
}


}

/// @nodoc
abstract mixin class $PaymentMethodOptionCopyWith<$Res>  {
  factory $PaymentMethodOptionCopyWith(PaymentMethodOption value, $Res Function(PaymentMethodOption) _then) = _$PaymentMethodOptionCopyWithImpl;
@useResult
$Res call({
 String code, String label
});




}
/// @nodoc
class _$PaymentMethodOptionCopyWithImpl<$Res>
    implements $PaymentMethodOptionCopyWith<$Res> {
  _$PaymentMethodOptionCopyWithImpl(this._self, this._then);

  final PaymentMethodOption _self;
  final $Res Function(PaymentMethodOption) _then;

/// Create a copy of PaymentMethodOption
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? code = null,Object? label = null,}) {
  return _then(PaymentMethodOption(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [PaymentMethodOption].
extension PaymentMethodOptionPatterns on PaymentMethodOption {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PaymentMethodOption value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PaymentMethodOption() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PaymentMethodOption value)  $default,){
final _that = this;
switch (_that) {
case _PaymentMethodOption():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PaymentMethodOption value)?  $default,){
final _that = this;
switch (_that) {
case _PaymentMethodOption() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String code,  String label)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PaymentMethodOption() when $default != null:
return $default(_that.code,_that.label);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String code,  String label)  $default,) {final _that = this;
switch (_that) {
case _PaymentMethodOption():
return $default(_that.code,_that.label);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String code,  String label)?  $default,) {final _that = this;
switch (_that) {
case _PaymentMethodOption() when $default != null:
return $default(_that.code,_that.label);case _:
  return null;

}
}

}

/// @nodoc


class _PaymentMethodOption implements PaymentMethodOption {
  const _PaymentMethodOption({required this.code, required this.label});
  

@override final  String code;
@override final  String label;

/// Create a copy of PaymentMethodOption
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PaymentMethodOptionCopyWith<_PaymentMethodOption> get copyWith => __$PaymentMethodOptionCopyWithImpl<_PaymentMethodOption>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PaymentMethodOption&&(identical(other.code, code) || other.code == code)&&(identical(other.label, label) || other.label == label));
}


@override
int get hashCode {
    return Object.hash(runtimeType,code,label);
}

@override
String toString() {
    return 'PaymentMethodOption(code: $code, label: $label)';
}


}

/// @nodoc
abstract mixin class _$PaymentMethodOptionCopyWith<$Res> implements $PaymentMethodOptionCopyWith<$Res> {
  factory _$PaymentMethodOptionCopyWith(_PaymentMethodOption value, $Res Function(_PaymentMethodOption) _then) = __$PaymentMethodOptionCopyWithImpl;
@override @useResult
$Res call({
 String code, String label
});




}
/// @nodoc
class __$PaymentMethodOptionCopyWithImpl<$Res>
    implements _$PaymentMethodOptionCopyWith<$Res> {
  __$PaymentMethodOptionCopyWithImpl(this._self, this._then);

  final _PaymentMethodOption _self;
  final $Res Function(_PaymentMethodOption) _then;

/// Create a copy of PaymentMethodOption
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? code = null,Object? label = null,}) {
  return _then(_PaymentMethodOption(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc
mixin _$PlaceOrderRequest {

 String get country; String get gateway; String get currency; String get expectedTotal; bool get acceptTerms;/// Guests only; customers use their account email.
 String? get email; String? get method; bool? get marketingConsent; String? get gaClientId;
/// Create a copy of PlaceOrderRequest
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PlaceOrderRequestCopyWith<PlaceOrderRequest> get copyWith => _$PlaceOrderRequestCopyWithImpl<PlaceOrderRequest>(this as PlaceOrderRequest, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as PlaceOrderRequest;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PlaceOrderRequest&&(identical(other.country, _this.country) || other.country == _this.country)&&(identical(other.gateway, _this.gateway) || other.gateway == _this.gateway)&&(identical(other.currency, _this.currency) || other.currency == _this.currency)&&(identical(other.expectedTotal, _this.expectedTotal) || other.expectedTotal == _this.expectedTotal)&&(identical(other.acceptTerms, _this.acceptTerms) || other.acceptTerms == _this.acceptTerms)&&(identical(other.email, _this.email) || other.email == _this.email)&&(identical(other.method, _this.method) || other.method == _this.method)&&(identical(other.marketingConsent, _this.marketingConsent) || other.marketingConsent == _this.marketingConsent)&&(identical(other.gaClientId, _this.gaClientId) || other.gaClientId == _this.gaClientId));
}


@override
int get hashCode {
  final _this = this as PlaceOrderRequest;
  return Object.hash(runtimeType,_this.country,_this.gateway,_this.currency,_this.expectedTotal,_this.acceptTerms,_this.email,_this.method,_this.marketingConsent,_this.gaClientId);
}

@override
String toString() {
  final _this = this as PlaceOrderRequest;
  return 'PlaceOrderRequest(country: ${_this.country}, gateway: ${_this.gateway}, currency: ${_this.currency}, expectedTotal: ${_this.expectedTotal}, acceptTerms: ${_this.acceptTerms}, email: ${_this.email}, method: ${_this.method}, marketingConsent: ${_this.marketingConsent}, gaClientId: ${_this.gaClientId})';
}


}

/// @nodoc
abstract mixin class $PlaceOrderRequestCopyWith<$Res>  {
  factory $PlaceOrderRequestCopyWith(PlaceOrderRequest value, $Res Function(PlaceOrderRequest) _then) = _$PlaceOrderRequestCopyWithImpl;
@useResult
$Res call({
 String country, String gateway, String currency, String expectedTotal, bool acceptTerms, String? email, String? method, bool? marketingConsent, String? gaClientId
});




}
/// @nodoc
class _$PlaceOrderRequestCopyWithImpl<$Res>
    implements $PlaceOrderRequestCopyWith<$Res> {
  _$PlaceOrderRequestCopyWithImpl(this._self, this._then);

  final PlaceOrderRequest _self;
  final $Res Function(PlaceOrderRequest) _then;

/// Create a copy of PlaceOrderRequest
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? country = null,Object? gateway = null,Object? currency = null,Object? expectedTotal = null,Object? acceptTerms = null,Object? email = freezed,Object? method = freezed,Object? marketingConsent = freezed,Object? gaClientId = freezed,}) {
  return _then(PlaceOrderRequest(
country: null == country ? _self.country : country // ignore: cast_nullable_to_non_nullable
as String,gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,currency: null == currency ? _self.currency : currency // ignore: cast_nullable_to_non_nullable
as String,expectedTotal: null == expectedTotal ? _self.expectedTotal : expectedTotal // ignore: cast_nullable_to_non_nullable
as String,acceptTerms: null == acceptTerms ? _self.acceptTerms : acceptTerms // ignore: cast_nullable_to_non_nullable
as bool,email: freezed == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String?,method: freezed == method ? _self.method : method // ignore: cast_nullable_to_non_nullable
as String?,marketingConsent: freezed == marketingConsent ? _self.marketingConsent : marketingConsent // ignore: cast_nullable_to_non_nullable
as bool?,gaClientId: freezed == gaClientId ? _self.gaClientId : gaClientId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [PlaceOrderRequest].
extension PlaceOrderRequestPatterns on PlaceOrderRequest {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PlaceOrderRequest value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PlaceOrderRequest() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PlaceOrderRequest value)  $default,){
final _that = this;
switch (_that) {
case _PlaceOrderRequest():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PlaceOrderRequest value)?  $default,){
final _that = this;
switch (_that) {
case _PlaceOrderRequest() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String country,  String gateway,  String currency,  String expectedTotal,  bool acceptTerms,  String? email,  String? method,  bool? marketingConsent,  String? gaClientId)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PlaceOrderRequest() when $default != null:
return $default(_that.country,_that.gateway,_that.currency,_that.expectedTotal,_that.acceptTerms,_that.email,_that.method,_that.marketingConsent,_that.gaClientId);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String country,  String gateway,  String currency,  String expectedTotal,  bool acceptTerms,  String? email,  String? method,  bool? marketingConsent,  String? gaClientId)  $default,) {final _that = this;
switch (_that) {
case _PlaceOrderRequest():
return $default(_that.country,_that.gateway,_that.currency,_that.expectedTotal,_that.acceptTerms,_that.email,_that.method,_that.marketingConsent,_that.gaClientId);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String country,  String gateway,  String currency,  String expectedTotal,  bool acceptTerms,  String? email,  String? method,  bool? marketingConsent,  String? gaClientId)?  $default,) {final _that = this;
switch (_that) {
case _PlaceOrderRequest() when $default != null:
return $default(_that.country,_that.gateway,_that.currency,_that.expectedTotal,_that.acceptTerms,_that.email,_that.method,_that.marketingConsent,_that.gaClientId);case _:
  return null;

}
}

}

/// @nodoc


class _PlaceOrderRequest extends PlaceOrderRequest {
  const _PlaceOrderRequest({required this.country, required this.gateway, required this.currency, required this.expectedTotal, required this.acceptTerms, this.email, this.method, this.marketingConsent, this.gaClientId}): super._();
  

@override final  String country;
@override final  String gateway;
@override final  String currency;
@override final  String expectedTotal;
@override final  bool acceptTerms;
/// Guests only; customers use their account email.
@override final  String? email;
@override final  String? method;
@override final  bool? marketingConsent;
@override final  String? gaClientId;

/// Create a copy of PlaceOrderRequest
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PlaceOrderRequestCopyWith<_PlaceOrderRequest> get copyWith => __$PlaceOrderRequestCopyWithImpl<_PlaceOrderRequest>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PlaceOrderRequest&&(identical(other.country, country) || other.country == country)&&(identical(other.gateway, gateway) || other.gateway == gateway)&&(identical(other.currency, currency) || other.currency == currency)&&(identical(other.expectedTotal, expectedTotal) || other.expectedTotal == expectedTotal)&&(identical(other.acceptTerms, acceptTerms) || other.acceptTerms == acceptTerms)&&(identical(other.email, email) || other.email == email)&&(identical(other.method, method) || other.method == method)&&(identical(other.marketingConsent, marketingConsent) || other.marketingConsent == marketingConsent)&&(identical(other.gaClientId, gaClientId) || other.gaClientId == gaClientId));
}


@override
int get hashCode {
    return Object.hash(runtimeType,country,gateway,currency,expectedTotal,acceptTerms,email,method,marketingConsent,gaClientId);
}

@override
String toString() {
    return 'PlaceOrderRequest(country: $country, gateway: $gateway, currency: $currency, expectedTotal: $expectedTotal, acceptTerms: $acceptTerms, email: $email, method: $method, marketingConsent: $marketingConsent, gaClientId: $gaClientId)';
}


}

/// @nodoc
abstract mixin class _$PlaceOrderRequestCopyWith<$Res> implements $PlaceOrderRequestCopyWith<$Res> {
  factory _$PlaceOrderRequestCopyWith(_PlaceOrderRequest value, $Res Function(_PlaceOrderRequest) _then) = __$PlaceOrderRequestCopyWithImpl;
@override @useResult
$Res call({
 String country, String gateway, String currency, String expectedTotal, bool acceptTerms, String? email, String? method, bool? marketingConsent, String? gaClientId
});




}
/// @nodoc
class __$PlaceOrderRequestCopyWithImpl<$Res>
    implements _$PlaceOrderRequestCopyWith<$Res> {
  __$PlaceOrderRequestCopyWithImpl(this._self, this._then);

  final _PlaceOrderRequest _self;
  final $Res Function(_PlaceOrderRequest) _then;

/// Create a copy of PlaceOrderRequest
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? country = null,Object? gateway = null,Object? currency = null,Object? expectedTotal = null,Object? acceptTerms = null,Object? email = freezed,Object? method = freezed,Object? marketingConsent = freezed,Object? gaClientId = freezed,}) {
  return _then(_PlaceOrderRequest(
country: null == country ? _self.country : country // ignore: cast_nullable_to_non_nullable
as String,gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,currency: null == currency ? _self.currency : currency // ignore: cast_nullable_to_non_nullable
as String,expectedTotal: null == expectedTotal ? _self.expectedTotal : expectedTotal // ignore: cast_nullable_to_non_nullable
as String,acceptTerms: null == acceptTerms ? _self.acceptTerms : acceptTerms // ignore: cast_nullable_to_non_nullable
as bool,email: freezed == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String?,method: freezed == method ? _self.method : method // ignore: cast_nullable_to_non_nullable
as String?,marketingConsent: freezed == marketingConsent ? _self.marketingConsent : marketingConsent // ignore: cast_nullable_to_non_nullable
as bool?,gaClientId: freezed == gaClientId ? _self.gaClientId : gaClientId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc
mixin _$PlacedOrder {

 String get publicId; String get status; String? get orderNumber; Money? get displayTotal; Money? get paymentTotal; DateTime? get expiresAt;
/// Create a copy of PlacedOrder
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PlacedOrderCopyWith<PlacedOrder> get copyWith => _$PlacedOrderCopyWithImpl<PlacedOrder>(this as PlacedOrder, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as PlacedOrder;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PlacedOrder&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.orderNumber, _this.orderNumber) || other.orderNumber == _this.orderNumber)&&(identical(other.displayTotal, _this.displayTotal) || other.displayTotal == _this.displayTotal)&&(identical(other.paymentTotal, _this.paymentTotal) || other.paymentTotal == _this.paymentTotal)&&(identical(other.expiresAt, _this.expiresAt) || other.expiresAt == _this.expiresAt));
}


@override
int get hashCode {
  final _this = this as PlacedOrder;
  return Object.hash(runtimeType,_this.publicId,_this.status,_this.orderNumber,_this.displayTotal,_this.paymentTotal,_this.expiresAt);
}

@override
String toString() {
  final _this = this as PlacedOrder;
  return 'PlacedOrder(publicId: ${_this.publicId}, status: ${_this.status}, orderNumber: ${_this.orderNumber}, displayTotal: ${_this.displayTotal}, paymentTotal: ${_this.paymentTotal}, expiresAt: ${_this.expiresAt})';
}


}

/// @nodoc
abstract mixin class $PlacedOrderCopyWith<$Res>  {
  factory $PlacedOrderCopyWith(PlacedOrder value, $Res Function(PlacedOrder) _then) = _$PlacedOrderCopyWithImpl;
@useResult
$Res call({
 String publicId, String status, String? orderNumber, Money? displayTotal, Money? paymentTotal, DateTime? expiresAt
});




}
/// @nodoc
class _$PlacedOrderCopyWithImpl<$Res>
    implements $PlacedOrderCopyWith<$Res> {
  _$PlacedOrderCopyWithImpl(this._self, this._then);

  final PlacedOrder _self;
  final $Res Function(PlacedOrder) _then;

/// Create a copy of PlacedOrder
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? status = null,Object? orderNumber = freezed,Object? displayTotal = freezed,Object? paymentTotal = freezed,Object? expiresAt = freezed,}) {
  return _then(PlacedOrder(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,orderNumber: freezed == orderNumber ? _self.orderNumber : orderNumber // ignore: cast_nullable_to_non_nullable
as String?,displayTotal: freezed == displayTotal ? _self.displayTotal : displayTotal // ignore: cast_nullable_to_non_nullable
as Money?,paymentTotal: freezed == paymentTotal ? _self.paymentTotal : paymentTotal // ignore: cast_nullable_to_non_nullable
as Money?,expiresAt: freezed == expiresAt ? _self.expiresAt : expiresAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [PlacedOrder].
extension PlacedOrderPatterns on PlacedOrder {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PlacedOrder value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PlacedOrder() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PlacedOrder value)  $default,){
final _that = this;
switch (_that) {
case _PlacedOrder():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PlacedOrder value)?  $default,){
final _that = this;
switch (_that) {
case _PlacedOrder() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String status,  String? orderNumber,  Money? displayTotal,  Money? paymentTotal,  DateTime? expiresAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PlacedOrder() when $default != null:
return $default(_that.publicId,_that.status,_that.orderNumber,_that.displayTotal,_that.paymentTotal,_that.expiresAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String status,  String? orderNumber,  Money? displayTotal,  Money? paymentTotal,  DateTime? expiresAt)  $default,) {final _that = this;
switch (_that) {
case _PlacedOrder():
return $default(_that.publicId,_that.status,_that.orderNumber,_that.displayTotal,_that.paymentTotal,_that.expiresAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String status,  String? orderNumber,  Money? displayTotal,  Money? paymentTotal,  DateTime? expiresAt)?  $default,) {final _that = this;
switch (_that) {
case _PlacedOrder() when $default != null:
return $default(_that.publicId,_that.status,_that.orderNumber,_that.displayTotal,_that.paymentTotal,_that.expiresAt);case _:
  return null;

}
}

}

/// @nodoc


class _PlacedOrder implements PlacedOrder {
  const _PlacedOrder({required this.publicId, required this.status, this.orderNumber, this.displayTotal, this.paymentTotal, this.expiresAt});
  

@override final  String publicId;
@override final  String status;
@override final  String? orderNumber;
@override final  Money? displayTotal;
@override final  Money? paymentTotal;
@override final  DateTime? expiresAt;

/// Create a copy of PlacedOrder
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PlacedOrderCopyWith<_PlacedOrder> get copyWith => __$PlacedOrderCopyWithImpl<_PlacedOrder>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PlacedOrder&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.status, status) || other.status == status)&&(identical(other.orderNumber, orderNumber) || other.orderNumber == orderNumber)&&(identical(other.displayTotal, displayTotal) || other.displayTotal == displayTotal)&&(identical(other.paymentTotal, paymentTotal) || other.paymentTotal == paymentTotal)&&(identical(other.expiresAt, expiresAt) || other.expiresAt == expiresAt));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,status,orderNumber,displayTotal,paymentTotal,expiresAt);
}

@override
String toString() {
    return 'PlacedOrder(publicId: $publicId, status: $status, orderNumber: $orderNumber, displayTotal: $displayTotal, paymentTotal: $paymentTotal, expiresAt: $expiresAt)';
}


}

/// @nodoc
abstract mixin class _$PlacedOrderCopyWith<$Res> implements $PlacedOrderCopyWith<$Res> {
  factory _$PlacedOrderCopyWith(_PlacedOrder value, $Res Function(_PlacedOrder) _then) = __$PlacedOrderCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String status, String? orderNumber, Money? displayTotal, Money? paymentTotal, DateTime? expiresAt
});




}
/// @nodoc
class __$PlacedOrderCopyWithImpl<$Res>
    implements _$PlacedOrderCopyWith<$Res> {
  __$PlacedOrderCopyWithImpl(this._self, this._then);

  final _PlacedOrder _self;
  final $Res Function(_PlacedOrder) _then;

/// Create a copy of PlacedOrder
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? status = null,Object? orderNumber = freezed,Object? displayTotal = freezed,Object? paymentTotal = freezed,Object? expiresAt = freezed,}) {
  return _then(_PlacedOrder(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,orderNumber: freezed == orderNumber ? _self.orderNumber : orderNumber // ignore: cast_nullable_to_non_nullable
as String?,displayTotal: freezed == displayTotal ? _self.displayTotal : displayTotal // ignore: cast_nullable_to_non_nullable
as Money?,paymentTotal: freezed == paymentTotal ? _self.paymentTotal : paymentTotal // ignore: cast_nullable_to_non_nullable
as Money?,expiresAt: freezed == expiresAt ? _self.expiresAt : expiresAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}

/// @nodoc
mixin _$PlaceOrderResponse {

 PlacedOrder get order;/// The same buyer already has an open order with the same contents: go to its status.
 bool get duplicate;/// The server replayed a stored response (`Idempotency-Replayed: true`).
 bool get replayed;/// Null when no attempt was started (replay, or the provider was down): call retry-payment.
 PaymentNext? get next;
/// Create a copy of PlaceOrderResponse
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PlaceOrderResponseCopyWith<PlaceOrderResponse> get copyWith => _$PlaceOrderResponseCopyWithImpl<PlaceOrderResponse>(this as PlaceOrderResponse, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as PlaceOrderResponse;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PlaceOrderResponse&&(identical(other.order, _this.order) || other.order == _this.order)&&(identical(other.duplicate, _this.duplicate) || other.duplicate == _this.duplicate)&&(identical(other.replayed, _this.replayed) || other.replayed == _this.replayed)&&(identical(other.next, _this.next) || other.next == _this.next));
}


@override
int get hashCode {
  final _this = this as PlaceOrderResponse;
  return Object.hash(runtimeType,_this.order,_this.duplicate,_this.replayed,_this.next);
}

@override
String toString() {
  final _this = this as PlaceOrderResponse;
  return 'PlaceOrderResponse(order: ${_this.order}, duplicate: ${_this.duplicate}, replayed: ${_this.replayed}, next: ${_this.next})';
}


}

/// @nodoc
abstract mixin class $PlaceOrderResponseCopyWith<$Res>  {
  factory $PlaceOrderResponseCopyWith(PlaceOrderResponse value, $Res Function(PlaceOrderResponse) _then) = _$PlaceOrderResponseCopyWithImpl;
@useResult
$Res call({
 PlacedOrder order, bool duplicate, bool replayed, PaymentNext? next
});


$PlacedOrderCopyWith<$Res> get order;$PaymentNextCopyWith<$Res>? get next;

}
/// @nodoc
class _$PlaceOrderResponseCopyWithImpl<$Res>
    implements $PlaceOrderResponseCopyWith<$Res> {
  _$PlaceOrderResponseCopyWithImpl(this._self, this._then);

  final PlaceOrderResponse _self;
  final $Res Function(PlaceOrderResponse) _then;

/// Create a copy of PlaceOrderResponse
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? order = null,Object? duplicate = null,Object? replayed = null,Object? next = freezed,}) {
  return _then(PlaceOrderResponse(
order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as PlacedOrder,duplicate: null == duplicate ? _self.duplicate : duplicate // ignore: cast_nullable_to_non_nullable
as bool,replayed: null == replayed ? _self.replayed : replayed // ignore: cast_nullable_to_non_nullable
as bool,next: freezed == next ? _self.next : next // ignore: cast_nullable_to_non_nullable
as PaymentNext?,
  ));
}
/// Create a copy of PlaceOrderResponse
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PlacedOrderCopyWith<$Res> get order {
  
  return $PlacedOrderCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}/// Create a copy of PlaceOrderResponse
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PaymentNextCopyWith<$Res>? get next {
    if (_self.next == null) {
    return null;
  }

  return $PaymentNextCopyWith<$Res>(_self.next!, (value) {
    return _then(_self.copyWith(next: value));
  });
}
}


/// Adds pattern-matching-related methods to [PlaceOrderResponse].
extension PlaceOrderResponsePatterns on PlaceOrderResponse {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PlaceOrderResponse value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PlaceOrderResponse() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PlaceOrderResponse value)  $default,){
final _that = this;
switch (_that) {
case _PlaceOrderResponse():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PlaceOrderResponse value)?  $default,){
final _that = this;
switch (_that) {
case _PlaceOrderResponse() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( PlacedOrder order,  bool duplicate,  bool replayed,  PaymentNext? next)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PlaceOrderResponse() when $default != null:
return $default(_that.order,_that.duplicate,_that.replayed,_that.next);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( PlacedOrder order,  bool duplicate,  bool replayed,  PaymentNext? next)  $default,) {final _that = this;
switch (_that) {
case _PlaceOrderResponse():
return $default(_that.order,_that.duplicate,_that.replayed,_that.next);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( PlacedOrder order,  bool duplicate,  bool replayed,  PaymentNext? next)?  $default,) {final _that = this;
switch (_that) {
case _PlaceOrderResponse() when $default != null:
return $default(_that.order,_that.duplicate,_that.replayed,_that.next);case _:
  return null;

}
}

}

/// @nodoc


class _PlaceOrderResponse implements PlaceOrderResponse {
  const _PlaceOrderResponse({required this.order, required this.duplicate, required this.replayed, this.next});
  

@override final  PlacedOrder order;
/// The same buyer already has an open order with the same contents: go to its status.
@override final  bool duplicate;
/// The server replayed a stored response (`Idempotency-Replayed: true`).
@override final  bool replayed;
/// Null when no attempt was started (replay, or the provider was down): call retry-payment.
@override final  PaymentNext? next;

/// Create a copy of PlaceOrderResponse
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PlaceOrderResponseCopyWith<_PlaceOrderResponse> get copyWith => __$PlaceOrderResponseCopyWithImpl<_PlaceOrderResponse>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PlaceOrderResponse&&(identical(other.order, order) || other.order == order)&&(identical(other.duplicate, duplicate) || other.duplicate == duplicate)&&(identical(other.replayed, replayed) || other.replayed == replayed)&&(identical(other.next, next) || other.next == next));
}


@override
int get hashCode {
    return Object.hash(runtimeType,order,duplicate,replayed,next);
}

@override
String toString() {
    return 'PlaceOrderResponse(order: $order, duplicate: $duplicate, replayed: $replayed, next: $next)';
}


}

/// @nodoc
abstract mixin class _$PlaceOrderResponseCopyWith<$Res> implements $PlaceOrderResponseCopyWith<$Res> {
  factory _$PlaceOrderResponseCopyWith(_PlaceOrderResponse value, $Res Function(_PlaceOrderResponse) _then) = __$PlaceOrderResponseCopyWithImpl;
@override @useResult
$Res call({
 PlacedOrder order, bool duplicate, bool replayed, PaymentNext? next
});


@override $PlacedOrderCopyWith<$Res> get order;@override $PaymentNextCopyWith<$Res>? get next;

}
/// @nodoc
class __$PlaceOrderResponseCopyWithImpl<$Res>
    implements _$PlaceOrderResponseCopyWith<$Res> {
  __$PlaceOrderResponseCopyWithImpl(this._self, this._then);

  final _PlaceOrderResponse _self;
  final $Res Function(_PlaceOrderResponse) _then;

/// Create a copy of PlaceOrderResponse
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? order = null,Object? duplicate = null,Object? replayed = null,Object? next = freezed,}) {
  return _then(_PlaceOrderResponse(
order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as PlacedOrder,duplicate: null == duplicate ? _self.duplicate : duplicate // ignore: cast_nullable_to_non_nullable
as bool,replayed: null == replayed ? _self.replayed : replayed // ignore: cast_nullable_to_non_nullable
as bool,next: freezed == next ? _self.next : next // ignore: cast_nullable_to_non_nullable
as PaymentNext?,
  ));
}

/// Create a copy of PlaceOrderResponse
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PlacedOrderCopyWith<$Res> get order {
  
  return $PlacedOrderCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}/// Create a copy of PlaceOrderResponse
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PaymentNextCopyWith<$Res>? get next {
    if (_self.next == null) {
    return null;
  }

  return $PaymentNextCopyWith<$Res>(_self.next!, (value) {
    return _then(_self.copyWith(next: value));
  });
}
}

/// @nodoc
mixin _$GuestLinkAccess {

 String get orderPublicId; bool get otpRequired;
/// Create a copy of GuestLinkAccess
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$GuestLinkAccessCopyWith<GuestLinkAccess> get copyWith => _$GuestLinkAccessCopyWithImpl<GuestLinkAccess>(this as GuestLinkAccess, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as GuestLinkAccess;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is GuestLinkAccess&&(identical(other.orderPublicId, _this.orderPublicId) || other.orderPublicId == _this.orderPublicId)&&(identical(other.otpRequired, _this.otpRequired) || other.otpRequired == _this.otpRequired));
}


@override
int get hashCode {
  final _this = this as GuestLinkAccess;
  return Object.hash(runtimeType,_this.orderPublicId,_this.otpRequired);
}

@override
String toString() {
  final _this = this as GuestLinkAccess;
  return 'GuestLinkAccess(orderPublicId: ${_this.orderPublicId}, otpRequired: ${_this.otpRequired})';
}


}

/// @nodoc
abstract mixin class $GuestLinkAccessCopyWith<$Res>  {
  factory $GuestLinkAccessCopyWith(GuestLinkAccess value, $Res Function(GuestLinkAccess) _then) = _$GuestLinkAccessCopyWithImpl;
@useResult
$Res call({
 String orderPublicId, bool otpRequired
});




}
/// @nodoc
class _$GuestLinkAccessCopyWithImpl<$Res>
    implements $GuestLinkAccessCopyWith<$Res> {
  _$GuestLinkAccessCopyWithImpl(this._self, this._then);

  final GuestLinkAccess _self;
  final $Res Function(GuestLinkAccess) _then;

/// Create a copy of GuestLinkAccess
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? orderPublicId = null,Object? otpRequired = null,}) {
  return _then(GuestLinkAccess(
orderPublicId: null == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String,otpRequired: null == otpRequired ? _self.otpRequired : otpRequired // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [GuestLinkAccess].
extension GuestLinkAccessPatterns on GuestLinkAccess {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _GuestLinkAccess value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _GuestLinkAccess() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _GuestLinkAccess value)  $default,){
final _that = this;
switch (_that) {
case _GuestLinkAccess():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _GuestLinkAccess value)?  $default,){
final _that = this;
switch (_that) {
case _GuestLinkAccess() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String orderPublicId,  bool otpRequired)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _GuestLinkAccess() when $default != null:
return $default(_that.orderPublicId,_that.otpRequired);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String orderPublicId,  bool otpRequired)  $default,) {final _that = this;
switch (_that) {
case _GuestLinkAccess():
return $default(_that.orderPublicId,_that.otpRequired);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String orderPublicId,  bool otpRequired)?  $default,) {final _that = this;
switch (_that) {
case _GuestLinkAccess() when $default != null:
return $default(_that.orderPublicId,_that.otpRequired);case _:
  return null;

}
}

}

/// @nodoc


class _GuestLinkAccess implements GuestLinkAccess {
  const _GuestLinkAccess({required this.orderPublicId, required this.otpRequired});
  

@override final  String orderPublicId;
@override final  bool otpRequired;

/// Create a copy of GuestLinkAccess
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$GuestLinkAccessCopyWith<_GuestLinkAccess> get copyWith => __$GuestLinkAccessCopyWithImpl<_GuestLinkAccess>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _GuestLinkAccess&&(identical(other.orderPublicId, orderPublicId) || other.orderPublicId == orderPublicId)&&(identical(other.otpRequired, otpRequired) || other.otpRequired == otpRequired));
}


@override
int get hashCode {
    return Object.hash(runtimeType,orderPublicId,otpRequired);
}

@override
String toString() {
    return 'GuestLinkAccess(orderPublicId: $orderPublicId, otpRequired: $otpRequired)';
}


}

/// @nodoc
abstract mixin class _$GuestLinkAccessCopyWith<$Res> implements $GuestLinkAccessCopyWith<$Res> {
  factory _$GuestLinkAccessCopyWith(_GuestLinkAccess value, $Res Function(_GuestLinkAccess) _then) = __$GuestLinkAccessCopyWithImpl;
@override @useResult
$Res call({
 String orderPublicId, bool otpRequired
});




}
/// @nodoc
class __$GuestLinkAccessCopyWithImpl<$Res>
    implements _$GuestLinkAccessCopyWith<$Res> {
  __$GuestLinkAccessCopyWithImpl(this._self, this._then);

  final _GuestLinkAccess _self;
  final $Res Function(_GuestLinkAccess) _then;

/// Create a copy of GuestLinkAccess
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? orderPublicId = null,Object? otpRequired = null,}) {
  return _then(_GuestLinkAccess(
orderPublicId: null == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String,otpRequired: null == otpRequired ? _self.otpRequired : otpRequired // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc
mixin _$PendingCheckout {

 String get idempotencyKey; String get bodyHash; String? get orderPublicId;
/// Create a copy of PendingCheckout
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PendingCheckoutCopyWith<PendingCheckout> get copyWith => _$PendingCheckoutCopyWithImpl<PendingCheckout>(this as PendingCheckout, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as PendingCheckout;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PendingCheckout&&(identical(other.idempotencyKey, _this.idempotencyKey) || other.idempotencyKey == _this.idempotencyKey)&&(identical(other.bodyHash, _this.bodyHash) || other.bodyHash == _this.bodyHash)&&(identical(other.orderPublicId, _this.orderPublicId) || other.orderPublicId == _this.orderPublicId));
}


@override
int get hashCode {
  final _this = this as PendingCheckout;
  return Object.hash(runtimeType,_this.idempotencyKey,_this.bodyHash,_this.orderPublicId);
}

@override
String toString() {
  final _this = this as PendingCheckout;
  return 'PendingCheckout(idempotencyKey: ${_this.idempotencyKey}, bodyHash: ${_this.bodyHash}, orderPublicId: ${_this.orderPublicId})';
}


}

/// @nodoc
abstract mixin class $PendingCheckoutCopyWith<$Res>  {
  factory $PendingCheckoutCopyWith(PendingCheckout value, $Res Function(PendingCheckout) _then) = _$PendingCheckoutCopyWithImpl;
@useResult
$Res call({
 String idempotencyKey, String bodyHash, String? orderPublicId
});




}
/// @nodoc
class _$PendingCheckoutCopyWithImpl<$Res>
    implements $PendingCheckoutCopyWith<$Res> {
  _$PendingCheckoutCopyWithImpl(this._self, this._then);

  final PendingCheckout _self;
  final $Res Function(PendingCheckout) _then;

/// Create a copy of PendingCheckout
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? idempotencyKey = null,Object? bodyHash = null,Object? orderPublicId = freezed,}) {
  return _then(PendingCheckout(
idempotencyKey: null == idempotencyKey ? _self.idempotencyKey : idempotencyKey // ignore: cast_nullable_to_non_nullable
as String,bodyHash: null == bodyHash ? _self.bodyHash : bodyHash // ignore: cast_nullable_to_non_nullable
as String,orderPublicId: freezed == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [PendingCheckout].
extension PendingCheckoutPatterns on PendingCheckout {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PendingCheckout value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PendingCheckout() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PendingCheckout value)  $default,){
final _that = this;
switch (_that) {
case _PendingCheckout():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PendingCheckout value)?  $default,){
final _that = this;
switch (_that) {
case _PendingCheckout() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String idempotencyKey,  String bodyHash,  String? orderPublicId)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PendingCheckout() when $default != null:
return $default(_that.idempotencyKey,_that.bodyHash,_that.orderPublicId);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String idempotencyKey,  String bodyHash,  String? orderPublicId)  $default,) {final _that = this;
switch (_that) {
case _PendingCheckout():
return $default(_that.idempotencyKey,_that.bodyHash,_that.orderPublicId);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String idempotencyKey,  String bodyHash,  String? orderPublicId)?  $default,) {final _that = this;
switch (_that) {
case _PendingCheckout() when $default != null:
return $default(_that.idempotencyKey,_that.bodyHash,_that.orderPublicId);case _:
  return null;

}
}

}

/// @nodoc


class _PendingCheckout implements PendingCheckout {
  const _PendingCheckout({required this.idempotencyKey, required this.bodyHash, this.orderPublicId});
  

@override final  String idempotencyKey;
@override final  String bodyHash;
@override final  String? orderPublicId;

/// Create a copy of PendingCheckout
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PendingCheckoutCopyWith<_PendingCheckout> get copyWith => __$PendingCheckoutCopyWithImpl<_PendingCheckout>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PendingCheckout&&(identical(other.idempotencyKey, idempotencyKey) || other.idempotencyKey == idempotencyKey)&&(identical(other.bodyHash, bodyHash) || other.bodyHash == bodyHash)&&(identical(other.orderPublicId, orderPublicId) || other.orderPublicId == orderPublicId));
}


@override
int get hashCode {
    return Object.hash(runtimeType,idempotencyKey,bodyHash,orderPublicId);
}

@override
String toString() {
    return 'PendingCheckout(idempotencyKey: $idempotencyKey, bodyHash: $bodyHash, orderPublicId: $orderPublicId)';
}


}

/// @nodoc
abstract mixin class _$PendingCheckoutCopyWith<$Res> implements $PendingCheckoutCopyWith<$Res> {
  factory _$PendingCheckoutCopyWith(_PendingCheckout value, $Res Function(_PendingCheckout) _then) = __$PendingCheckoutCopyWithImpl;
@override @useResult
$Res call({
 String idempotencyKey, String bodyHash, String? orderPublicId
});




}
/// @nodoc
class __$PendingCheckoutCopyWithImpl<$Res>
    implements _$PendingCheckoutCopyWith<$Res> {
  __$PendingCheckoutCopyWithImpl(this._self, this._then);

  final _PendingCheckout _self;
  final $Res Function(_PendingCheckout) _then;

/// Create a copy of PendingCheckout
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? idempotencyKey = null,Object? bodyHash = null,Object? orderPublicId = freezed,}) {
  return _then(_PendingCheckout(
idempotencyKey: null == idempotencyKey ? _self.idempotencyKey : idempotencyKey // ignore: cast_nullable_to_non_nullable
as String,bodyHash: null == bodyHash ? _self.bodyHash : bodyHash // ignore: cast_nullable_to_non_nullable
as String,orderPublicId: freezed == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

// dart format on
