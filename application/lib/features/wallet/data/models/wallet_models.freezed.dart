// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'wallet_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$WalletDto {

 String get currency;@MoneyConverter() Money get balance; String get status; bool get topUpAvailable;
/// Create a copy of WalletDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$WalletDtoCopyWith<WalletDto> get copyWith => _$WalletDtoCopyWithImpl<WalletDto>(this as WalletDto, _$identity);

  /// Serializes this WalletDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as WalletDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is WalletDto&&(identical(other.currency, _this.currency) || other.currency == _this.currency)&&(identical(other.balance, _this.balance) || other.balance == _this.balance)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.topUpAvailable, _this.topUpAvailable) || other.topUpAvailable == _this.topUpAvailable));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as WalletDto;
  return Object.hash(runtimeType,_this.currency,_this.balance,_this.status,_this.topUpAvailable);
}

@override
String toString() {
  final _this = this as WalletDto;
  return 'WalletDto(currency: ${_this.currency}, balance: ${_this.balance}, status: ${_this.status}, topUpAvailable: ${_this.topUpAvailable})';
}


}

/// @nodoc
abstract mixin class $WalletDtoCopyWith<$Res>  {
  factory $WalletDtoCopyWith(WalletDto value, $Res Function(WalletDto) _then) = _$WalletDtoCopyWithImpl;
@useResult
$Res call({
 String currency,@MoneyConverter() Money balance, String status, bool topUpAvailable
});




}
/// @nodoc
class _$WalletDtoCopyWithImpl<$Res>
    implements $WalletDtoCopyWith<$Res> {
  _$WalletDtoCopyWithImpl(this._self, this._then);

  final WalletDto _self;
  final $Res Function(WalletDto) _then;

/// Create a copy of WalletDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? currency = null,Object? balance = null,Object? status = null,Object? topUpAvailable = null,}) {
  return _then(WalletDto(
currency: null == currency ? _self.currency : currency // ignore: cast_nullable_to_non_nullable
as String,balance: null == balance ? _self.balance : balance // ignore: cast_nullable_to_non_nullable
as Money,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,topUpAvailable: null == topUpAvailable ? _self.topUpAvailable : topUpAvailable // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [WalletDto].
extension WalletDtoPatterns on WalletDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _WalletDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _WalletDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _WalletDto value)  $default,){
final _that = this;
switch (_that) {
case _WalletDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _WalletDto value)?  $default,){
final _that = this;
switch (_that) {
case _WalletDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String currency, @MoneyConverter()  Money balance,  String status,  bool topUpAvailable)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _WalletDto() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String currency, @MoneyConverter()  Money balance,  String status,  bool topUpAvailable)  $default,) {final _that = this;
switch (_that) {
case _WalletDto():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String currency, @MoneyConverter()  Money balance,  String status,  bool topUpAvailable)?  $default,) {final _that = this;
switch (_that) {
case _WalletDto() when $default != null:
return $default(_that.currency,_that.balance,_that.status,_that.topUpAvailable);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _WalletDto extends WalletDto {
  const _WalletDto({required this.currency, @MoneyConverter() required this.balance, required this.status, required this.topUpAvailable}): super._();
  factory _WalletDto.fromJson(Map<String, dynamic> json) => _$WalletDtoFromJson(json);

@override final  String currency;
@override@MoneyConverter() final  Money balance;
@override final  String status;
@override final  bool topUpAvailable;

/// Create a copy of WalletDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$WalletDtoCopyWith<_WalletDto> get copyWith => __$WalletDtoCopyWithImpl<_WalletDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$WalletDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _WalletDto&&(identical(other.currency, currency) || other.currency == currency)&&(identical(other.balance, balance) || other.balance == balance)&&(identical(other.status, status) || other.status == status)&&(identical(other.topUpAvailable, topUpAvailable) || other.topUpAvailable == topUpAvailable));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,currency,balance,status,topUpAvailable);
}

@override
String toString() {
    return 'WalletDto(currency: $currency, balance: $balance, status: $status, topUpAvailable: $topUpAvailable)';
}


}

/// @nodoc
abstract mixin class _$WalletDtoCopyWith<$Res> implements $WalletDtoCopyWith<$Res> {
  factory _$WalletDtoCopyWith(_WalletDto value, $Res Function(_WalletDto) _then) = __$WalletDtoCopyWithImpl;
@override @useResult
$Res call({
 String currency,@MoneyConverter() Money balance, String status, bool topUpAvailable
});




}
/// @nodoc
class __$WalletDtoCopyWithImpl<$Res>
    implements _$WalletDtoCopyWith<$Res> {
  __$WalletDtoCopyWithImpl(this._self, this._then);

  final _WalletDto _self;
  final $Res Function(_WalletDto) _then;

/// Create a copy of WalletDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? currency = null,Object? balance = null,Object? status = null,Object? topUpAvailable = null,}) {
  return _then(_WalletDto(
currency: null == currency ? _self.currency : currency // ignore: cast_nullable_to_non_nullable
as String,balance: null == balance ? _self.balance : balance // ignore: cast_nullable_to_non_nullable
as Money,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,topUpAvailable: null == topUpAvailable ? _self.topUpAvailable : topUpAvailable // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}


/// @nodoc
mixin _$WalletTransactionDto {

 int get id; String get type; String get amount; String get balanceAfter; DateTime get createdAt; String? get note;
/// Create a copy of WalletTransactionDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$WalletTransactionDtoCopyWith<WalletTransactionDto> get copyWith => _$WalletTransactionDtoCopyWithImpl<WalletTransactionDto>(this as WalletTransactionDto, _$identity);

  /// Serializes this WalletTransactionDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as WalletTransactionDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is WalletTransactionDto&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.type, _this.type) || other.type == _this.type)&&(identical(other.amount, _this.amount) || other.amount == _this.amount)&&(identical(other.balanceAfter, _this.balanceAfter) || other.balanceAfter == _this.balanceAfter)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.note, _this.note) || other.note == _this.note));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as WalletTransactionDto;
  return Object.hash(runtimeType,_this.id,_this.type,_this.amount,_this.balanceAfter,_this.createdAt,_this.note);
}

@override
String toString() {
  final _this = this as WalletTransactionDto;
  return 'WalletTransactionDto(id: ${_this.id}, type: ${_this.type}, amount: ${_this.amount}, balanceAfter: ${_this.balanceAfter}, createdAt: ${_this.createdAt}, note: ${_this.note})';
}


}

/// @nodoc
abstract mixin class $WalletTransactionDtoCopyWith<$Res>  {
  factory $WalletTransactionDtoCopyWith(WalletTransactionDto value, $Res Function(WalletTransactionDto) _then) = _$WalletTransactionDtoCopyWithImpl;
@useResult
$Res call({
 int id, String type, String amount, String balanceAfter, DateTime createdAt, String? note
});




}
/// @nodoc
class _$WalletTransactionDtoCopyWithImpl<$Res>
    implements $WalletTransactionDtoCopyWith<$Res> {
  _$WalletTransactionDtoCopyWithImpl(this._self, this._then);

  final WalletTransactionDto _self;
  final $Res Function(WalletTransactionDto) _then;

/// Create a copy of WalletTransactionDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? type = null,Object? amount = null,Object? balanceAfter = null,Object? createdAt = null,Object? note = freezed,}) {
  return _then(WalletTransactionDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,amount: null == amount ? _self.amount : amount // ignore: cast_nullable_to_non_nullable
as String,balanceAfter: null == balanceAfter ? _self.balanceAfter : balanceAfter // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,note: freezed == note ? _self.note : note // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [WalletTransactionDto].
extension WalletTransactionDtoPatterns on WalletTransactionDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _WalletTransactionDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _WalletTransactionDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _WalletTransactionDto value)  $default,){
final _that = this;
switch (_that) {
case _WalletTransactionDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _WalletTransactionDto value)?  $default,){
final _that = this;
switch (_that) {
case _WalletTransactionDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  String type,  String amount,  String balanceAfter,  DateTime createdAt,  String? note)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _WalletTransactionDto() when $default != null:
return $default(_that.id,_that.type,_that.amount,_that.balanceAfter,_that.createdAt,_that.note);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  String type,  String amount,  String balanceAfter,  DateTime createdAt,  String? note)  $default,) {final _that = this;
switch (_that) {
case _WalletTransactionDto():
return $default(_that.id,_that.type,_that.amount,_that.balanceAfter,_that.createdAt,_that.note);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  String type,  String amount,  String balanceAfter,  DateTime createdAt,  String? note)?  $default,) {final _that = this;
switch (_that) {
case _WalletTransactionDto() when $default != null:
return $default(_that.id,_that.type,_that.amount,_that.balanceAfter,_that.createdAt,_that.note);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _WalletTransactionDto extends WalletTransactionDto {
  const _WalletTransactionDto({required this.id, required this.type, required this.amount, required this.balanceAfter, required this.createdAt, this.note}): super._();
  factory _WalletTransactionDto.fromJson(Map<String, dynamic> json) => _$WalletTransactionDtoFromJson(json);

@override final  int id;
@override final  String type;
@override final  String amount;
@override final  String balanceAfter;
@override final  DateTime createdAt;
@override final  String? note;

/// Create a copy of WalletTransactionDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$WalletTransactionDtoCopyWith<_WalletTransactionDto> get copyWith => __$WalletTransactionDtoCopyWithImpl<_WalletTransactionDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$WalletTransactionDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _WalletTransactionDto&&(identical(other.id, id) || other.id == id)&&(identical(other.type, type) || other.type == type)&&(identical(other.amount, amount) || other.amount == amount)&&(identical(other.balanceAfter, balanceAfter) || other.balanceAfter == balanceAfter)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.note, note) || other.note == note));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,id,type,amount,balanceAfter,createdAt,note);
}

@override
String toString() {
    return 'WalletTransactionDto(id: $id, type: $type, amount: $amount, balanceAfter: $balanceAfter, createdAt: $createdAt, note: $note)';
}


}

/// @nodoc
abstract mixin class _$WalletTransactionDtoCopyWith<$Res> implements $WalletTransactionDtoCopyWith<$Res> {
  factory _$WalletTransactionDtoCopyWith(_WalletTransactionDto value, $Res Function(_WalletTransactionDto) _then) = __$WalletTransactionDtoCopyWithImpl;
@override @useResult
$Res call({
 int id, String type, String amount, String balanceAfter, DateTime createdAt, String? note
});




}
/// @nodoc
class __$WalletTransactionDtoCopyWithImpl<$Res>
    implements _$WalletTransactionDtoCopyWith<$Res> {
  __$WalletTransactionDtoCopyWithImpl(this._self, this._then);

  final _WalletTransactionDto _self;
  final $Res Function(_WalletTransactionDto) _then;

/// Create a copy of WalletTransactionDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? type = null,Object? amount = null,Object? balanceAfter = null,Object? createdAt = null,Object? note = freezed,}) {
  return _then(_WalletTransactionDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,amount: null == amount ? _self.amount : amount // ignore: cast_nullable_to_non_nullable
as String,balanceAfter: null == balanceAfter ? _self.balanceAfter : balanceAfter // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,note: freezed == note ? _self.note : note // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

// dart format on
