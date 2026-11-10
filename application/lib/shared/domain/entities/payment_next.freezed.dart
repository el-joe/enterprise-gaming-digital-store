// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'payment_next.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$PaymentNext {

 String get gateway;
/// Create a copy of PaymentNext
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PaymentNextCopyWith<PaymentNext> get copyWith => _$PaymentNextCopyWithImpl<PaymentNext>(this as PaymentNext, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as PaymentNext;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PaymentNext&&(identical(other.gateway, _this.gateway) || other.gateway == _this.gateway));
}


@override
int get hashCode {
  final _this = this as PaymentNext;
  return Object.hash(runtimeType,_this.gateway);
}

@override
String toString() {
  final _this = this as PaymentNext;
  return 'PaymentNext(gateway: ${_this.gateway})';
}


}

/// @nodoc
abstract mixin class $PaymentNextCopyWith<$Res>  {
  factory $PaymentNextCopyWith(PaymentNext value, $Res Function(PaymentNext) _then) = _$PaymentNextCopyWithImpl;
@useResult
$Res call({
 String gateway
});




}
/// @nodoc
class _$PaymentNextCopyWithImpl<$Res>
    implements $PaymentNextCopyWith<$Res> {
  _$PaymentNextCopyWithImpl(this._self, this._then);

  final PaymentNext _self;
  final $Res Function(PaymentNext) _then;

/// Create a copy of PaymentNext
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? gateway = null,}) {
  return _then(_self.copyWith(
gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [PaymentNext].
extension PaymentNextPatterns on PaymentNext {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( StripeNext value)?  stripe,TResult Function( PayPalNext value)?  paypal,TResult Function( RedirectNext value)?  redirect,TResult Function( WalletNext value)?  wallet,TResult Function( UnknownNext value)?  unknown,required TResult orElse(),}){
final _that = this;
switch (_that) {
case StripeNext() when stripe != null:
return stripe(_that);case PayPalNext() when paypal != null:
return paypal(_that);case RedirectNext() when redirect != null:
return redirect(_that);case WalletNext() when wallet != null:
return wallet(_that);case UnknownNext() when unknown != null:
return unknown(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( StripeNext value)  stripe,required TResult Function( PayPalNext value)  paypal,required TResult Function( RedirectNext value)  redirect,required TResult Function( WalletNext value)  wallet,required TResult Function( UnknownNext value)  unknown,}){
final _that = this;
switch (_that) {
case StripeNext():
return stripe(_that);case PayPalNext():
return paypal(_that);case RedirectNext():
return redirect(_that);case WalletNext():
return wallet(_that);case UnknownNext():
return unknown(_that);}
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( StripeNext value)?  stripe,TResult? Function( PayPalNext value)?  paypal,TResult? Function( RedirectNext value)?  redirect,TResult? Function( WalletNext value)?  wallet,TResult? Function( UnknownNext value)?  unknown,}){
final _that = this;
switch (_that) {
case StripeNext() when stripe != null:
return stripe(_that);case PayPalNext() when paypal != null:
return paypal(_that);case RedirectNext() when redirect != null:
return redirect(_that);case WalletNext() when wallet != null:
return wallet(_that);case UnknownNext() when unknown != null:
return unknown(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( String paymentPublicId,  String gateway,  String clientSecret,  Uri? returnUrl)?  stripe,TResult Function( String paymentPublicId,  String gateway,  String providerOrderId,  Uri approveUrl)?  paypal,TResult Function( String paymentPublicId,  String gateway,  Uri redirectUrl)?  redirect,TResult Function( String paymentPublicId,  String gateway)?  wallet,TResult Function( String type,  String gateway)?  unknown,required TResult orElse(),}) {final _that = this;
switch (_that) {
case StripeNext() when stripe != null:
return stripe(_that.paymentPublicId,_that.gateway,_that.clientSecret,_that.returnUrl);case PayPalNext() when paypal != null:
return paypal(_that.paymentPublicId,_that.gateway,_that.providerOrderId,_that.approveUrl);case RedirectNext() when redirect != null:
return redirect(_that.paymentPublicId,_that.gateway,_that.redirectUrl);case WalletNext() when wallet != null:
return wallet(_that.paymentPublicId,_that.gateway);case UnknownNext() when unknown != null:
return unknown(_that.type,_that.gateway);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( String paymentPublicId,  String gateway,  String clientSecret,  Uri? returnUrl)  stripe,required TResult Function( String paymentPublicId,  String gateway,  String providerOrderId,  Uri approveUrl)  paypal,required TResult Function( String paymentPublicId,  String gateway,  Uri redirectUrl)  redirect,required TResult Function( String paymentPublicId,  String gateway)  wallet,required TResult Function( String type,  String gateway)  unknown,}) {final _that = this;
switch (_that) {
case StripeNext():
return stripe(_that.paymentPublicId,_that.gateway,_that.clientSecret,_that.returnUrl);case PayPalNext():
return paypal(_that.paymentPublicId,_that.gateway,_that.providerOrderId,_that.approveUrl);case RedirectNext():
return redirect(_that.paymentPublicId,_that.gateway,_that.redirectUrl);case WalletNext():
return wallet(_that.paymentPublicId,_that.gateway);case UnknownNext():
return unknown(_that.type,_that.gateway);}
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( String paymentPublicId,  String gateway,  String clientSecret,  Uri? returnUrl)?  stripe,TResult? Function( String paymentPublicId,  String gateway,  String providerOrderId,  Uri approveUrl)?  paypal,TResult? Function( String paymentPublicId,  String gateway,  Uri redirectUrl)?  redirect,TResult? Function( String paymentPublicId,  String gateway)?  wallet,TResult? Function( String type,  String gateway)?  unknown,}) {final _that = this;
switch (_that) {
case StripeNext() when stripe != null:
return stripe(_that.paymentPublicId,_that.gateway,_that.clientSecret,_that.returnUrl);case PayPalNext() when paypal != null:
return paypal(_that.paymentPublicId,_that.gateway,_that.providerOrderId,_that.approveUrl);case RedirectNext() when redirect != null:
return redirect(_that.paymentPublicId,_that.gateway,_that.redirectUrl);case WalletNext() when wallet != null:
return wallet(_that.paymentPublicId,_that.gateway);case UnknownNext() when unknown != null:
return unknown(_that.type,_that.gateway);case _:
  return null;

}
}

}

/// @nodoc


class StripeNext implements PaymentNext {
  const StripeNext({required this.paymentPublicId, required this.gateway, required this.clientSecret, this.returnUrl});
  

 final  String paymentPublicId;
@override final  String gateway;
 final  String clientSecret;
 final  Uri? returnUrl;

/// Create a copy of PaymentNext
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$StripeNextCopyWith<StripeNext> get copyWith => _$StripeNextCopyWithImpl<StripeNext>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is StripeNext&&(identical(other.paymentPublicId, paymentPublicId) || other.paymentPublicId == paymentPublicId)&&(identical(other.gateway, gateway) || other.gateway == gateway)&&(identical(other.clientSecret, clientSecret) || other.clientSecret == clientSecret)&&(identical(other.returnUrl, returnUrl) || other.returnUrl == returnUrl));
}


@override
int get hashCode {
    return Object.hash(runtimeType,paymentPublicId,gateway,clientSecret,returnUrl);
}

@override
String toString() {
    return 'PaymentNext.stripe(paymentPublicId: $paymentPublicId, gateway: $gateway, clientSecret: $clientSecret, returnUrl: $returnUrl)';
}


}

/// @nodoc
abstract mixin class $StripeNextCopyWith<$Res> implements $PaymentNextCopyWith<$Res> {
  factory $StripeNextCopyWith(StripeNext value, $Res Function(StripeNext) _then) = _$StripeNextCopyWithImpl;
@override @useResult
$Res call({
 String paymentPublicId, String gateway, String clientSecret, Uri? returnUrl
});




}
/// @nodoc
class _$StripeNextCopyWithImpl<$Res>
    implements $StripeNextCopyWith<$Res> {
  _$StripeNextCopyWithImpl(this._self, this._then);

  final StripeNext _self;
  final $Res Function(StripeNext) _then;

/// Create a copy of PaymentNext
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? paymentPublicId = null,Object? gateway = null,Object? clientSecret = null,Object? returnUrl = freezed,}) {
  return _then(StripeNext(
paymentPublicId: null == paymentPublicId ? _self.paymentPublicId : paymentPublicId // ignore: cast_nullable_to_non_nullable
as String,gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,clientSecret: null == clientSecret ? _self.clientSecret : clientSecret // ignore: cast_nullable_to_non_nullable
as String,returnUrl: freezed == returnUrl ? _self.returnUrl : returnUrl // ignore: cast_nullable_to_non_nullable
as Uri?,
  ));
}


}

/// @nodoc


class PayPalNext implements PaymentNext {
  const PayPalNext({required this.paymentPublicId, required this.gateway, required this.providerOrderId, required this.approveUrl});
  

 final  String paymentPublicId;
@override final  String gateway;
 final  String providerOrderId;
 final  Uri approveUrl;

/// Create a copy of PaymentNext
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PayPalNextCopyWith<PayPalNext> get copyWith => _$PayPalNextCopyWithImpl<PayPalNext>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is PayPalNext&&(identical(other.paymentPublicId, paymentPublicId) || other.paymentPublicId == paymentPublicId)&&(identical(other.gateway, gateway) || other.gateway == gateway)&&(identical(other.providerOrderId, providerOrderId) || other.providerOrderId == providerOrderId)&&(identical(other.approveUrl, approveUrl) || other.approveUrl == approveUrl));
}


@override
int get hashCode {
    return Object.hash(runtimeType,paymentPublicId,gateway,providerOrderId,approveUrl);
}

@override
String toString() {
    return 'PaymentNext.paypal(paymentPublicId: $paymentPublicId, gateway: $gateway, providerOrderId: $providerOrderId, approveUrl: $approveUrl)';
}


}

/// @nodoc
abstract mixin class $PayPalNextCopyWith<$Res> implements $PaymentNextCopyWith<$Res> {
  factory $PayPalNextCopyWith(PayPalNext value, $Res Function(PayPalNext) _then) = _$PayPalNextCopyWithImpl;
@override @useResult
$Res call({
 String paymentPublicId, String gateway, String providerOrderId, Uri approveUrl
});




}
/// @nodoc
class _$PayPalNextCopyWithImpl<$Res>
    implements $PayPalNextCopyWith<$Res> {
  _$PayPalNextCopyWithImpl(this._self, this._then);

  final PayPalNext _self;
  final $Res Function(PayPalNext) _then;

/// Create a copy of PaymentNext
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? paymentPublicId = null,Object? gateway = null,Object? providerOrderId = null,Object? approveUrl = null,}) {
  return _then(PayPalNext(
paymentPublicId: null == paymentPublicId ? _self.paymentPublicId : paymentPublicId // ignore: cast_nullable_to_non_nullable
as String,gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,providerOrderId: null == providerOrderId ? _self.providerOrderId : providerOrderId // ignore: cast_nullable_to_non_nullable
as String,approveUrl: null == approveUrl ? _self.approveUrl : approveUrl // ignore: cast_nullable_to_non_nullable
as Uri,
  ));
}


}

/// @nodoc


class RedirectNext implements PaymentNext {
  const RedirectNext({required this.paymentPublicId, required this.gateway, required this.redirectUrl});
  

 final  String paymentPublicId;
@override final  String gateway;
 final  Uri redirectUrl;

/// Create a copy of PaymentNext
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RedirectNextCopyWith<RedirectNext> get copyWith => _$RedirectNextCopyWithImpl<RedirectNext>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is RedirectNext&&(identical(other.paymentPublicId, paymentPublicId) || other.paymentPublicId == paymentPublicId)&&(identical(other.gateway, gateway) || other.gateway == gateway)&&(identical(other.redirectUrl, redirectUrl) || other.redirectUrl == redirectUrl));
}


@override
int get hashCode {
    return Object.hash(runtimeType,paymentPublicId,gateway,redirectUrl);
}

@override
String toString() {
    return 'PaymentNext.redirect(paymentPublicId: $paymentPublicId, gateway: $gateway, redirectUrl: $redirectUrl)';
}


}

/// @nodoc
abstract mixin class $RedirectNextCopyWith<$Res> implements $PaymentNextCopyWith<$Res> {
  factory $RedirectNextCopyWith(RedirectNext value, $Res Function(RedirectNext) _then) = _$RedirectNextCopyWithImpl;
@override @useResult
$Res call({
 String paymentPublicId, String gateway, Uri redirectUrl
});




}
/// @nodoc
class _$RedirectNextCopyWithImpl<$Res>
    implements $RedirectNextCopyWith<$Res> {
  _$RedirectNextCopyWithImpl(this._self, this._then);

  final RedirectNext _self;
  final $Res Function(RedirectNext) _then;

/// Create a copy of PaymentNext
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? paymentPublicId = null,Object? gateway = null,Object? redirectUrl = null,}) {
  return _then(RedirectNext(
paymentPublicId: null == paymentPublicId ? _self.paymentPublicId : paymentPublicId // ignore: cast_nullable_to_non_nullable
as String,gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,redirectUrl: null == redirectUrl ? _self.redirectUrl : redirectUrl // ignore: cast_nullable_to_non_nullable
as Uri,
  ));
}


}

/// @nodoc


class WalletNext implements PaymentNext {
  const WalletNext({required this.paymentPublicId, required this.gateway});
  

 final  String paymentPublicId;
@override final  String gateway;

/// Create a copy of PaymentNext
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$WalletNextCopyWith<WalletNext> get copyWith => _$WalletNextCopyWithImpl<WalletNext>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is WalletNext&&(identical(other.paymentPublicId, paymentPublicId) || other.paymentPublicId == paymentPublicId)&&(identical(other.gateway, gateway) || other.gateway == gateway));
}


@override
int get hashCode {
    return Object.hash(runtimeType,paymentPublicId,gateway);
}

@override
String toString() {
    return 'PaymentNext.wallet(paymentPublicId: $paymentPublicId, gateway: $gateway)';
}


}

/// @nodoc
abstract mixin class $WalletNextCopyWith<$Res> implements $PaymentNextCopyWith<$Res> {
  factory $WalletNextCopyWith(WalletNext value, $Res Function(WalletNext) _then) = _$WalletNextCopyWithImpl;
@override @useResult
$Res call({
 String paymentPublicId, String gateway
});




}
/// @nodoc
class _$WalletNextCopyWithImpl<$Res>
    implements $WalletNextCopyWith<$Res> {
  _$WalletNextCopyWithImpl(this._self, this._then);

  final WalletNext _self;
  final $Res Function(WalletNext) _then;

/// Create a copy of PaymentNext
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? paymentPublicId = null,Object? gateway = null,}) {
  return _then(WalletNext(
paymentPublicId: null == paymentPublicId ? _self.paymentPublicId : paymentPublicId // ignore: cast_nullable_to_non_nullable
as String,gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc


class UnknownNext implements PaymentNext {
  const UnknownNext({required this.type, required this.gateway});
  

 final  String type;
@override final  String gateway;

/// Create a copy of PaymentNext
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$UnknownNextCopyWith<UnknownNext> get copyWith => _$UnknownNextCopyWithImpl<UnknownNext>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is UnknownNext&&(identical(other.type, type) || other.type == type)&&(identical(other.gateway, gateway) || other.gateway == gateway));
}


@override
int get hashCode {
    return Object.hash(runtimeType,type,gateway);
}

@override
String toString() {
    return 'PaymentNext.unknown(type: $type, gateway: $gateway)';
}


}

/// @nodoc
abstract mixin class $UnknownNextCopyWith<$Res> implements $PaymentNextCopyWith<$Res> {
  factory $UnknownNextCopyWith(UnknownNext value, $Res Function(UnknownNext) _then) = _$UnknownNextCopyWithImpl;
@override @useResult
$Res call({
 String type, String gateway
});




}
/// @nodoc
class _$UnknownNextCopyWithImpl<$Res>
    implements $UnknownNextCopyWith<$Res> {
  _$UnknownNextCopyWithImpl(this._self, this._then);

  final UnknownNext _self;
  final $Res Function(UnknownNext) _then;

/// Create a copy of PaymentNext
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? type = null,Object? gateway = null,}) {
  return _then(UnknownNext(
type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,gateway: null == gateway ? _self.gateway : gateway // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc
mixin _$OrderPaymentStatus {

 String get publicId; String get status;/// `unpaid`, `processing`, `paid`, `partially_refunded` or `refunded`.
 String get paymentStatus; bool get isFinal;
/// Create a copy of OrderPaymentStatus
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderPaymentStatusCopyWith<OrderPaymentStatus> get copyWith => _$OrderPaymentStatusCopyWithImpl<OrderPaymentStatus>(this as OrderPaymentStatus, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as OrderPaymentStatus;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderPaymentStatus&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.paymentStatus, _this.paymentStatus) || other.paymentStatus == _this.paymentStatus)&&(identical(other.isFinal, _this.isFinal) || other.isFinal == _this.isFinal));
}


@override
int get hashCode {
  final _this = this as OrderPaymentStatus;
  return Object.hash(runtimeType,_this.publicId,_this.status,_this.paymentStatus,_this.isFinal);
}

@override
String toString() {
  final _this = this as OrderPaymentStatus;
  return 'OrderPaymentStatus(publicId: ${_this.publicId}, status: ${_this.status}, paymentStatus: ${_this.paymentStatus}, isFinal: ${_this.isFinal})';
}


}

/// @nodoc
abstract mixin class $OrderPaymentStatusCopyWith<$Res>  {
  factory $OrderPaymentStatusCopyWith(OrderPaymentStatus value, $Res Function(OrderPaymentStatus) _then) = _$OrderPaymentStatusCopyWithImpl;
@useResult
$Res call({
 String publicId, String status, String paymentStatus, bool isFinal
});




}
/// @nodoc
class _$OrderPaymentStatusCopyWithImpl<$Res>
    implements $OrderPaymentStatusCopyWith<$Res> {
  _$OrderPaymentStatusCopyWithImpl(this._self, this._then);

  final OrderPaymentStatus _self;
  final $Res Function(OrderPaymentStatus) _then;

/// Create a copy of OrderPaymentStatus
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? status = null,Object? paymentStatus = null,Object? isFinal = null,}) {
  return _then(OrderPaymentStatus(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as String,isFinal: null == isFinal ? _self.isFinal : isFinal // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderPaymentStatus].
extension OrderPaymentStatusPatterns on OrderPaymentStatus {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderPaymentStatus value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderPaymentStatus() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderPaymentStatus value)  $default,){
final _that = this;
switch (_that) {
case _OrderPaymentStatus():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderPaymentStatus value)?  $default,){
final _that = this;
switch (_that) {
case _OrderPaymentStatus() when $default != null:
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
case _OrderPaymentStatus() when $default != null:
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
case _OrderPaymentStatus():
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
case _OrderPaymentStatus() when $default != null:
return $default(_that.publicId,_that.status,_that.paymentStatus,_that.isFinal);case _:
  return null;

}
}

}

/// @nodoc


class _OrderPaymentStatus extends OrderPaymentStatus {
  const _OrderPaymentStatus({required this.publicId, required this.status, required this.paymentStatus, required this.isFinal}): super._();
  

@override final  String publicId;
@override final  String status;
/// `unpaid`, `processing`, `paid`, `partially_refunded` or `refunded`.
@override final  String paymentStatus;
@override final  bool isFinal;

/// Create a copy of OrderPaymentStatus
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderPaymentStatusCopyWith<_OrderPaymentStatus> get copyWith => __$OrderPaymentStatusCopyWithImpl<_OrderPaymentStatus>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderPaymentStatus&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.status, status) || other.status == status)&&(identical(other.paymentStatus, paymentStatus) || other.paymentStatus == paymentStatus)&&(identical(other.isFinal, isFinal) || other.isFinal == isFinal));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,status,paymentStatus,isFinal);
}

@override
String toString() {
    return 'OrderPaymentStatus(publicId: $publicId, status: $status, paymentStatus: $paymentStatus, isFinal: $isFinal)';
}


}

/// @nodoc
abstract mixin class _$OrderPaymentStatusCopyWith<$Res> implements $OrderPaymentStatusCopyWith<$Res> {
  factory _$OrderPaymentStatusCopyWith(_OrderPaymentStatus value, $Res Function(_OrderPaymentStatus) _then) = __$OrderPaymentStatusCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String status, String paymentStatus, bool isFinal
});




}
/// @nodoc
class __$OrderPaymentStatusCopyWithImpl<$Res>
    implements _$OrderPaymentStatusCopyWith<$Res> {
  __$OrderPaymentStatusCopyWithImpl(this._self, this._then);

  final _OrderPaymentStatus _self;
  final $Res Function(_OrderPaymentStatus) _then;

/// Create a copy of OrderPaymentStatus
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? status = null,Object? paymentStatus = null,Object? isFinal = null,}) {
  return _then(_OrderPaymentStatus(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as String,isFinal: null == isFinal ? _self.isFinal : isFinal // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc
mixin _$GuestOrderToken {

 String get token; String get orderPublicId; DateTime get expiresAt;
/// Create a copy of GuestOrderToken
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$GuestOrderTokenCopyWith<GuestOrderToken> get copyWith => _$GuestOrderTokenCopyWithImpl<GuestOrderToken>(this as GuestOrderToken, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as GuestOrderToken;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is GuestOrderToken&&(identical(other.token, _this.token) || other.token == _this.token)&&(identical(other.orderPublicId, _this.orderPublicId) || other.orderPublicId == _this.orderPublicId)&&(identical(other.expiresAt, _this.expiresAt) || other.expiresAt == _this.expiresAt));
}


@override
int get hashCode {
  final _this = this as GuestOrderToken;
  return Object.hash(runtimeType,_this.token,_this.orderPublicId,_this.expiresAt);
}



}

/// @nodoc
abstract mixin class $GuestOrderTokenCopyWith<$Res>  {
  factory $GuestOrderTokenCopyWith(GuestOrderToken value, $Res Function(GuestOrderToken) _then) = _$GuestOrderTokenCopyWithImpl;
@useResult
$Res call({
 String token, String orderPublicId, DateTime expiresAt
});




}
/// @nodoc
class _$GuestOrderTokenCopyWithImpl<$Res>
    implements $GuestOrderTokenCopyWith<$Res> {
  _$GuestOrderTokenCopyWithImpl(this._self, this._then);

  final GuestOrderToken _self;
  final $Res Function(GuestOrderToken) _then;

/// Create a copy of GuestOrderToken
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? token = null,Object? orderPublicId = null,Object? expiresAt = null,}) {
  return _then(GuestOrderToken(
token: null == token ? _self.token : token // ignore: cast_nullable_to_non_nullable
as String,orderPublicId: null == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String,expiresAt: null == expiresAt ? _self.expiresAt : expiresAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}

}


/// Adds pattern-matching-related methods to [GuestOrderToken].
extension GuestOrderTokenPatterns on GuestOrderToken {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _GuestOrderToken value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _GuestOrderToken() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _GuestOrderToken value)  $default,){
final _that = this;
switch (_that) {
case _GuestOrderToken():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _GuestOrderToken value)?  $default,){
final _that = this;
switch (_that) {
case _GuestOrderToken() when $default != null:
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
case _GuestOrderToken() when $default != null:
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
case _GuestOrderToken():
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
case _GuestOrderToken() when $default != null:
return $default(_that.token,_that.orderPublicId,_that.expiresAt);case _:
  return null;

}
}

}

/// @nodoc


class _GuestOrderToken extends GuestOrderToken {
  const _GuestOrderToken({required this.token, required this.orderPublicId, required this.expiresAt}): super._();
  

@override final  String token;
@override final  String orderPublicId;
@override final  DateTime expiresAt;

/// Create a copy of GuestOrderToken
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$GuestOrderTokenCopyWith<_GuestOrderToken> get copyWith => __$GuestOrderTokenCopyWithImpl<_GuestOrderToken>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _GuestOrderToken&&(identical(other.token, token) || other.token == token)&&(identical(other.orderPublicId, orderPublicId) || other.orderPublicId == orderPublicId)&&(identical(other.expiresAt, expiresAt) || other.expiresAt == expiresAt));
}


@override
int get hashCode {
    return Object.hash(runtimeType,token,orderPublicId,expiresAt);
}



}

/// @nodoc
abstract mixin class _$GuestOrderTokenCopyWith<$Res> implements $GuestOrderTokenCopyWith<$Res> {
  factory _$GuestOrderTokenCopyWith(_GuestOrderToken value, $Res Function(_GuestOrderToken) _then) = __$GuestOrderTokenCopyWithImpl;
@override @useResult
$Res call({
 String token, String orderPublicId, DateTime expiresAt
});




}
/// @nodoc
class __$GuestOrderTokenCopyWithImpl<$Res>
    implements _$GuestOrderTokenCopyWith<$Res> {
  __$GuestOrderTokenCopyWithImpl(this._self, this._then);

  final _GuestOrderToken _self;
  final $Res Function(_GuestOrderToken) _then;

/// Create a copy of GuestOrderToken
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? token = null,Object? orderPublicId = null,Object? expiresAt = null,}) {
  return _then(_GuestOrderToken(
token: null == token ? _self.token : token // ignore: cast_nullable_to_non_nullable
as String,orderPublicId: null == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String,expiresAt: null == expiresAt ? _self.expiresAt : expiresAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}

// dart format on
