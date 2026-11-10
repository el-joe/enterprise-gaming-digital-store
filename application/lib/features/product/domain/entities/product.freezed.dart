// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'product.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$ProductDetail {

 ProductCard get card; List<MediaImage> get images; List<Region> get regions; ProductRating get rating; VariantsSummary get variantsSummary; List<DeliveryField> get deliveryFields; ProductSeo get seo; String get displayCurrency; String? get sku;/// Sanitized HTML.
 String? get description;/// Plain text with line breaks.
 String? get instructions; String? get terms; int? get deliveryTimeMinutes; int? get maxQuantityPerOrder;/// `in_stock`, `limited`, `out_of_stock`, or null without active variants.
 String? get availability;
/// Create a copy of ProductDetail
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ProductDetailCopyWith<ProductDetail> get copyWith => _$ProductDetailCopyWithImpl<ProductDetail>(this as ProductDetail, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as ProductDetail;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ProductDetail&&(identical(other.card, _this.card) || other.card == _this.card)&&const DeepCollectionEquality().equals(other.images, _this.images)&&const DeepCollectionEquality().equals(other.regions, _this.regions)&&(identical(other.rating, _this.rating) || other.rating == _this.rating)&&(identical(other.variantsSummary, _this.variantsSummary) || other.variantsSummary == _this.variantsSummary)&&const DeepCollectionEquality().equals(other.deliveryFields, _this.deliveryFields)&&(identical(other.seo, _this.seo) || other.seo == _this.seo)&&(identical(other.displayCurrency, _this.displayCurrency) || other.displayCurrency == _this.displayCurrency)&&(identical(other.sku, _this.sku) || other.sku == _this.sku)&&(identical(other.description, _this.description) || other.description == _this.description)&&(identical(other.instructions, _this.instructions) || other.instructions == _this.instructions)&&(identical(other.terms, _this.terms) || other.terms == _this.terms)&&(identical(other.deliveryTimeMinutes, _this.deliveryTimeMinutes) || other.deliveryTimeMinutes == _this.deliveryTimeMinutes)&&(identical(other.maxQuantityPerOrder, _this.maxQuantityPerOrder) || other.maxQuantityPerOrder == _this.maxQuantityPerOrder)&&(identical(other.availability, _this.availability) || other.availability == _this.availability));
}


@override
int get hashCode {
  final _this = this as ProductDetail;
  return Object.hash(runtimeType,_this.card,const DeepCollectionEquality().hash(_this.images),const DeepCollectionEquality().hash(_this.regions),_this.rating,_this.variantsSummary,const DeepCollectionEquality().hash(_this.deliveryFields),_this.seo,_this.displayCurrency,_this.sku,_this.description,_this.instructions,_this.terms,_this.deliveryTimeMinutes,_this.maxQuantityPerOrder,_this.availability);
}

@override
String toString() {
  final _this = this as ProductDetail;
  return 'ProductDetail(card: ${_this.card}, images: ${_this.images}, regions: ${_this.regions}, rating: ${_this.rating}, variantsSummary: ${_this.variantsSummary}, deliveryFields: ${_this.deliveryFields}, seo: ${_this.seo}, displayCurrency: ${_this.displayCurrency}, sku: ${_this.sku}, description: ${_this.description}, instructions: ${_this.instructions}, terms: ${_this.terms}, deliveryTimeMinutes: ${_this.deliveryTimeMinutes}, maxQuantityPerOrder: ${_this.maxQuantityPerOrder}, availability: ${_this.availability})';
}


}

/// @nodoc
abstract mixin class $ProductDetailCopyWith<$Res>  {
  factory $ProductDetailCopyWith(ProductDetail value, $Res Function(ProductDetail) _then) = _$ProductDetailCopyWithImpl;
@useResult
$Res call({
 ProductCard card, List<MediaImage> images, List<Region> regions, ProductRating rating, VariantsSummary variantsSummary, List<DeliveryField> deliveryFields, ProductSeo seo, String displayCurrency, String? sku, String? description, String? instructions, String? terms, int? deliveryTimeMinutes, int? maxQuantityPerOrder, String? availability
});


$ProductCardCopyWith<$Res> get card;$ProductRatingCopyWith<$Res> get rating;$VariantsSummaryCopyWith<$Res> get variantsSummary;$ProductSeoCopyWith<$Res> get seo;

}
/// @nodoc
class _$ProductDetailCopyWithImpl<$Res>
    implements $ProductDetailCopyWith<$Res> {
  _$ProductDetailCopyWithImpl(this._self, this._then);

  final ProductDetail _self;
  final $Res Function(ProductDetail) _then;

/// Create a copy of ProductDetail
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? card = null,Object? images = null,Object? regions = null,Object? rating = null,Object? variantsSummary = null,Object? deliveryFields = null,Object? seo = null,Object? displayCurrency = null,Object? sku = freezed,Object? description = freezed,Object? instructions = freezed,Object? terms = freezed,Object? deliveryTimeMinutes = freezed,Object? maxQuantityPerOrder = freezed,Object? availability = freezed,}) {
  return _then(ProductDetail(
card: null == card ? _self.card : card // ignore: cast_nullable_to_non_nullable
as ProductCard,images: null == images ? _self.images : images // ignore: cast_nullable_to_non_nullable
as List<MediaImage>,regions: null == regions ? _self.regions : regions // ignore: cast_nullable_to_non_nullable
as List<Region>,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as ProductRating,variantsSummary: null == variantsSummary ? _self.variantsSummary : variantsSummary // ignore: cast_nullable_to_non_nullable
as VariantsSummary,deliveryFields: null == deliveryFields ? _self.deliveryFields : deliveryFields // ignore: cast_nullable_to_non_nullable
as List<DeliveryField>,seo: null == seo ? _self.seo : seo // ignore: cast_nullable_to_non_nullable
as ProductSeo,displayCurrency: null == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String,sku: freezed == sku ? _self.sku : sku // ignore: cast_nullable_to_non_nullable
as String?,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,instructions: freezed == instructions ? _self.instructions : instructions // ignore: cast_nullable_to_non_nullable
as String?,terms: freezed == terms ? _self.terms : terms // ignore: cast_nullable_to_non_nullable
as String?,deliveryTimeMinutes: freezed == deliveryTimeMinutes ? _self.deliveryTimeMinutes : deliveryTimeMinutes // ignore: cast_nullable_to_non_nullable
as int?,maxQuantityPerOrder: freezed == maxQuantityPerOrder ? _self.maxQuantityPerOrder : maxQuantityPerOrder // ignore: cast_nullable_to_non_nullable
as int?,availability: freezed == availability ? _self.availability : availability // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}
/// Create a copy of ProductDetail
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$ProductCardCopyWith<$Res> get card {
  
  return $ProductCardCopyWith<$Res>(_self.card, (value) {
    return _then(_self.copyWith(card: value));
  });
}/// Create a copy of ProductDetail
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$ProductRatingCopyWith<$Res> get rating {
  
  return $ProductRatingCopyWith<$Res>(_self.rating, (value) {
    return _then(_self.copyWith(rating: value));
  });
}/// Create a copy of ProductDetail
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$VariantsSummaryCopyWith<$Res> get variantsSummary {
  
  return $VariantsSummaryCopyWith<$Res>(_self.variantsSummary, (value) {
    return _then(_self.copyWith(variantsSummary: value));
  });
}/// Create a copy of ProductDetail
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$ProductSeoCopyWith<$Res> get seo {
  
  return $ProductSeoCopyWith<$Res>(_self.seo, (value) {
    return _then(_self.copyWith(seo: value));
  });
}
}


/// Adds pattern-matching-related methods to [ProductDetail].
extension ProductDetailPatterns on ProductDetail {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ProductDetail value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ProductDetail() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ProductDetail value)  $default,){
final _that = this;
switch (_that) {
case _ProductDetail():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ProductDetail value)?  $default,){
final _that = this;
switch (_that) {
case _ProductDetail() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( ProductCard card,  List<MediaImage> images,  List<Region> regions,  ProductRating rating,  VariantsSummary variantsSummary,  List<DeliveryField> deliveryFields,  ProductSeo seo,  String displayCurrency,  String? sku,  String? description,  String? instructions,  String? terms,  int? deliveryTimeMinutes,  int? maxQuantityPerOrder,  String? availability)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ProductDetail() when $default != null:
return $default(_that.card,_that.images,_that.regions,_that.rating,_that.variantsSummary,_that.deliveryFields,_that.seo,_that.displayCurrency,_that.sku,_that.description,_that.instructions,_that.terms,_that.deliveryTimeMinutes,_that.maxQuantityPerOrder,_that.availability);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( ProductCard card,  List<MediaImage> images,  List<Region> regions,  ProductRating rating,  VariantsSummary variantsSummary,  List<DeliveryField> deliveryFields,  ProductSeo seo,  String displayCurrency,  String? sku,  String? description,  String? instructions,  String? terms,  int? deliveryTimeMinutes,  int? maxQuantityPerOrder,  String? availability)  $default,) {final _that = this;
switch (_that) {
case _ProductDetail():
return $default(_that.card,_that.images,_that.regions,_that.rating,_that.variantsSummary,_that.deliveryFields,_that.seo,_that.displayCurrency,_that.sku,_that.description,_that.instructions,_that.terms,_that.deliveryTimeMinutes,_that.maxQuantityPerOrder,_that.availability);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( ProductCard card,  List<MediaImage> images,  List<Region> regions,  ProductRating rating,  VariantsSummary variantsSummary,  List<DeliveryField> deliveryFields,  ProductSeo seo,  String displayCurrency,  String? sku,  String? description,  String? instructions,  String? terms,  int? deliveryTimeMinutes,  int? maxQuantityPerOrder,  String? availability)?  $default,) {final _that = this;
switch (_that) {
case _ProductDetail() when $default != null:
return $default(_that.card,_that.images,_that.regions,_that.rating,_that.variantsSummary,_that.deliveryFields,_that.seo,_that.displayCurrency,_that.sku,_that.description,_that.instructions,_that.terms,_that.deliveryTimeMinutes,_that.maxQuantityPerOrder,_that.availability);case _:
  return null;

}
}

}

/// @nodoc


class _ProductDetail implements ProductDetail {
  const _ProductDetail({required this.card, required  List<MediaImage> images, required  List<Region> regions, required this.rating, required this.variantsSummary, required  List<DeliveryField> deliveryFields, required this.seo, required this.displayCurrency, this.sku, this.description, this.instructions, this.terms, this.deliveryTimeMinutes, this.maxQuantityPerOrder, this.availability}): _images = images,_regions = regions,_deliveryFields = deliveryFields;
  

@override final  ProductCard card;
 final  List<MediaImage> _images;
@override List<MediaImage> get images {
  if (_images is EqualUnmodifiableListView) return _images;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_images);
}

 final  List<Region> _regions;
@override List<Region> get regions {
  if (_regions is EqualUnmodifiableListView) return _regions;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_regions);
}

@override final  ProductRating rating;
@override final  VariantsSummary variantsSummary;
 final  List<DeliveryField> _deliveryFields;
@override List<DeliveryField> get deliveryFields {
  if (_deliveryFields is EqualUnmodifiableListView) return _deliveryFields;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_deliveryFields);
}

@override final  ProductSeo seo;
@override final  String displayCurrency;
@override final  String? sku;
/// Sanitized HTML.
@override final  String? description;
/// Plain text with line breaks.
@override final  String? instructions;
@override final  String? terms;
@override final  int? deliveryTimeMinutes;
@override final  int? maxQuantityPerOrder;
/// `in_stock`, `limited`, `out_of_stock`, or null without active variants.
@override final  String? availability;

/// Create a copy of ProductDetail
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ProductDetailCopyWith<_ProductDetail> get copyWith => __$ProductDetailCopyWithImpl<_ProductDetail>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ProductDetail&&(identical(other.card, card) || other.card == card)&&const DeepCollectionEquality().equals(other.images, _images)&&const DeepCollectionEquality().equals(other.regions, _regions)&&(identical(other.rating, rating) || other.rating == rating)&&(identical(other.variantsSummary, variantsSummary) || other.variantsSummary == variantsSummary)&&const DeepCollectionEquality().equals(other.deliveryFields, _deliveryFields)&&(identical(other.seo, seo) || other.seo == seo)&&(identical(other.displayCurrency, displayCurrency) || other.displayCurrency == displayCurrency)&&(identical(other.sku, sku) || other.sku == sku)&&(identical(other.description, description) || other.description == description)&&(identical(other.instructions, instructions) || other.instructions == instructions)&&(identical(other.terms, terms) || other.terms == terms)&&(identical(other.deliveryTimeMinutes, deliveryTimeMinutes) || other.deliveryTimeMinutes == deliveryTimeMinutes)&&(identical(other.maxQuantityPerOrder, maxQuantityPerOrder) || other.maxQuantityPerOrder == maxQuantityPerOrder)&&(identical(other.availability, availability) || other.availability == availability));
}


@override
int get hashCode {
    return Object.hash(runtimeType,card,const DeepCollectionEquality().hash(_images),const DeepCollectionEquality().hash(_regions),rating,variantsSummary,const DeepCollectionEquality().hash(_deliveryFields),seo,displayCurrency,sku,description,instructions,terms,deliveryTimeMinutes,maxQuantityPerOrder,availability);
}

@override
String toString() {
    return 'ProductDetail(card: $card, images: $images, regions: $regions, rating: $rating, variantsSummary: $variantsSummary, deliveryFields: $deliveryFields, seo: $seo, displayCurrency: $displayCurrency, sku: $sku, description: $description, instructions: $instructions, terms: $terms, deliveryTimeMinutes: $deliveryTimeMinutes, maxQuantityPerOrder: $maxQuantityPerOrder, availability: $availability)';
}


}

/// @nodoc
abstract mixin class _$ProductDetailCopyWith<$Res> implements $ProductDetailCopyWith<$Res> {
  factory _$ProductDetailCopyWith(_ProductDetail value, $Res Function(_ProductDetail) _then) = __$ProductDetailCopyWithImpl;
@override @useResult
$Res call({
 ProductCard card, List<MediaImage> images, List<Region> regions, ProductRating rating, VariantsSummary variantsSummary, List<DeliveryField> deliveryFields, ProductSeo seo, String displayCurrency, String? sku, String? description, String? instructions, String? terms, int? deliveryTimeMinutes, int? maxQuantityPerOrder, String? availability
});


@override $ProductCardCopyWith<$Res> get card;@override $ProductRatingCopyWith<$Res> get rating;@override $VariantsSummaryCopyWith<$Res> get variantsSummary;@override $ProductSeoCopyWith<$Res> get seo;

}
/// @nodoc
class __$ProductDetailCopyWithImpl<$Res>
    implements _$ProductDetailCopyWith<$Res> {
  __$ProductDetailCopyWithImpl(this._self, this._then);

  final _ProductDetail _self;
  final $Res Function(_ProductDetail) _then;

/// Create a copy of ProductDetail
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? card = null,Object? images = null,Object? regions = null,Object? rating = null,Object? variantsSummary = null,Object? deliveryFields = null,Object? seo = null,Object? displayCurrency = null,Object? sku = freezed,Object? description = freezed,Object? instructions = freezed,Object? terms = freezed,Object? deliveryTimeMinutes = freezed,Object? maxQuantityPerOrder = freezed,Object? availability = freezed,}) {
  return _then(_ProductDetail(
card: null == card ? _self.card : card // ignore: cast_nullable_to_non_nullable
as ProductCard,images: null == images ? _self._images : images // ignore: cast_nullable_to_non_nullable
as List<MediaImage>,regions: null == regions ? _self._regions : regions // ignore: cast_nullable_to_non_nullable
as List<Region>,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as ProductRating,variantsSummary: null == variantsSummary ? _self.variantsSummary : variantsSummary // ignore: cast_nullable_to_non_nullable
as VariantsSummary,deliveryFields: null == deliveryFields ? _self._deliveryFields : deliveryFields // ignore: cast_nullable_to_non_nullable
as List<DeliveryField>,seo: null == seo ? _self.seo : seo // ignore: cast_nullable_to_non_nullable
as ProductSeo,displayCurrency: null == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String,sku: freezed == sku ? _self.sku : sku // ignore: cast_nullable_to_non_nullable
as String?,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,instructions: freezed == instructions ? _self.instructions : instructions // ignore: cast_nullable_to_non_nullable
as String?,terms: freezed == terms ? _self.terms : terms // ignore: cast_nullable_to_non_nullable
as String?,deliveryTimeMinutes: freezed == deliveryTimeMinutes ? _self.deliveryTimeMinutes : deliveryTimeMinutes // ignore: cast_nullable_to_non_nullable
as int?,maxQuantityPerOrder: freezed == maxQuantityPerOrder ? _self.maxQuantityPerOrder : maxQuantityPerOrder // ignore: cast_nullable_to_non_nullable
as int?,availability: freezed == availability ? _self.availability : availability // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

/// Create a copy of ProductDetail
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$ProductCardCopyWith<$Res> get card {
  
  return $ProductCardCopyWith<$Res>(_self.card, (value) {
    return _then(_self.copyWith(card: value));
  });
}/// Create a copy of ProductDetail
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$ProductRatingCopyWith<$Res> get rating {
  
  return $ProductRatingCopyWith<$Res>(_self.rating, (value) {
    return _then(_self.copyWith(rating: value));
  });
}/// Create a copy of ProductDetail
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$VariantsSummaryCopyWith<$Res> get variantsSummary {
  
  return $VariantsSummaryCopyWith<$Res>(_self.variantsSummary, (value) {
    return _then(_self.copyWith(variantsSummary: value));
  });
}/// Create a copy of ProductDetail
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$ProductSeoCopyWith<$Res> get seo {
  
  return $ProductSeoCopyWith<$Res>(_self.seo, (value) {
    return _then(_self.copyWith(seo: value));
  });
}
}

/// @nodoc
mixin _$Region {

 String get code; String get name;
/// Create a copy of Region
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RegionCopyWith<Region> get copyWith => _$RegionCopyWithImpl<Region>(this as Region, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as Region;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is Region&&(identical(other.code, _this.code) || other.code == _this.code)&&(identical(other.name, _this.name) || other.name == _this.name));
}


@override
int get hashCode {
  final _this = this as Region;
  return Object.hash(runtimeType,_this.code,_this.name);
}

@override
String toString() {
  final _this = this as Region;
  return 'Region(code: ${_this.code}, name: ${_this.name})';
}


}

/// @nodoc
abstract mixin class $RegionCopyWith<$Res>  {
  factory $RegionCopyWith(Region value, $Res Function(Region) _then) = _$RegionCopyWithImpl;
@useResult
$Res call({
 String code, String name
});




}
/// @nodoc
class _$RegionCopyWithImpl<$Res>
    implements $RegionCopyWith<$Res> {
  _$RegionCopyWithImpl(this._self, this._then);

  final Region _self;
  final $Res Function(Region) _then;

/// Create a copy of Region
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? code = null,Object? name = null,}) {
  return _then(Region(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [Region].
extension RegionPatterns on Region {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _Region value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _Region() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _Region value)  $default,){
final _that = this;
switch (_that) {
case _Region():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _Region value)?  $default,){
final _that = this;
switch (_that) {
case _Region() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String code,  String name)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _Region() when $default != null:
return $default(_that.code,_that.name);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String code,  String name)  $default,) {final _that = this;
switch (_that) {
case _Region():
return $default(_that.code,_that.name);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String code,  String name)?  $default,) {final _that = this;
switch (_that) {
case _Region() when $default != null:
return $default(_that.code,_that.name);case _:
  return null;

}
}

}

/// @nodoc


class _Region implements Region {
  const _Region({required this.code, required this.name});
  

@override final  String code;
@override final  String name;

/// Create a copy of Region
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$RegionCopyWith<_Region> get copyWith => __$RegionCopyWithImpl<_Region>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _Region&&(identical(other.code, code) || other.code == code)&&(identical(other.name, name) || other.name == name));
}


@override
int get hashCode {
    return Object.hash(runtimeType,code,name);
}

@override
String toString() {
    return 'Region(code: $code, name: $name)';
}


}

/// @nodoc
abstract mixin class _$RegionCopyWith<$Res> implements $RegionCopyWith<$Res> {
  factory _$RegionCopyWith(_Region value, $Res Function(_Region) _then) = __$RegionCopyWithImpl;
@override @useResult
$Res call({
 String code, String name
});




}
/// @nodoc
class __$RegionCopyWithImpl<$Res>
    implements _$RegionCopyWith<$Res> {
  __$RegionCopyWithImpl(this._self, this._then);

  final _Region _self;
  final $Res Function(_Region) _then;

/// Create a copy of Region
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? code = null,Object? name = null,}) {
  return _then(_Region(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc
mixin _$ProductRating {

 String get average; int get count;
/// Create a copy of ProductRating
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ProductRatingCopyWith<ProductRating> get copyWith => _$ProductRatingCopyWithImpl<ProductRating>(this as ProductRating, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as ProductRating;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ProductRating&&(identical(other.average, _this.average) || other.average == _this.average)&&(identical(other.count, _this.count) || other.count == _this.count));
}


@override
int get hashCode {
  final _this = this as ProductRating;
  return Object.hash(runtimeType,_this.average,_this.count);
}

@override
String toString() {
  final _this = this as ProductRating;
  return 'ProductRating(average: ${_this.average}, count: ${_this.count})';
}


}

/// @nodoc
abstract mixin class $ProductRatingCopyWith<$Res>  {
  factory $ProductRatingCopyWith(ProductRating value, $Res Function(ProductRating) _then) = _$ProductRatingCopyWithImpl;
@useResult
$Res call({
 String average, int count
});




}
/// @nodoc
class _$ProductRatingCopyWithImpl<$Res>
    implements $ProductRatingCopyWith<$Res> {
  _$ProductRatingCopyWithImpl(this._self, this._then);

  final ProductRating _self;
  final $Res Function(ProductRating) _then;

/// Create a copy of ProductRating
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? average = null,Object? count = null,}) {
  return _then(ProductRating(
average: null == average ? _self.average : average // ignore: cast_nullable_to_non_nullable
as String,count: null == count ? _self.count : count // ignore: cast_nullable_to_non_nullable
as int,
  ));
}

}


/// Adds pattern-matching-related methods to [ProductRating].
extension ProductRatingPatterns on ProductRating {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ProductRating value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ProductRating() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ProductRating value)  $default,){
final _that = this;
switch (_that) {
case _ProductRating():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ProductRating value)?  $default,){
final _that = this;
switch (_that) {
case _ProductRating() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String average,  int count)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ProductRating() when $default != null:
return $default(_that.average,_that.count);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String average,  int count)  $default,) {final _that = this;
switch (_that) {
case _ProductRating():
return $default(_that.average,_that.count);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String average,  int count)?  $default,) {final _that = this;
switch (_that) {
case _ProductRating() when $default != null:
return $default(_that.average,_that.count);case _:
  return null;

}
}

}

/// @nodoc


class _ProductRating implements ProductRating {
  const _ProductRating({required this.average, required this.count});
  

@override final  String average;
@override final  int count;

/// Create a copy of ProductRating
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ProductRatingCopyWith<_ProductRating> get copyWith => __$ProductRatingCopyWithImpl<_ProductRating>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ProductRating&&(identical(other.average, average) || other.average == average)&&(identical(other.count, count) || other.count == count));
}


@override
int get hashCode {
    return Object.hash(runtimeType,average,count);
}

@override
String toString() {
    return 'ProductRating(average: $average, count: $count)';
}


}

/// @nodoc
abstract mixin class _$ProductRatingCopyWith<$Res> implements $ProductRatingCopyWith<$Res> {
  factory _$ProductRatingCopyWith(_ProductRating value, $Res Function(_ProductRating) _then) = __$ProductRatingCopyWithImpl;
@override @useResult
$Res call({
 String average, int count
});




}
/// @nodoc
class __$ProductRatingCopyWithImpl<$Res>
    implements _$ProductRatingCopyWith<$Res> {
  __$ProductRatingCopyWithImpl(this._self, this._then);

  final _ProductRating _self;
  final $Res Function(_ProductRating) _then;

/// Create a copy of ProductRating
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? average = null,Object? count = null,}) {
  return _then(_ProductRating(
average: null == average ? _self.average : average // ignore: cast_nullable_to_non_nullable
as String,count: null == count ? _self.count : count // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc
mixin _$VariantsSummary {

 int get count; List<Region> get regions; bool get hasCustomAmount;
/// Create a copy of VariantsSummary
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$VariantsSummaryCopyWith<VariantsSummary> get copyWith => _$VariantsSummaryCopyWithImpl<VariantsSummary>(this as VariantsSummary, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as VariantsSummary;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is VariantsSummary&&(identical(other.count, _this.count) || other.count == _this.count)&&const DeepCollectionEquality().equals(other.regions, _this.regions)&&(identical(other.hasCustomAmount, _this.hasCustomAmount) || other.hasCustomAmount == _this.hasCustomAmount));
}


@override
int get hashCode {
  final _this = this as VariantsSummary;
  return Object.hash(runtimeType,_this.count,const DeepCollectionEquality().hash(_this.regions),_this.hasCustomAmount);
}

@override
String toString() {
  final _this = this as VariantsSummary;
  return 'VariantsSummary(count: ${_this.count}, regions: ${_this.regions}, hasCustomAmount: ${_this.hasCustomAmount})';
}


}

/// @nodoc
abstract mixin class $VariantsSummaryCopyWith<$Res>  {
  factory $VariantsSummaryCopyWith(VariantsSummary value, $Res Function(VariantsSummary) _then) = _$VariantsSummaryCopyWithImpl;
@useResult
$Res call({
 int count, List<Region> regions, bool hasCustomAmount
});




}
/// @nodoc
class _$VariantsSummaryCopyWithImpl<$Res>
    implements $VariantsSummaryCopyWith<$Res> {
  _$VariantsSummaryCopyWithImpl(this._self, this._then);

  final VariantsSummary _self;
  final $Res Function(VariantsSummary) _then;

/// Create a copy of VariantsSummary
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? count = null,Object? regions = null,Object? hasCustomAmount = null,}) {
  return _then(VariantsSummary(
count: null == count ? _self.count : count // ignore: cast_nullable_to_non_nullable
as int,regions: null == regions ? _self.regions : regions // ignore: cast_nullable_to_non_nullable
as List<Region>,hasCustomAmount: null == hasCustomAmount ? _self.hasCustomAmount : hasCustomAmount // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [VariantsSummary].
extension VariantsSummaryPatterns on VariantsSummary {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _VariantsSummary value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _VariantsSummary() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _VariantsSummary value)  $default,){
final _that = this;
switch (_that) {
case _VariantsSummary():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _VariantsSummary value)?  $default,){
final _that = this;
switch (_that) {
case _VariantsSummary() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int count,  List<Region> regions,  bool hasCustomAmount)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _VariantsSummary() when $default != null:
return $default(_that.count,_that.regions,_that.hasCustomAmount);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int count,  List<Region> regions,  bool hasCustomAmount)  $default,) {final _that = this;
switch (_that) {
case _VariantsSummary():
return $default(_that.count,_that.regions,_that.hasCustomAmount);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int count,  List<Region> regions,  bool hasCustomAmount)?  $default,) {final _that = this;
switch (_that) {
case _VariantsSummary() when $default != null:
return $default(_that.count,_that.regions,_that.hasCustomAmount);case _:
  return null;

}
}

}

/// @nodoc


class _VariantsSummary implements VariantsSummary {
  const _VariantsSummary({required this.count, required  List<Region> regions, required this.hasCustomAmount}): _regions = regions;
  

@override final  int count;
 final  List<Region> _regions;
@override List<Region> get regions {
  if (_regions is EqualUnmodifiableListView) return _regions;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_regions);
}

@override final  bool hasCustomAmount;

/// Create a copy of VariantsSummary
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$VariantsSummaryCopyWith<_VariantsSummary> get copyWith => __$VariantsSummaryCopyWithImpl<_VariantsSummary>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _VariantsSummary&&(identical(other.count, count) || other.count == count)&&const DeepCollectionEquality().equals(other.regions, _regions)&&(identical(other.hasCustomAmount, hasCustomAmount) || other.hasCustomAmount == hasCustomAmount));
}


@override
int get hashCode {
    return Object.hash(runtimeType,count,const DeepCollectionEquality().hash(_regions),hasCustomAmount);
}

@override
String toString() {
    return 'VariantsSummary(count: $count, regions: $regions, hasCustomAmount: $hasCustomAmount)';
}


}

/// @nodoc
abstract mixin class _$VariantsSummaryCopyWith<$Res> implements $VariantsSummaryCopyWith<$Res> {
  factory _$VariantsSummaryCopyWith(_VariantsSummary value, $Res Function(_VariantsSummary) _then) = __$VariantsSummaryCopyWithImpl;
@override @useResult
$Res call({
 int count, List<Region> regions, bool hasCustomAmount
});




}
/// @nodoc
class __$VariantsSummaryCopyWithImpl<$Res>
    implements _$VariantsSummaryCopyWith<$Res> {
  __$VariantsSummaryCopyWithImpl(this._self, this._then);

  final _VariantsSummary _self;
  final $Res Function(_VariantsSummary) _then;

/// Create a copy of VariantsSummary
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? count = null,Object? regions = null,Object? hasCustomAmount = null,}) {
  return _then(_VariantsSummary(
count: null == count ? _self.count : count // ignore: cast_nullable_to_non_nullable
as int,regions: null == regions ? _self._regions : regions // ignore: cast_nullable_to_non_nullable
as List<Region>,hasCustomAmount: null == hasCustomAmount ? _self.hasCustomAmount : hasCustomAmount // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc
mixin _$DeliveryField {

 String get key; String get label;/// `text`, `number`, `email` or `select`.
 String get type; bool get required; String? get helpText; int? get maxLength;/// Whole-value pattern, anchored by the server.
 String? get pattern; List<DeliveryFieldOption>? get options;
/// Create a copy of DeliveryField
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DeliveryFieldCopyWith<DeliveryField> get copyWith => _$DeliveryFieldCopyWithImpl<DeliveryField>(this as DeliveryField, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as DeliveryField;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DeliveryField&&(identical(other.key, _this.key) || other.key == _this.key)&&(identical(other.label, _this.label) || other.label == _this.label)&&(identical(other.type, _this.type) || other.type == _this.type)&&(identical(other.required, _this.required) || other.required == _this.required)&&(identical(other.helpText, _this.helpText) || other.helpText == _this.helpText)&&(identical(other.maxLength, _this.maxLength) || other.maxLength == _this.maxLength)&&(identical(other.pattern, _this.pattern) || other.pattern == _this.pattern)&&const DeepCollectionEquality().equals(other.options, _this.options));
}


@override
int get hashCode {
  final _this = this as DeliveryField;
  return Object.hash(runtimeType,_this.key,_this.label,_this.type,_this.required,_this.helpText,_this.maxLength,_this.pattern,const DeepCollectionEquality().hash(_this.options));
}

@override
String toString() {
  final _this = this as DeliveryField;
  return 'DeliveryField(key: ${_this.key}, label: ${_this.label}, type: ${_this.type}, required: ${_this.required}, helpText: ${_this.helpText}, maxLength: ${_this.maxLength}, pattern: ${_this.pattern}, options: ${_this.options})';
}


}

/// @nodoc
abstract mixin class $DeliveryFieldCopyWith<$Res>  {
  factory $DeliveryFieldCopyWith(DeliveryField value, $Res Function(DeliveryField) _then) = _$DeliveryFieldCopyWithImpl;
@useResult
$Res call({
 String key, String label, String type, bool required, String? helpText, int? maxLength, String? pattern, List<DeliveryFieldOption>? options
});




}
/// @nodoc
class _$DeliveryFieldCopyWithImpl<$Res>
    implements $DeliveryFieldCopyWith<$Res> {
  _$DeliveryFieldCopyWithImpl(this._self, this._then);

  final DeliveryField _self;
  final $Res Function(DeliveryField) _then;

/// Create a copy of DeliveryField
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? key = null,Object? label = null,Object? type = null,Object? required = null,Object? helpText = freezed,Object? maxLength = freezed,Object? pattern = freezed,Object? options = freezed,}) {
  return _then(DeliveryField(
key: null == key ? _self.key : key // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,required: null == required ? _self.required : required // ignore: cast_nullable_to_non_nullable
as bool,helpText: freezed == helpText ? _self.helpText : helpText // ignore: cast_nullable_to_non_nullable
as String?,maxLength: freezed == maxLength ? _self.maxLength : maxLength // ignore: cast_nullable_to_non_nullable
as int?,pattern: freezed == pattern ? _self.pattern : pattern // ignore: cast_nullable_to_non_nullable
as String?,options: freezed == options ? _self.options : options // ignore: cast_nullable_to_non_nullable
as List<DeliveryFieldOption>?,
  ));
}

}


/// Adds pattern-matching-related methods to [DeliveryField].
extension DeliveryFieldPatterns on DeliveryField {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _DeliveryField value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _DeliveryField() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _DeliveryField value)  $default,){
final _that = this;
switch (_that) {
case _DeliveryField():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _DeliveryField value)?  $default,){
final _that = this;
switch (_that) {
case _DeliveryField() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String key,  String label,  String type,  bool required,  String? helpText,  int? maxLength,  String? pattern,  List<DeliveryFieldOption>? options)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _DeliveryField() when $default != null:
return $default(_that.key,_that.label,_that.type,_that.required,_that.helpText,_that.maxLength,_that.pattern,_that.options);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String key,  String label,  String type,  bool required,  String? helpText,  int? maxLength,  String? pattern,  List<DeliveryFieldOption>? options)  $default,) {final _that = this;
switch (_that) {
case _DeliveryField():
return $default(_that.key,_that.label,_that.type,_that.required,_that.helpText,_that.maxLength,_that.pattern,_that.options);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String key,  String label,  String type,  bool required,  String? helpText,  int? maxLength,  String? pattern,  List<DeliveryFieldOption>? options)?  $default,) {final _that = this;
switch (_that) {
case _DeliveryField() when $default != null:
return $default(_that.key,_that.label,_that.type,_that.required,_that.helpText,_that.maxLength,_that.pattern,_that.options);case _:
  return null;

}
}

}

/// @nodoc


class _DeliveryField implements DeliveryField {
  const _DeliveryField({required this.key, required this.label, required this.type, required this.required, this.helpText, this.maxLength, this.pattern,  List<DeliveryFieldOption>? options}): _options = options;
  

@override final  String key;
@override final  String label;
/// `text`, `number`, `email` or `select`.
@override final  String type;
@override final  bool required;
@override final  String? helpText;
@override final  int? maxLength;
/// Whole-value pattern, anchored by the server.
@override final  String? pattern;
 final  List<DeliveryFieldOption>? _options;
@override List<DeliveryFieldOption>? get options {
  final value = _options;
  if (value == null) return null;
  if (_options is EqualUnmodifiableListView) return _options;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}


/// Create a copy of DeliveryField
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DeliveryFieldCopyWith<_DeliveryField> get copyWith => __$DeliveryFieldCopyWithImpl<_DeliveryField>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _DeliveryField&&(identical(other.key, key) || other.key == key)&&(identical(other.label, label) || other.label == label)&&(identical(other.type, type) || other.type == type)&&(identical(other.required, required) || other.required == required)&&(identical(other.helpText, helpText) || other.helpText == helpText)&&(identical(other.maxLength, maxLength) || other.maxLength == maxLength)&&(identical(other.pattern, pattern) || other.pattern == pattern)&&const DeepCollectionEquality().equals(other.options, _options));
}


@override
int get hashCode {
    return Object.hash(runtimeType,key,label,type,required,helpText,maxLength,pattern,const DeepCollectionEquality().hash(_options));
}

@override
String toString() {
    return 'DeliveryField(key: $key, label: $label, type: $type, required: $required, helpText: $helpText, maxLength: $maxLength, pattern: $pattern, options: $options)';
}


}

/// @nodoc
abstract mixin class _$DeliveryFieldCopyWith<$Res> implements $DeliveryFieldCopyWith<$Res> {
  factory _$DeliveryFieldCopyWith(_DeliveryField value, $Res Function(_DeliveryField) _then) = __$DeliveryFieldCopyWithImpl;
@override @useResult
$Res call({
 String key, String label, String type, bool required, String? helpText, int? maxLength, String? pattern, List<DeliveryFieldOption>? options
});




}
/// @nodoc
class __$DeliveryFieldCopyWithImpl<$Res>
    implements _$DeliveryFieldCopyWith<$Res> {
  __$DeliveryFieldCopyWithImpl(this._self, this._then);

  final _DeliveryField _self;
  final $Res Function(_DeliveryField) _then;

/// Create a copy of DeliveryField
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? key = null,Object? label = null,Object? type = null,Object? required = null,Object? helpText = freezed,Object? maxLength = freezed,Object? pattern = freezed,Object? options = freezed,}) {
  return _then(_DeliveryField(
key: null == key ? _self.key : key // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,required: null == required ? _self.required : required // ignore: cast_nullable_to_non_nullable
as bool,helpText: freezed == helpText ? _self.helpText : helpText // ignore: cast_nullable_to_non_nullable
as String?,maxLength: freezed == maxLength ? _self.maxLength : maxLength // ignore: cast_nullable_to_non_nullable
as int?,pattern: freezed == pattern ? _self.pattern : pattern // ignore: cast_nullable_to_non_nullable
as String?,options: freezed == options ? _self._options : options // ignore: cast_nullable_to_non_nullable
as List<DeliveryFieldOption>?,
  ));
}


}

/// @nodoc
mixin _$DeliveryFieldOption {

 String get value; String get label;
/// Create a copy of DeliveryFieldOption
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DeliveryFieldOptionCopyWith<DeliveryFieldOption> get copyWith => _$DeliveryFieldOptionCopyWithImpl<DeliveryFieldOption>(this as DeliveryFieldOption, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as DeliveryFieldOption;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DeliveryFieldOption&&(identical(other.value, _this.value) || other.value == _this.value)&&(identical(other.label, _this.label) || other.label == _this.label));
}


@override
int get hashCode {
  final _this = this as DeliveryFieldOption;
  return Object.hash(runtimeType,_this.value,_this.label);
}

@override
String toString() {
  final _this = this as DeliveryFieldOption;
  return 'DeliveryFieldOption(value: ${_this.value}, label: ${_this.label})';
}


}

/// @nodoc
abstract mixin class $DeliveryFieldOptionCopyWith<$Res>  {
  factory $DeliveryFieldOptionCopyWith(DeliveryFieldOption value, $Res Function(DeliveryFieldOption) _then) = _$DeliveryFieldOptionCopyWithImpl;
@useResult
$Res call({
 String value, String label
});




}
/// @nodoc
class _$DeliveryFieldOptionCopyWithImpl<$Res>
    implements $DeliveryFieldOptionCopyWith<$Res> {
  _$DeliveryFieldOptionCopyWithImpl(this._self, this._then);

  final DeliveryFieldOption _self;
  final $Res Function(DeliveryFieldOption) _then;

/// Create a copy of DeliveryFieldOption
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? value = null,Object? label = null,}) {
  return _then(DeliveryFieldOption(
value: null == value ? _self.value : value // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [DeliveryFieldOption].
extension DeliveryFieldOptionPatterns on DeliveryFieldOption {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _DeliveryFieldOption value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _DeliveryFieldOption() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _DeliveryFieldOption value)  $default,){
final _that = this;
switch (_that) {
case _DeliveryFieldOption():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _DeliveryFieldOption value)?  $default,){
final _that = this;
switch (_that) {
case _DeliveryFieldOption() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String value,  String label)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _DeliveryFieldOption() when $default != null:
return $default(_that.value,_that.label);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String value,  String label)  $default,) {final _that = this;
switch (_that) {
case _DeliveryFieldOption():
return $default(_that.value,_that.label);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String value,  String label)?  $default,) {final _that = this;
switch (_that) {
case _DeliveryFieldOption() when $default != null:
return $default(_that.value,_that.label);case _:
  return null;

}
}

}

/// @nodoc


class _DeliveryFieldOption implements DeliveryFieldOption {
  const _DeliveryFieldOption({required this.value, required this.label});
  

@override final  String value;
@override final  String label;

/// Create a copy of DeliveryFieldOption
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DeliveryFieldOptionCopyWith<_DeliveryFieldOption> get copyWith => __$DeliveryFieldOptionCopyWithImpl<_DeliveryFieldOption>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _DeliveryFieldOption&&(identical(other.value, value) || other.value == value)&&(identical(other.label, label) || other.label == label));
}


@override
int get hashCode {
    return Object.hash(runtimeType,value,label);
}

@override
String toString() {
    return 'DeliveryFieldOption(value: $value, label: $label)';
}


}

/// @nodoc
abstract mixin class _$DeliveryFieldOptionCopyWith<$Res> implements $DeliveryFieldOptionCopyWith<$Res> {
  factory _$DeliveryFieldOptionCopyWith(_DeliveryFieldOption value, $Res Function(_DeliveryFieldOption) _then) = __$DeliveryFieldOptionCopyWithImpl;
@override @useResult
$Res call({
 String value, String label
});




}
/// @nodoc
class __$DeliveryFieldOptionCopyWithImpl<$Res>
    implements _$DeliveryFieldOptionCopyWith<$Res> {
  __$DeliveryFieldOptionCopyWithImpl(this._self, this._then);

  final _DeliveryFieldOption _self;
  final $Res Function(_DeliveryFieldOption) _then;

/// Create a copy of DeliveryFieldOption
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? value = null,Object? label = null,}) {
  return _then(_DeliveryFieldOption(
value: null == value ? _self.value : value // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc
mixin _$ProductSeo {

 String? get title; String? get description; String? get robots; Uri? get image;
/// Create a copy of ProductSeo
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ProductSeoCopyWith<ProductSeo> get copyWith => _$ProductSeoCopyWithImpl<ProductSeo>(this as ProductSeo, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as ProductSeo;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ProductSeo&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.description, _this.description) || other.description == _this.description)&&(identical(other.robots, _this.robots) || other.robots == _this.robots)&&(identical(other.image, _this.image) || other.image == _this.image));
}


@override
int get hashCode {
  final _this = this as ProductSeo;
  return Object.hash(runtimeType,_this.title,_this.description,_this.robots,_this.image);
}

@override
String toString() {
  final _this = this as ProductSeo;
  return 'ProductSeo(title: ${_this.title}, description: ${_this.description}, robots: ${_this.robots}, image: ${_this.image})';
}


}

/// @nodoc
abstract mixin class $ProductSeoCopyWith<$Res>  {
  factory $ProductSeoCopyWith(ProductSeo value, $Res Function(ProductSeo) _then) = _$ProductSeoCopyWithImpl;
@useResult
$Res call({
 String? title, String? description, String? robots, Uri? image
});




}
/// @nodoc
class _$ProductSeoCopyWithImpl<$Res>
    implements $ProductSeoCopyWith<$Res> {
  _$ProductSeoCopyWithImpl(this._self, this._then);

  final ProductSeo _self;
  final $Res Function(ProductSeo) _then;

/// Create a copy of ProductSeo
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? title = freezed,Object? description = freezed,Object? robots = freezed,Object? image = freezed,}) {
  return _then(ProductSeo(
title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,robots: freezed == robots ? _self.robots : robots // ignore: cast_nullable_to_non_nullable
as String?,image: freezed == image ? _self.image : image // ignore: cast_nullable_to_non_nullable
as Uri?,
  ));
}

}


/// Adds pattern-matching-related methods to [ProductSeo].
extension ProductSeoPatterns on ProductSeo {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ProductSeo value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ProductSeo() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ProductSeo value)  $default,){
final _that = this;
switch (_that) {
case _ProductSeo():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ProductSeo value)?  $default,){
final _that = this;
switch (_that) {
case _ProductSeo() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String? title,  String? description,  String? robots,  Uri? image)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ProductSeo() when $default != null:
return $default(_that.title,_that.description,_that.robots,_that.image);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String? title,  String? description,  String? robots,  Uri? image)  $default,) {final _that = this;
switch (_that) {
case _ProductSeo():
return $default(_that.title,_that.description,_that.robots,_that.image);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String? title,  String? description,  String? robots,  Uri? image)?  $default,) {final _that = this;
switch (_that) {
case _ProductSeo() when $default != null:
return $default(_that.title,_that.description,_that.robots,_that.image);case _:
  return null;

}
}

}

/// @nodoc


class _ProductSeo implements ProductSeo {
  const _ProductSeo({this.title, this.description, this.robots, this.image});
  

@override final  String? title;
@override final  String? description;
@override final  String? robots;
@override final  Uri? image;

/// Create a copy of ProductSeo
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ProductSeoCopyWith<_ProductSeo> get copyWith => __$ProductSeoCopyWithImpl<_ProductSeo>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ProductSeo&&(identical(other.title, title) || other.title == title)&&(identical(other.description, description) || other.description == description)&&(identical(other.robots, robots) || other.robots == robots)&&(identical(other.image, image) || other.image == image));
}


@override
int get hashCode {
    return Object.hash(runtimeType,title,description,robots,image);
}

@override
String toString() {
    return 'ProductSeo(title: $title, description: $description, robots: $robots, image: $image)';
}


}

/// @nodoc
abstract mixin class _$ProductSeoCopyWith<$Res> implements $ProductSeoCopyWith<$Res> {
  factory _$ProductSeoCopyWith(_ProductSeo value, $Res Function(_ProductSeo) _then) = __$ProductSeoCopyWithImpl;
@override @useResult
$Res call({
 String? title, String? description, String? robots, Uri? image
});




}
/// @nodoc
class __$ProductSeoCopyWithImpl<$Res>
    implements _$ProductSeoCopyWith<$Res> {
  __$ProductSeoCopyWithImpl(this._self, this._then);

  final _ProductSeo _self;
  final $Res Function(_ProductSeo) _then;

/// Create a copy of ProductSeo
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? title = freezed,Object? description = freezed,Object? robots = freezed,Object? image = freezed,}) {
  return _then(_ProductSeo(
title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,robots: freezed == robots ? _self.robots : robots // ignore: cast_nullable_to_non_nullable
as String?,image: freezed == image ? _self.image : image // ignore: cast_nullable_to_non_nullable
as Uri?,
  ));
}


}

/// @nodoc
mixin _$VariantOffer {

 String get publicId; String get title; String get sku; bool get isCustomAmount; String get availability; bool get purchasable; bool get priceMayVary; String? get region;/// Decimal string (`10.000000`).
 String? get denominationValue; String? get denominationCurrency; Money? get price;
/// Create a copy of VariantOffer
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$VariantOfferCopyWith<VariantOffer> get copyWith => _$VariantOfferCopyWithImpl<VariantOffer>(this as VariantOffer, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as VariantOffer;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is VariantOffer&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.sku, _this.sku) || other.sku == _this.sku)&&(identical(other.isCustomAmount, _this.isCustomAmount) || other.isCustomAmount == _this.isCustomAmount)&&(identical(other.availability, _this.availability) || other.availability == _this.availability)&&(identical(other.purchasable, _this.purchasable) || other.purchasable == _this.purchasable)&&(identical(other.priceMayVary, _this.priceMayVary) || other.priceMayVary == _this.priceMayVary)&&(identical(other.region, _this.region) || other.region == _this.region)&&(identical(other.denominationValue, _this.denominationValue) || other.denominationValue == _this.denominationValue)&&(identical(other.denominationCurrency, _this.denominationCurrency) || other.denominationCurrency == _this.denominationCurrency)&&(identical(other.price, _this.price) || other.price == _this.price));
}


@override
int get hashCode {
  final _this = this as VariantOffer;
  return Object.hash(runtimeType,_this.publicId,_this.title,_this.sku,_this.isCustomAmount,_this.availability,_this.purchasable,_this.priceMayVary,_this.region,_this.denominationValue,_this.denominationCurrency,_this.price);
}

@override
String toString() {
  final _this = this as VariantOffer;
  return 'VariantOffer(publicId: ${_this.publicId}, title: ${_this.title}, sku: ${_this.sku}, isCustomAmount: ${_this.isCustomAmount}, availability: ${_this.availability}, purchasable: ${_this.purchasable}, priceMayVary: ${_this.priceMayVary}, region: ${_this.region}, denominationValue: ${_this.denominationValue}, denominationCurrency: ${_this.denominationCurrency}, price: ${_this.price})';
}


}

/// @nodoc
abstract mixin class $VariantOfferCopyWith<$Res>  {
  factory $VariantOfferCopyWith(VariantOffer value, $Res Function(VariantOffer) _then) = _$VariantOfferCopyWithImpl;
@useResult
$Res call({
 String publicId, String title, String sku, bool isCustomAmount, String availability, bool purchasable, bool priceMayVary, String? region, String? denominationValue, String? denominationCurrency, Money? price
});




}
/// @nodoc
class _$VariantOfferCopyWithImpl<$Res>
    implements $VariantOfferCopyWith<$Res> {
  _$VariantOfferCopyWithImpl(this._self, this._then);

  final VariantOffer _self;
  final $Res Function(VariantOffer) _then;

/// Create a copy of VariantOffer
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? title = null,Object? sku = null,Object? isCustomAmount = null,Object? availability = null,Object? purchasable = null,Object? priceMayVary = null,Object? region = freezed,Object? denominationValue = freezed,Object? denominationCurrency = freezed,Object? price = freezed,}) {
  return _then(VariantOffer(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,sku: null == sku ? _self.sku : sku // ignore: cast_nullable_to_non_nullable
as String,isCustomAmount: null == isCustomAmount ? _self.isCustomAmount : isCustomAmount // ignore: cast_nullable_to_non_nullable
as bool,availability: null == availability ? _self.availability : availability // ignore: cast_nullable_to_non_nullable
as String,purchasable: null == purchasable ? _self.purchasable : purchasable // ignore: cast_nullable_to_non_nullable
as bool,priceMayVary: null == priceMayVary ? _self.priceMayVary : priceMayVary // ignore: cast_nullable_to_non_nullable
as bool,region: freezed == region ? _self.region : region // ignore: cast_nullable_to_non_nullable
as String?,denominationValue: freezed == denominationValue ? _self.denominationValue : denominationValue // ignore: cast_nullable_to_non_nullable
as String?,denominationCurrency: freezed == denominationCurrency ? _self.denominationCurrency : denominationCurrency // ignore: cast_nullable_to_non_nullable
as String?,price: freezed == price ? _self.price : price // ignore: cast_nullable_to_non_nullable
as Money?,
  ));
}

}


/// Adds pattern-matching-related methods to [VariantOffer].
extension VariantOfferPatterns on VariantOffer {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _VariantOffer value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _VariantOffer() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _VariantOffer value)  $default,){
final _that = this;
switch (_that) {
case _VariantOffer():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _VariantOffer value)?  $default,){
final _that = this;
switch (_that) {
case _VariantOffer() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String title,  String sku,  bool isCustomAmount,  String availability,  bool purchasable,  bool priceMayVary,  String? region,  String? denominationValue,  String? denominationCurrency,  Money? price)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _VariantOffer() when $default != null:
return $default(_that.publicId,_that.title,_that.sku,_that.isCustomAmount,_that.availability,_that.purchasable,_that.priceMayVary,_that.region,_that.denominationValue,_that.denominationCurrency,_that.price);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String title,  String sku,  bool isCustomAmount,  String availability,  bool purchasable,  bool priceMayVary,  String? region,  String? denominationValue,  String? denominationCurrency,  Money? price)  $default,) {final _that = this;
switch (_that) {
case _VariantOffer():
return $default(_that.publicId,_that.title,_that.sku,_that.isCustomAmount,_that.availability,_that.purchasable,_that.priceMayVary,_that.region,_that.denominationValue,_that.denominationCurrency,_that.price);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String title,  String sku,  bool isCustomAmount,  String availability,  bool purchasable,  bool priceMayVary,  String? region,  String? denominationValue,  String? denominationCurrency,  Money? price)?  $default,) {final _that = this;
switch (_that) {
case _VariantOffer() when $default != null:
return $default(_that.publicId,_that.title,_that.sku,_that.isCustomAmount,_that.availability,_that.purchasable,_that.priceMayVary,_that.region,_that.denominationValue,_that.denominationCurrency,_that.price);case _:
  return null;

}
}

}

/// @nodoc


class _VariantOffer implements VariantOffer {
  const _VariantOffer({required this.publicId, required this.title, required this.sku, required this.isCustomAmount, required this.availability, required this.purchasable, required this.priceMayVary, this.region, this.denominationValue, this.denominationCurrency, this.price});
  

@override final  String publicId;
@override final  String title;
@override final  String sku;
@override final  bool isCustomAmount;
@override final  String availability;
@override final  bool purchasable;
@override final  bool priceMayVary;
@override final  String? region;
/// Decimal string (`10.000000`).
@override final  String? denominationValue;
@override final  String? denominationCurrency;
@override final  Money? price;

/// Create a copy of VariantOffer
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$VariantOfferCopyWith<_VariantOffer> get copyWith => __$VariantOfferCopyWithImpl<_VariantOffer>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _VariantOffer&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.title, title) || other.title == title)&&(identical(other.sku, sku) || other.sku == sku)&&(identical(other.isCustomAmount, isCustomAmount) || other.isCustomAmount == isCustomAmount)&&(identical(other.availability, availability) || other.availability == availability)&&(identical(other.purchasable, purchasable) || other.purchasable == purchasable)&&(identical(other.priceMayVary, priceMayVary) || other.priceMayVary == priceMayVary)&&(identical(other.region, region) || other.region == region)&&(identical(other.denominationValue, denominationValue) || other.denominationValue == denominationValue)&&(identical(other.denominationCurrency, denominationCurrency) || other.denominationCurrency == denominationCurrency)&&(identical(other.price, price) || other.price == price));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,title,sku,isCustomAmount,availability,purchasable,priceMayVary,region,denominationValue,denominationCurrency,price);
}

@override
String toString() {
    return 'VariantOffer(publicId: $publicId, title: $title, sku: $sku, isCustomAmount: $isCustomAmount, availability: $availability, purchasable: $purchasable, priceMayVary: $priceMayVary, region: $region, denominationValue: $denominationValue, denominationCurrency: $denominationCurrency, price: $price)';
}


}

/// @nodoc
abstract mixin class _$VariantOfferCopyWith<$Res> implements $VariantOfferCopyWith<$Res> {
  factory _$VariantOfferCopyWith(_VariantOffer value, $Res Function(_VariantOffer) _then) = __$VariantOfferCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String title, String sku, bool isCustomAmount, String availability, bool purchasable, bool priceMayVary, String? region, String? denominationValue, String? denominationCurrency, Money? price
});




}
/// @nodoc
class __$VariantOfferCopyWithImpl<$Res>
    implements _$VariantOfferCopyWith<$Res> {
  __$VariantOfferCopyWithImpl(this._self, this._then);

  final _VariantOffer _self;
  final $Res Function(_VariantOffer) _then;

/// Create a copy of VariantOffer
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? title = null,Object? sku = null,Object? isCustomAmount = null,Object? availability = null,Object? purchasable = null,Object? priceMayVary = null,Object? region = freezed,Object? denominationValue = freezed,Object? denominationCurrency = freezed,Object? price = freezed,}) {
  return _then(_VariantOffer(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,title: null == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String,sku: null == sku ? _self.sku : sku // ignore: cast_nullable_to_non_nullable
as String,isCustomAmount: null == isCustomAmount ? _self.isCustomAmount : isCustomAmount // ignore: cast_nullable_to_non_nullable
as bool,availability: null == availability ? _self.availability : availability // ignore: cast_nullable_to_non_nullable
as String,purchasable: null == purchasable ? _self.purchasable : purchasable // ignore: cast_nullable_to_non_nullable
as bool,priceMayVary: null == priceMayVary ? _self.priceMayVary : priceMayVary // ignore: cast_nullable_to_non_nullable
as bool,region: freezed == region ? _self.region : region // ignore: cast_nullable_to_non_nullable
as String?,denominationValue: freezed == denominationValue ? _self.denominationValue : denominationValue // ignore: cast_nullable_to_non_nullable
as String?,denominationCurrency: freezed == denominationCurrency ? _self.denominationCurrency : denominationCurrency // ignore: cast_nullable_to_non_nullable
as String?,price: freezed == price ? _self.price : price // ignore: cast_nullable_to_non_nullable
as Money?,
  ));
}


}

/// @nodoc
mixin _$ProductVariants {

 List<VariantOffer> get variants; String get displayCurrency;
/// Create a copy of ProductVariants
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ProductVariantsCopyWith<ProductVariants> get copyWith => _$ProductVariantsCopyWithImpl<ProductVariants>(this as ProductVariants, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as ProductVariants;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ProductVariants&&const DeepCollectionEquality().equals(other.variants, _this.variants)&&(identical(other.displayCurrency, _this.displayCurrency) || other.displayCurrency == _this.displayCurrency));
}


@override
int get hashCode {
  final _this = this as ProductVariants;
  return Object.hash(runtimeType,const DeepCollectionEquality().hash(_this.variants),_this.displayCurrency);
}

@override
String toString() {
  final _this = this as ProductVariants;
  return 'ProductVariants(variants: ${_this.variants}, displayCurrency: ${_this.displayCurrency})';
}


}

/// @nodoc
abstract mixin class $ProductVariantsCopyWith<$Res>  {
  factory $ProductVariantsCopyWith(ProductVariants value, $Res Function(ProductVariants) _then) = _$ProductVariantsCopyWithImpl;
@useResult
$Res call({
 List<VariantOffer> variants, String displayCurrency
});




}
/// @nodoc
class _$ProductVariantsCopyWithImpl<$Res>
    implements $ProductVariantsCopyWith<$Res> {
  _$ProductVariantsCopyWithImpl(this._self, this._then);

  final ProductVariants _self;
  final $Res Function(ProductVariants) _then;

/// Create a copy of ProductVariants
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? variants = null,Object? displayCurrency = null,}) {
  return _then(ProductVariants(
variants: null == variants ? _self.variants : variants // ignore: cast_nullable_to_non_nullable
as List<VariantOffer>,displayCurrency: null == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [ProductVariants].
extension ProductVariantsPatterns on ProductVariants {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ProductVariants value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ProductVariants() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ProductVariants value)  $default,){
final _that = this;
switch (_that) {
case _ProductVariants():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ProductVariants value)?  $default,){
final _that = this;
switch (_that) {
case _ProductVariants() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<VariantOffer> variants,  String displayCurrency)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ProductVariants() when $default != null:
return $default(_that.variants,_that.displayCurrency);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<VariantOffer> variants,  String displayCurrency)  $default,) {final _that = this;
switch (_that) {
case _ProductVariants():
return $default(_that.variants,_that.displayCurrency);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<VariantOffer> variants,  String displayCurrency)?  $default,) {final _that = this;
switch (_that) {
case _ProductVariants() when $default != null:
return $default(_that.variants,_that.displayCurrency);case _:
  return null;

}
}

}

/// @nodoc


class _ProductVariants implements ProductVariants {
  const _ProductVariants({required  List<VariantOffer> variants, required this.displayCurrency}): _variants = variants;
  

 final  List<VariantOffer> _variants;
@override List<VariantOffer> get variants {
  if (_variants is EqualUnmodifiableListView) return _variants;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_variants);
}

@override final  String displayCurrency;

/// Create a copy of ProductVariants
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ProductVariantsCopyWith<_ProductVariants> get copyWith => __$ProductVariantsCopyWithImpl<_ProductVariants>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ProductVariants&&const DeepCollectionEquality().equals(other.variants, _variants)&&(identical(other.displayCurrency, displayCurrency) || other.displayCurrency == displayCurrency));
}


@override
int get hashCode {
    return Object.hash(runtimeType,const DeepCollectionEquality().hash(_variants),displayCurrency);
}

@override
String toString() {
    return 'ProductVariants(variants: $variants, displayCurrency: $displayCurrency)';
}


}

/// @nodoc
abstract mixin class _$ProductVariantsCopyWith<$Res> implements $ProductVariantsCopyWith<$Res> {
  factory _$ProductVariantsCopyWith(_ProductVariants value, $Res Function(_ProductVariants) _then) = __$ProductVariantsCopyWithImpl;
@override @useResult
$Res call({
 List<VariantOffer> variants, String displayCurrency
});




}
/// @nodoc
class __$ProductVariantsCopyWithImpl<$Res>
    implements _$ProductVariantsCopyWith<$Res> {
  __$ProductVariantsCopyWithImpl(this._self, this._then);

  final _ProductVariants _self;
  final $Res Function(_ProductVariants) _then;

/// Create a copy of ProductVariants
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? variants = null,Object? displayCurrency = null,}) {
  return _then(_ProductVariants(
variants: null == variants ? _self._variants : variants // ignore: cast_nullable_to_non_nullable
as List<VariantOffer>,displayCurrency: null == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc
mixin _$ProductReview {

 int get id; int get rating; String get reviewer; bool get verifiedPurchase; DateTime get createdAt; String? get title; String? get body;
/// Create a copy of ProductReview
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ProductReviewCopyWith<ProductReview> get copyWith => _$ProductReviewCopyWithImpl<ProductReview>(this as ProductReview, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as ProductReview;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ProductReview&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.rating, _this.rating) || other.rating == _this.rating)&&(identical(other.reviewer, _this.reviewer) || other.reviewer == _this.reviewer)&&(identical(other.verifiedPurchase, _this.verifiedPurchase) || other.verifiedPurchase == _this.verifiedPurchase)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.body, _this.body) || other.body == _this.body));
}


@override
int get hashCode {
  final _this = this as ProductReview;
  return Object.hash(runtimeType,_this.id,_this.rating,_this.reviewer,_this.verifiedPurchase,_this.createdAt,_this.title,_this.body);
}

@override
String toString() {
  final _this = this as ProductReview;
  return 'ProductReview(id: ${_this.id}, rating: ${_this.rating}, reviewer: ${_this.reviewer}, verifiedPurchase: ${_this.verifiedPurchase}, createdAt: ${_this.createdAt}, title: ${_this.title}, body: ${_this.body})';
}


}

/// @nodoc
abstract mixin class $ProductReviewCopyWith<$Res>  {
  factory $ProductReviewCopyWith(ProductReview value, $Res Function(ProductReview) _then) = _$ProductReviewCopyWithImpl;
@useResult
$Res call({
 int id, int rating, String reviewer, bool verifiedPurchase, DateTime createdAt, String? title, String? body
});




}
/// @nodoc
class _$ProductReviewCopyWithImpl<$Res>
    implements $ProductReviewCopyWith<$Res> {
  _$ProductReviewCopyWithImpl(this._self, this._then);

  final ProductReview _self;
  final $Res Function(ProductReview) _then;

/// Create a copy of ProductReview
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? rating = null,Object? reviewer = null,Object? verifiedPurchase = null,Object? createdAt = null,Object? title = freezed,Object? body = freezed,}) {
  return _then(ProductReview(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as int,reviewer: null == reviewer ? _self.reviewer : reviewer // ignore: cast_nullable_to_non_nullable
as String,verifiedPurchase: null == verifiedPurchase ? _self.verifiedPurchase : verifiedPurchase // ignore: cast_nullable_to_non_nullable
as bool,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,body: freezed == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [ProductReview].
extension ProductReviewPatterns on ProductReview {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ProductReview value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ProductReview() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ProductReview value)  $default,){
final _that = this;
switch (_that) {
case _ProductReview():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ProductReview value)?  $default,){
final _that = this;
switch (_that) {
case _ProductReview() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  int rating,  String reviewer,  bool verifiedPurchase,  DateTime createdAt,  String? title,  String? body)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ProductReview() when $default != null:
return $default(_that.id,_that.rating,_that.reviewer,_that.verifiedPurchase,_that.createdAt,_that.title,_that.body);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  int rating,  String reviewer,  bool verifiedPurchase,  DateTime createdAt,  String? title,  String? body)  $default,) {final _that = this;
switch (_that) {
case _ProductReview():
return $default(_that.id,_that.rating,_that.reviewer,_that.verifiedPurchase,_that.createdAt,_that.title,_that.body);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  int rating,  String reviewer,  bool verifiedPurchase,  DateTime createdAt,  String? title,  String? body)?  $default,) {final _that = this;
switch (_that) {
case _ProductReview() when $default != null:
return $default(_that.id,_that.rating,_that.reviewer,_that.verifiedPurchase,_that.createdAt,_that.title,_that.body);case _:
  return null;

}
}

}

/// @nodoc


class _ProductReview implements ProductReview {
  const _ProductReview({required this.id, required this.rating, required this.reviewer, required this.verifiedPurchase, required this.createdAt, this.title, this.body});
  

@override final  int id;
@override final  int rating;
@override final  String reviewer;
@override final  bool verifiedPurchase;
@override final  DateTime createdAt;
@override final  String? title;
@override final  String? body;

/// Create a copy of ProductReview
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ProductReviewCopyWith<_ProductReview> get copyWith => __$ProductReviewCopyWithImpl<_ProductReview>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ProductReview&&(identical(other.id, id) || other.id == id)&&(identical(other.rating, rating) || other.rating == rating)&&(identical(other.reviewer, reviewer) || other.reviewer == reviewer)&&(identical(other.verifiedPurchase, verifiedPurchase) || other.verifiedPurchase == verifiedPurchase)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.title, title) || other.title == title)&&(identical(other.body, body) || other.body == body));
}


@override
int get hashCode {
    return Object.hash(runtimeType,id,rating,reviewer,verifiedPurchase,createdAt,title,body);
}

@override
String toString() {
    return 'ProductReview(id: $id, rating: $rating, reviewer: $reviewer, verifiedPurchase: $verifiedPurchase, createdAt: $createdAt, title: $title, body: $body)';
}


}

/// @nodoc
abstract mixin class _$ProductReviewCopyWith<$Res> implements $ProductReviewCopyWith<$Res> {
  factory _$ProductReviewCopyWith(_ProductReview value, $Res Function(_ProductReview) _then) = __$ProductReviewCopyWithImpl;
@override @useResult
$Res call({
 int id, int rating, String reviewer, bool verifiedPurchase, DateTime createdAt, String? title, String? body
});




}
/// @nodoc
class __$ProductReviewCopyWithImpl<$Res>
    implements _$ProductReviewCopyWith<$Res> {
  __$ProductReviewCopyWithImpl(this._self, this._then);

  final _ProductReview _self;
  final $Res Function(_ProductReview) _then;

/// Create a copy of ProductReview
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? rating = null,Object? reviewer = null,Object? verifiedPurchase = null,Object? createdAt = null,Object? title = freezed,Object? body = freezed,}) {
  return _then(_ProductReview(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as int,reviewer: null == reviewer ? _self.reviewer : reviewer // ignore: cast_nullable_to_non_nullable
as String,verifiedPurchase: null == verifiedPurchase ? _self.verifiedPurchase : verifiedPurchase // ignore: cast_nullable_to_non_nullable
as bool,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,body: freezed == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc
mixin _$ProductReviewsPage {

 List<ProductReview> get reviews; PageInfo get page; String get ratingAvg; int get ratingCount;
/// Create a copy of ProductReviewsPage
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ProductReviewsPageCopyWith<ProductReviewsPage> get copyWith => _$ProductReviewsPageCopyWithImpl<ProductReviewsPage>(this as ProductReviewsPage, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as ProductReviewsPage;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ProductReviewsPage&&const DeepCollectionEquality().equals(other.reviews, _this.reviews)&&(identical(other.page, _this.page) || other.page == _this.page)&&(identical(other.ratingAvg, _this.ratingAvg) || other.ratingAvg == _this.ratingAvg)&&(identical(other.ratingCount, _this.ratingCount) || other.ratingCount == _this.ratingCount));
}


@override
int get hashCode {
  final _this = this as ProductReviewsPage;
  return Object.hash(runtimeType,const DeepCollectionEquality().hash(_this.reviews),_this.page,_this.ratingAvg,_this.ratingCount);
}

@override
String toString() {
  final _this = this as ProductReviewsPage;
  return 'ProductReviewsPage(reviews: ${_this.reviews}, page: ${_this.page}, ratingAvg: ${_this.ratingAvg}, ratingCount: ${_this.ratingCount})';
}


}

/// @nodoc
abstract mixin class $ProductReviewsPageCopyWith<$Res>  {
  factory $ProductReviewsPageCopyWith(ProductReviewsPage value, $Res Function(ProductReviewsPage) _then) = _$ProductReviewsPageCopyWithImpl;
@useResult
$Res call({
 List<ProductReview> reviews, PageInfo page, String ratingAvg, int ratingCount
});


$PageInfoCopyWith<$Res> get page;

}
/// @nodoc
class _$ProductReviewsPageCopyWithImpl<$Res>
    implements $ProductReviewsPageCopyWith<$Res> {
  _$ProductReviewsPageCopyWithImpl(this._self, this._then);

  final ProductReviewsPage _self;
  final $Res Function(ProductReviewsPage) _then;

/// Create a copy of ProductReviewsPage
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? reviews = null,Object? page = null,Object? ratingAvg = null,Object? ratingCount = null,}) {
  return _then(ProductReviewsPage(
reviews: null == reviews ? _self.reviews : reviews // ignore: cast_nullable_to_non_nullable
as List<ProductReview>,page: null == page ? _self.page : page // ignore: cast_nullable_to_non_nullable
as PageInfo,ratingAvg: null == ratingAvg ? _self.ratingAvg : ratingAvg // ignore: cast_nullable_to_non_nullable
as String,ratingCount: null == ratingCount ? _self.ratingCount : ratingCount // ignore: cast_nullable_to_non_nullable
as int,
  ));
}
/// Create a copy of ProductReviewsPage
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PageInfoCopyWith<$Res> get page {
  
  return $PageInfoCopyWith<$Res>(_self.page, (value) {
    return _then(_self.copyWith(page: value));
  });
}
}


/// Adds pattern-matching-related methods to [ProductReviewsPage].
extension ProductReviewsPagePatterns on ProductReviewsPage {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ProductReviewsPage value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ProductReviewsPage() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ProductReviewsPage value)  $default,){
final _that = this;
switch (_that) {
case _ProductReviewsPage():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ProductReviewsPage value)?  $default,){
final _that = this;
switch (_that) {
case _ProductReviewsPage() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<ProductReview> reviews,  PageInfo page,  String ratingAvg,  int ratingCount)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ProductReviewsPage() when $default != null:
return $default(_that.reviews,_that.page,_that.ratingAvg,_that.ratingCount);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<ProductReview> reviews,  PageInfo page,  String ratingAvg,  int ratingCount)  $default,) {final _that = this;
switch (_that) {
case _ProductReviewsPage():
return $default(_that.reviews,_that.page,_that.ratingAvg,_that.ratingCount);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<ProductReview> reviews,  PageInfo page,  String ratingAvg,  int ratingCount)?  $default,) {final _that = this;
switch (_that) {
case _ProductReviewsPage() when $default != null:
return $default(_that.reviews,_that.page,_that.ratingAvg,_that.ratingCount);case _:
  return null;

}
}

}

/// @nodoc


class _ProductReviewsPage implements ProductReviewsPage {
  const _ProductReviewsPage({required  List<ProductReview> reviews, required this.page, required this.ratingAvg, required this.ratingCount}): _reviews = reviews;
  

 final  List<ProductReview> _reviews;
@override List<ProductReview> get reviews {
  if (_reviews is EqualUnmodifiableListView) return _reviews;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_reviews);
}

@override final  PageInfo page;
@override final  String ratingAvg;
@override final  int ratingCount;

/// Create a copy of ProductReviewsPage
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ProductReviewsPageCopyWith<_ProductReviewsPage> get copyWith => __$ProductReviewsPageCopyWithImpl<_ProductReviewsPage>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ProductReviewsPage&&const DeepCollectionEquality().equals(other.reviews, _reviews)&&(identical(other.page, page) || other.page == page)&&(identical(other.ratingAvg, ratingAvg) || other.ratingAvg == ratingAvg)&&(identical(other.ratingCount, ratingCount) || other.ratingCount == ratingCount));
}


@override
int get hashCode {
    return Object.hash(runtimeType,const DeepCollectionEquality().hash(_reviews),page,ratingAvg,ratingCount);
}

@override
String toString() {
    return 'ProductReviewsPage(reviews: $reviews, page: $page, ratingAvg: $ratingAvg, ratingCount: $ratingCount)';
}


}

/// @nodoc
abstract mixin class _$ProductReviewsPageCopyWith<$Res> implements $ProductReviewsPageCopyWith<$Res> {
  factory _$ProductReviewsPageCopyWith(_ProductReviewsPage value, $Res Function(_ProductReviewsPage) _then) = __$ProductReviewsPageCopyWithImpl;
@override @useResult
$Res call({
 List<ProductReview> reviews, PageInfo page, String ratingAvg, int ratingCount
});


@override $PageInfoCopyWith<$Res> get page;

}
/// @nodoc
class __$ProductReviewsPageCopyWithImpl<$Res>
    implements _$ProductReviewsPageCopyWith<$Res> {
  __$ProductReviewsPageCopyWithImpl(this._self, this._then);

  final _ProductReviewsPage _self;
  final $Res Function(_ProductReviewsPage) _then;

/// Create a copy of ProductReviewsPage
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? reviews = null,Object? page = null,Object? ratingAvg = null,Object? ratingCount = null,}) {
  return _then(_ProductReviewsPage(
reviews: null == reviews ? _self._reviews : reviews // ignore: cast_nullable_to_non_nullable
as List<ProductReview>,page: null == page ? _self.page : page // ignore: cast_nullable_to_non_nullable
as PageInfo,ratingAvg: null == ratingAvg ? _self.ratingAvg : ratingAvg // ignore: cast_nullable_to_non_nullable
as String,ratingCount: null == ratingCount ? _self.ratingCount : ratingCount // ignore: cast_nullable_to_non_nullable
as int,
  ));
}

/// Create a copy of ProductReviewsPage
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PageInfoCopyWith<$Res> get page {
  
  return $PageInfoCopyWith<$Res>(_self.page, (value) {
    return _then(_self.copyWith(page: value));
  });
}
}

// dart format on
