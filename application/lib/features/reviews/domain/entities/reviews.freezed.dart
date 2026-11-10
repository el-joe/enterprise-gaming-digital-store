// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'reviews.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$MyReview {

 int get id; int get orderItemId; String get productName; String get productSlug; int get rating; String get status; DateTime get createdAt; String? get title; String? get body;
/// Create a copy of MyReview
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MyReviewCopyWith<MyReview> get copyWith => _$MyReviewCopyWithImpl<MyReview>(this as MyReview, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as MyReview;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MyReview&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.orderItemId, _this.orderItemId) || other.orderItemId == _this.orderItemId)&&(identical(other.productName, _this.productName) || other.productName == _this.productName)&&(identical(other.productSlug, _this.productSlug) || other.productSlug == _this.productSlug)&&(identical(other.rating, _this.rating) || other.rating == _this.rating)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.body, _this.body) || other.body == _this.body));
}


@override
int get hashCode {
  final _this = this as MyReview;
  return Object.hash(runtimeType,_this.id,_this.orderItemId,_this.productName,_this.productSlug,_this.rating,_this.status,_this.createdAt,_this.title,_this.body);
}

@override
String toString() {
  final _this = this as MyReview;
  return 'MyReview(id: ${_this.id}, orderItemId: ${_this.orderItemId}, productName: ${_this.productName}, productSlug: ${_this.productSlug}, rating: ${_this.rating}, status: ${_this.status}, createdAt: ${_this.createdAt}, title: ${_this.title}, body: ${_this.body})';
}


}

/// @nodoc
abstract mixin class $MyReviewCopyWith<$Res>  {
  factory $MyReviewCopyWith(MyReview value, $Res Function(MyReview) _then) = _$MyReviewCopyWithImpl;
@useResult
$Res call({
 int id, int orderItemId, String productName, String productSlug, int rating, String status, DateTime createdAt, String? title, String? body
});




}
/// @nodoc
class _$MyReviewCopyWithImpl<$Res>
    implements $MyReviewCopyWith<$Res> {
  _$MyReviewCopyWithImpl(this._self, this._then);

  final MyReview _self;
  final $Res Function(MyReview) _then;

/// Create a copy of MyReview
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? orderItemId = null,Object? productName = null,Object? productSlug = null,Object? rating = null,Object? status = null,Object? createdAt = null,Object? title = freezed,Object? body = freezed,}) {
  return _then(MyReview(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,orderItemId: null == orderItemId ? _self.orderItemId : orderItemId // ignore: cast_nullable_to_non_nullable
as int,productName: null == productName ? _self.productName : productName // ignore: cast_nullable_to_non_nullable
as String,productSlug: null == productSlug ? _self.productSlug : productSlug // ignore: cast_nullable_to_non_nullable
as String,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as int,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,body: freezed == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [MyReview].
extension MyReviewPatterns on MyReview {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _MyReview value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _MyReview() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _MyReview value)  $default,){
final _that = this;
switch (_that) {
case _MyReview():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _MyReview value)?  $default,){
final _that = this;
switch (_that) {
case _MyReview() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  int orderItemId,  String productName,  String productSlug,  int rating,  String status,  DateTime createdAt,  String? title,  String? body)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _MyReview() when $default != null:
return $default(_that.id,_that.orderItemId,_that.productName,_that.productSlug,_that.rating,_that.status,_that.createdAt,_that.title,_that.body);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  int orderItemId,  String productName,  String productSlug,  int rating,  String status,  DateTime createdAt,  String? title,  String? body)  $default,) {final _that = this;
switch (_that) {
case _MyReview():
return $default(_that.id,_that.orderItemId,_that.productName,_that.productSlug,_that.rating,_that.status,_that.createdAt,_that.title,_that.body);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  int orderItemId,  String productName,  String productSlug,  int rating,  String status,  DateTime createdAt,  String? title,  String? body)?  $default,) {final _that = this;
switch (_that) {
case _MyReview() when $default != null:
return $default(_that.id,_that.orderItemId,_that.productName,_that.productSlug,_that.rating,_that.status,_that.createdAt,_that.title,_that.body);case _:
  return null;

}
}

}

/// @nodoc


class _MyReview implements MyReview {
  const _MyReview({required this.id, required this.orderItemId, required this.productName, required this.productSlug, required this.rating, required this.status, required this.createdAt, this.title, this.body});
  

@override final  int id;
@override final  int orderItemId;
@override final  String productName;
@override final  String productSlug;
@override final  int rating;
@override final  String status;
@override final  DateTime createdAt;
@override final  String? title;
@override final  String? body;

/// Create a copy of MyReview
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$MyReviewCopyWith<_MyReview> get copyWith => __$MyReviewCopyWithImpl<_MyReview>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _MyReview&&(identical(other.id, id) || other.id == id)&&(identical(other.orderItemId, orderItemId) || other.orderItemId == orderItemId)&&(identical(other.productName, productName) || other.productName == productName)&&(identical(other.productSlug, productSlug) || other.productSlug == productSlug)&&(identical(other.rating, rating) || other.rating == rating)&&(identical(other.status, status) || other.status == status)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.title, title) || other.title == title)&&(identical(other.body, body) || other.body == body));
}


@override
int get hashCode {
    return Object.hash(runtimeType,id,orderItemId,productName,productSlug,rating,status,createdAt,title,body);
}

@override
String toString() {
    return 'MyReview(id: $id, orderItemId: $orderItemId, productName: $productName, productSlug: $productSlug, rating: $rating, status: $status, createdAt: $createdAt, title: $title, body: $body)';
}


}

/// @nodoc
abstract mixin class _$MyReviewCopyWith<$Res> implements $MyReviewCopyWith<$Res> {
  factory _$MyReviewCopyWith(_MyReview value, $Res Function(_MyReview) _then) = __$MyReviewCopyWithImpl;
@override @useResult
$Res call({
 int id, int orderItemId, String productName, String productSlug, int rating, String status, DateTime createdAt, String? title, String? body
});




}
/// @nodoc
class __$MyReviewCopyWithImpl<$Res>
    implements _$MyReviewCopyWith<$Res> {
  __$MyReviewCopyWithImpl(this._self, this._then);

  final _MyReview _self;
  final $Res Function(_MyReview) _then;

/// Create a copy of MyReview
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? orderItemId = null,Object? productName = null,Object? productSlug = null,Object? rating = null,Object? status = null,Object? createdAt = null,Object? title = freezed,Object? body = freezed,}) {
  return _then(_MyReview(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,orderItemId: null == orderItemId ? _self.orderItemId : orderItemId // ignore: cast_nullable_to_non_nullable
as int,productName: null == productName ? _self.productName : productName // ignore: cast_nullable_to_non_nullable
as String,productSlug: null == productSlug ? _self.productSlug : productSlug // ignore: cast_nullable_to_non_nullable
as String,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as int,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,body: freezed == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc
mixin _$ReviewableItem {

 int get orderItemId; String get orderPublicId; String get orderNumber; String get productName; String get productSlug;
/// Create a copy of ReviewableItem
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ReviewableItemCopyWith<ReviewableItem> get copyWith => _$ReviewableItemCopyWithImpl<ReviewableItem>(this as ReviewableItem, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as ReviewableItem;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ReviewableItem&&(identical(other.orderItemId, _this.orderItemId) || other.orderItemId == _this.orderItemId)&&(identical(other.orderPublicId, _this.orderPublicId) || other.orderPublicId == _this.orderPublicId)&&(identical(other.orderNumber, _this.orderNumber) || other.orderNumber == _this.orderNumber)&&(identical(other.productName, _this.productName) || other.productName == _this.productName)&&(identical(other.productSlug, _this.productSlug) || other.productSlug == _this.productSlug));
}


@override
int get hashCode {
  final _this = this as ReviewableItem;
  return Object.hash(runtimeType,_this.orderItemId,_this.orderPublicId,_this.orderNumber,_this.productName,_this.productSlug);
}

@override
String toString() {
  final _this = this as ReviewableItem;
  return 'ReviewableItem(orderItemId: ${_this.orderItemId}, orderPublicId: ${_this.orderPublicId}, orderNumber: ${_this.orderNumber}, productName: ${_this.productName}, productSlug: ${_this.productSlug})';
}


}

/// @nodoc
abstract mixin class $ReviewableItemCopyWith<$Res>  {
  factory $ReviewableItemCopyWith(ReviewableItem value, $Res Function(ReviewableItem) _then) = _$ReviewableItemCopyWithImpl;
@useResult
$Res call({
 int orderItemId, String orderPublicId, String orderNumber, String productName, String productSlug
});




}
/// @nodoc
class _$ReviewableItemCopyWithImpl<$Res>
    implements $ReviewableItemCopyWith<$Res> {
  _$ReviewableItemCopyWithImpl(this._self, this._then);

  final ReviewableItem _self;
  final $Res Function(ReviewableItem) _then;

/// Create a copy of ReviewableItem
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? orderItemId = null,Object? orderPublicId = null,Object? orderNumber = null,Object? productName = null,Object? productSlug = null,}) {
  return _then(ReviewableItem(
orderItemId: null == orderItemId ? _self.orderItemId : orderItemId // ignore: cast_nullable_to_non_nullable
as int,orderPublicId: null == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String,orderNumber: null == orderNumber ? _self.orderNumber : orderNumber // ignore: cast_nullable_to_non_nullable
as String,productName: null == productName ? _self.productName : productName // ignore: cast_nullable_to_non_nullable
as String,productSlug: null == productSlug ? _self.productSlug : productSlug // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [ReviewableItem].
extension ReviewableItemPatterns on ReviewableItem {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ReviewableItem value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ReviewableItem() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ReviewableItem value)  $default,){
final _that = this;
switch (_that) {
case _ReviewableItem():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ReviewableItem value)?  $default,){
final _that = this;
switch (_that) {
case _ReviewableItem() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int orderItemId,  String orderPublicId,  String orderNumber,  String productName,  String productSlug)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ReviewableItem() when $default != null:
return $default(_that.orderItemId,_that.orderPublicId,_that.orderNumber,_that.productName,_that.productSlug);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int orderItemId,  String orderPublicId,  String orderNumber,  String productName,  String productSlug)  $default,) {final _that = this;
switch (_that) {
case _ReviewableItem():
return $default(_that.orderItemId,_that.orderPublicId,_that.orderNumber,_that.productName,_that.productSlug);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int orderItemId,  String orderPublicId,  String orderNumber,  String productName,  String productSlug)?  $default,) {final _that = this;
switch (_that) {
case _ReviewableItem() when $default != null:
return $default(_that.orderItemId,_that.orderPublicId,_that.orderNumber,_that.productName,_that.productSlug);case _:
  return null;

}
}

}

/// @nodoc


class _ReviewableItem implements ReviewableItem {
  const _ReviewableItem({required this.orderItemId, required this.orderPublicId, required this.orderNumber, required this.productName, required this.productSlug});
  

@override final  int orderItemId;
@override final  String orderPublicId;
@override final  String orderNumber;
@override final  String productName;
@override final  String productSlug;

/// Create a copy of ReviewableItem
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ReviewableItemCopyWith<_ReviewableItem> get copyWith => __$ReviewableItemCopyWithImpl<_ReviewableItem>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ReviewableItem&&(identical(other.orderItemId, orderItemId) || other.orderItemId == orderItemId)&&(identical(other.orderPublicId, orderPublicId) || other.orderPublicId == orderPublicId)&&(identical(other.orderNumber, orderNumber) || other.orderNumber == orderNumber)&&(identical(other.productName, productName) || other.productName == productName)&&(identical(other.productSlug, productSlug) || other.productSlug == productSlug));
}


@override
int get hashCode {
    return Object.hash(runtimeType,orderItemId,orderPublicId,orderNumber,productName,productSlug);
}

@override
String toString() {
    return 'ReviewableItem(orderItemId: $orderItemId, orderPublicId: $orderPublicId, orderNumber: $orderNumber, productName: $productName, productSlug: $productSlug)';
}


}

/// @nodoc
abstract mixin class _$ReviewableItemCopyWith<$Res> implements $ReviewableItemCopyWith<$Res> {
  factory _$ReviewableItemCopyWith(_ReviewableItem value, $Res Function(_ReviewableItem) _then) = __$ReviewableItemCopyWithImpl;
@override @useResult
$Res call({
 int orderItemId, String orderPublicId, String orderNumber, String productName, String productSlug
});




}
/// @nodoc
class __$ReviewableItemCopyWithImpl<$Res>
    implements _$ReviewableItemCopyWith<$Res> {
  __$ReviewableItemCopyWithImpl(this._self, this._then);

  final _ReviewableItem _self;
  final $Res Function(_ReviewableItem) _then;

/// Create a copy of ReviewableItem
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? orderItemId = null,Object? orderPublicId = null,Object? orderNumber = null,Object? productName = null,Object? productSlug = null,}) {
  return _then(_ReviewableItem(
orderItemId: null == orderItemId ? _self.orderItemId : orderItemId // ignore: cast_nullable_to_non_nullable
as int,orderPublicId: null == orderPublicId ? _self.orderPublicId : orderPublicId // ignore: cast_nullable_to_non_nullable
as String,orderNumber: null == orderNumber ? _self.orderNumber : orderNumber // ignore: cast_nullable_to_non_nullable
as String,productName: null == productName ? _self.productName : productName // ignore: cast_nullable_to_non_nullable
as String,productSlug: null == productSlug ? _self.productSlug : productSlug // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

// dart format on
