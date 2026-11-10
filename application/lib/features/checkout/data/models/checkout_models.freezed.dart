// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'checkout_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$PaymentMethodDto {

 String get code; String get label;
/// Create a copy of PaymentMethodDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PaymentMethodDtoCopyWith<PaymentMethodDto> get copyWith => _$PaymentMethodDtoCopyWithImpl<PaymentMethodDto>(this as PaymentMethodDto, _$identity);

  /// Serializes this PaymentMethodDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as PaymentMethodDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PaymentMethodDto&&(identical(other.code, _this.code) || other.code == _this.code)&&(identical(other.label, _this.label) || other.label == _this.label));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as PaymentMethodDto;
  return Object.hash(runtimeType,_this.code,_this.label);
}

@override
String toString() {
  final _this = this as PaymentMethodDto;
  return 'PaymentMethodDto(code: ${_this.code}, label: ${_this.label})';
}


}

/// @nodoc
abstract mixin class $PaymentMethodDtoCopyWith<$Res>  {
  factory $PaymentMethodDtoCopyWith(PaymentMethodDto value, $Res Function(PaymentMethodDto) _then) = _$PaymentMethodDtoCopyWithImpl;
@useResult
$Res call({
 String code, String label
});




}
/// @nodoc
class _$PaymentMethodDtoCopyWithImpl<$Res>
    implements $PaymentMethodDtoCopyWith<$Res> {
  _$PaymentMethodDtoCopyWithImpl(this._self, this._then);

  final PaymentMethodDto _self;
  final $Res Function(PaymentMethodDto) _then;

/// Create a copy of PaymentMethodDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? code = null,Object? label = null,}) {
  return _then(PaymentMethodDto(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [PaymentMethodDto].
extension PaymentMethodDtoPatterns on PaymentMethodDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PaymentMethodDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PaymentMethodDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PaymentMethodDto value)  $default,){
final _that = this;
switch (_that) {
case _PaymentMethodDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PaymentMethodDto value)?  $default,){
final _that = this;
switch (_that) {
case _PaymentMethodDto() when $default != null:
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
case _PaymentMethodDto() when $default != null:
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
case _PaymentMethodDto():
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
case _PaymentMethodDto() when $default != null:
return $default(_that.code,_that.label);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _PaymentMethodDto implements PaymentMethodDto {
  const _PaymentMethodDto({required this.code, required this.label});
  factory _PaymentMethodDto.fromJson(Map<String, dynamic> json) => _$PaymentMethodDtoFromJson(json);

@override final  String code;
@override final  String label;

/// Create a copy of PaymentMethodDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PaymentMethodDtoCopyWith<_PaymentMethodDto> get copyWith => __$PaymentMethodDtoCopyWithImpl<_PaymentMethodDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$PaymentMethodDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PaymentMethodDto&&(identical(other.code, code) || other.code == code)&&(identical(other.label, label) || other.label == label));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,code,label);
}

@override
String toString() {
    return 'PaymentMethodDto(code: $code, label: $label)';
}


}

/// @nodoc
abstract mixin class _$PaymentMethodDtoCopyWith<$Res> implements $PaymentMethodDtoCopyWith<$Res> {
  factory _$PaymentMethodDtoCopyWith(_PaymentMethodDto value, $Res Function(_PaymentMethodDto) _then) = __$PaymentMethodDtoCopyWithImpl;
@override @useResult
$Res call({
 String code, String label
});




}
/// @nodoc
class __$PaymentMethodDtoCopyWithImpl<$Res>
    implements _$PaymentMethodDtoCopyWith<$Res> {
  __$PaymentMethodDtoCopyWithImpl(this._self, this._then);

  final _PaymentMethodDto _self;
  final $Res Function(_PaymentMethodDto) _then;

/// Create a copy of PaymentMethodDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? code = null,Object? label = null,}) {
  return _then(_PaymentMethodDto(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}


/// @nodoc
mixin _$PaymentOptionDto {

 String get gateway; String get label; List<PaymentMethodDto> get methods; bool get available; String get paymentCurrency;@MoneyConverter() Money get paymentAmount; String get displayCurrency;@MoneyConverter() Money get displayAmount; String? get notice;
/// Create a copy of PaymentOptionDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PaymentOptionDtoCopyWith<PaymentOptionDto> get copyWith => _$PaymentOptionDtoCopyWithImpl<PaymentOptionDto>(this as PaymentOptionDto, _$identity);

  /// Serializes this PaymentOptionDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as PaymentOptionDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PaymentOptionDto&&(identical(other.gateway, _this.gateway) || other.gateway == _this.gateway)&&(identical(other.label, _this.label) || other.label == _this.label)&&const DeepCollectionEquality().equals(other.methods, _this.methods)&&(identical(other.available, _this.available) || other.available == _this.available)&&(identical(other.paymentCurrency, _this.paymentCurrency) || other.paymentCurrency == _this.paymentCurrency)&&(identical(other.paymentAmount, _this.paymentAmount) || other.paymentAmount == _this.paymentAmount)&&(identical(other.displayCurrency, _this.displayCurrency) || other.displayCurrency == _this.displayCurrency)&&(identical(other.displayAmount, _this.displayAmount) || other.displayAmount == _this.displayAmount)&&(identical(other.notice, _this.notice) || other.notice == _this.notice));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as PaymentOptionDto;
  return Object.hash(runtimeType,_this.gateway,_this.label,const DeepCollectionEquality().hash(_this.methods),_this.available,_this.paymentCurrency,_this.paymentAmount,_this.displayCurrency,_this.displayAmount,_this.notice);
}

@override
String toString() {
  final _this = this as PaymentOptionDto;
  return 'PaymentOptionDto(gateway: ${_this.gateway}, label: ${_this.label}, methods: ${_this.methods}, available: ${_this.available}, paymentCurrency: ${_this.paymentCurrency}, paymentAmount: ${_this.paymentAmount}, displayCurrency: ${_this.displayCurrency}, displayAmount: ${_this.displayAmount}, notice: ${_this.notice})';
}


}

/// @nodoc
abstract mixin class $PaymentOptionDtoCopyWith<$Res>  {
  factory $PaymentOptionDtoCopyWith(PaymentOptionDto value, $Res Function(PaymentOptionDto) _then) = _$PaymentOptionDtoCopyWithImpl;
@useResult
$Res call({
 String gateway, String label, List<PaymentMethodDto> methods, bool available, String paymentCurrency,@MoneyConverter() Money paymentAmount, String displayCurrency,@MoneyConverter() Money displayAmount, String? notice
});




}
/// @nodoc
class _$PaymentOptionDtoCopyWithImpl<$Res>
    implements $PaymentOptionDtoCopyWith<$Res> {
  _$PaymentOptionDtoCopyWithImpl(this._self, this._then);

  final PaymentOptionDto _self;
  final $Res Function(PaymentOptionDto) _then;

/// Create a copy of PaymentOptionDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? gateway = null,Object? label = null,Object? methods = null,Object? available = null,Object? paymentCurrency = null,Object? paymentAmount = null,Object? displayCurrency = null,Object? displayAmount = null,Object? notice = freezed,}) {
  return _then(PaymentOptionDto(
gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,methods: null == methods ? _self.methods : methods // ignore: cast_nullable_to_non_nullable
as List<PaymentMethodDto>,available: null == available ? _self.available : available // ignore: cast_nullable_to_non_nullable
as bool,paymentCurrency: null == paymentCurrency ? _self.paymentCurrency : paymentCurrency // ignore: cast_nullable_to_non_nullable
as String,paymentAmount: null == paymentAmount ? _self.paymentAmount : paymentAmount // ignore: cast_nullable_to_non_nullable
as Money,displayCurrency: null == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String,displayAmount: null == displayAmount ? _self.displayAmount : displayAmount // ignore: cast_nullable_to_non_nullable
as Money,notice: freezed == notice ? _self.notice : notice // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [PaymentOptionDto].
extension PaymentOptionDtoPatterns on PaymentOptionDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PaymentOptionDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PaymentOptionDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PaymentOptionDto value)  $default,){
final _that = this;
switch (_that) {
case _PaymentOptionDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PaymentOptionDto value)?  $default,){
final _that = this;
switch (_that) {
case _PaymentOptionDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String gateway,  String label,  List<PaymentMethodDto> methods,  bool available,  String paymentCurrency, @MoneyConverter()  Money paymentAmount,  String displayCurrency, @MoneyConverter()  Money displayAmount,  String? notice)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PaymentOptionDto() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String gateway,  String label,  List<PaymentMethodDto> methods,  bool available,  String paymentCurrency, @MoneyConverter()  Money paymentAmount,  String displayCurrency, @MoneyConverter()  Money displayAmount,  String? notice)  $default,) {final _that = this;
switch (_that) {
case _PaymentOptionDto():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String gateway,  String label,  List<PaymentMethodDto> methods,  bool available,  String paymentCurrency, @MoneyConverter()  Money paymentAmount,  String displayCurrency, @MoneyConverter()  Money displayAmount,  String? notice)?  $default,) {final _that = this;
switch (_that) {
case _PaymentOptionDto() when $default != null:
return $default(_that.gateway,_that.label,_that.methods,_that.available,_that.paymentCurrency,_that.paymentAmount,_that.displayCurrency,_that.displayAmount,_that.notice);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _PaymentOptionDto extends PaymentOptionDto {
  const _PaymentOptionDto({required this.gateway, required this.label, required  List<PaymentMethodDto> methods, required this.available, required this.paymentCurrency, @MoneyConverter() required this.paymentAmount, required this.displayCurrency, @MoneyConverter() required this.displayAmount, this.notice}): _methods = methods,super._();
  factory _PaymentOptionDto.fromJson(Map<String, dynamic> json) => _$PaymentOptionDtoFromJson(json);

@override final  String gateway;
@override final  String label;
 final  List<PaymentMethodDto> _methods;
@override List<PaymentMethodDto> get methods {
  if (_methods is EqualUnmodifiableListView) return _methods;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_methods);
}

@override final  bool available;
@override final  String paymentCurrency;
@override@MoneyConverter() final  Money paymentAmount;
@override final  String displayCurrency;
@override@MoneyConverter() final  Money displayAmount;
@override final  String? notice;

/// Create a copy of PaymentOptionDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PaymentOptionDtoCopyWith<_PaymentOptionDto> get copyWith => __$PaymentOptionDtoCopyWithImpl<_PaymentOptionDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$PaymentOptionDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PaymentOptionDto&&(identical(other.gateway, gateway) || other.gateway == gateway)&&(identical(other.label, label) || other.label == label)&&const DeepCollectionEquality().equals(other.methods, _methods)&&(identical(other.available, available) || other.available == available)&&(identical(other.paymentCurrency, paymentCurrency) || other.paymentCurrency == paymentCurrency)&&(identical(other.paymentAmount, paymentAmount) || other.paymentAmount == paymentAmount)&&(identical(other.displayCurrency, displayCurrency) || other.displayCurrency == displayCurrency)&&(identical(other.displayAmount, displayAmount) || other.displayAmount == displayAmount)&&(identical(other.notice, notice) || other.notice == notice));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,gateway,label,const DeepCollectionEquality().hash(_methods),available,paymentCurrency,paymentAmount,displayCurrency,displayAmount,notice);
}

@override
String toString() {
    return 'PaymentOptionDto(gateway: $gateway, label: $label, methods: $methods, available: $available, paymentCurrency: $paymentCurrency, paymentAmount: $paymentAmount, displayCurrency: $displayCurrency, displayAmount: $displayAmount, notice: $notice)';
}


}

/// @nodoc
abstract mixin class _$PaymentOptionDtoCopyWith<$Res> implements $PaymentOptionDtoCopyWith<$Res> {
  factory _$PaymentOptionDtoCopyWith(_PaymentOptionDto value, $Res Function(_PaymentOptionDto) _then) = __$PaymentOptionDtoCopyWithImpl;
@override @useResult
$Res call({
 String gateway, String label, List<PaymentMethodDto> methods, bool available, String paymentCurrency,@MoneyConverter() Money paymentAmount, String displayCurrency,@MoneyConverter() Money displayAmount, String? notice
});




}
/// @nodoc
class __$PaymentOptionDtoCopyWithImpl<$Res>
    implements _$PaymentOptionDtoCopyWith<$Res> {
  __$PaymentOptionDtoCopyWithImpl(this._self, this._then);

  final _PaymentOptionDto _self;
  final $Res Function(_PaymentOptionDto) _then;

/// Create a copy of PaymentOptionDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? gateway = null,Object? label = null,Object? methods = null,Object? available = null,Object? paymentCurrency = null,Object? paymentAmount = null,Object? displayCurrency = null,Object? displayAmount = null,Object? notice = freezed,}) {
  return _then(_PaymentOptionDto(
gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,methods: null == methods ? _self._methods : methods // ignore: cast_nullable_to_non_nullable
as List<PaymentMethodDto>,available: null == available ? _self.available : available // ignore: cast_nullable_to_non_nullable
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
mixin _$PlacedOrderDto {

 String get publicId; String get status; String? get orderNumber;@NullableMoneyConverter() Money? get displayTotal;@NullableMoneyConverter() Money? get paymentTotal; DateTime? get expiresAt;
/// Create a copy of PlacedOrderDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PlacedOrderDtoCopyWith<PlacedOrderDto> get copyWith => _$PlacedOrderDtoCopyWithImpl<PlacedOrderDto>(this as PlacedOrderDto, _$identity);

  /// Serializes this PlacedOrderDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as PlacedOrderDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PlacedOrderDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.orderNumber, _this.orderNumber) || other.orderNumber == _this.orderNumber)&&(identical(other.displayTotal, _this.displayTotal) || other.displayTotal == _this.displayTotal)&&(identical(other.paymentTotal, _this.paymentTotal) || other.paymentTotal == _this.paymentTotal)&&(identical(other.expiresAt, _this.expiresAt) || other.expiresAt == _this.expiresAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as PlacedOrderDto;
  return Object.hash(runtimeType,_this.publicId,_this.status,_this.orderNumber,_this.displayTotal,_this.paymentTotal,_this.expiresAt);
}

@override
String toString() {
  final _this = this as PlacedOrderDto;
  return 'PlacedOrderDto(publicId: ${_this.publicId}, status: ${_this.status}, orderNumber: ${_this.orderNumber}, displayTotal: ${_this.displayTotal}, paymentTotal: ${_this.paymentTotal}, expiresAt: ${_this.expiresAt})';
}


}

/// @nodoc
abstract mixin class $PlacedOrderDtoCopyWith<$Res>  {
  factory $PlacedOrderDtoCopyWith(PlacedOrderDto value, $Res Function(PlacedOrderDto) _then) = _$PlacedOrderDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String status, String? orderNumber,@NullableMoneyConverter() Money? displayTotal,@NullableMoneyConverter() Money? paymentTotal, DateTime? expiresAt
});




}
/// @nodoc
class _$PlacedOrderDtoCopyWithImpl<$Res>
    implements $PlacedOrderDtoCopyWith<$Res> {
  _$PlacedOrderDtoCopyWithImpl(this._self, this._then);

  final PlacedOrderDto _self;
  final $Res Function(PlacedOrderDto) _then;

/// Create a copy of PlacedOrderDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? status = null,Object? orderNumber = freezed,Object? displayTotal = freezed,Object? paymentTotal = freezed,Object? expiresAt = freezed,}) {
  return _then(PlacedOrderDto(
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


/// Adds pattern-matching-related methods to [PlacedOrderDto].
extension PlacedOrderDtoPatterns on PlacedOrderDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PlacedOrderDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PlacedOrderDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PlacedOrderDto value)  $default,){
final _that = this;
switch (_that) {
case _PlacedOrderDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PlacedOrderDto value)?  $default,){
final _that = this;
switch (_that) {
case _PlacedOrderDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String status,  String? orderNumber, @NullableMoneyConverter()  Money? displayTotal, @NullableMoneyConverter()  Money? paymentTotal,  DateTime? expiresAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PlacedOrderDto() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String status,  String? orderNumber, @NullableMoneyConverter()  Money? displayTotal, @NullableMoneyConverter()  Money? paymentTotal,  DateTime? expiresAt)  $default,) {final _that = this;
switch (_that) {
case _PlacedOrderDto():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String status,  String? orderNumber, @NullableMoneyConverter()  Money? displayTotal, @NullableMoneyConverter()  Money? paymentTotal,  DateTime? expiresAt)?  $default,) {final _that = this;
switch (_that) {
case _PlacedOrderDto() when $default != null:
return $default(_that.publicId,_that.status,_that.orderNumber,_that.displayTotal,_that.paymentTotal,_that.expiresAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _PlacedOrderDto extends PlacedOrderDto {
  const _PlacedOrderDto({required this.publicId, required this.status, this.orderNumber, @NullableMoneyConverter() this.displayTotal, @NullableMoneyConverter() this.paymentTotal, this.expiresAt}): super._();
  factory _PlacedOrderDto.fromJson(Map<String, dynamic> json) => _$PlacedOrderDtoFromJson(json);

@override final  String publicId;
@override final  String status;
@override final  String? orderNumber;
@override@NullableMoneyConverter() final  Money? displayTotal;
@override@NullableMoneyConverter() final  Money? paymentTotal;
@override final  DateTime? expiresAt;

/// Create a copy of PlacedOrderDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PlacedOrderDtoCopyWith<_PlacedOrderDto> get copyWith => __$PlacedOrderDtoCopyWithImpl<_PlacedOrderDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$PlacedOrderDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PlacedOrderDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.status, status) || other.status == status)&&(identical(other.orderNumber, orderNumber) || other.orderNumber == orderNumber)&&(identical(other.displayTotal, displayTotal) || other.displayTotal == displayTotal)&&(identical(other.paymentTotal, paymentTotal) || other.paymentTotal == paymentTotal)&&(identical(other.expiresAt, expiresAt) || other.expiresAt == expiresAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,status,orderNumber,displayTotal,paymentTotal,expiresAt);
}

@override
String toString() {
    return 'PlacedOrderDto(publicId: $publicId, status: $status, orderNumber: $orderNumber, displayTotal: $displayTotal, paymentTotal: $paymentTotal, expiresAt: $expiresAt)';
}


}

/// @nodoc
abstract mixin class _$PlacedOrderDtoCopyWith<$Res> implements $PlacedOrderDtoCopyWith<$Res> {
  factory _$PlacedOrderDtoCopyWith(_PlacedOrderDto value, $Res Function(_PlacedOrderDto) _then) = __$PlacedOrderDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String status, String? orderNumber,@NullableMoneyConverter() Money? displayTotal,@NullableMoneyConverter() Money? paymentTotal, DateTime? expiresAt
});




}
/// @nodoc
class __$PlacedOrderDtoCopyWithImpl<$Res>
    implements _$PlacedOrderDtoCopyWith<$Res> {
  __$PlacedOrderDtoCopyWithImpl(this._self, this._then);

  final _PlacedOrderDto _self;
  final $Res Function(_PlacedOrderDto) _then;

/// Create a copy of PlacedOrderDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? status = null,Object? orderNumber = freezed,Object? displayTotal = freezed,Object? paymentTotal = freezed,Object? expiresAt = freezed,}) {
  return _then(_PlacedOrderDto(
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
mixin _$PlaceOrderDataDto {

 PlacedOrderDto get order; PaymentNextDto? get next; GuestOrderTokenDto? get guestOrderToken;
/// Create a copy of PlaceOrderDataDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PlaceOrderDataDtoCopyWith<PlaceOrderDataDto> get copyWith => _$PlaceOrderDataDtoCopyWithImpl<PlaceOrderDataDto>(this as PlaceOrderDataDto, _$identity);

  /// Serializes this PlaceOrderDataDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as PlaceOrderDataDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PlaceOrderDataDto&&(identical(other.order, _this.order) || other.order == _this.order)&&(identical(other.next, _this.next) || other.next == _this.next)&&(identical(other.guestOrderToken, _this.guestOrderToken) || other.guestOrderToken == _this.guestOrderToken));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as PlaceOrderDataDto;
  return Object.hash(runtimeType,_this.order,_this.next,_this.guestOrderToken);
}

@override
String toString() {
  final _this = this as PlaceOrderDataDto;
  return 'PlaceOrderDataDto(order: ${_this.order}, next: ${_this.next}, guestOrderToken: ${_this.guestOrderToken})';
}


}

/// @nodoc
abstract mixin class $PlaceOrderDataDtoCopyWith<$Res>  {
  factory $PlaceOrderDataDtoCopyWith(PlaceOrderDataDto value, $Res Function(PlaceOrderDataDto) _then) = _$PlaceOrderDataDtoCopyWithImpl;
@useResult
$Res call({
 PlacedOrderDto order, PaymentNextDto? next, GuestOrderTokenDto? guestOrderToken
});


$PlacedOrderDtoCopyWith<$Res> get order;$PaymentNextDtoCopyWith<$Res>? get next;$GuestOrderTokenDtoCopyWith<$Res>? get guestOrderToken;

}
/// @nodoc
class _$PlaceOrderDataDtoCopyWithImpl<$Res>
    implements $PlaceOrderDataDtoCopyWith<$Res> {
  _$PlaceOrderDataDtoCopyWithImpl(this._self, this._then);

  final PlaceOrderDataDto _self;
  final $Res Function(PlaceOrderDataDto) _then;

/// Create a copy of PlaceOrderDataDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? order = null,Object? next = freezed,Object? guestOrderToken = freezed,}) {
  return _then(PlaceOrderDataDto(
order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as PlacedOrderDto,next: freezed == next ? _self.next : next // ignore: cast_nullable_to_non_nullable
as PaymentNextDto?,guestOrderToken: freezed == guestOrderToken ? _self.guestOrderToken : guestOrderToken // ignore: cast_nullable_to_non_nullable
as GuestOrderTokenDto?,
  ));
}
/// Create a copy of PlaceOrderDataDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PlacedOrderDtoCopyWith<$Res> get order {
  
  return $PlacedOrderDtoCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}/// Create a copy of PlaceOrderDataDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PaymentNextDtoCopyWith<$Res>? get next {
    if (_self.next == null) {
    return null;
  }

  return $PaymentNextDtoCopyWith<$Res>(_self.next!, (value) {
    return _then(_self.copyWith(next: value));
  });
}/// Create a copy of PlaceOrderDataDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$GuestOrderTokenDtoCopyWith<$Res>? get guestOrderToken {
    if (_self.guestOrderToken == null) {
    return null;
  }

  return $GuestOrderTokenDtoCopyWith<$Res>(_self.guestOrderToken!, (value) {
    return _then(_self.copyWith(guestOrderToken: value));
  });
}
}


/// Adds pattern-matching-related methods to [PlaceOrderDataDto].
extension PlaceOrderDataDtoPatterns on PlaceOrderDataDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PlaceOrderDataDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PlaceOrderDataDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PlaceOrderDataDto value)  $default,){
final _that = this;
switch (_that) {
case _PlaceOrderDataDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PlaceOrderDataDto value)?  $default,){
final _that = this;
switch (_that) {
case _PlaceOrderDataDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( PlacedOrderDto order,  PaymentNextDto? next,  GuestOrderTokenDto? guestOrderToken)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PlaceOrderDataDto() when $default != null:
return $default(_that.order,_that.next,_that.guestOrderToken);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( PlacedOrderDto order,  PaymentNextDto? next,  GuestOrderTokenDto? guestOrderToken)  $default,) {final _that = this;
switch (_that) {
case _PlaceOrderDataDto():
return $default(_that.order,_that.next,_that.guestOrderToken);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( PlacedOrderDto order,  PaymentNextDto? next,  GuestOrderTokenDto? guestOrderToken)?  $default,) {final _that = this;
switch (_that) {
case _PlaceOrderDataDto() when $default != null:
return $default(_that.order,_that.next,_that.guestOrderToken);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _PlaceOrderDataDto implements PlaceOrderDataDto {
  const _PlaceOrderDataDto({required this.order, this.next, this.guestOrderToken});
  factory _PlaceOrderDataDto.fromJson(Map<String, dynamic> json) => _$PlaceOrderDataDtoFromJson(json);

@override final  PlacedOrderDto order;
@override final  PaymentNextDto? next;
@override final  GuestOrderTokenDto? guestOrderToken;

/// Create a copy of PlaceOrderDataDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PlaceOrderDataDtoCopyWith<_PlaceOrderDataDto> get copyWith => __$PlaceOrderDataDtoCopyWithImpl<_PlaceOrderDataDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$PlaceOrderDataDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PlaceOrderDataDto&&(identical(other.order, order) || other.order == order)&&(identical(other.next, next) || other.next == next)&&(identical(other.guestOrderToken, guestOrderToken) || other.guestOrderToken == guestOrderToken));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,order,next,guestOrderToken);
}

@override
String toString() {
    return 'PlaceOrderDataDto(order: $order, next: $next, guestOrderToken: $guestOrderToken)';
}


}

/// @nodoc
abstract mixin class _$PlaceOrderDataDtoCopyWith<$Res> implements $PlaceOrderDataDtoCopyWith<$Res> {
  factory _$PlaceOrderDataDtoCopyWith(_PlaceOrderDataDto value, $Res Function(_PlaceOrderDataDto) _then) = __$PlaceOrderDataDtoCopyWithImpl;
@override @useResult
$Res call({
 PlacedOrderDto order, PaymentNextDto? next, GuestOrderTokenDto? guestOrderToken
});


@override $PlacedOrderDtoCopyWith<$Res> get order;@override $PaymentNextDtoCopyWith<$Res>? get next;@override $GuestOrderTokenDtoCopyWith<$Res>? get guestOrderToken;

}
/// @nodoc
class __$PlaceOrderDataDtoCopyWithImpl<$Res>
    implements _$PlaceOrderDataDtoCopyWith<$Res> {
  __$PlaceOrderDataDtoCopyWithImpl(this._self, this._then);

  final _PlaceOrderDataDto _self;
  final $Res Function(_PlaceOrderDataDto) _then;

/// Create a copy of PlaceOrderDataDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? order = null,Object? next = freezed,Object? guestOrderToken = freezed,}) {
  return _then(_PlaceOrderDataDto(
order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as PlacedOrderDto,next: freezed == next ? _self.next : next // ignore: cast_nullable_to_non_nullable
as PaymentNextDto?,guestOrderToken: freezed == guestOrderToken ? _self.guestOrderToken : guestOrderToken // ignore: cast_nullable_to_non_nullable
as GuestOrderTokenDto?,
  ));
}

/// Create a copy of PlaceOrderDataDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PlacedOrderDtoCopyWith<$Res> get order {
  
  return $PlacedOrderDtoCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}/// Create a copy of PlaceOrderDataDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PaymentNextDtoCopyWith<$Res>? get next {
    if (_self.next == null) {
    return null;
  }

  return $PaymentNextDtoCopyWith<$Res>(_self.next!, (value) {
    return _then(_self.copyWith(next: value));
  });
}/// Create a copy of PlaceOrderDataDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$GuestOrderTokenDtoCopyWith<$Res>? get guestOrderToken {
    if (_self.guestOrderToken == null) {
    return null;
  }

  return $GuestOrderTokenDtoCopyWith<$Res>(_self.guestOrderToken!, (value) {
    return _then(_self.copyWith(guestOrderToken: value));
  });
}
}


/// @nodoc
mixin _$GuestAccessDto {

 String get orderPublicId; bool get otpRequired; GuestOrderTokenDto? get guestOrderToken;
/// Create a copy of GuestAccessDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$GuestAccessDtoCopyWith<GuestAccessDto> get copyWith => _$GuestAccessDtoCopyWithImpl<GuestAccessDto>(this as GuestAccessDto, _$identity);

  /// Serializes this GuestAccessDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as GuestAccessDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is GuestAccessDto&&(identical(other.orderPublicId, _this.orderPublicId) || other.orderPublicId == _this.orderPublicId)&&(identical(other.otpRequired, _this.otpRequired) || other.otpRequired == _this.otpRequired)&&(identical(other.guestOrderToken, _this.guestOrderToken) || other.guestOrderToken == _this.guestOrderToken));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as GuestAccessDto;
  return Object.hash(runtimeType,_this.orderPublicId,_this.otpRequired,_this.guestOrderToken);
}

@override
String toString() {
  final _this = this as GuestAccessDto;
  return 'GuestAccessDto(orderPublicId: ${_this.orderPublicId}, otpRequired: ${_this.otpRequired}, guestOrderToken: ${_this.guestOrderToken})';
}


}

/// @nodoc
abstract mixin class $GuestAccessDtoCopyWith<$Res>  {
  factory $GuestAccessDtoCopyWith(GuestAccessDto value, $Res Function(GuestAccessDto) _then) = _$GuestAccessDtoCopyWithImpl;
@useResult
$Res call({
 String orderPublicId, bool otpRequired, GuestOrderTokenDto? guestOrderToken
});


$GuestOrderTokenDtoCopyWith<$Res>? get guestOrderToken;

}
/// @nodoc
class _$GuestAccessDtoCopyWithImpl<$Res>
    implements $GuestAccessDtoCopyWith<$Res> {
  _$GuestAccessDtoCopyWithImpl(this._self, this._then);

  final GuestAccessDto _self;
  final $Res Function(GuestAccessDto) _then;

/// Create a copy of GuestAccessDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? orderPublicId = null,Object? otpRequired = null,Object? guestOrderToken = freezed,}) {
  return _then(GuestAccessDto(
orderPublicId: null == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String,otpRequired: null == otpRequired ? _self.otpRequired : otpRequired // ignore: cast_nullable_to_non_nullable
as bool,guestOrderToken: freezed == guestOrderToken ? _self.guestOrderToken : guestOrderToken // ignore: cast_nullable_to_non_nullable
as GuestOrderTokenDto?,
  ));
}
/// Create a copy of GuestAccessDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$GuestOrderTokenDtoCopyWith<$Res>? get guestOrderToken {
    if (_self.guestOrderToken == null) {
    return null;
  }

  return $GuestOrderTokenDtoCopyWith<$Res>(_self.guestOrderToken!, (value) {
    return _then(_self.copyWith(guestOrderToken: value));
  });
}
}


/// Adds pattern-matching-related methods to [GuestAccessDto].
extension GuestAccessDtoPatterns on GuestAccessDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _GuestAccessDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _GuestAccessDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _GuestAccessDto value)  $default,){
final _that = this;
switch (_that) {
case _GuestAccessDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _GuestAccessDto value)?  $default,){
final _that = this;
switch (_that) {
case _GuestAccessDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String orderPublicId,  bool otpRequired,  GuestOrderTokenDto? guestOrderToken)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _GuestAccessDto() when $default != null:
return $default(_that.orderPublicId,_that.otpRequired,_that.guestOrderToken);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String orderPublicId,  bool otpRequired,  GuestOrderTokenDto? guestOrderToken)  $default,) {final _that = this;
switch (_that) {
case _GuestAccessDto():
return $default(_that.orderPublicId,_that.otpRequired,_that.guestOrderToken);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String orderPublicId,  bool otpRequired,  GuestOrderTokenDto? guestOrderToken)?  $default,) {final _that = this;
switch (_that) {
case _GuestAccessDto() when $default != null:
return $default(_that.orderPublicId,_that.otpRequired,_that.guestOrderToken);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _GuestAccessDto implements GuestAccessDto {
  const _GuestAccessDto({required this.orderPublicId, required this.otpRequired, this.guestOrderToken});
  factory _GuestAccessDto.fromJson(Map<String, dynamic> json) => _$GuestAccessDtoFromJson(json);

@override final  String orderPublicId;
@override final  bool otpRequired;
@override final  GuestOrderTokenDto? guestOrderToken;

/// Create a copy of GuestAccessDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$GuestAccessDtoCopyWith<_GuestAccessDto> get copyWith => __$GuestAccessDtoCopyWithImpl<_GuestAccessDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$GuestAccessDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _GuestAccessDto&&(identical(other.orderPublicId, orderPublicId) || other.orderPublicId == orderPublicId)&&(identical(other.otpRequired, otpRequired) || other.otpRequired == otpRequired)&&(identical(other.guestOrderToken, guestOrderToken) || other.guestOrderToken == guestOrderToken));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,orderPublicId,otpRequired,guestOrderToken);
}

@override
String toString() {
    return 'GuestAccessDto(orderPublicId: $orderPublicId, otpRequired: $otpRequired, guestOrderToken: $guestOrderToken)';
}


}

/// @nodoc
abstract mixin class _$GuestAccessDtoCopyWith<$Res> implements $GuestAccessDtoCopyWith<$Res> {
  factory _$GuestAccessDtoCopyWith(_GuestAccessDto value, $Res Function(_GuestAccessDto) _then) = __$GuestAccessDtoCopyWithImpl;
@override @useResult
$Res call({
 String orderPublicId, bool otpRequired, GuestOrderTokenDto? guestOrderToken
});


@override $GuestOrderTokenDtoCopyWith<$Res>? get guestOrderToken;

}
/// @nodoc
class __$GuestAccessDtoCopyWithImpl<$Res>
    implements _$GuestAccessDtoCopyWith<$Res> {
  __$GuestAccessDtoCopyWithImpl(this._self, this._then);

  final _GuestAccessDto _self;
  final $Res Function(_GuestAccessDto) _then;

/// Create a copy of GuestAccessDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? orderPublicId = null,Object? otpRequired = null,Object? guestOrderToken = freezed,}) {
  return _then(_GuestAccessDto(
orderPublicId: null == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String,otpRequired: null == otpRequired ? _self.otpRequired : otpRequired // ignore: cast_nullable_to_non_nullable
as bool,guestOrderToken: freezed == guestOrderToken ? _self.guestOrderToken : guestOrderToken // ignore: cast_nullable_to_non_nullable
as GuestOrderTokenDto?,
  ));
}

/// Create a copy of GuestAccessDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$GuestOrderTokenDtoCopyWith<$Res>? get guestOrderToken {
    if (_self.guestOrderToken == null) {
    return null;
  }

  return $GuestOrderTokenDtoCopyWith<$Res>(_self.guestOrderToken!, (value) {
    return _then(_self.copyWith(guestOrderToken: value));
  });
}
}


/// @nodoc
mixin _$OtpVerifyDto {

 String get orderPublicId; bool get emailVerified; GuestOrderTokenDto get guestOrderToken;
/// Create a copy of OtpVerifyDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OtpVerifyDtoCopyWith<OtpVerifyDto> get copyWith => _$OtpVerifyDtoCopyWithImpl<OtpVerifyDto>(this as OtpVerifyDto, _$identity);

  /// Serializes this OtpVerifyDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as OtpVerifyDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OtpVerifyDto&&(identical(other.orderPublicId, _this.orderPublicId) || other.orderPublicId == _this.orderPublicId)&&(identical(other.emailVerified, _this.emailVerified) || other.emailVerified == _this.emailVerified)&&(identical(other.guestOrderToken, _this.guestOrderToken) || other.guestOrderToken == _this.guestOrderToken));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as OtpVerifyDto;
  return Object.hash(runtimeType,_this.orderPublicId,_this.emailVerified,_this.guestOrderToken);
}

@override
String toString() {
  final _this = this as OtpVerifyDto;
  return 'OtpVerifyDto(orderPublicId: ${_this.orderPublicId}, emailVerified: ${_this.emailVerified}, guestOrderToken: ${_this.guestOrderToken})';
}


}

/// @nodoc
abstract mixin class $OtpVerifyDtoCopyWith<$Res>  {
  factory $OtpVerifyDtoCopyWith(OtpVerifyDto value, $Res Function(OtpVerifyDto) _then) = _$OtpVerifyDtoCopyWithImpl;
@useResult
$Res call({
 String orderPublicId, bool emailVerified, GuestOrderTokenDto guestOrderToken
});


$GuestOrderTokenDtoCopyWith<$Res> get guestOrderToken;

}
/// @nodoc
class _$OtpVerifyDtoCopyWithImpl<$Res>
    implements $OtpVerifyDtoCopyWith<$Res> {
  _$OtpVerifyDtoCopyWithImpl(this._self, this._then);

  final OtpVerifyDto _self;
  final $Res Function(OtpVerifyDto) _then;

/// Create a copy of OtpVerifyDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? orderPublicId = null,Object? emailVerified = null,Object? guestOrderToken = null,}) {
  return _then(OtpVerifyDto(
orderPublicId: null == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String,emailVerified: null == emailVerified ? _self.emailVerified : emailVerified // ignore: cast_nullable_to_non_nullable
as bool,guestOrderToken: null == guestOrderToken ? _self.guestOrderToken : guestOrderToken // ignore: cast_nullable_to_non_nullable
as GuestOrderTokenDto,
  ));
}
/// Create a copy of OtpVerifyDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$GuestOrderTokenDtoCopyWith<$Res> get guestOrderToken {
  
  return $GuestOrderTokenDtoCopyWith<$Res>(_self.guestOrderToken, (value) {
    return _then(_self.copyWith(guestOrderToken: value));
  });
}
}


/// Adds pattern-matching-related methods to [OtpVerifyDto].
extension OtpVerifyDtoPatterns on OtpVerifyDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OtpVerifyDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OtpVerifyDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OtpVerifyDto value)  $default,){
final _that = this;
switch (_that) {
case _OtpVerifyDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OtpVerifyDto value)?  $default,){
final _that = this;
switch (_that) {
case _OtpVerifyDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String orderPublicId,  bool emailVerified,  GuestOrderTokenDto guestOrderToken)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OtpVerifyDto() when $default != null:
return $default(_that.orderPublicId,_that.emailVerified,_that.guestOrderToken);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String orderPublicId,  bool emailVerified,  GuestOrderTokenDto guestOrderToken)  $default,) {final _that = this;
switch (_that) {
case _OtpVerifyDto():
return $default(_that.orderPublicId,_that.emailVerified,_that.guestOrderToken);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String orderPublicId,  bool emailVerified,  GuestOrderTokenDto guestOrderToken)?  $default,) {final _that = this;
switch (_that) {
case _OtpVerifyDto() when $default != null:
return $default(_that.orderPublicId,_that.emailVerified,_that.guestOrderToken);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _OtpVerifyDto implements OtpVerifyDto {
  const _OtpVerifyDto({required this.orderPublicId, required this.emailVerified, required this.guestOrderToken});
  factory _OtpVerifyDto.fromJson(Map<String, dynamic> json) => _$OtpVerifyDtoFromJson(json);

@override final  String orderPublicId;
@override final  bool emailVerified;
@override final  GuestOrderTokenDto guestOrderToken;

/// Create a copy of OtpVerifyDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OtpVerifyDtoCopyWith<_OtpVerifyDto> get copyWith => __$OtpVerifyDtoCopyWithImpl<_OtpVerifyDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$OtpVerifyDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OtpVerifyDto&&(identical(other.orderPublicId, orderPublicId) || other.orderPublicId == orderPublicId)&&(identical(other.emailVerified, emailVerified) || other.emailVerified == emailVerified)&&(identical(other.guestOrderToken, guestOrderToken) || other.guestOrderToken == guestOrderToken));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,orderPublicId,emailVerified,guestOrderToken);
}

@override
String toString() {
    return 'OtpVerifyDto(orderPublicId: $orderPublicId, emailVerified: $emailVerified, guestOrderToken: $guestOrderToken)';
}


}

/// @nodoc
abstract mixin class _$OtpVerifyDtoCopyWith<$Res> implements $OtpVerifyDtoCopyWith<$Res> {
  factory _$OtpVerifyDtoCopyWith(_OtpVerifyDto value, $Res Function(_OtpVerifyDto) _then) = __$OtpVerifyDtoCopyWithImpl;
@override @useResult
$Res call({
 String orderPublicId, bool emailVerified, GuestOrderTokenDto guestOrderToken
});


@override $GuestOrderTokenDtoCopyWith<$Res> get guestOrderToken;

}
/// @nodoc
class __$OtpVerifyDtoCopyWithImpl<$Res>
    implements _$OtpVerifyDtoCopyWith<$Res> {
  __$OtpVerifyDtoCopyWithImpl(this._self, this._then);

  final _OtpVerifyDto _self;
  final $Res Function(_OtpVerifyDto) _then;

/// Create a copy of OtpVerifyDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? orderPublicId = null,Object? emailVerified = null,Object? guestOrderToken = null,}) {
  return _then(_OtpVerifyDto(
orderPublicId: null == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String,emailVerified: null == emailVerified ? _self.emailVerified : emailVerified // ignore: cast_nullable_to_non_nullable
as bool,guestOrderToken: null == guestOrderToken ? _self.guestOrderToken : guestOrderToken // ignore: cast_nullable_to_non_nullable
as GuestOrderTokenDto,
  ));
}

/// Create a copy of OtpVerifyDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$GuestOrderTokenDtoCopyWith<$Res> get guestOrderToken {
  
  return $GuestOrderTokenDtoCopyWith<$Res>(_self.guestOrderToken, (value) {
    return _then(_self.copyWith(guestOrderToken: value));
  });
}
}

// dart format on
