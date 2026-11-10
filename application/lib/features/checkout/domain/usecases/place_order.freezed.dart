// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'place_order.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$PlaceOrderOutcome {

 PlacedOrder get order;
/// Create a copy of PlaceOrderOutcome
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PlaceOrderOutcomeCopyWith<PlaceOrderOutcome> get copyWith => _$PlaceOrderOutcomeCopyWithImpl<PlaceOrderOutcome>(this as PlaceOrderOutcome, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as PlaceOrderOutcome;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PlaceOrderOutcome&&(identical(other.order, _this.order) || other.order == _this.order));
}


@override
int get hashCode {
  final _this = this as PlaceOrderOutcome;
  return Object.hash(runtimeType,_this.order);
}

@override
String toString() {
  final _this = this as PlaceOrderOutcome;
  return 'PlaceOrderOutcome(order: ${_this.order})';
}


}

/// @nodoc
abstract mixin class $PlaceOrderOutcomeCopyWith<$Res>  {
  factory $PlaceOrderOutcomeCopyWith(PlaceOrderOutcome value, $Res Function(PlaceOrderOutcome) _then) = _$PlaceOrderOutcomeCopyWithImpl;
@useResult
$Res call({
 PlacedOrder order
});


$PlacedOrderCopyWith<$Res> get order;

}
/// @nodoc
class _$PlaceOrderOutcomeCopyWithImpl<$Res>
    implements $PlaceOrderOutcomeCopyWith<$Res> {
  _$PlaceOrderOutcomeCopyWithImpl(this._self, this._then);

  final PlaceOrderOutcome _self;
  final $Res Function(PlaceOrderOutcome) _then;

/// Create a copy of PlaceOrderOutcome
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? order = null,}) {
  return _then(_self.copyWith(
order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as PlacedOrder,
  ));
}
/// Create a copy of PlaceOrderOutcome
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PlacedOrderCopyWith<$Res> get order {
  
  return $PlacedOrderCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}
}


/// Adds pattern-matching-related methods to [PlaceOrderOutcome].
extension PlaceOrderOutcomePatterns on PlaceOrderOutcome {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( PlaceOrderPay value)?  pay,TResult Function( PlaceOrderDuplicate value)?  duplicate,TResult Function( PlaceOrderPaymentNotStarted value)?  paymentNotStarted,required TResult orElse(),}){
final _that = this;
switch (_that) {
case PlaceOrderPay() when pay != null:
return pay(_that);case PlaceOrderDuplicate() when duplicate != null:
return duplicate(_that);case PlaceOrderPaymentNotStarted() when paymentNotStarted != null:
return paymentNotStarted(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( PlaceOrderPay value)  pay,required TResult Function( PlaceOrderDuplicate value)  duplicate,required TResult Function( PlaceOrderPaymentNotStarted value)  paymentNotStarted,}){
final _that = this;
switch (_that) {
case PlaceOrderPay():
return pay(_that);case PlaceOrderDuplicate():
return duplicate(_that);case PlaceOrderPaymentNotStarted():
return paymentNotStarted(_that);}
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( PlaceOrderPay value)?  pay,TResult? Function( PlaceOrderDuplicate value)?  duplicate,TResult? Function( PlaceOrderPaymentNotStarted value)?  paymentNotStarted,}){
final _that = this;
switch (_that) {
case PlaceOrderPay() when pay != null:
return pay(_that);case PlaceOrderDuplicate() when duplicate != null:
return duplicate(_that);case PlaceOrderPaymentNotStarted() when paymentNotStarted != null:
return paymentNotStarted(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( PlacedOrder order,  PaymentNext next,  bool replayed)?  pay,TResult Function( PlacedOrder order)?  duplicate,TResult Function( PlacedOrder order,  Failure failure)?  paymentNotStarted,required TResult orElse(),}) {final _that = this;
switch (_that) {
case PlaceOrderPay() when pay != null:
return pay(_that.order,_that.next,_that.replayed);case PlaceOrderDuplicate() when duplicate != null:
return duplicate(_that.order);case PlaceOrderPaymentNotStarted() when paymentNotStarted != null:
return paymentNotStarted(_that.order,_that.failure);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( PlacedOrder order,  PaymentNext next,  bool replayed)  pay,required TResult Function( PlacedOrder order)  duplicate,required TResult Function( PlacedOrder order,  Failure failure)  paymentNotStarted,}) {final _that = this;
switch (_that) {
case PlaceOrderPay():
return pay(_that.order,_that.next,_that.replayed);case PlaceOrderDuplicate():
return duplicate(_that.order);case PlaceOrderPaymentNotStarted():
return paymentNotStarted(_that.order,_that.failure);}
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( PlacedOrder order,  PaymentNext next,  bool replayed)?  pay,TResult? Function( PlacedOrder order)?  duplicate,TResult? Function( PlacedOrder order,  Failure failure)?  paymentNotStarted,}) {final _that = this;
switch (_that) {
case PlaceOrderPay() when pay != null:
return pay(_that.order,_that.next,_that.replayed);case PlaceOrderDuplicate() when duplicate != null:
return duplicate(_that.order);case PlaceOrderPaymentNotStarted() when paymentNotStarted != null:
return paymentNotStarted(_that.order,_that.failure);case _:
  return null;

}
}

}

/// @nodoc


class PlaceOrderPay implements PlaceOrderOutcome {
  const PlaceOrderPay({required this.order, required this.next, required this.replayed});
  

@override final  PlacedOrder order;
 final  PaymentNext next;
 final  bool replayed;

/// Create a copy of PlaceOrderOutcome
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PlaceOrderPayCopyWith<PlaceOrderPay> get copyWith => _$PlaceOrderPayCopyWithImpl<PlaceOrderPay>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is PlaceOrderPay&&(identical(other.order, order) || other.order == order)&&(identical(other.next, next) || other.next == next)&&(identical(other.replayed, replayed) || other.replayed == replayed));
}


@override
int get hashCode {
    return Object.hash(runtimeType,order,next,replayed);
}

@override
String toString() {
    return 'PlaceOrderOutcome.pay(order: $order, next: $next, replayed: $replayed)';
}


}

/// @nodoc
abstract mixin class $PlaceOrderPayCopyWith<$Res> implements $PlaceOrderOutcomeCopyWith<$Res> {
  factory $PlaceOrderPayCopyWith(PlaceOrderPay value, $Res Function(PlaceOrderPay) _then) = _$PlaceOrderPayCopyWithImpl;
@override @useResult
$Res call({
 PlacedOrder order, PaymentNext next, bool replayed
});


@override $PlacedOrderCopyWith<$Res> get order;$PaymentNextCopyWith<$Res> get next;

}
/// @nodoc
class _$PlaceOrderPayCopyWithImpl<$Res>
    implements $PlaceOrderPayCopyWith<$Res> {
  _$PlaceOrderPayCopyWithImpl(this._self, this._then);

  final PlaceOrderPay _self;
  final $Res Function(PlaceOrderPay) _then;

/// Create a copy of PlaceOrderOutcome
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? order = null,Object? next = null,Object? replayed = null,}) {
  return _then(PlaceOrderPay(
order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as PlacedOrder,next: null == next ? _self.next : next // ignore: cast_nullable_to_non_nullable
as PaymentNext,replayed: null == replayed ? _self.replayed : replayed // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

/// Create a copy of PlaceOrderOutcome
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PlacedOrderCopyWith<$Res> get order {
  
  return $PlacedOrderCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}/// Create a copy of PlaceOrderOutcome
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PaymentNextCopyWith<$Res> get next {
  
  return $PaymentNextCopyWith<$Res>(_self.next, (value) {
    return _then(_self.copyWith(next: value));
  });
}
}

/// @nodoc


class PlaceOrderDuplicate implements PlaceOrderOutcome {
  const PlaceOrderDuplicate({required this.order});
  

@override final  PlacedOrder order;

/// Create a copy of PlaceOrderOutcome
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PlaceOrderDuplicateCopyWith<PlaceOrderDuplicate> get copyWith => _$PlaceOrderDuplicateCopyWithImpl<PlaceOrderDuplicate>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is PlaceOrderDuplicate&&(identical(other.order, order) || other.order == order));
}


@override
int get hashCode {
    return Object.hash(runtimeType,order);
}

@override
String toString() {
    return 'PlaceOrderOutcome.duplicate(order: $order)';
}


}

/// @nodoc
abstract mixin class $PlaceOrderDuplicateCopyWith<$Res> implements $PlaceOrderOutcomeCopyWith<$Res> {
  factory $PlaceOrderDuplicateCopyWith(PlaceOrderDuplicate value, $Res Function(PlaceOrderDuplicate) _then) = _$PlaceOrderDuplicateCopyWithImpl;
@override @useResult
$Res call({
 PlacedOrder order
});


@override $PlacedOrderCopyWith<$Res> get order;

}
/// @nodoc
class _$PlaceOrderDuplicateCopyWithImpl<$Res>
    implements $PlaceOrderDuplicateCopyWith<$Res> {
  _$PlaceOrderDuplicateCopyWithImpl(this._self, this._then);

  final PlaceOrderDuplicate _self;
  final $Res Function(PlaceOrderDuplicate) _then;

/// Create a copy of PlaceOrderOutcome
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? order = null,}) {
  return _then(PlaceOrderDuplicate(
order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as PlacedOrder,
  ));
}

/// Create a copy of PlaceOrderOutcome
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PlacedOrderCopyWith<$Res> get order {
  
  return $PlacedOrderCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}
}

/// @nodoc


class PlaceOrderPaymentNotStarted implements PlaceOrderOutcome {
  const PlaceOrderPaymentNotStarted({required this.order, required this.failure});
  

@override final  PlacedOrder order;
 final  Failure failure;

/// Create a copy of PlaceOrderOutcome
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PlaceOrderPaymentNotStartedCopyWith<PlaceOrderPaymentNotStarted> get copyWith => _$PlaceOrderPaymentNotStartedCopyWithImpl<PlaceOrderPaymentNotStarted>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is PlaceOrderPaymentNotStarted&&(identical(other.order, order) || other.order == order)&&(identical(other.failure, failure) || other.failure == failure));
}


@override
int get hashCode {
    return Object.hash(runtimeType,order,failure);
}

@override
String toString() {
    return 'PlaceOrderOutcome.paymentNotStarted(order: $order, failure: $failure)';
}


}

/// @nodoc
abstract mixin class $PlaceOrderPaymentNotStartedCopyWith<$Res> implements $PlaceOrderOutcomeCopyWith<$Res> {
  factory $PlaceOrderPaymentNotStartedCopyWith(PlaceOrderPaymentNotStarted value, $Res Function(PlaceOrderPaymentNotStarted) _then) = _$PlaceOrderPaymentNotStartedCopyWithImpl;
@override @useResult
$Res call({
 PlacedOrder order, Failure failure
});


@override $PlacedOrderCopyWith<$Res> get order;

}
/// @nodoc
class _$PlaceOrderPaymentNotStartedCopyWithImpl<$Res>
    implements $PlaceOrderPaymentNotStartedCopyWith<$Res> {
  _$PlaceOrderPaymentNotStartedCopyWithImpl(this._self, this._then);

  final PlaceOrderPaymentNotStarted _self;
  final $Res Function(PlaceOrderPaymentNotStarted) _then;

/// Create a copy of PlaceOrderOutcome
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? order = null,Object? failure = null,}) {
  return _then(PlaceOrderPaymentNotStarted(
order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as PlacedOrder,failure: null == failure ? _self.failure : failure // ignore: cast_nullable_to_non_nullable
as Failure,
  ));
}

/// Create a copy of PlaceOrderOutcome
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PlacedOrderCopyWith<$Res> get order {
  
  return $PlacedOrderCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}
}

// dart format on
