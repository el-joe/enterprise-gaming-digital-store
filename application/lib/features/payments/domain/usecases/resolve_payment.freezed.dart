// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'resolve_payment.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$PaymentResolution {





@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is PaymentResolution);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
    return 'PaymentResolution()';
}


}

/// @nodoc
class $PaymentResolutionCopyWith<$Res>  {
$PaymentResolutionCopyWith(PaymentResolution _, $Res Function(PaymentResolution) __);
}


/// Adds pattern-matching-related methods to [PaymentResolution].
extension PaymentResolutionPatterns on PaymentResolution {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( PaymentResolved value)?  paid,TResult Function( PaymentFinished value)?  finished,TResult Function( PaymentStillProcessing value)?  stillProcessing,required TResult orElse(),}){
final _that = this;
switch (_that) {
case PaymentResolved() when paid != null:
return paid(_that);case PaymentFinished() when finished != null:
return finished(_that);case PaymentStillProcessing() when stillProcessing != null:
return stillProcessing(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( PaymentResolved value)  paid,required TResult Function( PaymentFinished value)  finished,required TResult Function( PaymentStillProcessing value)  stillProcessing,}){
final _that = this;
switch (_that) {
case PaymentResolved():
return paid(_that);case PaymentFinished():
return finished(_that);case PaymentStillProcessing():
return stillProcessing(_that);}
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( PaymentResolved value)?  paid,TResult? Function( PaymentFinished value)?  finished,TResult? Function( PaymentStillProcessing value)?  stillProcessing,}){
final _that = this;
switch (_that) {
case PaymentResolved() when paid != null:
return paid(_that);case PaymentFinished() when finished != null:
return finished(_that);case PaymentStillProcessing() when stillProcessing != null:
return stillProcessing(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( OrderPaymentStatus status)?  paid,TResult Function( OrderPaymentStatus status)?  finished,TResult Function( OrderPaymentStatus? lastKnown)?  stillProcessing,required TResult orElse(),}) {final _that = this;
switch (_that) {
case PaymentResolved() when paid != null:
return paid(_that.status);case PaymentFinished() when finished != null:
return finished(_that.status);case PaymentStillProcessing() when stillProcessing != null:
return stillProcessing(_that.lastKnown);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( OrderPaymentStatus status)  paid,required TResult Function( OrderPaymentStatus status)  finished,required TResult Function( OrderPaymentStatus? lastKnown)  stillProcessing,}) {final _that = this;
switch (_that) {
case PaymentResolved():
return paid(_that.status);case PaymentFinished():
return finished(_that.status);case PaymentStillProcessing():
return stillProcessing(_that.lastKnown);}
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( OrderPaymentStatus status)?  paid,TResult? Function( OrderPaymentStatus status)?  finished,TResult? Function( OrderPaymentStatus? lastKnown)?  stillProcessing,}) {final _that = this;
switch (_that) {
case PaymentResolved() when paid != null:
return paid(_that.status);case PaymentFinished() when finished != null:
return finished(_that.status);case PaymentStillProcessing() when stillProcessing != null:
return stillProcessing(_that.lastKnown);case _:
  return null;

}
}

}

/// @nodoc


class PaymentResolved implements PaymentResolution {
  const PaymentResolved(this.status);
  

 final  OrderPaymentStatus status;

/// Create a copy of PaymentResolution
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PaymentResolvedCopyWith<PaymentResolved> get copyWith => _$PaymentResolvedCopyWithImpl<PaymentResolved>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is PaymentResolved&&(identical(other.status, status) || other.status == status));
}


@override
int get hashCode {
    return Object.hash(runtimeType,status);
}

@override
String toString() {
    return 'PaymentResolution.paid(status: $status)';
}


}

/// @nodoc
abstract mixin class $PaymentResolvedCopyWith<$Res> implements $PaymentResolutionCopyWith<$Res> {
  factory $PaymentResolvedCopyWith(PaymentResolved value, $Res Function(PaymentResolved) _then) = _$PaymentResolvedCopyWithImpl;
@useResult
$Res call({
 OrderPaymentStatus status
});


$OrderPaymentStatusCopyWith<$Res> get status;

}
/// @nodoc
class _$PaymentResolvedCopyWithImpl<$Res>
    implements $PaymentResolvedCopyWith<$Res> {
  _$PaymentResolvedCopyWithImpl(this._self, this._then);

  final PaymentResolved _self;
  final $Res Function(PaymentResolved) _then;

/// Create a copy of PaymentResolution
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? status = null,}) {
  return _then(PaymentResolved(
null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as OrderPaymentStatus,
  ));
}

/// Create a copy of PaymentResolution
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderPaymentStatusCopyWith<$Res> get status {
  
  return $OrderPaymentStatusCopyWith<$Res>(_self.status, (value) {
    return _then(_self.copyWith(status: value));
  });
}
}

/// @nodoc


class PaymentFinished implements PaymentResolution {
  const PaymentFinished(this.status);
  

 final  OrderPaymentStatus status;

/// Create a copy of PaymentResolution
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PaymentFinishedCopyWith<PaymentFinished> get copyWith => _$PaymentFinishedCopyWithImpl<PaymentFinished>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is PaymentFinished&&(identical(other.status, status) || other.status == status));
}


@override
int get hashCode {
    return Object.hash(runtimeType,status);
}

@override
String toString() {
    return 'PaymentResolution.finished(status: $status)';
}


}

/// @nodoc
abstract mixin class $PaymentFinishedCopyWith<$Res> implements $PaymentResolutionCopyWith<$Res> {
  factory $PaymentFinishedCopyWith(PaymentFinished value, $Res Function(PaymentFinished) _then) = _$PaymentFinishedCopyWithImpl;
@useResult
$Res call({
 OrderPaymentStatus status
});


$OrderPaymentStatusCopyWith<$Res> get status;

}
/// @nodoc
class _$PaymentFinishedCopyWithImpl<$Res>
    implements $PaymentFinishedCopyWith<$Res> {
  _$PaymentFinishedCopyWithImpl(this._self, this._then);

  final PaymentFinished _self;
  final $Res Function(PaymentFinished) _then;

/// Create a copy of PaymentResolution
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? status = null,}) {
  return _then(PaymentFinished(
null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as OrderPaymentStatus,
  ));
}

/// Create a copy of PaymentResolution
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderPaymentStatusCopyWith<$Res> get status {
  
  return $OrderPaymentStatusCopyWith<$Res>(_self.status, (value) {
    return _then(_self.copyWith(status: value));
  });
}
}

/// @nodoc


class PaymentStillProcessing implements PaymentResolution {
  const PaymentStillProcessing(this.lastKnown);
  

 final  OrderPaymentStatus? lastKnown;

/// Create a copy of PaymentResolution
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PaymentStillProcessingCopyWith<PaymentStillProcessing> get copyWith => _$PaymentStillProcessingCopyWithImpl<PaymentStillProcessing>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is PaymentStillProcessing&&(identical(other.lastKnown, lastKnown) || other.lastKnown == lastKnown));
}


@override
int get hashCode {
    return Object.hash(runtimeType,lastKnown);
}

@override
String toString() {
    return 'PaymentResolution.stillProcessing(lastKnown: $lastKnown)';
}


}

/// @nodoc
abstract mixin class $PaymentStillProcessingCopyWith<$Res> implements $PaymentResolutionCopyWith<$Res> {
  factory $PaymentStillProcessingCopyWith(PaymentStillProcessing value, $Res Function(PaymentStillProcessing) _then) = _$PaymentStillProcessingCopyWithImpl;
@useResult
$Res call({
 OrderPaymentStatus? lastKnown
});


$OrderPaymentStatusCopyWith<$Res>? get lastKnown;

}
/// @nodoc
class _$PaymentStillProcessingCopyWithImpl<$Res>
    implements $PaymentStillProcessingCopyWith<$Res> {
  _$PaymentStillProcessingCopyWithImpl(this._self, this._then);

  final PaymentStillProcessing _self;
  final $Res Function(PaymentStillProcessing) _then;

/// Create a copy of PaymentResolution
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? lastKnown = freezed,}) {
  return _then(PaymentStillProcessing(
freezed == lastKnown ? _self.lastKnown : lastKnown // ignore: cast_nullable_to_non_nullable
as OrderPaymentStatus?,
  ));
}

/// Create a copy of PaymentResolution
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderPaymentStatusCopyWith<$Res>? get lastKnown {
    if (_self.lastKnown == null) {
    return null;
  }

  return $OrderPaymentStatusCopyWith<$Res>(_self.lastKnown!, (value) {
    return _then(_self.copyWith(lastKnown: value));
  });
}
}

// dart format on
