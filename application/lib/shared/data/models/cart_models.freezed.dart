// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'cart_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$CartDto {

 String get publicId; String get currency; List<CartItemDto> get items; CartTotalsDto get totals; bool get priceMayVary; bool get checkoutReady; String? get country; CartCouponDto? get coupon;
/// Create a copy of CartDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CartDtoCopyWith<CartDto> get copyWith => _$CartDtoCopyWithImpl<CartDto>(this as CartDto, _$identity);

  /// Serializes this CartDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as CartDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CartDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.currency, _this.currency) || other.currency == _this.currency)&&const DeepCollectionEquality().equals(other.items, _this.items)&&(identical(other.totals, _this.totals) || other.totals == _this.totals)&&(identical(other.priceMayVary, _this.priceMayVary) || other.priceMayVary == _this.priceMayVary)&&(identical(other.checkoutReady, _this.checkoutReady) || other.checkoutReady == _this.checkoutReady)&&(identical(other.country, _this.country) || other.country == _this.country)&&(identical(other.coupon, _this.coupon) || other.coupon == _this.coupon));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as CartDto;
  return Object.hash(runtimeType,_this.publicId,_this.currency,const DeepCollectionEquality().hash(_this.items),_this.totals,_this.priceMayVary,_this.checkoutReady,_this.country,_this.coupon);
}

@override
String toString() {
  final _this = this as CartDto;
  return 'CartDto(publicId: ${_this.publicId}, currency: ${_this.currency}, items: ${_this.items}, totals: ${_this.totals}, priceMayVary: ${_this.priceMayVary}, checkoutReady: ${_this.checkoutReady}, country: ${_this.country}, coupon: ${_this.coupon})';
}


}

/// @nodoc
abstract mixin class $CartDtoCopyWith<$Res>  {
  factory $CartDtoCopyWith(CartDto value, $Res Function(CartDto) _then) = _$CartDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String currency, List<CartItemDto> items, CartTotalsDto totals, bool priceMayVary, bool checkoutReady, String? country, CartCouponDto? coupon
});


$CartTotalsDtoCopyWith<$Res> get totals;$CartCouponDtoCopyWith<$Res>? get coupon;

}
/// @nodoc
class _$CartDtoCopyWithImpl<$Res>
    implements $CartDtoCopyWith<$Res> {
  _$CartDtoCopyWithImpl(this._self, this._then);

  final CartDto _self;
  final $Res Function(CartDto) _then;

/// Create a copy of CartDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? currency = null,Object? items = null,Object? totals = null,Object? priceMayVary = null,Object? checkoutReady = null,Object? country = freezed,Object? coupon = freezed,}) {
  return _then(CartDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,currency: null == currency ? _self.currency : currency // ignore: cast_nullable_to_non_nullable
as String,items: null == items ? _self.items : items // ignore: cast_nullable_to_non_nullable
as List<CartItemDto>,totals: null == totals ? _self.totals : totals // ignore: cast_nullable_to_non_nullable
as CartTotalsDto,priceMayVary: null == priceMayVary ? _self.priceMayVary : priceMayVary // ignore: cast_nullable_to_non_nullable
as bool,checkoutReady: null == checkoutReady ? _self.checkoutReady : checkoutReady // ignore: cast_nullable_to_non_nullable
as bool,country: freezed == country ? _self.country : country // ignore: cast_nullable_to_non_nullable
as String?,coupon: freezed == coupon ? _self.coupon : coupon // ignore: cast_nullable_to_non_nullable
as CartCouponDto?,
  ));
}
/// Create a copy of CartDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CartTotalsDtoCopyWith<$Res> get totals {
  
  return $CartTotalsDtoCopyWith<$Res>(_self.totals, (value) {
    return _then(_self.copyWith(totals: value));
  });
}/// Create a copy of CartDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CartCouponDtoCopyWith<$Res>? get coupon {
    if (_self.coupon == null) {
    return null;
  }

  return $CartCouponDtoCopyWith<$Res>(_self.coupon!, (value) {
    return _then(_self.copyWith(coupon: value));
  });
}
}


/// Adds pattern-matching-related methods to [CartDto].
extension CartDtoPatterns on CartDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CartDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CartDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CartDto value)  $default,){
final _that = this;
switch (_that) {
case _CartDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CartDto value)?  $default,){
final _that = this;
switch (_that) {
case _CartDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String currency,  List<CartItemDto> items,  CartTotalsDto totals,  bool priceMayVary,  bool checkoutReady,  String? country,  CartCouponDto? coupon)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CartDto() when $default != null:
return $default(_that.publicId,_that.currency,_that.items,_that.totals,_that.priceMayVary,_that.checkoutReady,_that.country,_that.coupon);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String currency,  List<CartItemDto> items,  CartTotalsDto totals,  bool priceMayVary,  bool checkoutReady,  String? country,  CartCouponDto? coupon)  $default,) {final _that = this;
switch (_that) {
case _CartDto():
return $default(_that.publicId,_that.currency,_that.items,_that.totals,_that.priceMayVary,_that.checkoutReady,_that.country,_that.coupon);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String currency,  List<CartItemDto> items,  CartTotalsDto totals,  bool priceMayVary,  bool checkoutReady,  String? country,  CartCouponDto? coupon)?  $default,) {final _that = this;
switch (_that) {
case _CartDto() when $default != null:
return $default(_that.publicId,_that.currency,_that.items,_that.totals,_that.priceMayVary,_that.checkoutReady,_that.country,_that.coupon);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CartDto extends CartDto {
  const _CartDto({required this.publicId, required this.currency, required  List<CartItemDto> items, required this.totals, required this.priceMayVary, required this.checkoutReady, this.country, this.coupon}): _items = items,super._();
  factory _CartDto.fromJson(Map<String, dynamic> json) => _$CartDtoFromJson(json);

@override final  String publicId;
@override final  String currency;
 final  List<CartItemDto> _items;
@override List<CartItemDto> get items {
  if (_items is EqualUnmodifiableListView) return _items;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_items);
}

@override final  CartTotalsDto totals;
@override final  bool priceMayVary;
@override final  bool checkoutReady;
@override final  String? country;
@override final  CartCouponDto? coupon;

/// Create a copy of CartDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CartDtoCopyWith<_CartDto> get copyWith => __$CartDtoCopyWithImpl<_CartDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CartDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CartDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.currency, currency) || other.currency == currency)&&const DeepCollectionEquality().equals(other.items, _items)&&(identical(other.totals, totals) || other.totals == totals)&&(identical(other.priceMayVary, priceMayVary) || other.priceMayVary == priceMayVary)&&(identical(other.checkoutReady, checkoutReady) || other.checkoutReady == checkoutReady)&&(identical(other.country, country) || other.country == country)&&(identical(other.coupon, coupon) || other.coupon == coupon));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,currency,const DeepCollectionEquality().hash(_items),totals,priceMayVary,checkoutReady,country,coupon);
}

@override
String toString() {
    return 'CartDto(publicId: $publicId, currency: $currency, items: $items, totals: $totals, priceMayVary: $priceMayVary, checkoutReady: $checkoutReady, country: $country, coupon: $coupon)';
}


}

/// @nodoc
abstract mixin class _$CartDtoCopyWith<$Res> implements $CartDtoCopyWith<$Res> {
  factory _$CartDtoCopyWith(_CartDto value, $Res Function(_CartDto) _then) = __$CartDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String currency, List<CartItemDto> items, CartTotalsDto totals, bool priceMayVary, bool checkoutReady, String? country, CartCouponDto? coupon
});


@override $CartTotalsDtoCopyWith<$Res> get totals;@override $CartCouponDtoCopyWith<$Res>? get coupon;

}
/// @nodoc
class __$CartDtoCopyWithImpl<$Res>
    implements _$CartDtoCopyWith<$Res> {
  __$CartDtoCopyWithImpl(this._self, this._then);

  final _CartDto _self;
  final $Res Function(_CartDto) _then;

/// Create a copy of CartDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? currency = null,Object? items = null,Object? totals = null,Object? priceMayVary = null,Object? checkoutReady = null,Object? country = freezed,Object? coupon = freezed,}) {
  return _then(_CartDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,currency: null == currency ? _self.currency : currency // ignore: cast_nullable_to_non_nullable
as String,items: null == items ? _self._items : items // ignore: cast_nullable_to_non_nullable
as List<CartItemDto>,totals: null == totals ? _self.totals : totals // ignore: cast_nullable_to_non_nullable
as CartTotalsDto,priceMayVary: null == priceMayVary ? _self.priceMayVary : priceMayVary // ignore: cast_nullable_to_non_nullable
as bool,checkoutReady: null == checkoutReady ? _self.checkoutReady : checkoutReady // ignore: cast_nullable_to_non_nullable
as bool,country: freezed == country ? _self.country : country // ignore: cast_nullable_to_non_nullable
as String?,coupon: freezed == coupon ? _self.coupon : coupon // ignore: cast_nullable_to_non_nullable
as CartCouponDto?,
  ));
}

/// Create a copy of CartDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CartTotalsDtoCopyWith<$Res> get totals {
  
  return $CartTotalsDtoCopyWith<$Res>(_self.totals, (value) {
    return _then(_self.copyWith(totals: value));
  });
}/// Create a copy of CartDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CartCouponDtoCopyWith<$Res>? get coupon {
    if (_self.coupon == null) {
    return null;
  }

  return $CartCouponDtoCopyWith<$Res>(_self.coupon!, (value) {
    return _then(_self.copyWith(coupon: value));
  });
}
}


/// @nodoc
mixin _$CartVariantDto {

 String get publicId; String get title; String? get sku;
/// Create a copy of CartVariantDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CartVariantDtoCopyWith<CartVariantDto> get copyWith => _$CartVariantDtoCopyWithImpl<CartVariantDto>(this as CartVariantDto, _$identity);

  /// Serializes this CartVariantDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as CartVariantDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CartVariantDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.sku, _this.sku) || other.sku == _this.sku));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as CartVariantDto;
  return Object.hash(runtimeType,_this.publicId,_this.title,_this.sku);
}

@override
String toString() {
  final _this = this as CartVariantDto;
  return 'CartVariantDto(publicId: ${_this.publicId}, title: ${_this.title}, sku: ${_this.sku})';
}


}

/// @nodoc
abstract mixin class $CartVariantDtoCopyWith<$Res>  {
  factory $CartVariantDtoCopyWith(CartVariantDto value, $Res Function(CartVariantDto) _then) = _$CartVariantDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String title, String? sku
});




}
/// @nodoc
class _$CartVariantDtoCopyWithImpl<$Res>
    implements $CartVariantDtoCopyWith<$Res> {
  _$CartVariantDtoCopyWithImpl(this._self, this._then);

  final CartVariantDto _self;
  final $Res Function(CartVariantDto) _then;

/// Create a copy of CartVariantDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? title = null,Object? sku = freezed,}) {
  return _then(CartVariantDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,sku: freezed == sku ? _self.sku : sku // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [CartVariantDto].
extension CartVariantDtoPatterns on CartVariantDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CartVariantDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CartVariantDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CartVariantDto value)  $default,){
final _that = this;
switch (_that) {
case _CartVariantDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CartVariantDto value)?  $default,){
final _that = this;
switch (_that) {
case _CartVariantDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String title,  String? sku)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CartVariantDto() when $default != null:
return $default(_that.publicId,_that.title,_that.sku);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String title,  String? sku)  $default,) {final _that = this;
switch (_that) {
case _CartVariantDto():
return $default(_that.publicId,_that.title,_that.sku);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String title,  String? sku)?  $default,) {final _that = this;
switch (_that) {
case _CartVariantDto() when $default != null:
return $default(_that.publicId,_that.title,_that.sku);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CartVariantDto implements CartVariantDto {
  const _CartVariantDto({required this.publicId, required this.title, this.sku});
  factory _CartVariantDto.fromJson(Map<String, dynamic> json) => _$CartVariantDtoFromJson(json);

@override final  String publicId;
@override final  String title;
@override final  String? sku;

/// Create a copy of CartVariantDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CartVariantDtoCopyWith<_CartVariantDto> get copyWith => __$CartVariantDtoCopyWithImpl<_CartVariantDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CartVariantDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CartVariantDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.title, title) || other.title == title)&&(identical(other.sku, sku) || other.sku == sku));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,title,sku);
}

@override
String toString() {
    return 'CartVariantDto(publicId: $publicId, title: $title, sku: $sku)';
}


}

/// @nodoc
abstract mixin class _$CartVariantDtoCopyWith<$Res> implements $CartVariantDtoCopyWith<$Res> {
  factory _$CartVariantDtoCopyWith(_CartVariantDto value, $Res Function(_CartVariantDto) _then) = __$CartVariantDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String title, String? sku
});




}
/// @nodoc
class __$CartVariantDtoCopyWithImpl<$Res>
    implements _$CartVariantDtoCopyWith<$Res> {
  __$CartVariantDtoCopyWithImpl(this._self, this._then);

  final _CartVariantDto _self;
  final $Res Function(_CartVariantDto) _then;

/// Create a copy of CartVariantDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? title = null,Object? sku = freezed,}) {
  return _then(_CartVariantDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,sku: freezed == sku ? _self.sku : sku // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$CartProductDto {

 String get name; String get slug; String? get deliveryType;
/// Create a copy of CartProductDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CartProductDtoCopyWith<CartProductDto> get copyWith => _$CartProductDtoCopyWithImpl<CartProductDto>(this as CartProductDto, _$identity);

  /// Serializes this CartProductDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as CartProductDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CartProductDto&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug)&&(identical(other.deliveryType, _this.deliveryType) || other.deliveryType == _this.deliveryType));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as CartProductDto;
  return Object.hash(runtimeType,_this.name,_this.slug,_this.deliveryType);
}

@override
String toString() {
  final _this = this as CartProductDto;
  return 'CartProductDto(name: ${_this.name}, slug: ${_this.slug}, deliveryType: ${_this.deliveryType})';
}


}

/// @nodoc
abstract mixin class $CartProductDtoCopyWith<$Res>  {
  factory $CartProductDtoCopyWith(CartProductDto value, $Res Function(CartProductDto) _then) = _$CartProductDtoCopyWithImpl;
@useResult
$Res call({
 String name, String slug, String? deliveryType
});




}
/// @nodoc
class _$CartProductDtoCopyWithImpl<$Res>
    implements $CartProductDtoCopyWith<$Res> {
  _$CartProductDtoCopyWithImpl(this._self, this._then);

  final CartProductDto _self;
  final $Res Function(CartProductDto) _then;

/// Create a copy of CartProductDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? name = null,Object? slug = null,Object? deliveryType = freezed,}) {
  return _then(CartProductDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,deliveryType: freezed == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [CartProductDto].
extension CartProductDtoPatterns on CartProductDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CartProductDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CartProductDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CartProductDto value)  $default,){
final _that = this;
switch (_that) {
case _CartProductDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CartProductDto value)?  $default,){
final _that = this;
switch (_that) {
case _CartProductDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String name,  String slug,  String? deliveryType)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CartProductDto() when $default != null:
return $default(_that.name,_that.slug,_that.deliveryType);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String name,  String slug,  String? deliveryType)  $default,) {final _that = this;
switch (_that) {
case _CartProductDto():
return $default(_that.name,_that.slug,_that.deliveryType);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String name,  String slug,  String? deliveryType)?  $default,) {final _that = this;
switch (_that) {
case _CartProductDto() when $default != null:
return $default(_that.name,_that.slug,_that.deliveryType);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CartProductDto implements CartProductDto {
  const _CartProductDto({required this.name, required this.slug, this.deliveryType});
  factory _CartProductDto.fromJson(Map<String, dynamic> json) => _$CartProductDtoFromJson(json);

@override final  String name;
@override final  String slug;
@override final  String? deliveryType;

/// Create a copy of CartProductDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CartProductDtoCopyWith<_CartProductDto> get copyWith => __$CartProductDtoCopyWithImpl<_CartProductDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CartProductDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CartProductDto&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug)&&(identical(other.deliveryType, deliveryType) || other.deliveryType == deliveryType));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,name,slug,deliveryType);
}

@override
String toString() {
    return 'CartProductDto(name: $name, slug: $slug, deliveryType: $deliveryType)';
}


}

/// @nodoc
abstract mixin class _$CartProductDtoCopyWith<$Res> implements $CartProductDtoCopyWith<$Res> {
  factory _$CartProductDtoCopyWith(_CartProductDto value, $Res Function(_CartProductDto) _then) = __$CartProductDtoCopyWithImpl;
@override @useResult
$Res call({
 String name, String slug, String? deliveryType
});




}
/// @nodoc
class __$CartProductDtoCopyWithImpl<$Res>
    implements _$CartProductDtoCopyWith<$Res> {
  __$CartProductDtoCopyWithImpl(this._self, this._then);

  final _CartProductDto _self;
  final $Res Function(_CartProductDto) _then;

/// Create a copy of CartProductDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? name = null,Object? slug = null,Object? deliveryType = freezed,}) {
  return _then(_CartProductDto(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,deliveryType: freezed == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$CartItemDto {

 int get id; CartVariantDto get variant; CartProductDto get product; int get quantity;@JsonKey(fromJson: deliveryValuesFromJson) Map<String, String> get deliveryValues; bool get purchasable; String? get blockReason;@NullableMoneyConverter() Money? get unitPrice;@NullableMoneyConverter() Money? get subtotal;@NullableMoneyConverter() Money? get total;
/// Create a copy of CartItemDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CartItemDtoCopyWith<CartItemDto> get copyWith => _$CartItemDtoCopyWithImpl<CartItemDto>(this as CartItemDto, _$identity);

  /// Serializes this CartItemDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as CartItemDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CartItemDto&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.variant, _this.variant) || other.variant == _this.variant)&&(identical(other.product, _this.product) || other.product == _this.product)&&(identical(other.quantity, _this.quantity) || other.quantity == _this.quantity)&&const DeepCollectionEquality().equals(other.deliveryValues, _this.deliveryValues)&&(identical(other.purchasable, _this.purchasable) || other.purchasable == _this.purchasable)&&(identical(other.blockReason, _this.blockReason) || other.blockReason == _this.blockReason)&&(identical(other.unitPrice, _this.unitPrice) || other.unitPrice == _this.unitPrice)&&(identical(other.subtotal, _this.subtotal) || other.subtotal == _this.subtotal)&&(identical(other.total, _this.total) || other.total == _this.total));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as CartItemDto;
  return Object.hash(runtimeType,_this.id,_this.variant,_this.product,_this.quantity,const DeepCollectionEquality().hash(_this.deliveryValues),_this.purchasable,_this.blockReason,_this.unitPrice,_this.subtotal,_this.total);
}

@override
String toString() {
  final _this = this as CartItemDto;
  return 'CartItemDto(id: ${_this.id}, variant: ${_this.variant}, product: ${_this.product}, quantity: ${_this.quantity}, deliveryValues: ${_this.deliveryValues}, purchasable: ${_this.purchasable}, blockReason: ${_this.blockReason}, unitPrice: ${_this.unitPrice}, subtotal: ${_this.subtotal}, total: ${_this.total})';
}


}

/// @nodoc
abstract mixin class $CartItemDtoCopyWith<$Res>  {
  factory $CartItemDtoCopyWith(CartItemDto value, $Res Function(CartItemDto) _then) = _$CartItemDtoCopyWithImpl;
@useResult
$Res call({
 int id, CartVariantDto variant, CartProductDto product, int quantity,@JsonKey(fromJson: deliveryValuesFromJson) Map<String, String> deliveryValues, bool purchasable, String? blockReason,@NullableMoneyConverter() Money? unitPrice,@NullableMoneyConverter() Money? subtotal,@NullableMoneyConverter() Money? total
});


$CartVariantDtoCopyWith<$Res> get variant;$CartProductDtoCopyWith<$Res> get product;

}
/// @nodoc
class _$CartItemDtoCopyWithImpl<$Res>
    implements $CartItemDtoCopyWith<$Res> {
  _$CartItemDtoCopyWithImpl(this._self, this._then);

  final CartItemDto _self;
  final $Res Function(CartItemDto) _then;

/// Create a copy of CartItemDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? variant = null,Object? product = null,Object? quantity = null,Object? deliveryValues = null,Object? purchasable = null,Object? blockReason = freezed,Object? unitPrice = freezed,Object? subtotal = freezed,Object? total = freezed,}) {
  return _then(CartItemDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,variant: null == variant ? _self.variant : variant // ignore: cast_nullable_to_non_nullable
as CartVariantDto,product: null == product ? _self.product : product // ignore: cast_nullable_to_non_nullable
as CartProductDto,quantity: null == quantity ? _self.quantity : quantity // ignore: cast_nullable_to_non_nullable
as int,deliveryValues: null == deliveryValues ? _self.deliveryValues : deliveryValues // ignore: cast_nullable_to_non_nullable
as Map<String, String>,purchasable: null == purchasable ? _self.purchasable : purchasable // ignore: cast_nullable_to_non_nullable
as bool,blockReason: freezed == blockReason ? _self.blockReason : blockReason // ignore: cast_nullable_to_non_nullable
as String?,unitPrice: freezed == unitPrice ? _self.unitPrice : unitPrice // ignore: cast_nullable_to_non_nullable
as Money?,subtotal: freezed == subtotal ? _self.subtotal : subtotal // ignore: cast_nullable_to_non_nullable
as Money?,total: freezed == total ? _self.total : total // ignore: cast_nullable_to_non_nullable
as Money?,
  ));
}
/// Create a copy of CartItemDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CartVariantDtoCopyWith<$Res> get variant {
  
  return $CartVariantDtoCopyWith<$Res>(_self.variant, (value) {
    return _then(_self.copyWith(variant: value));
  });
}/// Create a copy of CartItemDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CartProductDtoCopyWith<$Res> get product {
  
  return $CartProductDtoCopyWith<$Res>(_self.product, (value) {
    return _then(_self.copyWith(product: value));
  });
}
}


/// Adds pattern-matching-related methods to [CartItemDto].
extension CartItemDtoPatterns on CartItemDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CartItemDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CartItemDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CartItemDto value)  $default,){
final _that = this;
switch (_that) {
case _CartItemDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CartItemDto value)?  $default,){
final _that = this;
switch (_that) {
case _CartItemDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  CartVariantDto variant,  CartProductDto product,  int quantity, @JsonKey(fromJson: deliveryValuesFromJson)  Map<String, String> deliveryValues,  bool purchasable,  String? blockReason, @NullableMoneyConverter()  Money? unitPrice, @NullableMoneyConverter()  Money? subtotal, @NullableMoneyConverter()  Money? total)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CartItemDto() when $default != null:
return $default(_that.id,_that.variant,_that.product,_that.quantity,_that.deliveryValues,_that.purchasable,_that.blockReason,_that.unitPrice,_that.subtotal,_that.total);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  CartVariantDto variant,  CartProductDto product,  int quantity, @JsonKey(fromJson: deliveryValuesFromJson)  Map<String, String> deliveryValues,  bool purchasable,  String? blockReason, @NullableMoneyConverter()  Money? unitPrice, @NullableMoneyConverter()  Money? subtotal, @NullableMoneyConverter()  Money? total)  $default,) {final _that = this;
switch (_that) {
case _CartItemDto():
return $default(_that.id,_that.variant,_that.product,_that.quantity,_that.deliveryValues,_that.purchasable,_that.blockReason,_that.unitPrice,_that.subtotal,_that.total);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  CartVariantDto variant,  CartProductDto product,  int quantity, @JsonKey(fromJson: deliveryValuesFromJson)  Map<String, String> deliveryValues,  bool purchasable,  String? blockReason, @NullableMoneyConverter()  Money? unitPrice, @NullableMoneyConverter()  Money? subtotal, @NullableMoneyConverter()  Money? total)?  $default,) {final _that = this;
switch (_that) {
case _CartItemDto() when $default != null:
return $default(_that.id,_that.variant,_that.product,_that.quantity,_that.deliveryValues,_that.purchasable,_that.blockReason,_that.unitPrice,_that.subtotal,_that.total);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CartItemDto extends CartItemDto {
  const _CartItemDto({required this.id, required this.variant, required this.product, required this.quantity, @JsonKey(fromJson: deliveryValuesFromJson) required  Map<String, String> deliveryValues, required this.purchasable, this.blockReason, @NullableMoneyConverter() this.unitPrice, @NullableMoneyConverter() this.subtotal, @NullableMoneyConverter() this.total}): _deliveryValues = deliveryValues,super._();
  factory _CartItemDto.fromJson(Map<String, dynamic> json) => _$CartItemDtoFromJson(json);

@override final  int id;
@override final  CartVariantDto variant;
@override final  CartProductDto product;
@override final  int quantity;
 final  Map<String, String> _deliveryValues;
@override@JsonKey(fromJson: deliveryValuesFromJson) Map<String, String> get deliveryValues {
  if (_deliveryValues is EqualUnmodifiableMapView) return _deliveryValues;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(_deliveryValues);
}

@override final  bool purchasable;
@override final  String? blockReason;
@override@NullableMoneyConverter() final  Money? unitPrice;
@override@NullableMoneyConverter() final  Money? subtotal;
@override@NullableMoneyConverter() final  Money? total;

/// Create a copy of CartItemDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CartItemDtoCopyWith<_CartItemDto> get copyWith => __$CartItemDtoCopyWithImpl<_CartItemDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CartItemDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CartItemDto&&(identical(other.id, id) || other.id == id)&&(identical(other.variant, variant) || other.variant == variant)&&(identical(other.product, product) || other.product == product)&&(identical(other.quantity, quantity) || other.quantity == quantity)&&const DeepCollectionEquality().equals(other.deliveryValues, _deliveryValues)&&(identical(other.purchasable, purchasable) || other.purchasable == purchasable)&&(identical(other.blockReason, blockReason) || other.blockReason == blockReason)&&(identical(other.unitPrice, unitPrice) || other.unitPrice == unitPrice)&&(identical(other.subtotal, subtotal) || other.subtotal == subtotal)&&(identical(other.total, total) || other.total == total));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,id,variant,product,quantity,const DeepCollectionEquality().hash(_deliveryValues),purchasable,blockReason,unitPrice,subtotal,total);
}

@override
String toString() {
    return 'CartItemDto(id: $id, variant: $variant, product: $product, quantity: $quantity, deliveryValues: $deliveryValues, purchasable: $purchasable, blockReason: $blockReason, unitPrice: $unitPrice, subtotal: $subtotal, total: $total)';
}


}

/// @nodoc
abstract mixin class _$CartItemDtoCopyWith<$Res> implements $CartItemDtoCopyWith<$Res> {
  factory _$CartItemDtoCopyWith(_CartItemDto value, $Res Function(_CartItemDto) _then) = __$CartItemDtoCopyWithImpl;
@override @useResult
$Res call({
 int id, CartVariantDto variant, CartProductDto product, int quantity,@JsonKey(fromJson: deliveryValuesFromJson) Map<String, String> deliveryValues, bool purchasable, String? blockReason,@NullableMoneyConverter() Money? unitPrice,@NullableMoneyConverter() Money? subtotal,@NullableMoneyConverter() Money? total
});


@override $CartVariantDtoCopyWith<$Res> get variant;@override $CartProductDtoCopyWith<$Res> get product;

}
/// @nodoc
class __$CartItemDtoCopyWithImpl<$Res>
    implements _$CartItemDtoCopyWith<$Res> {
  __$CartItemDtoCopyWithImpl(this._self, this._then);

  final _CartItemDto _self;
  final $Res Function(_CartItemDto) _then;

/// Create a copy of CartItemDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? variant = null,Object? product = null,Object? quantity = null,Object? deliveryValues = null,Object? purchasable = null,Object? blockReason = freezed,Object? unitPrice = freezed,Object? subtotal = freezed,Object? total = freezed,}) {
  return _then(_CartItemDto(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,variant: null == variant ? _self.variant : variant // ignore: cast_nullable_to_non_nullable
as CartVariantDto,product: null == product ? _self.product : product // ignore: cast_nullable_to_non_nullable
as CartProductDto,quantity: null == quantity ? _self.quantity : quantity // ignore: cast_nullable_to_non_nullable
as int,deliveryValues: null == deliveryValues ? _self._deliveryValues : deliveryValues // ignore: cast_nullable_to_non_nullable
as Map<String, String>,purchasable: null == purchasable ? _self.purchasable : purchasable // ignore: cast_nullable_to_non_nullable
as bool,blockReason: freezed == blockReason ? _self.blockReason : blockReason // ignore: cast_nullable_to_non_nullable
as String?,unitPrice: freezed == unitPrice ? _self.unitPrice : unitPrice // ignore: cast_nullable_to_non_nullable
as Money?,subtotal: freezed == subtotal ? _self.subtotal : subtotal // ignore: cast_nullable_to_non_nullable
as Money?,total: freezed == total ? _self.total : total // ignore: cast_nullable_to_non_nullable
as Money?,
  ));
}

/// Create a copy of CartItemDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CartVariantDtoCopyWith<$Res> get variant {
  
  return $CartVariantDtoCopyWith<$Res>(_self.variant, (value) {
    return _then(_self.copyWith(variant: value));
  });
}/// Create a copy of CartItemDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CartProductDtoCopyWith<$Res> get product {
  
  return $CartProductDtoCopyWith<$Res>(_self.product, (value) {
    return _then(_self.copyWith(product: value));
  });
}
}


/// @nodoc
mixin _$CartCouponDto {

 String get code; bool get applied; bool get limitedByMargin; String? get error;
/// Create a copy of CartCouponDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CartCouponDtoCopyWith<CartCouponDto> get copyWith => _$CartCouponDtoCopyWithImpl<CartCouponDto>(this as CartCouponDto, _$identity);

  /// Serializes this CartCouponDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as CartCouponDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CartCouponDto&&(identical(other.code, _this.code) || other.code == _this.code)&&(identical(other.applied, _this.applied) || other.applied == _this.applied)&&(identical(other.limitedByMargin, _this.limitedByMargin) || other.limitedByMargin == _this.limitedByMargin)&&(identical(other.error, _this.error) || other.error == _this.error));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as CartCouponDto;
  return Object.hash(runtimeType,_this.code,_this.applied,_this.limitedByMargin,_this.error);
}

@override
String toString() {
  final _this = this as CartCouponDto;
  return 'CartCouponDto(code: ${_this.code}, applied: ${_this.applied}, limitedByMargin: ${_this.limitedByMargin}, error: ${_this.error})';
}


}

/// @nodoc
abstract mixin class $CartCouponDtoCopyWith<$Res>  {
  factory $CartCouponDtoCopyWith(CartCouponDto value, $Res Function(CartCouponDto) _then) = _$CartCouponDtoCopyWithImpl;
@useResult
$Res call({
 String code, bool applied, bool limitedByMargin, String? error
});




}
/// @nodoc
class _$CartCouponDtoCopyWithImpl<$Res>
    implements $CartCouponDtoCopyWith<$Res> {
  _$CartCouponDtoCopyWithImpl(this._self, this._then);

  final CartCouponDto _self;
  final $Res Function(CartCouponDto) _then;

/// Create a copy of CartCouponDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? code = null,Object? applied = null,Object? limitedByMargin = null,Object? error = freezed,}) {
  return _then(CartCouponDto(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,applied: null == applied ? _self.applied : applied // ignore: cast_nullable_to_non_nullable
as bool,limitedByMargin: null == limitedByMargin ? _self.limitedByMargin : limitedByMargin // ignore: cast_nullable_to_non_nullable
as bool,error: freezed == error ? _self.error : error // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [CartCouponDto].
extension CartCouponDtoPatterns on CartCouponDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CartCouponDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CartCouponDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CartCouponDto value)  $default,){
final _that = this;
switch (_that) {
case _CartCouponDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CartCouponDto value)?  $default,){
final _that = this;
switch (_that) {
case _CartCouponDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String code,  bool applied,  bool limitedByMargin,  String? error)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CartCouponDto() when $default != null:
return $default(_that.code,_that.applied,_that.limitedByMargin,_that.error);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String code,  bool applied,  bool limitedByMargin,  String? error)  $default,) {final _that = this;
switch (_that) {
case _CartCouponDto():
return $default(_that.code,_that.applied,_that.limitedByMargin,_that.error);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String code,  bool applied,  bool limitedByMargin,  String? error)?  $default,) {final _that = this;
switch (_that) {
case _CartCouponDto() when $default != null:
return $default(_that.code,_that.applied,_that.limitedByMargin,_that.error);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CartCouponDto implements CartCouponDto {
  const _CartCouponDto({required this.code, required this.applied, required this.limitedByMargin, this.error});
  factory _CartCouponDto.fromJson(Map<String, dynamic> json) => _$CartCouponDtoFromJson(json);

@override final  String code;
@override final  bool applied;
@override final  bool limitedByMargin;
@override final  String? error;

/// Create a copy of CartCouponDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CartCouponDtoCopyWith<_CartCouponDto> get copyWith => __$CartCouponDtoCopyWithImpl<_CartCouponDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CartCouponDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CartCouponDto&&(identical(other.code, code) || other.code == code)&&(identical(other.applied, applied) || other.applied == applied)&&(identical(other.limitedByMargin, limitedByMargin) || other.limitedByMargin == limitedByMargin)&&(identical(other.error, error) || other.error == error));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,code,applied,limitedByMargin,error);
}

@override
String toString() {
    return 'CartCouponDto(code: $code, applied: $applied, limitedByMargin: $limitedByMargin, error: $error)';
}


}

/// @nodoc
abstract mixin class _$CartCouponDtoCopyWith<$Res> implements $CartCouponDtoCopyWith<$Res> {
  factory _$CartCouponDtoCopyWith(_CartCouponDto value, $Res Function(_CartCouponDto) _then) = __$CartCouponDtoCopyWithImpl;
@override @useResult
$Res call({
 String code, bool applied, bool limitedByMargin, String? error
});




}
/// @nodoc
class __$CartCouponDtoCopyWithImpl<$Res>
    implements _$CartCouponDtoCopyWith<$Res> {
  __$CartCouponDtoCopyWithImpl(this._self, this._then);

  final _CartCouponDto _self;
  final $Res Function(_CartCouponDto) _then;

/// Create a copy of CartCouponDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? code = null,Object? applied = null,Object? limitedByMargin = null,Object? error = freezed,}) {
  return _then(_CartCouponDto(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,applied: null == applied ? _self.applied : applied // ignore: cast_nullable_to_non_nullable
as bool,limitedByMargin: null == limitedByMargin ? _self.limitedByMargin : limitedByMargin // ignore: cast_nullable_to_non_nullable
as bool,error: freezed == error ? _self.error : error // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$CartTotalsDto {

@MoneyConverter() Money get subtotal;@MoneyConverter() Money get discount;@MoneyConverter() Money get total;
/// Create a copy of CartTotalsDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CartTotalsDtoCopyWith<CartTotalsDto> get copyWith => _$CartTotalsDtoCopyWithImpl<CartTotalsDto>(this as CartTotalsDto, _$identity);

  /// Serializes this CartTotalsDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as CartTotalsDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CartTotalsDto&&(identical(other.subtotal, _this.subtotal) || other.subtotal == _this.subtotal)&&(identical(other.discount, _this.discount) || other.discount == _this.discount)&&(identical(other.total, _this.total) || other.total == _this.total));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as CartTotalsDto;
  return Object.hash(runtimeType,_this.subtotal,_this.discount,_this.total);
}

@override
String toString() {
  final _this = this as CartTotalsDto;
  return 'CartTotalsDto(subtotal: ${_this.subtotal}, discount: ${_this.discount}, total: ${_this.total})';
}


}

/// @nodoc
abstract mixin class $CartTotalsDtoCopyWith<$Res>  {
  factory $CartTotalsDtoCopyWith(CartTotalsDto value, $Res Function(CartTotalsDto) _then) = _$CartTotalsDtoCopyWithImpl;
@useResult
$Res call({
@MoneyConverter() Money subtotal,@MoneyConverter() Money discount,@MoneyConverter() Money total
});




}
/// @nodoc
class _$CartTotalsDtoCopyWithImpl<$Res>
    implements $CartTotalsDtoCopyWith<$Res> {
  _$CartTotalsDtoCopyWithImpl(this._self, this._then);

  final CartTotalsDto _self;
  final $Res Function(CartTotalsDto) _then;

/// Create a copy of CartTotalsDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? subtotal = null,Object? discount = null,Object? total = null,}) {
  return _then(CartTotalsDto(
subtotal: null == subtotal ? _self.subtotal : subtotal // ignore: cast_nullable_to_non_nullable
as Money,discount: null == discount ? _self.discount : discount // ignore: cast_nullable_to_non_nullable
as Money,total: null == total ? _self.total : total // ignore: cast_nullable_to_non_nullable
as Money,
  ));
}

}


/// Adds pattern-matching-related methods to [CartTotalsDto].
extension CartTotalsDtoPatterns on CartTotalsDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CartTotalsDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CartTotalsDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CartTotalsDto value)  $default,){
final _that = this;
switch (_that) {
case _CartTotalsDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CartTotalsDto value)?  $default,){
final _that = this;
switch (_that) {
case _CartTotalsDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function(@MoneyConverter()  Money subtotal, @MoneyConverter()  Money discount, @MoneyConverter()  Money total)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CartTotalsDto() when $default != null:
return $default(_that.subtotal,_that.discount,_that.total);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function(@MoneyConverter()  Money subtotal, @MoneyConverter()  Money discount, @MoneyConverter()  Money total)  $default,) {final _that = this;
switch (_that) {
case _CartTotalsDto():
return $default(_that.subtotal,_that.discount,_that.total);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function(@MoneyConverter()  Money subtotal, @MoneyConverter()  Money discount, @MoneyConverter()  Money total)?  $default,) {final _that = this;
switch (_that) {
case _CartTotalsDto() when $default != null:
return $default(_that.subtotal,_that.discount,_that.total);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _CartTotalsDto implements CartTotalsDto {
  const _CartTotalsDto({@MoneyConverter() required this.subtotal, @MoneyConverter() required this.discount, @MoneyConverter() required this.total});
  factory _CartTotalsDto.fromJson(Map<String, dynamic> json) => _$CartTotalsDtoFromJson(json);

@override@MoneyConverter() final  Money subtotal;
@override@MoneyConverter() final  Money discount;
@override@MoneyConverter() final  Money total;

/// Create a copy of CartTotalsDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CartTotalsDtoCopyWith<_CartTotalsDto> get copyWith => __$CartTotalsDtoCopyWithImpl<_CartTotalsDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$CartTotalsDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CartTotalsDto&&(identical(other.subtotal, subtotal) || other.subtotal == subtotal)&&(identical(other.discount, discount) || other.discount == discount)&&(identical(other.total, total) || other.total == total));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,subtotal,discount,total);
}

@override
String toString() {
    return 'CartTotalsDto(subtotal: $subtotal, discount: $discount, total: $total)';
}


}

/// @nodoc
abstract mixin class _$CartTotalsDtoCopyWith<$Res> implements $CartTotalsDtoCopyWith<$Res> {
  factory _$CartTotalsDtoCopyWith(_CartTotalsDto value, $Res Function(_CartTotalsDto) _then) = __$CartTotalsDtoCopyWithImpl;
@override @useResult
$Res call({
@MoneyConverter() Money subtotal,@MoneyConverter() Money discount,@MoneyConverter() Money total
});




}
/// @nodoc
class __$CartTotalsDtoCopyWithImpl<$Res>
    implements _$CartTotalsDtoCopyWith<$Res> {
  __$CartTotalsDtoCopyWithImpl(this._self, this._then);

  final _CartTotalsDto _self;
  final $Res Function(_CartTotalsDto) _then;

/// Create a copy of CartTotalsDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? subtotal = null,Object? discount = null,Object? total = null,}) {
  return _then(_CartTotalsDto(
subtotal: null == subtotal ? _self.subtotal : subtotal // ignore: cast_nullable_to_non_nullable
as Money,discount: null == discount ? _self.discount : discount // ignore: cast_nullable_to_non_nullable
as Money,total: null == total ? _self.total : total // ignore: cast_nullable_to_non_nullable
as Money,
  ));
}


}

// dart format on
