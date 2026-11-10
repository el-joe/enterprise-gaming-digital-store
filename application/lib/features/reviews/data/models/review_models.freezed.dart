// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'review_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$ReviewProductDto {

 String get name; String get slug;
/// Create a copy of ReviewProductDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ReviewProductDtoCopyWith<ReviewProductDto> get copyWith => _$ReviewProductDtoCopyWithImpl<ReviewProductDto>(this as ReviewProductDto, _$identity);

  /// Serializes this ReviewProductDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as ReviewProductDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ReviewProductDto&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as ReviewProductDto;
  return Object.hash(runtimeType,_this.name,_this.slug);
}

@override
String toString() {
  final _this = this as ReviewProductDto;
  return 'ReviewProductDto(name: ${_this.name}, slug: ${_this.slug})';
}


}

/// @nodoc
abstract mixin class $ReviewProductDtoCopyWith<$Res>  {
  factory $ReviewProductDtoCopyWith(ReviewProductDto value, $Res Function(ReviewProductDto) _then) = _$ReviewProductDtoCopyWithImpl;
@useResult
$Res call({
 String name, String slug
});




}
/// @nodoc
class _$ReviewProductDtoCopyWithImpl<$Res>
    implements $ReviewProductDtoCopyWith<$Res> {
  _$ReviewProductDtoCopyWithImpl(this._self, this._then);

  final ReviewProductDto _self;
  final $Res Function(ReviewProductDto) _then;

/// Create a copy of ReviewProductDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? name = null,Object? slug = null,}) {
  return _then(ReviewProductDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [ReviewProductDto].
extension ReviewProductDtoPatterns on ReviewProductDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ReviewProductDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ReviewProductDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ReviewProductDto value)  $default,){
final _that = this;
switch (_that) {
case _ReviewProductDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ReviewProductDto value)?  $default,){
final _that = this;
switch (_that) {
case _ReviewProductDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String name,  String slug)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ReviewProductDto() when $default != null:
return $default(_that.name,_that.slug);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String name,  String slug)  $default,) {final _that = this;
switch (_that) {
case _ReviewProductDto():
return $default(_that.name,_that.slug);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String name,  String slug)?  $default,) {final _that = this;
switch (_that) {
case _ReviewProductDto() when $default != null:
return $default(_that.name,_that.slug);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _ReviewProductDto implements ReviewProductDto {
  const _ReviewProductDto({required this.name, required this.slug});
  factory _ReviewProductDto.fromJson(Map<String, dynamic> json) => _$ReviewProductDtoFromJson(json);

@override final  String name;
@override final  String slug;

/// Create a copy of ReviewProductDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ReviewProductDtoCopyWith<_ReviewProductDto> get copyWith => __$ReviewProductDtoCopyWithImpl<_ReviewProductDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$ReviewProductDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ReviewProductDto&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,name,slug);
}

@override
String toString() {
    return 'ReviewProductDto(name: $name, slug: $slug)';
}


}

/// @nodoc
abstract mixin class _$ReviewProductDtoCopyWith<$Res> implements $ReviewProductDtoCopyWith<$Res> {
  factory _$ReviewProductDtoCopyWith(_ReviewProductDto value, $Res Function(_ReviewProductDto) _then) = __$ReviewProductDtoCopyWithImpl;
@override @useResult
$Res call({
 String name, String slug
});




}
/// @nodoc
class __$ReviewProductDtoCopyWithImpl<$Res>
    implements _$ReviewProductDtoCopyWith<$Res> {
  __$ReviewProductDtoCopyWithImpl(this._self, this._then);

  final _ReviewProductDto _self;
  final $Res Function(_ReviewProductDto) _then;

/// Create a copy of ReviewProductDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? name = null,Object? slug = null,}) {
  return _then(_ReviewProductDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}


/// @nodoc
mixin _$MyReviewDto {

 int get id; int get orderItemId; ReviewProductDto get product; int get rating; String get status; DateTime get createdAt; String? get title; String? get body;
/// Create a copy of MyReviewDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$MyReviewDtoCopyWith<MyReviewDto> get copyWith => _$MyReviewDtoCopyWithImpl<MyReviewDto>(this as MyReviewDto, _$identity);

  /// Serializes this MyReviewDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as MyReviewDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is MyReviewDto&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.orderItemId, _this.orderItemId) || other.orderItemId == _this.orderItemId)&&(identical(other.product, _this.product) || other.product == _this.product)&&(identical(other.rating, _this.rating) || other.rating == _this.rating)&&(identical(other.status, _this.status) || other.status == _this.status)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.body, _this.body) || other.body == _this.body));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as MyReviewDto;
  return Object.hash(runtimeType,_this.id,_this.orderItemId,_this.product,_this.rating,_this.status,_this.createdAt,_this.title,_this.body);
}

@override
String toString() {
  final _this = this as MyReviewDto;
  return 'MyReviewDto(id: ${_this.id}, orderItemId: ${_this.orderItemId}, product: ${_this.product}, rating: ${_this.rating}, status: ${_this.status}, createdAt: ${_this.createdAt}, title: ${_this.title}, body: ${_this.body})';
}


}

/// @nodoc
abstract mixin class $MyReviewDtoCopyWith<$Res>  {
  factory $MyReviewDtoCopyWith(MyReviewDto value, $Res Function(MyReviewDto) _then) = _$MyReviewDtoCopyWithImpl;
@useResult
$Res call({
 int id, int orderItemId, ReviewProductDto product, int rating, String status, DateTime createdAt, String? title, String? body
});


$ReviewProductDtoCopyWith<$Res> get product;

}
/// @nodoc
class _$MyReviewDtoCopyWithImpl<$Res>
    implements $MyReviewDtoCopyWith<$Res> {
  _$MyReviewDtoCopyWithImpl(this._self, this._then);

  final MyReviewDto _self;
  final $Res Function(MyReviewDto) _then;

/// Create a copy of MyReviewDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? orderItemId = null,Object? product = null,Object? rating = null,Object? status = null,Object? createdAt = null,Object? title = freezed,Object? body = freezed,}) {
  return _then(MyReviewDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,orderItemId: null == orderItemId ? _self.orderItemId : orderItemId // ignore: cast_nullable_to_non_nullable
as int,product: null == product ? _self.product : product // ignore: cast_nullable_to_non_nullable
as ReviewProductDto,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as int,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,body: freezed == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}
/// Create a copy of MyReviewDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$ReviewProductDtoCopyWith<$Res> get product {
  
  return $ReviewProductDtoCopyWith<$Res>(_self.product, (value) {
    return _then(_self.copyWith(product: value));
  });
}
}


/// Adds pattern-matching-related methods to [MyReviewDto].
extension MyReviewDtoPatterns on MyReviewDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _MyReviewDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _MyReviewDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _MyReviewDto value)  $default,){
final _that = this;
switch (_that) {
case _MyReviewDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _MyReviewDto value)?  $default,){
final _that = this;
switch (_that) {
case _MyReviewDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  int orderItemId,  ReviewProductDto product,  int rating,  String status,  DateTime createdAt,  String? title,  String? body)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _MyReviewDto() when $default != null:
return $default(_that.id,_that.orderItemId,_that.product,_that.rating,_that.status,_that.createdAt,_that.title,_that.body);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  int orderItemId,  ReviewProductDto product,  int rating,  String status,  DateTime createdAt,  String? title,  String? body)  $default,) {final _that = this;
switch (_that) {
case _MyReviewDto():
return $default(_that.id,_that.orderItemId,_that.product,_that.rating,_that.status,_that.createdAt,_that.title,_that.body);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  int orderItemId,  ReviewProductDto product,  int rating,  String status,  DateTime createdAt,  String? title,  String? body)?  $default,) {final _that = this;
switch (_that) {
case _MyReviewDto() when $default != null:
return $default(_that.id,_that.orderItemId,_that.product,_that.rating,_that.status,_that.createdAt,_that.title,_that.body);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _MyReviewDto extends MyReviewDto {
  const _MyReviewDto({required this.id, required this.orderItemId, required this.product, required this.rating, required this.status, required this.createdAt, this.title, this.body}): super._();
  factory _MyReviewDto.fromJson(Map<String, dynamic> json) => _$MyReviewDtoFromJson(json);

@override final  int id;
@override final  int orderItemId;
@override final  ReviewProductDto product;
@override final  int rating;
@override final  String status;
@override final  DateTime createdAt;
@override final  String? title;
@override final  String? body;

/// Create a copy of MyReviewDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$MyReviewDtoCopyWith<_MyReviewDto> get copyWith => __$MyReviewDtoCopyWithImpl<_MyReviewDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$MyReviewDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _MyReviewDto&&(identical(other.id, id) || other.id == id)&&(identical(other.orderItemId, orderItemId) || other.orderItemId == orderItemId)&&(identical(other.product, product) || other.product == product)&&(identical(other.rating, rating) || other.rating == rating)&&(identical(other.status, status) || other.status == status)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.title, title) || other.title == title)&&(identical(other.body, body) || other.body == body));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,id,orderItemId,product,rating,status,createdAt,title,body);
}

@override
String toString() {
    return 'MyReviewDto(id: $id, orderItemId: $orderItemId, product: $product, rating: $rating, status: $status, createdAt: $createdAt, title: $title, body: $body)';
}


}

/// @nodoc
abstract mixin class _$MyReviewDtoCopyWith<$Res> implements $MyReviewDtoCopyWith<$Res> {
  factory _$MyReviewDtoCopyWith(_MyReviewDto value, $Res Function(_MyReviewDto) _then) = __$MyReviewDtoCopyWithImpl;
@override @useResult
$Res call({
 int id, int orderItemId, ReviewProductDto product, int rating, String status, DateTime createdAt, String? title, String? body
});


@override $ReviewProductDtoCopyWith<$Res> get product;

}
/// @nodoc
class __$MyReviewDtoCopyWithImpl<$Res>
    implements _$MyReviewDtoCopyWith<$Res> {
  __$MyReviewDtoCopyWithImpl(this._self, this._then);

  final _MyReviewDto _self;
  final $Res Function(_MyReviewDto) _then;

/// Create a copy of MyReviewDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? orderItemId = null,Object? product = null,Object? rating = null,Object? status = null,Object? createdAt = null,Object? title = freezed,Object? body = freezed,}) {
  return _then(_MyReviewDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,orderItemId: null == orderItemId ? _self.orderItemId : orderItemId // ignore: cast_nullable_to_non_nullable
as int,product: null == product ? _self.product : product // ignore: cast_nullable_to_non_nullable
as ReviewProductDto,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as int,status: null == status ? _self.status : status // ignore: cast_nullable_to_non_nullable
as String,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,body: freezed == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

/// Create a copy of MyReviewDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$ReviewProductDtoCopyWith<$Res> get product {
  
  return $ReviewProductDtoCopyWith<$Res>(_self.product, (value) {
    return _then(_self.copyWith(product: value));
  });
}
}


/// @nodoc
mixin _$ReviewOrderRefDto {

 String get publicId; String get orderNumber;
/// Create a copy of ReviewOrderRefDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ReviewOrderRefDtoCopyWith<ReviewOrderRefDto> get copyWith => _$ReviewOrderRefDtoCopyWithImpl<ReviewOrderRefDto>(this as ReviewOrderRefDto, _$identity);

  /// Serializes this ReviewOrderRefDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as ReviewOrderRefDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ReviewOrderRefDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.orderNumber, _this.orderNumber) || other.orderNumber == _this.orderNumber));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as ReviewOrderRefDto;
  return Object.hash(runtimeType,_this.publicId,_this.orderNumber);
}

@override
String toString() {
  final _this = this as ReviewOrderRefDto;
  return 'ReviewOrderRefDto(publicId: ${_this.publicId}, orderNumber: ${_this.orderNumber})';
}


}

/// @nodoc
abstract mixin class $ReviewOrderRefDtoCopyWith<$Res>  {
  factory $ReviewOrderRefDtoCopyWith(ReviewOrderRefDto value, $Res Function(ReviewOrderRefDto) _then) = _$ReviewOrderRefDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String orderNumber
});




}
/// @nodoc
class _$ReviewOrderRefDtoCopyWithImpl<$Res>
    implements $ReviewOrderRefDtoCopyWith<$Res> {
  _$ReviewOrderRefDtoCopyWithImpl(this._self, this._then);

  final ReviewOrderRefDto _self;
  final $Res Function(ReviewOrderRefDto) _then;

/// Create a copy of ReviewOrderRefDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? orderNumber = null,}) {
  return _then(ReviewOrderRefDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,orderNumber: null == orderNumber ? _self.orderNumber : orderNumber // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [ReviewOrderRefDto].
extension ReviewOrderRefDtoPatterns on ReviewOrderRefDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ReviewOrderRefDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ReviewOrderRefDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ReviewOrderRefDto value)  $default,){
final _that = this;
switch (_that) {
case _ReviewOrderRefDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ReviewOrderRefDto value)?  $default,){
final _that = this;
switch (_that) {
case _ReviewOrderRefDto() when $default != null:
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
case _ReviewOrderRefDto() when $default != null:
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
case _ReviewOrderRefDto():
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
case _ReviewOrderRefDto() when $default != null:
return $default(_that.publicId,_that.orderNumber);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _ReviewOrderRefDto implements ReviewOrderRefDto {
  const _ReviewOrderRefDto({required this.publicId, required this.orderNumber});
  factory _ReviewOrderRefDto.fromJson(Map<String, dynamic> json) => _$ReviewOrderRefDtoFromJson(json);

@override final  String publicId;
@override final  String orderNumber;

/// Create a copy of ReviewOrderRefDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ReviewOrderRefDtoCopyWith<_ReviewOrderRefDto> get copyWith => __$ReviewOrderRefDtoCopyWithImpl<_ReviewOrderRefDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$ReviewOrderRefDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ReviewOrderRefDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.orderNumber, orderNumber) || other.orderNumber == orderNumber));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,orderNumber);
}

@override
String toString() {
    return 'ReviewOrderRefDto(publicId: $publicId, orderNumber: $orderNumber)';
}


}

/// @nodoc
abstract mixin class _$ReviewOrderRefDtoCopyWith<$Res> implements $ReviewOrderRefDtoCopyWith<$Res> {
  factory _$ReviewOrderRefDtoCopyWith(_ReviewOrderRefDto value, $Res Function(_ReviewOrderRefDto) _then) = __$ReviewOrderRefDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String orderNumber
});




}
/// @nodoc
class __$ReviewOrderRefDtoCopyWithImpl<$Res>
    implements _$ReviewOrderRefDtoCopyWith<$Res> {
  __$ReviewOrderRefDtoCopyWithImpl(this._self, this._then);

  final _ReviewOrderRefDto _self;
  final $Res Function(_ReviewOrderRefDto) _then;

/// Create a copy of ReviewOrderRefDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? orderNumber = null,}) {
  return _then(_ReviewOrderRefDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,orderNumber: null == orderNumber ? _self.orderNumber : orderNumber // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}


/// @nodoc
mixin _$ReviewableItemDto {

 int get orderItemId; ReviewOrderRefDto get order; ReviewProductDto get product;
/// Create a copy of ReviewableItemDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ReviewableItemDtoCopyWith<ReviewableItemDto> get copyWith => _$ReviewableItemDtoCopyWithImpl<ReviewableItemDto>(this as ReviewableItemDto, _$identity);

  /// Serializes this ReviewableItemDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as ReviewableItemDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ReviewableItemDto&&(identical(other.orderItemId, _this.orderItemId) || other.orderItemId == _this.orderItemId)&&(identical(other.order, _this.order) || other.order == _this.order)&&(identical(other.product, _this.product) || other.product == _this.product));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as ReviewableItemDto;
  return Object.hash(runtimeType,_this.orderItemId,_this.order,_this.product);
}

@override
String toString() {
  final _this = this as ReviewableItemDto;
  return 'ReviewableItemDto(orderItemId: ${_this.orderItemId}, order: ${_this.order}, product: ${_this.product})';
}


}

/// @nodoc
abstract mixin class $ReviewableItemDtoCopyWith<$Res>  {
  factory $ReviewableItemDtoCopyWith(ReviewableItemDto value, $Res Function(ReviewableItemDto) _then) = _$ReviewableItemDtoCopyWithImpl;
@useResult
$Res call({
 int orderItemId, ReviewOrderRefDto order, ReviewProductDto product
});


$ReviewOrderRefDtoCopyWith<$Res> get order;$ReviewProductDtoCopyWith<$Res> get product;

}
/// @nodoc
class _$ReviewableItemDtoCopyWithImpl<$Res>
    implements $ReviewableItemDtoCopyWith<$Res> {
  _$ReviewableItemDtoCopyWithImpl(this._self, this._then);

  final ReviewableItemDto _self;
  final $Res Function(ReviewableItemDto) _then;

/// Create a copy of ReviewableItemDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? orderItemId = null,Object? order = null,Object? product = null,}) {
  return _then(ReviewableItemDto(
orderItemId: null == orderItemId ? _self.orderItemId : orderItemId // ignore: cast_nullable_to_non_nullable
as int,order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as ReviewOrderRefDto,product: null == product ? _self.product : product // ignore: cast_nullable_to_non_nullable
as ReviewProductDto,
  ));
}
/// Create a copy of ReviewableItemDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$ReviewOrderRefDtoCopyWith<$Res> get order {
  
  return $ReviewOrderRefDtoCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}/// Create a copy of ReviewableItemDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$ReviewProductDtoCopyWith<$Res> get product {
  
  return $ReviewProductDtoCopyWith<$Res>(_self.product, (value) {
    return _then(_self.copyWith(product: value));
  });
}
}


/// Adds pattern-matching-related methods to [ReviewableItemDto].
extension ReviewableItemDtoPatterns on ReviewableItemDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ReviewableItemDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ReviewableItemDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ReviewableItemDto value)  $default,){
final _that = this;
switch (_that) {
case _ReviewableItemDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ReviewableItemDto value)?  $default,){
final _that = this;
switch (_that) {
case _ReviewableItemDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int orderItemId,  ReviewOrderRefDto order,  ReviewProductDto product)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ReviewableItemDto() when $default != null:
return $default(_that.orderItemId,_that.order,_that.product);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int orderItemId,  ReviewOrderRefDto order,  ReviewProductDto product)  $default,) {final _that = this;
switch (_that) {
case _ReviewableItemDto():
return $default(_that.orderItemId,_that.order,_that.product);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int orderItemId,  ReviewOrderRefDto order,  ReviewProductDto product)?  $default,) {final _that = this;
switch (_that) {
case _ReviewableItemDto() when $default != null:
return $default(_that.orderItemId,_that.order,_that.product);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _ReviewableItemDto extends ReviewableItemDto {
  const _ReviewableItemDto({required this.orderItemId, required this.order, required this.product}): super._();
  factory _ReviewableItemDto.fromJson(Map<String, dynamic> json) => _$ReviewableItemDtoFromJson(json);

@override final  int orderItemId;
@override final  ReviewOrderRefDto order;
@override final  ReviewProductDto product;

/// Create a copy of ReviewableItemDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ReviewableItemDtoCopyWith<_ReviewableItemDto> get copyWith => __$ReviewableItemDtoCopyWithImpl<_ReviewableItemDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$ReviewableItemDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ReviewableItemDto&&(identical(other.orderItemId, orderItemId) || other.orderItemId == orderItemId)&&(identical(other.order, order) || other.order == order)&&(identical(other.product, product) || other.product == product));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,orderItemId,order,product);
}

@override
String toString() {
    return 'ReviewableItemDto(orderItemId: $orderItemId, order: $order, product: $product)';
}


}

/// @nodoc
abstract mixin class _$ReviewableItemDtoCopyWith<$Res> implements $ReviewableItemDtoCopyWith<$Res> {
  factory _$ReviewableItemDtoCopyWith(_ReviewableItemDto value, $Res Function(_ReviewableItemDto) _then) = __$ReviewableItemDtoCopyWithImpl;
@override @useResult
$Res call({
 int orderItemId, ReviewOrderRefDto order, ReviewProductDto product
});


@override $ReviewOrderRefDtoCopyWith<$Res> get order;@override $ReviewProductDtoCopyWith<$Res> get product;

}
/// @nodoc
class __$ReviewableItemDtoCopyWithImpl<$Res>
    implements _$ReviewableItemDtoCopyWith<$Res> {
  __$ReviewableItemDtoCopyWithImpl(this._self, this._then);

  final _ReviewableItemDto _self;
  final $Res Function(_ReviewableItemDto) _then;

/// Create a copy of ReviewableItemDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? orderItemId = null,Object? order = null,Object? product = null,}) {
  return _then(_ReviewableItemDto(
orderItemId: null == orderItemId ? _self.orderItemId : orderItemId // ignore: cast_nullable_to_non_nullable
as int,order: null == order ? _self.order : order // ignore: cast_nullable_to_non_nullable
as ReviewOrderRefDto,product: null == product ? _self.product : product // ignore: cast_nullable_to_non_nullable
as ReviewProductDto,
  ));
}

/// Create a copy of ReviewableItemDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$ReviewOrderRefDtoCopyWith<$Res> get order {
  
  return $ReviewOrderRefDtoCopyWith<$Res>(_self.order, (value) {
    return _then(_self.copyWith(order: value));
  });
}/// Create a copy of ReviewableItemDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$ReviewProductDtoCopyWith<$Res> get product {
  
  return $ReviewProductDtoCopyWith<$Res>(_self.product, (value) {
    return _then(_self.copyWith(product: value));
  });
}
}

// dart format on
