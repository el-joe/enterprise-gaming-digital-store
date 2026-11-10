// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'support_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$OrderRefDto {

 String? get publicId; String? get orderNumber;
/// Create a copy of OrderRefDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderRefDtoCopyWith<OrderRefDto> get copyWith => _$OrderRefDtoCopyWithImpl<OrderRefDto>(this as OrderRefDto, _$identity);

  /// Serializes this OrderRefDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as OrderRefDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderRefDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.orderNumber, _this.orderNumber) || other.orderNumber == _this.orderNumber));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as OrderRefDto;
  return Object.hash(runtimeType,_this.publicId,_this.orderNumber);
}

@override
String toString() {
  final _this = this as OrderRefDto;
  return 'OrderRefDto(publicId: ${_this.publicId}, orderNumber: ${_this.orderNumber})';
}


}

/// @nodoc
abstract mixin class $OrderRefDtoCopyWith<$Res>  {
  factory $OrderRefDtoCopyWith(OrderRefDto value, $Res Function(OrderRefDto) _then) = _$OrderRefDtoCopyWithImpl;
@useResult
$Res call({
 String? publicId, String? orderNumber
});




}
/// @nodoc
class _$OrderRefDtoCopyWithImpl<$Res>
    implements $OrderRefDtoCopyWith<$Res> {
  _$OrderRefDtoCopyWithImpl(this._self, this._then);

  final OrderRefDto _self;
  final $Res Function(OrderRefDto) _then;

/// Create a copy of OrderRefDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = freezed,Object? orderNumber = freezed,}) {
  return _then(OrderRefDto(
publicId: freezed == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String?,orderNumber: freezed == orderNumber ? _self.orderNumber : orderNumber // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderRefDto].
extension OrderRefDtoPatterns on OrderRefDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderRefDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderRefDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderRefDto value)  $default,){
final _that = this;
switch (_that) {
case _OrderRefDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderRefDto value)?  $default,){
final _that = this;
switch (_that) {
case _OrderRefDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String? publicId,  String? orderNumber)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _OrderRefDto() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String? publicId,  String? orderNumber)  $default,) {final _that = this;
switch (_that) {
case _OrderRefDto():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String? publicId,  String? orderNumber)?  $default,) {final _that = this;
switch (_that) {
case _OrderRefDto() when $default != null:
return $default(_that.publicId,_that.orderNumber);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _OrderRefDto extends OrderRefDto {
  const _OrderRefDto({this.publicId, this.orderNumber}): super._();
  factory _OrderRefDto.fromJson(Map<String, dynamic> json) => _$OrderRefDtoFromJson(json);

@override final  String? publicId;
@override final  String? orderNumber;

/// Create a copy of OrderRefDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderRefDtoCopyWith<_OrderRefDto> get copyWith => __$OrderRefDtoCopyWithImpl<_OrderRefDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$OrderRefDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderRefDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.orderNumber, orderNumber) || other.orderNumber == orderNumber));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,orderNumber);
}

@override
String toString() {
    return 'OrderRefDto(publicId: $publicId, orderNumber: $orderNumber)';
}


}

/// @nodoc
abstract mixin class _$OrderRefDtoCopyWith<$Res> implements $OrderRefDtoCopyWith<$Res> {
  factory _$OrderRefDtoCopyWith(_OrderRefDto value, $Res Function(_OrderRefDto) _then) = __$OrderRefDtoCopyWithImpl;
@override @useResult
$Res call({
 String? publicId, String? orderNumber
});




}
/// @nodoc
class __$OrderRefDtoCopyWithImpl<$Res>
    implements _$OrderRefDtoCopyWith<$Res> {
  __$OrderRefDtoCopyWithImpl(this._self, this._then);

  final _OrderRefDto _self;
  final $Res Function(_OrderRefDto) _then;

/// Create a copy of OrderRefDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = freezed,Object? orderNumber = freezed,}) {
  return _then(_OrderRefDto(
publicId: freezed == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String?,orderNumber: freezed == orderNumber ? _self.orderNumber : orderNumber // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$TicketMessageDto {

 int get id; String get from; String get body; DateTime get createdAt;
/// Create a copy of TicketMessageDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$TicketMessageDtoCopyWith<TicketMessageDto> get copyWith => _$TicketMessageDtoCopyWithImpl<TicketMessageDto>(this as TicketMessageDto, _$identity);

  /// Serializes this TicketMessageDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as TicketMessageDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is TicketMessageDto&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.from, _this.from) || other.from == _this.from)&&(identical(other.body, _this.body) || other.body == _this.body)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as TicketMessageDto;
  return Object.hash(runtimeType,_this.id,_this.from,_this.body,_this.createdAt);
}

@override
String toString() {
  final _this = this as TicketMessageDto;
  return 'TicketMessageDto(id: ${_this.id}, from: ${_this.from}, body: ${_this.body}, createdAt: ${_this.createdAt})';
}


}

/// @nodoc
abstract mixin class $TicketMessageDtoCopyWith<$Res>  {
  factory $TicketMessageDtoCopyWith(TicketMessageDto value, $Res Function(TicketMessageDto) _then) = _$TicketMessageDtoCopyWithImpl;
@useResult
$Res call({
 int id, String from, String body, DateTime createdAt
});




}
/// @nodoc
class _$TicketMessageDtoCopyWithImpl<$Res>
    implements $TicketMessageDtoCopyWith<$Res> {
  _$TicketMessageDtoCopyWithImpl(this._self, this._then);

  final TicketMessageDto _self;
  final $Res Function(TicketMessageDto) _then;

/// Create a copy of TicketMessageDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? from = null,Object? body = null,Object? createdAt = null,}) {
  return _then(TicketMessageDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,from: null == from ? _self.from : from // ignore: cast_nullable_to_non_nullable
as String,body: null == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}

}


/// Adds pattern-matching-related methods to [TicketMessageDto].
extension TicketMessageDtoPatterns on TicketMessageDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _TicketMessageDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _TicketMessageDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _TicketMessageDto value)  $default,){
final _that = this;
switch (_that) {
case _TicketMessageDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _TicketMessageDto value)?  $default,){
final _that = this;
switch (_that) {
case _TicketMessageDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  String from,  String body,  DateTime createdAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _TicketMessageDto() when $default != null:
return $default(_that.id,_that.from,_that.body,_that.createdAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  String from,  String body,  DateTime createdAt)  $default,) {final _that = this;
switch (_that) {
case _TicketMessageDto():
return $default(_that.id,_that.from,_that.body,_that.createdAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  String from,  String body,  DateTime createdAt)?  $default,) {final _that = this;
switch (_that) {
case _TicketMessageDto() when $default != null:
return $default(_that.id,_that.from,_that.body,_that.createdAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _TicketMessageDto implements TicketMessageDto {
  const _TicketMessageDto({required this.id, required this.from, required this.body, required this.createdAt});
  factory _TicketMessageDto.fromJson(Map<String, dynamic> json) => _$TicketMessageDtoFromJson(json);

@override final  int id;
@override final  String from;
@override final  String body;
@override final  DateTime createdAt;

/// Create a copy of TicketMessageDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$TicketMessageDtoCopyWith<_TicketMessageDto> get copyWith => __$TicketMessageDtoCopyWithImpl<_TicketMessageDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$TicketMessageDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _TicketMessageDto&&(identical(other.id, id) || other.id == id)&&(identical(other.from, from) || other.from == from)&&(identical(other.body, body) || other.body == body)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,id,from,body,createdAt);
}

@override
String toString() {
    return 'TicketMessageDto(id: $id, from: $from, body: $body, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class _$TicketMessageDtoCopyWith<$Res> implements $TicketMessageDtoCopyWith<$Res> {
  factory _$TicketMessageDtoCopyWith(_TicketMessageDto value, $Res Function(_TicketMessageDto) _then) = __$TicketMessageDtoCopyWithImpl;
@override @useResult
$Res call({
 int id, String from, String body, DateTime createdAt
});




}
/// @nodoc
class __$TicketMessageDtoCopyWithImpl<$Res>
    implements _$TicketMessageDtoCopyWith<$Res> {
  __$TicketMessageDtoCopyWithImpl(this._self, this._then);

  final _TicketMessageDto _self;
  final $Res Function(_TicketMessageDto) _then;

/// Create a copy of TicketMessageDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? from = null,Object? body = null,Object? createdAt = null,}) {
  return _then(_TicketMessageDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,from: null == from ? _self.from : from // ignore: cast_nullable_to_non_nullable
as String,body: null == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}


/// @nodoc
mixin _$SupportTicketDto {

 String get publicId; String get ticketNumber; String get category; String get subject; String get status; DateTime get createdAt; DateTime? get lastActivityAt; OrderRefDto? get order; List<TicketMessageDto> get messages;
/// Create a copy of SupportTicketDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SupportTicketDtoCopyWith<SupportTicketDto> get copyWith => _$SupportTicketDtoCopyWithImpl<SupportTicketDto>(this as SupportTicketDto, _$identity);

  /// Serializes this SupportTicketDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as SupportTicketDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SupportTicketDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.ticketNumber, _this.ticketNumber) || other.ticketNumber == _this.ticketNumber)&&(identical(other.category, _this.category) || other.category == _this.category)&&(identical(other.subject, _this.subject) || other.subject == _this.subject)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.lastActivityAt, _this.lastActivityAt) || other.lastActivityAt == _this.lastActivityAt)&&(identical(other.order, _this.order) || other.order == _this.order)&&const DeepCollectionEquality().equals(other.messages, _this.messages));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as SupportTicketDto;
  return Object.hash(runtimeType,_this.publicId,_this.ticketNumber,_this.category,_this.subject,_this.status,_this.createdAt,_this.lastActivityAt,_this.order,const DeepCollectionEquality().hash(_this.messages));
}

@override
String toString() {
  final _this = this as SupportTicketDto;
  return 'SupportTicketDto(publicId: ${_this.publicId}, ticketNumber: ${_this.ticketNumber}, category: ${_this.category}, subject: ${_this.subject}, status: ${_this.status}, createdAt: ${_this.createdAt}, lastActivityAt: ${_this.lastActivityAt}, order: ${_this.order}, messages: ${_this.messages})';
}


}

/// @nodoc
abstract mixin class $SupportTicketDtoCopyWith<$Res>  {
  factory $SupportTicketDtoCopyWith(SupportTicketDto value, $Res Function(SupportTicketDto) _then) = _$SupportTicketDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String ticketNumber, String category, String subject, String status, DateTime createdAt, DateTime? lastActivityAt, OrderRefDto? order, List<TicketMessageDto> messages
});


$OrderRefDtoCopyWith<$Res>? get order;

}
/// @nodoc
class _$SupportTicketDtoCopyWithImpl<$Res>
    implements $SupportTicketDtoCopyWith<$Res> {
  _$SupportTicketDtoCopyWithImpl(this._self, this._then);

  final SupportTicketDto _self;
  final $Res Function(SupportTicketDto) _then;

/// Create a copy of SupportTicketDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? ticketNumber = null,Object? category = null,Object? subject = null,Object? status = null,Object? createdAt = null,Object? lastActivityAt = freezed,Object? order = freezed,Object? messages = null,}) {
  return _then(SupportTicketDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,ticketNumber: null == ticketNumber ? _self.ticketNumber : ticketNumber // ignore: cast_nullable_to_non_nullable
as String,category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,subject: null == subject ? _self.subject : subject // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,lastActivityAt: freezed == lastActivityAt ? _self.lastActivityAt : lastActivityAt // ignore: cast_nullable_to_non_nullable
as DateTime?,order: freezed == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderRefDto?,messages: null == messages ? _self.messages : messages // ignore: cast_nullable_to_non_nullable
as List<TicketMessageDto>,
  ));
}
/// Create a copy of SupportTicketDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderRefDtoCopyWith<$Res>? get order {
    if (_self.order == null) {
    return null;
  }

  return $OrderRefDtoCopyWith<$Res>(_self.order!, (value) {
    return _then(_self.copyWith(order: value));
  });
}
}


/// Adds pattern-matching-related methods to [SupportTicketDto].
extension SupportTicketDtoPatterns on SupportTicketDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _SupportTicketDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _SupportTicketDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _SupportTicketDto value)  $default,){
final _that = this;
switch (_that) {
case _SupportTicketDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _SupportTicketDto value)?  $default,){
final _that = this;
switch (_that) {
case _SupportTicketDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String ticketNumber,  String category,  String subject,  String status,  DateTime createdAt,  DateTime? lastActivityAt,  OrderRefDto? order,  List<TicketMessageDto> messages)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _SupportTicketDto() when $default != null:
return $default(_that.publicId,_that.ticketNumber,_that.category,_that.subject,_that.status,_that.createdAt,_that.lastActivityAt,_that.order,_that.messages);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String ticketNumber,  String category,  String subject,  String status,  DateTime createdAt,  DateTime? lastActivityAt,  OrderRefDto? order,  List<TicketMessageDto> messages)  $default,) {final _that = this;
switch (_that) {
case _SupportTicketDto():
return $default(_that.publicId,_that.ticketNumber,_that.category,_that.subject,_that.status,_that.createdAt,_that.lastActivityAt,_that.order,_that.messages);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String ticketNumber,  String category,  String subject,  String status,  DateTime createdAt,  DateTime? lastActivityAt,  OrderRefDto? order,  List<TicketMessageDto> messages)?  $default,) {final _that = this;
switch (_that) {
case _SupportTicketDto() when $default != null:
return $default(_that.publicId,_that.ticketNumber,_that.category,_that.subject,_that.status,_that.createdAt,_that.lastActivityAt,_that.order,_that.messages);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _SupportTicketDto extends SupportTicketDto {
  const _SupportTicketDto({required this.publicId, required this.ticketNumber, required this.category, required this.subject, required this.status, required this.createdAt, this.lastActivityAt, this.order,  List<TicketMessageDto> messages = const <TicketMessageDto>[]}): _messages = messages,super._();
  factory _SupportTicketDto.fromJson(Map<String, dynamic> json) => _$SupportTicketDtoFromJson(json);

@override final  String publicId;
@override final  String ticketNumber;
@override final  String category;
@override final  String subject;
@override final  String status;
@override final  DateTime createdAt;
@override final  DateTime? lastActivityAt;
@override final  OrderRefDto? order;
 final  List<TicketMessageDto> _messages;
@override@JsonKey() List<TicketMessageDto> get messages {
  if (_messages is EqualUnmodifiableListView) return _messages;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_messages);
}


/// Create a copy of SupportTicketDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$SupportTicketDtoCopyWith<_SupportTicketDto> get copyWith => __$SupportTicketDtoCopyWithImpl<_SupportTicketDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$SupportTicketDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _SupportTicketDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.ticketNumber, ticketNumber) || other.ticketNumber == ticketNumber)&&(identical(other.category, category) || other.category == category)&&(identical(other.subject, subject) || other.subject == subject)&&(identical(other.status, status) || other.status == status)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.lastActivityAt, lastActivityAt) || other.lastActivityAt == lastActivityAt)&&(identical(other.order, order) || other.order == order)&&const DeepCollectionEquality().equals(other.messages, _messages));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,ticketNumber,category,subject,status,createdAt,lastActivityAt,order,const DeepCollectionEquality().hash(_messages));
}

@override
String toString() {
    return 'SupportTicketDto(publicId: $publicId, ticketNumber: $ticketNumber, category: $category, subject: $subject, status: $status, createdAt: $createdAt, lastActivityAt: $lastActivityAt, order: $order, messages: $messages)';
}


}

/// @nodoc
abstract mixin class _$SupportTicketDtoCopyWith<$Res> implements $SupportTicketDtoCopyWith<$Res> {
  factory _$SupportTicketDtoCopyWith(_SupportTicketDto value, $Res Function(_SupportTicketDto) _then) = __$SupportTicketDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String ticketNumber, String category, String subject, String status, DateTime createdAt, DateTime? lastActivityAt, OrderRefDto? order, List<TicketMessageDto> messages
});


@override $OrderRefDtoCopyWith<$Res>? get order;

}
/// @nodoc
class __$SupportTicketDtoCopyWithImpl<$Res>
    implements _$SupportTicketDtoCopyWith<$Res> {
  __$SupportTicketDtoCopyWithImpl(this._self, this._then);

  final _SupportTicketDto _self;
  final $Res Function(_SupportTicketDto) _then;

/// Create a copy of SupportTicketDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? ticketNumber = null,Object? category = null,Object? subject = null,Object? status = null,Object? createdAt = null,Object? lastActivityAt = freezed,Object? order = freezed,Object? messages = null,}) {
  return _then(_SupportTicketDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,ticketNumber: null == ticketNumber ? _self.ticketNumber : ticketNumber // ignore: cast_nullable_to_non_nullable
as String,category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,subject: null == subject ? _self.subject : subject // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,lastActivityAt: freezed == lastActivityAt ? _self.lastActivityAt : lastActivityAt // ignore: cast_nullable_to_non_nullable
as DateTime?,order: freezed == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderRefDto?,messages: null == messages ? _self._messages : messages // ignore: cast_nullable_to_non_nullable
as List<TicketMessageDto>,
  ));
}

/// Create a copy of SupportTicketDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderRefDtoCopyWith<$Res>? get order {
    if (_self.order == null) {
    return null;
  }

  return $OrderRefDtoCopyWith<$Res>(_self.order!, (value) {
    return _then(_self.copyWith(order: value));
  });
}
}


/// @nodoc
mixin _$RefundRefDto {

 String get publicId; String get status;
/// Create a copy of RefundRefDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RefundRefDtoCopyWith<RefundRefDto> get copyWith => _$RefundRefDtoCopyWithImpl<RefundRefDto>(this as RefundRefDto, _$identity);

  /// Serializes this RefundRefDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as RefundRefDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is RefundRefDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.status, _this.status) || other.status == _this.status));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as RefundRefDto;
  return Object.hash(runtimeType,_this.publicId,_this.status);
}

@override
String toString() {
  final _this = this as RefundRefDto;
  return 'RefundRefDto(publicId: ${_this.publicId}, status: ${_this.status})';
}


}

/// @nodoc
abstract mixin class $RefundRefDtoCopyWith<$Res>  {
  factory $RefundRefDtoCopyWith(RefundRefDto value, $Res Function(RefundRefDto) _then) = _$RefundRefDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String status
});




}
/// @nodoc
class _$RefundRefDtoCopyWithImpl<$Res>
    implements $RefundRefDtoCopyWith<$Res> {
  _$RefundRefDtoCopyWithImpl(this._self, this._then);

  final RefundRefDto _self;
  final $Res Function(RefundRefDto) _then;

/// Create a copy of RefundRefDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? status = null,}) {
  return _then(RefundRefDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [RefundRefDto].
extension RefundRefDtoPatterns on RefundRefDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _RefundRefDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _RefundRefDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _RefundRefDto value)  $default,){
final _that = this;
switch (_that) {
case _RefundRefDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _RefundRefDto value)?  $default,){
final _that = this;
switch (_that) {
case _RefundRefDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String status)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _RefundRefDto() when $default != null:
return $default(_that.publicId,_that.status);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String status)  $default,) {final _that = this;
switch (_that) {
case _RefundRefDto():
return $default(_that.publicId,_that.status);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String status)?  $default,) {final _that = this;
switch (_that) {
case _RefundRefDto() when $default != null:
return $default(_that.publicId,_that.status);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _RefundRefDto implements RefundRefDto {
  const _RefundRefDto({required this.publicId, required this.status});
  factory _RefundRefDto.fromJson(Map<String, dynamic> json) => _$RefundRefDtoFromJson(json);

@override final  String publicId;
@override final  String status;

/// Create a copy of RefundRefDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$RefundRefDtoCopyWith<_RefundRefDto> get copyWith => __$RefundRefDtoCopyWithImpl<_RefundRefDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$RefundRefDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _RefundRefDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.status, status) || other.status == status));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,status);
}

@override
String toString() {
    return 'RefundRefDto(publicId: $publicId, status: $status)';
}


}

/// @nodoc
abstract mixin class _$RefundRefDtoCopyWith<$Res> implements $RefundRefDtoCopyWith<$Res> {
  factory _$RefundRefDtoCopyWith(_RefundRefDto value, $Res Function(_RefundRefDto) _then) = __$RefundRefDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String status
});




}
/// @nodoc
class __$RefundRefDtoCopyWithImpl<$Res>
    implements _$RefundRefDtoCopyWith<$Res> {
  __$RefundRefDtoCopyWithImpl(this._self, this._then);

  final _RefundRefDto _self;
  final $Res Function(_RefundRefDto) _then;

/// Create a copy of RefundRefDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? status = null,}) {
  return _then(_RefundRefDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}


/// @nodoc
mixin _$RefundRequestDto {

 int get id; OrderRefDto get order; String get reason; String get status; DateTime get createdAt; RefundRefDto? get refund;
/// Create a copy of RefundRequestDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RefundRequestDtoCopyWith<RefundRequestDto> get copyWith => _$RefundRequestDtoCopyWithImpl<RefundRequestDto>(this as RefundRequestDto, _$identity);

  /// Serializes this RefundRequestDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as RefundRequestDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is RefundRequestDto&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.order, _this.order) || other.order == _this.order)&&(identical(other.reason, _this.reason) || other.reason == _this.reason)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.refund, _this.refund) || other.refund == _this.refund));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as RefundRequestDto;
  return Object.hash(runtimeType,_this.id,_this.order,_this.reason,_this.status,_this.createdAt,_this.refund);
}

@override
String toString() {
  final _this = this as RefundRequestDto;
  return 'RefundRequestDto(id: ${_this.id}, order: ${_this.order}, reason: ${_this.reason}, status: ${_this.status}, createdAt: ${_this.createdAt}, refund: ${_this.refund})';
}


}

/// @nodoc
abstract mixin class $RefundRequestDtoCopyWith<$Res>  {
  factory $RefundRequestDtoCopyWith(RefundRequestDto value, $Res Function(RefundRequestDto) _then) = _$RefundRequestDtoCopyWithImpl;
@useResult
$Res call({
 int id, OrderRefDto order, String reason, String status, DateTime createdAt, RefundRefDto? refund
});


$OrderRefDtoCopyWith<$Res> get order;$RefundRefDtoCopyWith<$Res>? get refund;

}
/// @nodoc
class _$RefundRequestDtoCopyWithImpl<$Res>
    implements $RefundRequestDtoCopyWith<$Res> {
  _$RefundRequestDtoCopyWithImpl(this._self, this._then);

  final RefundRequestDto _self;
  final $Res Function(RefundRequestDto) _then;

/// Create a copy of RefundRequestDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? order = null,Object? reason = null,Object? status = null,Object? createdAt = null,Object? refund = freezed,}) {
  return _then(RefundRequestDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderRefDto,reason: null == reason ? _self.reason : reason // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,refund: freezed == refund ? _self.refund : refund // ignore: cast_nullable_to_non_nullable
as RefundRefDto?,
  ));
}
/// Create a copy of RefundRequestDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderRefDtoCopyWith<$Res> get order {
  
  return $OrderRefDtoCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}/// Create a copy of RefundRequestDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$RefundRefDtoCopyWith<$Res>? get refund {
    if (_self.refund == null) {
    return null;
  }

  return $RefundRefDtoCopyWith<$Res>(_self.refund!, (value) {
    return _then(_self.copyWith(refund: value));
  });
}
}


/// Adds pattern-matching-related methods to [RefundRequestDto].
extension RefundRequestDtoPatterns on RefundRequestDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _RefundRequestDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _RefundRequestDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _RefundRequestDto value)  $default,){
final _that = this;
switch (_that) {
case _RefundRequestDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _RefundRequestDto value)?  $default,){
final _that = this;
switch (_that) {
case _RefundRequestDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  OrderRefDto order,  String reason,  String status,  DateTime createdAt,  RefundRefDto? refund)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _RefundRequestDto() when $default != null:
return $default(_that.id,_that.order,_that.reason,_that.status,_that.createdAt,_that.refund);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  OrderRefDto order,  String reason,  String status,  DateTime createdAt,  RefundRefDto? refund)  $default,) {final _that = this;
switch (_that) {
case _RefundRequestDto():
return $default(_that.id,_that.order,_that.reason,_that.status,_that.createdAt,_that.refund);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  OrderRefDto order,  String reason,  String status,  DateTime createdAt,  RefundRefDto? refund)?  $default,) {final _that = this;
switch (_that) {
case _RefundRequestDto() when $default != null:
return $default(_that.id,_that.order,_that.reason,_that.status,_that.createdAt,_that.refund);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _RefundRequestDto extends RefundRequestDto {
  const _RefundRequestDto({required this.id, required this.order, required this.reason, required this.status, required this.createdAt, this.refund}): super._();
  factory _RefundRequestDto.fromJson(Map<String, dynamic> json) => _$RefundRequestDtoFromJson(json);

@override final  int id;
@override final  OrderRefDto order;
@override final  String reason;
@override final  String status;
@override final  DateTime createdAt;
@override final  RefundRefDto? refund;

/// Create a copy of RefundRequestDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$RefundRequestDtoCopyWith<_RefundRequestDto> get copyWith => __$RefundRequestDtoCopyWithImpl<_RefundRequestDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$RefundRequestDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _RefundRequestDto&&(identical(other.id, id) || other.id == id)&&(identical(other.order, order) || other.order == order)&&(identical(other.reason, reason) || other.reason == reason)&&(identical(other.status, status) || other.status == status)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.refund, refund) || other.refund == refund));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,id,order,reason,status,createdAt,refund);
}

@override
String toString() {
    return 'RefundRequestDto(id: $id, order: $order, reason: $reason, status: $status, createdAt: $createdAt, refund: $refund)';
}


}

/// @nodoc
abstract mixin class _$RefundRequestDtoCopyWith<$Res> implements $RefundRequestDtoCopyWith<$Res> {
  factory _$RefundRequestDtoCopyWith(_RefundRequestDto value, $Res Function(_RefundRequestDto) _then) = __$RefundRequestDtoCopyWithImpl;
@override @useResult
$Res call({
 int id, OrderRefDto order, String reason, String status, DateTime createdAt, RefundRefDto? refund
});


@override $OrderRefDtoCopyWith<$Res> get order;@override $RefundRefDtoCopyWith<$Res>? get refund;

}
/// @nodoc
class __$RefundRequestDtoCopyWithImpl<$Res>
    implements _$RefundRequestDtoCopyWith<$Res> {
  __$RefundRequestDtoCopyWithImpl(this._self, this._then);

  final _RefundRequestDto _self;
  final $Res Function(_RefundRequestDto) _then;

/// Create a copy of RefundRequestDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? order = null,Object? reason = null,Object? status = null,Object? createdAt = null,Object? refund = freezed,}) {
  return _then(_RefundRequestDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderRefDto,reason: null == reason ? _self.reason : reason // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,refund: freezed == refund ? _self.refund : refund // ignore: cast_nullable_to_non_nullable
as RefundRefDto?,
  ));
}

/// Create a copy of RefundRequestDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderRefDtoCopyWith<$Res> get order {
  
  return $OrderRefDtoCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}/// Create a copy of RefundRequestDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$RefundRefDtoCopyWith<$Res>? get refund {
    if (_self.refund == null) {
    return null;
  }

  return $RefundRefDtoCopyWith<$Res>(_self.refund!, (value) {
    return _then(_self.copyWith(refund: value));
  });
}
}

// dart format on
