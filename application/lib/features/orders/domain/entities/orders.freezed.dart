// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'orders.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$OrderSummary {

 String get publicId; String get number; String get status;/// `unpaid`, `processing`, `paid`, `partially_refunded`, `refunded`.
 String get paymentStatus; bool get isFinal; Money get displayTotal; int get itemCount; DateTime get createdAt; Money? get paymentTotal; Uri? get thumbnailUrl; DateTime? get paidAt;
/// Create a copy of OrderSummary
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderSummaryCopyWith<OrderSummary> get copyWith => _$OrderSummaryCopyWithImpl<OrderSummary>(this as OrderSummary, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as OrderSummary;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderSummary&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.number, _this.number) || other.number == _this.number)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.paymentStatus, _this.paymentStatus) || other.paymentStatus == _this.paymentStatus)&&(identical(other.isFinal, _this.isFinal) || other.isFinal == _this.isFinal)&&(identical(other.displayTotal, _this.displayTotal) || other.displayTotal == _this.displayTotal)&&(identical(other.itemCount, _this.itemCount) || other.itemCount == _this.itemCount)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.paymentTotal, _this.paymentTotal) || other.paymentTotal == _this.paymentTotal)&&(identical(other.thumbnailUrl, _this.thumbnailUrl) || other.thumbnailUrl == _this.thumbnailUrl)&&(identical(other.paidAt, _this.paidAt) || other.paidAt == _this.paidAt));
}


@override
int get hashCode {
  final _this = this as OrderSummary;
  return Object.hash(runtimeType,_this.publicId,_this.number,_this.status,_this.paymentStatus,_this.isFinal,_this.displayTotal,_this.itemCount,_this.createdAt,_this.paymentTotal,_this.thumbnailUrl,_this.paidAt);
}

@override
String toString() {
  final _this = this as OrderSummary;
  return 'OrderSummary(publicId: ${_this.publicId}, number: ${_this.number}, status: ${_this.status}, paymentStatus: ${_this.paymentStatus}, isFinal: ${_this.isFinal}, displayTotal: ${_this.displayTotal}, itemCount: ${_this.itemCount}, createdAt: ${_this.createdAt}, paymentTotal: ${_this.paymentTotal}, thumbnailUrl: ${_this.thumbnailUrl}, paidAt: ${_this.paidAt})';
}


}

/// @nodoc
abstract mixin class $OrderSummaryCopyWith<$Res>  {
  factory $OrderSummaryCopyWith(OrderSummary value, $Res Function(OrderSummary) _then) = _$OrderSummaryCopyWithImpl;
@useResult
$Res call({
 String publicId, String number, String status, String paymentStatus, bool isFinal, Money displayTotal, int itemCount, DateTime createdAt, Money? paymentTotal, Uri? thumbnailUrl, DateTime? paidAt
});




}
/// @nodoc
class _$OrderSummaryCopyWithImpl<$Res>
    implements $OrderSummaryCopyWith<$Res> {
  _$OrderSummaryCopyWithImpl(this._self, this._then);

  final OrderSummary _self;
  final $Res Function(OrderSummary) _then;

/// Create a copy of OrderSummary
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? number = null,Object? status = null,Object? paymentStatus = null,Object? isFinal = null,Object? displayTotal = null,Object? itemCount = null,Object? createdAt = null,Object? paymentTotal = freezed,Object? thumbnailUrl = freezed,Object? paidAt = freezed,}) {
  return _then(OrderSummary(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,number: null == number ? _self.number : number // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as String,isFinal: null == isFinal ? _self.isFinal : isFinal // ignore: cast_nullable_to_non_nullable
as bool,displayTotal: null == displayTotal ? _self.displayTotal : displayTotal // ignore: cast_nullable_to_non_nullable
as Money,itemCount: null == itemCount ? _self.itemCount : itemCount // ignore: cast_nullable_to_non_nullable
as int,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,paymentTotal: freezed == paymentTotal ? _self.paymentTotal : paymentTotal // ignore: cast_nullable_to_non_nullable
as Money?,thumbnailUrl: freezed == thumbnailUrl ? _self.thumbnailUrl : thumbnailUrl // ignore: cast_nullable_to_non_nullable
as Uri?,paidAt: freezed == paidAt ? _self.paidAt : paidAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderSummary].
extension OrderSummaryPatterns on OrderSummary {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderSummary value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderSummary() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderSummary value)  $default,){
final _that = this;
switch (_that) {
case _OrderSummary():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderSummary value)?  $default,){
final _that = this;
switch (_that) {
case _OrderSummary() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String number,  String status,  String paymentStatus,  bool isFinal,  Money displayTotal,  int itemCount,  DateTime createdAt,  Money? paymentTotal,  Uri? thumbnailUrl,  DateTime? paidAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderSummary() when $default != null:
return $default(_that.publicId,_that.number,_that.status,_that.paymentStatus,_that.isFinal,_that.displayTotal,_that.itemCount,_that.createdAt,_that.paymentTotal,_that.thumbnailUrl,_that.paidAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String number,  String status,  String paymentStatus,  bool isFinal,  Money displayTotal,  int itemCount,  DateTime createdAt,  Money? paymentTotal,  Uri? thumbnailUrl,  DateTime? paidAt)  $default,) {final _that = this;
switch (_that) {
case _OrderSummary():
return $default(_that.publicId,_that.number,_that.status,_that.paymentStatus,_that.isFinal,_that.displayTotal,_that.itemCount,_that.createdAt,_that.paymentTotal,_that.thumbnailUrl,_that.paidAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String number,  String status,  String paymentStatus,  bool isFinal,  Money displayTotal,  int itemCount,  DateTime createdAt,  Money? paymentTotal,  Uri? thumbnailUrl,  DateTime? paidAt)?  $default,) {final _that = this;
switch (_that) {
case _OrderSummary() when $default != null:
return $default(_that.publicId,_that.number,_that.status,_that.paymentStatus,_that.isFinal,_that.displayTotal,_that.itemCount,_that.createdAt,_that.paymentTotal,_that.thumbnailUrl,_that.paidAt);case _:
  return null;

}
}

}

/// @nodoc


class _OrderSummary implements OrderSummary {
  const _OrderSummary({required this.publicId, required this.number, required this.status, required this.paymentStatus, required this.isFinal, required this.displayTotal, required this.itemCount, required this.createdAt, this.paymentTotal, this.thumbnailUrl, this.paidAt});
  

@override final  String publicId;
@override final  String number;
@override final  String status;
/// `unpaid`, `processing`, `paid`, `partially_refunded`, `refunded`.
@override final  String paymentStatus;
@override final  bool isFinal;
@override final  Money displayTotal;
@override final  int itemCount;
@override final  DateTime createdAt;
@override final  Money? paymentTotal;
@override final  Uri? thumbnailUrl;
@override final  DateTime? paidAt;

/// Create a copy of OrderSummary
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderSummaryCopyWith<_OrderSummary> get copyWith => __$OrderSummaryCopyWithImpl<_OrderSummary>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderSummary&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.number, number) || other.number == number)&&(identical(other.status, status) || other.status == status)&&(identical(other.paymentStatus, paymentStatus) || other.paymentStatus == paymentStatus)&&(identical(other.isFinal, isFinal) || other.isFinal == isFinal)&&(identical(other.displayTotal, displayTotal) || other.displayTotal == displayTotal)&&(identical(other.itemCount, itemCount) || other.itemCount == itemCount)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.paymentTotal, paymentTotal) || other.paymentTotal == paymentTotal)&&(identical(other.thumbnailUrl, thumbnailUrl) || other.thumbnailUrl == thumbnailUrl)&&(identical(other.paidAt, paidAt) || other.paidAt == paidAt));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,number,status,paymentStatus,isFinal,displayTotal,itemCount,createdAt,paymentTotal,thumbnailUrl,paidAt);
}

@override
String toString() {
    return 'OrderSummary(publicId: $publicId, number: $number, status: $status, paymentStatus: $paymentStatus, isFinal: $isFinal, displayTotal: $displayTotal, itemCount: $itemCount, createdAt: $createdAt, paymentTotal: $paymentTotal, thumbnailUrl: $thumbnailUrl, paidAt: $paidAt)';
}


}

/// @nodoc
abstract mixin class _$OrderSummaryCopyWith<$Res> implements $OrderSummaryCopyWith<$Res> {
  factory _$OrderSummaryCopyWith(_OrderSummary value, $Res Function(_OrderSummary) _then) = __$OrderSummaryCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String number, String status, String paymentStatus, bool isFinal, Money displayTotal, int itemCount, DateTime createdAt, Money? paymentTotal, Uri? thumbnailUrl, DateTime? paidAt
});




}
/// @nodoc
class __$OrderSummaryCopyWithImpl<$Res>
    implements _$OrderSummaryCopyWith<$Res> {
  __$OrderSummaryCopyWithImpl(this._self, this._then);

  final _OrderSummary _self;
  final $Res Function(_OrderSummary) _then;

/// Create a copy of OrderSummary
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? number = null,Object? status = null,Object? paymentStatus = null,Object? isFinal = null,Object? displayTotal = null,Object? itemCount = null,Object? createdAt = null,Object? paymentTotal = freezed,Object? thumbnailUrl = freezed,Object? paidAt = freezed,}) {
  return _then(_OrderSummary(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,number: null == number ? _self.number : number // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,paymentStatus: null == paymentStatus ? _self.paymentStatus : paymentStatus // ignore: cast_nullable_to_non_nullable
as String,isFinal: null == isFinal ? _self.isFinal : isFinal // ignore: cast_nullable_to_non_nullable
as bool,displayTotal: null == displayTotal ? _self.displayTotal : displayTotal // ignore: cast_nullable_to_non_nullable
as Money,itemCount: null == itemCount ? _self.itemCount : itemCount // ignore: cast_nullable_to_non_nullable
as int,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,paymentTotal: freezed == paymentTotal ? _self.paymentTotal : paymentTotal // ignore: cast_nullable_to_non_nullable
as Money?,thumbnailUrl: freezed == thumbnailUrl ? _self.thumbnailUrl : thumbnailUrl // ignore: cast_nullable_to_non_nullable
as Uri?,paidAt: freezed == paidAt ? _self.paidAt : paidAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}

/// @nodoc
mixin _$OrderDetail {

 OrderSummary get summary; String get displayCurrency; List<OrderLine> get items; List<OrderPayment> get payments; List<OrderRefund> get refunds;/// `none`, `pending`, `partial`, `full`.
 String get refundState; List<OrderTimelineEntry> get timeline; String? get paymentCurrency; DateTime? get completedAt;
/// Create a copy of OrderDetail
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderDetailCopyWith<OrderDetail> get copyWith => _$OrderDetailCopyWithImpl<OrderDetail>(this as OrderDetail, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as OrderDetail;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderDetail&&(identical(other.summary, _this.summary) || other.summary == _this.summary)&&(identical(other.displayCurrency, _this.displayCurrency) || other.displayCurrency == _this.displayCurrency)&&const DeepCollectionEquality().equals(other.items, _this.items)&&const DeepCollectionEquality().equals(other.payments, _this.payments)&&const DeepCollectionEquality().equals(other.refunds, _this.refunds)&&(identical(other.refundState, _this.refundState) || other.refundState == _this.refundState)&&const DeepCollectionEquality().equals(other.timeline, _this.timeline)&&(identical(other.paymentCurrency, _this.paymentCurrency) || other.paymentCurrency == _this.paymentCurrency)&&(identical(other.completedAt, _this.completedAt) || other.completedAt == _this.completedAt));
}


@override
int get hashCode {
  final _this = this as OrderDetail;
  return Object.hash(runtimeType,_this.summary,_this.displayCurrency,const DeepCollectionEquality().hash(_this.items),const DeepCollectionEquality().hash(_this.payments),const DeepCollectionEquality().hash(_this.refunds),_this.refundState,const DeepCollectionEquality().hash(_this.timeline),_this.paymentCurrency,_this.completedAt);
}

@override
String toString() {
  final _this = this as OrderDetail;
  return 'OrderDetail(summary: ${_this.summary}, displayCurrency: ${_this.displayCurrency}, items: ${_this.items}, payments: ${_this.payments}, refunds: ${_this.refunds}, refundState: ${_this.refundState}, timeline: ${_this.timeline}, paymentCurrency: ${_this.paymentCurrency}, completedAt: ${_this.completedAt})';
}


}

/// @nodoc
abstract mixin class $OrderDetailCopyWith<$Res>  {
  factory $OrderDetailCopyWith(OrderDetail value, $Res Function(OrderDetail) _then) = _$OrderDetailCopyWithImpl;
@useResult
$Res call({
 OrderSummary summary, String displayCurrency, List<OrderLine> items, List<OrderPayment> payments, List<OrderRefund> refunds, String refundState, List<OrderTimelineEntry> timeline, String? paymentCurrency, DateTime? completedAt
});


$OrderSummaryCopyWith<$Res> get summary;

}
/// @nodoc
class _$OrderDetailCopyWithImpl<$Res>
    implements $OrderDetailCopyWith<$Res> {
  _$OrderDetailCopyWithImpl(this._self, this._then);

  final OrderDetail _self;
  final $Res Function(OrderDetail) _then;

/// Create a copy of OrderDetail
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? summary = null,Object? displayCurrency = null,Object? items = null,Object? payments = null,Object? refunds = null,Object? refundState = null,Object? timeline = null,Object? paymentCurrency = freezed,Object? completedAt = freezed,}) {
  return _then(OrderDetail(
summary: null == summary ? _self.summary : summary // ignore: cast_nullable_to_non_nullable
as OrderSummary,displayCurrency: null == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String,items: null == items ? _self.items : items // ignore: cast_nullable_to_non_nullable
as List<OrderLine>,payments: null == payments ? _self.payments : payments // ignore: cast_nullable_to_non_nullable
as List<OrderPayment>,refunds: null == refunds ? _self.refunds : refunds // ignore: cast_nullable_to_non_nullable
as List<OrderRefund>,refundState: null == refundState ? _self.refundState : refundState // ignore: cast_nullable_to_non_nullable
as String,timeline: null == timeline ? _self.timeline : timeline // ignore: cast_nullable_to_non_nullable
as List<OrderTimelineEntry>,paymentCurrency: freezed == paymentCurrency ? _self.paymentCurrency : paymentCurrency // ignore: cast_nullable_to_non_nullable
as String?,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}
/// Create a copy of OrderDetail
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderSummaryCopyWith<$Res> get summary {
  
  return $OrderSummaryCopyWith<$Res>(_self.summary, (value) {
    return _then(_self.copyWith(summary: value));
  });
}
}


/// Adds pattern-matching-related methods to [OrderDetail].
extension OrderDetailPatterns on OrderDetail {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderDetail value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderDetail() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderDetail value)  $default,){
final _that = this;
switch (_that) {
case _OrderDetail():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderDetail value)?  $default,){
final _that = this;
switch (_that) {
case _OrderDetail() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( OrderSummary summary,  String displayCurrency,  List<OrderLine> items,  List<OrderPayment> payments,  List<OrderRefund> refunds,  String refundState,  List<OrderTimelineEntry> timeline,  String? paymentCurrency,  DateTime? completedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderDetail() when $default != null:
return $default(_that.summary,_that.displayCurrency,_that.items,_that.payments,_that.refunds,_that.refundState,_that.timeline,_that.paymentCurrency,_that.completedAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( OrderSummary summary,  String displayCurrency,  List<OrderLine> items,  List<OrderPayment> payments,  List<OrderRefund> refunds,  String refundState,  List<OrderTimelineEntry> timeline,  String? paymentCurrency,  DateTime? completedAt)  $default,) {final _that = this;
switch (_that) {
case _OrderDetail():
return $default(_that.summary,_that.displayCurrency,_that.items,_that.payments,_that.refunds,_that.refundState,_that.timeline,_that.paymentCurrency,_that.completedAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( OrderSummary summary,  String displayCurrency,  List<OrderLine> items,  List<OrderPayment> payments,  List<OrderRefund> refunds,  String refundState,  List<OrderTimelineEntry> timeline,  String? paymentCurrency,  DateTime? completedAt)?  $default,) {final _that = this;
switch (_that) {
case _OrderDetail() when $default != null:
return $default(_that.summary,_that.displayCurrency,_that.items,_that.payments,_that.refunds,_that.refundState,_that.timeline,_that.paymentCurrency,_that.completedAt);case _:
  return null;

}
}

}

/// @nodoc


class _OrderDetail implements OrderDetail {
  const _OrderDetail({required this.summary, required this.displayCurrency, required  List<OrderLine> items, required  List<OrderPayment> payments, required  List<OrderRefund> refunds, required this.refundState, required  List<OrderTimelineEntry> timeline, this.paymentCurrency, this.completedAt}): _items = items,_payments = payments,_refunds = refunds,_timeline = timeline;
  

@override final  OrderSummary summary;
@override final  String displayCurrency;
 final  List<OrderLine> _items;
@override List<OrderLine> get items {
  if (_items is EqualUnmodifiableListView) return _items;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_items);
}

 final  List<OrderPayment> _payments;
@override List<OrderPayment> get payments {
  if (_payments is EqualUnmodifiableListView) return _payments;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_payments);
}

 final  List<OrderRefund> _refunds;
@override List<OrderRefund> get refunds {
  if (_refunds is EqualUnmodifiableListView) return _refunds;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_refunds);
}

/// `none`, `pending`, `partial`, `full`.
@override final  String refundState;
 final  List<OrderTimelineEntry> _timeline;
@override List<OrderTimelineEntry> get timeline {
  if (_timeline is EqualUnmodifiableListView) return _timeline;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_timeline);
}

@override final  String? paymentCurrency;
@override final  DateTime? completedAt;

/// Create a copy of OrderDetail
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderDetailCopyWith<_OrderDetail> get copyWith => __$OrderDetailCopyWithImpl<_OrderDetail>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderDetail&&(identical(other.summary, summary) || other.summary == summary)&&(identical(other.displayCurrency, displayCurrency) || other.displayCurrency == displayCurrency)&&const DeepCollectionEquality().equals(other.items, _items)&&const DeepCollectionEquality().equals(other.payments, _payments)&&const DeepCollectionEquality().equals(other.refunds, _refunds)&&(identical(other.refundState, refundState) || other.refundState == refundState)&&const DeepCollectionEquality().equals(other.timeline, _timeline)&&(identical(other.paymentCurrency, paymentCurrency) || other.paymentCurrency == paymentCurrency)&&(identical(other.completedAt, completedAt) || other.completedAt == completedAt));
}


@override
int get hashCode {
    return Object.hash(runtimeType,summary,displayCurrency,const DeepCollectionEquality().hash(_items),const DeepCollectionEquality().hash(_payments),const DeepCollectionEquality().hash(_refunds),refundState,const DeepCollectionEquality().hash(_timeline),paymentCurrency,completedAt);
}

@override
String toString() {
    return 'OrderDetail(summary: $summary, displayCurrency: $displayCurrency, items: $items, payments: $payments, refunds: $refunds, refundState: $refundState, timeline: $timeline, paymentCurrency: $paymentCurrency, completedAt: $completedAt)';
}


}

/// @nodoc
abstract mixin class _$OrderDetailCopyWith<$Res> implements $OrderDetailCopyWith<$Res> {
  factory _$OrderDetailCopyWith(_OrderDetail value, $Res Function(_OrderDetail) _then) = __$OrderDetailCopyWithImpl;
@override @useResult
$Res call({
 OrderSummary summary, String displayCurrency, List<OrderLine> items, List<OrderPayment> payments, List<OrderRefund> refunds, String refundState, List<OrderTimelineEntry> timeline, String? paymentCurrency, DateTime? completedAt
});


@override $OrderSummaryCopyWith<$Res> get summary;

}
/// @nodoc
class __$OrderDetailCopyWithImpl<$Res>
    implements _$OrderDetailCopyWith<$Res> {
  __$OrderDetailCopyWithImpl(this._self, this._then);

  final _OrderDetail _self;
  final $Res Function(_OrderDetail) _then;

/// Create a copy of OrderDetail
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? summary = null,Object? displayCurrency = null,Object? items = null,Object? payments = null,Object? refunds = null,Object? refundState = null,Object? timeline = null,Object? paymentCurrency = freezed,Object? completedAt = freezed,}) {
  return _then(_OrderDetail(
summary: null == summary ? _self.summary : summary // ignore: cast_nullable_to_non_nullable
as OrderSummary,displayCurrency: null == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String,items: null == items ? _self._items : items // ignore: cast_nullable_to_non_nullable
as List<OrderLine>,payments: null == payments ? _self._payments : payments // ignore: cast_nullable_to_non_nullable
as List<OrderPayment>,refunds: null == refunds ? _self._refunds : refunds // ignore: cast_nullable_to_non_nullable
as List<OrderRefund>,refundState: null == refundState ? _self.refundState : refundState // ignore: cast_nullable_to_non_nullable
as String,timeline: null == timeline ? _self._timeline : timeline // ignore: cast_nullable_to_non_nullable
as List<OrderTimelineEntry>,paymentCurrency: freezed == paymentCurrency ? _self.paymentCurrency : paymentCurrency // ignore: cast_nullable_to_non_nullable
as String?,completedAt: freezed == completedAt ? _self.completedAt : completedAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

/// Create a copy of OrderDetail
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderSummaryCopyWith<$Res> get summary {
  
  return $OrderSummaryCopyWith<$Res>(_self.summary, (value) {
    return _then(_self.copyWith(summary: value));
  });
}
}

/// @nodoc
mixin _$OrderLine {

 int get id; String get name; int get quantity; List<OrderUnit> get units; String? get productSlug; String? get deliveryType; Money? get unitPrice; Money? get lineTotal;
/// Create a copy of OrderLine
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderLineCopyWith<OrderLine> get copyWith => _$OrderLineCopyWithImpl<OrderLine>(this as OrderLine, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as OrderLine;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderLine&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.quantity, _this.quantity) || other.quantity == _this.quantity)&&const DeepCollectionEquality().equals(other.units, _this.units)&&(identical(other.productSlug, _this.productSlug) || other.productSlug == _this.productSlug)&&(identical(other.deliveryType, _this.deliveryType) || other.deliveryType == _this.deliveryType)&&(identical(other.unitPrice, _this.unitPrice) || other.unitPrice == _this.unitPrice)&&(identical(other.lineTotal, _this.lineTotal) || other.lineTotal == _this.lineTotal));
}


@override
int get hashCode {
  final _this = this as OrderLine;
  return Object.hash(runtimeType,_this.id,_this.name,_this.quantity,const DeepCollectionEquality().hash(_this.units),_this.productSlug,_this.deliveryType,_this.unitPrice,_this.lineTotal);
}

@override
String toString() {
  final _this = this as OrderLine;
  return 'OrderLine(id: ${_this.id}, name: ${_this.name}, quantity: ${_this.quantity}, units: ${_this.units}, productSlug: ${_this.productSlug}, deliveryType: ${_this.deliveryType}, unitPrice: ${_this.unitPrice}, lineTotal: ${_this.lineTotal})';
}


}

/// @nodoc
abstract mixin class $OrderLineCopyWith<$Res>  {
  factory $OrderLineCopyWith(OrderLine value, $Res Function(OrderLine) _then) = _$OrderLineCopyWithImpl;
@useResult
$Res call({
 int id, String name, int quantity, List<OrderUnit> units, String? productSlug, String? deliveryType, Money? unitPrice, Money? lineTotal
});




}
/// @nodoc
class _$OrderLineCopyWithImpl<$Res>
    implements $OrderLineCopyWith<$Res> {
  _$OrderLineCopyWithImpl(this._self, this._then);

  final OrderLine _self;
  final $Res Function(OrderLine) _then;

/// Create a copy of OrderLine
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? name = null,Object? quantity = null,Object? units = null,Object? productSlug = freezed,Object? deliveryType = freezed,Object? unitPrice = freezed,Object? lineTotal = freezed,}) {
  return _then(OrderLine(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,quantity: null == quantity ? _self.quantity : quantity // ignore: cast_nullable_to_non_nullable
as int,units: null == units ? _self.units : units // ignore: cast_nullable_to_non_nullable
as List<OrderUnit>,productSlug: freezed == productSlug ? _self.productSlug : productSlug // ignore: cast_nullable_to_non_nullable
as String?,deliveryType: freezed == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String?,unitPrice: freezed == unitPrice ? _self.unitPrice : unitPrice // ignore: cast_nullable_to_non_nullable
as Money?,lineTotal: freezed == lineTotal ? _self.lineTotal : lineTotal // ignore: cast_nullable_to_non_nullable
as Money?,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderLine].
extension OrderLinePatterns on OrderLine {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderLine value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderLine() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderLine value)  $default,){
final _that = this;
switch (_that) {
case _OrderLine():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderLine value)?  $default,){
final _that = this;
switch (_that) {
case _OrderLine() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  String name,  int quantity,  List<OrderUnit> units,  String? productSlug,  String? deliveryType,  Money? unitPrice,  Money? lineTotal)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderLine() when $default != null:
return $default(_that.id,_that.name,_that.quantity,_that.units,_that.productSlug,_that.deliveryType,_that.unitPrice,_that.lineTotal);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  String name,  int quantity,  List<OrderUnit> units,  String? productSlug,  String? deliveryType,  Money? unitPrice,  Money? lineTotal)  $default,) {final _that = this;
switch (_that) {
case _OrderLine():
return $default(_that.id,_that.name,_that.quantity,_that.units,_that.productSlug,_that.deliveryType,_that.unitPrice,_that.lineTotal);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  String name,  int quantity,  List<OrderUnit> units,  String? productSlug,  String? deliveryType,  Money? unitPrice,  Money? lineTotal)?  $default,) {final _that = this;
switch (_that) {
case _OrderLine() when $default != null:
return $default(_that.id,_that.name,_that.quantity,_that.units,_that.productSlug,_that.deliveryType,_that.unitPrice,_that.lineTotal);case _:
  return null;

}
}

}

/// @nodoc


class _OrderLine implements OrderLine {
  const _OrderLine({required this.id, required this.name, required this.quantity, required  List<OrderUnit> units, this.productSlug, this.deliveryType, this.unitPrice, this.lineTotal}): _units = units;
  

@override final  int id;
@override final  String name;
@override final  int quantity;
 final  List<OrderUnit> _units;
@override List<OrderUnit> get units {
  if (_units is EqualUnmodifiableListView) return _units;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_units);
}

@override final  String? productSlug;
@override final  String? deliveryType;
@override final  Money? unitPrice;
@override final  Money? lineTotal;

/// Create a copy of OrderLine
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderLineCopyWith<_OrderLine> get copyWith => __$OrderLineCopyWithImpl<_OrderLine>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderLine&&(identical(other.id, id) || other.id == id)&&(identical(other.name, name) || other.name == name)&&(identical(other.quantity, quantity) || other.quantity == quantity)&&const DeepCollectionEquality().equals(other.units, _units)&&(identical(other.productSlug, productSlug) || other.productSlug == productSlug)&&(identical(other.deliveryType, deliveryType) || other.deliveryType == deliveryType)&&(identical(other.unitPrice, unitPrice) || other.unitPrice == unitPrice)&&(identical(other.lineTotal, lineTotal) || other.lineTotal == lineTotal));
}


@override
int get hashCode {
    return Object.hash(runtimeType,id,name,quantity,const DeepCollectionEquality().hash(_units),productSlug,deliveryType,unitPrice,lineTotal);
}

@override
String toString() {
    return 'OrderLine(id: $id, name: $name, quantity: $quantity, units: $units, productSlug: $productSlug, deliveryType: $deliveryType, unitPrice: $unitPrice, lineTotal: $lineTotal)';
}


}

/// @nodoc
abstract mixin class _$OrderLineCopyWith<$Res> implements $OrderLineCopyWith<$Res> {
  factory _$OrderLineCopyWith(_OrderLine value, $Res Function(_OrderLine) _then) = __$OrderLineCopyWithImpl;
@override @useResult
$Res call({
 int id, String name, int quantity, List<OrderUnit> units, String? productSlug, String? deliveryType, Money? unitPrice, Money? lineTotal
});




}
/// @nodoc
class __$OrderLineCopyWithImpl<$Res>
    implements _$OrderLineCopyWith<$Res> {
  __$OrderLineCopyWithImpl(this._self, this._then);

  final _OrderLine _self;
  final $Res Function(_OrderLine) _then;

/// Create a copy of OrderLine
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? name = null,Object? quantity = null,Object? units = null,Object? productSlug = freezed,Object? deliveryType = freezed,Object? unitPrice = freezed,Object? lineTotal = freezed,}) {
  return _then(_OrderLine(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,quantity: null == quantity ? _self.quantity : quantity // ignore: cast_nullable_to_non_nullable
as int,units: null == units ? _self._units : units // ignore: cast_nullable_to_non_nullable
as List<OrderUnit>,productSlug: freezed == productSlug ? _self.productSlug : productSlug // ignore: cast_nullable_to_non_nullable
as String?,deliveryType: freezed == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String?,unitPrice: freezed == unitPrice ? _self.unitPrice : unitPrice // ignore: cast_nullable_to_non_nullable
as Money?,lineTotal: freezed == lineTotal ? _self.lineTotal : lineTotal // ignore: cast_nullable_to_non_nullable
as Money?,
  ));
}


}

/// @nodoc
mixin _$OrderUnit {

 String get publicId; String get status; bool get revealable; String? get deliveryType; String? get maskedCode; DateTime? get deliveredAt; DateTime? get firstRevealedAt; DateTime? get refundedAt;
/// Create a copy of OrderUnit
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderUnitCopyWith<OrderUnit> get copyWith => _$OrderUnitCopyWithImpl<OrderUnit>(this as OrderUnit, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as OrderUnit;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderUnit&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.revealable, _this.revealable) || other.revealable == _this.revealable)&&(identical(other.deliveryType, _this.deliveryType) || other.deliveryType == _this.deliveryType)&&(identical(other.maskedCode, _this.maskedCode) || other.maskedCode == _this.maskedCode)&&(identical(other.deliveredAt, _this.deliveredAt) || other.deliveredAt == _this.deliveredAt)&&(identical(other.firstRevealedAt, _this.firstRevealedAt) || other.firstRevealedAt == _this.firstRevealedAt)&&(identical(other.refundedAt, _this.refundedAt) || other.refundedAt == _this.refundedAt));
}


@override
int get hashCode {
  final _this = this as OrderUnit;
  return Object.hash(runtimeType,_this.publicId,_this.status,_this.revealable,_this.deliveryType,_this.maskedCode,_this.deliveredAt,_this.firstRevealedAt,_this.refundedAt);
}

@override
String toString() {
  final _this = this as OrderUnit;
  return 'OrderUnit(publicId: ${_this.publicId}, status: ${_this.status}, revealable: ${_this.revealable}, deliveryType: ${_this.deliveryType}, maskedCode: ${_this.maskedCode}, deliveredAt: ${_this.deliveredAt}, firstRevealedAt: ${_this.firstRevealedAt}, refundedAt: ${_this.refundedAt})';
}


}

/// @nodoc
abstract mixin class $OrderUnitCopyWith<$Res>  {
  factory $OrderUnitCopyWith(OrderUnit value, $Res Function(OrderUnit) _then) = _$OrderUnitCopyWithImpl;
@useResult
$Res call({
 String publicId, String status, bool revealable, String? deliveryType, String? maskedCode, DateTime? deliveredAt, DateTime? firstRevealedAt, DateTime? refundedAt
});




}
/// @nodoc
class _$OrderUnitCopyWithImpl<$Res>
    implements $OrderUnitCopyWith<$Res> {
  _$OrderUnitCopyWithImpl(this._self, this._then);

  final OrderUnit _self;
  final $Res Function(OrderUnit) _then;

/// Create a copy of OrderUnit
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? status = null,Object? revealable = null,Object? deliveryType = freezed,Object? maskedCode = freezed,Object? deliveredAt = freezed,Object? firstRevealedAt = freezed,Object? refundedAt = freezed,}) {
  return _then(OrderUnit(
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


/// Adds pattern-matching-related methods to [OrderUnit].
extension OrderUnitPatterns on OrderUnit {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderUnit value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderUnit() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderUnit value)  $default,){
final _that = this;
switch (_that) {
case _OrderUnit():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderUnit value)?  $default,){
final _that = this;
switch (_that) {
case _OrderUnit() when $default != null:
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
case _OrderUnit() when $default != null:
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
case _OrderUnit():
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
case _OrderUnit() when $default != null:
return $default(_that.publicId,_that.status,_that.revealable,_that.deliveryType,_that.maskedCode,_that.deliveredAt,_that.firstRevealedAt,_that.refundedAt);case _:
  return null;

}
}

}

/// @nodoc


class _OrderUnit implements OrderUnit {
  const _OrderUnit({required this.publicId, required this.status, required this.revealable, this.deliveryType, this.maskedCode, this.deliveredAt, this.firstRevealedAt, this.refundedAt});
  

@override final  String publicId;
@override final  String status;
@override final  bool revealable;
@override final  String? deliveryType;
@override final  String? maskedCode;
@override final  DateTime? deliveredAt;
@override final  DateTime? firstRevealedAt;
@override final  DateTime? refundedAt;

/// Create a copy of OrderUnit
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderUnitCopyWith<_OrderUnit> get copyWith => __$OrderUnitCopyWithImpl<_OrderUnit>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderUnit&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.status, status) || other.status == status)&&(identical(other.revealable, revealable) || other.revealable == revealable)&&(identical(other.deliveryType, deliveryType) || other.deliveryType == deliveryType)&&(identical(other.maskedCode, maskedCode) || other.maskedCode == maskedCode)&&(identical(other.deliveredAt, deliveredAt) || other.deliveredAt == deliveredAt)&&(identical(other.firstRevealedAt, firstRevealedAt) || other.firstRevealedAt == firstRevealedAt)&&(identical(other.refundedAt, refundedAt) || other.refundedAt == refundedAt));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,status,revealable,deliveryType,maskedCode,deliveredAt,firstRevealedAt,refundedAt);
}

@override
String toString() {
    return 'OrderUnit(publicId: $publicId, status: $status, revealable: $revealable, deliveryType: $deliveryType, maskedCode: $maskedCode, deliveredAt: $deliveredAt, firstRevealedAt: $firstRevealedAt, refundedAt: $refundedAt)';
}


}

/// @nodoc
abstract mixin class _$OrderUnitCopyWith<$Res> implements $OrderUnitCopyWith<$Res> {
  factory _$OrderUnitCopyWith(_OrderUnit value, $Res Function(_OrderUnit) _then) = __$OrderUnitCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String status, bool revealable, String? deliveryType, String? maskedCode, DateTime? deliveredAt, DateTime? firstRevealedAt, DateTime? refundedAt
});




}
/// @nodoc
class __$OrderUnitCopyWithImpl<$Res>
    implements _$OrderUnitCopyWith<$Res> {
  __$OrderUnitCopyWithImpl(this._self, this._then);

  final _OrderUnit _self;
  final $Res Function(_OrderUnit) _then;

/// Create a copy of OrderUnit
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? status = null,Object? revealable = null,Object? deliveryType = freezed,Object? maskedCode = freezed,Object? deliveredAt = freezed,Object? firstRevealedAt = freezed,Object? refundedAt = freezed,}) {
  return _then(_OrderUnit(
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
mixin _$OrderPayment {

 String get publicId; int get attempt; String get status; Money get amount; DateTime get createdAt; String? get provider; String? get method; String? get failureCode;
/// Create a copy of OrderPayment
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderPaymentCopyWith<OrderPayment> get copyWith => _$OrderPaymentCopyWithImpl<OrderPayment>(this as OrderPayment, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as OrderPayment;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderPayment&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.attempt, _this.attempt) || other.attempt == _this.attempt)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.amount, _this.amount) || other.amount == _this.amount)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.provider, _this.provider) || other.provider == _this.provider)&&(identical(other.method, _this.method) || other.method == _this.method)&&(identical(other.failureCode, _this.failureCode) || other.failureCode == _this.failureCode));
}


@override
int get hashCode {
  final _this = this as OrderPayment;
  return Object.hash(runtimeType,_this.publicId,_this.attempt,_this.status,_this.amount,_this.createdAt,_this.provider,_this.method,_this.failureCode);
}

@override
String toString() {
  final _this = this as OrderPayment;
  return 'OrderPayment(publicId: ${_this.publicId}, attempt: ${_this.attempt}, status: ${_this.status}, amount: ${_this.amount}, createdAt: ${_this.createdAt}, provider: ${_this.provider}, method: ${_this.method}, failureCode: ${_this.failureCode})';
}


}

/// @nodoc
abstract mixin class $OrderPaymentCopyWith<$Res>  {
  factory $OrderPaymentCopyWith(OrderPayment value, $Res Function(OrderPayment) _then) = _$OrderPaymentCopyWithImpl;
@useResult
$Res call({
 String publicId, int attempt, String status, Money amount, DateTime createdAt, String? provider, String? method, String? failureCode
});




}
/// @nodoc
class _$OrderPaymentCopyWithImpl<$Res>
    implements $OrderPaymentCopyWith<$Res> {
  _$OrderPaymentCopyWithImpl(this._self, this._then);

  final OrderPayment _self;
  final $Res Function(OrderPayment) _then;

/// Create a copy of OrderPayment
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? attempt = null,Object? status = null,Object? amount = null,Object? createdAt = null,Object? provider = freezed,Object? method = freezed,Object? failureCode = freezed,}) {
  return _then(OrderPayment(
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


/// Adds pattern-matching-related methods to [OrderPayment].
extension OrderPaymentPatterns on OrderPayment {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderPayment value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderPayment() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderPayment value)  $default,){
final _that = this;
switch (_that) {
case _OrderPayment():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderPayment value)?  $default,){
final _that = this;
switch (_that) {
case _OrderPayment() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  int attempt,  String status,  Money amount,  DateTime createdAt,  String? provider,  String? method,  String? failureCode)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderPayment() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  int attempt,  String status,  Money amount,  DateTime createdAt,  String? provider,  String? method,  String? failureCode)  $default,) {final _that = this;
switch (_that) {
case _OrderPayment():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  int attempt,  String status,  Money amount,  DateTime createdAt,  String? provider,  String? method,  String? failureCode)?  $default,) {final _that = this;
switch (_that) {
case _OrderPayment() when $default != null:
return $default(_that.publicId,_that.attempt,_that.status,_that.amount,_that.createdAt,_that.provider,_that.method,_that.failureCode);case _:
  return null;

}
}

}

/// @nodoc


class _OrderPayment implements OrderPayment {
  const _OrderPayment({required this.publicId, required this.attempt, required this.status, required this.amount, required this.createdAt, this.provider, this.method, this.failureCode});
  

@override final  String publicId;
@override final  int attempt;
@override final  String status;
@override final  Money amount;
@override final  DateTime createdAt;
@override final  String? provider;
@override final  String? method;
@override final  String? failureCode;

/// Create a copy of OrderPayment
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderPaymentCopyWith<_OrderPayment> get copyWith => __$OrderPaymentCopyWithImpl<_OrderPayment>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderPayment&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.attempt, attempt) || other.attempt == attempt)&&(identical(other.status, status) || other.status == status)&&(identical(other.amount, amount) || other.amount == amount)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.provider, provider) || other.provider == provider)&&(identical(other.method, method) || other.method == method)&&(identical(other.failureCode, failureCode) || other.failureCode == failureCode));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,attempt,status,amount,createdAt,provider,method,failureCode);
}

@override
String toString() {
    return 'OrderPayment(publicId: $publicId, attempt: $attempt, status: $status, amount: $amount, createdAt: $createdAt, provider: $provider, method: $method, failureCode: $failureCode)';
}


}

/// @nodoc
abstract mixin class _$OrderPaymentCopyWith<$Res> implements $OrderPaymentCopyWith<$Res> {
  factory _$OrderPaymentCopyWith(_OrderPayment value, $Res Function(_OrderPayment) _then) = __$OrderPaymentCopyWithImpl;
@override @useResult
$Res call({
 String publicId, int attempt, String status, Money amount, DateTime createdAt, String? provider, String? method, String? failureCode
});




}
/// @nodoc
class __$OrderPaymentCopyWithImpl<$Res>
    implements _$OrderPaymentCopyWith<$Res> {
  __$OrderPaymentCopyWithImpl(this._self, this._then);

  final _OrderPayment _self;
  final $Res Function(_OrderPayment) _then;

/// Create a copy of OrderPayment
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? attempt = null,Object? status = null,Object? amount = null,Object? createdAt = null,Object? provider = freezed,Object? method = freezed,Object? failureCode = freezed,}) {
  return _then(_OrderPayment(
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
mixin _$OrderRefund {

 String get publicId; String get status; String get destination; Money get amount; DateTime? get createdAt; DateTime? get completedAt;
/// Create a copy of OrderRefund
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderRefundCopyWith<OrderRefund> get copyWith => _$OrderRefundCopyWithImpl<OrderRefund>(this as OrderRefund, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as OrderRefund;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderRefund&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.destination, _this.destination) || other.destination == _this.destination)&&(identical(other.amount, _this.amount) || other.amount == _this.amount)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.completedAt, _this.completedAt) || other.completedAt == _this.completedAt));
}


@override
int get hashCode {
  final _this = this as OrderRefund;
  return Object.hash(runtimeType,_this.publicId,_this.status,_this.destination,_this.amount,_this.createdAt,_this.completedAt);
}

@override
String toString() {
  final _this = this as OrderRefund;
  return 'OrderRefund(publicId: ${_this.publicId}, status: ${_this.status}, destination: ${_this.destination}, amount: ${_this.amount}, createdAt: ${_this.createdAt}, completedAt: ${_this.completedAt})';
}


}

/// @nodoc
abstract mixin class $OrderRefundCopyWith<$Res>  {
  factory $OrderRefundCopyWith(OrderRefund value, $Res Function(OrderRefund) _then) = _$OrderRefundCopyWithImpl;
@useResult
$Res call({
 String publicId, String status, String destination, Money amount, DateTime? createdAt, DateTime? completedAt
});




}
/// @nodoc
class _$OrderRefundCopyWithImpl<$Res>
    implements $OrderRefundCopyWith<$Res> {
  _$OrderRefundCopyWithImpl(this._self, this._then);

  final OrderRefund _self;
  final $Res Function(OrderRefund) _then;

/// Create a copy of OrderRefund
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? status = null,Object? destination = null,Object? amount = null,Object? createdAt = freezed,Object? completedAt = freezed,}) {
  return _then(OrderRefund(
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


/// Adds pattern-matching-related methods to [OrderRefund].
extension OrderRefundPatterns on OrderRefund {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderRefund value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderRefund() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderRefund value)  $default,){
final _that = this;
switch (_that) {
case _OrderRefund():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderRefund value)?  $default,){
final _that = this;
switch (_that) {
case _OrderRefund() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String status,  String destination,  Money amount,  DateTime? createdAt,  DateTime? completedAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderRefund() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String status,  String destination,  Money amount,  DateTime? createdAt,  DateTime? completedAt)  $default,) {final _that = this;
switch (_that) {
case _OrderRefund():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String status,  String destination,  Money amount,  DateTime? createdAt,  DateTime? completedAt)?  $default,) {final _that = this;
switch (_that) {
case _OrderRefund() when $default != null:
return $default(_that.publicId,_that.status,_that.destination,_that.amount,_that.createdAt,_that.completedAt);case _:
  return null;

}
}

}

/// @nodoc


class _OrderRefund implements OrderRefund {
  const _OrderRefund({required this.publicId, required this.status, required this.destination, required this.amount, this.createdAt, this.completedAt});
  

@override final  String publicId;
@override final  String status;
@override final  String destination;
@override final  Money amount;
@override final  DateTime? createdAt;
@override final  DateTime? completedAt;

/// Create a copy of OrderRefund
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderRefundCopyWith<_OrderRefund> get copyWith => __$OrderRefundCopyWithImpl<_OrderRefund>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderRefund&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.status, status) || other.status == status)&&(identical(other.destination, destination) || other.destination == destination)&&(identical(other.amount, amount) || other.amount == amount)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.completedAt, completedAt) || other.completedAt == completedAt));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,status,destination,amount,createdAt,completedAt);
}

@override
String toString() {
    return 'OrderRefund(publicId: $publicId, status: $status, destination: $destination, amount: $amount, createdAt: $createdAt, completedAt: $completedAt)';
}


}

/// @nodoc
abstract mixin class _$OrderRefundCopyWith<$Res> implements $OrderRefundCopyWith<$Res> {
  factory _$OrderRefundCopyWith(_OrderRefund value, $Res Function(_OrderRefund) _then) = __$OrderRefundCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String status, String destination, Money amount, DateTime? createdAt, DateTime? completedAt
});




}
/// @nodoc
class __$OrderRefundCopyWithImpl<$Res>
    implements _$OrderRefundCopyWith<$Res> {
  __$OrderRefundCopyWithImpl(this._self, this._then);

  final _OrderRefund _self;
  final $Res Function(_OrderRefund) _then;

/// Create a copy of OrderRefund
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? status = null,Object? destination = null,Object? amount = null,Object? createdAt = freezed,Object? completedAt = freezed,}) {
  return _then(_OrderRefund(
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
mixin _$OrderTimelineEntry {

 String get status; DateTime? get at;
/// Create a copy of OrderTimelineEntry
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderTimelineEntryCopyWith<OrderTimelineEntry> get copyWith => _$OrderTimelineEntryCopyWithImpl<OrderTimelineEntry>(this as OrderTimelineEntry, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as OrderTimelineEntry;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderTimelineEntry&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.at, _this.at) || other.at == _this.at));
}


@override
int get hashCode {
  final _this = this as OrderTimelineEntry;
  return Object.hash(runtimeType,_this.status,_this.at);
}

@override
String toString() {
  final _this = this as OrderTimelineEntry;
  return 'OrderTimelineEntry(status: ${_this.status}, at: ${_this.at})';
}


}

/// @nodoc
abstract mixin class $OrderTimelineEntryCopyWith<$Res>  {
  factory $OrderTimelineEntryCopyWith(OrderTimelineEntry value, $Res Function(OrderTimelineEntry) _then) = _$OrderTimelineEntryCopyWithImpl;
@useResult
$Res call({
 String status, DateTime? at
});




}
/// @nodoc
class _$OrderTimelineEntryCopyWithImpl<$Res>
    implements $OrderTimelineEntryCopyWith<$Res> {
  _$OrderTimelineEntryCopyWithImpl(this._self, this._then);

  final OrderTimelineEntry _self;
  final $Res Function(OrderTimelineEntry) _then;

/// Create a copy of OrderTimelineEntry
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? status = null,Object? at = freezed,}) {
  return _then(OrderTimelineEntry(
status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,at: freezed == at ? _self.at : at // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderTimelineEntry].
extension OrderTimelineEntryPatterns on OrderTimelineEntry {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderTimelineEntry value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderTimelineEntry() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderTimelineEntry value)  $default,){
final _that = this;
switch (_that) {
case _OrderTimelineEntry():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderTimelineEntry value)?  $default,){
final _that = this;
switch (_that) {
case _OrderTimelineEntry() when $default != null:
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
case _OrderTimelineEntry() when $default != null:
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
case _OrderTimelineEntry():
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
case _OrderTimelineEntry() when $default != null:
return $default(_that.status,_that.at);case _:
  return null;

}
}

}

/// @nodoc


class _OrderTimelineEntry implements OrderTimelineEntry {
  const _OrderTimelineEntry({required this.status, this.at});
  

@override final  String status;
@override final  DateTime? at;

/// Create a copy of OrderTimelineEntry
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderTimelineEntryCopyWith<_OrderTimelineEntry> get copyWith => __$OrderTimelineEntryCopyWithImpl<_OrderTimelineEntry>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderTimelineEntry&&(identical(other.status, status) || other.status == status)&&(identical(other.at, at) || other.at == at));
}


@override
int get hashCode {
    return Object.hash(runtimeType,status,at);
}

@override
String toString() {
    return 'OrderTimelineEntry(status: $status, at: $at)';
}


}

/// @nodoc
abstract mixin class _$OrderTimelineEntryCopyWith<$Res> implements $OrderTimelineEntryCopyWith<$Res> {
  factory _$OrderTimelineEntryCopyWith(_OrderTimelineEntry value, $Res Function(_OrderTimelineEntry) _then) = __$OrderTimelineEntryCopyWithImpl;
@override @useResult
$Res call({
 String status, DateTime? at
});




}
/// @nodoc
class __$OrderTimelineEntryCopyWithImpl<$Res>
    implements _$OrderTimelineEntryCopyWith<$Res> {
  __$OrderTimelineEntryCopyWithImpl(this._self, this._then);

  final _OrderTimelineEntry _self;
  final $Res Function(_OrderTimelineEntry) _then;

/// Create a copy of OrderTimelineEntry
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? status = null,Object? at = freezed,}) {
  return _then(_OrderTimelineEntry(
status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,at: freezed == at ? _self.at : at // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}


}

/// @nodoc
mixin _$GuestOrderDetail {

 OrderDetail get order; bool get linkVerified; bool get otpVerified;
/// Create a copy of GuestOrderDetail
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$GuestOrderDetailCopyWith<GuestOrderDetail> get copyWith => _$GuestOrderDetailCopyWithImpl<GuestOrderDetail>(this as GuestOrderDetail, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as GuestOrderDetail;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is GuestOrderDetail&&(identical(other.order, _this.order) || other.order == _this.order)&&(identical(other.linkVerified, _this.linkVerified) || other.linkVerified == _this.linkVerified)&&(identical(other.otpVerified, _this.otpVerified) || other.otpVerified == _this.otpVerified));
}


@override
int get hashCode {
  final _this = this as GuestOrderDetail;
  return Object.hash(runtimeType,_this.order,_this.linkVerified,_this.otpVerified);
}

@override
String toString() {
  final _this = this as GuestOrderDetail;
  return 'GuestOrderDetail(order: ${_this.order}, linkVerified: ${_this.linkVerified}, otpVerified: ${_this.otpVerified})';
}


}

/// @nodoc
abstract mixin class $GuestOrderDetailCopyWith<$Res>  {
  factory $GuestOrderDetailCopyWith(GuestOrderDetail value, $Res Function(GuestOrderDetail) _then) = _$GuestOrderDetailCopyWithImpl;
@useResult
$Res call({
 OrderDetail order, bool linkVerified, bool otpVerified
});


$OrderDetailCopyWith<$Res> get order;

}
/// @nodoc
class _$GuestOrderDetailCopyWithImpl<$Res>
    implements $GuestOrderDetailCopyWith<$Res> {
  _$GuestOrderDetailCopyWithImpl(this._self, this._then);

  final GuestOrderDetail _self;
  final $Res Function(GuestOrderDetail) _then;

/// Create a copy of GuestOrderDetail
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? order = null,Object? linkVerified = null,Object? otpVerified = null,}) {
  return _then(GuestOrderDetail(
order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderDetail,linkVerified: null == linkVerified ? _self.linkVerified : linkVerified // ignore: cast_nullable_to_non_nullable
as bool,otpVerified: null == otpVerified ? _self.otpVerified : otpVerified // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}
/// Create a copy of GuestOrderDetail
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderDetailCopyWith<$Res> get order {
  
  return $OrderDetailCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}
}


/// Adds pattern-matching-related methods to [GuestOrderDetail].
extension GuestOrderDetailPatterns on GuestOrderDetail {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _GuestOrderDetail value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _GuestOrderDetail() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _GuestOrderDetail value)  $default,){
final _that = this;
switch (_that) {
case _GuestOrderDetail():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _GuestOrderDetail value)?  $default,){
final _that = this;
switch (_that) {
case _GuestOrderDetail() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( OrderDetail order,  bool linkVerified,  bool otpVerified)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _GuestOrderDetail() when $default != null:
return $default(_that.order,_that.linkVerified,_that.otpVerified);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( OrderDetail order,  bool linkVerified,  bool otpVerified)  $default,) {final _that = this;
switch (_that) {
case _GuestOrderDetail():
return $default(_that.order,_that.linkVerified,_that.otpVerified);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( OrderDetail order,  bool linkVerified,  bool otpVerified)?  $default,) {final _that = this;
switch (_that) {
case _GuestOrderDetail() when $default != null:
return $default(_that.order,_that.linkVerified,_that.otpVerified);case _:
  return null;

}
}

}

/// @nodoc


class _GuestOrderDetail extends GuestOrderDetail {
  const _GuestOrderDetail({required this.order, required this.linkVerified, required this.otpVerified}): super._();
  

@override final  OrderDetail order;
@override final  bool linkVerified;
@override final  bool otpVerified;

/// Create a copy of GuestOrderDetail
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$GuestOrderDetailCopyWith<_GuestOrderDetail> get copyWith => __$GuestOrderDetailCopyWithImpl<_GuestOrderDetail>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _GuestOrderDetail&&(identical(other.order, order) || other.order == order)&&(identical(other.linkVerified, linkVerified) || other.linkVerified == linkVerified)&&(identical(other.otpVerified, otpVerified) || other.otpVerified == otpVerified));
}


@override
int get hashCode {
    return Object.hash(runtimeType,order,linkVerified,otpVerified);
}

@override
String toString() {
    return 'GuestOrderDetail(order: $order, linkVerified: $linkVerified, otpVerified: $otpVerified)';
}


}

/// @nodoc
abstract mixin class _$GuestOrderDetailCopyWith<$Res> implements $GuestOrderDetailCopyWith<$Res> {
  factory _$GuestOrderDetailCopyWith(_GuestOrderDetail value, $Res Function(_GuestOrderDetail) _then) = __$GuestOrderDetailCopyWithImpl;
@override @useResult
$Res call({
 OrderDetail order, bool linkVerified, bool otpVerified
});


@override $OrderDetailCopyWith<$Res> get order;

}
/// @nodoc
class __$GuestOrderDetailCopyWithImpl<$Res>
    implements _$GuestOrderDetailCopyWith<$Res> {
  __$GuestOrderDetailCopyWithImpl(this._self, this._then);

  final _GuestOrderDetail _self;
  final $Res Function(_GuestOrderDetail) _then;

/// Create a copy of GuestOrderDetail
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? order = null,Object? linkVerified = null,Object? otpVerified = null,}) {
  return _then(_GuestOrderDetail(
order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderDetail,linkVerified: null == linkVerified ? _self.linkVerified : linkVerified // ignore: cast_nullable_to_non_nullable
as bool,otpVerified: null == otpVerified ? _self.otpVerified : otpVerified // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

/// Create a copy of GuestOrderDetail
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderDetailCopyWith<$Res> get order {
  
  return $OrderDetailCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}
}

/// @nodoc
mixin _$OrdersQuery {

 String? get status; String? get from; String? get to; int get page; int get perPage;
/// Create a copy of OrdersQuery
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrdersQueryCopyWith<OrdersQuery> get copyWith => _$OrdersQueryCopyWithImpl<OrdersQuery>(this as OrdersQuery, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as OrdersQuery;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrdersQuery&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.from, _this.from) || other.from == _this.from)&&(identical(other.to, _this.to) || other.to == _this.to)&&(identical(other.page, _this.page) || other.page == _this.page)&&(identical(other.perPage, _this.perPage) || other.perPage == _this.perPage));
}


@override
int get hashCode {
  final _this = this as OrdersQuery;
  return Object.hash(runtimeType,_this.status,_this.from,_this.to,_this.page,_this.perPage);
}

@override
String toString() {
  final _this = this as OrdersQuery;
  return 'OrdersQuery(status: ${_this.status}, from: ${_this.from}, to: ${_this.to}, page: ${_this.page}, perPage: ${_this.perPage})';
}


}

/// @nodoc
abstract mixin class $OrdersQueryCopyWith<$Res>  {
  factory $OrdersQueryCopyWith(OrdersQuery value, $Res Function(OrdersQuery) _then) = _$OrdersQueryCopyWithImpl;
@useResult
$Res call({
 String? status, String? from, String? to, int page, int perPage
});




}
/// @nodoc
class _$OrdersQueryCopyWithImpl<$Res>
    implements $OrdersQueryCopyWith<$Res> {
  _$OrdersQueryCopyWithImpl(this._self, this._then);

  final OrdersQuery _self;
  final $Res Function(OrdersQuery) _then;

/// Create a copy of OrdersQuery
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? status = freezed,Object? from = freezed,Object? to = freezed,Object? page = null,Object? perPage = null,}) {
  return _then(OrdersQuery(
status: freezed == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String?,from: freezed == from ? _self.from : from // ignore: cast_nullable_to_non_nullable
as String?,to: freezed == to ? _self.to : to // ignore: cast_nullable_to_non_nullable
as String?,page: null == page ? _self.page : page // ignore: cast_nullable_to_non_nullable
as int,perPage: null == perPage ? _self.perPage : perPage // ignore: cast_nullable_to_non_nullable
as int,
  ));
}

}


/// Adds pattern-matching-related methods to [OrdersQuery].
extension OrdersQueryPatterns on OrdersQuery {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrdersQuery value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrdersQuery() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrdersQuery value)  $default,){
final _that = this;
switch (_that) {
case _OrdersQuery():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrdersQuery value)?  $default,){
final _that = this;
switch (_that) {
case _OrdersQuery() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String? status,  String? from,  String? to,  int page,  int perPage)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrdersQuery() when $default != null:
return $default(_that.status,_that.from,_that.to,_that.page,_that.perPage);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String? status,  String? from,  String? to,  int page,  int perPage)  $default,) {final _that = this;
switch (_that) {
case _OrdersQuery():
return $default(_that.status,_that.from,_that.to,_that.page,_that.perPage);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String? status,  String? from,  String? to,  int page,  int perPage)?  $default,) {final _that = this;
switch (_that) {
case _OrdersQuery() when $default != null:
return $default(_that.status,_that.from,_that.to,_that.page,_that.perPage);case _:
  return null;

}
}

}

/// @nodoc


class _OrdersQuery implements OrdersQuery {
  const _OrdersQuery({this.status, this.from, this.to, this.page = 1, this.perPage = 20});
  

@override final  String? status;
@override final  String? from;
@override final  String? to;
@override@JsonKey() final  int page;
@override@JsonKey() final  int perPage;

/// Create a copy of OrdersQuery
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrdersQueryCopyWith<_OrdersQuery> get copyWith => __$OrdersQueryCopyWithImpl<_OrdersQuery>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrdersQuery&&(identical(other.status, status) || other.status == status)&&(identical(other.from, from) || other.from == from)&&(identical(other.to, to) || other.to == to)&&(identical(other.page, page) || other.page == page)&&(identical(other.perPage, perPage) || other.perPage == perPage));
}


@override
int get hashCode {
    return Object.hash(runtimeType,status,from,to,page,perPage);
}

@override
String toString() {
    return 'OrdersQuery(status: $status, from: $from, to: $to, page: $page, perPage: $perPage)';
}


}

/// @nodoc
abstract mixin class _$OrdersQueryCopyWith<$Res> implements $OrdersQueryCopyWith<$Res> {
  factory _$OrdersQueryCopyWith(_OrdersQuery value, $Res Function(_OrdersQuery) _then) = __$OrdersQueryCopyWithImpl;
@override @useResult
$Res call({
 String? status, String? from, String? to, int page, int perPage
});




}
/// @nodoc
class __$OrdersQueryCopyWithImpl<$Res>
    implements _$OrdersQueryCopyWith<$Res> {
  __$OrdersQueryCopyWithImpl(this._self, this._then);

  final _OrdersQuery _self;
  final $Res Function(_OrdersQuery) _then;

/// Create a copy of OrdersQuery
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? status = freezed,Object? from = freezed,Object? to = freezed,Object? page = null,Object? perPage = null,}) {
  return _then(_OrdersQuery(
status: freezed == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String?,from: freezed == from ? _self.from : from // ignore: cast_nullable_to_non_nullable
as String?,to: freezed == to ? _self.to : to // ignore: cast_nullable_to_non_nullable
as String?,page: null == page ? _self.page : page // ignore: cast_nullable_to_non_nullable
as int,perPage: null == perPage ? _self.perPage : perPage // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

// dart format on
