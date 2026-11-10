// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'notification_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$InboxNotificationDto {

 String get id; String get type; String get category; String get title; String get body; DateTime get createdAt; LinkTargetDto? get link; DateTime? get readAt;
/// Create a copy of InboxNotificationDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$InboxNotificationDtoCopyWith<InboxNotificationDto> get copyWith => _$InboxNotificationDtoCopyWithImpl<InboxNotificationDto>(this as InboxNotificationDto, _$identity);

  /// Serializes this InboxNotificationDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as InboxNotificationDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is InboxNotificationDto&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.type, _this.type) || other.type == _this.type)&&(identical(other.category, _this.category) || other.category == _this.category)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.body, _this.body) || other.body == _this.body)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.link, _this.link) || other.link == _this.link)&&(identical(other.readAt, _this.readAt) || other.readAt == _this.readAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as InboxNotificationDto;
  return Object.hash(runtimeType,_this.id,_this.type,_this.category,_this.title,_this.body,_this.createdAt,_this.link,_this.readAt);
}

@override
String toString() {
  final _this = this as InboxNotificationDto;
  return 'InboxNotificationDto(id: ${_this.id}, type: ${_this.type}, category: ${_this.category}, title: ${_this.title}, body: ${_this.body}, createdAt: ${_this.createdAt}, link: ${_this.link}, readAt: ${_this.readAt})';
}


}

/// @nodoc
abstract mixin class $InboxNotificationDtoCopyWith<$Res>  {
  factory $InboxNotificationDtoCopyWith(InboxNotificationDto value, $Res Function(InboxNotificationDto) _then) = _$InboxNotificationDtoCopyWithImpl;
@useResult
$Res call({
 String id, String type, String category, String title, String body, DateTime createdAt, LinkTargetDto? link, DateTime? readAt
});


$LinkTargetDtoCopyWith<$Res>? get link;

}
/// @nodoc
class _$InboxNotificationDtoCopyWithImpl<$Res>
    implements $InboxNotificationDtoCopyWith<$Res> {
  _$InboxNotificationDtoCopyWithImpl(this._self, this._then);

  final InboxNotificationDto _self;
  final $Res Function(InboxNotificationDto) _then;

/// Create a copy of InboxNotificationDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? type = null,Object? category = null,Object? title = null,Object? body = null,Object? createdAt = null,Object? link = freezed,Object? readAt = freezed,}) {
  return _then(InboxNotificationDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,body: null == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTargetDto?,readAt: freezed == readAt ? _self.readAt : readAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}
/// Create a copy of InboxNotificationDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetDtoCopyWith<$Res>? get link {
    if (_self.link == null) {
    return null;
  }

  return $LinkTargetDtoCopyWith<$Res>(_self.link!, (value) {
    return _then(_self.copyWith(link: value));
  });
}
}


/// Adds pattern-matching-related methods to [InboxNotificationDto].
extension InboxNotificationDtoPatterns on InboxNotificationDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _InboxNotificationDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _InboxNotificationDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _InboxNotificationDto value)  $default,){
final _that = this;
switch (_that) {
case _InboxNotificationDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _InboxNotificationDto value)?  $default,){
final _that = this;
switch (_that) {
case _InboxNotificationDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String type,  String category,  String title,  String body,  DateTime createdAt,  LinkTargetDto? link,  DateTime? readAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _InboxNotificationDto() when $default != null:
return $default(_that.id,_that.type,_that.category,_that.title,_that.body,_that.createdAt,_that.link,_that.readAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String type,  String category,  String title,  String body,  DateTime createdAt,  LinkTargetDto? link,  DateTime? readAt)  $default,) {final _that = this;
switch (_that) {
case _InboxNotificationDto():
return $default(_that.id,_that.type,_that.category,_that.title,_that.body,_that.createdAt,_that.link,_that.readAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String type,  String category,  String title,  String body,  DateTime createdAt,  LinkTargetDto? link,  DateTime? readAt)?  $default,) {final _that = this;
switch (_that) {
case _InboxNotificationDto() when $default != null:
return $default(_that.id,_that.type,_that.category,_that.title,_that.body,_that.createdAt,_that.link,_that.readAt);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _InboxNotificationDto extends InboxNotificationDto {
  const _InboxNotificationDto({required this.id, required this.type, required this.category, required this.title, required this.body, required this.createdAt, this.link, this.readAt}): super._();
  factory _InboxNotificationDto.fromJson(Map<String, dynamic> json) => _$InboxNotificationDtoFromJson(json);

@override final  String id;
@override final  String type;
@override final  String category;
@override final  String title;
@override final  String body;
@override final  DateTime createdAt;
@override final  LinkTargetDto? link;
@override final  DateTime? readAt;

/// Create a copy of InboxNotificationDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$InboxNotificationDtoCopyWith<_InboxNotificationDto> get copyWith => __$InboxNotificationDtoCopyWithImpl<_InboxNotificationDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$InboxNotificationDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _InboxNotificationDto&&(identical(other.id, id) || other.id == id)&&(identical(other.type, type) || other.type == type)&&(identical(other.category, category) || other.category == category)&&(identical(other.title, title) || other.title == title)&&(identical(other.body, body) || other.body == body)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.link, link) || other.link == link)&&(identical(other.readAt, readAt) || other.readAt == readAt));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,id,type,category,title,body,createdAt,link,readAt);
}

@override
String toString() {
    return 'InboxNotificationDto(id: $id, type: $type, category: $category, title: $title, body: $body, createdAt: $createdAt, link: $link, readAt: $readAt)';
}


}

/// @nodoc
abstract mixin class _$InboxNotificationDtoCopyWith<$Res> implements $InboxNotificationDtoCopyWith<$Res> {
  factory _$InboxNotificationDtoCopyWith(_InboxNotificationDto value, $Res Function(_InboxNotificationDto) _then) = __$InboxNotificationDtoCopyWithImpl;
@override @useResult
$Res call({
 String id, String type, String category, String title, String body, DateTime createdAt, LinkTargetDto? link, DateTime? readAt
});


@override $LinkTargetDtoCopyWith<$Res>? get link;

}
/// @nodoc
class __$InboxNotificationDtoCopyWithImpl<$Res>
    implements _$InboxNotificationDtoCopyWith<$Res> {
  __$InboxNotificationDtoCopyWithImpl(this._self, this._then);

  final _InboxNotificationDto _self;
  final $Res Function(_InboxNotificationDto) _then;

/// Create a copy of InboxNotificationDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? type = null,Object? category = null,Object? title = null,Object? body = null,Object? createdAt = null,Object? link = freezed,Object? readAt = freezed,}) {
  return _then(_InboxNotificationDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,body: null == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTargetDto?,readAt: freezed == readAt ? _self.readAt : readAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

/// Create a copy of InboxNotificationDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetDtoCopyWith<$Res>? get link {
    if (_self.link == null) {
    return null;
  }

  return $LinkTargetDtoCopyWith<$Res>(_self.link!, (value) {
    return _then(_self.copyWith(link: value));
  });
}
}


/// @nodoc
mixin _$NotificationPreferenceDto {

 String get category; String get channel; bool get enabled; bool get mandatory;
/// Create a copy of NotificationPreferenceDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$NotificationPreferenceDtoCopyWith<NotificationPreferenceDto> get copyWith => _$NotificationPreferenceDtoCopyWithImpl<NotificationPreferenceDto>(this as NotificationPreferenceDto, _$identity);

  /// Serializes this NotificationPreferenceDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as NotificationPreferenceDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is NotificationPreferenceDto&&(identical(other.category, _this.category) || other.category == _this.category)&&(identical(other.channel, _this.channel) || other.channel == _this.channel)&&(identical(other.enabled, _this.enabled) || other.enabled == _this.enabled)&&(identical(other.mandatory, _this.mandatory) || other.mandatory == _this.mandatory));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as NotificationPreferenceDto;
  return Object.hash(runtimeType,_this.category,_this.channel,_this.enabled,_this.mandatory);
}

@override
String toString() {
  final _this = this as NotificationPreferenceDto;
  return 'NotificationPreferenceDto(category: ${_this.category}, channel: ${_this.channel}, enabled: ${_this.enabled}, mandatory: ${_this.mandatory})';
}


}

/// @nodoc
abstract mixin class $NotificationPreferenceDtoCopyWith<$Res>  {
  factory $NotificationPreferenceDtoCopyWith(NotificationPreferenceDto value, $Res Function(NotificationPreferenceDto) _then) = _$NotificationPreferenceDtoCopyWithImpl;
@useResult
$Res call({
 String category, String channel, bool enabled, bool mandatory
});




}
/// @nodoc
class _$NotificationPreferenceDtoCopyWithImpl<$Res>
    implements $NotificationPreferenceDtoCopyWith<$Res> {
  _$NotificationPreferenceDtoCopyWithImpl(this._self, this._then);

  final NotificationPreferenceDto _self;
  final $Res Function(NotificationPreferenceDto) _then;

/// Create a copy of NotificationPreferenceDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? category = null,Object? channel = null,Object? enabled = null,Object? mandatory = null,}) {
  return _then(NotificationPreferenceDto(
category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,channel: null == channel ? _self.channel : channel // ignore: cast_nullable_to_non_nullable
as String,enabled: null == enabled ? _self.enabled : enabled // ignore: cast_nullable_to_non_nullable
as bool,mandatory: null == mandatory ? _self.mandatory : mandatory // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [NotificationPreferenceDto].
extension NotificationPreferenceDtoPatterns on NotificationPreferenceDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _NotificationPreferenceDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _NotificationPreferenceDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _NotificationPreferenceDto value)  $default,){
final _that = this;
switch (_that) {
case _NotificationPreferenceDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _NotificationPreferenceDto value)?  $default,){
final _that = this;
switch (_that) {
case _NotificationPreferenceDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String category,  String channel,  bool enabled,  bool mandatory)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _NotificationPreferenceDto() when $default != null:
return $default(_that.category,_that.channel,_that.enabled,_that.mandatory);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String category,  String channel,  bool enabled,  bool mandatory)  $default,) {final _that = this;
switch (_that) {
case _NotificationPreferenceDto():
return $default(_that.category,_that.channel,_that.enabled,_that.mandatory);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String category,  String channel,  bool enabled,  bool mandatory)?  $default,) {final _that = this;
switch (_that) {
case _NotificationPreferenceDto() when $default != null:
return $default(_that.category,_that.channel,_that.enabled,_that.mandatory);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _NotificationPreferenceDto extends NotificationPreferenceDto {
  const _NotificationPreferenceDto({required this.category, required this.channel, required this.enabled, required this.mandatory}): super._();
  factory _NotificationPreferenceDto.fromJson(Map<String, dynamic> json) => _$NotificationPreferenceDtoFromJson(json);

@override final  String category;
@override final  String channel;
@override final  bool enabled;
@override final  bool mandatory;

/// Create a copy of NotificationPreferenceDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$NotificationPreferenceDtoCopyWith<_NotificationPreferenceDto> get copyWith => __$NotificationPreferenceDtoCopyWithImpl<_NotificationPreferenceDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$NotificationPreferenceDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _NotificationPreferenceDto&&(identical(other.category, category) || other.category == category)&&(identical(other.channel, channel) || other.channel == channel)&&(identical(other.enabled, enabled) || other.enabled == enabled)&&(identical(other.mandatory, mandatory) || other.mandatory == mandatory));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,category,channel,enabled,mandatory);
}

@override
String toString() {
    return 'NotificationPreferenceDto(category: $category, channel: $channel, enabled: $enabled, mandatory: $mandatory)';
}


}

/// @nodoc
abstract mixin class _$NotificationPreferenceDtoCopyWith<$Res> implements $NotificationPreferenceDtoCopyWith<$Res> {
  factory _$NotificationPreferenceDtoCopyWith(_NotificationPreferenceDto value, $Res Function(_NotificationPreferenceDto) _then) = __$NotificationPreferenceDtoCopyWithImpl;
@override @useResult
$Res call({
 String category, String channel, bool enabled, bool mandatory
});




}
/// @nodoc
class __$NotificationPreferenceDtoCopyWithImpl<$Res>
    implements _$NotificationPreferenceDtoCopyWith<$Res> {
  __$NotificationPreferenceDtoCopyWithImpl(this._self, this._then);

  final _NotificationPreferenceDto _self;
  final $Res Function(_NotificationPreferenceDto) _then;

/// Create a copy of NotificationPreferenceDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? category = null,Object? channel = null,Object? enabled = null,Object? mandatory = null,}) {
  return _then(_NotificationPreferenceDto(
category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,channel: null == channel ? _self.channel : channel // ignore: cast_nullable_to_non_nullable
as String,enabled: null == enabled ? _self.enabled : enabled // ignore: cast_nullable_to_non_nullable
as bool,mandatory: null == mandatory ? _self.mandatory : mandatory // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

// dart format on
