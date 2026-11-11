// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'wallet.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$Wallet {

 String get currency; Money get balance;/// `active` or `frozen`.
 String get status; bool get topUpAvailable;
/// Create a copy of Wallet
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$WalletCopyWith<Wallet> get copyWith => _$WalletCopyWithImpl<Wallet>(this as Wallet, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as Wallet;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is Wallet&&(identical(other.currency, _this.currency) || other.currency == _this.currency)&&(identical(other.balance, _this.balance) || other.balance == _this.balance)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.topUpAvailable, _this.topUpAvailable) || other.topUpAvailable == _this.topUpAvailable));
}


@override
int get hashCode {
  final _this = this as Wallet;
  return Object.hash(runtimeType,_this.currency,_this.balance,_this.status,_this.topUpAvailable);
}

@override
String toString() {
  final _this = this as Wallet;
  return 'Wallet(currency: ${_this.currency}, balance: ${_this.balance}, status: ${_this.status}, topUpAvailable: ${_this.topUpAvailable})';
}


}

/// @nodoc
abstract mixin class $WalletCopyWith<$Res>  {
  factory $WalletCopyWith(Wallet value, $Res Function(Wallet) _then) = _$WalletCopyWithImpl;
@useResult
$Res call({
 String currency, Money balance, String status, bool topUpAvailable
});




}
/// @nodoc
class _$WalletCopyWithImpl<$Res>
    implements $WalletCopyWith<$Res> {
  _$WalletCopyWithImpl(this._self, this._then);

  final Wallet _self;
  final $Res Function(Wallet) _then;

/// Create a copy of Wallet
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? currency = null,Object? balance = null,Object? status = null,Object? topUpAvailable = null,}) {
  return _then(Wallet(
currency: null == currency ? _self.currency : currency // ignore: cast_nullable_to_non_nullable
as String,balance: null == balance ? _self.balance : balance // ignore: cast_nullable_to_non_nullable
as Money,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,topUpAvailable: null == topUpAvailable ? _self.topUpAvailable : topUpAvailable // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [Wallet].
extension WalletPatterns on Wallet {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _Wallet value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _Wallet() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _Wallet value)  $default,){
final _that = this;
switch (_that) {
case _Wallet():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _Wallet value)?  $default,){
final _that = this;
switch (_that) {
case _Wallet() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String currency,  Money balance,  String status,  bool topUpAvailable)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _Wallet() when $default != null:
return $default(_that.currency,_that.balance,_that.status,_that.topUpAvailable);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String currency,  Money balance,  String status,  bool topUpAvailable)  $default,) {final _that = this;
switch (_that) {
case _Wallet():
return $default(_that.currency,_that.balance,_that.status,_that.topUpAvailable);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String currency,  Money balance,  String status,  bool topUpAvailable)?  $default,) {final _that = this;
switch (_that) {
case _Wallet() when $default != null:
return $default(_that.currency,_that.balance,_that.status,_that.topUpAvailable);case _:
  return null;

}
}

}

/// @nodoc


class _Wallet implements Wallet {
  const _Wallet({required this.currency, required this.balance, required this.status, required this.topUpAvailable});
  

@override final  String currency;
@override final  Money balance;
/// `active` or `frozen`.
@override final  String status;
@override final  bool topUpAvailable;

/// Create a copy of Wallet
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$WalletCopyWith<_Wallet> get copyWith => __$WalletCopyWithImpl<_Wallet>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _Wallet&&(identical(other.currency, currency) || other.currency == currency)&&(identical(other.balance, balance) || other.balance == balance)&&(identical(other.status, status) || other.status == status)&&(identical(other.topUpAvailable, topUpAvailable) || other.topUpAvailable == topUpAvailable));
}


@override
int get hashCode {
    return Object.hash(runtimeType,currency,balance,status,topUpAvailable);
}

@override
String toString() {
    return 'Wallet(currency: $currency, balance: $balance, status: $status, topUpAvailable: $topUpAvailable)';
}


}

/// @nodoc
abstract mixin class _$WalletCopyWith<$Res> implements $WalletCopyWith<$Res> {
  factory _$WalletCopyWith(_Wallet value, $Res Function(_Wallet) _then) = __$WalletCopyWithImpl;
@override @useResult
$Res call({
 String currency, Money balance, String status, bool topUpAvailable
});




}
/// @nodoc
class __$WalletCopyWithImpl<$Res>
    implements _$WalletCopyWith<$Res> {
  __$WalletCopyWithImpl(this._self, this._then);

  final _Wallet _self;
  final $Res Function(_Wallet) _then;

/// Create a copy of Wallet
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? currency = null,Object? balance = null,Object? status = null,Object? topUpAvailable = null,}) {
  return _then(_Wallet(
currency: null == currency ? _self.currency : currency // ignore: cast_nullable_to_non_nullable
as String,balance: null == balance ? _self.balance : balance // ignore: cast_nullable_to_non_nullable
as Money,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,topUpAvailable: null == topUpAvailable ? _self.topUpAvailable : topUpAvailable // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc
mixin _$WalletTransaction {

 int get id;/// `credit_refund`, `credit_admin`, `debit_purchase`, `debit_admin` or `reversal`.
 String get type; String get amount; String get balanceAfter; DateTime get createdAt;/// The server's note (English only): the staff reason of an adjustment, or a system note.
 String? get note;/// The customer's own order behind a purchase or a refund (M-A15); null otherwise.
 WalletOrderRef? get order;
/// Create a copy of WalletTransaction
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$WalletTransactionCopyWith<WalletTransaction> get copyWith => _$WalletTransactionCopyWithImpl<WalletTransaction>(this as WalletTransaction, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as WalletTransaction;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is WalletTransaction&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.type, _this.type) || other.type == _this.type)&&(identical(other.amount, _this.amount) || other.amount == _this.amount)&&(identical(other.balanceAfter, _this.balanceAfter) || other.balanceAfter == _this.balanceAfter)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.note, _this.note) || other.note == _this.note)&&(identical(other.order, _this.order) || other.order == _this.order));
}


@override
int get hashCode {
  final _this = this as WalletTransaction;
  return Object.hash(runtimeType,_this.id,_this.type,_this.amount,_this.balanceAfter,_this.createdAt,_this.note,_this.order);
}

@override
String toString() {
  final _this = this as WalletTransaction;
  return 'WalletTransaction(id: ${_this.id}, type: ${_this.type}, amount: ${_this.amount}, balanceAfter: ${_this.balanceAfter}, createdAt: ${_this.createdAt}, note: ${_this.note}, order: ${_this.order})';
}


}

/// @nodoc
abstract mixin class $WalletTransactionCopyWith<$Res>  {
  factory $WalletTransactionCopyWith(WalletTransaction value, $Res Function(WalletTransaction) _then) = _$WalletTransactionCopyWithImpl;
@useResult
$Res call({
 int id, String type, String amount, String balanceAfter, DateTime createdAt, String? note, WalletOrderRef? order
});


$WalletOrderRefCopyWith<$Res>? get order;

}
/// @nodoc
class _$WalletTransactionCopyWithImpl<$Res>
    implements $WalletTransactionCopyWith<$Res> {
  _$WalletTransactionCopyWithImpl(this._self, this._then);

  final WalletTransaction _self;
  final $Res Function(WalletTransaction) _then;

/// Create a copy of WalletTransaction
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? type = null,Object? amount = null,Object? balanceAfter = null,Object? createdAt = null,Object? note = freezed,Object? order = freezed,}) {
  return _then(WalletTransaction(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,amount: null == amount ? _self.amount : amount // ignore: cast_nullable_to_non_nullable
as String,balanceAfter: null == balanceAfter ? _self.balanceAfter : balanceAfter // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,note: freezed == note ? _self.note : note // ignore: cast_nullable_to_non_nullable
as String?,order: freezed == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as WalletOrderRef?,
  ));
}
/// Create a copy of WalletTransaction
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$WalletOrderRefCopyWith<$Res>? get order {
    if (_self.order == null) {
    return null;
  }

  return $WalletOrderRefCopyWith<$Res>(_self.order!, (value) {
    return _then(_self.copyWith(order: value));
  });
}
}


/// Adds pattern-matching-related methods to [WalletTransaction].
extension WalletTransactionPatterns on WalletTransaction {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _WalletTransaction value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _WalletTransaction() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _WalletTransaction value)  $default,){
final _that = this;
switch (_that) {
case _WalletTransaction():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _WalletTransaction value)?  $default,){
final _that = this;
switch (_that) {
case _WalletTransaction() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  String type,  String amount,  String balanceAfter,  DateTime createdAt,  String? note,  WalletOrderRef? order)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _WalletTransaction() when $default != null:
return $default(_that.id,_that.type,_that.amount,_that.balanceAfter,_that.createdAt,_that.note,_that.order);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  String type,  String amount,  String balanceAfter,  DateTime createdAt,  String? note,  WalletOrderRef? order)  $default,) {final _that = this;
switch (_that) {
case _WalletTransaction():
return $default(_that.id,_that.type,_that.amount,_that.balanceAfter,_that.createdAt,_that.note,_that.order);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  String type,  String amount,  String balanceAfter,  DateTime createdAt,  String? note,  WalletOrderRef? order)?  $default,) {final _that = this;
switch (_that) {
case _WalletTransaction() when $default != null:
return $default(_that.id,_that.type,_that.amount,_that.balanceAfter,_that.createdAt,_that.note,_that.order);case _:
  return null;

}
}

}

/// @nodoc


class _WalletTransaction extends WalletTransaction {
  const _WalletTransaction({required this.id, required this.type, required this.amount, required this.balanceAfter, required this.createdAt, this.note, this.order}): super._();
  

@override final  int id;
/// `credit_refund`, `credit_admin`, `debit_purchase`, `debit_admin` or `reversal`.
@override final  String type;
@override final  String amount;
@override final  String balanceAfter;
@override final  DateTime createdAt;
/// The server's note (English only): the staff reason of an adjustment, or a system note.
@override final  String? note;
/// The customer's own order behind a purchase or a refund (M-A15); null otherwise.
@override final  WalletOrderRef? order;

/// Create a copy of WalletTransaction
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$WalletTransactionCopyWith<_WalletTransaction> get copyWith => __$WalletTransactionCopyWithImpl<_WalletTransaction>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _WalletTransaction&&(identical(other.id, id) || other.id == id)&&(identical(other.type, type) || other.type == type)&&(identical(other.amount, amount) || other.amount == amount)&&(identical(other.balanceAfter, balanceAfter) || other.balanceAfter == balanceAfter)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.note, note) || other.note == note)&&(identical(other.order, order) || other.order == order));
}


@override
int get hashCode {
    return Object.hash(runtimeType,id,type,amount,balanceAfter,createdAt,note,order);
}

@override
String toString() {
    return 'WalletTransaction(id: $id, type: $type, amount: $amount, balanceAfter: $balanceAfter, createdAt: $createdAt, note: $note, order: $order)';
}


}

/// @nodoc
abstract mixin class _$WalletTransactionCopyWith<$Res> implements $WalletTransactionCopyWith<$Res> {
  factory _$WalletTransactionCopyWith(_WalletTransaction value, $Res Function(_WalletTransaction) _then) = __$WalletTransactionCopyWithImpl;
@override @useResult
$Res call({
 int id, String type, String amount, String balanceAfter, DateTime createdAt, String? note, WalletOrderRef? order
});


@override $WalletOrderRefCopyWith<$Res>? get order;

}
/// @nodoc
class __$WalletTransactionCopyWithImpl<$Res>
    implements _$WalletTransactionCopyWith<$Res> {
  __$WalletTransactionCopyWithImpl(this._self, this._then);

  final _WalletTransaction _self;
  final $Res Function(_WalletTransaction) _then;

/// Create a copy of WalletTransaction
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? type = null,Object? amount = null,Object? balanceAfter = null,Object? createdAt = null,Object? note = freezed,Object? order = freezed,}) {
  return _then(_WalletTransaction(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,amount: null == amount ? _self.amount : amount // ignore: cast_nullable_to_non_nullable
as String,balanceAfter: null == balanceAfter ? _self.balanceAfter : balanceAfter // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,note: freezed == note ? _self.note : note // ignore: cast_nullable_to_non_nullable
as String?,order: freezed == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as WalletOrderRef?,
  ));
}

/// Create a copy of WalletTransaction
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$WalletOrderRefCopyWith<$Res>? get order {
    if (_self.order == null) {
    return null;
  }

  return $WalletOrderRefCopyWith<$Res>(_self.order!, (value) {
    return _then(_self.copyWith(order: value));
  });
}
}

/// @nodoc
mixin _$WalletOrderRef {

 String get publicId; String get orderNumber;
/// Create a copy of WalletOrderRef
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$WalletOrderRefCopyWith<WalletOrderRef> get copyWith => _$WalletOrderRefCopyWithImpl<WalletOrderRef>(this as WalletOrderRef, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as WalletOrderRef;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is WalletOrderRef&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.orderNumber, _this.orderNumber) || other.orderNumber == _this.orderNumber));
}


@override
int get hashCode {
  final _this = this as WalletOrderRef;
  return Object.hash(runtimeType,_this.publicId,_this.orderNumber);
}

@override
String toString() {
  final _this = this as WalletOrderRef;
  return 'WalletOrderRef(publicId: ${_this.publicId}, orderNumber: ${_this.orderNumber})';
}


}

/// @nodoc
abstract mixin class $WalletOrderRefCopyWith<$Res>  {
  factory $WalletOrderRefCopyWith(WalletOrderRef value, $Res Function(WalletOrderRef) _then) = _$WalletOrderRefCopyWithImpl;
@useResult
$Res call({
 String publicId, String orderNumber
});




}
/// @nodoc
class _$WalletOrderRefCopyWithImpl<$Res>
    implements $WalletOrderRefCopyWith<$Res> {
  _$WalletOrderRefCopyWithImpl(this._self, this._then);

  final WalletOrderRef _self;
  final $Res Function(WalletOrderRef) _then;

/// Create a copy of WalletOrderRef
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? orderNumber = null,}) {
  return _then(WalletOrderRef(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,orderNumber: null == orderNumber ? _self.orderNumber : orderNumber // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [WalletOrderRef].
extension WalletOrderRefPatterns on WalletOrderRef {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _WalletOrderRef value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _WalletOrderRef() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _WalletOrderRef value)  $default,){
final _that = this;
switch (_that) {
case _WalletOrderRef():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _WalletOrderRef value)?  $default,){
final _that = this;
switch (_that) {
case _WalletOrderRef() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String orderNumber)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _WalletOrderRef() when $default != null:
return $default(_that.publicId,_that.orderNumber);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String orderNumber)  $default,) {final _that = this;
switch (_that) {
case _WalletOrderRef():
return $default(_that.publicId,_that.orderNumber);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String orderNumber)?  $default,) {final _that = this;
switch (_that) {
case _WalletOrderRef() when $default != null:
return $default(_that.publicId,_that.orderNumber);case _:
  return null;

}
}

}

/// @nodoc


class _WalletOrderRef implements WalletOrderRef {
  const _WalletOrderRef({required this.publicId, required this.orderNumber});
  

@override final  String publicId;
@override final  String orderNumber;

/// Create a copy of WalletOrderRef
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$WalletOrderRefCopyWith<_WalletOrderRef> get copyWith => __$WalletOrderRefCopyWithImpl<_WalletOrderRef>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _WalletOrderRef&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.orderNumber, orderNumber) || other.orderNumber == orderNumber));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,orderNumber);
}

@override
String toString() {
    return 'WalletOrderRef(publicId: $publicId, orderNumber: $orderNumber)';
}


}

/// @nodoc
abstract mixin class _$WalletOrderRefCopyWith<$Res> implements $WalletOrderRefCopyWith<$Res> {
  factory _$WalletOrderRefCopyWith(_WalletOrderRef value, $Res Function(_WalletOrderRef) _then) = __$WalletOrderRefCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String orderNumber
});




}
/// @nodoc
class __$WalletOrderRefCopyWithImpl<$Res>
    implements _$WalletOrderRefCopyWith<$Res> {
  __$WalletOrderRefCopyWithImpl(this._self, this._then);

  final _WalletOrderRef _self;
  final $Res Function(_WalletOrderRef) _then;

/// Create a copy of WalletOrderRef
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? orderNumber = null,}) {
  return _then(_WalletOrderRef(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,orderNumber: null == orderNumber ? _self.orderNumber : orderNumber // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

// dart format on
