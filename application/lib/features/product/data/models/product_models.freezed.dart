// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'product_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;

/// @nodoc
mixin _$ProductDetailDto {

 String get publicId; String get name; String get slug; String get deliveryType; bool get priceMayVary; bool get isFeatured; List<MediaImageDto> get images; List<RegionDto> get regions; RatingDto get rating; VariantsSummaryDto get variantsSummary; List<DeliveryFieldDto> get deliveryFields; SeoDto get seo; String? get shortDescription; NamedSlugDto? get platform; NamedSlugDto? get category; MediaImageDto? get image;@NullableMoneyConverter() Money? get priceFrom; String? get ratingAvg; String? get sku; String? get description; String? get instructions; String? get terms; int? get deliveryTimeMinutes; int? get maxQuantityPerOrder; String? get availability;
/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ProductDetailDtoCopyWith<ProductDetailDto> get copyWith => _$ProductDetailDtoCopyWithImpl<ProductDetailDto>(this as ProductDetailDto, _$identity);

  /// Serializes this ProductDetailDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as ProductDetailDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ProductDetailDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug)&&(identical(other.deliveryType, _this.deliveryType) || other.deliveryType == _this.deliveryType)&&(identical(other.priceMayVary, _this.priceMayVary) || other.priceMayVary == _this.priceMayVary)&&(identical(other.isFeatured, _this.isFeatured) || other.isFeatured == _this.isFeatured)&&const DeepCollectionEquality().equals(other.images, _this.images)&&const DeepCollectionEquality().equals(other.regions, _this.regions)&&(identical(other.rating, _this.rating) || other.rating == _this.rating)&&(identical(other.variantsSummary, _this.variantsSummary) || other.variantsSummary == _this.variantsSummary)&&const DeepCollectionEquality().equals(other.deliveryFields, _this.deliveryFields)&&(identical(other.seo, _this.seo) || other.seo == _this.seo)&&(identical(other.shortDescription, _this.shortDescription) || other.shortDescription == _this.shortDescription)&&(identical(other.platform, _this.platform) || other.platform == _this.platform)&&(identical(other.category, _this.category) || other.category == _this.category)&&(identical(other.image, _this.image) || other.image == _this.image)&&(identical(other.priceFrom, _this.priceFrom) || other.priceFrom == _this.priceFrom)&&(identical(other.ratingAvg, _this.ratingAvg) || other.ratingAvg == _this.ratingAvg)&&(identical(other.sku, _this.sku) || other.sku == _this.sku)&&(identical(other.description, _this.description) || other.description == _this.description)&&(identical(other.instructions, _this.instructions) || other.instructions == _this.instructions)&&(identical(other.terms, _this.terms) || other.terms == _this.terms)&&(identical(other.deliveryTimeMinutes, _this.deliveryTimeMinutes) || other.deliveryTimeMinutes == _this.deliveryTimeMinutes)&&(identical(other.maxQuantityPerOrder, _this.maxQuantityPerOrder) || other.maxQuantityPerOrder == _this.maxQuantityPerOrder)&&(identical(other.availability, _this.availability) || other.availability == _this.availability));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as ProductDetailDto;
  return Object.hashAll([runtimeType,_this.publicId,_this.name,_this.slug,_this.deliveryType,_this.priceMayVary,_this.isFeatured,const DeepCollectionEquality().hash(_this.images),const DeepCollectionEquality().hash(_this.regions),_this.rating,_this.variantsSummary,const DeepCollectionEquality().hash(_this.deliveryFields),_this.seo,_this.shortDescription,_this.platform,_this.category,_this.image,_this.priceFrom,_this.ratingAvg,_this.sku,_this.description,_this.instructions,_this.terms,_this.deliveryTimeMinutes,_this.maxQuantityPerOrder,_this.availability]);
}

@override
String toString() {
  final _this = this as ProductDetailDto;
  return 'ProductDetailDto(publicId: ${_this.publicId}, name: ${_this.name}, slug: ${_this.slug}, deliveryType: ${_this.deliveryType}, priceMayVary: ${_this.priceMayVary}, isFeatured: ${_this.isFeatured}, images: ${_this.images}, regions: ${_this.regions}, rating: ${_this.rating}, variantsSummary: ${_this.variantsSummary}, deliveryFields: ${_this.deliveryFields}, seo: ${_this.seo}, shortDescription: ${_this.shortDescription}, platform: ${_this.platform}, category: ${_this.category}, image: ${_this.image}, priceFrom: ${_this.priceFrom}, ratingAvg: ${_this.ratingAvg}, sku: ${_this.sku}, description: ${_this.description}, instructions: ${_this.instructions}, terms: ${_this.terms}, deliveryTimeMinutes: ${_this.deliveryTimeMinutes}, maxQuantityPerOrder: ${_this.maxQuantityPerOrder}, availability: ${_this.availability})';
}


}

/// @nodoc
abstract mixin class $ProductDetailDtoCopyWith<$Res>  {
  factory $ProductDetailDtoCopyWith(ProductDetailDto value, $Res Function(ProductDetailDto) _then) = _$ProductDetailDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String name, String slug, String deliveryType, bool priceMayVary, bool isFeatured, List<MediaImageDto> images, List<RegionDto> regions, RatingDto rating, VariantsSummaryDto variantsSummary, List<DeliveryFieldDto> deliveryFields, SeoDto seo, String? shortDescription, NamedSlugDto? platform, NamedSlugDto? category, MediaImageDto? image,@NullableMoneyConverter() Money? priceFrom, String? ratingAvg, String? sku, String? description, String? instructions, String? terms, int? deliveryTimeMinutes, int? maxQuantityPerOrder, String? availability
});


$RatingDtoCopyWith<$Res> get rating;$VariantsSummaryDtoCopyWith<$Res> get variantsSummary;$SeoDtoCopyWith<$Res> get seo;$NamedSlugDtoCopyWith<$Res>? get platform;$NamedSlugDtoCopyWith<$Res>? get category;$MediaImageDtoCopyWith<$Res>? get image;

}
/// @nodoc
class _$ProductDetailDtoCopyWithImpl<$Res>
    implements $ProductDetailDtoCopyWith<$Res> {
  _$ProductDetailDtoCopyWithImpl(this._self, this._then);

  final ProductDetailDto _self;
  final $Res Function(ProductDetailDto) _then;

/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? name = null,Object? slug = null,Object? deliveryType = null,Object? priceMayVary = null,Object? isFeatured = null,Object? images = null,Object? regions = null,Object? rating = null,Object? variantsSummary = null,Object? deliveryFields = null,Object? seo = null,Object? shortDescription = freezed,Object? platform = freezed,Object? category = freezed,Object? image = freezed,Object? priceFrom = freezed,Object? ratingAvg = freezed,Object? sku = freezed,Object? description = freezed,Object? instructions = freezed,Object? terms = freezed,Object? deliveryTimeMinutes = freezed,Object? maxQuantityPerOrder = freezed,Object? availability = freezed,}) {
  return _then(ProductDetailDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,deliveryType: null == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String,priceMayVary: null == priceMayVary ? _self.priceMayVary : priceMayVary // ignore: cast_nullable_to_non_nullable
as bool,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,images: null == images ? _self.images : images // ignore: cast_nullable_to_non_nullable
as List<MediaImageDto>,regions: null == regions ? _self.regions : regions // ignore: cast_nullable_to_non_nullable
as List<RegionDto>,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as RatingDto,variantsSummary: null == variantsSummary ? _self.variantsSummary : variantsSummary // ignore: cast_nullable_to_non_nullable
as VariantsSummaryDto,deliveryFields: null == deliveryFields ? _self.deliveryFields : deliveryFields // ignore: cast_nullable_to_non_nullable
as List<DeliveryFieldDto>,seo: null == seo ? _self.seo : seo // ignore: cast_nullable_to_non_nullable
as SeoDto,shortDescription: freezed == shortDescription ? _self.shortDescription : shortDescription // ignore: cast_nullable_to_non_nullable
as String?,platform: freezed == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as NamedSlugDto?,category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as NamedSlugDto?,image: freezed == image ? _self.image : image // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,priceFrom: freezed == priceFrom ? _self.priceFrom : priceFrom // ignore: cast_nullable_to_non_nullable
as Money?,ratingAvg: freezed == ratingAvg ? _self.ratingAvg : ratingAvg // ignore: cast_nullable_to_non_nullable
as String?,sku: freezed == sku ? _self.sku : sku // ignore: cast_nullable_to_non_nullable
as String?,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,instructions: freezed == instructions ? _self.instructions : instructions // ignore: cast_nullable_to_non_nullable
as String?,terms: freezed == terms ? _self.terms : terms // ignore: cast_nullable_to_non_nullable
as String?,deliveryTimeMinutes: freezed == deliveryTimeMinutes ? _self.deliveryTimeMinutes : deliveryTimeMinutes // ignore: cast_nullable_to_non_nullable
as int?,maxQuantityPerOrder: freezed == maxQuantityPerOrder ? _self.maxQuantityPerOrder : maxQuantityPerOrder // ignore: cast_nullable_to_non_nullable
as int?,availability: freezed == availability ? _self.availability : availability // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}
/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$RatingDtoCopyWith<$Res> get rating {
  
  return $RatingDtoCopyWith<$Res>(_self.rating, (value) {
    return _then(_self.copyWith(rating: value));
  });
}/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$VariantsSummaryDtoCopyWith<$Res> get variantsSummary {
  
  return $VariantsSummaryDtoCopyWith<$Res>(_self.variantsSummary, (value) {
    return _then(_self.copyWith(variantsSummary: value));
  });
}/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$SeoDtoCopyWith<$Res> get seo {
  
  return $SeoDtoCopyWith<$Res>(_self.seo, (value) {
    return _then(_self.copyWith(seo: value));
  });
}/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$NamedSlugDtoCopyWith<$Res>? get platform {
    if (_self.platform == null) {
    return null;
  }

  return $NamedSlugDtoCopyWith<$Res>(_self.platform!, (value) {
    return _then(_self.copyWith(platform: value));
  });
}/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$NamedSlugDtoCopyWith<$Res>? get category {
    if (_self.category == null) {
    return null;
  }

  return $NamedSlugDtoCopyWith<$Res>(_self.category!, (value) {
    return _then(_self.copyWith(category: value));
  });
}/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get image {
    if (_self.image == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.image!, (value) {
    return _then(_self.copyWith(image: value));
  });
}
}


/// Adds pattern-matching-related methods to [ProductDetailDto].
extension ProductDetailDtoPatterns on ProductDetailDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ProductDetailDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ProductDetailDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ProductDetailDto value)  $default,){
final _that = this;
switch (_that) {
case _ProductDetailDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ProductDetailDto value)?  $default,){
final _that = this;
switch (_that) {
case _ProductDetailDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String name,  String slug,  String deliveryType,  bool priceMayVary,  bool isFeatured,  List<MediaImageDto> images,  List<RegionDto> regions,  RatingDto rating,  VariantsSummaryDto variantsSummary,  List<DeliveryFieldDto> deliveryFields,  SeoDto seo,  String? shortDescription,  NamedSlugDto? platform,  NamedSlugDto? category,  MediaImageDto? image, @NullableMoneyConverter()  Money? priceFrom,  String? ratingAvg,  String? sku,  String? description,  String? instructions,  String? terms,  int? deliveryTimeMinutes,  int? maxQuantityPerOrder,  String? availability)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _ProductDetailDto() when $default != null:
return $default(_that.publicId,_that.name,_that.slug,_that.deliveryType,_that.priceMayVary,_that.isFeatured,_that.images,_that.regions,_that.rating,_that.variantsSummary,_that.deliveryFields,_that.seo,_that.shortDescription,_that.platform,_that.category,_that.image,_that.priceFrom,_that.ratingAvg,_that.sku,_that.description,_that.instructions,_that.terms,_that.deliveryTimeMinutes,_that.maxQuantityPerOrder,_that.availability);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String name,  String slug,  String deliveryType,  bool priceMayVary,  bool isFeatured,  List<MediaImageDto> images,  List<RegionDto> regions,  RatingDto rating,  VariantsSummaryDto variantsSummary,  List<DeliveryFieldDto> deliveryFields,  SeoDto seo,  String? shortDescription,  NamedSlugDto? platform,  NamedSlugDto? category,  MediaImageDto? image, @NullableMoneyConverter()  Money? priceFrom,  String? ratingAvg,  String? sku,  String? description,  String? instructions,  String? terms,  int? deliveryTimeMinutes,  int? maxQuantityPerOrder,  String? availability)  $default,) {final _that = this;
switch (_that) {
case _ProductDetailDto():
return $default(_that.publicId,_that.name,_that.slug,_that.deliveryType,_that.priceMayVary,_that.isFeatured,_that.images,_that.regions,_that.rating,_that.variantsSummary,_that.deliveryFields,_that.seo,_that.shortDescription,_that.platform,_that.category,_that.image,_that.priceFrom,_that.ratingAvg,_that.sku,_that.description,_that.instructions,_that.terms,_that.deliveryTimeMinutes,_that.maxQuantityPerOrder,_that.availability);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String name,  String slug,  String deliveryType,  bool priceMayVary,  bool isFeatured,  List<MediaImageDto> images,  List<RegionDto> regions,  RatingDto rating,  VariantsSummaryDto variantsSummary,  List<DeliveryFieldDto> deliveryFields,  SeoDto seo,  String? shortDescription,  NamedSlugDto? platform,  NamedSlugDto? category,  MediaImageDto? image, @NullableMoneyConverter()  Money? priceFrom,  String? ratingAvg,  String? sku,  String? description,  String? instructions,  String? terms,  int? deliveryTimeMinutes,  int? maxQuantityPerOrder,  String? availability)?  $default,) {final _that = this;
switch (_that) {
case _ProductDetailDto() when $default != null:
return $default(_that.publicId,_that.name,_that.slug,_that.deliveryType,_that.priceMayVary,_that.isFeatured,_that.images,_that.regions,_that.rating,_that.variantsSummary,_that.deliveryFields,_that.seo,_that.shortDescription,_that.platform,_that.category,_that.image,_that.priceFrom,_that.ratingAvg,_that.sku,_that.description,_that.instructions,_that.terms,_that.deliveryTimeMinutes,_that.maxQuantityPerOrder,_that.availability);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _ProductDetailDto extends ProductDetailDto {
  const _ProductDetailDto({required this.publicId, required this.name, required this.slug, required this.deliveryType, required this.priceMayVary, required this.isFeatured, required  List<MediaImageDto> images, required  List<RegionDto> regions, required this.rating, required this.variantsSummary, required  List<DeliveryFieldDto> deliveryFields, required this.seo, this.shortDescription, this.platform, this.category, this.image, @NullableMoneyConverter() this.priceFrom, this.ratingAvg, this.sku, this.description, this.instructions, this.terms, this.deliveryTimeMinutes, this.maxQuantityPerOrder, this.availability}): _images = images,_regions = regions,_deliveryFields = deliveryFields,super._();
  factory _ProductDetailDto.fromJson(Map<String, dynamic> json) => _$ProductDetailDtoFromJson(json);

@override final  String publicId;
@override final  String name;
@override final  String slug;
@override final  String deliveryType;
@override final  bool priceMayVary;
@override final  bool isFeatured;
 final  List<MediaImageDto> _images;
@override List<MediaImageDto> get images {
  if (_images is EqualUnmodifiableListView) return _images;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_images);
}

 final  List<RegionDto> _regions;
@override List<RegionDto> get regions {
  if (_regions is EqualUnmodifiableListView) return _regions;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_regions);
}

@override final  RatingDto rating;
@override final  VariantsSummaryDto variantsSummary;
 final  List<DeliveryFieldDto> _deliveryFields;
@override List<DeliveryFieldDto> get deliveryFields {
  if (_deliveryFields is EqualUnmodifiableListView) return _deliveryFields;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_deliveryFields);
}

@override final  SeoDto seo;
@override final  String? shortDescription;
@override final  NamedSlugDto? platform;
@override final  NamedSlugDto? category;
@override final  MediaImageDto? image;
@override@NullableMoneyConverter() final  Money? priceFrom;
@override final  String? ratingAvg;
@override final  String? sku;
@override final  String? description;
@override final  String? instructions;
@override final  String? terms;
@override final  int? deliveryTimeMinutes;
@override final  int? maxQuantityPerOrder;
@override final  String? availability;

/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ProductDetailDtoCopyWith<_ProductDetailDto> get copyWith => __$ProductDetailDtoCopyWithImpl<_ProductDetailDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$ProductDetailDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ProductDetailDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug)&&(identical(other.deliveryType, deliveryType) || other.deliveryType == deliveryType)&&(identical(other.priceMayVary, priceMayVary) || other.priceMayVary == priceMayVary)&&(identical(other.isFeatured, isFeatured) || other.isFeatured == isFeatured)&&const DeepCollectionEquality().equals(other.images, _images)&&const DeepCollectionEquality().equals(other.regions, _regions)&&(identical(other.rating, rating) || other.rating == rating)&&(identical(other.variantsSummary, variantsSummary) || other.variantsSummary == variantsSummary)&&const DeepCollectionEquality().equals(other.deliveryFields, _deliveryFields)&&(identical(other.seo, seo) || other.seo == seo)&&(identical(other.shortDescription, shortDescription) || other.shortDescription == shortDescription)&&(identical(other.platform, platform) || other.platform == platform)&&(identical(other.category, category) || other.category == category)&&(identical(other.image, image) || other.image == image)&&(identical(other.priceFrom, priceFrom) || other.priceFrom == priceFrom)&&(identical(other.ratingAvg, ratingAvg) || other.ratingAvg == ratingAvg)&&(identical(other.sku, sku) || other.sku == sku)&&(identical(other.description, description) || other.description == description)&&(identical(other.instructions, instructions) || other.instructions == instructions)&&(identical(other.terms, terms) || other.terms == terms)&&(identical(other.deliveryTimeMinutes, deliveryTimeMinutes) || other.deliveryTimeMinutes == deliveryTimeMinutes)&&(identical(other.maxQuantityPerOrder, maxQuantityPerOrder) || other.maxQuantityPerOrder == maxQuantityPerOrder)&&(identical(other.availability, availability) || other.availability == availability));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hashAll([runtimeType,publicId,name,slug,deliveryType,priceMayVary,isFeatured,const DeepCollectionEquality().hash(_images),const DeepCollectionEquality().hash(_regions),rating,variantsSummary,const DeepCollectionEquality().hash(_deliveryFields),seo,shortDescription,platform,category,image,priceFrom,ratingAvg,sku,description,instructions,terms,deliveryTimeMinutes,maxQuantityPerOrder,availability]);
}

@override
String toString() {
    return 'ProductDetailDto(publicId: $publicId, name: $name, slug: $slug, deliveryType: $deliveryType, priceMayVary: $priceMayVary, isFeatured: $isFeatured, images: $images, regions: $regions, rating: $rating, variantsSummary: $variantsSummary, deliveryFields: $deliveryFields, seo: $seo, shortDescription: $shortDescription, platform: $platform, category: $category, image: $image, priceFrom: $priceFrom, ratingAvg: $ratingAvg, sku: $sku, description: $description, instructions: $instructions, terms: $terms, deliveryTimeMinutes: $deliveryTimeMinutes, maxQuantityPerOrder: $maxQuantityPerOrder, availability: $availability)';
}


}

/// @nodoc
abstract mixin class _$ProductDetailDtoCopyWith<$Res> implements $ProductDetailDtoCopyWith<$Res> {
  factory _$ProductDetailDtoCopyWith(_ProductDetailDto value, $Res Function(_ProductDetailDto) _then) = __$ProductDetailDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String name, String slug, String deliveryType, bool priceMayVary, bool isFeatured, List<MediaImageDto> images, List<RegionDto> regions, RatingDto rating, VariantsSummaryDto variantsSummary, List<DeliveryFieldDto> deliveryFields, SeoDto seo, String? shortDescription, NamedSlugDto? platform, NamedSlugDto? category, MediaImageDto? image,@NullableMoneyConverter() Money? priceFrom, String? ratingAvg, String? sku, String? description, String? instructions, String? terms, int? deliveryTimeMinutes, int? maxQuantityPerOrder, String? availability
});


@override $RatingDtoCopyWith<$Res> get rating;@override $VariantsSummaryDtoCopyWith<$Res> get variantsSummary;@override $SeoDtoCopyWith<$Res> get seo;@override $NamedSlugDtoCopyWith<$Res>? get platform;@override $NamedSlugDtoCopyWith<$Res>? get category;@override $MediaImageDtoCopyWith<$Res>? get image;

}
/// @nodoc
class __$ProductDetailDtoCopyWithImpl<$Res>
    implements _$ProductDetailDtoCopyWith<$Res> {
  __$ProductDetailDtoCopyWithImpl(this._self, this._then);

  final _ProductDetailDto _self;
  final $Res Function(_ProductDetailDto) _then;

/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? name = null,Object? slug = null,Object? deliveryType = null,Object? priceMayVary = null,Object? isFeatured = null,Object? images = null,Object? regions = null,Object? rating = null,Object? variantsSummary = null,Object? deliveryFields = null,Object? seo = null,Object? shortDescription = freezed,Object? platform = freezed,Object? category = freezed,Object? image = freezed,Object? priceFrom = freezed,Object? ratingAvg = freezed,Object? sku = freezed,Object? description = freezed,Object? instructions = freezed,Object? terms = freezed,Object? deliveryTimeMinutes = freezed,Object? maxQuantityPerOrder = freezed,Object? availability = freezed,}) {
  return _then(_ProductDetailDto(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,deliveryType: null == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String,priceMayVary: null == priceMayVary ? _self.priceMayVary : priceMayVary // ignore: cast_nullable_to_non_nullable
as bool,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,images: null == images ? _self._images : images // ignore: cast_nullable_to_non_nullable
as List<MediaImageDto>,regions: null == regions ? _self._regions : regions // ignore: cast_nullable_to_non_nullable
as List<RegionDto>,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as RatingDto,variantsSummary: null == variantsSummary ? _self.variantsSummary : variantsSummary // ignore: cast_nullable_to_non_nullable
as VariantsSummaryDto,deliveryFields: null == deliveryFields ? _self._deliveryFields : deliveryFields // ignore: cast_nullable_to_non_nullable
as List<DeliveryFieldDto>,seo: null == seo ? _self.seo : seo // ignore: cast_nullable_to_non_nullable
as SeoDto,shortDescription: freezed == shortDescription ? _self.shortDescription : shortDescription // ignore: cast_nullable_to_non_nullable
as String?,platform: freezed == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as NamedSlugDto?,category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as NamedSlugDto?,image: freezed == image ? _self.image : image // ignore: cast_nullable_to_non_nullable
as MediaImageDto?,priceFrom: freezed == priceFrom ? _self.priceFrom : priceFrom // ignore: cast_nullable_to_non_nullable
as Money?,ratingAvg: freezed == ratingAvg ? _self.ratingAvg : ratingAvg // ignore: cast_nullable_to_non_nullable
as String?,sku: freezed == sku ? _self.sku : sku // ignore: cast_nullable_to_non_nullable
as String?,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,instructions: freezed == instructions ? _self.instructions : instructions // ignore: cast_nullable_to_non_nullable
as String?,terms: freezed == terms ? _self.terms : terms // ignore: cast_nullable_to_non_nullable
as String?,deliveryTimeMinutes: freezed == deliveryTimeMinutes ? _self.deliveryTimeMinutes : deliveryTimeMinutes // ignore: cast_nullable_to_non_nullable
as int?,maxQuantityPerOrder: freezed == maxQuantityPerOrder ? _self.maxQuantityPerOrder : maxQuantityPerOrder // ignore: cast_nullable_to_non_nullable
as int?,availability: freezed == availability ? _self.availability : availability // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$RatingDtoCopyWith<$Res> get rating {
  
  return $RatingDtoCopyWith<$Res>(_self.rating, (value) {
    return _then(_self.copyWith(rating: value));
  });
}/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$VariantsSummaryDtoCopyWith<$Res> get variantsSummary {
  
  return $VariantsSummaryDtoCopyWith<$Res>(_self.variantsSummary, (value) {
    return _then(_self.copyWith(variantsSummary: value));
  });
}/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$SeoDtoCopyWith<$Res> get seo {
  
  return $SeoDtoCopyWith<$Res>(_self.seo, (value) {
    return _then(_self.copyWith(seo: value));
  });
}/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$NamedSlugDtoCopyWith<$Res>? get platform {
    if (_self.platform == null) {
    return null;
  }

  return $NamedSlugDtoCopyWith<$Res>(_self.platform!, (value) {
    return _then(_self.copyWith(platform: value));
  });
}/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$NamedSlugDtoCopyWith<$Res>? get category {
    if (_self.category == null) {
    return null;
  }

  return $NamedSlugDtoCopyWith<$Res>(_self.category!, (value) {
    return _then(_self.copyWith(category: value));
  });
}/// Create a copy of ProductDetailDto
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageDtoCopyWith<$Res>? get image {
    if (_self.image == null) {
    return null;
  }

  return $MediaImageDtoCopyWith<$Res>(_self.image!, (value) {
    return _then(_self.copyWith(image: value));
  });
}
}


/// @nodoc
mixin _$RegionDto {

 String get code; String get name;
/// Create a copy of RegionDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RegionDtoCopyWith<RegionDto> get copyWith => _$RegionDtoCopyWithImpl<RegionDto>(this as RegionDto, _$identity);

  /// Serializes this RegionDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as RegionDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is RegionDto&&(identical(other.code, _this.code) || other.code == _this.code)&&(identical(other.name, _this.name) || other.name == _this.name));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as RegionDto;
  return Object.hash(runtimeType,_this.code,_this.name);
}

@override
String toString() {
  final _this = this as RegionDto;
  return 'RegionDto(code: ${_this.code}, name: ${_this.name})';
}


}

/// @nodoc
abstract mixin class $RegionDtoCopyWith<$Res>  {
  factory $RegionDtoCopyWith(RegionDto value, $Res Function(RegionDto) _then) = _$RegionDtoCopyWithImpl;
@useResult
$Res call({
 String code, String name
});




}
/// @nodoc
class _$RegionDtoCopyWithImpl<$Res>
    implements $RegionDtoCopyWith<$Res> {
  _$RegionDtoCopyWithImpl(this._self, this._then);

  final RegionDto _self;
  final $Res Function(RegionDto) _then;

/// Create a copy of RegionDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? code = null,Object? name = null,}) {
  return _then(RegionDto(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [RegionDto].
extension RegionDtoPatterns on RegionDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _RegionDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _RegionDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _RegionDto value)  $default,){
final _that = this;
switch (_that) {
case _RegionDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _RegionDto value)?  $default,){
final _that = this;
switch (_that) {
case _RegionDto() when $default != null:
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
case _RegionDto() when $default != null:
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
case _RegionDto():
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
case _RegionDto() when $default != null:
return $default(_that.code,_that.name);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _RegionDto extends RegionDto {
  const _RegionDto({required this.code, required this.name}): super._();
  factory _RegionDto.fromJson(Map<String, dynamic> json) => _$RegionDtoFromJson(json);

@override final  String code;
@override final  String name;

/// Create a copy of RegionDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$RegionDtoCopyWith<_RegionDto> get copyWith => __$RegionDtoCopyWithImpl<_RegionDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$RegionDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _RegionDto&&(identical(other.code, code) || other.code == code)&&(identical(other.name, name) || other.name == name));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,code,name);
}

@override
String toString() {
    return 'RegionDto(code: $code, name: $name)';
}


}

/// @nodoc
abstract mixin class _$RegionDtoCopyWith<$Res> implements $RegionDtoCopyWith<$Res> {
  factory _$RegionDtoCopyWith(_RegionDto value, $Res Function(_RegionDto) _then) = __$RegionDtoCopyWithImpl;
@override @useResult
$Res call({
 String code, String name
});




}
/// @nodoc
class __$RegionDtoCopyWithImpl<$Res>
    implements _$RegionDtoCopyWith<$Res> {
  __$RegionDtoCopyWithImpl(this._self, this._then);

  final _RegionDto _self;
  final $Res Function(_RegionDto) _then;

/// Create a copy of RegionDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? code = null,Object? name = null,}) {
  return _then(_RegionDto(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}


/// @nodoc
mixin _$RatingDto {

 String get average; int get count;
/// Create a copy of RatingDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$RatingDtoCopyWith<RatingDto> get copyWith => _$RatingDtoCopyWithImpl<RatingDto>(this as RatingDto, _$identity);

  /// Serializes this RatingDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as RatingDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is RatingDto&&(identical(other.average, _this.average) || other.average == _this.average)&&(identical(other.count, _this.count) || other.count == _this.count));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as RatingDto;
  return Object.hash(runtimeType,_this.average,_this.count);
}

@override
String toString() {
  final _this = this as RatingDto;
  return 'RatingDto(average: ${_this.average}, count: ${_this.count})';
}


}

/// @nodoc
abstract mixin class $RatingDtoCopyWith<$Res>  {
  factory $RatingDtoCopyWith(RatingDto value, $Res Function(RatingDto) _then) = _$RatingDtoCopyWithImpl;
@useResult
$Res call({
 String average, int count
});




}
/// @nodoc
class _$RatingDtoCopyWithImpl<$Res>
    implements $RatingDtoCopyWith<$Res> {
  _$RatingDtoCopyWithImpl(this._self, this._then);

  final RatingDto _self;
  final $Res Function(RatingDto) _then;

/// Create a copy of RatingDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? average = null,Object? count = null,}) {
  return _then(RatingDto(
average: null == average ? _self.average : average // ignore: cast_nullable_to_non_nullable
as String,count: null == count ? _self.count : count // ignore: cast_nullable_to_non_nullable
as int,
  ));
}

}


/// Adds pattern-matching-related methods to [RatingDto].
extension RatingDtoPatterns on RatingDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _RatingDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _RatingDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _RatingDto value)  $default,){
final _that = this;
switch (_that) {
case _RatingDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _RatingDto value)?  $default,){
final _that = this;
switch (_that) {
case _RatingDto() when $default != null:
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
case _RatingDto() when $default != null:
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
case _RatingDto():
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
case _RatingDto() when $default != null:
return $default(_that.average,_that.count);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _RatingDto implements RatingDto {
  const _RatingDto({required this.average, required this.count});
  factory _RatingDto.fromJson(Map<String, dynamic> json) => _$RatingDtoFromJson(json);

@override final  String average;
@override final  int count;

/// Create a copy of RatingDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$RatingDtoCopyWith<_RatingDto> get copyWith => __$RatingDtoCopyWithImpl<_RatingDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$RatingDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _RatingDto&&(identical(other.average, average) || other.average == average)&&(identical(other.count, count) || other.count == count));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,average,count);
}

@override
String toString() {
    return 'RatingDto(average: $average, count: $count)';
}


}

/// @nodoc
abstract mixin class _$RatingDtoCopyWith<$Res> implements $RatingDtoCopyWith<$Res> {
  factory _$RatingDtoCopyWith(_RatingDto value, $Res Function(_RatingDto) _then) = __$RatingDtoCopyWithImpl;
@override @useResult
$Res call({
 String average, int count
});




}
/// @nodoc
class __$RatingDtoCopyWithImpl<$Res>
    implements _$RatingDtoCopyWith<$Res> {
  __$RatingDtoCopyWithImpl(this._self, this._then);

  final _RatingDto _self;
  final $Res Function(_RatingDto) _then;

/// Create a copy of RatingDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? average = null,Object? count = null,}) {
  return _then(_RatingDto(
average: null == average ? _self.average : average // ignore: cast_nullable_to_non_nullable
as String,count: null == count ? _self.count : count // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}


/// @nodoc
mixin _$VariantsSummaryDto {

 int get count; List<RegionDto> get regions; bool get hasCustomAmount;
/// Create a copy of VariantsSummaryDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$VariantsSummaryDtoCopyWith<VariantsSummaryDto> get copyWith => _$VariantsSummaryDtoCopyWithImpl<VariantsSummaryDto>(this as VariantsSummaryDto, _$identity);

  /// Serializes this VariantsSummaryDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as VariantsSummaryDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is VariantsSummaryDto&&(identical(other.count, _this.count) || other.count == _this.count)&&const DeepCollectionEquality().equals(other.regions, _this.regions)&&(identical(other.hasCustomAmount, _this.hasCustomAmount) || other.hasCustomAmount == _this.hasCustomAmount));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as VariantsSummaryDto;
  return Object.hash(runtimeType,_this.count,const DeepCollectionEquality().hash(_this.regions),_this.hasCustomAmount);
}

@override
String toString() {
  final _this = this as VariantsSummaryDto;
  return 'VariantsSummaryDto(count: ${_this.count}, regions: ${_this.regions}, hasCustomAmount: ${_this.hasCustomAmount})';
}


}

/// @nodoc
abstract mixin class $VariantsSummaryDtoCopyWith<$Res>  {
  factory $VariantsSummaryDtoCopyWith(VariantsSummaryDto value, $Res Function(VariantsSummaryDto) _then) = _$VariantsSummaryDtoCopyWithImpl;
@useResult
$Res call({
 int count, List<RegionDto> regions, bool hasCustomAmount
});




}
/// @nodoc
class _$VariantsSummaryDtoCopyWithImpl<$Res>
    implements $VariantsSummaryDtoCopyWith<$Res> {
  _$VariantsSummaryDtoCopyWithImpl(this._self, this._then);

  final VariantsSummaryDto _self;
  final $Res Function(VariantsSummaryDto) _then;

/// Create a copy of VariantsSummaryDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? count = null,Object? regions = null,Object? hasCustomAmount = null,}) {
  return _then(VariantsSummaryDto(
count: null == count ? _self.count : count // ignore: cast_nullable_to_non_nullable
as int,regions: null == regions ? _self.regions : regions // ignore: cast_nullable_to_non_nullable
as List<RegionDto>,hasCustomAmount: null == hasCustomAmount ? _self.hasCustomAmount : hasCustomAmount // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [VariantsSummaryDto].
extension VariantsSummaryDtoPatterns on VariantsSummaryDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _VariantsSummaryDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _VariantsSummaryDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _VariantsSummaryDto value)  $default,){
final _that = this;
switch (_that) {
case _VariantsSummaryDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _VariantsSummaryDto value)?  $default,){
final _that = this;
switch (_that) {
case _VariantsSummaryDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int count,  List<RegionDto> regions,  bool hasCustomAmount)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _VariantsSummaryDto() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int count,  List<RegionDto> regions,  bool hasCustomAmount)  $default,) {final _that = this;
switch (_that) {
case _VariantsSummaryDto():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int count,  List<RegionDto> regions,  bool hasCustomAmount)?  $default,) {final _that = this;
switch (_that) {
case _VariantsSummaryDto() when $default != null:
return $default(_that.count,_that.regions,_that.hasCustomAmount);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _VariantsSummaryDto implements VariantsSummaryDto {
  const _VariantsSummaryDto({required this.count, required  List<RegionDto> regions, required this.hasCustomAmount}): _regions = regions;
  factory _VariantsSummaryDto.fromJson(Map<String, dynamic> json) => _$VariantsSummaryDtoFromJson(json);

@override final  int count;
 final  List<RegionDto> _regions;
@override List<RegionDto> get regions {
  if (_regions is EqualUnmodifiableListView) return _regions;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_regions);
}

@override final  bool hasCustomAmount;

/// Create a copy of VariantsSummaryDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$VariantsSummaryDtoCopyWith<_VariantsSummaryDto> get copyWith => __$VariantsSummaryDtoCopyWithImpl<_VariantsSummaryDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$VariantsSummaryDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _VariantsSummaryDto&&(identical(other.count, count) || other.count == count)&&const DeepCollectionEquality().equals(other.regions, _regions)&&(identical(other.hasCustomAmount, hasCustomAmount) || other.hasCustomAmount == hasCustomAmount));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,count,const DeepCollectionEquality().hash(_regions),hasCustomAmount);
}

@override
String toString() {
    return 'VariantsSummaryDto(count: $count, regions: $regions, hasCustomAmount: $hasCustomAmount)';
}


}

/// @nodoc
abstract mixin class _$VariantsSummaryDtoCopyWith<$Res> implements $VariantsSummaryDtoCopyWith<$Res> {
  factory _$VariantsSummaryDtoCopyWith(_VariantsSummaryDto value, $Res Function(_VariantsSummaryDto) _then) = __$VariantsSummaryDtoCopyWithImpl;
@override @useResult
$Res call({
 int count, List<RegionDto> regions, bool hasCustomAmount
});




}
/// @nodoc
class __$VariantsSummaryDtoCopyWithImpl<$Res>
    implements _$VariantsSummaryDtoCopyWith<$Res> {
  __$VariantsSummaryDtoCopyWithImpl(this._self, this._then);

  final _VariantsSummaryDto _self;
  final $Res Function(_VariantsSummaryDto) _then;

/// Create a copy of VariantsSummaryDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? count = null,Object? regions = null,Object? hasCustomAmount = null,}) {
  return _then(_VariantsSummaryDto(
count: null == count ? _self.count : count // ignore: cast_nullable_to_non_nullable
as int,regions: null == regions ? _self._regions : regions // ignore: cast_nullable_to_non_nullable
as List<RegionDto>,hasCustomAmount: null == hasCustomAmount ? _self.hasCustomAmount : hasCustomAmount // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}


/// @nodoc
mixin _$DeliveryFieldOptionDto {

 String get value; String get label;
/// Create a copy of DeliveryFieldOptionDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DeliveryFieldOptionDtoCopyWith<DeliveryFieldOptionDto> get copyWith => _$DeliveryFieldOptionDtoCopyWithImpl<DeliveryFieldOptionDto>(this as DeliveryFieldOptionDto, _$identity);

  /// Serializes this DeliveryFieldOptionDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as DeliveryFieldOptionDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DeliveryFieldOptionDto&&(identical(other.value, _this.value) || other.value == _this.value)&&(identical(other.label, _this.label) || other.label == _this.label));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as DeliveryFieldOptionDto;
  return Object.hash(runtimeType,_this.value,_this.label);
}

@override
String toString() {
  final _this = this as DeliveryFieldOptionDto;
  return 'DeliveryFieldOptionDto(value: ${_this.value}, label: ${_this.label})';
}


}

/// @nodoc
abstract mixin class $DeliveryFieldOptionDtoCopyWith<$Res>  {
  factory $DeliveryFieldOptionDtoCopyWith(DeliveryFieldOptionDto value, $Res Function(DeliveryFieldOptionDto) _then) = _$DeliveryFieldOptionDtoCopyWithImpl;
@useResult
$Res call({
 String value, String label
});




}
/// @nodoc
class _$DeliveryFieldOptionDtoCopyWithImpl<$Res>
    implements $DeliveryFieldOptionDtoCopyWith<$Res> {
  _$DeliveryFieldOptionDtoCopyWithImpl(this._self, this._then);

  final DeliveryFieldOptionDto _self;
  final $Res Function(DeliveryFieldOptionDto) _then;

/// Create a copy of DeliveryFieldOptionDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? value = null,Object? label = null,}) {
  return _then(DeliveryFieldOptionDto(
value: null == value ? _self.value : value // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [DeliveryFieldOptionDto].
extension DeliveryFieldOptionDtoPatterns on DeliveryFieldOptionDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _DeliveryFieldOptionDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _DeliveryFieldOptionDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _DeliveryFieldOptionDto value)  $default,){
final _that = this;
switch (_that) {
case _DeliveryFieldOptionDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _DeliveryFieldOptionDto value)?  $default,){
final _that = this;
switch (_that) {
case _DeliveryFieldOptionDto() when $default != null:
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
case _DeliveryFieldOptionDto() when $default != null:
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
case _DeliveryFieldOptionDto():
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
case _DeliveryFieldOptionDto() when $default != null:
return $default(_that.value,_that.label);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _DeliveryFieldOptionDto implements DeliveryFieldOptionDto {
  const _DeliveryFieldOptionDto({required this.value, required this.label});
  factory _DeliveryFieldOptionDto.fromJson(Map<String, dynamic> json) => _$DeliveryFieldOptionDtoFromJson(json);

@override final  String value;
@override final  String label;

/// Create a copy of DeliveryFieldOptionDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DeliveryFieldOptionDtoCopyWith<_DeliveryFieldOptionDto> get copyWith => __$DeliveryFieldOptionDtoCopyWithImpl<_DeliveryFieldOptionDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$DeliveryFieldOptionDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _DeliveryFieldOptionDto&&(identical(other.value, value) || other.value == value)&&(identical(other.label, label) || other.label == label));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,value,label);
}

@override
String toString() {
    return 'DeliveryFieldOptionDto(value: $value, label: $label)';
}


}

/// @nodoc
abstract mixin class _$DeliveryFieldOptionDtoCopyWith<$Res> implements $DeliveryFieldOptionDtoCopyWith<$Res> {
  factory _$DeliveryFieldOptionDtoCopyWith(_DeliveryFieldOptionDto value, $Res Function(_DeliveryFieldOptionDto) _then) = __$DeliveryFieldOptionDtoCopyWithImpl;
@override @useResult
$Res call({
 String value, String label
});




}
/// @nodoc
class __$DeliveryFieldOptionDtoCopyWithImpl<$Res>
    implements _$DeliveryFieldOptionDtoCopyWith<$Res> {
  __$DeliveryFieldOptionDtoCopyWithImpl(this._self, this._then);

  final _DeliveryFieldOptionDto _self;
  final $Res Function(_DeliveryFieldOptionDto) _then;

/// Create a copy of DeliveryFieldOptionDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? value = null,Object? label = null,}) {
  return _then(_DeliveryFieldOptionDto(
value: null == value ? _self.value : value // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}


/// @nodoc
mixin _$DeliveryFieldDto {

 String get key; String get label; String get type; bool get required; String? get helpText; int? get maxLength; String? get pattern; List<DeliveryFieldOptionDto>? get options;
/// Create a copy of DeliveryFieldDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DeliveryFieldDtoCopyWith<DeliveryFieldDto> get copyWith => _$DeliveryFieldDtoCopyWithImpl<DeliveryFieldDto>(this as DeliveryFieldDto, _$identity);

  /// Serializes this DeliveryFieldDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as DeliveryFieldDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DeliveryFieldDto&&(identical(other.key, _this.key) || other.key == _this.key)&&(identical(other.label, _this.label) || other.label == _this.label)&&(identical(other.type, _this.type) || other.type == _this.type)&&(identical(other.required, _this.required) || other.required == _this.required)&&(identical(other.helpText, _this.helpText) || other.helpText == _this.helpText)&&(identical(other.maxLength, _this.maxLength) || other.maxLength == _this.maxLength)&&(identical(other.pattern, _this.pattern) || other.pattern == _this.pattern)&&const DeepCollectionEquality().equals(other.options, _this.options));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as DeliveryFieldDto;
  return Object.hash(runtimeType,_this.key,_this.label,_this.type,_this.required,_this.helpText,_this.maxLength,_this.pattern,const DeepCollectionEquality().hash(_this.options));
}

@override
String toString() {
  final _this = this as DeliveryFieldDto;
  return 'DeliveryFieldDto(key: ${_this.key}, label: ${_this.label}, type: ${_this.type}, required: ${_this.required}, helpText: ${_this.helpText}, maxLength: ${_this.maxLength}, pattern: ${_this.pattern}, options: ${_this.options})';
}


}

/// @nodoc
abstract mixin class $DeliveryFieldDtoCopyWith<$Res>  {
  factory $DeliveryFieldDtoCopyWith(DeliveryFieldDto value, $Res Function(DeliveryFieldDto) _then) = _$DeliveryFieldDtoCopyWithImpl;
@useResult
$Res call({
 String key, String label, String type, bool required, String? helpText, int? maxLength, String? pattern, List<DeliveryFieldOptionDto>? options
});




}
/// @nodoc
class _$DeliveryFieldDtoCopyWithImpl<$Res>
    implements $DeliveryFieldDtoCopyWith<$Res> {
  _$DeliveryFieldDtoCopyWithImpl(this._self, this._then);

  final DeliveryFieldDto _self;
  final $Res Function(DeliveryFieldDto) _then;

/// Create a copy of DeliveryFieldDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? key = null,Object? label = null,Object? type = null,Object? required = null,Object? helpText = freezed,Object? maxLength = freezed,Object? pattern = freezed,Object? options = freezed,}) {
  return _then(DeliveryFieldDto(
key: null == key ? _self.key : key // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,required: null == required ? _self.required : required // ignore: cast_nullable_to_non_nullable
as bool,helpText: freezed == helpText ? _self.helpText : helpText // ignore: cast_nullable_to_non_nullable
as String?,maxLength: freezed == maxLength ? _self.maxLength : maxLength // ignore: cast_nullable_to_non_nullable
as int?,pattern: freezed == pattern ? _self.pattern : pattern // ignore: cast_nullable_to_non_nullable
as String?,options: freezed == options ? _self.options : options // ignore: cast_nullable_to_non_nullable
as List<DeliveryFieldOptionDto>?,
  ));
}

}


/// Adds pattern-matching-related methods to [DeliveryFieldDto].
extension DeliveryFieldDtoPatterns on DeliveryFieldDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _DeliveryFieldDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _DeliveryFieldDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _DeliveryFieldDto value)  $default,){
final _that = this;
switch (_that) {
case _DeliveryFieldDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _DeliveryFieldDto value)?  $default,){
final _that = this;
switch (_that) {
case _DeliveryFieldDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String key,  String label,  String type,  bool required,  String? helpText,  int? maxLength,  String? pattern,  List<DeliveryFieldOptionDto>? options)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _DeliveryFieldDto() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String key,  String label,  String type,  bool required,  String? helpText,  int? maxLength,  String? pattern,  List<DeliveryFieldOptionDto>? options)  $default,) {final _that = this;
switch (_that) {
case _DeliveryFieldDto():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String key,  String label,  String type,  bool required,  String? helpText,  int? maxLength,  String? pattern,  List<DeliveryFieldOptionDto>? options)?  $default,) {final _that = this;
switch (_that) {
case _DeliveryFieldDto() when $default != null:
return $default(_that.key,_that.label,_that.type,_that.required,_that.helpText,_that.maxLength,_that.pattern,_that.options);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _DeliveryFieldDto extends DeliveryFieldDto {
  const _DeliveryFieldDto({required this.key, required this.label, required this.type, required this.required, this.helpText, this.maxLength, this.pattern,  List<DeliveryFieldOptionDto>? options}): _options = options,super._();
  factory _DeliveryFieldDto.fromJson(Map<String, dynamic> json) => _$DeliveryFieldDtoFromJson(json);

@override final  String key;
@override final  String label;
@override final  String type;
@override final  bool required;
@override final  String? helpText;
@override final  int? maxLength;
@override final  String? pattern;
 final  List<DeliveryFieldOptionDto>? _options;
@override List<DeliveryFieldOptionDto>? get options {
  final value = _options;
  if (value == null) return null;
  if (_options is EqualUnmodifiableListView) return _options;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(value);
}


/// Create a copy of DeliveryFieldDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DeliveryFieldDtoCopyWith<_DeliveryFieldDto> get copyWith => __$DeliveryFieldDtoCopyWithImpl<_DeliveryFieldDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$DeliveryFieldDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _DeliveryFieldDto&&(identical(other.key, key) || other.key == key)&&(identical(other.label, label) || other.label == label)&&(identical(other.type, type) || other.type == type)&&(identical(other.required, required) || other.required == required)&&(identical(other.helpText, helpText) || other.helpText == helpText)&&(identical(other.maxLength, maxLength) || other.maxLength == maxLength)&&(identical(other.pattern, pattern) || other.pattern == pattern)&&const DeepCollectionEquality().equals(other.options, _options));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,key,label,type,required,helpText,maxLength,pattern,const DeepCollectionEquality().hash(_options));
}

@override
String toString() {
    return 'DeliveryFieldDto(key: $key, label: $label, type: $type, required: $required, helpText: $helpText, maxLength: $maxLength, pattern: $pattern, options: $options)';
}


}

/// @nodoc
abstract mixin class _$DeliveryFieldDtoCopyWith<$Res> implements $DeliveryFieldDtoCopyWith<$Res> {
  factory _$DeliveryFieldDtoCopyWith(_DeliveryFieldDto value, $Res Function(_DeliveryFieldDto) _then) = __$DeliveryFieldDtoCopyWithImpl;
@override @useResult
$Res call({
 String key, String label, String type, bool required, String? helpText, int? maxLength, String? pattern, List<DeliveryFieldOptionDto>? options
});




}
/// @nodoc
class __$DeliveryFieldDtoCopyWithImpl<$Res>
    implements _$DeliveryFieldDtoCopyWith<$Res> {
  __$DeliveryFieldDtoCopyWithImpl(this._self, this._then);

  final _DeliveryFieldDto _self;
  final $Res Function(_DeliveryFieldDto) _then;

/// Create a copy of DeliveryFieldDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? key = null,Object? label = null,Object? type = null,Object? required = null,Object? helpText = freezed,Object? maxLength = freezed,Object? pattern = freezed,Object? options = freezed,}) {
  return _then(_DeliveryFieldDto(
key: null == key ? _self.key : key // ignore: cast_nullable_to_non_nullable
as String,label: null == label ? _self.label : label // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,required: null == required ? _self.required : required // ignore: cast_nullable_to_non_nullable
as bool,helpText: freezed == helpText ? _self.helpText : helpText // ignore: cast_nullable_to_non_nullable
as String?,maxLength: freezed == maxLength ? _self.maxLength : maxLength // ignore: cast_nullable_to_non_nullable
as int?,pattern: freezed == pattern ? _self.pattern : pattern // ignore: cast_nullable_to_non_nullable
as String?,options: freezed == options ? _self._options : options // ignore: cast_nullable_to_non_nullable
as List<DeliveryFieldOptionDto>?,
  ));
}


}


/// @nodoc
mixin _$SeoDto {

 String? get title; String? get description; String? get robots; String? get image;
/// Create a copy of SeoDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SeoDtoCopyWith<SeoDto> get copyWith => _$SeoDtoCopyWithImpl<SeoDto>(this as SeoDto, _$identity);

  /// Serializes this SeoDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as SeoDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SeoDto&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.description, _this.description) || other.description == _this.description)&&(identical(other.robots, _this.robots) || other.robots == _this.robots)&&(identical(other.image, _this.image) || other.image == _this.image));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as SeoDto;
  return Object.hash(runtimeType,_this.title,_this.description,_this.robots,_this.image);
}

@override
String toString() {
  final _this = this as SeoDto;
  return 'SeoDto(title: ${_this.title}, description: ${_this.description}, robots: ${_this.robots}, image: ${_this.image})';
}


}

/// @nodoc
abstract mixin class $SeoDtoCopyWith<$Res>  {
  factory $SeoDtoCopyWith(SeoDto value, $Res Function(SeoDto) _then) = _$SeoDtoCopyWithImpl;
@useResult
$Res call({
 String? title, String? description, String? robots, String? image
});




}
/// @nodoc
class _$SeoDtoCopyWithImpl<$Res>
    implements $SeoDtoCopyWith<$Res> {
  _$SeoDtoCopyWithImpl(this._self, this._then);

  final SeoDto _self;
  final $Res Function(SeoDto) _then;

/// Create a copy of SeoDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? title = freezed,Object? description = freezed,Object? robots = freezed,Object? image = freezed,}) {
  return _then(SeoDto(
title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,robots: freezed == robots ? _self.robots : robots // ignore: cast_nullable_to_non_nullable
as String?,image: freezed == image ? _self.image : image // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [SeoDto].
extension SeoDtoPatterns on SeoDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _SeoDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _SeoDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _SeoDto value)  $default,){
final _that = this;
switch (_that) {
case _SeoDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _SeoDto value)?  $default,){
final _that = this;
switch (_that) {
case _SeoDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String? title,  String? description,  String? robots,  String? image)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _SeoDto() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String? title,  String? description,  String? robots,  String? image)  $default,) {final _that = this;
switch (_that) {
case _SeoDto():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String? title,  String? description,  String? robots,  String? image)?  $default,) {final _that = this;
switch (_that) {
case _SeoDto() when $default != null:
return $default(_that.title,_that.description,_that.robots,_that.image);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _SeoDto implements SeoDto {
  const _SeoDto({this.title, this.description, this.robots, this.image});
  factory _SeoDto.fromJson(Map<String, dynamic> json) => _$SeoDtoFromJson(json);

@override final  String? title;
@override final  String? description;
@override final  String? robots;
@override final  String? image;

/// Create a copy of SeoDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$SeoDtoCopyWith<_SeoDto> get copyWith => __$SeoDtoCopyWithImpl<_SeoDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$SeoDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _SeoDto&&(identical(other.title, title) || other.title == title)&&(identical(other.description, description) || other.description == description)&&(identical(other.robots, robots) || other.robots == robots)&&(identical(other.image, image) || other.image == image));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,title,description,robots,image);
}

@override
String toString() {
    return 'SeoDto(title: $title, description: $description, robots: $robots, image: $image)';
}


}

/// @nodoc
abstract mixin class _$SeoDtoCopyWith<$Res> implements $SeoDtoCopyWith<$Res> {
  factory _$SeoDtoCopyWith(_SeoDto value, $Res Function(_SeoDto) _then) = __$SeoDtoCopyWithImpl;
@override @useResult
$Res call({
 String? title, String? description, String? robots, String? image
});




}
/// @nodoc
class __$SeoDtoCopyWithImpl<$Res>
    implements _$SeoDtoCopyWith<$Res> {
  __$SeoDtoCopyWithImpl(this._self, this._then);

  final _SeoDto _self;
  final $Res Function(_SeoDto) _then;

/// Create a copy of SeoDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? title = freezed,Object? description = freezed,Object? robots = freezed,Object? image = freezed,}) {
  return _then(_SeoDto(
title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,robots: freezed == robots ? _self.robots : robots // ignore: cast_nullable_to_non_nullable
as String?,image: freezed == image ? _self.image : image // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}


/// @nodoc
mixin _$VariantOfferDto {

 String get publicId; String get title; String get sku; bool get isCustomAmount; String get availability; bool get purchasable; bool get priceMayVary; String? get region; String? get denominationValue; String? get denominationCurrency;@NullableMoneyConverter() Money? get price;
/// Create a copy of VariantOfferDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$VariantOfferDtoCopyWith<VariantOfferDto> get copyWith => _$VariantOfferDtoCopyWithImpl<VariantOfferDto>(this as VariantOfferDto, _$identity);

  /// Serializes this VariantOfferDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as VariantOfferDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is VariantOfferDto&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.sku, _this.sku) || other.sku == _this.sku)&&(identical(other.isCustomAmount, _this.isCustomAmount) || other.isCustomAmount == _this.isCustomAmount)&&(identical(other.availability, _this.availability) || other.availability == _this.availability)&&(identical(other.purchasable, _this.purchasable) || other.purchasable == _this.purchasable)&&(identical(other.priceMayVary, _this.priceMayVary) || other.priceMayVary == _this.priceMayVary)&&(identical(other.region, _this.region) || other.region == _this.region)&&(identical(other.denominationValue, _this.denominationValue) || other.denominationValue == _this.denominationValue)&&(identical(other.denominationCurrency, _this.denominationCurrency) || other.denominationCurrency == _this.denominationCurrency)&&(identical(other.price, _this.price) || other.price == _this.price));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as VariantOfferDto;
  return Object.hash(runtimeType,_this.publicId,_this.title,_this.sku,_this.isCustomAmount,_this.availability,_this.purchasable,_this.priceMayVary,_this.region,_this.denominationValue,_this.denominationCurrency,_this.price);
}

@override
String toString() {
  final _this = this as VariantOfferDto;
  return 'VariantOfferDto(publicId: ${_this.publicId}, title: ${_this.title}, sku: ${_this.sku}, isCustomAmount: ${_this.isCustomAmount}, availability: ${_this.availability}, purchasable: ${_this.purchasable}, priceMayVary: ${_this.priceMayVary}, region: ${_this.region}, denominationValue: ${_this.denominationValue}, denominationCurrency: ${_this.denominationCurrency}, price: ${_this.price})';
}


}

/// @nodoc
abstract mixin class $VariantOfferDtoCopyWith<$Res>  {
  factory $VariantOfferDtoCopyWith(VariantOfferDto value, $Res Function(VariantOfferDto) _then) = _$VariantOfferDtoCopyWithImpl;
@useResult
$Res call({
 String publicId, String title, String sku, bool isCustomAmount, String availability, bool purchasable, bool priceMayVary, String? region, String? denominationValue, String? denominationCurrency,@NullableMoneyConverter() Money? price
});




}
/// @nodoc
class _$VariantOfferDtoCopyWithImpl<$Res>
    implements $VariantOfferDtoCopyWith<$Res> {
  _$VariantOfferDtoCopyWithImpl(this._self, this._then);

  final VariantOfferDto _self;
  final $Res Function(VariantOfferDto) _then;

/// Create a copy of VariantOfferDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? title = null,Object? sku = null,Object? isCustomAmount = null,Object? availability = null,Object? purchasable = null,Object? priceMayVary = null,Object? region = freezed,Object? denominationValue = freezed,Object? denominationCurrency = freezed,Object? price = freezed,}) {
  return _then(VariantOfferDto(
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


/// Adds pattern-matching-related methods to [VariantOfferDto].
extension VariantOfferDtoPatterns on VariantOfferDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _VariantOfferDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _VariantOfferDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _VariantOfferDto value)  $default,){
final _that = this;
switch (_that) {
case _VariantOfferDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _VariantOfferDto value)?  $default,){
final _that = this;
switch (_that) {
case _VariantOfferDto() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String title,  String sku,  bool isCustomAmount,  String availability,  bool purchasable,  bool priceMayVary,  String? region,  String? denominationValue,  String? denominationCurrency, @NullableMoneyConverter()  Money? price)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _VariantOfferDto() when $default != null:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String title,  String sku,  bool isCustomAmount,  String availability,  bool purchasable,  bool priceMayVary,  String? region,  String? denominationValue,  String? denominationCurrency, @NullableMoneyConverter()  Money? price)  $default,) {final _that = this;
switch (_that) {
case _VariantOfferDto():
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String title,  String sku,  bool isCustomAmount,  String availability,  bool purchasable,  bool priceMayVary,  String? region,  String? denominationValue,  String? denominationCurrency, @NullableMoneyConverter()  Money? price)?  $default,) {final _that = this;
switch (_that) {
case _VariantOfferDto() when $default != null:
return $default(_that.publicId,_that.title,_that.sku,_that.isCustomAmount,_that.availability,_that.purchasable,_that.priceMayVary,_that.region,_that.denominationValue,_that.denominationCurrency,_that.price);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _VariantOfferDto extends VariantOfferDto {
  const _VariantOfferDto({required this.publicId, required this.title, required this.sku, required this.isCustomAmount, required this.availability, required this.purchasable, required this.priceMayVary, this.region, this.denominationValue, this.denominationCurrency, @NullableMoneyConverter() this.price}): super._();
  factory _VariantOfferDto.fromJson(Map<String, dynamic> json) => _$VariantOfferDtoFromJson(json);

@override final  String publicId;
@override final  String title;
@override final  String sku;
@override final  bool isCustomAmount;
@override final  String availability;
@override final  bool purchasable;
@override final  bool priceMayVary;
@override final  String? region;
@override final  String? denominationValue;
@override final  String? denominationCurrency;
@override@NullableMoneyConverter() final  Money? price;

/// Create a copy of VariantOfferDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$VariantOfferDtoCopyWith<_VariantOfferDto> get copyWith => __$VariantOfferDtoCopyWithImpl<_VariantOfferDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$VariantOfferDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _VariantOfferDto&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.title, title) || other.title == title)&&(identical(other.sku, sku) || other.sku == sku)&&(identical(other.isCustomAmount, isCustomAmount) || other.isCustomAmount == isCustomAmount)&&(identical(other.availability, availability) || other.availability == availability)&&(identical(other.purchasable, purchasable) || other.purchasable == purchasable)&&(identical(other.priceMayVary, priceMayVary) || other.priceMayVary == priceMayVary)&&(identical(other.region, region) || other.region == region)&&(identical(other.denominationValue, denominationValue) || other.denominationValue == denominationValue)&&(identical(other.denominationCurrency, denominationCurrency) || other.denominationCurrency == denominationCurrency)&&(identical(other.price, price) || other.price == price));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,publicId,title,sku,isCustomAmount,availability,purchasable,priceMayVary,region,denominationValue,denominationCurrency,price);
}

@override
String toString() {
    return 'VariantOfferDto(publicId: $publicId, title: $title, sku: $sku, isCustomAmount: $isCustomAmount, availability: $availability, purchasable: $purchasable, priceMayVary: $priceMayVary, region: $region, denominationValue: $denominationValue, denominationCurrency: $denominationCurrency, price: $price)';
}


}

/// @nodoc
abstract mixin class _$VariantOfferDtoCopyWith<$Res> implements $VariantOfferDtoCopyWith<$Res> {
  factory _$VariantOfferDtoCopyWith(_VariantOfferDto value, $Res Function(_VariantOfferDto) _then) = __$VariantOfferDtoCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String title, String sku, bool isCustomAmount, String availability, bool purchasable, bool priceMayVary, String? region, String? denominationValue, String? denominationCurrency,@NullableMoneyConverter() Money? price
});




}
/// @nodoc
class __$VariantOfferDtoCopyWithImpl<$Res>
    implements _$VariantOfferDtoCopyWith<$Res> {
  __$VariantOfferDtoCopyWithImpl(this._self, this._then);

  final _VariantOfferDto _self;
  final $Res Function(_VariantOfferDto) _then;

/// Create a copy of VariantOfferDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? title = null,Object? sku = null,Object? isCustomAmount = null,Object? availability = null,Object? purchasable = null,Object? priceMayVary = null,Object? region = freezed,Object? denominationValue = freezed,Object? denominationCurrency = freezed,Object? price = freezed,}) {
  return _then(_VariantOfferDto(
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
mixin _$ProductReviewDto {

 int get id; int get rating; String get reviewer; bool get verifiedPurchase; DateTime get createdAt; String? get title; String? get body;
/// Create a copy of ProductReviewDto
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ProductReviewDtoCopyWith<ProductReviewDto> get copyWith => _$ProductReviewDtoCopyWithImpl<ProductReviewDto>(this as ProductReviewDto, _$identity);

  /// Serializes this ProductReviewDto to a JSON map.
  Map<String, dynamic> toJson();


@override
bool operator ==(Object other) {
  final _this = this as ProductReviewDto;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ProductReviewDto&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.rating, _this.rating) || other.rating == _this.rating)&&(identical(other.reviewer, _this.reviewer) || other.reviewer == _this.reviewer)&&(identical(other.verifiedPurchase, _this.verifiedPurchase) || other.verifiedPurchase == _this.verifiedPurchase)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.body, _this.body) || other.body == _this.body));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
  final _this = this as ProductReviewDto;
  return Object.hash(runtimeType,_this.id,_this.rating,_this.reviewer,_this.verifiedPurchase,_this.createdAt,_this.title,_this.body);
}

@override
String toString() {
  final _this = this as ProductReviewDto;
  return 'ProductReviewDto(id: ${_this.id}, rating: ${_this.rating}, reviewer: ${_this.reviewer}, verifiedPurchase: ${_this.verifiedPurchase}, createdAt: ${_this.createdAt}, title: ${_this.title}, body: ${_this.body})';
}


}

/// @nodoc
abstract mixin class $ProductReviewDtoCopyWith<$Res>  {
  factory $ProductReviewDtoCopyWith(ProductReviewDto value, $Res Function(ProductReviewDto) _then) = _$ProductReviewDtoCopyWithImpl;
@useResult
$Res call({
 int id, int rating, String reviewer, bool verifiedPurchase, DateTime createdAt, String? title, String? body
});




}
/// @nodoc
class _$ProductReviewDtoCopyWithImpl<$Res>
    implements $ProductReviewDtoCopyWith<$Res> {
  _$ProductReviewDtoCopyWithImpl(this._self, this._then);

  final ProductReviewDto _self;
  final $Res Function(ProductReviewDto) _then;

/// Create a copy of ProductReviewDto
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? rating = null,Object? reviewer = null,Object? verifiedPurchase = null,Object? createdAt = null,Object? title = freezed,Object? body = freezed,}) {
  return _then(ProductReviewDto(
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


/// Adds pattern-matching-related methods to [ProductReviewDto].
extension ProductReviewDtoPatterns on ProductReviewDto {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _ProductReviewDto value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _ProductReviewDto() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _ProductReviewDto value)  $default,){
final _that = this;
switch (_that) {
case _ProductReviewDto():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _ProductReviewDto value)?  $default,){
final _that = this;
switch (_that) {
case _ProductReviewDto() when $default != null:
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
case _ProductReviewDto() when $default != null:
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
case _ProductReviewDto():
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
case _ProductReviewDto() when $default != null:
return $default(_that.id,_that.rating,_that.reviewer,_that.verifiedPurchase,_that.createdAt,_that.title,_that.body);case _:
  return null;

}
}

}

/// @nodoc
@JsonSerializable()

class _ProductReviewDto extends ProductReviewDto {
  const _ProductReviewDto({required this.id, required this.rating, required this.reviewer, required this.verifiedPurchase, required this.createdAt, this.title, this.body}): super._();
  factory _ProductReviewDto.fromJson(Map<String, dynamic> json) => _$ProductReviewDtoFromJson(json);

@override final  int id;
@override final  int rating;
@override final  String reviewer;
@override final  bool verifiedPurchase;
@override final  DateTime createdAt;
@override final  String? title;
@override final  String? body;

/// Create a copy of ProductReviewDto
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$ProductReviewDtoCopyWith<_ProductReviewDto> get copyWith => __$ProductReviewDtoCopyWithImpl<_ProductReviewDto>(this, _$identity);

@override
Map<String, dynamic> toJson() {
  return _$ProductReviewDtoToJson(this, );
}

@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _ProductReviewDto&&(identical(other.id, id) || other.id == id)&&(identical(other.rating, rating) || other.rating == rating)&&(identical(other.reviewer, reviewer) || other.reviewer == reviewer)&&(identical(other.verifiedPurchase, verifiedPurchase) || other.verifiedPurchase == verifiedPurchase)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.title, title) || other.title == title)&&(identical(other.body, body) || other.body == body));
}

@JsonKey(includeFromJson: false, includeToJson: false)
@override
int get hashCode {
    return Object.hash(runtimeType,id,rating,reviewer,verifiedPurchase,createdAt,title,body);
}

@override
String toString() {
    return 'ProductReviewDto(id: $id, rating: $rating, reviewer: $reviewer, verifiedPurchase: $verifiedPurchase, createdAt: $createdAt, title: $title, body: $body)';
}


}

/// @nodoc
abstract mixin class _$ProductReviewDtoCopyWith<$Res> implements $ProductReviewDtoCopyWith<$Res> {
  factory _$ProductReviewDtoCopyWith(_ProductReviewDto value, $Res Function(_ProductReviewDto) _then) = __$ProductReviewDtoCopyWithImpl;
@override @useResult
$Res call({
 int id, int rating, String reviewer, bool verifiedPurchase, DateTime createdAt, String? title, String? body
});




}
/// @nodoc
class __$ProductReviewDtoCopyWithImpl<$Res>
    implements _$ProductReviewDtoCopyWith<$Res> {
  __$ProductReviewDtoCopyWithImpl(this._self, this._then);

  final _ProductReviewDto _self;
  final $Res Function(_ProductReviewDto) _then;

/// Create a copy of ProductReviewDto
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? rating = null,Object? reviewer = null,Object? verifiedPurchase = null,Object? createdAt = null,Object? title = freezed,Object? body = freezed,}) {
  return _then(_ProductReviewDto(
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

// dart format on
