// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'catalog.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$CatalogQuery {

 String? get category; String? get platform; String? get region; String? get deliveryType; String? get minPrice; String? get maxPrice;/// `popular`, `newest`, `price_asc`, `price_desc` or `name`.
 String? get sort; int get page; int get perPage;
/// Create a copy of CatalogQuery
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CatalogQueryCopyWith<CatalogQuery> get copyWith => _$CatalogQueryCopyWithImpl<CatalogQuery>(this as CatalogQuery, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as CatalogQuery;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CatalogQuery&&(identical(other.category, _this.category) || other.category == _this.category)&&(identical(other.platform, _this.platform) || other.platform == _this.platform)&&(identical(other.region, _this.region) || other.region == _this.region)&&(identical(other.deliveryType, _this.deliveryType) || other.deliveryType == _this.deliveryType)&&(identical(other.minPrice, _this.minPrice) || other.minPrice == _this.minPrice)&&(identical(other.maxPrice, _this.maxPrice) || other.maxPrice == _this.maxPrice)&&(identical(other.sort, _this.sort) || other.sort == _this.sort)&&(identical(other.page, _this.page) || other.page == _this.page)&&(identical(other.perPage, _this.perPage) || other.perPage == _this.perPage));
}


@override
int get hashCode {
  final _this = this as CatalogQuery;
  return Object.hash(runtimeType,_this.category,_this.platform,_this.region,_this.deliveryType,_this.minPrice,_this.maxPrice,_this.sort,_this.page,_this.perPage);
}

@override
String toString() {
  final _this = this as CatalogQuery;
  return 'CatalogQuery(category: ${_this.category}, platform: ${_this.platform}, region: ${_this.region}, deliveryType: ${_this.deliveryType}, minPrice: ${_this.minPrice}, maxPrice: ${_this.maxPrice}, sort: ${_this.sort}, page: ${_this.page}, perPage: ${_this.perPage})';
}


}

/// @nodoc
abstract mixin class $CatalogQueryCopyWith<$Res>  {
  factory $CatalogQueryCopyWith(CatalogQuery value, $Res Function(CatalogQuery) _then) = _$CatalogQueryCopyWithImpl;
@useResult
$Res call({
 String? category, String? platform, String? region, String? deliveryType, String? minPrice, String? maxPrice, String? sort, int page, int perPage
});




}
/// @nodoc
class _$CatalogQueryCopyWithImpl<$Res>
    implements $CatalogQueryCopyWith<$Res> {
  _$CatalogQueryCopyWithImpl(this._self, this._then);

  final CatalogQuery _self;
  final $Res Function(CatalogQuery) _then;

/// Create a copy of CatalogQuery
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? category = freezed,Object? platform = freezed,Object? region = freezed,Object? deliveryType = freezed,Object? minPrice = freezed,Object? maxPrice = freezed,Object? sort = freezed,Object? page = null,Object? perPage = null,}) {
  return _then(CatalogQuery(
category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String?,platform: freezed == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as String?,region: freezed == region ? _self.region : region // ignore: cast_nullable_to_non_nullable
as String?,deliveryType: freezed == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String?,minPrice: freezed == minPrice ? _self.minPrice : minPrice // ignore: cast_nullable_to_non_nullable
as String?,maxPrice: freezed == maxPrice ? _self.maxPrice : maxPrice // ignore: cast_nullable_to_non_nullable
as String?,sort: freezed == sort ? _self.sort : sort // ignore: cast_nullable_to_non_nullable
as String?,page: null == page ? _self.page : page // ignore: cast_nullable_to_non_nullable
as int,perPage: null == perPage ? _self.perPage : perPage // ignore: cast_nullable_to_non_nullable
as int,
  ));
}

}


/// Adds pattern-matching-related methods to [CatalogQuery].
extension CatalogQueryPatterns on CatalogQuery {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CatalogQuery value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CatalogQuery() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CatalogQuery value)  $default,){
final _that = this;
switch (_that) {
case _CatalogQuery():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CatalogQuery value)?  $default,){
final _that = this;
switch (_that) {
case _CatalogQuery() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String? category,  String? platform,  String? region,  String? deliveryType,  String? minPrice,  String? maxPrice,  String? sort,  int page,  int perPage)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CatalogQuery() when $default != null:
return $default(_that.category,_that.platform,_that.region,_that.deliveryType,_that.minPrice,_that.maxPrice,_that.sort,_that.page,_that.perPage);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String? category,  String? platform,  String? region,  String? deliveryType,  String? minPrice,  String? maxPrice,  String? sort,  int page,  int perPage)  $default,) {final _that = this;
switch (_that) {
case _CatalogQuery():
return $default(_that.category,_that.platform,_that.region,_that.deliveryType,_that.minPrice,_that.maxPrice,_that.sort,_that.page,_that.perPage);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String? category,  String? platform,  String? region,  String? deliveryType,  String? minPrice,  String? maxPrice,  String? sort,  int page,  int perPage)?  $default,) {final _that = this;
switch (_that) {
case _CatalogQuery() when $default != null:
return $default(_that.category,_that.platform,_that.region,_that.deliveryType,_that.minPrice,_that.maxPrice,_that.sort,_that.page,_that.perPage);case _:
  return null;

}
}

}

/// @nodoc


class _CatalogQuery implements CatalogQuery {
  const _CatalogQuery({this.category, this.platform, this.region, this.deliveryType, this.minPrice, this.maxPrice, this.sort, this.page = 1, this.perPage = 20});
  

@override final  String? category;
@override final  String? platform;
@override final  String? region;
@override final  String? deliveryType;
@override final  String? minPrice;
@override final  String? maxPrice;
/// `popular`, `newest`, `price_asc`, `price_desc` or `name`.
@override final  String? sort;
@override@JsonKey() final  int page;
@override@JsonKey() final  int perPage;

/// Create a copy of CatalogQuery
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CatalogQueryCopyWith<_CatalogQuery> get copyWith => __$CatalogQueryCopyWithImpl<_CatalogQuery>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CatalogQuery&&(identical(other.category, category) || other.category == category)&&(identical(other.platform, platform) || other.platform == platform)&&(identical(other.region, region) || other.region == region)&&(identical(other.deliveryType, deliveryType) || other.deliveryType == deliveryType)&&(identical(other.minPrice, minPrice) || other.minPrice == minPrice)&&(identical(other.maxPrice, maxPrice) || other.maxPrice == maxPrice)&&(identical(other.sort, sort) || other.sort == sort)&&(identical(other.page, page) || other.page == page)&&(identical(other.perPage, perPage) || other.perPage == perPage));
}


@override
int get hashCode {
    return Object.hash(runtimeType,category,platform,region,deliveryType,minPrice,maxPrice,sort,page,perPage);
}

@override
String toString() {
    return 'CatalogQuery(category: $category, platform: $platform, region: $region, deliveryType: $deliveryType, minPrice: $minPrice, maxPrice: $maxPrice, sort: $sort, page: $page, perPage: $perPage)';
}


}

/// @nodoc
abstract mixin class _$CatalogQueryCopyWith<$Res> implements $CatalogQueryCopyWith<$Res> {
  factory _$CatalogQueryCopyWith(_CatalogQuery value, $Res Function(_CatalogQuery) _then) = __$CatalogQueryCopyWithImpl;
@override @useResult
$Res call({
 String? category, String? platform, String? region, String? deliveryType, String? minPrice, String? maxPrice, String? sort, int page, int perPage
});




}
/// @nodoc
class __$CatalogQueryCopyWithImpl<$Res>
    implements _$CatalogQueryCopyWith<$Res> {
  __$CatalogQueryCopyWithImpl(this._self, this._then);

  final _CatalogQuery _self;
  final $Res Function(_CatalogQuery) _then;

/// Create a copy of CatalogQuery
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? category = freezed,Object? platform = freezed,Object? region = freezed,Object? deliveryType = freezed,Object? minPrice = freezed,Object? maxPrice = freezed,Object? sort = freezed,Object? page = null,Object? perPage = null,}) {
  return _then(_CatalogQuery(
category: freezed == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as String?,platform: freezed == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as String?,region: freezed == region ? _self.region : region // ignore: cast_nullable_to_non_nullable
as String?,deliveryType: freezed == deliveryType ? _self.deliveryType : deliveryType // ignore: cast_nullable_to_non_nullable
as String?,minPrice: freezed == minPrice ? _self.minPrice : minPrice // ignore: cast_nullable_to_non_nullable
as String?,maxPrice: freezed == maxPrice ? _self.maxPrice : maxPrice // ignore: cast_nullable_to_non_nullable
as String?,sort: freezed == sort ? _self.sort : sort // ignore: cast_nullable_to_non_nullable
as String?,page: null == page ? _self.page : page // ignore: cast_nullable_to_non_nullable
as int,perPage: null == perPage ? _self.perPage : perPage // ignore: cast_nullable_to_non_nullable
as int,
  ));
}


}

/// @nodoc
mixin _$CatalogPage {

 List<ProductCard> get products; PageInfo get page; String get displayCurrency;
/// Create a copy of CatalogPage
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CatalogPageCopyWith<CatalogPage> get copyWith => _$CatalogPageCopyWithImpl<CatalogPage>(this as CatalogPage, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as CatalogPage;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CatalogPage&&const DeepCollectionEquality().equals(other.products, _this.products)&&(identical(other.page, _this.page) || other.page == _this.page)&&(identical(other.displayCurrency, _this.displayCurrency) || other.displayCurrency == _this.displayCurrency));
}


@override
int get hashCode {
  final _this = this as CatalogPage;
  return Object.hash(runtimeType,const DeepCollectionEquality().hash(_this.products),_this.page,_this.displayCurrency);
}

@override
String toString() {
  final _this = this as CatalogPage;
  return 'CatalogPage(products: ${_this.products}, page: ${_this.page}, displayCurrency: ${_this.displayCurrency})';
}


}

/// @nodoc
abstract mixin class $CatalogPageCopyWith<$Res>  {
  factory $CatalogPageCopyWith(CatalogPage value, $Res Function(CatalogPage) _then) = _$CatalogPageCopyWithImpl;
@useResult
$Res call({
 List<ProductCard> products, PageInfo page, String displayCurrency
});


$PageInfoCopyWith<$Res> get page;

}
/// @nodoc
class _$CatalogPageCopyWithImpl<$Res>
    implements $CatalogPageCopyWith<$Res> {
  _$CatalogPageCopyWithImpl(this._self, this._then);

  final CatalogPage _self;
  final $Res Function(CatalogPage) _then;

/// Create a copy of CatalogPage
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? products = null,Object? page = null,Object? displayCurrency = null,}) {
  return _then(CatalogPage(
products: null == products ? _self.products : products // ignore: cast_nullable_to_non_nullable
as List<ProductCard>,page: null == page ? _self.page : page // ignore: cast_nullable_to_non_nullable
as PageInfo,displayCurrency: null == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String,
  ));
}
/// Create a copy of CatalogPage
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$PageInfoCopyWith<$Res> get page {
  
  return $PageInfoCopyWith<$Res>(_self.page, (value) {
    return _then(_self.copyWith(page: value));
  });
}
}


/// Adds pattern-matching-related methods to [CatalogPage].
extension CatalogPagePatterns on CatalogPage {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _CatalogPage value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _CatalogPage() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _CatalogPage value)  $default,){
final _that = this;
switch (_that) {
case _CatalogPage():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _CatalogPage value)?  $default,){
final _that = this;
switch (_that) {
case _CatalogPage() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<ProductCard> products,  PageInfo page,  String displayCurrency)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _CatalogPage() when $default != null:
return $default(_that.products,_that.page,_that.displayCurrency);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<ProductCard> products,  PageInfo page,  String displayCurrency)  $default,) {final _that = this;
switch (_that) {
case _CatalogPage():
return $default(_that.products,_that.page,_that.displayCurrency);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<ProductCard> products,  PageInfo page,  String displayCurrency)?  $default,) {final _that = this;
switch (_that) {
case _CatalogPage() when $default != null:
return $default(_that.products,_that.page,_that.displayCurrency);case _:
  return null;

}
}

}

/// @nodoc


class _CatalogPage implements CatalogPage {
  const _CatalogPage({required  List<ProductCard> products, required this.page, required this.displayCurrency}): _products = products;
  

 final  List<ProductCard> _products;
@override List<ProductCard> get products {
  if (_products is EqualUnmodifiableListView) return _products;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_products);
}

@override final  PageInfo page;
@override final  String displayCurrency;

/// Create a copy of CatalogPage
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CatalogPageCopyWith<_CatalogPage> get copyWith => __$CatalogPageCopyWithImpl<_CatalogPage>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _CatalogPage&&const DeepCollectionEquality().equals(other.products, _products)&&(identical(other.page, page) || other.page == page)&&(identical(other.displayCurrency, displayCurrency) || other.displayCurrency == displayCurrency));
}


@override
int get hashCode {
    return Object.hash(runtimeType,const DeepCollectionEquality().hash(_products),page,displayCurrency);
}

@override
String toString() {
    return 'CatalogPage(products: $products, page: $page, displayCurrency: $displayCurrency)';
}


}

/// @nodoc
abstract mixin class _$CatalogPageCopyWith<$Res> implements $CatalogPageCopyWith<$Res> {
  factory _$CatalogPageCopyWith(_CatalogPage value, $Res Function(_CatalogPage) _then) = __$CatalogPageCopyWithImpl;
@override @useResult
$Res call({
 List<ProductCard> products, PageInfo page, String displayCurrency
});


@override $PageInfoCopyWith<$Res> get page;

}
/// @nodoc
class __$CatalogPageCopyWithImpl<$Res>
    implements _$CatalogPageCopyWith<$Res> {
  __$CatalogPageCopyWithImpl(this._self, this._then);

  final _CatalogPage _self;
  final $Res Function(_CatalogPage) _then;

/// Create a copy of CatalogPage
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? products = null,Object? page = null,Object? displayCurrency = null,}) {
  return _then(_CatalogPage(
products: null == products ? _self._products : products // ignore: cast_nullable_to_non_nullable
as List<ProductCard>,page: null == page ? _self.page : page // ignore: cast_nullable_to_non_nullable
as PageInfo,displayCurrency: null == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

/// Create a copy of CatalogPage
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
mixin _$Platform {

 String get name; String get slug; int get productCount; bool get isFeatured; String? get description; MediaImage? get logo; MediaImage? get banner;
/// Create a copy of Platform
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$PlatformCopyWith<Platform> get copyWith => _$PlatformCopyWithImpl<Platform>(this as Platform, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as Platform;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is Platform&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug)&&(identical(other.productCount, _this.productCount) || other.productCount == _this.productCount)&&(identical(other.isFeatured, _this.isFeatured) || other.isFeatured == _this.isFeatured)&&(identical(other.description, _this.description) || other.description == _this.description)&&(identical(other.logo, _this.logo) || other.logo == _this.logo)&&(identical(other.banner, _this.banner) || other.banner == _this.banner));
}


@override
int get hashCode {
  final _this = this as Platform;
  return Object.hash(runtimeType,_this.name,_this.slug,_this.productCount,_this.isFeatured,_this.description,_this.logo,_this.banner);
}

@override
String toString() {
  final _this = this as Platform;
  return 'Platform(name: ${_this.name}, slug: ${_this.slug}, productCount: ${_this.productCount}, isFeatured: ${_this.isFeatured}, description: ${_this.description}, logo: ${_this.logo}, banner: ${_this.banner})';
}


}

/// @nodoc
abstract mixin class $PlatformCopyWith<$Res>  {
  factory $PlatformCopyWith(Platform value, $Res Function(Platform) _then) = _$PlatformCopyWithImpl;
@useResult
$Res call({
 String name, String slug, int productCount, bool isFeatured, String? description, MediaImage? logo, MediaImage? banner
});


$MediaImageCopyWith<$Res>? get logo;$MediaImageCopyWith<$Res>? get banner;

}
/// @nodoc
class _$PlatformCopyWithImpl<$Res>
    implements $PlatformCopyWith<$Res> {
  _$PlatformCopyWithImpl(this._self, this._then);

  final Platform _self;
  final $Res Function(Platform) _then;

/// Create a copy of Platform
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? name = null,Object? slug = null,Object? productCount = null,Object? isFeatured = null,Object? description = freezed,Object? logo = freezed,Object? banner = freezed,}) {
  return _then(Platform(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,productCount: null == productCount ? _self.productCount : productCount // ignore: cast_nullable_to_non_nullable
as int,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,logo: freezed == logo ? _self.logo : logo // ignore: cast_nullable_to_non_nullable
as MediaImage?,banner: freezed == banner ? _self.banner : banner // ignore: cast_nullable_to_non_nullable
as MediaImage?,
  ));
}
/// Create a copy of Platform
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageCopyWith<$Res>? get logo {
    if (_self.logo == null) {
    return null;
  }

  return $MediaImageCopyWith<$Res>(_self.logo!, (value) {
    return _then(_self.copyWith(logo: value));
  });
}/// Create a copy of Platform
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageCopyWith<$Res>? get banner {
    if (_self.banner == null) {
    return null;
  }

  return $MediaImageCopyWith<$Res>(_self.banner!, (value) {
    return _then(_self.copyWith(banner: value));
  });
}
}


/// Adds pattern-matching-related methods to [Platform].
extension PlatformPatterns on Platform {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _Platform value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _Platform() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _Platform value)  $default,){
final _that = this;
switch (_that) {
case _Platform():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _Platform value)?  $default,){
final _that = this;
switch (_that) {
case _Platform() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String name,  String slug,  int productCount,  bool isFeatured,  String? description,  MediaImage? logo,  MediaImage? banner)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _Platform() when $default != null:
return $default(_that.name,_that.slug,_that.productCount,_that.isFeatured,_that.description,_that.logo,_that.banner);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String name,  String slug,  int productCount,  bool isFeatured,  String? description,  MediaImage? logo,  MediaImage? banner)  $default,) {final _that = this;
switch (_that) {
case _Platform():
return $default(_that.name,_that.slug,_that.productCount,_that.isFeatured,_that.description,_that.logo,_that.banner);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String name,  String slug,  int productCount,  bool isFeatured,  String? description,  MediaImage? logo,  MediaImage? banner)?  $default,) {final _that = this;
switch (_that) {
case _Platform() when $default != null:
return $default(_that.name,_that.slug,_that.productCount,_that.isFeatured,_that.description,_that.logo,_that.banner);case _:
  return null;

}
}

}

/// @nodoc


class _Platform implements Platform {
  const _Platform({required this.name, required this.slug, required this.productCount, required this.isFeatured, this.description, this.logo, this.banner});
  

@override final  String name;
@override final  String slug;
@override final  int productCount;
@override final  bool isFeatured;
@override final  String? description;
@override final  MediaImage? logo;
@override final  MediaImage? banner;

/// Create a copy of Platform
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$PlatformCopyWith<_Platform> get copyWith => __$PlatformCopyWithImpl<_Platform>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _Platform&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug)&&(identical(other.productCount, productCount) || other.productCount == productCount)&&(identical(other.isFeatured, isFeatured) || other.isFeatured == isFeatured)&&(identical(other.description, description) || other.description == description)&&(identical(other.logo, logo) || other.logo == logo)&&(identical(other.banner, banner) || other.banner == banner));
}


@override
int get hashCode {
    return Object.hash(runtimeType,name,slug,productCount,isFeatured,description,logo,banner);
}

@override
String toString() {
    return 'Platform(name: $name, slug: $slug, productCount: $productCount, isFeatured: $isFeatured, description: $description, logo: $logo, banner: $banner)';
}


}

/// @nodoc
abstract mixin class _$PlatformCopyWith<$Res> implements $PlatformCopyWith<$Res> {
  factory _$PlatformCopyWith(_Platform value, $Res Function(_Platform) _then) = __$PlatformCopyWithImpl;
@override @useResult
$Res call({
 String name, String slug, int productCount, bool isFeatured, String? description, MediaImage? logo, MediaImage? banner
});


@override $MediaImageCopyWith<$Res>? get logo;@override $MediaImageCopyWith<$Res>? get banner;

}
/// @nodoc
class __$PlatformCopyWithImpl<$Res>
    implements _$PlatformCopyWith<$Res> {
  __$PlatformCopyWithImpl(this._self, this._then);

  final _Platform _self;
  final $Res Function(_Platform) _then;

/// Create a copy of Platform
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? name = null,Object? slug = null,Object? productCount = null,Object? isFeatured = null,Object? description = freezed,Object? logo = freezed,Object? banner = freezed,}) {
  return _then(_Platform(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,productCount: null == productCount ? _self.productCount : productCount // ignore: cast_nullable_to_non_nullable
as int,isFeatured: null == isFeatured ? _self.isFeatured : isFeatured // ignore: cast_nullable_to_non_nullable
as bool,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,logo: freezed == logo ? _self.logo : logo // ignore: cast_nullable_to_non_nullable
as MediaImage?,banner: freezed == banner ? _self.banner : banner // ignore: cast_nullable_to_non_nullable
as MediaImage?,
  ));
}

/// Create a copy of Platform
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageCopyWith<$Res>? get logo {
    if (_self.logo == null) {
    return null;
  }

  return $MediaImageCopyWith<$Res>(_self.logo!, (value) {
    return _then(_self.copyWith(logo: value));
  });
}/// Create a copy of Platform
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageCopyWith<$Res>? get banner {
    if (_self.banner == null) {
    return null;
  }

  return $MediaImageCopyWith<$Res>(_self.banner!, (value) {
    return _then(_self.copyWith(banner: value));
  });
}
}

/// @nodoc
mixin _$Category {

 String get name; String get slug; int get productCount; String? get description;/// Parent slug, null for a top-level category.
 String? get parent; MediaImage? get banner;
/// Create a copy of Category
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CategoryCopyWith<Category> get copyWith => _$CategoryCopyWithImpl<Category>(this as Category, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as Category;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is Category&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug)&&(identical(other.productCount, _this.productCount) || other.productCount == _this.productCount)&&(identical(other.description, _this.description) || other.description == _this.description)&&(identical(other.parent, _this.parent) || other.parent == _this.parent)&&(identical(other.banner, _this.banner) || other.banner == _this.banner));
}


@override
int get hashCode {
  final _this = this as Category;
  return Object.hash(runtimeType,_this.name,_this.slug,_this.productCount,_this.description,_this.parent,_this.banner);
}

@override
String toString() {
  final _this = this as Category;
  return 'Category(name: ${_this.name}, slug: ${_this.slug}, productCount: ${_this.productCount}, description: ${_this.description}, parent: ${_this.parent}, banner: ${_this.banner})';
}


}

/// @nodoc
abstract mixin class $CategoryCopyWith<$Res>  {
  factory $CategoryCopyWith(Category value, $Res Function(Category) _then) = _$CategoryCopyWithImpl;
@useResult
$Res call({
 String name, String slug, int productCount, String? description, String? parent, MediaImage? banner
});


$MediaImageCopyWith<$Res>? get banner;

}
/// @nodoc
class _$CategoryCopyWithImpl<$Res>
    implements $CategoryCopyWith<$Res> {
  _$CategoryCopyWithImpl(this._self, this._then);

  final Category _self;
  final $Res Function(Category) _then;

/// Create a copy of Category
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? name = null,Object? slug = null,Object? productCount = null,Object? description = freezed,Object? parent = freezed,Object? banner = freezed,}) {
  return _then(Category(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,productCount: null == productCount ? _self.productCount : productCount // ignore: cast_nullable_to_non_nullable
as int,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,parent: freezed == parent ? _self.parent : parent // ignore: cast_nullable_to_non_nullable
as String?,banner: freezed == banner ? _self.banner : banner // ignore: cast_nullable_to_non_nullable
as MediaImage?,
  ));
}
/// Create a copy of Category
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageCopyWith<$Res>? get banner {
    if (_self.banner == null) {
    return null;
  }

  return $MediaImageCopyWith<$Res>(_self.banner!, (value) {
    return _then(_self.copyWith(banner: value));
  });
}
}


/// Adds pattern-matching-related methods to [Category].
extension CategoryPatterns on Category {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _Category value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _Category() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _Category value)  $default,){
final _that = this;
switch (_that) {
case _Category():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _Category value)?  $default,){
final _that = this;
switch (_that) {
case _Category() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String name,  String slug,  int productCount,  String? description,  String? parent,  MediaImage? banner)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _Category() when $default != null:
return $default(_that.name,_that.slug,_that.productCount,_that.description,_that.parent,_that.banner);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String name,  String slug,  int productCount,  String? description,  String? parent,  MediaImage? banner)  $default,) {final _that = this;
switch (_that) {
case _Category():
return $default(_that.name,_that.slug,_that.productCount,_that.description,_that.parent,_that.banner);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String name,  String slug,  int productCount,  String? description,  String? parent,  MediaImage? banner)?  $default,) {final _that = this;
switch (_that) {
case _Category() when $default != null:
return $default(_that.name,_that.slug,_that.productCount,_that.description,_that.parent,_that.banner);case _:
  return null;

}
}

}

/// @nodoc


class _Category implements Category {
  const _Category({required this.name, required this.slug, required this.productCount, this.description, this.parent, this.banner});
  

@override final  String name;
@override final  String slug;
@override final  int productCount;
@override final  String? description;
/// Parent slug, null for a top-level category.
@override final  String? parent;
@override final  MediaImage? banner;

/// Create a copy of Category
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CategoryCopyWith<_Category> get copyWith => __$CategoryCopyWithImpl<_Category>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _Category&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug)&&(identical(other.productCount, productCount) || other.productCount == productCount)&&(identical(other.description, description) || other.description == description)&&(identical(other.parent, parent) || other.parent == parent)&&(identical(other.banner, banner) || other.banner == banner));
}


@override
int get hashCode {
    return Object.hash(runtimeType,name,slug,productCount,description,parent,banner);
}

@override
String toString() {
    return 'Category(name: $name, slug: $slug, productCount: $productCount, description: $description, parent: $parent, banner: $banner)';
}


}

/// @nodoc
abstract mixin class _$CategoryCopyWith<$Res> implements $CategoryCopyWith<$Res> {
  factory _$CategoryCopyWith(_Category value, $Res Function(_Category) _then) = __$CategoryCopyWithImpl;
@override @useResult
$Res call({
 String name, String slug, int productCount, String? description, String? parent, MediaImage? banner
});


@override $MediaImageCopyWith<$Res>? get banner;

}
/// @nodoc
class __$CategoryCopyWithImpl<$Res>
    implements _$CategoryCopyWith<$Res> {
  __$CategoryCopyWithImpl(this._self, this._then);

  final _Category _self;
  final $Res Function(_Category) _then;

/// Create a copy of Category
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? name = null,Object? slug = null,Object? productCount = null,Object? description = freezed,Object? parent = freezed,Object? banner = freezed,}) {
  return _then(_Category(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,productCount: null == productCount ? _self.productCount : productCount // ignore: cast_nullable_to_non_nullable
as int,description: freezed == description ? _self.description : description // ignore: cast_nullable_to_non_nullable
as String?,parent: freezed == parent ? _self.parent : parent // ignore: cast_nullable_to_non_nullable
as String?,banner: freezed == banner ? _self.banner : banner // ignore: cast_nullable_to_non_nullable
as MediaImage?,
  ));
}

/// Create a copy of Category
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageCopyWith<$Res>? get banner {
    if (_self.banner == null) {
    return null;
  }

  return $MediaImageCopyWith<$Res>(_self.banner!, (value) {
    return _then(_self.copyWith(banner: value));
  });
}
}

/// @nodoc
mixin _$Country {

 String get code; String get name; String? get dialCode; String? get defaultCurrency;
/// Create a copy of Country
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CountryCopyWith<Country> get copyWith => _$CountryCopyWithImpl<Country>(this as Country, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as Country;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is Country&&(identical(other.code, _this.code) || other.code == _this.code)&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.dialCode, _this.dialCode) || other.dialCode == _this.dialCode)&&(identical(other.defaultCurrency, _this.defaultCurrency) || other.defaultCurrency == _this.defaultCurrency));
}


@override
int get hashCode {
  final _this = this as Country;
  return Object.hash(runtimeType,_this.code,_this.name,_this.dialCode,_this.defaultCurrency);
}

@override
String toString() {
  final _this = this as Country;
  return 'Country(code: ${_this.code}, name: ${_this.name}, dialCode: ${_this.dialCode}, defaultCurrency: ${_this.defaultCurrency})';
}


}

/// @nodoc
abstract mixin class $CountryCopyWith<$Res>  {
  factory $CountryCopyWith(Country value, $Res Function(Country) _then) = _$CountryCopyWithImpl;
@useResult
$Res call({
 String code, String name, String? dialCode, String? defaultCurrency
});




}
/// @nodoc
class _$CountryCopyWithImpl<$Res>
    implements $CountryCopyWith<$Res> {
  _$CountryCopyWithImpl(this._self, this._then);

  final Country _self;
  final $Res Function(Country) _then;

/// Create a copy of Country
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? code = null,Object? name = null,Object? dialCode = freezed,Object? defaultCurrency = freezed,}) {
  return _then(Country(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,dialCode: freezed == dialCode ? _self.dialCode : dialCode // ignore: cast_nullable_to_non_nullable
as String?,defaultCurrency: freezed == defaultCurrency ? _self.defaultCurrency : defaultCurrency // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [Country].
extension CountryPatterns on Country {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _Country value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _Country() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _Country value)  $default,){
final _that = this;
switch (_that) {
case _Country():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _Country value)?  $default,){
final _that = this;
switch (_that) {
case _Country() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String code,  String name,  String? dialCode,  String? defaultCurrency)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _Country() when $default != null:
return $default(_that.code,_that.name,_that.dialCode,_that.defaultCurrency);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String code,  String name,  String? dialCode,  String? defaultCurrency)  $default,) {final _that = this;
switch (_that) {
case _Country():
return $default(_that.code,_that.name,_that.dialCode,_that.defaultCurrency);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String code,  String name,  String? dialCode,  String? defaultCurrency)?  $default,) {final _that = this;
switch (_that) {
case _Country() when $default != null:
return $default(_that.code,_that.name,_that.dialCode,_that.defaultCurrency);case _:
  return null;

}
}

}

/// @nodoc


class _Country implements Country {
  const _Country({required this.code, required this.name, this.dialCode, this.defaultCurrency});
  

@override final  String code;
@override final  String name;
@override final  String? dialCode;
@override final  String? defaultCurrency;

/// Create a copy of Country
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$CountryCopyWith<_Country> get copyWith => __$CountryCopyWithImpl<_Country>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _Country&&(identical(other.code, code) || other.code == code)&&(identical(other.name, name) || other.name == name)&&(identical(other.dialCode, dialCode) || other.dialCode == dialCode)&&(identical(other.defaultCurrency, defaultCurrency) || other.defaultCurrency == defaultCurrency));
}


@override
int get hashCode {
    return Object.hash(runtimeType,code,name,dialCode,defaultCurrency);
}

@override
String toString() {
    return 'Country(code: $code, name: $name, dialCode: $dialCode, defaultCurrency: $defaultCurrency)';
}


}

/// @nodoc
abstract mixin class _$CountryCopyWith<$Res> implements $CountryCopyWith<$Res> {
  factory _$CountryCopyWith(_Country value, $Res Function(_Country) _then) = __$CountryCopyWithImpl;
@override @useResult
$Res call({
 String code, String name, String? dialCode, String? defaultCurrency
});




}
/// @nodoc
class __$CountryCopyWithImpl<$Res>
    implements _$CountryCopyWith<$Res> {
  __$CountryCopyWithImpl(this._self, this._then);

  final _Country _self;
  final $Res Function(_Country) _then;

/// Create a copy of Country
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? code = null,Object? name = null,Object? dialCode = freezed,Object? defaultCurrency = freezed,}) {
  return _then(_Country(
code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,dialCode: freezed == dialCode ? _self.dialCode : dialCode // ignore: cast_nullable_to_non_nullable
as String?,defaultCurrency: freezed == defaultCurrency ? _self.defaultCurrency : defaultCurrency // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc
mixin _$DisplayCurrencyOption {

 String get name; CurrencyInfo get info;
/// Create a copy of DisplayCurrencyOption
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$DisplayCurrencyOptionCopyWith<DisplayCurrencyOption> get copyWith => _$DisplayCurrencyOptionCopyWithImpl<DisplayCurrencyOption>(this as DisplayCurrencyOption, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as DisplayCurrencyOption;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is DisplayCurrencyOption&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.info, _this.info) || other.info == _this.info));
}


@override
int get hashCode {
  final _this = this as DisplayCurrencyOption;
  return Object.hash(runtimeType,_this.name,_this.info);
}

@override
String toString() {
  final _this = this as DisplayCurrencyOption;
  return 'DisplayCurrencyOption(name: ${_this.name}, info: ${_this.info})';
}


}

/// @nodoc
abstract mixin class $DisplayCurrencyOptionCopyWith<$Res>  {
  factory $DisplayCurrencyOptionCopyWith(DisplayCurrencyOption value, $Res Function(DisplayCurrencyOption) _then) = _$DisplayCurrencyOptionCopyWithImpl;
@useResult
$Res call({
 String name, CurrencyInfo info
});




}
/// @nodoc
class _$DisplayCurrencyOptionCopyWithImpl<$Res>
    implements $DisplayCurrencyOptionCopyWith<$Res> {
  _$DisplayCurrencyOptionCopyWithImpl(this._self, this._then);

  final DisplayCurrencyOption _self;
  final $Res Function(DisplayCurrencyOption) _then;

/// Create a copy of DisplayCurrencyOption
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? name = null,Object? info = null,}) {
  return _then(DisplayCurrencyOption(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,info: null == info ? _self.info : info // ignore: cast_nullable_to_non_nullable
as CurrencyInfo,
  ));
}

}


/// Adds pattern-matching-related methods to [DisplayCurrencyOption].
extension DisplayCurrencyOptionPatterns on DisplayCurrencyOption {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _DisplayCurrencyOption value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _DisplayCurrencyOption() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _DisplayCurrencyOption value)  $default,){
final _that = this;
switch (_that) {
case _DisplayCurrencyOption():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _DisplayCurrencyOption value)?  $default,){
final _that = this;
switch (_that) {
case _DisplayCurrencyOption() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String name,  CurrencyInfo info)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _DisplayCurrencyOption() when $default != null:
return $default(_that.name,_that.info);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String name,  CurrencyInfo info)  $default,) {final _that = this;
switch (_that) {
case _DisplayCurrencyOption():
return $default(_that.name,_that.info);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String name,  CurrencyInfo info)?  $default,) {final _that = this;
switch (_that) {
case _DisplayCurrencyOption() when $default != null:
return $default(_that.name,_that.info);case _:
  return null;

}
}

}

/// @nodoc


class _DisplayCurrencyOption implements DisplayCurrencyOption {
  const _DisplayCurrencyOption({required this.name, required this.info});
  

@override final  String name;
@override final  CurrencyInfo info;

/// Create a copy of DisplayCurrencyOption
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$DisplayCurrencyOptionCopyWith<_DisplayCurrencyOption> get copyWith => __$DisplayCurrencyOptionCopyWithImpl<_DisplayCurrencyOption>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _DisplayCurrencyOption&&(identical(other.name, name) || other.name == name)&&(identical(other.info, info) || other.info == info));
}


@override
int get hashCode {
    return Object.hash(runtimeType,name,info);
}

@override
String toString() {
    return 'DisplayCurrencyOption(name: $name, info: $info)';
}


}

/// @nodoc
abstract mixin class _$DisplayCurrencyOptionCopyWith<$Res> implements $DisplayCurrencyOptionCopyWith<$Res> {
  factory _$DisplayCurrencyOptionCopyWith(_DisplayCurrencyOption value, $Res Function(_DisplayCurrencyOption) _then) = __$DisplayCurrencyOptionCopyWithImpl;
@override @useResult
$Res call({
 String name, CurrencyInfo info
});




}
/// @nodoc
class __$DisplayCurrencyOptionCopyWithImpl<$Res>
    implements _$DisplayCurrencyOptionCopyWith<$Res> {
  __$DisplayCurrencyOptionCopyWithImpl(this._self, this._then);

  final _DisplayCurrencyOption _self;
  final $Res Function(_DisplayCurrencyOption) _then;

/// Create a copy of DisplayCurrencyOption
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? name = null,Object? info = null,}) {
  return _then(_DisplayCurrencyOption(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,info: null == info ? _self.info : info // ignore: cast_nullable_to_non_nullable
as CurrencyInfo,
  ));
}


}

// dart format on
