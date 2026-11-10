// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'cart.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$Cart {

 String get publicId; String get currency; List<CartLine> get items; CartTotals get totals; bool get priceMayVary; bool get checkoutReady; String? get country; CartCoupon? get coupon;
/// Create a copy of Cart
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CartCopyWith<Cart> get copyWith => _$CartCopyWithImpl<Cart>(this as Cart, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as Cart;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is Cart&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.currency, _this.currency) || other.currency == _this.currency)&&const DeepCollectionEquality().equals(other.items, _this.items)&&(identical(other.totals, _this.totals) || other.totals == _this.totals)&&(identical(other.priceMayVary, _this.priceMayVary) || other.priceMayVary == _this.priceMayVary)&&(identical(other.checkoutReady, _this.checkoutReady) || other.checkoutReady == _this.checkoutReady)&&(identical(other.country, _this.country) || other.country == _this.country)&&(identical(other.coupon, _this.coupon) || other.coupon == _this.coupon));
}


@override
int get hashCode {
  final _this = this as Cart;
  return Object.hash(runtimeType,_this.publicId,_this.currency,const DeepCollectionEquality().hash(_this.items),_this.totals,_this.priceMayVary,_this.checkoutReady,_this.country,_this.coupon);
}

@override
String toString() {
  final _this = this as Cart;
  return 'Cart(publicId: ${_this.publicId}, currency: ${_this.currency}, items: ${_this.items}, totals: ${_this.totals}, priceMayVary: ${_this.priceMayVary}, checkoutReady: ${_this.checkoutReady}, country: ${_this.country}, coupon: ${_this.coupon})';
}


}

/// @nodoc
abstract mixin class $CartCopyWith<$Res>  {
  factory $CartCopyWith(Cart value, $Res Function(Cart) _then) = _$CartCopyWithImpl;
@useResult
$Res call({
 String publicId, String currency, List<CartLine> items, CartTotals totals, bool priceMayVary, bool checkoutReady, String? country, CartCoupon? coupon
});


$CartTotalsCopyWith<$Res> get totals;$CartCouponCopyWith<$Res>? get coupon;

}
/// @nodoc
class _$CartCopyWithImpl<$Res>
    implements $CartCopyWith<$Res> {
  _$CartCopyWithImpl(this._self, this._then);

  final Cart _self;
  final $Res Function(Cart) _then;

/// Create a copy of Cart
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? currency = null,Object? items = null,Object? totals = null,Object? priceMayVary = null,Object? checkoutReady = null,Object? country = freezed,Object? coupon = freezed,}) {
  return _then(Cart(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,currency: null == currency ? _self.currency : currency // ignore: cast_nullable_to_non_nullable
as String,items: null == items ? _self.items : items // ignore: cast_nullable_to_non_nullable
as List<CartLine>,totals: null == totals ? _self.totals : totals // ignore: cast_nullable_to_non_nullable
as CartTotals,priceMayVary: null == priceMayVary ? _self.priceMayVary : priceMayVary // ignore: cast_nullable_to_non_nullable
as bool,checkoutReady: null == checkoutReady ? _self.checkoutReady : checkoutReady // ignore: cast_nullable_to_non_nullable
as bool,country: freezed == country ? _self.country : country // ignore: cast_nullable_to_non_nullable
as String?,coupon: freezed == coupon ? _self.coupon : coupon // ignore: cast_nullable_to_non_nullable
as CartCoupon?,
  ));
}
/// Create a copy of Cart
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CartTotalsCopyWith<$Res> get totals {
  
  return $CartTotalsCopyWith<$Res>(_self.totals, (value) {
    return _then(_self.copyWith(totals: value));
  });
}/// Create a copy of Cart
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CartCouponCopyWith<$Res>? get coupon {
    if (_self.coupon == null) {
    return null;
  }

  return $CartCouponCopyWith<$Res>(_self.coupon!, (value) {
    return _then(_self.copyWith(coupon: value));
  });
}
}


/// Adds pattern-matching-related methods to [Cart].
extension CartPatterns on Cart {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _Cart value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _Cart() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _Cart value)  $default,){
final _that = this;
switch (_that) {
case _Cart():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _Cart value)?  $default,){
final _that = this;
switch (_that) {
case _Cart() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String currency,  List<CartLine> items,  CartTotals totals,  bool priceMayVary,  bool checkoutReady,  String? country,  CartCoupon? coupon)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _Cart() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String currency,  List<CartLine> items,  CartTotals totals,  bool priceMayVary,  bool checkoutReady,  String? country,  CartCoupon? coupon)  $default,) {final _that = this;
switch (_that) {
case _Cart():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String currency,  List<CartLine> items,  CartTotals totals,  bool priceMayVary,  bool checkoutReady,  String? country,  CartCoupon? coupon)?  $default,) {final _that = this;
switch (_that) {
case _Cart() when $default != null:
return $default(_that.publicId,_that.currency,_that.items,_that.totals,_that.priceMayVary,_that.checkoutReady,_that.country,_that.coupon);case _:
  return null;

}
}

}

/// @nodoc


class _Cart implements Cart {
  const _Cart({required this.publicId, required this.currency, required  List<CartLine> items, required this.totals, required this.priceMayVary, required this.checkoutReady, this.country, this.coupon}): _items = items;
  

@override final  String publicId;
@override final  String currency;
 final  List<CartLine> _items;
@override List<CartLine> get items {
  if (_items is EqualUnmodifiableListView) return _items;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_items);
}

@override final  CartTotals totals;
@override final  bool priceMayVary;
@override final  bool checkoutReady;
@override final  String? country;
@override final  CartCoupon? coupon;

/// Create a copy of Cart
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CartCopyWith<_Cart> get copyWith => __$CartCopyWithImpl<_Cart>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _Cart&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.currency, currency) || other.currency == currency)&&const DeepCollectionEquality().equals(other.items, _items)&&(identical(other.totals, totals) || other.totals == totals)&&(identical(other.priceMayVary, priceMayVary) || other.priceMayVary == priceMayVary)&&(identical(other.checkoutReady, checkoutReady) || other.checkoutReady == checkoutReady)&&(identical(other.country, country) || other.country == country)&&(identical(other.coupon, coupon) || other.coupon == coupon));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,currency,const DeepCollectionEquality().hash(_items),totals,priceMayVary,checkoutReady,country,coupon);
}

@override
String toString() {
    return 'Cart(publicId: $publicId, currency: $currency, items: $items, totals: $totals, priceMayVary: $priceMayVary, checkoutReady: $checkoutReady, country: $country, coupon: $coupon)';
}


}

/// @nodoc
abstract mixin class _$CartCopyWith<$Res> implements $CartCopyWith<$Res> {
  factory _$CartCopyWith(_Cart value, $Res Function(_Cart) _then) = __$CartCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String currency, List<CartLine> items, CartTotals totals, bool priceMayVary, bool checkoutReady, String? country, CartCoupon? coupon
});


@override $CartTotalsCopyWith<$Res> get totals;@override $CartCouponCopyWith<$Res>? get coupon;

}
/// @nodoc
class __$CartCopyWithImpl<$Res>
    implements _$CartCopyWith<$Res> {
  __$CartCopyWithImpl(this._self, this._then);

  final _Cart _self;
  final $Res Function(_Cart) _then;

/// Create a copy of Cart
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? currency = null,Object? items = null,Object? totals = null,Object? priceMayVary = null,Object? checkoutReady = null,Object? country = freezed,Object? coupon = freezed,}) {
  return _then(_Cart(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,currency: null == currency ? _self.currency : currency // ignore: cast_nullable_to_non_nullable
as String,items: null == items ? _self._items : items // ignore: cast_nullable_to_non_nullable
as List<CartLine>,totals: null == totals ? _self.totals : totals // ignore: cast_nullable_to_non_nullable
as CartTotals,priceMayVary: null == priceMayVary ? _self.priceMayVary : priceMayVary // ignore: cast_nullable_to_non_nullable
as bool,checkoutReady: null == checkoutReady ? _self.checkoutReady : checkoutReady // ignore: cast_nullable_to_non_nullable
as bool,country: freezed == country ? _self.country : country // ignore: cast_nullable_to_non_nullable
as String?,coupon: freezed == coupon ? _self.coupon : coupon // ignore: cast_nullable_to_non_nullable
as CartCoupon?,
  ));
}

/// Create a copy of Cart
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CartTotalsCopyWith<$Res> get totals {
  
  return $CartTotalsCopyWith<$Res>(_self.totals, (value) {
    return _then(_self.copyWith(totals: value));
  });
}/// Create a copy of Cart
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$CartCouponCopyWith<$Res>? get coupon {
    if (_self.coupon == null) {
    return null;
  }

  return $CartCouponCopyWith<$Res>(_self.coupon!, (value) {
    return _then(_self.copyWith(coupon: value));
  });
}
}

/// @nodoc
mixin _$CartLine {

 int get id; String get variantPublicId; String get variantTitle; String get productName; String get productSlug; int get quantity; Map<String, String> get deliveryValues; bool get purchasable; String? get variantSku; String? get deliveryType;/// `unavailable`, `out_of_stock`, `custom_amount_unsupported`, `quantity_limit`,
/// `not_sold_in_country` or a pricing reason; null when purchasable.
 String? get blockReason; Money? get unitPrice; Money? get subtotal; Money? get total;
/// Create a copy of CartLine
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CartLineCopyWith<CartLine> get copyWith => _$CartLineCopyWithImpl<CartLine>(this as CartLine, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as CartLine;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CartLine&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.variantPublicId, _this.variantPublicId) || other.variantPublicId == _this.variantPublicId)&&(identical(other.variantTitle, _this.variantTitle) || other.variantTitle == _this.variantTitle)&&(identical(other.productName, _this.productName) || other.productName == _this.productName)&&(identical(other.productSlug, _this.productSlug) || other.productSlug == _this.productSlug)&&(identical(other.quantity, _this.quantity) || other.quantity == _this.quantity)&&const DeepCollectionEquality().equals(other.deliveryValues, _this.deliveryValues)&&(identical(other.purchasable, _this.purchasable) || other.purchasable == _this.purchasable)&&(identical(other.variantSku, _this.variantSku) || other.variantSku == _this.variantSku)&&(identical(other.deliveryType, _this.deliveryType) || other.deliveryType == _this.deliveryType)&&(identical(other.blockReason, _this.blockReason) || other.blockReason == _this.blockReason)&&(identical(other.unitPrice, _this.unitPrice) || other.unitPrice == _this.unitPrice)&&(identical(other.subtotal, _this.subtotal) || other.subtotal == _this.subtotal)&&(identical(other.total, _this.total) || other.total == _this.total));
}


@override
int get hashCode {
  final _this = this as CartLine;
  return Object.hash(runtimeType,_this.id,_this.variantPublicId,_this.variantTitle,_this.productName,_this.productSlug,_this.quantity,const DeepCollectionEquality().hash(_this.deliveryValues),_this.purchasable,_this.variantSku,_this.deliveryType,_this.blockReason,_this.unitPrice,_this.subtotal,_this.total);
}

@override
String toString() {
  final _this = this as CartLine;
  return 'CartLine(id: ${_this.id}, variantPublicId: ${_this.variantPublicId}, variantTitle: ${_this.variantTitle}, productName: ${_this.productName}, productSlug: ${_this.productSlug}, quantity: ${_this.quantity}, deliveryValues: ${_this.deliveryValues}, purchasable: ${_this.purchasable}, variantSku: ${_this.variantSku}, deliveryType: ${_this.deliveryType}, blockReason: ${_this.blockReason}, unitPrice: ${_this.unitPrice}, subtotal: ${_this.subtotal}, total: ${_this.total})';
}


}

/// @nodoc
abstract mixin class $CartLineCopyWith<$Res>  {
  factory $CartLineCopyWith(CartLine value, $Res Function(CartLine) _then) = _$CartLineCopyWithImpl;
@useResult
$Res call({
 int id, String variantPublicId, String variantTitle, String productName, String productSlug, int quantity, Map<String, String> deliveryValues, bool purchasable, String? variantSku, String? deliveryType, String? blockReason, Money? unitPrice, Money? subtotal, Money? total
});




}
/// @nodoc
class _$CartLineCopyWithImpl<$Res>
    implements $CartLineCopyWith<$Res> {
  _$CartLineCopyWithImpl(this._self, this._then);

  final CartLine _self;
  final $Res Function(CartLine) _then;

/// Create a copy of CartLine
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? variantPublicId = null,Object? variantTitle = null,Object? productName = null,Object? productSlug = null,Object? quantity = null,Object? deliveryValues = null,Object? purchasable = null,Object? variantSku = freezed,Object? deliveryType = freezed,Object? blockReason = freezed,Object? unitPrice = freezed,Object? subtotal = freezed,Object? total = freezed,}) {
  return _then(CartLine(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,variantPublicId: null == variantPublicId ? _self.variantPublicId : variantPublicId // ignore: cast_nullable_to_non_nullable
as String,variantTitle: null == variantTitle ? _self.variantTitle : variantTitle // ignore: cast_nullable_to_non_nullable
as String,productName: null == productName ? _self.productName : productName // ignore: cast_nullable_to_non_nullable
as String,productSlug: null == productSlug ? _self.productSlug : productSlug // ignore: cast_nullable_to_non_nullable
as String,quantity: null == quantity ? _self.quantity : quantity // ignore: cast_nullable_to_non_nullable
as int,deliveryValues: null == deliveryValues ? _self.deliveryValues : deliveryValues // ignore: cast_nullable_to_non_nullable
as Map<String, String>,purchasable: null == purchasable ? _self.purchasable : purchasable // ignore: cast_nullable_to_non_nullable
as bool,variantSku: freezed == variantSku ? _self.variantSku : variantSku // ignore: cast_nullable_to_non_nullable
as String?,deliveryType: freezed == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String?,blockReason: freezed == blockReason ? _self.blockReason : blockReason // ignore: cast_nullable_to_non_nullable
as String?,unitPrice: freezed == unitPrice ? _self.unitPrice : unitPrice // ignore: cast_nullable_to_non_nullable
as Money?,subtotal: freezed == subtotal ? _self.subtotal : subtotal // ignore: cast_nullable_to_non_nullable
as Money?,total: freezed == total ? _self.total : total // ignore: cast_nullable_to_non_nullable
as Money?,
  ));
}

}


/// Adds pattern-matching-related methods to [CartLine].
extension CartLinePatterns on CartLine {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CartLine value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CartLine() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CartLine value)  $default,){
final _that = this;
switch (_that) {
case _CartLine():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CartLine value)?  $default,){
final _that = this;
switch (_that) {
case _CartLine() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  String variantPublicId,  String variantTitle,  String productName,  String productSlug,  int quantity,  Map<String, String> deliveryValues,  bool purchasable,  String? variantSku,  String? deliveryType,  String? blockReason,  Money? unitPrice,  Money? subtotal,  Money? total)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CartLine() when $default != null:
return $default(_that.id,_that.variantPublicId,_that.variantTitle,_that.productName,_that.productSlug,_that.quantity,_that.deliveryValues,_that.purchasable,_that.variantSku,_that.deliveryType,_that.blockReason,_that.unitPrice,_that.subtotal,_that.total);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  String variantPublicId,  String variantTitle,  String productName,  String productSlug,  int quantity,  Map<String, String> deliveryValues,  bool purchasable,  String? variantSku,  String? deliveryType,  String? blockReason,  Money? unitPrice,  Money? subtotal,  Money? total)  $default,) {final _that = this;
switch (_that) {
case _CartLine():
return $default(_that.id,_that.variantPublicId,_that.variantTitle,_that.productName,_that.productSlug,_that.quantity,_that.deliveryValues,_that.purchasable,_that.variantSku,_that.deliveryType,_that.blockReason,_that.unitPrice,_that.subtotal,_that.total);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  String variantPublicId,  String variantTitle,  String productName,  String productSlug,  int quantity,  Map<String, String> deliveryValues,  bool purchasable,  String? variantSku,  String? deliveryType,  String? blockReason,  Money? unitPrice,  Money? subtotal,  Money? total)?  $default,) {final _that = this;
switch (_that) {
case _CartLine() when $default != null:
return $default(_that.id,_that.variantPublicId,_that.variantTitle,_that.productName,_that.productSlug,_that.quantity,_that.deliveryValues,_that.purchasable,_that.variantSku,_that.deliveryType,_that.blockReason,_that.unitPrice,_that.subtotal,_that.total);case _:
  return null;

}
}

}

/// @nodoc


class _CartLine implements CartLine {
  const _CartLine({required this.id, required this.variantPublicId, required this.variantTitle, required this.productName, required this.productSlug, required this.quantity, required  Map<String, String> deliveryValues, required this.purchasable, this.variantSku, this.deliveryType, this.blockReason, this.unitPrice, this.subtotal, this.total}): _deliveryValues = deliveryValues;
  

@override final  int id;
@override final  String variantPublicId;
@override final  String variantTitle;
@override final  String productName;
@override final  String productSlug;
@override final  int quantity;
 final  Map<String, String> _deliveryValues;
@override Map<String, String> get deliveryValues {
  if (_deliveryValues is EqualUnmodifiableMapView) return _deliveryValues;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableMapView(_deliveryValues);
}

@override final  bool purchasable;
@override final  String? variantSku;
@override final  String? deliveryType;
/// `unavailable`, `out_of_stock`, `custom_amount_unsupported`, `quantity_limit`,
/// `not_sold_in_country` or a pricing reason; null when purchasable.
@override final  String? blockReason;
@override final  Money? unitPrice;
@override final  Money? subtotal;
@override final  Money? total;

/// Create a copy of CartLine
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CartLineCopyWith<_CartLine> get copyWith => __$CartLineCopyWithImpl<_CartLine>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CartLine&&(identical(other.id, id) || other.id == id)&&(identical(other.variantPublicId, variantPublicId) || other.variantPublicId == variantPublicId)&&(identical(other.variantTitle, variantTitle) || other.variantTitle == variantTitle)&&(identical(other.productName, productName) || other.productName == productName)&&(identical(other.productSlug, productSlug) || other.productSlug == productSlug)&&(identical(other.quantity, quantity) || other.quantity == quantity)&&const DeepCollectionEquality().equals(other.deliveryValues, _deliveryValues)&&(identical(other.purchasable, purchasable) || other.purchasable == purchasable)&&(identical(other.variantSku, variantSku) || other.variantSku == variantSku)&&(identical(other.deliveryType, deliveryType) || other.deliveryType == deliveryType)&&(identical(other.blockReason, blockReason) || other.blockReason == blockReason)&&(identical(other.unitPrice, unitPrice) || other.unitPrice == unitPrice)&&(identical(other.subtotal, subtotal) || other.subtotal == subtotal)&&(identical(other.total, total) || other.total == total));
}


@override
int get hashCode {
    return Object.hash(runtimeType,id,variantPublicId,variantTitle,productName,productSlug,quantity,const DeepCollectionEquality().hash(_deliveryValues),purchasable,variantSku,deliveryType,blockReason,unitPrice,subtotal,total);
}

@override
String toString() {
    return 'CartLine(id: $id, variantPublicId: $variantPublicId, variantTitle: $variantTitle, productName: $productName, productSlug: $productSlug, quantity: $quantity, deliveryValues: $deliveryValues, purchasable: $purchasable, variantSku: $variantSku, deliveryType: $deliveryType, blockReason: $blockReason, unitPrice: $unitPrice, subtotal: $subtotal, total: $total)';
}


}

/// @nodoc
abstract mixin class _$CartLineCopyWith<$Res> implements $CartLineCopyWith<$Res> {
  factory _$CartLineCopyWith(_CartLine value, $Res Function(_CartLine) _then) = __$CartLineCopyWithImpl;
@override @useResult
$Res call({
 int id, String variantPublicId, String variantTitle, String productName, String productSlug, int quantity, Map<String, String> deliveryValues, bool purchasable, String? variantSku, String? deliveryType, String? blockReason, Money? unitPrice, Money? subtotal, Money? total
});




}
/// @nodoc
class __$CartLineCopyWithImpl<$Res>
    implements _$CartLineCopyWith<$Res> {
  __$CartLineCopyWithImpl(this._self, this._then);

  final _CartLine _self;
  final $Res Function(_CartLine) _then;

/// Create a copy of CartLine
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? variantPublicId = null,Object? variantTitle = null,Object? productName = null,Object? productSlug = null,Object? quantity = null,Object? deliveryValues = null,Object? purchasable = null,Object? variantSku = freezed,Object? deliveryType = freezed,Object? blockReason = freezed,Object? unitPrice = freezed,Object? subtotal = freezed,Object? total = freezed,}) {
  return _then(_CartLine(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,variantPublicId: null == variantPublicId ? _self.variantPublicId : variantPublicId // ignore: cast_nullable_to_non_nullable
as String,variantTitle: null == variantTitle ? _self.variantTitle : variantTitle // ignore: cast_nullable_to_non_nullable
as String,productName: null == productName ? _self.productName : productName // ignore: cast_nullable_to_non_nullable
as String,productSlug: null == productSlug ? _self.productSlug : productSlug // ignore: cast_nullable_to_non_nullable
as String,quantity: null == quantity ? _self.quantity : quantity // ignore: cast_nullable_to_non_nullable
as int,deliveryValues: null == deliveryValues ? _self._deliveryValues : deliveryValues // ignore: cast_nullable_to_non_nullable
as Map<String, String>,purchasable: null == purchasable ? _self.purchasable : purchasable // ignore: cast_nullable_to_non_nullable
as bool,variantSku: freezed == variantSku ? _self.variantSku : variantSku // ignore: cast_nullable_to_non_nullable
as String?,deliveryType: freezed == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String?,blockReason: freezed == blockReason ? _self.blockReason : blockReason // ignore: cast_nullable_to_non_nullable
as String?,unitPrice: freezed == unitPrice ? _self.unitPrice : unitPrice // ignore: cast_nullable_to_non_nullable
as Money?,subtotal: freezed == subtotal ? _self.subtotal : subtotal // ignore: cast_nullable_to_non_nullable
as Money?,total: freezed == total ? _self.total : total // ignore: cast_nullable_to_non_nullable
as Money?,
  ));
}


}

/// @nodoc
mixin _$CartCoupon {

 String get code; bool get applied; bool get limitedByMargin; String? get error;
/// Create a copy of CartCoupon
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CartCouponCopyWith<CartCoupon> get copyWith => _$CartCouponCopyWithImpl<CartCoupon>(this as CartCoupon, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as CartCoupon;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CartCoupon&&(identical(other.code, _this.code) || other.code == _this.code)&&(identical(other.applied, _this.applied) || other.applied == _this.applied)&&(identical(other.limitedByMargin, _this.limitedByMargin) || other.limitedByMargin == _this.limitedByMargin)&&(identical(other.error, _this.error) || other.error == _this.error));
}


@override
int get hashCode {
  final _this = this as CartCoupon;
  return Object.hash(runtimeType,_this.code,_this.applied,_this.limitedByMargin,_this.error);
}

@override
String toString() {
  final _this = this as CartCoupon;
  return 'CartCoupon(code: ${_this.code}, applied: ${_this.applied}, limitedByMargin: ${_this.limitedByMargin}, error: ${_this.error})';
}


}

/// @nodoc
abstract mixin class $CartCouponCopyWith<$Res>  {
  factory $CartCouponCopyWith(CartCoupon value, $Res Function(CartCoupon) _then) = _$CartCouponCopyWithImpl;
@useResult
$Res call({
 String code, bool applied, bool limitedByMargin, String? error
});




}
/// @nodoc
class _$CartCouponCopyWithImpl<$Res>
    implements $CartCouponCopyWith<$Res> {
  _$CartCouponCopyWithImpl(this._self, this._then);

  final CartCoupon _self;
  final $Res Function(CartCoupon) _then;

/// Create a copy of CartCoupon
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? code = null,Object? applied = null,Object? limitedByMargin = null,Object? error = freezed,}) {
  return _then(CartCoupon(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,applied: null == applied ? _self.applied : applied // ignore: cast_nullable_to_non_nullable
as bool,limitedByMargin: null == limitedByMargin ? _self.limitedByMargin : limitedByMargin // ignore: cast_nullable_to_non_nullable
as bool,error: freezed == error ? _self.error : error // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [CartCoupon].
extension CartCouponPatterns on CartCoupon {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CartCoupon value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CartCoupon() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CartCoupon value)  $default,){
final _that = this;
switch (_that) {
case _CartCoupon():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CartCoupon value)?  $default,){
final _that = this;
switch (_that) {
case _CartCoupon() when $default != null:
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
case _CartCoupon() when $default != null:
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
case _CartCoupon():
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
case _CartCoupon() when $default != null:
return $default(_that.code,_that.applied,_that.limitedByMargin,_that.error);case _:
  return null;

}
}

}

/// @nodoc


class _CartCoupon implements CartCoupon {
  const _CartCoupon({required this.code, required this.applied, required this.limitedByMargin, this.error});
  

@override final  String code;
@override final  bool applied;
@override final  bool limitedByMargin;
@override final  String? error;

/// Create a copy of CartCoupon
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CartCouponCopyWith<_CartCoupon> get copyWith => __$CartCouponCopyWithImpl<_CartCoupon>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CartCoupon&&(identical(other.code, code) || other.code == code)&&(identical(other.applied, applied) || other.applied == applied)&&(identical(other.limitedByMargin, limitedByMargin) || other.limitedByMargin == limitedByMargin)&&(identical(other.error, error) || other.error == error));
}


@override
int get hashCode {
    return Object.hash(runtimeType,code,applied,limitedByMargin,error);
}

@override
String toString() {
    return 'CartCoupon(code: $code, applied: $applied, limitedByMargin: $limitedByMargin, error: $error)';
}


}

/// @nodoc
abstract mixin class _$CartCouponCopyWith<$Res> implements $CartCouponCopyWith<$Res> {
  factory _$CartCouponCopyWith(_CartCoupon value, $Res Function(_CartCoupon) _then) = __$CartCouponCopyWithImpl;
@override @useResult
$Res call({
 String code, bool applied, bool limitedByMargin, String? error
});




}
/// @nodoc
class __$CartCouponCopyWithImpl<$Res>
    implements _$CartCouponCopyWith<$Res> {
  __$CartCouponCopyWithImpl(this._self, this._then);

  final _CartCoupon _self;
  final $Res Function(_CartCoupon) _then;

/// Create a copy of CartCoupon
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? code = null,Object? applied = null,Object? limitedByMargin = null,Object? error = freezed,}) {
  return _then(_CartCoupon(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,applied: null == applied ? _self.applied : applied // ignore: cast_nullable_to_non_nullable
as bool,limitedByMargin: null == limitedByMargin ? _self.limitedByMargin : limitedByMargin // ignore: cast_nullable_to_non_nullable
as bool,error: freezed == error ? _self.error : error // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc
mixin _$CartTotals {

 Money get subtotal; Money get discount; Money get total;
/// Create a copy of CartTotals
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CartTotalsCopyWith<CartTotals> get copyWith => _$CartTotalsCopyWithImpl<CartTotals>(this as CartTotals, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as CartTotals;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CartTotals&&(identical(other.subtotal, _this.subtotal) || other.subtotal == _this.subtotal)&&(identical(other.discount, _this.discount) || other.discount == _this.discount)&&(identical(other.total, _this.total) || other.total == _this.total));
}


@override
int get hashCode {
  final _this = this as CartTotals;
  return Object.hash(runtimeType,_this.subtotal,_this.discount,_this.total);
}

@override
String toString() {
  final _this = this as CartTotals;
  return 'CartTotals(subtotal: ${_this.subtotal}, discount: ${_this.discount}, total: ${_this.total})';
}


}

/// @nodoc
abstract mixin class $CartTotalsCopyWith<$Res>  {
  factory $CartTotalsCopyWith(CartTotals value, $Res Function(CartTotals) _then) = _$CartTotalsCopyWithImpl;
@useResult
$Res call({
 Money subtotal, Money discount, Money total
});




}
/// @nodoc
class _$CartTotalsCopyWithImpl<$Res>
    implements $CartTotalsCopyWith<$Res> {
  _$CartTotalsCopyWithImpl(this._self, this._then);

  final CartTotals _self;
  final $Res Function(CartTotals) _then;

/// Create a copy of CartTotals
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? subtotal = null,Object? discount = null,Object? total = null,}) {
  return _then(CartTotals(
subtotal: null == subtotal ? _self.subtotal : subtotal // ignore: cast_nullable_to_non_nullable
as Money,discount: null == discount ? _self.discount : discount // ignore: cast_nullable_to_non_nullable
as Money,total: null == total ? _self.total : total // ignore: cast_nullable_to_non_nullable
as Money,
  ));
}

}


/// Adds pattern-matching-related methods to [CartTotals].
extension CartTotalsPatterns on CartTotals {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CartTotals value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CartTotals() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CartTotals value)  $default,){
final _that = this;
switch (_that) {
case _CartTotals():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CartTotals value)?  $default,){
final _that = this;
switch (_that) {
case _CartTotals() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( Money subtotal,  Money discount,  Money total)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CartTotals() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( Money subtotal,  Money discount,  Money total)  $default,) {final _that = this;
switch (_that) {
case _CartTotals():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( Money subtotal,  Money discount,  Money total)?  $default,) {final _that = this;
switch (_that) {
case _CartTotals() when $default != null:
return $default(_that.subtotal,_that.discount,_that.total);case _:
  return null;

}
}

}

/// @nodoc


class _CartTotals implements CartTotals {
  const _CartTotals({required this.subtotal, required this.discount, required this.total});
  

@override final  Money subtotal;
@override final  Money discount;
@override final  Money total;

/// Create a copy of CartTotals
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CartTotalsCopyWith<_CartTotals> get copyWith => __$CartTotalsCopyWithImpl<_CartTotals>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CartTotals&&(identical(other.subtotal, subtotal) || other.subtotal == subtotal)&&(identical(other.discount, discount) || other.discount == discount)&&(identical(other.total, total) || other.total == total));
}


@override
int get hashCode {
    return Object.hash(runtimeType,subtotal,discount,total);
}

@override
String toString() {
    return 'CartTotals(subtotal: $subtotal, discount: $discount, total: $total)';
}


}

/// @nodoc
abstract mixin class _$CartTotalsCopyWith<$Res> implements $CartTotalsCopyWith<$Res> {
  factory _$CartTotalsCopyWith(_CartTotals value, $Res Function(_CartTotals) _then) = __$CartTotalsCopyWithImpl;
@override @useResult
$Res call({
 Money subtotal, Money discount, Money total
});




}
/// @nodoc
class __$CartTotalsCopyWithImpl<$Res>
    implements _$CartTotalsCopyWith<$Res> {
  __$CartTotalsCopyWithImpl(this._self, this._then);

  final _CartTotals _self;
  final $Res Function(_CartTotals) _then;

/// Create a copy of CartTotals
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? subtotal = null,Object? discount = null,Object? total = null,}) {
  return _then(_CartTotals(
subtotal: null == subtotal ? _self.subtotal : subtotal // ignore: cast_nullable_to_non_nullable
as Money,discount: null == discount ? _self.discount : discount // ignore: cast_nullable_to_non_nullable
as Money,total: null == total ? _self.total : total // ignore: cast_nullable_to_non_nullable
as Money,
  ));
}


}

// dart format on
