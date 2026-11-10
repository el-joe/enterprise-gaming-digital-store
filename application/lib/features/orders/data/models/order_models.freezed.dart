// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'order_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$OrderTotalsDto {

@MoneyConverter() Money get display;@NullableMoneyConverter() Money? get payment;
/// Create a copy of OrderTotalsDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderTotalsDtoCopyWith<OrderTotalsDto> get copyWith => _$OrderTotalsDtoCopyWithImpl<OrderTotalsDto>(this as OrderTotalsDto, _$identity);

  /// Serializes this OrderTotalsDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as OrderTotalsDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderTotalsDto&&(identical(other.display, _this.display) || other.display == _this.display)&&(identical(other.payment, _this.payment) || other.payment == _this.payment));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as OrderTotalsDto;
  return Object.hash(runtimeType,_this.display,_this.payment);
}

@override
String toString() {
  final _this = this as OrderTotalsDto;
  return 'OrderTotalsDto(display: ${_this.display}, payment: ${_this.payment})';
}


}

/// @nodoc
abstract mixin class $OrderTotalsDtoCopyWith<$Res>  {
  factory $OrderTotalsDtoCopyWith(OrderTotalsDto value, $Res Function(OrderTotalsDto) _then) = _$OrderTotalsDtoCopyWithImpl;
@useResult
$Res call({
@MoneyConverter() Money display,@NullableMoneyConverter() Money? payment
});




}
/// @nodoc
class _$OrderTotalsDtoCopyWithImpl<$Res>
    implements $OrderTotalsDtoCopyWith<$Res> {
  _$OrderTotalsDtoCopyWithImpl(this._self, this._then);

  final OrderTotalsDto _self;
  final $Res Function(OrderTotalsDto) _then;

/// Create a copy of OrderTotalsDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? display = null,Object? payment = freezed,}) {
  return _then(OrderTotalsDto(
display: null == display ? _self.display : display // ignore: cast_nullable_to_non_nullable
as Money,payment: freezed == payment ? _self.payment : payment // ignore: cast_nullable_to_non_nullable
as Money?,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderTotalsDto].
extension OrderTotalsDtoPatterns on OrderTotalsDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderTotalsDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderTotalsDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderTotalsDto value)  $default,){
final _that = this;
switch (_that) {
case _OrderTotalsDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderTotalsDto value)?  $default,){
final _that = this;
switch (_that) {
case _OrderTotalsDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function(@MoneyConverter()  Money display, @NullableMoneyConverter()  Money? payment)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderTotalsDto() when $default != null:
return $default(_that.display,_that.payment);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function(@MoneyConverter()  Money display, @NullableMoneyConverter()  Money? payment)  $default,) {final _that = this;
switch (_that) {
case _OrderTotalsDto():
return $default(_that.display,_that.payment);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function(@MoneyConverter()  Money display, @NullableMoneyConverter()  Money? payment)?  $default,) {final _that = this;
switch (_that) {
case _OrderTotalsDto() when $default != null:
return $default(_that.display,_that.payment);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _OrderTotalsDto implements OrderTotalsDto {
  const _OrderTotalsDto({@MoneyConverter() required this.display, @NullableMoneyConverter() this.payment});
  factory _OrderTotalsDto.fromJson(Map<String, dynamic> json) => _$OrderTotalsDtoFromJson(json);

@override@MoneyConverter() final  Money display;
@override@NullableMoneyConverter() final  Money? payment;

/// Create a copy of OrderTotalsDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderTotalsDtoCopyWith<_OrderTotalsDto> get copyWith => __$OrderTotalsDtoCopyWithImpl<_OrderTotalsDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$OrderTotalsDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderTotalsDto&&(identical(other.display, display) || other.display == display)&&(identical(other.payment, payment) || other.payment == payment));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,display,payment);
}

@override
String toString() {
    return 'OrderTotalsDto(display: $display, payment: $payment)';
}


}

/// @nodoc
abstract mixin class _$OrderTotalsDtoCopyWith<$Res> implements $OrderTotalsDtoCopyWith<$Res> {
  factory _$OrderTotalsDtoCopyWith(_OrderTotalsDto value, $Res Function(_OrderTotalsDto) _then) = __$OrderTotalsDtoCopyWithImpl;
@override @useResult
$Res call({
@MoneyConverter() Money display,@NullableMoneyConverter() Money? payment
});




}
/// @nodoc
class __$OrderTotalsDtoCopyWithImpl<$Res>
    implements _$OrderTotalsDtoCopyWith<$Res> {
  __$OrderTotalsDtoCopyWithImpl(this._self, this._then);

  final _OrderTotalsDto _self;
  final $Res Function(_OrderTotalsDto) _then;

/// Create a copy of OrderTotalsDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? display = null,Object? payment = freezed,}) {
  return _then(_OrderTotalsDto(
display: null == display ? _self.display : display // ignore: cast_nullable_to_non_nullable
as Money,payment: freezed == payment ? _self.payment : payment // ignore: cast_nullable_to_non_nullable
as Money?,
  ));
}


}


/// @nodoc
mixin _$OrderSummaryDto {

 String get publicId; String get number; String get status; String get paymentStatus; bool get isFinal; OrderTotalsDto get totals; int get itemCount; DateTime get createdAt; String? get thumbnailUrl; DateTime? get paidAt;
/// Create a copy of OrderSummaryDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderSummaryDtoCopyWith<OrderSummaryDto> get copyWith => _$OrderSummaryDtoCopyWithImpl<OrderSummaryDto>(this as OrderSummaryDto, _$identity);

  /// Serializes this OrderSummaryDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as OrderSummaryDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderSummaryDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.number, _this.number) || other.number == _this.number)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.paymentStatus, _this.paymentStatus) || other.paymentStatus == _this.paymentStatus)&&(identical(other.isFinal, _this.isFinal) || other.isFinal == _this.isFinal)&&(identical(other.totals, _this.totals) || other.totals == _this.totals)&&(identical(other.itemCount, _this.itemCount) || other.itemCount == _this.itemCount)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.thumbnailUrl, _this.thumbnailUrl) || other.thumbnailUrl == _this.thumbnailUrl)&&(identical(other.paidAt, _this.paidAt) || other.paidAt == _this.paidAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as OrderSummaryDto;
  return Object.hash(runtimeType,_this.publicId,_this.number,_this.status,_this.paymentStatus,_this.isFinal,_this.totals,_this.itemCount,_this.createdAt,_this.thumbnailUrl,_this.paidAt);
}

@override
String toString() {
  final _this = this as OrderSummaryDto;
  return 'OrderSummaryDto(publicId: ${_this.publicId}, number: ${_this.number}, status: ${_this.status}, paymentStatus: ${_this.paymentStatus}, isFinal: ${_this.isFinal}, totals: ${_this.totals}, itemCount: ${_this.itemCount}, createdAt: ${_this.createdAt}, thumbnailUrl: ${_this.thumbnailUrl}, paidAt: ${_this.paidAt})';
}


}

/// @nodoc
abstract mixin class $OrderSummaryDtoCopyWith<$Res>  {
  factory $OrderSummaryDtoCopyWith(OrderSummaryDto value, $Res Function(OrderSummaryDto) _then) = _$OrderSummaryDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String number, String status, String paymentStatus, bool isFinal, OrderTotalsDto totals, int itemCount, DateTime createdAt, String? thumbnailUrl, DateTime? paidAt
});


$OrderTotalsDtoCopyWith<$Res> get totals;

}
/// @nodoc
class _$OrderSummaryDtoCopyWithImpl<$Res>
    implements $OrderSummaryDtoCopyWith<$Res> {
  _$OrderSummaryDtoCopyWithImpl(this._self, this._then);

  final OrderSummaryDto _self;
  final $Res Function(OrderSummaryDto) _then;

/// Create a copy of OrderSummaryDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? number = null,Object? status = null,Object? paymentStatus = null,Object? isFinal = null,Object? totals = null,Object? itemCount = null,Object? createdAt = null,Object? thumbnailUrl = freezed,Object? paidAt = freezed,}) {
  return _then(OrderSummaryDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,number: null == number ? _self.number : number // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as String,isFinal: null == isFinal ? _self.isFinal : isFinal // ignore: cast_nullable_to_non_nullable
as bool,totals: null == totals ? _self.totals : totals // ignore: cast_nullable_to_non_nullable
as OrderTotalsDto,itemCount: null == itemCount ? _self.itemCount : itemCount // ignore: cast_nullable_to_non_nullable
as int,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,thumbnailUrl: freezed == thumbnailUrl ? _self.thumbnailUrl : thumbnailUrl // ignore: cast_nullable_to_non_nullable
as String?,paidAt: freezed == paidAt ? _self.paidAt : paidAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}
/// Create a copy of OrderSummaryDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderTotalsDtoCopyWith<$Res> get totals {
  
  return $OrderTotalsDtoCopyWith<$Res>(_self.totals, (value) {
    return _then(_self.copyWith(totals: value));
  });
}
}


/// Adds pattern-matching-related methods to [OrderSummaryDto].
extension OrderSummaryDtoPatterns on OrderSummaryDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderSummaryDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderSummaryDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderSummaryDto value)  $default,){
final _that = this;
switch (_that) {
case _OrderSummaryDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderSummaryDto value)?  $default,){
final _that = this;
switch (_that) {
case _OrderSummaryDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String number,  String status,  String paymentStatus,  bool isFinal,  OrderTotalsDto totals,  int itemCount,  DateTime createdAt,  String? thumbnailUrl,  DateTime? paidAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderSummaryDto() when $default != null:
return $default(_that.publicId,_that.number,_that.status,_that.paymentStatus,_that.isFinal,_that.totals,_that.itemCount,_that.createdAt,_that.thumbnailUrl,_that.paidAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String number,  String status,  String paymentStatus,  bool isFinal,  OrderTotalsDto totals,  int itemCount,  DateTime createdAt,  String? thumbnailUrl,  DateTime? paidAt)  $default,) {final _that = this;
switch (_that) {
case _OrderSummaryDto():
return $default(_that.publicId,_that.number,_that.status,_that.paymentStatus,_that.isFinal,_that.totals,_that.itemCount,_that.createdAt,_that.thumbnailUrl,_that.paidAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String number,  String status,  String paymentStatus,  bool isFinal,  OrderTotalsDto totals,  int itemCount,  DateTime createdAt,  String? thumbnailUrl,  DateTime? paidAt)?  $default,) {final _that = this;
switch (_that) {
case _OrderSummaryDto() when $default != null:
return $default(_that.publicId,_that.number,_that.status,_that.paymentStatus,_that.isFinal,_that.totals,_that.itemCount,_that.createdAt,_that.thumbnailUrl,_that.paidAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _OrderSummaryDto extends OrderSummaryDto {
  const _OrderSummaryDto({required this.publicId, required this.number, required this.status, required this.paymentStatus, required this.isFinal, required this.totals, required this.itemCount, required this.createdAt, this.thumbnailUrl, this.paidAt}): super._();
  factory _OrderSummaryDto.fromJson(Map<String, dynamic> json) => _$OrderSummaryDtoFromJson(json);

@override final  String publicId;
@override final  String number;
@override final  String status;
@override final  String paymentStatus;
@override final  bool isFinal;
@override final  OrderTotalsDto totals;
@override final  int itemCount;
@override final  DateTime createdAt;
@override final  String? thumbnailUrl;
@override final  DateTime? paidAt;

/// Create a copy of OrderSummaryDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderSummaryDtoCopyWith<_OrderSummaryDto> get copyWith => __$OrderSummaryDtoCopyWithImpl<_OrderSummaryDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$OrderSummaryDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderSummaryDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.number, number) || other.number == number)&&(identical(other.status, status) || other.status == status)&&(identical(other.paymentStatus, paymentStatus) || other.paymentStatus == paymentStatus)&&(identical(other.isFinal, isFinal) || other.isFinal == isFinal)&&(identical(other.totals, totals) || other.totals == totals)&&(identical(other.itemCount, itemCount) || other.itemCount == itemCount)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.thumbnailUrl, thumbnailUrl) || other.thumbnailUrl == thumbnailUrl)&&(identical(other.paidAt, paidAt) || other.paidAt == paidAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,number,status,paymentStatus,isFinal,totals,itemCount,createdAt,thumbnailUrl,paidAt);
}

@override
String toString() {
    return 'OrderSummaryDto(publicId: $publicId, number: $number, status: $status, paymentStatus: $paymentStatus, isFinal: $isFinal, totals: $totals, itemCount: $itemCount, createdAt: $createdAt, thumbnailUrl: $thumbnailUrl, paidAt: $paidAt)';
}


}

/// @nodoc
abstract mixin class _$OrderSummaryDtoCopyWith<$Res> implements $OrderSummaryDtoCopyWith<$Res> {
  factory _$OrderSummaryDtoCopyWith(_OrderSummaryDto value, $Res Function(_OrderSummaryDto) _then) = __$OrderSummaryDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String number, String status, String paymentStatus, bool isFinal, OrderTotalsDto totals, int itemCount, DateTime createdAt, String? thumbnailUrl, DateTime? paidAt
});


@override $OrderTotalsDtoCopyWith<$Res> get totals;

}
/// @nodoc
class __$OrderSummaryDtoCopyWithImpl<$Res>
    implements _$OrderSummaryDtoCopyWith<$Res> {
  __$OrderSummaryDtoCopyWithImpl(this._self, this._then);

  final _OrderSummaryDto _self;
  final $Res Function(_OrderSummaryDto) _then;

/// Create a copy of OrderSummaryDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? number = null,Object? status = null,Object? paymentStatus = null,Object? isFinal = null,Object? totals = null,Object? itemCount = null,Object? createdAt = null,Object? thumbnailUrl = freezed,Object? paidAt = freezed,}) {
  return _then(_OrderSummaryDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,number: null == number ? _self.number : number // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as String,isFinal: null == isFinal ? _self.isFinal : isFinal // ignore: cast_nullable_to_non_nullable
as bool,totals: null == totals ? _self.totals : totals // ignore: cast_nullable_to_non_nullable
as OrderTotalsDto,itemCount: null == itemCount ? _self.itemCount : itemCount // ignore: cast_nullable_to_non_nullable
as int,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,thumbnailUrl: freezed == thumbnailUrl ? _self.thumbnailUrl : thumbnailUrl // ignore: cast_nullable_to_non_nullable
as String?,paidAt: freezed == paidAt ? _self.paidAt : paidAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

/// Create a copy of OrderSummaryDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderTotalsDtoCopyWith<$Res> get totals {
  
  return $OrderTotalsDtoCopyWith<$Res>(_self.totals, (value) {
    return _then(_self.copyWith(totals: value));
  });
}
}


/// @nodoc
mixin _$OrderCurrencyDto {

 String get display; String? get payment;
/// Create a copy of OrderCurrencyDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderCurrencyDtoCopyWith<OrderCurrencyDto> get copyWith => _$OrderCurrencyDtoCopyWithImpl<OrderCurrencyDto>(this as OrderCurrencyDto, _$identity);

  /// Serializes this OrderCurrencyDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as OrderCurrencyDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderCurrencyDto&&(identical(other.display, _this.display) || other.display == _this.display)&&(identical(other.payment, _this.payment) || other.payment == _this.payment));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as OrderCurrencyDto;
  return Object.hash(runtimeType,_this.display,_this.payment);
}

@override
String toString() {
  final _this = this as OrderCurrencyDto;
  return 'OrderCurrencyDto(display: ${_this.display}, payment: ${_this.payment})';
}


}

/// @nodoc
abstract mixin class $OrderCurrencyDtoCopyWith<$Res>  {
  factory $OrderCurrencyDtoCopyWith(OrderCurrencyDto value, $Res Function(OrderCurrencyDto) _then) = _$OrderCurrencyDtoCopyWithImpl;
@useResult
$Res call({
 String display, String? payment
});




}
/// @nodoc
class _$OrderCurrencyDtoCopyWithImpl<$Res>
    implements $OrderCurrencyDtoCopyWith<$Res> {
  _$OrderCurrencyDtoCopyWithImpl(this._self, this._then);

  final OrderCurrencyDto _self;
  final $Res Function(OrderCurrencyDto) _then;

/// Create a copy of OrderCurrencyDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? display = null,Object? payment = freezed,}) {
  return _then(OrderCurrencyDto(
display: null == display ? _self.display : display // ignore: cast_nullable_to_non_nullable
as String,payment: freezed == payment ? _self.payment : payment // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderCurrencyDto].
extension OrderCurrencyDtoPatterns on OrderCurrencyDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderCurrencyDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderCurrencyDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderCurrencyDto value)  $default,){
final _that = this;
switch (_that) {
case _OrderCurrencyDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderCurrencyDto value)?  $default,){
final _that = this;
switch (_that) {
case _OrderCurrencyDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String display,  String? payment)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderCurrencyDto() when $default != null:
return $default(_that.display,_that.payment);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String display,  String? payment)  $default,) {final _that = this;
switch (_that) {
case _OrderCurrencyDto():
return $default(_that.display,_that.payment);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String display,  String? payment)?  $default,) {final _that = this;
switch (_that) {
case _OrderCurrencyDto() when $default != null:
return $default(_that.display,_that.payment);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _OrderCurrencyDto implements OrderCurrencyDto {
  const _OrderCurrencyDto({required this.display, this.payment});
  factory _OrderCurrencyDto.fromJson(Map<String, dynamic> json) => _$OrderCurrencyDtoFromJson(json);

@override final  String display;
@override final  String? payment;

/// Create a copy of OrderCurrencyDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderCurrencyDtoCopyWith<_OrderCurrencyDto> get copyWith => __$OrderCurrencyDtoCopyWithImpl<_OrderCurrencyDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$OrderCurrencyDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderCurrencyDto&&(identical(other.display, display) || other.display == display)&&(identical(other.payment, payment) || other.payment == payment));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,display,payment);
}

@override
String toString() {
    return 'OrderCurrencyDto(display: $display, payment: $payment)';
}


}

/// @nodoc
abstract mixin class _$OrderCurrencyDtoCopyWith<$Res> implements $OrderCurrencyDtoCopyWith<$Res> {
  factory _$OrderCurrencyDtoCopyWith(_OrderCurrencyDto value, $Res Function(_OrderCurrencyDto) _then) = __$OrderCurrencyDtoCopyWithImpl;
@override @useResult
$Res call({
 String display, String? payment
});




}
/// @nodoc
class __$OrderCurrencyDtoCopyWithImpl<$Res>
    implements _$OrderCurrencyDtoCopyWith<$Res> {
  __$OrderCurrencyDtoCopyWithImpl(this._self, this._then);

  final _OrderCurrencyDto _self;
  final $Res Function(_OrderCurrencyDto) _then;

/// Create a copy of OrderCurrencyDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? display = null,Object? payment = freezed,}) {
  return _then(_OrderCurrencyDto(
display: null == display ? _self.display : display // ignore: cast_nullable_to_non_nullable
as String,payment: freezed == payment ? _self.payment : payment // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$OrderProductRefDto {

 String? get slug;
/// Create a copy of OrderProductRefDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderProductRefDtoCopyWith<OrderProductRefDto> get copyWith => _$OrderProductRefDtoCopyWithImpl<OrderProductRefDto>(this as OrderProductRefDto, _$identity);

  /// Serializes this OrderProductRefDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as OrderProductRefDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderProductRefDto&&(identical(other.slug, _this.slug) || other.slug == _this.slug));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as OrderProductRefDto;
  return Object.hash(runtimeType,_this.slug);
}

@override
String toString() {
  final _this = this as OrderProductRefDto;
  return 'OrderProductRefDto(slug: ${_this.slug})';
}


}

/// @nodoc
abstract mixin class $OrderProductRefDtoCopyWith<$Res>  {
  factory $OrderProductRefDtoCopyWith(OrderProductRefDto value, $Res Function(OrderProductRefDto) _then) = _$OrderProductRefDtoCopyWithImpl;
@useResult
$Res call({
 String? slug
});




}
/// @nodoc
class _$OrderProductRefDtoCopyWithImpl<$Res>
    implements $OrderProductRefDtoCopyWith<$Res> {
  _$OrderProductRefDtoCopyWithImpl(this._self, this._then);

  final OrderProductRefDto _self;
  final $Res Function(OrderProductRefDto) _then;

/// Create a copy of OrderProductRefDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? slug = freezed,}) {
  return _then(OrderProductRefDto(
slug: freezed == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderProductRefDto].
extension OrderProductRefDtoPatterns on OrderProductRefDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderProductRefDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderProductRefDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderProductRefDto value)  $default,){
final _that = this;
switch (_that) {
case _OrderProductRefDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderProductRefDto value)?  $default,){
final _that = this;
switch (_that) {
case _OrderProductRefDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String? slug)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderProductRefDto() when $default != null:
return $default(_that.slug);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String? slug)  $default,) {final _that = this;
switch (_that) {
case _OrderProductRefDto():
return $default(_that.slug);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String? slug)?  $default,) {final _that = this;
switch (_that) {
case _OrderProductRefDto() when $default != null:
return $default(_that.slug);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _OrderProductRefDto implements OrderProductRefDto {
  const _OrderProductRefDto({this.slug});
  factory _OrderProductRefDto.fromJson(Map<String, dynamic> json) => _$OrderProductRefDtoFromJson(json);

@override final  String? slug;

/// Create a copy of OrderProductRefDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderProductRefDtoCopyWith<_OrderProductRefDto> get copyWith => __$OrderProductRefDtoCopyWithImpl<_OrderProductRefDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$OrderProductRefDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderProductRefDto&&(identical(other.slug, slug) || other.slug == slug));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,slug);
}

@override
String toString() {
    return 'OrderProductRefDto(slug: $slug)';
}


}

/// @nodoc
abstract mixin class _$OrderProductRefDtoCopyWith<$Res> implements $OrderProductRefDtoCopyWith<$Res> {
  factory _$OrderProductRefDtoCopyWith(_OrderProductRefDto value, $Res Function(_OrderProductRefDto) _then) = __$OrderProductRefDtoCopyWithImpl;
@override @useResult
$Res call({
 String? slug
});




}
/// @nodoc
class __$OrderProductRefDtoCopyWithImpl<$Res>
    implements _$OrderProductRefDtoCopyWith<$Res> {
  __$OrderProductRefDtoCopyWithImpl(this._self, this._then);

  final _OrderProductRefDto _self;
  final $Res Function(_OrderProductRefDto) _then;

/// Create a copy of OrderProductRefDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? slug = freezed,}) {
  return _then(_OrderProductRefDto(
slug: freezed == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$OrderUnitDto {

 String get publicId; String get status; bool get revealable; String? get deliveryType; String? get maskedCode; DateTime? get deliveredAt; DateTime? get firstRevealedAt; DateTime? get refundedAt;
/// Create a copy of OrderUnitDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderUnitDtoCopyWith<OrderUnitDto> get copyWith => _$OrderUnitDtoCopyWithImpl<OrderUnitDto>(this as OrderUnitDto, _$identity);

  /// Serializes this OrderUnitDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as OrderUnitDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderUnitDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.revealable, _this.revealable) || other.revealable == _this.revealable)&&(identical(other.deliveryType, _this.deliveryType) || other.deliveryType == _this.deliveryType)&&(identical(other.maskedCode, _this.maskedCode) || other.maskedCode == _this.maskedCode)&&(identical(other.deliveredAt, _this.deliveredAt) || other.deliveredAt == _this.deliveredAt)&&(identical(other.firstRevealedAt, _this.firstRevealedAt) || other.firstRevealedAt == _this.firstRevealedAt)&&(identical(other.refundedAt, _this.refundedAt) || other.refundedAt == _this.refundedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as OrderUnitDto;
  return Object.hash(runtimeType,_this.publicId,_this.status,_this.revealable,_this.deliveryType,_this.maskedCode,_this.deliveredAt,_this.firstRevealedAt,_this.refundedAt);
}

@override
String toString() {
  final _this = this as OrderUnitDto;
  return 'OrderUnitDto(publicId: ${_this.publicId}, status: ${_this.status}, revealable: ${_this.revealable}, deliveryType: ${_this.deliveryType}, maskedCode: ${_this.maskedCode}, deliveredAt: ${_this.deliveredAt}, firstRevealedAt: ${_this.firstRevealedAt}, refundedAt: ${_this.refundedAt})';
}


}

/// @nodoc
abstract mixin class $OrderUnitDtoCopyWith<$Res>  {
  factory $OrderUnitDtoCopyWith(OrderUnitDto value, $Res Function(OrderUnitDto) _then) = _$OrderUnitDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String status, bool revealable, String? deliveryType, String? maskedCode, DateTime? deliveredAt, DateTime? firstRevealedAt, DateTime? refundedAt
});




}
/// @nodoc
class _$OrderUnitDtoCopyWithImpl<$Res>
    implements $OrderUnitDtoCopyWith<$Res> {
  _$OrderUnitDtoCopyWithImpl(this._self, this._then);

  final OrderUnitDto _self;
  final $Res Function(OrderUnitDto) _then;

/// Create a copy of OrderUnitDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? status = null,Object? revealable = null,Object? deliveryType = freezed,Object? maskedCode = freezed,Object? deliveredAt = freezed,Object? firstRevealedAt = freezed,Object? refundedAt = freezed,}) {
  return _then(OrderUnitDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,revealable: null == revealable ? _self.revealable : revealable // ignore: cast_nullable_to_non_nullable
as bool,deliveryType: freezed == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String?,maskedCode: freezed == maskedCode ? _self.maskedCode : maskedCode // ignore: cast_nullable_to_non_nullable
as String?,deliveredAt: freezed == deliveredAt ? _self.deliveredAt : deliveredAt // ignore: cast_nullable_to_non_nullable
as DateTime?,firstRevealedAt: freezed == firstRevealedAt ? _self.firstRevealedAt : firstRevealedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,refundedAt: freezed == refundedAt ? _self.refundedAt : refundedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderUnitDto].
extension OrderUnitDtoPatterns on OrderUnitDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderUnitDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderUnitDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderUnitDto value)  $default,){
final _that = this;
switch (_that) {
case _OrderUnitDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderUnitDto value)?  $default,){
final _that = this;
switch (_that) {
case _OrderUnitDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String status,  bool revealable,  String? deliveryType,  String? maskedCode,  DateTime? deliveredAt,  DateTime? firstRevealedAt,  DateTime? refundedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderUnitDto() when $default != null:
return $default(_that.publicId,_that.status,_that.revealable,_that.deliveryType,_that.maskedCode,_that.deliveredAt,_that.firstRevealedAt,_that.refundedAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String status,  bool revealable,  String? deliveryType,  String? maskedCode,  DateTime? deliveredAt,  DateTime? firstRevealedAt,  DateTime? refundedAt)  $default,) {final _that = this;
switch (_that) {
case _OrderUnitDto():
return $default(_that.publicId,_that.status,_that.revealable,_that.deliveryType,_that.maskedCode,_that.deliveredAt,_that.firstRevealedAt,_that.refundedAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String status,  bool revealable,  String? deliveryType,  String? maskedCode,  DateTime? deliveredAt,  DateTime? firstRevealedAt,  DateTime? refundedAt)?  $default,) {final _that = this;
switch (_that) {
case _OrderUnitDto() when $default != null:
return $default(_that.publicId,_that.status,_that.revealable,_that.deliveryType,_that.maskedCode,_that.deliveredAt,_that.firstRevealedAt,_that.refundedAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _OrderUnitDto extends OrderUnitDto {
  const _OrderUnitDto({required this.publicId, required this.status, required this.revealable, this.deliveryType, this.maskedCode, this.deliveredAt, this.firstRevealedAt, this.refundedAt}): super._();
  factory _OrderUnitDto.fromJson(Map<String, dynamic> json) => _$OrderUnitDtoFromJson(json);

@override final  String publicId;
@override final  String status;
@override final  bool revealable;
@override final  String? deliveryType;
@override final  String? maskedCode;
@override final  DateTime? deliveredAt;
@override final  DateTime? firstRevealedAt;
@override final  DateTime? refundedAt;

/// Create a copy of OrderUnitDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderUnitDtoCopyWith<_OrderUnitDto> get copyWith => __$OrderUnitDtoCopyWithImpl<_OrderUnitDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$OrderUnitDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderUnitDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.status, status) || other.status == status)&&(identical(other.revealable, revealable) || other.revealable == revealable)&&(identical(other.deliveryType, deliveryType) || other.deliveryType == deliveryType)&&(identical(other.maskedCode, maskedCode) || other.maskedCode == maskedCode)&&(identical(other.deliveredAt, deliveredAt) || other.deliveredAt == deliveredAt)&&(identical(other.firstRevealedAt, firstRevealedAt) || other.firstRevealedAt == firstRevealedAt)&&(identical(other.refundedAt, refundedAt) || other.refundedAt == refundedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,status,revealable,deliveryType,maskedCode,deliveredAt,firstRevealedAt,refundedAt);
}

@override
String toString() {
    return 'OrderUnitDto(publicId: $publicId, status: $status, revealable: $revealable, deliveryType: $deliveryType, maskedCode: $maskedCode, deliveredAt: $deliveredAt, firstRevealedAt: $firstRevealedAt, refundedAt: $refundedAt)';
}


}

/// @nodoc
abstract mixin class _$OrderUnitDtoCopyWith<$Res> implements $OrderUnitDtoCopyWith<$Res> {
  factory _$OrderUnitDtoCopyWith(_OrderUnitDto value, $Res Function(_OrderUnitDto) _then) = __$OrderUnitDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String status, bool revealable, String? deliveryType, String? maskedCode, DateTime? deliveredAt, DateTime? firstRevealedAt, DateTime? refundedAt
});




}
/// @nodoc
class __$OrderUnitDtoCopyWithImpl<$Res>
    implements _$OrderUnitDtoCopyWith<$Res> {
  __$OrderUnitDtoCopyWithImpl(this._self, this._then);

  final _OrderUnitDto _self;
  final $Res Function(_OrderUnitDto) _then;

/// Create a copy of OrderUnitDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? status = null,Object? revealable = null,Object? deliveryType = freezed,Object? maskedCode = freezed,Object? deliveredAt = freezed,Object? firstRevealedAt = freezed,Object? refundedAt = freezed,}) {
  return _then(_OrderUnitDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,revealable: null == revealable ? _self.revealable : revealable // ignore: cast_nullable_to_non_nullable
as bool,deliveryType: freezed == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String?,maskedCode: freezed == maskedCode ? _self.maskedCode : maskedCode // ignore: cast_nullable_to_non_nullable
as String?,deliveredAt: freezed == deliveredAt ? _self.deliveredAt : deliveredAt // ignore: cast_nullable_to_non_nullable
as DateTime?,firstRevealedAt: freezed == firstRevealedAt ? _self.firstRevealedAt : firstRevealedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,refundedAt: freezed == refundedAt ? _self.refundedAt : refundedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$OrderItemDto {

 int get id; String get name; int get quantity; List<OrderUnitDto> get units; OrderProductRefDto? get product; String? get deliveryType;@NullableMoneyConverter() Money? get unitPrice;@NullableMoneyConverter() Money? get lineTotal;
/// Create a copy of OrderItemDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderItemDtoCopyWith<OrderItemDto> get copyWith => _$OrderItemDtoCopyWithImpl<OrderItemDto>(this as OrderItemDto, _$identity);

  /// Serializes this OrderItemDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as OrderItemDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderItemDto&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.quantity, _this.quantity) || other.quantity == _this.quantity)&&const DeepCollectionEquality().equals(other.units, _this.units)&&(identical(other.product, _this.product) || other.product == _this.product)&&(identical(other.deliveryType, _this.deliveryType) || other.deliveryType == _this.deliveryType)&&(identical(other.unitPrice, _this.unitPrice) || other.unitPrice == _this.unitPrice)&&(identical(other.lineTotal, _this.lineTotal) || other.lineTotal == _this.lineTotal));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as OrderItemDto;
  return Object.hash(runtimeType,_this.id,_this.name,_this.quantity,const DeepCollectionEquality().hash(_this.units),_this.product,_this.deliveryType,_this.unitPrice,_this.lineTotal);
}

@override
String toString() {
  final _this = this as OrderItemDto;
  return 'OrderItemDto(id: ${_this.id}, name: ${_this.name}, quantity: ${_this.quantity}, units: ${_this.units}, product: ${_this.product}, deliveryType: ${_this.deliveryType}, unitPrice: ${_this.unitPrice}, lineTotal: ${_this.lineTotal})';
}


}

/// @nodoc
abstract mixin class $OrderItemDtoCopyWith<$Res>  {
  factory $OrderItemDtoCopyWith(OrderItemDto value, $Res Function(OrderItemDto) _then) = _$OrderItemDtoCopyWithImpl;
@useResult
$Res call({
 int id, String name, int quantity, List<OrderUnitDto> units, OrderProductRefDto? product, String? deliveryType,@NullableMoneyConverter() Money? unitPrice,@NullableMoneyConverter() Money? lineTotal
});


$OrderProductRefDtoCopyWith<$Res>? get product;

}
/// @nodoc
class _$OrderItemDtoCopyWithImpl<$Res>
    implements $OrderItemDtoCopyWith<$Res> {
  _$OrderItemDtoCopyWithImpl(this._self, this._then);

  final OrderItemDto _self;
  final $Res Function(OrderItemDto) _then;

/// Create a copy of OrderItemDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? name = null,Object? quantity = null,Object? units = null,Object? product = freezed,Object? deliveryType = freezed,Object? unitPrice = freezed,Object? lineTotal = freezed,}) {
  return _then(OrderItemDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,quantity: null == quantity ? _self.quantity : quantity // ignore: cast_nullable_to_non_nullable
as int,units: null == units ? _self.units : units // ignore: cast_nullable_to_non_nullable
as List<OrderUnitDto>,product: freezed == product ? _self.product : product // ignore: cast_nullable_to_non_nullable
as OrderProductRefDto?,deliveryType: freezed == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String?,unitPrice: freezed == unitPrice ? _self.unitPrice : unitPrice // ignore: cast_nullable_to_non_nullable
as Money?,lineTotal: freezed == lineTotal ? _self.lineTotal : lineTotal // ignore: cast_nullable_to_non_nullable
as Money?,
  ));
}
/// Create a copy of OrderItemDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderProductRefDtoCopyWith<$Res>? get product {
    if (_self.product == null) {
    return null;
  }

  return $OrderProductRefDtoCopyWith<$Res>(_self.product!, (value) {
    return _then(_self.copyWith(product: value));
  });
}
}


/// Adds pattern-matching-related methods to [OrderItemDto].
extension OrderItemDtoPatterns on OrderItemDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderItemDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderItemDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderItemDto value)  $default,){
final _that = this;
switch (_that) {
case _OrderItemDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderItemDto value)?  $default,){
final _that = this;
switch (_that) {
case _OrderItemDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  String name,  int quantity,  List<OrderUnitDto> units,  OrderProductRefDto? product,  String? deliveryType, @NullableMoneyConverter()  Money? unitPrice, @NullableMoneyConverter()  Money? lineTotal)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderItemDto() when $default != null:
return $default(_that.id,_that.name,_that.quantity,_that.units,_that.product,_that.deliveryType,_that.unitPrice,_that.lineTotal);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  String name,  int quantity,  List<OrderUnitDto> units,  OrderProductRefDto? product,  String? deliveryType, @NullableMoneyConverter()  Money? unitPrice, @NullableMoneyConverter()  Money? lineTotal)  $default,) {final _that = this;
switch (_that) {
case _OrderItemDto():
return $default(_that.id,_that.name,_that.quantity,_that.units,_that.product,_that.deliveryType,_that.unitPrice,_that.lineTotal);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  String name,  int quantity,  List<OrderUnitDto> units,  OrderProductRefDto? product,  String? deliveryType, @NullableMoneyConverter()  Money? unitPrice, @NullableMoneyConverter()  Money? lineTotal)?  $default,) {final _that = this;
switch (_that) {
case _OrderItemDto() when $default != null:
return $default(_that.id,_that.name,_that.quantity,_that.units,_that.product,_that.deliveryType,_that.unitPrice,_that.lineTotal);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _OrderItemDto extends OrderItemDto {
  const _OrderItemDto({required this.id, required this.name, required this.quantity, required  List<OrderUnitDto> units, this.product, this.deliveryType, @NullableMoneyConverter() this.unitPrice, @NullableMoneyConverter() this.lineTotal}): _units = units,super._();
  factory _OrderItemDto.fromJson(Map<String, dynamic> json) => _$OrderItemDtoFromJson(json);

@override final  int id;
@override final  String name;
@override final  int quantity;
 final  List<OrderUnitDto> _units;
@override List<OrderUnitDto> get units {
  if (_units is EqualUnmodifiableListView) return _units;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_units);
}

@override final  OrderProductRefDto? product;
@override final  String? deliveryType;
@override@NullableMoneyConverter() final  Money? unitPrice;
@override@NullableMoneyConverter() final  Money? lineTotal;

/// Create a copy of OrderItemDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderItemDtoCopyWith<_OrderItemDto> get copyWith => __$OrderItemDtoCopyWithImpl<_OrderItemDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$OrderItemDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderItemDto&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.quantity, quantity) || other.quantity == quantity)&&const DeepCollectionEquality().equals(other.units, _units)&&(identical(other.product, product) || other.product == product)&&(identical(other.deliveryType, deliveryType) || other.deliveryType == deliveryType)&&(identical(other.unitPrice, unitPrice) || other.unitPrice == unitPrice)&&(identical(other.lineTotal, lineTotal) || other.lineTotal == lineTotal));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,id,name,quantity,const DeepCollectionEquality().hash(_units),product,deliveryType,unitPrice,lineTotal);
}

@override
String toString() {
    return 'OrderItemDto(id: $id, name: $name, quantity: $quantity, units: $units, product: $product, deliveryType: $deliveryType, unitPrice: $unitPrice, lineTotal: $lineTotal)';
}


}

/// @nodoc
abstract mixin class _$OrderItemDtoCopyWith<$Res> implements $OrderItemDtoCopyWith<$Res> {
  factory _$OrderItemDtoCopyWith(_OrderItemDto value, $Res Function(_OrderItemDto) _then) = __$OrderItemDtoCopyWithImpl;
@override @useResult
$Res call({
 int id, String name, int quantity, List<OrderUnitDto> units, OrderProductRefDto? product, String? deliveryType,@NullableMoneyConverter() Money? unitPrice,@NullableMoneyConverter() Money? lineTotal
});


@override $OrderProductRefDtoCopyWith<$Res>? get product;

}
/// @nodoc
class __$OrderItemDtoCopyWithImpl<$Res>
    implements _$OrderItemDtoCopyWith<$Res> {
  __$OrderItemDtoCopyWithImpl(this._self, this._then);

  final _OrderItemDto _self;
  final $Res Function(_OrderItemDto) _then;

/// Create a copy of OrderItemDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? name = null,Object? quantity = null,Object? units = null,Object? product = freezed,Object? deliveryType = freezed,Object? unitPrice = freezed,Object? lineTotal = freezed,}) {
  return _then(_OrderItemDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,quantity: null == quantity ? _self.quantity : quantity // ignore: cast_nullable_to_non_nullable
as int,units: null == units ? _self._units : units // ignore: cast_nullable_to_non_nullable
as List<OrderUnitDto>,product: freezed == product ? _self.product : product // ignore: cast_nullable_to_non_nullable
as OrderProductRefDto?,deliveryType: freezed == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String?,unitPrice: freezed == unitPrice ? _self.unitPrice : unitPrice // ignore: cast_nullable_to_non_nullable
as Money?,lineTotal: freezed == lineTotal ? _self.lineTotal : lineTotal // ignore: cast_nullable_to_non_nullable
as Money?,
  ));
}

/// Create a copy of OrderItemDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderProductRefDtoCopyWith<$Res>? get product {
    if (_self.product == null) {
    return null;
  }

  return $OrderProductRefDtoCopyWith<$Res>(_self.product!, (value) {
    return _then(_self.copyWith(product: value));
  });
}
}


/// @nodoc
mixin _$OrderPaymentDto {

 String get publicId; int get attempt; String get status;@MoneyConverter() Money get amount; DateTime get createdAt; String? get provider; String? get method; String? get failureCode;
/// Create a copy of OrderPaymentDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderPaymentDtoCopyWith<OrderPaymentDto> get copyWith => _$OrderPaymentDtoCopyWithImpl<OrderPaymentDto>(this as OrderPaymentDto, _$identity);

  /// Serializes this OrderPaymentDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as OrderPaymentDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderPaymentDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.attempt, _this.attempt) || other.attempt == _this.attempt)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.amount, _this.amount) || other.amount == _this.amount)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.provider, _this.provider) || other.provider == _this.provider)&&(identical(other.method, _this.method) || other.method == _this.method)&&(identical(other.failureCode, _this.failureCode) || other.failureCode == _this.failureCode));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as OrderPaymentDto;
  return Object.hash(runtimeType,_this.publicId,_this.attempt,_this.status,_this.amount,_this.createdAt,_this.provider,_this.method,_this.failureCode);
}

@override
String toString() {
  final _this = this as OrderPaymentDto;
  return 'OrderPaymentDto(publicId: ${_this.publicId}, attempt: ${_this.attempt}, status: ${_this.status}, amount: ${_this.amount}, createdAt: ${_this.createdAt}, provider: ${_this.provider}, method: ${_this.method}, failureCode: ${_this.failureCode})';
}


}

/// @nodoc
abstract mixin class $OrderPaymentDtoCopyWith<$Res>  {
  factory $OrderPaymentDtoCopyWith(OrderPaymentDto value, $Res Function(OrderPaymentDto) _then) = _$OrderPaymentDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, int attempt, String status,@MoneyConverter() Money amount, DateTime createdAt, String? provider, String? method, String? failureCode
});




}
/// @nodoc
class _$OrderPaymentDtoCopyWithImpl<$Res>
    implements $OrderPaymentDtoCopyWith<$Res> {
  _$OrderPaymentDtoCopyWithImpl(this._self, this._then);

  final OrderPaymentDto _self;
  final $Res Function(OrderPaymentDto) _then;

/// Create a copy of OrderPaymentDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? attempt = null,Object? status = null,Object? amount = null,Object? createdAt = null,Object? provider = freezed,Object? method = freezed,Object? failureCode = freezed,}) {
  return _then(OrderPaymentDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,attempt: null == attempt ? _self.attempt : attempt // ignore: cast_nullable_to_non_nullable
as int,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,amount: null == amount ? _self.amount : amount // ignore: cast_nullable_to_non_nullable
as Money,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,provider: freezed == provider ? _self.provider : provider // ignore: cast_nullable_to_non_nullable
as String?,method: freezed == method ? _self.method : method // ignore: cast_nullable_to_non_nullable
as String?,failureCode: freezed == failureCode ? _self.failureCode : failureCode // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderPaymentDto].
extension OrderPaymentDtoPatterns on OrderPaymentDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderPaymentDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderPaymentDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderPaymentDto value)  $default,){
final _that = this;
switch (_that) {
case _OrderPaymentDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderPaymentDto value)?  $default,){
final _that = this;
switch (_that) {
case _OrderPaymentDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  int attempt,  String status, @MoneyConverter()  Money amount,  DateTime createdAt,  String? provider,  String? method,  String? failureCode)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderPaymentDto() when $default != null:
return $default(_that.publicId,_that.attempt,_that.status,_that.amount,_that.createdAt,_that.provider,_that.method,_that.failureCode);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  int attempt,  String status, @MoneyConverter()  Money amount,  DateTime createdAt,  String? provider,  String? method,  String? failureCode)  $default,) {final _that = this;
switch (_that) {
case _OrderPaymentDto():
return $default(_that.publicId,_that.attempt,_that.status,_that.amount,_that.createdAt,_that.provider,_that.method,_that.failureCode);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  int attempt,  String status, @MoneyConverter()  Money amount,  DateTime createdAt,  String? provider,  String? method,  String? failureCode)?  $default,) {final _that = this;
switch (_that) {
case _OrderPaymentDto() when $default != null:
return $default(_that.publicId,_that.attempt,_that.status,_that.amount,_that.createdAt,_that.provider,_that.method,_that.failureCode);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _OrderPaymentDto extends OrderPaymentDto {
  const _OrderPaymentDto({required this.publicId, required this.attempt, required this.status, @MoneyConverter() required this.amount, required this.createdAt, this.provider, this.method, this.failureCode}): super._();
  factory _OrderPaymentDto.fromJson(Map<String, dynamic> json) => _$OrderPaymentDtoFromJson(json);

@override final  String publicId;
@override final  int attempt;
@override final  String status;
@override@MoneyConverter() final  Money amount;
@override final  DateTime createdAt;
@override final  String? provider;
@override final  String? method;
@override final  String? failureCode;

/// Create a copy of OrderPaymentDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderPaymentDtoCopyWith<_OrderPaymentDto> get copyWith => __$OrderPaymentDtoCopyWithImpl<_OrderPaymentDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$OrderPaymentDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderPaymentDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.attempt, attempt) || other.attempt == attempt)&&(identical(other.status, status) || other.status == status)&&(identical(other.amount, amount) || other.amount == amount)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.provider, provider) || other.provider == provider)&&(identical(other.method, method) || other.method == method)&&(identical(other.failureCode, failureCode) || other.failureCode == failureCode));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,attempt,status,amount,createdAt,provider,method,failureCode);
}

@override
String toString() {
    return 'OrderPaymentDto(publicId: $publicId, attempt: $attempt, status: $status, amount: $amount, createdAt: $createdAt, provider: $provider, method: $method, failureCode: $failureCode)';
}


}

/// @nodoc
abstract mixin class _$OrderPaymentDtoCopyWith<$Res> implements $OrderPaymentDtoCopyWith<$Res> {
  factory _$OrderPaymentDtoCopyWith(_OrderPaymentDto value, $Res Function(_OrderPaymentDto) _then) = __$OrderPaymentDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, int attempt, String status,@MoneyConverter() Money amount, DateTime createdAt, String? provider, String? method, String? failureCode
});




}
/// @nodoc
class __$OrderPaymentDtoCopyWithImpl<$Res>
    implements _$OrderPaymentDtoCopyWith<$Res> {
  __$OrderPaymentDtoCopyWithImpl(this._self, this._then);

  final _OrderPaymentDto _self;
  final $Res Function(_OrderPaymentDto) _then;

/// Create a copy of OrderPaymentDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? attempt = null,Object? status = null,Object? amount = null,Object? createdAt = null,Object? provider = freezed,Object? method = freezed,Object? failureCode = freezed,}) {
  return _then(_OrderPaymentDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,attempt: null == attempt ? _self.attempt : attempt // ignore: cast_nullable_to_non_nullable
as int,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,amount: null == amount ? _self.amount : amount // ignore: cast_nullable_to_non_nullable
as Money,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,provider: freezed == provider ? _self.provider : provider // ignore: cast_nullable_to_non_nullable
as String?,method: freezed == method ? _self.method : method // ignore: cast_nullable_to_non_nullable
as String?,failureCode: freezed == failureCode ? _self.failureCode : failureCode // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$OrderRefundDto {

 String get publicId; String get status; String get destination;@MoneyConverter() Money get amount; DateTime? get createdAt; DateTime? get completedAt;
/// Create a copy of OrderRefundDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderRefundDtoCopyWith<OrderRefundDto> get copyWith => _$OrderRefundDtoCopyWithImpl<OrderRefundDto>(this as OrderRefundDto, _$identity);

  /// Serializes this OrderRefundDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as OrderRefundDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderRefundDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.destination, _this.destination) || other.destination == _this.destination)&&(identical(other.amount, _this.amount) || other.amount == _this.amount)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.completedAt, _this.completedAt) || other.completedAt == _this.completedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as OrderRefundDto;
  return Object.hash(runtimeType,_this.publicId,_this.status,_this.destination,_this.amount,_this.createdAt,_this.completedAt);
}

@override
String toString() {
  final _this = this as OrderRefundDto;
  return 'OrderRefundDto(publicId: ${_this.publicId}, status: ${_this.status}, destination: ${_this.destination}, amount: ${_this.amount}, createdAt: ${_this.createdAt}, completedAt: ${_this.completedAt})';
}


}

/// @nodoc
abstract mixin class $OrderRefundDtoCopyWith<$Res>  {
  factory $OrderRefundDtoCopyWith(OrderRefundDto value, $Res Function(OrderRefundDto) _then) = _$OrderRefundDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String status, String destination,@MoneyConverter() Money amount, DateTime? createdAt, DateTime? completedAt
});




}
/// @nodoc
class _$OrderRefundDtoCopyWithImpl<$Res>
    implements $OrderRefundDtoCopyWith<$Res> {
  _$OrderRefundDtoCopyWithImpl(this._self, this._then);

  final OrderRefundDto _self;
  final $Res Function(OrderRefundDto) _then;

/// Create a copy of OrderRefundDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? status = null,Object? destination = null,Object? amount = null,Object? createdAt = freezed,Object? completedAt = freezed,}) {
  return _then(OrderRefundDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,destination: null == destination ? _self.destination : destination // ignore: cast_nullable_to_non_nullable
as String,amount: null == amount ? _self.amount : amount // ignore: cast_nullable_to_non_nullable
as Money,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderRefundDto].
extension OrderRefundDtoPatterns on OrderRefundDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderRefundDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderRefundDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderRefundDto value)  $default,){
final _that = this;
switch (_that) {
case _OrderRefundDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderRefundDto value)?  $default,){
final _that = this;
switch (_that) {
case _OrderRefundDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String status,  String destination, @MoneyConverter()  Money amount,  DateTime? createdAt,  DateTime? completedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderRefundDto() when $default != null:
return $default(_that.publicId,_that.status,_that.destination,_that.amount,_that.createdAt,_that.completedAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String status,  String destination, @MoneyConverter()  Money amount,  DateTime? createdAt,  DateTime? completedAt)  $default,) {final _that = this;
switch (_that) {
case _OrderRefundDto():
return $default(_that.publicId,_that.status,_that.destination,_that.amount,_that.createdAt,_that.completedAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String status,  String destination, @MoneyConverter()  Money amount,  DateTime? createdAt,  DateTime? completedAt)?  $default,) {final _that = this;
switch (_that) {
case _OrderRefundDto() when $default != null:
return $default(_that.publicId,_that.status,_that.destination,_that.amount,_that.createdAt,_that.completedAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _OrderRefundDto extends OrderRefundDto {
  const _OrderRefundDto({required this.publicId, required this.status, required this.destination, @MoneyConverter() required this.amount, this.createdAt, this.completedAt}): super._();
  factory _OrderRefundDto.fromJson(Map<String, dynamic> json) => _$OrderRefundDtoFromJson(json);

@override final  String publicId;
@override final  String status;
@override final  String destination;
@override@MoneyConverter() final  Money amount;
@override final  DateTime? createdAt;
@override final  DateTime? completedAt;

/// Create a copy of OrderRefundDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderRefundDtoCopyWith<_OrderRefundDto> get copyWith => __$OrderRefundDtoCopyWithImpl<_OrderRefundDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$OrderRefundDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderRefundDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.status, status) || other.status == status)&&(identical(other.destination, destination) || other.destination == destination)&&(identical(other.amount, amount) || other.amount == amount)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.completedAt, completedAt) || other.completedAt == completedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,status,destination,amount,createdAt,completedAt);
}

@override
String toString() {
    return 'OrderRefundDto(publicId: $publicId, status: $status, destination: $destination, amount: $amount, createdAt: $createdAt, completedAt: $completedAt)';
}


}

/// @nodoc
abstract mixin class _$OrderRefundDtoCopyWith<$Res> implements $OrderRefundDtoCopyWith<$Res> {
  factory _$OrderRefundDtoCopyWith(_OrderRefundDto value, $Res Function(_OrderRefundDto) _then) = __$OrderRefundDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String status, String destination,@MoneyConverter() Money amount, DateTime? createdAt, DateTime? completedAt
});




}
/// @nodoc
class __$OrderRefundDtoCopyWithImpl<$Res>
    implements _$OrderRefundDtoCopyWith<$Res> {
  __$OrderRefundDtoCopyWithImpl(this._self, this._then);

  final _OrderRefundDto _self;
  final $Res Function(_OrderRefundDto) _then;

/// Create a copy of OrderRefundDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? status = null,Object? destination = null,Object? amount = null,Object? createdAt = freezed,Object? completedAt = freezed,}) {
  return _then(_OrderRefundDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,destination: null == destination ? _self.destination : destination // ignore: cast_nullable_to_non_nullable
as String,amount: null == amount ? _self.amount : amount // ignore: cast_nullable_to_non_nullable
as Money,createdAt: freezed == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime?,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$OrderTimelineDto {

 String get status; DateTime? get at;
/// Create a copy of OrderTimelineDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderTimelineDtoCopyWith<OrderTimelineDto> get copyWith => _$OrderTimelineDtoCopyWithImpl<OrderTimelineDto>(this as OrderTimelineDto, _$identity);

  /// Serializes this OrderTimelineDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as OrderTimelineDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderTimelineDto&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.at, _this.at) || other.at == _this.at));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as OrderTimelineDto;
  return Object.hash(runtimeType,_this.status,_this.at);
}

@override
String toString() {
  final _this = this as OrderTimelineDto;
  return 'OrderTimelineDto(status: ${_this.status}, at: ${_this.at})';
}


}

/// @nodoc
abstract mixin class $OrderTimelineDtoCopyWith<$Res>  {
  factory $OrderTimelineDtoCopyWith(OrderTimelineDto value, $Res Function(OrderTimelineDto) _then) = _$OrderTimelineDtoCopyWithImpl;
@useResult
$Res call({
 String status, DateTime? at
});




}
/// @nodoc
class _$OrderTimelineDtoCopyWithImpl<$Res>
    implements $OrderTimelineDtoCopyWith<$Res> {
  _$OrderTimelineDtoCopyWithImpl(this._self, this._then);

  final OrderTimelineDto _self;
  final $Res Function(OrderTimelineDto) _then;

/// Create a copy of OrderTimelineDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? status = null,Object? at = freezed,}) {
  return _then(OrderTimelineDto(
status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,at: freezed == at ? _self.at : at // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderTimelineDto].
extension OrderTimelineDtoPatterns on OrderTimelineDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderTimelineDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderTimelineDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderTimelineDto value)  $default,){
final _that = this;
switch (_that) {
case _OrderTimelineDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderTimelineDto value)?  $default,){
final _that = this;
switch (_that) {
case _OrderTimelineDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String status,  DateTime? at)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderTimelineDto() when $default != null:
return $default(_that.status,_that.at);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String status,  DateTime? at)  $default,) {final _that = this;
switch (_that) {
case _OrderTimelineDto():
return $default(_that.status,_that.at);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String status,  DateTime? at)?  $default,) {final _that = this;
switch (_that) {
case _OrderTimelineDto() when $default != null:
return $default(_that.status,_that.at);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _OrderTimelineDto implements OrderTimelineDto {
  const _OrderTimelineDto({required this.status, this.at});
  factory _OrderTimelineDto.fromJson(Map<String, dynamic> json) => _$OrderTimelineDtoFromJson(json);

@override final  String status;
@override final  DateTime? at;

/// Create a copy of OrderTimelineDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderTimelineDtoCopyWith<_OrderTimelineDto> get copyWith => __$OrderTimelineDtoCopyWithImpl<_OrderTimelineDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$OrderTimelineDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderTimelineDto&&(identical(other.status, status) || other.status == status)&&(identical(other.at, at) || other.at == at));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,status,at);
}

@override
String toString() {
    return 'OrderTimelineDto(status: $status, at: $at)';
}


}

/// @nodoc
abstract mixin class _$OrderTimelineDtoCopyWith<$Res> implements $OrderTimelineDtoCopyWith<$Res> {
  factory _$OrderTimelineDtoCopyWith(_OrderTimelineDto value, $Res Function(_OrderTimelineDto) _then) = __$OrderTimelineDtoCopyWithImpl;
@override @useResult
$Res call({
 String status, DateTime? at
});




}
/// @nodoc
class __$OrderTimelineDtoCopyWithImpl<$Res>
    implements _$OrderTimelineDtoCopyWith<$Res> {
  __$OrderTimelineDtoCopyWithImpl(this._self, this._then);

  final _OrderTimelineDto _self;
  final $Res Function(_OrderTimelineDto) _then;

/// Create a copy of OrderTimelineDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? status = null,Object? at = freezed,}) {
  return _then(_OrderTimelineDto(
status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,at: freezed == at ? _self.at : at // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}


/// @nodoc
mixin _$OrderDetailDto {

 String get publicId; String get number; String get status; String get paymentStatus; bool get isFinal; OrderTotalsDto get totals; int get itemCount; DateTime get createdAt; OrderCurrencyDto get currency; List<OrderItemDto> get items; List<OrderPaymentDto> get payments; List<OrderRefundDto> get refunds; String get refundState; List<OrderTimelineDto> get timeline; String? get thumbnailUrl; DateTime? get paidAt; DateTime? get completedAt;
/// Create a copy of OrderDetailDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderDetailDtoCopyWith<OrderDetailDto> get copyWith => _$OrderDetailDtoCopyWithImpl<OrderDetailDto>(this as OrderDetailDto, _$identity);

  /// Serializes this OrderDetailDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as OrderDetailDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderDetailDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.number, _this.number) || other.number == _this.number)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.paymentStatus, _this.paymentStatus) || other.paymentStatus == _this.paymentStatus)&&(identical(other.isFinal, _this.isFinal) || other.isFinal == _this.isFinal)&&(identical(other.totals, _this.totals) || other.totals == _this.totals)&&(identical(other.itemCount, _this.itemCount) || other.itemCount == _this.itemCount)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.currency, _this.currency) || other.currency == _this.currency)&&const DeepCollectionEquality().equals(other.items, _this.items)&&const DeepCollectionEquality().equals(other.payments, _this.payments)&&const DeepCollectionEquality().equals(other.refunds, _this.refunds)&&(identical(other.refundState, _this.refundState) || other.refundState == _this.refundState)&&const DeepCollectionEquality().equals(other.timeline, _this.timeline)&&(identical(other.thumbnailUrl, _this.thumbnailUrl) || other.thumbnailUrl == _this.thumbnailUrl)&&(identical(other.paidAt, _this.paidAt) || other.paidAt == _this.paidAt)&&(identical(other.completedAt, _this.completedAt) || other.completedAt == _this.completedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as OrderDetailDto;
  return Object.hash(runtimeType,_this.publicId,_this.number,_this.status,_this.paymentStatus,_this.isFinal,_this.totals,_this.itemCount,_this.createdAt,_this.currency,const DeepCollectionEquality().hash(_this.items),const DeepCollectionEquality().hash(_this.payments),const DeepCollectionEquality().hash(_this.refunds),_this.refundState,const DeepCollectionEquality().hash(_this.timeline),_this.thumbnailUrl,_this.paidAt,_this.completedAt);
}

@override
String toString() {
  final _this = this as OrderDetailDto;
  return 'OrderDetailDto(publicId: ${_this.publicId}, number: ${_this.number}, status: ${_this.status}, paymentStatus: ${_this.paymentStatus}, isFinal: ${_this.isFinal}, totals: ${_this.totals}, itemCount: ${_this.itemCount}, createdAt: ${_this.createdAt}, currency: ${_this.currency}, items: ${_this.items}, payments: ${_this.payments}, refunds: ${_this.refunds}, refundState: ${_this.refundState}, timeline: ${_this.timeline}, thumbnailUrl: ${_this.thumbnailUrl}, paidAt: ${_this.paidAt}, completedAt: ${_this.completedAt})';
}


}

/// @nodoc
abstract mixin class $OrderDetailDtoCopyWith<$Res>  {
  factory $OrderDetailDtoCopyWith(OrderDetailDto value, $Res Function(OrderDetailDto) _then) = _$OrderDetailDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String number, String status, String paymentStatus, bool isFinal, OrderTotalsDto totals, int itemCount, DateTime createdAt, OrderCurrencyDto currency, List<OrderItemDto> items, List<OrderPaymentDto> payments, List<OrderRefundDto> refunds, String refundState, List<OrderTimelineDto> timeline, String? thumbnailUrl, DateTime? paidAt, DateTime? completedAt
});


$OrderTotalsDtoCopyWith<$Res> get totals;$OrderCurrencyDtoCopyWith<$Res> get currency;

}
/// @nodoc
class _$OrderDetailDtoCopyWithImpl<$Res>
    implements $OrderDetailDtoCopyWith<$Res> {
  _$OrderDetailDtoCopyWithImpl(this._self, this._then);

  final OrderDetailDto _self;
  final $Res Function(OrderDetailDto) _then;

/// Create a copy of OrderDetailDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? number = null,Object? status = null,Object? paymentStatus = null,Object? isFinal = null,Object? totals = null,Object? itemCount = null,Object? createdAt = null,Object? currency = null,Object? items = null,Object? payments = null,Object? refunds = null,Object? refundState = null,Object? timeline = null,Object? thumbnailUrl = freezed,Object? paidAt = freezed,Object? completedAt = freezed,}) {
  return _then(OrderDetailDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,number: null == number ? _self.number : number // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as String,isFinal: null == isFinal ? _self.isFinal : isFinal // ignore: cast_nullable_to_non_nullable
as bool,totals: null == totals ? _self.totals : totals // ignore: cast_nullable_to_non_nullable
as OrderTotalsDto,itemCount: null == itemCount ? _self.itemCount : itemCount // ignore: cast_nullable_to_non_nullable
as int,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,currency: null == currency ? _self.currency : currency // ignore: cast_nullable_to_non_nullable
as OrderCurrencyDto,items: null == items ? _self.items : items // ignore: cast_nullable_to_non_nullable
as List<OrderItemDto>,payments: null == payments ? _self.payments : payments // ignore: cast_nullable_to_non_nullable
as List<OrderPaymentDto>,refunds: null == refunds ? _self.refunds : refunds // ignore: cast_nullable_to_non_nullable
as List<OrderRefundDto>,refundState: null == refundState ? _self.refundState : refundState // ignore: cast_nullable_to_non_nullable
as String,timeline: null == timeline ? _self.timeline : timeline // ignore: cast_nullable_to_non_nullable
as List<OrderTimelineDto>,thumbnailUrl: freezed == thumbnailUrl ? _self.thumbnailUrl : thumbnailUrl // ignore: cast_nullable_to_non_nullable
as String?,paidAt: freezed == paidAt ? _self.paidAt : paidAt // ignore: cast_nullable_to_non_nullable
as DateTime?,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}
/// Create a copy of OrderDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderTotalsDtoCopyWith<$Res> get totals {
  
  return $OrderTotalsDtoCopyWith<$Res>(_self.totals, (value) {
    return _then(_self.copyWith(totals: value));
  });
}/// Create a copy of OrderDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderCurrencyDtoCopyWith<$Res> get currency {
  
  return $OrderCurrencyDtoCopyWith<$Res>(_self.currency, (value) {
    return _then(_self.copyWith(currency: value));
  });
}
}


/// Adds pattern-matching-related methods to [OrderDetailDto].
extension OrderDetailDtoPatterns on OrderDetailDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderDetailDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderDetailDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderDetailDto value)  $default,){
final _that = this;
switch (_that) {
case _OrderDetailDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderDetailDto value)?  $default,){
final _that = this;
switch (_that) {
case _OrderDetailDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String number,  String status,  String paymentStatus,  bool isFinal,  OrderTotalsDto totals,  int itemCount,  DateTime createdAt,  OrderCurrencyDto currency,  List<OrderItemDto> items,  List<OrderPaymentDto> payments,  List<OrderRefundDto> refunds,  String refundState,  List<OrderTimelineDto> timeline,  String? thumbnailUrl,  DateTime? paidAt,  DateTime? completedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderDetailDto() when $default != null:
return $default(_that.publicId,_that.number,_that.status,_that.paymentStatus,_that.isFinal,_that.totals,_that.itemCount,_that.createdAt,_that.currency,_that.items,_that.payments,_that.refunds,_that.refundState,_that.timeline,_that.thumbnailUrl,_that.paidAt,_that.completedAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String number,  String status,  String paymentStatus,  bool isFinal,  OrderTotalsDto totals,  int itemCount,  DateTime createdAt,  OrderCurrencyDto currency,  List<OrderItemDto> items,  List<OrderPaymentDto> payments,  List<OrderRefundDto> refunds,  String refundState,  List<OrderTimelineDto> timeline,  String? thumbnailUrl,  DateTime? paidAt,  DateTime? completedAt)  $default,) {final _that = this;
switch (_that) {
case _OrderDetailDto():
return $default(_that.publicId,_that.number,_that.status,_that.paymentStatus,_that.isFinal,_that.totals,_that.itemCount,_that.createdAt,_that.currency,_that.items,_that.payments,_that.refunds,_that.refundState,_that.timeline,_that.thumbnailUrl,_that.paidAt,_that.completedAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String number,  String status,  String paymentStatus,  bool isFinal,  OrderTotalsDto totals,  int itemCount,  DateTime createdAt,  OrderCurrencyDto currency,  List<OrderItemDto> items,  List<OrderPaymentDto> payments,  List<OrderRefundDto> refunds,  String refundState,  List<OrderTimelineDto> timeline,  String? thumbnailUrl,  DateTime? paidAt,  DateTime? completedAt)?  $default,) {final _that = this;
switch (_that) {
case _OrderDetailDto() when $default != null:
return $default(_that.publicId,_that.number,_that.status,_that.paymentStatus,_that.isFinal,_that.totals,_that.itemCount,_that.createdAt,_that.currency,_that.items,_that.payments,_that.refunds,_that.refundState,_that.timeline,_that.thumbnailUrl,_that.paidAt,_that.completedAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _OrderDetailDto extends OrderDetailDto {
  const _OrderDetailDto({required this.publicId, required this.number, required this.status, required this.paymentStatus, required this.isFinal, required this.totals, required this.itemCount, required this.createdAt, required this.currency, required  List<OrderItemDto> items, required  List<OrderPaymentDto> payments, required  List<OrderRefundDto> refunds, required this.refundState, required  List<OrderTimelineDto> timeline, this.thumbnailUrl, this.paidAt, this.completedAt}): _items = items,_payments = payments,_refunds = refunds,_timeline = timeline,super._();
  factory _OrderDetailDto.fromJson(Map<String, dynamic> json) => _$OrderDetailDtoFromJson(json);

@override final  String publicId;
@override final  String number;
@override final  String status;
@override final  String paymentStatus;
@override final  bool isFinal;
@override final  OrderTotalsDto totals;
@override final  int itemCount;
@override final  DateTime createdAt;
@override final  OrderCurrencyDto currency;
 final  List<OrderItemDto> _items;
@override List<OrderItemDto> get items {
  if (_items is EqualUnmodifiableListView) return _items;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_items);
}

 final  List<OrderPaymentDto> _payments;
@override List<OrderPaymentDto> get payments {
  if (_payments is EqualUnmodifiableListView) return _payments;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_payments);
}

 final  List<OrderRefundDto> _refunds;
@override List<OrderRefundDto> get refunds {
  if (_refunds is EqualUnmodifiableListView) return _refunds;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_refunds);
}

@override final  String refundState;
 final  List<OrderTimelineDto> _timeline;
@override List<OrderTimelineDto> get timeline {
  if (_timeline is EqualUnmodifiableListView) return _timeline;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_timeline);
}

@override final  String? thumbnailUrl;
@override final  DateTime? paidAt;
@override final  DateTime? completedAt;

/// Create a copy of OrderDetailDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderDetailDtoCopyWith<_OrderDetailDto> get copyWith => __$OrderDetailDtoCopyWithImpl<_OrderDetailDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$OrderDetailDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderDetailDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.number, number) || other.number == number)&&(identical(other.status, status) || other.status == status)&&(identical(other.paymentStatus, paymentStatus) || other.paymentStatus == paymentStatus)&&(identical(other.isFinal, isFinal) || other.isFinal == isFinal)&&(identical(other.totals, totals) || other.totals == totals)&&(identical(other.itemCount, itemCount) || other.itemCount == itemCount)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.currency, currency) || other.currency == currency)&&const DeepCollectionEquality().equals(other.items, _items)&&const DeepCollectionEquality().equals(other.payments, _payments)&&const DeepCollectionEquality().equals(other.refunds, _refunds)&&(identical(other.refundState, refundState) || other.refundState == refundState)&&const DeepCollectionEquality().equals(other.timeline, _timeline)&&(identical(other.thumbnailUrl, thumbnailUrl) || other.thumbnailUrl == thumbnailUrl)&&(identical(other.paidAt, paidAt) || other.paidAt == paidAt)&&(identical(other.completedAt, completedAt) || other.completedAt == completedAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,number,status,paymentStatus,isFinal,totals,itemCount,createdAt,currency,const DeepCollectionEquality().hash(_items),const DeepCollectionEquality().hash(_payments),const DeepCollectionEquality().hash(_refunds),refundState,const DeepCollectionEquality().hash(_timeline),thumbnailUrl,paidAt,completedAt);
}

@override
String toString() {
    return 'OrderDetailDto(publicId: $publicId, number: $number, status: $status, paymentStatus: $paymentStatus, isFinal: $isFinal, totals: $totals, itemCount: $itemCount, createdAt: $createdAt, currency: $currency, items: $items, payments: $payments, refunds: $refunds, refundState: $refundState, timeline: $timeline, thumbnailUrl: $thumbnailUrl, paidAt: $paidAt, completedAt: $completedAt)';
}


}

/// @nodoc
abstract mixin class _$OrderDetailDtoCopyWith<$Res> implements $OrderDetailDtoCopyWith<$Res> {
  factory _$OrderDetailDtoCopyWith(_OrderDetailDto value, $Res Function(_OrderDetailDto) _then) = __$OrderDetailDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String number, String status, String paymentStatus, bool isFinal, OrderTotalsDto totals, int itemCount, DateTime createdAt, OrderCurrencyDto currency, List<OrderItemDto> items, List<OrderPaymentDto> payments, List<OrderRefundDto> refunds, String refundState, List<OrderTimelineDto> timeline, String? thumbnailUrl, DateTime? paidAt, DateTime? completedAt
});


@override $OrderTotalsDtoCopyWith<$Res> get totals;@override $OrderCurrencyDtoCopyWith<$Res> get currency;

}
/// @nodoc
class __$OrderDetailDtoCopyWithImpl<$Res>
    implements _$OrderDetailDtoCopyWith<$Res> {
  __$OrderDetailDtoCopyWithImpl(this._self, this._then);

  final _OrderDetailDto _self;
  final $Res Function(_OrderDetailDto) _then;

/// Create a copy of OrderDetailDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? number = null,Object? status = null,Object? paymentStatus = null,Object? isFinal = null,Object? totals = null,Object? itemCount = null,Object? createdAt = null,Object? currency = null,Object? items = null,Object? payments = null,Object? refunds = null,Object? refundState = null,Object? timeline = null,Object? thumbnailUrl = freezed,Object? paidAt = freezed,Object? completedAt = freezed,}) {
  return _then(_OrderDetailDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,number: null == number ? _self.number : number // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as String,isFinal: null == isFinal ? _self.isFinal : isFinal // ignore: cast_nullable_to_non_nullable
as bool,totals: null == totals ? _self.totals : totals // ignore: cast_nullable_to_non_nullable
as OrderTotalsDto,itemCount: null == itemCount ? _self.itemCount : itemCount // ignore: cast_nullable_to_non_nullable
as int,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,currency: null == currency ? _self.currency : currency // ignore: cast_nullable_to_non_nullable
as OrderCurrencyDto,items: null == items ? _self._items : items // ignore: cast_nullable_to_non_nullable
as List<OrderItemDto>,payments: null == payments ? _self._payments : payments // ignore: cast_nullable_to_non_nullable
as List<OrderPaymentDto>,refunds: null == refunds ? _self._refunds : refunds // ignore: cast_nullable_to_non_nullable
as List<OrderRefundDto>,refundState: null == refundState ? _self.refundState : refundState // ignore: cast_nullable_to_non_nullable
as String,timeline: null == timeline ? _self._timeline : timeline // ignore: cast_nullable_to_non_nullable
as List<OrderTimelineDto>,thumbnailUrl: freezed == thumbnailUrl ? _self.thumbnailUrl : thumbnailUrl // ignore: cast_nullable_to_non_nullable
as String?,paidAt: freezed == paidAt ? _self.paidAt : paidAt // ignore: cast_nullable_to_non_nullable
as DateTime?,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

/// Create a copy of OrderDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderTotalsDtoCopyWith<$Res> get totals {
  
  return $OrderTotalsDtoCopyWith<$Res>(_self.totals, (value) {
    return _then(_self.copyWith(totals: value));
  });
}/// Create a copy of OrderDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderCurrencyDtoCopyWith<$Res> get currency {
  
  return $OrderCurrencyDtoCopyWith<$Res>(_self.currency, (value) {
    return _then(_self.copyWith(currency: value));
  });
}
}

// dart format on
