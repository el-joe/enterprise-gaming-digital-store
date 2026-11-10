// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'payment_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$PaymentNextDto {

 String get type; String get gateway; String? get paymentPublicId; String? get clientSecret; String? get returnUrl; String? get providerOrderId; String? get approveUrl; String? get redirectUrl;
/// Create a copy of PaymentNextDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PaymentNextDtoCopyWith<PaymentNextDto> get copyWith => _$PaymentNextDtoCopyWithImpl<PaymentNextDto>(this as PaymentNextDto, _$identity);

  /// Serializes this PaymentNextDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as PaymentNextDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PaymentNextDto&&(identical(other.type, _this.type) || other.type == _this.type)&&(identical(other.gateway, _this.gateway) || other.gateway == _this.gateway)&&(identical(other.paymentPublicId, _this.paymentPublicId) || other.paymentPublicId == _this.paymentPublicId)&&(identical(other.clientSecret, _this.clientSecret) || other.clientSecret == _this.clientSecret)&&(identical(other.returnUrl, _this.returnUrl) || other.returnUrl == _this.returnUrl)&&(identical(other.providerOrderId, _this.providerOrderId) || other.providerOrderId == _this.providerOrderId)&&(identical(other.approveUrl, _this.approveUrl) || other.approveUrl == _this.approveUrl)&&(identical(other.redirectUrl, _this.redirectUrl) || other.redirectUrl == _this.redirectUrl));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as PaymentNextDto;
  return Object.hash(runtimeType,_this.type,_this.gateway,_this.paymentPublicId,_this.clientSecret,_this.returnUrl,_this.providerOrderId,_this.approveUrl,_this.redirectUrl);
}



}

/// @nodoc
abstract mixin class $PaymentNextDtoCopyWith<$Res>  {
  factory $PaymentNextDtoCopyWith(PaymentNextDto value, $Res Function(PaymentNextDto) _then) = _$PaymentNextDtoCopyWithImpl;
@useResult
$Res call({
 String type, String gateway, String? paymentPublicId, String? clientSecret, String? returnUrl, String? providerOrderId, String? approveUrl, String? redirectUrl
});




}
/// @nodoc
class _$PaymentNextDtoCopyWithImpl<$Res>
    implements $PaymentNextDtoCopyWith<$Res> {
  _$PaymentNextDtoCopyWithImpl(this._self, this._then);

  final PaymentNextDto _self;
  final $Res Function(PaymentNextDto) _then;

/// Create a copy of PaymentNextDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? type = null,Object? gateway = null,Object? paymentPublicId = freezed,Object? clientSecret = freezed,Object? returnUrl = freezed,Object? providerOrderId = freezed,Object? approveUrl = freezed,Object? redirectUrl = freezed,}) {
  return _then(PaymentNextDto(
type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,paymentPublicId: freezed == paymentPublicId ? _self.paymentPublicId : paymentPublicId // ignore: cast_nullable_to_non_nullable
as String?,clientSecret: freezed == clientSecret ? _self.clientSecret : clientSecret // ignore: cast_nullable_to_non_nullable
as String?,returnUrl: freezed == returnUrl ? _self.returnUrl : returnUrl // ignore: cast_nullable_to_non_nullable
as String?,providerOrderId: freezed == providerOrderId ? _self.providerOrderId : providerOrderId // ignore: cast_nullable_to_non_nullable
as String?,approveUrl: freezed == approveUrl ? _self.approveUrl : approveUrl // ignore: cast_nullable_to_non_nullable
as String?,redirectUrl: freezed == redirectUrl ? _self.redirectUrl : redirectUrl // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [PaymentNextDto].
extension PaymentNextDtoPatterns on PaymentNextDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PaymentNextDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PaymentNextDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PaymentNextDto value)  $default,){
final _that = this;
switch (_that) {
case _PaymentNextDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PaymentNextDto value)?  $default,){
final _that = this;
switch (_that) {
case _PaymentNextDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String type,  String gateway,  String? paymentPublicId,  String? clientSecret,  String? returnUrl,  String? providerOrderId,  String? approveUrl,  String? redirectUrl)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PaymentNextDto() when $default != null:
return $default(_that.type,_that.gateway,_that.paymentPublicId,_that.clientSecret,_that.returnUrl,_that.providerOrderId,_that.approveUrl,_that.redirectUrl);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String type,  String gateway,  String? paymentPublicId,  String? clientSecret,  String? returnUrl,  String? providerOrderId,  String? approveUrl,  String? redirectUrl)  $default,) {final _that = this;
switch (_that) {
case _PaymentNextDto():
return $default(_that.type,_that.gateway,_that.paymentPublicId,_that.clientSecret,_that.returnUrl,_that.providerOrderId,_that.approveUrl,_that.redirectUrl);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String type,  String gateway,  String? paymentPublicId,  String? clientSecret,  String? returnUrl,  String? providerOrderId,  String? approveUrl,  String? redirectUrl)?  $default,) {final _that = this;
switch (_that) {
case _PaymentNextDto() when $default != null:
return $default(_that.type,_that.gateway,_that.paymentPublicId,_that.clientSecret,_that.returnUrl,_that.providerOrderId,_that.approveUrl,_that.redirectUrl);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _PaymentNextDto extends PaymentNextDto {
  const _PaymentNextDto({required this.type, required this.gateway, this.paymentPublicId, this.clientSecret, this.returnUrl, this.providerOrderId, this.approveUrl, this.redirectUrl}): super._();
  factory _PaymentNextDto.fromJson(Map<String, dynamic> json) => _$PaymentNextDtoFromJson(json);

@override final  String type;
@override final  String gateway;
@override final  String? paymentPublicId;
@override final  String? clientSecret;
@override final  String? returnUrl;
@override final  String? providerOrderId;
@override final  String? approveUrl;
@override final  String? redirectUrl;

/// Create a copy of PaymentNextDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PaymentNextDtoCopyWith<_PaymentNextDto> get copyWith => __$PaymentNextDtoCopyWithImpl<_PaymentNextDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$PaymentNextDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PaymentNextDto&&(identical(other.type, type) || other.type == type)&&(identical(other.gateway, gateway) || other.gateway == gateway)&&(identical(other.paymentPublicId, paymentPublicId) || other.paymentPublicId == paymentPublicId)&&(identical(other.clientSecret, clientSecret) || other.clientSecret == clientSecret)&&(identical(other.returnUrl, returnUrl) || other.returnUrl == returnUrl)&&(identical(other.providerOrderId, providerOrderId) || other.providerOrderId == providerOrderId)&&(identical(other.approveUrl, approveUrl) || other.approveUrl == approveUrl)&&(identical(other.redirectUrl, redirectUrl) || other.redirectUrl == redirectUrl));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,type,gateway,paymentPublicId,clientSecret,returnUrl,providerOrderId,approveUrl,redirectUrl);
}



}

/// @nodoc
abstract mixin class _$PaymentNextDtoCopyWith<$Res> implements $PaymentNextDtoCopyWith<$Res> {
  factory _$PaymentNextDtoCopyWith(_PaymentNextDto value, $Res Function(_PaymentNextDto) _then) = __$PaymentNextDtoCopyWithImpl;
@override @useResult
$Res call({
 String type, String gateway, String? paymentPublicId, String? clientSecret, String? returnUrl, String? providerOrderId, String? approveUrl, String? redirectUrl
});




}
/// @nodoc
class __$PaymentNextDtoCopyWithImpl<$Res>
    implements _$PaymentNextDtoCopyWith<$Res> {
  __$PaymentNextDtoCopyWithImpl(this._self, this._then);

  final _PaymentNextDto _self;
  final $Res Function(_PaymentNextDto) _then;

/// Create a copy of PaymentNextDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? type = null,Object? gateway = null,Object? paymentPublicId = freezed,Object? clientSecret = freezed,Object? returnUrl = freezed,Object? providerOrderId = freezed,Object? approveUrl = freezed,Object? redirectUrl = freezed,}) {
  return _then(_PaymentNextDto(
type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,paymentPublicId: freezed == paymentPublicId ? _self.paymentPublicId : paymentPublicId // ignore: cast_nullable_to_non_nullable
as String?,clientSecret: freezed == clientSecret ? _self.clientSecret : clientSecret // ignore: cast_nullable_to_non_nullable
as String?,returnUrl: freezed == returnUrl ? _self.returnUrl : returnUrl // ignore: cast_nullable_to_non_nullable
as String?,providerOrderId: freezed == providerOrderId ? _self.providerOrderId : providerOrderId // ignore: cast_nullable_to_non_nullable
as String?,approveUrl: freezed == approveUrl ? _self.approveUrl : approveUrl // ignore: cast_nullable_to_non_nullable
as String?,redirectUrl: freezed == redirectUrl ? _self.redirectUrl : redirectUrl // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$OrderPaymentStatusDto {

 String get publicId; String get status; String get paymentStatus; bool get isFinal;
/// Create a copy of OrderPaymentStatusDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderPaymentStatusDtoCopyWith<OrderPaymentStatusDto> get copyWith => _$OrderPaymentStatusDtoCopyWithImpl<OrderPaymentStatusDto>(this as OrderPaymentStatusDto, _$identity);

  /// Serializes this OrderPaymentStatusDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as OrderPaymentStatusDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderPaymentStatusDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.paymentStatus, _this.paymentStatus) || other.paymentStatus == _this.paymentStatus)&&(identical(other.isFinal, _this.isFinal) || other.isFinal == _this.isFinal));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as OrderPaymentStatusDto;
  return Object.hash(runtimeType,_this.publicId,_this.status,_this.paymentStatus,_this.isFinal);
}

@override
String toString() {
  final _this = this as OrderPaymentStatusDto;
  return 'OrderPaymentStatusDto(publicId: ${_this.publicId}, status: ${_this.status}, paymentStatus: ${_this.paymentStatus}, isFinal: ${_this.isFinal})';
}


}

/// @nodoc
abstract mixin class $OrderPaymentStatusDtoCopyWith<$Res>  {
  factory $OrderPaymentStatusDtoCopyWith(OrderPaymentStatusDto value, $Res Function(OrderPaymentStatusDto) _then) = _$OrderPaymentStatusDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String status, String paymentStatus, bool isFinal
});




}
/// @nodoc
class _$OrderPaymentStatusDtoCopyWithImpl<$Res>
    implements $OrderPaymentStatusDtoCopyWith<$Res> {
  _$OrderPaymentStatusDtoCopyWithImpl(this._self, this._then);

  final OrderPaymentStatusDto _self;
  final $Res Function(OrderPaymentStatusDto) _then;

/// Create a copy of OrderPaymentStatusDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? status = null,Object? paymentStatus = null,Object? isFinal = null,}) {
  return _then(OrderPaymentStatusDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as String,isFinal: null == isFinal ? _self.isFinal : isFinal // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderPaymentStatusDto].
extension OrderPaymentStatusDtoPatterns on OrderPaymentStatusDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderPaymentStatusDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderPaymentStatusDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderPaymentStatusDto value)  $default,){
final _that = this;
switch (_that) {
case _OrderPaymentStatusDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderPaymentStatusDto value)?  $default,){
final _that = this;
switch (_that) {
case _OrderPaymentStatusDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String status,  String paymentStatus,  bool isFinal)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderPaymentStatusDto() when $default != null:
return $default(_that.publicId,_that.status,_that.paymentStatus,_that.isFinal);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String status,  String paymentStatus,  bool isFinal)  $default,) {final _that = this;
switch (_that) {
case _OrderPaymentStatusDto():
return $default(_that.publicId,_that.status,_that.paymentStatus,_that.isFinal);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String status,  String paymentStatus,  bool isFinal)?  $default,) {final _that = this;
switch (_that) {
case _OrderPaymentStatusDto() when $default != null:
return $default(_that.publicId,_that.status,_that.paymentStatus,_that.isFinal);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _OrderPaymentStatusDto extends OrderPaymentStatusDto {
  const _OrderPaymentStatusDto({required this.publicId, required this.status, required this.paymentStatus, required this.isFinal}): super._();
  factory _OrderPaymentStatusDto.fromJson(Map<String, dynamic> json) => _$OrderPaymentStatusDtoFromJson(json);

@override final  String publicId;
@override final  String status;
@override final  String paymentStatus;
@override final  bool isFinal;

/// Create a copy of OrderPaymentStatusDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderPaymentStatusDtoCopyWith<_OrderPaymentStatusDto> get copyWith => __$OrderPaymentStatusDtoCopyWithImpl<_OrderPaymentStatusDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$OrderPaymentStatusDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderPaymentStatusDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.status, status) || other.status == status)&&(identical(other.paymentStatus, paymentStatus) || other.paymentStatus == paymentStatus)&&(identical(other.isFinal, isFinal) || other.isFinal == isFinal));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,status,paymentStatus,isFinal);
}

@override
String toString() {
    return 'OrderPaymentStatusDto(publicId: $publicId, status: $status, paymentStatus: $paymentStatus, isFinal: $isFinal)';
}


}

/// @nodoc
abstract mixin class _$OrderPaymentStatusDtoCopyWith<$Res> implements $OrderPaymentStatusDtoCopyWith<$Res> {
  factory _$OrderPaymentStatusDtoCopyWith(_OrderPaymentStatusDto value, $Res Function(_OrderPaymentStatusDto) _then) = __$OrderPaymentStatusDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String status, String paymentStatus, bool isFinal
});




}
/// @nodoc
class __$OrderPaymentStatusDtoCopyWithImpl<$Res>
    implements _$OrderPaymentStatusDtoCopyWith<$Res> {
  __$OrderPaymentStatusDtoCopyWithImpl(this._self, this._then);

  final _OrderPaymentStatusDto _self;
  final $Res Function(_OrderPaymentStatusDto) _then;

/// Create a copy of OrderPaymentStatusDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? status = null,Object? paymentStatus = null,Object? isFinal = null,}) {
  return _then(_OrderPaymentStatusDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as String,isFinal: null == isFinal ? _self.isFinal : isFinal // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}


/// @nodoc
mixin _$GuestOrderTokenDto {

 String get token; String get orderPublicId; DateTime get expiresAt;
/// Create a copy of GuestOrderTokenDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$GuestOrderTokenDtoCopyWith<GuestOrderTokenDto> get copyWith => _$GuestOrderTokenDtoCopyWithImpl<GuestOrderTokenDto>(this as GuestOrderTokenDto, _$identity);

  /// Serializes this GuestOrderTokenDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as GuestOrderTokenDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is GuestOrderTokenDto&&(identical(other.token, _this.token) || other.token == _this.token)&&(identical(other.orderPublicId, _this.orderPublicId) || other.orderPublicId == _this.orderPublicId)&&(identical(other.expiresAt, _this.expiresAt) || other.expiresAt == _this.expiresAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as GuestOrderTokenDto;
  return Object.hash(runtimeType,_this.token,_this.orderPublicId,_this.expiresAt);
}



}

/// @nodoc
abstract mixin class $GuestOrderTokenDtoCopyWith<$Res>  {
  factory $GuestOrderTokenDtoCopyWith(GuestOrderTokenDto value, $Res Function(GuestOrderTokenDto) _then) = _$GuestOrderTokenDtoCopyWithImpl;
@useResult
$Res call({
 String token, String orderPublicId, DateTime expiresAt
});




}
/// @nodoc
class _$GuestOrderTokenDtoCopyWithImpl<$Res>
    implements $GuestOrderTokenDtoCopyWith<$Res> {
  _$GuestOrderTokenDtoCopyWithImpl(this._self, this._then);

  final GuestOrderTokenDto _self;
  final $Res Function(GuestOrderTokenDto) _then;

/// Create a copy of GuestOrderTokenDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? token = null,Object? orderPublicId = null,Object? expiresAt = null,}) {
  return _then(GuestOrderTokenDto(
token: null == token ? _self.token : token // ignore: cast_nullable_to_non_nullable
as String,orderPublicId: null == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String,expiresAt: null == expiresAt ? _self.expiresAt : expiresAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}

}


/// Adds pattern-matching-related methods to [GuestOrderTokenDto].
extension GuestOrderTokenDtoPatterns on GuestOrderTokenDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _GuestOrderTokenDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _GuestOrderTokenDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _GuestOrderTokenDto value)  $default,){
final _that = this;
switch (_that) {
case _GuestOrderTokenDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _GuestOrderTokenDto value)?  $default,){
final _that = this;
switch (_that) {
case _GuestOrderTokenDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String token,  String orderPublicId,  DateTime expiresAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _GuestOrderTokenDto() when $default != null:
return $default(_that.token,_that.orderPublicId,_that.expiresAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String token,  String orderPublicId,  DateTime expiresAt)  $default,) {final _that = this;
switch (_that) {
case _GuestOrderTokenDto():
return $default(_that.token,_that.orderPublicId,_that.expiresAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String token,  String orderPublicId,  DateTime expiresAt)?  $default,) {final _that = this;
switch (_that) {
case _GuestOrderTokenDto() when $default != null:
return $default(_that.token,_that.orderPublicId,_that.expiresAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _GuestOrderTokenDto extends GuestOrderTokenDto {
  const _GuestOrderTokenDto({required this.token, required this.orderPublicId, required this.expiresAt}): super._();
  factory _GuestOrderTokenDto.fromJson(Map<String, dynamic> json) => _$GuestOrderTokenDtoFromJson(json);

@override final  String token;
@override final  String orderPublicId;
@override final  DateTime expiresAt;

/// Create a copy of GuestOrderTokenDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$GuestOrderTokenDtoCopyWith<_GuestOrderTokenDto> get copyWith => __$GuestOrderTokenDtoCopyWithImpl<_GuestOrderTokenDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$GuestOrderTokenDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _GuestOrderTokenDto&&(identical(other.token, token) || other.token == token)&&(identical(other.orderPublicId, orderPublicId) || other.orderPublicId == orderPublicId)&&(identical(other.expiresAt, expiresAt) || other.expiresAt == expiresAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,token,orderPublicId,expiresAt);
}



}

/// @nodoc
abstract mixin class _$GuestOrderTokenDtoCopyWith<$Res> implements $GuestOrderTokenDtoCopyWith<$Res> {
  factory _$GuestOrderTokenDtoCopyWith(_GuestOrderTokenDto value, $Res Function(_GuestOrderTokenDto) _then) = __$GuestOrderTokenDtoCopyWithImpl;
@override @useResult
$Res call({
 String token, String orderPublicId, DateTime expiresAt
});




}
/// @nodoc
class __$GuestOrderTokenDtoCopyWithImpl<$Res>
    implements _$GuestOrderTokenDtoCopyWith<$Res> {
  __$GuestOrderTokenDtoCopyWithImpl(this._self, this._then);

  final _GuestOrderTokenDto _self;
  final $Res Function(_GuestOrderTokenDto) _then;

/// Create a copy of GuestOrderTokenDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? token = null,Object? orderPublicId = null,Object? expiresAt = null,}) {
  return _then(_GuestOrderTokenDto(
token: null == token ? _self.token : token // ignore: cast_nullable_to_non_nullable
as String,orderPublicId: null == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String,expiresAt: null == expiresAt ? _self.expiresAt : expiresAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}

// dart format on
