// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'notifications.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$InboxNotification {

/// UUID.
 String get id;/// `order_delivered`, `order_partially_delivered`, `order_fulfilment_failed`,
/// `support_ticket_replied`, `payment_failed` (M-A18).
 String get type; String get category; String get title; String get body; DateTime get createdAt; LinkTarget? get link; DateTime? get readAt;
/// Create a copy of InboxNotification
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$InboxNotificationCopyWith<InboxNotification> get copyWith => _$InboxNotificationCopyWithImpl<InboxNotification>(this as InboxNotification, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as InboxNotification;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is InboxNotification&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.type, _this.type) || other.type == _this.type)&&(identical(other.category, _this.category) || other.category == _this.category)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.body, _this.body) || other.body == _this.body)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.link, _this.link) || other.link == _this.link)&&(identical(other.readAt, _this.readAt) || other.readAt == _this.readAt));
}


@override
int get hashCode {
  final _this = this as InboxNotification;
  return Object.hash(runtimeType,_this.id,_this.type,_this.category,_this.title,_this.body,_this.createdAt,_this.link,_this.readAt);
}

@override
String toString() {
  final _this = this as InboxNotification;
  return 'InboxNotification(id: ${_this.id}, type: ${_this.type}, category: ${_this.category}, title: ${_this.title}, body: ${_this.body}, createdAt: ${_this.createdAt}, link: ${_this.link}, readAt: ${_this.readAt})';
}


}

/// @nodoc
abstract mixin class $InboxNotificationCopyWith<$Res>  {
  factory $InboxNotificationCopyWith(InboxNotification value, $Res Function(InboxNotification) _then) = _$InboxNotificationCopyWithImpl;
@useResult
$Res call({
 String id, String type, String category, String title, String body, DateTime createdAt, LinkTarget? link, DateTime? readAt
});


$LinkTargetCopyWith<$Res>? get link;

}
/// @nodoc
class _$InboxNotificationCopyWithImpl<$Res>
    implements $InboxNotificationCopyWith<$Res> {
  _$InboxNotificationCopyWithImpl(this._self, this._then);

  final InboxNotification _self;
  final $Res Function(InboxNotification) _then;

/// Create a copy of InboxNotification
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? type = null,Object? category = null,Object? title = null,Object? body = null,Object? createdAt = null,Object? link = freezed,Object? readAt = freezed,}) {
  return _then(InboxNotification(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,body: null == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTarget?,readAt: freezed == readAt ? _self.readAt : readAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}
/// Create a copy of InboxNotification
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetCopyWith<$Res>? get link {
    if (_self.link == null) {
    return null;
  }

  return $LinkTargetCopyWith<$Res>(_self.link!, (value) {
    return _then(_self.copyWith(link: value));
  });
}
}


/// Adds pattern-matching-related methods to [InboxNotification].
extension InboxNotificationPatterns on InboxNotification {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _InboxNotification value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _InboxNotification() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _InboxNotification value)  $default,){
final _that = this;
switch (_that) {
case _InboxNotification():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _InboxNotification value)?  $default,){
final _that = this;
switch (_that) {
case _InboxNotification() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String id,  String type,  String category,  String title,  String body,  DateTime createdAt,  LinkTarget? link,  DateTime? readAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _InboxNotification() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String id,  String type,  String category,  String title,  String body,  DateTime createdAt,  LinkTarget? link,  DateTime? readAt)  $default,) {final _that = this;
switch (_that) {
case _InboxNotification():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String id,  String type,  String category,  String title,  String body,  DateTime createdAt,  LinkTarget? link,  DateTime? readAt)?  $default,) {final _that = this;
switch (_that) {
case _InboxNotification() when $default != null:
return $default(_that.id,_that.type,_that.category,_that.title,_that.body,_that.createdAt,_that.link,_that.readAt);case _:
  return null;

}
}

}

/// @nodoc


class _InboxNotification extends InboxNotification {
  const _InboxNotification({required this.id, required this.type, required this.category, required this.title, required this.body, required this.createdAt, this.link, this.readAt}): super._();
  

/// UUID.
@override final  String id;
/// `order_delivered`, `order_partially_delivered`, `order_fulfilment_failed`,
/// `support_ticket_replied`, `payment_failed` (M-A18).
@override final  String type;
@override final  String category;
@override final  String title;
@override final  String body;
@override final  DateTime createdAt;
@override final  LinkTarget? link;
@override final  DateTime? readAt;

/// Create a copy of InboxNotification
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$InboxNotificationCopyWith<_InboxNotification> get copyWith => __$InboxNotificationCopyWithImpl<_InboxNotification>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _InboxNotification&&(identical(other.id, id) || other.id == id)&&(identical(other.type, type) || other.type == type)&&(identical(other.category, category) || other.category == category)&&(identical(other.title, title) || other.title == title)&&(identical(other.body, body) || other.body == body)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.link, link) || other.link == link)&&(identical(other.readAt, readAt) || other.readAt == readAt));
}


@override
int get hashCode {
    return Object.hash(runtimeType,id,type,category,title,body,createdAt,link,readAt);
}

@override
String toString() {
    return 'InboxNotification(id: $id, type: $type, category: $category, title: $title, body: $body, createdAt: $createdAt, link: $link, readAt: $readAt)';
}


}

/// @nodoc
abstract mixin class _$InboxNotificationCopyWith<$Res> implements $InboxNotificationCopyWith<$Res> {
  factory _$InboxNotificationCopyWith(_InboxNotification value, $Res Function(_InboxNotification) _then) = __$InboxNotificationCopyWithImpl;
@override @useResult
$Res call({
 String id, String type, String category, String title, String body, DateTime createdAt, LinkTarget? link, DateTime? readAt
});


@override $LinkTargetCopyWith<$Res>? get link;

}
/// @nodoc
class __$InboxNotificationCopyWithImpl<$Res>
    implements _$InboxNotificationCopyWith<$Res> {
  __$InboxNotificationCopyWithImpl(this._self, this._then);

  final _InboxNotification _self;
  final $Res Function(_InboxNotification) _then;

/// Create a copy of InboxNotification
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? type = null,Object? category = null,Object? title = null,Object? body = null,Object? createdAt = null,Object? link = freezed,Object? readAt = freezed,}) {
  return _then(_InboxNotification(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,body: null == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTarget?,readAt: freezed == readAt ? _self.readAt : readAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

/// Create a copy of InboxNotification
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetCopyWith<$Res>? get link {
    if (_self.link == null) {
    return null;
  }

  return $LinkTargetCopyWith<$Res>(_self.link!, (value) {
    return _then(_self.copyWith(link: value));
  });
}
}

/// @nodoc
mixin _$InboxPage {

 List<InboxNotification> get notifications; PageInfo get page; int get unreadCount;
/// Create a copy of InboxPage
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$InboxPageCopyWith<InboxPage> get copyWith => _$InboxPageCopyWithImpl<InboxPage>(this as InboxPage, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as InboxPage;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is InboxPage&&const DeepCollectionEquality().equals(other.notifications, _this.notifications)&&(identical(other.page, _this.page) || other.page == _this.page)&&(identical(other.unreadCount, _this.unreadCount) || other.unreadCount == _this.unreadCount));
}


@override
int get hashCode {
  final _this = this as InboxPage;
  return Object.hash(runtimeType,const DeepCollectionEquality().hash(_this.notifications),_this.page,_this.unreadCount);
}

@override
String toString() {
  final _this = this as InboxPage;
  return 'InboxPage(notifications: ${_this.notifications}, page: ${_this.page}, unreadCount: ${_this.unreadCount})';
}


}

/// @nodoc
abstract mixin class $InboxPageCopyWith<$Res>  {
  factory $InboxPageCopyWith(InboxPage value, $Res Function(InboxPage) _then) = _$InboxPageCopyWithImpl;
@useResult
$Res call({
 List<InboxNotification> notifications, PageInfo page, int unreadCount
});


$PageInfoCopyWith<$Res> get page;

}
/// @nodoc
class _$InboxPageCopyWithImpl<$Res>
    implements $InboxPageCopyWith<$Res> {
  _$InboxPageCopyWithImpl(this._self, this._then);

  final InboxPage _self;
  final $Res Function(InboxPage) _then;

/// Create a copy of InboxPage
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? notifications = null,Object? page = null,Object? unreadCount = null,}) {
  return _then(InboxPage(
notifications: null == notifications ? _self.notifications : notifications // ignore: cast_nullable_to_non_nullable
as List<InboxNotification>,page: null == page ? _self.page : page // ignore: cast_nullable_to_non_nullable
as PageInfo,unreadCount: null == unreadCount ? _self.unreadCount : unreadCount // ignore: cast_nullable_to_non_nullable
as int,
  ));
}
/// Create a copy of InboxPage
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PageInfoCopyWith<$Res> get page {
  
  return $PageInfoCopyWith<$Res>(_self.page, (value) {
    return _then(_self.copyWith(page: value));
  });
}
}


/// Adds pattern-matching-related methods to [InboxPage].
extension InboxPagePatterns on InboxPage {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _InboxPage value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _InboxPage() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _InboxPage value)  $default,){
final _that = this;
switch (_that) {
case _InboxPage():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _InboxPage value)?  $default,){
final _that = this;
switch (_that) {
case _InboxPage() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<InboxNotification> notifications,  PageInfo page,  int unreadCount)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _InboxPage() when $default != null:
return $default(_that.notifications,_that.page,_that.unreadCount);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<InboxNotification> notifications,  PageInfo page,  int unreadCount)  $default,) {final _that = this;
switch (_that) {
case _InboxPage():
return $default(_that.notifications,_that.page,_that.unreadCount);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<InboxNotification> notifications,  PageInfo page,  int unreadCount)?  $default,) {final _that = this;
switch (_that) {
case _InboxPage() when $default != null:
return $default(_that.notifications,_that.page,_that.unreadCount);case _:
  return null;

}
}

}

/// @nodoc


class _InboxPage implements InboxPage {
  const _InboxPage({required  List<InboxNotification> notifications, required this.page, required this.unreadCount}): _notifications = notifications;
  

 final  List<InboxNotification> _notifications;
@override List<InboxNotification> get notifications {
  if (_notifications is EqualUnmodifiableListView) return _notifications;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_notifications);
}

@override final  PageInfo page;
@override final  int unreadCount;

/// Create a copy of InboxPage
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$InboxPageCopyWith<_InboxPage> get copyWith => __$InboxPageCopyWithImpl<_InboxPage>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _InboxPage&&const DeepCollectionEquality().equals(other.notifications, _notifications)&&(identical(other.page, page) || other.page == page)&&(identical(other.unreadCount, unreadCount) || other.unreadCount == unreadCount));
}


@override
int get hashCode {
    return Object.hash(runtimeType,const DeepCollectionEquality().hash(_notifications),page,unreadCount);
}

@override
String toString() {
    return 'InboxPage(notifications: $notifications, page: $page, unreadCount: $unreadCount)';
}


}

/// @nodoc
abstract mixin class _$InboxPageCopyWith<$Res> implements $InboxPageCopyWith<$Res> {
  factory _$InboxPageCopyWith(_InboxPage value, $Res Function(_InboxPage) _then) = __$InboxPageCopyWithImpl;
@override @useResult
$Res call({
 List<InboxNotification> notifications, PageInfo page, int unreadCount
});


@override $PageInfoCopyWith<$Res> get page;

}
/// @nodoc
class __$InboxPageCopyWithImpl<$Res>
    implements _$InboxPageCopyWith<$Res> {
  __$InboxPageCopyWithImpl(this._self, this._then);

  final _InboxPage _self;
  final $Res Function(_InboxPage) _then;

/// Create a copy of InboxPage
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? notifications = null,Object? page = null,Object? unreadCount = null,}) {
  return _then(_InboxPage(
notifications: null == notifications ? _self._notifications : notifications // ignore: cast_nullable_to_non_nullable
as List<InboxNotification>,page: null == page ? _self.page : page // ignore: cast_nullable_to_non_nullable
as PageInfo,unreadCount: null == unreadCount ? _self.unreadCount : unreadCount // ignore: cast_nullable_to_non_nullable
as int,
  ));
}

/// Create a copy of InboxPage
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PageInfoCopyWith<$Res> get page {
  
  return $PageInfoCopyWith<$Res>(_self.page, (value) {
    return _then(_self.copyWith(page: value));
  });
}
}

/// @nodoc
mixin _$NotificationPreference {

 String get category; String get channel; bool get enabled; bool get mandatory;
/// Create a copy of NotificationPreference
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$NotificationPreferenceCopyWith<NotificationPreference> get copyWith => _$NotificationPreferenceCopyWithImpl<NotificationPreference>(this as NotificationPreference, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as NotificationPreference;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is NotificationPreference&&(identical(other.category, _this.category) || other.category == _this.category)&&(identical(other.channel, _this.channel) || other.channel == _this.channel)&&(identical(other.enabled, _this.enabled) || other.enabled == _this.enabled)&&(identical(other.mandatory, _this.mandatory) || other.mandatory == _this.mandatory));
}


@override
int get hashCode {
  final _this = this as NotificationPreference;
  return Object.hash(runtimeType,_this.category,_this.channel,_this.enabled,_this.mandatory);
}

@override
String toString() {
  final _this = this as NotificationPreference;
  return 'NotificationPreference(category: ${_this.category}, channel: ${_this.channel}, enabled: ${_this.enabled}, mandatory: ${_this.mandatory})';
}


}

/// @nodoc
abstract mixin class $NotificationPreferenceCopyWith<$Res>  {
  factory $NotificationPreferenceCopyWith(NotificationPreference value, $Res Function(NotificationPreference) _then) = _$NotificationPreferenceCopyWithImpl;
@useResult
$Res call({
 String category, String channel, bool enabled, bool mandatory
});




}
/// @nodoc
class _$NotificationPreferenceCopyWithImpl<$Res>
    implements $NotificationPreferenceCopyWith<$Res> {
  _$NotificationPreferenceCopyWithImpl(this._self, this._then);

  final NotificationPreference _self;
  final $Res Function(NotificationPreference) _then;

/// Create a copy of NotificationPreference
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? category = null,Object? channel = null,Object? enabled = null,Object? mandatory = null,}) {
  return _then(NotificationPreference(
category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,channel: null == channel ? _self.channel : channel // ignore: cast_nullable_to_non_nullable
as String,enabled: null == enabled ? _self.enabled : enabled // ignore: cast_nullable_to_non_nullable
as bool,mandatory: null == mandatory ? _self.mandatory : mandatory // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [NotificationPreference].
extension NotificationPreferencePatterns on NotificationPreference {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _NotificationPreference value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _NotificationPreference() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _NotificationPreference value)  $default,){
final _that = this;
switch (_that) {
case _NotificationPreference():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _NotificationPreference value)?  $default,){
final _that = this;
switch (_that) {
case _NotificationPreference() when $default != null:
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
case _NotificationPreference() when $default != null:
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
case _NotificationPreference():
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
case _NotificationPreference() when $default != null:
return $default(_that.category,_that.channel,_that.enabled,_that.mandatory);case _:
  return null;

}
}

}

/// @nodoc


class _NotificationPreference implements NotificationPreference {
  const _NotificationPreference({required this.category, required this.channel, required this.enabled, required this.mandatory});
  

@override final  String category;
@override final  String channel;
@override final  bool enabled;
@override final  bool mandatory;

/// Create a copy of NotificationPreference
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$NotificationPreferenceCopyWith<_NotificationPreference> get copyWith => __$NotificationPreferenceCopyWithImpl<_NotificationPreference>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _NotificationPreference&&(identical(other.category, category) || other.category == category)&&(identical(other.channel, channel) || other.channel == channel)&&(identical(other.enabled, enabled) || other.enabled == enabled)&&(identical(other.mandatory, mandatory) || other.mandatory == mandatory));
}


@override
int get hashCode {
    return Object.hash(runtimeType,category,channel,enabled,mandatory);
}

@override
String toString() {
    return 'NotificationPreference(category: $category, channel: $channel, enabled: $enabled, mandatory: $mandatory)';
}


}

/// @nodoc
abstract mixin class _$NotificationPreferenceCopyWith<$Res> implements $NotificationPreferenceCopyWith<$Res> {
  factory _$NotificationPreferenceCopyWith(_NotificationPreference value, $Res Function(_NotificationPreference) _then) = __$NotificationPreferenceCopyWithImpl;
@override @useResult
$Res call({
 String category, String channel, bool enabled, bool mandatory
});




}
/// @nodoc
class __$NotificationPreferenceCopyWithImpl<$Res>
    implements _$NotificationPreferenceCopyWith<$Res> {
  __$NotificationPreferenceCopyWithImpl(this._self, this._then);

  final _NotificationPreference _self;
  final $Res Function(_NotificationPreference) _then;

/// Create a copy of NotificationPreference
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? category = null,Object? channel = null,Object? enabled = null,Object? mandatory = null,}) {
  return _then(_NotificationPreference(
category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,channel: null == channel ? _self.channel : channel // ignore: cast_nullable_to_non_nullable
as String,enabled: null == enabled ? _self.enabled : enabled // ignore: cast_nullable_to_non_nullable
as bool,mandatory: null == mandatory ? _self.mandatory : mandatory // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc
mixin _$PreferenceChange {

 String get category; String get channel; bool get enabled;
/// Create a copy of PreferenceChange
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PreferenceChangeCopyWith<PreferenceChange> get copyWith => _$PreferenceChangeCopyWithImpl<PreferenceChange>(this as PreferenceChange, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as PreferenceChange;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is PreferenceChange&&(identical(other.category, _this.category) || other.category == _this.category)&&(identical(other.channel, _this.channel) || other.channel == _this.channel)&&(identical(other.enabled, _this.enabled) || other.enabled == _this.enabled));
}


@override
int get hashCode {
  final _this = this as PreferenceChange;
  return Object.hash(runtimeType,_this.category,_this.channel,_this.enabled);
}

@override
String toString() {
  final _this = this as PreferenceChange;
  return 'PreferenceChange(category: ${_this.category}, channel: ${_this.channel}, enabled: ${_this.enabled})';
}


}

/// @nodoc
abstract mixin class $PreferenceChangeCopyWith<$Res>  {
  factory $PreferenceChangeCopyWith(PreferenceChange value, $Res Function(PreferenceChange) _then) = _$PreferenceChangeCopyWithImpl;
@useResult
$Res call({
 String category, String channel, bool enabled
});




}
/// @nodoc
class _$PreferenceChangeCopyWithImpl<$Res>
    implements $PreferenceChangeCopyWith<$Res> {
  _$PreferenceChangeCopyWithImpl(this._self, this._then);

  final PreferenceChange _self;
  final $Res Function(PreferenceChange) _then;

/// Create a copy of PreferenceChange
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? category = null,Object? channel = null,Object? enabled = null,}) {
  return _then(PreferenceChange(
category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,channel: null == channel ? _self.channel : channel // ignore: cast_nullable_to_non_nullable
as String,enabled: null == enabled ? _self.enabled : enabled // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [PreferenceChange].
extension PreferenceChangePatterns on PreferenceChange {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _PreferenceChange value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _PreferenceChange() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _PreferenceChange value)  $default,){
final _that = this;
switch (_that) {
case _PreferenceChange():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _PreferenceChange value)?  $default,){
final _that = this;
switch (_that) {
case _PreferenceChange() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String category,  String channel,  bool enabled)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _PreferenceChange() when $default != null:
return $default(_that.category,_that.channel,_that.enabled);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String category,  String channel,  bool enabled)  $default,) {final _that = this;
switch (_that) {
case _PreferenceChange():
return $default(_that.category,_that.channel,_that.enabled);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String category,  String channel,  bool enabled)?  $default,) {final _that = this;
switch (_that) {
case _PreferenceChange() when $default != null:
return $default(_that.category,_that.channel,_that.enabled);case _:
  return null;

}
}

}

/// @nodoc


class _PreferenceChange implements PreferenceChange {
  const _PreferenceChange({required this.category, required this.channel, required this.enabled});
  

@override final  String category;
@override final  String channel;
@override final  bool enabled;

/// Create a copy of PreferenceChange
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PreferenceChangeCopyWith<_PreferenceChange> get copyWith => __$PreferenceChangeCopyWithImpl<_PreferenceChange>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _PreferenceChange&&(identical(other.category, category) || other.category == category)&&(identical(other.channel, channel) || other.channel == channel)&&(identical(other.enabled, enabled) || other.enabled == enabled));
}


@override
int get hashCode {
    return Object.hash(runtimeType,category,channel,enabled);
}

@override
String toString() {
    return 'PreferenceChange(category: $category, channel: $channel, enabled: $enabled)';
}


}

/// @nodoc
abstract mixin class _$PreferenceChangeCopyWith<$Res> implements $PreferenceChangeCopyWith<$Res> {
  factory _$PreferenceChangeCopyWith(_PreferenceChange value, $Res Function(_PreferenceChange) _then) = __$PreferenceChangeCopyWithImpl;
@override @useResult
$Res call({
 String category, String channel, bool enabled
});




}
/// @nodoc
class __$PreferenceChangeCopyWithImpl<$Res>
    implements _$PreferenceChangeCopyWith<$Res> {
  __$PreferenceChangeCopyWithImpl(this._self, this._then);

  final _PreferenceChange _self;
  final $Res Function(_PreferenceChange) _then;

/// Create a copy of PreferenceChange
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? category = null,Object? channel = null,Object? enabled = null,}) {
  return _then(_PreferenceChange(
category: null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String,channel: null == channel ? _self.channel : channel // ignore: cast_nullable_to_non_nullable
as String,enabled: null == enabled ? _self.enabled : enabled // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

// dart format on
