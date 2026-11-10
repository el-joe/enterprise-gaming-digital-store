// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'support.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$SupportTicket {

 String get publicId; String get ticketNumber;/// `order`, `payment`, `refund`, `account`, `other`.
 String get category; String get subject; String get status; DateTime get createdAt; List<TicketMessage> get messages; DateTime? get lastActivityAt; OrderRef? get order;
/// Create a copy of SupportTicket
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SupportTicketCopyWith<SupportTicket> get copyWith => _$SupportTicketCopyWithImpl<SupportTicket>(this as SupportTicket, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as SupportTicket;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SupportTicket&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.ticketNumber, _this.ticketNumber) || other.ticketNumber == _this.ticketNumber)&&(identical(other.category, _this.category) || other.category == _this.category)&&(identical(other.subject, _this.subject) || other.subject == _this.subject)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&const DeepCollectionEquality().equals(other.messages, _this.messages)&&(identical(other.lastActivityAt, _this.lastActivityAt) || other.lastActivityAt == _this.lastActivityAt)&&(identical(other.order, _this.order) || other.order == _this.order));
}


@override
int get hashCode {
  final _this = this as SupportTicket;
  return Object.hash(runtimeType,_this.publicId,_this.ticketNumber,_this.category,_this.subject,_this.status,_this.createdAt,const DeepCollectionEquality().hash(_this.messages),_this.lastActivityAt,_this.order);
}

@override
String toString() {
  final _this = this as SupportTicket;
  return 'SupportTicket(publicId: ${_this.publicId}, ticketNumber: ${_this.ticketNumber}, category: ${_this.category}, subject: ${_this.subject}, status: ${_this.status}, createdAt: ${_this.createdAt}, messages: ${_this.messages}, lastActivityAt: ${_this.lastActivityAt}, order: ${_this.order})';
}


}

/// @nodoc
abstract mixin class $SupportTicketCopyWith<$Res>  {
  factory $SupportTicketCopyWith(SupportTicket value, $Res Function(SupportTicket) _then) = _$SupportTicketCopyWithImpl;
@useResult
$Res call({
 String publicId, String ticketNumber, String category, String subject, String status, DateTime createdAt, List<TicketMessage> messages, DateTime? lastActivityAt, OrderRef? order
});


$OrderRefCopyWith<$Res>? get order;

}
/// @nodoc
class _$SupportTicketCopyWithImpl<$Res>
    implements $SupportTicketCopyWith<$Res> {
  _$SupportTicketCopyWithImpl(this._self, this._then);

  final SupportTicket _self;
  final $Res Function(SupportTicket) _then;

/// Create a copy of SupportTicket
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? ticketNumber = null,Object? category = null,Object? subject = null,Object? status = null,Object? createdAt = null,Object? messages = null,Object? lastActivityAt = freezed,Object? order = freezed,}) {
  return _then(SupportTicket(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,ticketNumber: null == ticketNumber ? _self.ticketNumber : ticketNumber // ignore: cast_nullable_to_non_nullable
as String,category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,subject: null == subject ? _self.subject : subject // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,messages: null == messages ? _self.messages : messages // ignore: cast_nullable_to_non_nullable
as List<TicketMessage>,lastActivityAt: freezed == lastActivityAt ? _self.lastActivityAt : lastActivityAt // ignore: cast_nullable_to_non_nullable
as DateTime?,order: freezed == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderRef?,
  ));
}
/// Create a copy of SupportTicket
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderRefCopyWith<$Res>? get order {
    if (_self.order == null) {
    return null;
  }

  return $OrderRefCopyWith<$Res>(_self.order!, (value) {
    return _then(_self.copyWith(order: value));
  });
}
}


/// Adds pattern-matching-related methods to [SupportTicket].
extension SupportTicketPatterns on SupportTicket {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _SupportTicket value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _SupportTicket() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _SupportTicket value)  $default,){
final _that = this;
switch (_that) {
case _SupportTicket():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _SupportTicket value)?  $default,){
final _that = this;
switch (_that) {
case _SupportTicket() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String ticketNumber,  String category,  String subject,  String status,  DateTime createdAt,  List<TicketMessage> messages,  DateTime? lastActivityAt,  OrderRef? order)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _SupportTicket() when $default != null:
return $default(_that.publicId,_that.ticketNumber,_that.category,_that.subject,_that.status,_that.createdAt,_that.messages,_that.lastActivityAt,_that.order);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String ticketNumber,  String category,  String subject,  String status,  DateTime createdAt,  List<TicketMessage> messages,  DateTime? lastActivityAt,  OrderRef? order)  $default,) {final _that = this;
switch (_that) {
case _SupportTicket():
return $default(_that.publicId,_that.ticketNumber,_that.category,_that.subject,_that.status,_that.createdAt,_that.messages,_that.lastActivityAt,_that.order);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String ticketNumber,  String category,  String subject,  String status,  DateTime createdAt,  List<TicketMessage> messages,  DateTime? lastActivityAt,  OrderRef? order)?  $default,) {final _that = this;
switch (_that) {
case _SupportTicket() when $default != null:
return $default(_that.publicId,_that.ticketNumber,_that.category,_that.subject,_that.status,_that.createdAt,_that.messages,_that.lastActivityAt,_that.order);case _:
  return null;

}
}

}

/// @nodoc


class _SupportTicket implements SupportTicket {
  const _SupportTicket({required this.publicId, required this.ticketNumber, required this.category, required this.subject, required this.status, required this.createdAt, required  List<TicketMessage> messages, this.lastActivityAt, this.order}): _messages = messages;
  

@override final  String publicId;
@override final  String ticketNumber;
/// `order`, `payment`, `refund`, `account`, `other`.
@override final  String category;
@override final  String subject;
@override final  String status;
@override final  DateTime createdAt;
 final  List<TicketMessage> _messages;
@override List<TicketMessage> get messages {
  if (_messages is EqualUnmodifiableListView) return _messages;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_messages);
}

@override final  DateTime? lastActivityAt;
@override final  OrderRef? order;

/// Create a copy of SupportTicket
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$SupportTicketCopyWith<_SupportTicket> get copyWith => __$SupportTicketCopyWithImpl<_SupportTicket>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _SupportTicket&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.ticketNumber, ticketNumber) || other.ticketNumber == ticketNumber)&&(identical(other.category, category) || other.category == category)&&(identical(other.subject, subject) || other.subject == subject)&&(identical(other.status, status) || other.status == status)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&const DeepCollectionEquality().equals(other.messages, _messages)&&(identical(other.lastActivityAt, lastActivityAt) || other.lastActivityAt == lastActivityAt)&&(identical(other.order, order) || other.order == order));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,ticketNumber,category,subject,status,createdAt,const DeepCollectionEquality().hash(_messages),lastActivityAt,order);
}

@override
String toString() {
    return 'SupportTicket(publicId: $publicId, ticketNumber: $ticketNumber, category: $category, subject: $subject, status: $status, createdAt: $createdAt, messages: $messages, lastActivityAt: $lastActivityAt, order: $order)';
}


}

/// @nodoc
abstract mixin class _$SupportTicketCopyWith<$Res> implements $SupportTicketCopyWith<$Res> {
  factory _$SupportTicketCopyWith(_SupportTicket value, $Res Function(_SupportTicket) _then) = __$SupportTicketCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String ticketNumber, String category, String subject, String status, DateTime createdAt, List<TicketMessage> messages, DateTime? lastActivityAt, OrderRef? order
});


@override $OrderRefCopyWith<$Res>? get order;

}
/// @nodoc
class __$SupportTicketCopyWithImpl<$Res>
    implements _$SupportTicketCopyWith<$Res> {
  __$SupportTicketCopyWithImpl(this._self, this._then);

  final _SupportTicket _self;
  final $Res Function(_SupportTicket) _then;

/// Create a copy of SupportTicket
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? ticketNumber = null,Object? category = null,Object? subject = null,Object? status = null,Object? createdAt = null,Object? messages = null,Object? lastActivityAt = freezed,Object? order = freezed,}) {
  return _then(_SupportTicket(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,ticketNumber: null == ticketNumber ? _self.ticketNumber : ticketNumber // ignore: cast_nullable_to_non_nullable
as String,category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,subject: null == subject ? _self.subject : subject // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,messages: null == messages ? _self._messages : messages // ignore: cast_nullable_to_non_nullable
as List<TicketMessage>,lastActivityAt: freezed == lastActivityAt ? _self.lastActivityAt : lastActivityAt // ignore: cast_nullable_to_non_nullable
as DateTime?,order: freezed == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderRef?,
  ));
}

/// Create a copy of SupportTicket
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderRefCopyWith<$Res>? get order {
    if (_self.order == null) {
    return null;
  }

  return $OrderRefCopyWith<$Res>(_self.order!, (value) {
    return _then(_self.copyWith(order: value));
  });
}
}

/// @nodoc
mixin _$TicketMessage {

 int get id;/// `customer` or `staff`.
 String get from; String get body; DateTime get createdAt;
/// Create a copy of TicketMessage
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$TicketMessageCopyWith<TicketMessage> get copyWith => _$TicketMessageCopyWithImpl<TicketMessage>(this as TicketMessage, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as TicketMessage;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is TicketMessage&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.from, _this.from) || other.from == _this.from)&&(identical(other.body, _this.body) || other.body == _this.body)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt));
}


@override
int get hashCode {
  final _this = this as TicketMessage;
  return Object.hash(runtimeType,_this.id,_this.from,_this.body,_this.createdAt);
}

@override
String toString() {
  final _this = this as TicketMessage;
  return 'TicketMessage(id: ${_this.id}, from: ${_this.from}, body: ${_this.body}, createdAt: ${_this.createdAt})';
}


}

/// @nodoc
abstract mixin class $TicketMessageCopyWith<$Res>  {
  factory $TicketMessageCopyWith(TicketMessage value, $Res Function(TicketMessage) _then) = _$TicketMessageCopyWithImpl;
@useResult
$Res call({
 int id, String from, String body, DateTime createdAt
});




}
/// @nodoc
class _$TicketMessageCopyWithImpl<$Res>
    implements $TicketMessageCopyWith<$Res> {
  _$TicketMessageCopyWithImpl(this._self, this._then);

  final TicketMessage _self;
  final $Res Function(TicketMessage) _then;

/// Create a copy of TicketMessage
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? from = null,Object? body = null,Object? createdAt = null,}) {
  return _then(TicketMessage(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,from: null == from ? _self.from : from // ignore: cast_nullable_to_non_nullable
as String,body: null == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}

}


/// Adds pattern-matching-related methods to [TicketMessage].
extension TicketMessagePatterns on TicketMessage {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _TicketMessage value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _TicketMessage() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _TicketMessage value)  $default,){
final _that = this;
switch (_that) {
case _TicketMessage():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _TicketMessage value)?  $default,){
final _that = this;
switch (_that) {
case _TicketMessage() when $default != null:
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
case _TicketMessage() when $default != null:
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
case _TicketMessage():
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
case _TicketMessage() when $default != null:
return $default(_that.id,_that.from,_that.body,_that.createdAt);case _:
  return null;

}
}

}

/// @nodoc


class _TicketMessage implements TicketMessage {
  const _TicketMessage({required this.id, required this.from, required this.body, required this.createdAt});
  

@override final  int id;
/// `customer` or `staff`.
@override final  String from;
@override final  String body;
@override final  DateTime createdAt;

/// Create a copy of TicketMessage
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$TicketMessageCopyWith<_TicketMessage> get copyWith => __$TicketMessageCopyWithImpl<_TicketMessage>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _TicketMessage&&(identical(other.id, id) || other.id == id)&&(identical(other.from, from) || other.from == from)&&(identical(other.body, body) || other.body == body)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt));
}


@override
int get hashCode {
    return Object.hash(runtimeType,id,from,body,createdAt);
}

@override
String toString() {
    return 'TicketMessage(id: $id, from: $from, body: $body, createdAt: $createdAt)';
}


}

/// @nodoc
abstract mixin class _$TicketMessageCopyWith<$Res> implements $TicketMessageCopyWith<$Res> {
  factory _$TicketMessageCopyWith(_TicketMessage value, $Res Function(_TicketMessage) _then) = __$TicketMessageCopyWithImpl;
@override @useResult
$Res call({
 int id, String from, String body, DateTime createdAt
});




}
/// @nodoc
class __$TicketMessageCopyWithImpl<$Res>
    implements _$TicketMessageCopyWith<$Res> {
  __$TicketMessageCopyWithImpl(this._self, this._then);

  final _TicketMessage _self;
  final $Res Function(_TicketMessage) _then;

/// Create a copy of TicketMessage
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? from = null,Object? body = null,Object? createdAt = null,}) {
  return _then(_TicketMessage(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,from: null == from ? _self.from : from // ignore: cast_nullable_to_non_nullable
as String,body: null == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}

/// @nodoc
mixin _$OrderRef {

 String? get publicId; String? get orderNumber;
/// Create a copy of OrderRef
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$OrderRefCopyWith<OrderRef> get copyWith => _$OrderRefCopyWithImpl<OrderRef>(this as OrderRef, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as OrderRef;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is OrderRef&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.orderNumber, _this.orderNumber) || other.orderNumber == _this.orderNumber));
}


@override
int get hashCode {
  final _this = this as OrderRef;
  return Object.hash(runtimeType,_this.publicId,_this.orderNumber);
}

@override
String toString() {
  final _this = this as OrderRef;
  return 'OrderRef(publicId: ${_this.publicId}, orderNumber: ${_this.orderNumber})';
}


}

/// @nodoc
abstract mixin class $OrderRefCopyWith<$Res>  {
  factory $OrderRefCopyWith(OrderRef value, $Res Function(OrderRef) _then) = _$OrderRefCopyWithImpl;
@useResult
$Res call({
 String? publicId, String? orderNumber
});




}
/// @nodoc
class _$OrderRefCopyWithImpl<$Res>
    implements $OrderRefCopyWith<$Res> {
  _$OrderRefCopyWithImpl(this._self, this._then);

  final OrderRef _self;
  final $Res Function(OrderRef) _then;

/// Create a copy of OrderRef
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = freezed,Object? orderNumber = freezed,}) {
  return _then(OrderRef(
publicId: freezed == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String?,orderNumber: freezed == orderNumber ? _self.orderNumber : orderNumber // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [OrderRef].
extension OrderRefPatterns on OrderRef {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _OrderRef value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _OrderRef() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _OrderRef value)  $default,){
final _that = this;
switch (_that) {
case _OrderRef():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _OrderRef value)?  $default,){
final _that = this;
switch (_that) {
case _OrderRef() when $default != null:
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
case _OrderRef() when $default != null:
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
case _OrderRef():
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
case _OrderRef() when $default != null:
return $default(_that.publicId,_that.orderNumber);case _:
  return null;

}
}

}

/// @nodoc


class _OrderRef implements OrderRef {
  const _OrderRef({this.publicId, this.orderNumber});
  

@override final  String? publicId;
@override final  String? orderNumber;

/// Create a copy of OrderRef
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$OrderRefCopyWith<_OrderRef> get copyWith => __$OrderRefCopyWithImpl<_OrderRef>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _OrderRef&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.orderNumber, orderNumber) || other.orderNumber == orderNumber));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,orderNumber);
}

@override
String toString() {
    return 'OrderRef(publicId: $publicId, orderNumber: $orderNumber)';
}


}

/// @nodoc
abstract mixin class _$OrderRefCopyWith<$Res> implements $OrderRefCopyWith<$Res> {
  factory _$OrderRefCopyWith(_OrderRef value, $Res Function(_OrderRef) _then) = __$OrderRefCopyWithImpl;
@override @useResult
$Res call({
 String? publicId, String? orderNumber
});




}
/// @nodoc
class __$OrderRefCopyWithImpl<$Res>
    implements _$OrderRefCopyWith<$Res> {
  __$OrderRefCopyWithImpl(this._self, this._then);

  final _OrderRef _self;
  final $Res Function(_OrderRef) _then;

/// Create a copy of OrderRef
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = freezed,Object? orderNumber = freezed,}) {
  return _then(_OrderRef(
publicId: freezed == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String?,orderNumber: freezed == orderNumber ? _self.orderNumber : orderNumber // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc
mixin _$RefundRequest {

 int get id; OrderRef get order; String get reason; String get status; DateTime get createdAt; RefundRef? get refund;
/// Create a copy of RefundRequest
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RefundRequestCopyWith<RefundRequest> get copyWith => _$RefundRequestCopyWithImpl<RefundRequest>(this as RefundRequest, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as RefundRequest;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is RefundRequest&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.order, _this.order) || other.order == _this.order)&&(identical(other.reason, _this.reason) || other.reason == _this.reason)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.refund, _this.refund) || other.refund == _this.refund));
}


@override
int get hashCode {
  final _this = this as RefundRequest;
  return Object.hash(runtimeType,_this.id,_this.order,_this.reason,_this.status,_this.createdAt,_this.refund);
}

@override
String toString() {
  final _this = this as RefundRequest;
  return 'RefundRequest(id: ${_this.id}, order: ${_this.order}, reason: ${_this.reason}, status: ${_this.status}, createdAt: ${_this.createdAt}, refund: ${_this.refund})';
}


}

/// @nodoc
abstract mixin class $RefundRequestCopyWith<$Res>  {
  factory $RefundRequestCopyWith(RefundRequest value, $Res Function(RefundRequest) _then) = _$RefundRequestCopyWithImpl;
@useResult
$Res call({
 int id, OrderRef order, String reason, String status, DateTime createdAt, RefundRef? refund
});


$OrderRefCopyWith<$Res> get order;$RefundRefCopyWith<$Res>? get refund;

}
/// @nodoc
class _$RefundRequestCopyWithImpl<$Res>
    implements $RefundRequestCopyWith<$Res> {
  _$RefundRequestCopyWithImpl(this._self, this._then);

  final RefundRequest _self;
  final $Res Function(RefundRequest) _then;

/// Create a copy of RefundRequest
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? order = null,Object? reason = null,Object? status = null,Object? createdAt = null,Object? refund = freezed,}) {
  return _then(RefundRequest(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderRef,reason: null == reason ? _self.reason : reason // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,refund: freezed == refund ? _self.refund : refund // ignore: cast_nullable_to_non_nullable
as RefundRef?,
  ));
}
/// Create a copy of RefundRequest
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderRefCopyWith<$Res> get order {
  
  return $OrderRefCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}/// Create a copy of RefundRequest
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$RefundRefCopyWith<$Res>? get refund {
    if (_self.refund == null) {
    return null;
  }

  return $RefundRefCopyWith<$Res>(_self.refund!, (value) {
    return _then(_self.copyWith(refund: value));
  });
}
}


/// Adds pattern-matching-related methods to [RefundRequest].
extension RefundRequestPatterns on RefundRequest {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _RefundRequest value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _RefundRequest() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _RefundRequest value)  $default,){
final _that = this;
switch (_that) {
case _RefundRequest():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _RefundRequest value)?  $default,){
final _that = this;
switch (_that) {
case _RefundRequest() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  OrderRef order,  String reason,  String status,  DateTime createdAt,  RefundRef? refund)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _RefundRequest() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  OrderRef order,  String reason,  String status,  DateTime createdAt,  RefundRef? refund)  $default,) {final _that = this;
switch (_that) {
case _RefundRequest():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  OrderRef order,  String reason,  String status,  DateTime createdAt,  RefundRef? refund)?  $default,) {final _that = this;
switch (_that) {
case _RefundRequest() when $default != null:
return $default(_that.id,_that.order,_that.reason,_that.status,_that.createdAt,_that.refund);case _:
  return null;

}
}

}

/// @nodoc


class _RefundRequest implements RefundRequest {
  const _RefundRequest({required this.id, required this.order, required this.reason, required this.status, required this.createdAt, this.refund});
  

@override final  int id;
@override final  OrderRef order;
@override final  String reason;
@override final  String status;
@override final  DateTime createdAt;
@override final  RefundRef? refund;

/// Create a copy of RefundRequest
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$RefundRequestCopyWith<_RefundRequest> get copyWith => __$RefundRequestCopyWithImpl<_RefundRequest>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _RefundRequest&&(identical(other.id, id) || other.id == id)&&(identical(other.order, order) || other.order == order)&&(identical(other.reason, reason) || other.reason == reason)&&(identical(other.status, status) || other.status == status)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.refund, refund) || other.refund == refund));
}


@override
int get hashCode {
    return Object.hash(runtimeType,id,order,reason,status,createdAt,refund);
}

@override
String toString() {
    return 'RefundRequest(id: $id, order: $order, reason: $reason, status: $status, createdAt: $createdAt, refund: $refund)';
}


}

/// @nodoc
abstract mixin class _$RefundRequestCopyWith<$Res> implements $RefundRequestCopyWith<$Res> {
  factory _$RefundRequestCopyWith(_RefundRequest value, $Res Function(_RefundRequest) _then) = __$RefundRequestCopyWithImpl;
@override @useResult
$Res call({
 int id, OrderRef order, String reason, String status, DateTime createdAt, RefundRef? refund
});


@override $OrderRefCopyWith<$Res> get order;@override $RefundRefCopyWith<$Res>? get refund;

}
/// @nodoc
class __$RefundRequestCopyWithImpl<$Res>
    implements _$RefundRequestCopyWith<$Res> {
  __$RefundRequestCopyWithImpl(this._self, this._then);

  final _RefundRequest _self;
  final $Res Function(_RefundRequest) _then;

/// Create a copy of RefundRequest
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? order = null,Object? reason = null,Object? status = null,Object? createdAt = null,Object? refund = freezed,}) {
  return _then(_RefundRequest(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as OrderRef,reason: null == reason ? _self.reason : reason // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,refund: freezed == refund ? _self.refund : refund // ignore: cast_nullable_to_non_nullable
as RefundRef?,
  ));
}

/// Create a copy of RefundRequest
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$OrderRefCopyWith<$Res> get order {
  
  return $OrderRefCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}/// Create a copy of RefundRequest
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$RefundRefCopyWith<$Res>? get refund {
    if (_self.refund == null) {
    return null;
  }

  return $RefundRefCopyWith<$Res>(_self.refund!, (value) {
    return _then(_self.copyWith(refund: value));
  });
}
}

/// @nodoc
mixin _$RefundRef {

 String get publicId; String get status;
/// Create a copy of RefundRef
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RefundRefCopyWith<RefundRef> get copyWith => _$RefundRefCopyWithImpl<RefundRef>(this as RefundRef, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as RefundRef;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is RefundRef&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.status, _this.status) || other.status == _this.status));
}


@override
int get hashCode {
  final _this = this as RefundRef;
  return Object.hash(runtimeType,_this.publicId,_this.status);
}

@override
String toString() {
  final _this = this as RefundRef;
  return 'RefundRef(publicId: ${_this.publicId}, status: ${_this.status})';
}


}

/// @nodoc
abstract mixin class $RefundRefCopyWith<$Res>  {
  factory $RefundRefCopyWith(RefundRef value, $Res Function(RefundRef) _then) = _$RefundRefCopyWithImpl;
@useResult
$Res call({
 String publicId, String status
});




}
/// @nodoc
class _$RefundRefCopyWithImpl<$Res>
    implements $RefundRefCopyWith<$Res> {
  _$RefundRefCopyWithImpl(this._self, this._then);

  final RefundRef _self;
  final $Res Function(RefundRef) _then;

/// Create a copy of RefundRef
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? status = null,}) {
  return _then(RefundRef(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [RefundRef].
extension RefundRefPatterns on RefundRef {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _RefundRef value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _RefundRef() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _RefundRef value)  $default,){
final _that = this;
switch (_that) {
case _RefundRef():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _RefundRef value)?  $default,){
final _that = this;
switch (_that) {
case _RefundRef() when $default != null:
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
case _RefundRef() when $default != null:
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
case _RefundRef():
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
case _RefundRef() when $default != null:
return $default(_that.publicId,_that.status);case _:
  return null;

}
}

}

/// @nodoc


class _RefundRef implements RefundRef {
  const _RefundRef({required this.publicId, required this.status});
  

@override final  String publicId;
@override final  String status;

/// Create a copy of RefundRef
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$RefundRefCopyWith<_RefundRef> get copyWith => __$RefundRefCopyWithImpl<_RefundRef>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _RefundRef&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.status, status) || other.status == status));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,status);
}

@override
String toString() {
    return 'RefundRef(publicId: $publicId, status: $status)';
}


}

/// @nodoc
abstract mixin class _$RefundRefCopyWith<$Res> implements $RefundRefCopyWith<$Res> {
  factory _$RefundRefCopyWith(_RefundRef value, $Res Function(_RefundRef) _then) = __$RefundRefCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String status
});




}
/// @nodoc
class __$RefundRefCopyWithImpl<$Res>
    implements _$RefundRefCopyWith<$Res> {
  __$RefundRefCopyWithImpl(this._self, this._then);

  final _RefundRef _self;
  final $Res Function(_RefundRef) _then;

/// Create a copy of RefundRef
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? status = null,}) {
  return _then(_RefundRef(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

// dart format on
