// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'home.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$HomePage {

 List<HomeSection> get sections; List<HomeBanner> get heroBanners; List<HomeBanner> get promoBanners; String get displayCurrency;
/// Create a copy of HomePage
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomePageCopyWith<HomePage> get copyWith => _$HomePageCopyWithImpl<HomePage>(this as HomePage, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as HomePage;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomePage&&const DeepCollectionEquality().equals(other.sections, _this.sections)&&const DeepCollectionEquality().equals(other.heroBanners, _this.heroBanners)&&const DeepCollectionEquality().equals(other.promoBanners, _this.promoBanners)&&(identical(other.displayCurrency, _this.displayCurrency) || other.displayCurrency == _this.displayCurrency));
}


@override
int get hashCode {
  final _this = this as HomePage;
  return Object.hash(runtimeType,const DeepCollectionEquality().hash(_this.sections),const DeepCollectionEquality().hash(_this.heroBanners),const DeepCollectionEquality().hash(_this.promoBanners),_this.displayCurrency);
}

@override
String toString() {
  final _this = this as HomePage;
  return 'HomePage(sections: ${_this.sections}, heroBanners: ${_this.heroBanners}, promoBanners: ${_this.promoBanners}, displayCurrency: ${_this.displayCurrency})';
}


}

/// @nodoc
abstract mixin class $HomePageCopyWith<$Res>  {
  factory $HomePageCopyWith(HomePage value, $Res Function(HomePage) _then) = _$HomePageCopyWithImpl;
@useResult
$Res call({
 List<HomeSection> sections, List<HomeBanner> heroBanners, List<HomeBanner> promoBanners, String displayCurrency
});




}
/// @nodoc
class _$HomePageCopyWithImpl<$Res>
    implements $HomePageCopyWith<$Res> {
  _$HomePageCopyWithImpl(this._self, this._then);

  final HomePage _self;
  final $Res Function(HomePage) _then;

/// Create a copy of HomePage
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? sections = null,Object? heroBanners = null,Object? promoBanners = null,Object? displayCurrency = null,}) {
  return _then(HomePage(
sections: null == sections ? _self.sections : sections // ignore: cast_nullable_to_non_nullable
as List<HomeSection>,heroBanners: null == heroBanners ? _self.heroBanners : heroBanners // ignore: cast_nullable_to_non_nullable
as List<HomeBanner>,promoBanners: null == promoBanners ? _self.promoBanners : promoBanners // ignore: cast_nullable_to_non_nullable
as List<HomeBanner>,displayCurrency: null == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [HomePage].
extension HomePagePatterns on HomePage {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomePage value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomePage() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomePage value)  $default,){
final _that = this;
switch (_that) {
case _HomePage():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomePage value)?  $default,){
final _that = this;
switch (_that) {
case _HomePage() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( List<HomeSection> sections,  List<HomeBanner> heroBanners,  List<HomeBanner> promoBanners,  String displayCurrency)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomePage() when $default != null:
return $default(_that.sections,_that.heroBanners,_that.promoBanners,_that.displayCurrency);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( List<HomeSection> sections,  List<HomeBanner> heroBanners,  List<HomeBanner> promoBanners,  String displayCurrency)  $default,) {final _that = this;
switch (_that) {
case _HomePage():
return $default(_that.sections,_that.heroBanners,_that.promoBanners,_that.displayCurrency);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( List<HomeSection> sections,  List<HomeBanner> heroBanners,  List<HomeBanner> promoBanners,  String displayCurrency)?  $default,) {final _that = this;
switch (_that) {
case _HomePage() when $default != null:
return $default(_that.sections,_that.heroBanners,_that.promoBanners,_that.displayCurrency);case _:
  return null;

}
}

}

/// @nodoc


class _HomePage implements HomePage {
  const _HomePage({required  List<HomeSection> sections, required  List<HomeBanner> heroBanners, required  List<HomeBanner> promoBanners, required this.displayCurrency}): _sections = sections,_heroBanners = heroBanners,_promoBanners = promoBanners;
  

 final  List<HomeSection> _sections;
@override List<HomeSection> get sections {
  if (_sections is EqualUnmodifiableListView) return _sections;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_sections);
}

 final  List<HomeBanner> _heroBanners;
@override List<HomeBanner> get heroBanners {
  if (_heroBanners is EqualUnmodifiableListView) return _heroBanners;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_heroBanners);
}

 final  List<HomeBanner> _promoBanners;
@override List<HomeBanner> get promoBanners {
  if (_promoBanners is EqualUnmodifiableListView) return _promoBanners;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_promoBanners);
}

@override final  String displayCurrency;

/// Create a copy of HomePage
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomePageCopyWith<_HomePage> get copyWith => __$HomePageCopyWithImpl<_HomePage>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomePage&&const DeepCollectionEquality().equals(other.sections, _sections)&&const DeepCollectionEquality().equals(other.heroBanners, _heroBanners)&&const DeepCollectionEquality().equals(other.promoBanners, _promoBanners)&&(identical(other.displayCurrency, displayCurrency) || other.displayCurrency == displayCurrency));
}


@override
int get hashCode {
    return Object.hash(runtimeType,const DeepCollectionEquality().hash(_sections),const DeepCollectionEquality().hash(_heroBanners),const DeepCollectionEquality().hash(_promoBanners),displayCurrency);
}

@override
String toString() {
    return 'HomePage(sections: $sections, heroBanners: $heroBanners, promoBanners: $promoBanners, displayCurrency: $displayCurrency)';
}


}

/// @nodoc
abstract mixin class _$HomePageCopyWith<$Res> implements $HomePageCopyWith<$Res> {
  factory _$HomePageCopyWith(_HomePage value, $Res Function(_HomePage) _then) = __$HomePageCopyWithImpl;
@override @useResult
$Res call({
 List<HomeSection> sections, List<HomeBanner> heroBanners, List<HomeBanner> promoBanners, String displayCurrency
});




}
/// @nodoc
class __$HomePageCopyWithImpl<$Res>
    implements _$HomePageCopyWith<$Res> {
  __$HomePageCopyWithImpl(this._self, this._then);

  final _HomePage _self;
  final $Res Function(_HomePage) _then;

/// Create a copy of HomePage
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? sections = null,Object? heroBanners = null,Object? promoBanners = null,Object? displayCurrency = null,}) {
  return _then(_HomePage(
sections: null == sections ? _self._sections : sections // ignore: cast_nullable_to_non_nullable
as List<HomeSection>,heroBanners: null == heroBanners ? _self._heroBanners : heroBanners // ignore: cast_nullable_to_non_nullable
as List<HomeBanner>,promoBanners: null == promoBanners ? _self._promoBanners : promoBanners // ignore: cast_nullable_to_non_nullable
as List<HomeBanner>,displayCurrency: null == displayCurrency ? _self.displayCurrency : displayCurrency // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc
mixin _$HomeSection {

 String get key; String get source; List<HomeItem> get items; String? get title; String? get subtitle; String? get rule;
/// Create a copy of HomeSection
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeSectionCopyWith<HomeSection> get copyWith => _$HomeSectionCopyWithImpl<HomeSection>(this as HomeSection, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as HomeSection;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeSection&&(identical(other.key, _this.key) || other.key == _this.key)&&(identical(other.source, _this.source) || other.source == _this.source)&&const DeepCollectionEquality().equals(other.items, _this.items)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.subtitle, _this.subtitle) || other.subtitle == _this.subtitle)&&(identical(other.rule, _this.rule) || other.rule == _this.rule));
}


@override
int get hashCode {
  final _this = this as HomeSection;
  return Object.hash(runtimeType,_this.key,_this.source,const DeepCollectionEquality().hash(_this.items),_this.title,_this.subtitle,_this.rule);
}

@override
String toString() {
  final _this = this as HomeSection;
  return 'HomeSection(key: ${_this.key}, source: ${_this.source}, items: ${_this.items}, title: ${_this.title}, subtitle: ${_this.subtitle}, rule: ${_this.rule})';
}


}

/// @nodoc
abstract mixin class $HomeSectionCopyWith<$Res>  {
  factory $HomeSectionCopyWith(HomeSection value, $Res Function(HomeSection) _then) = _$HomeSectionCopyWithImpl;
@useResult
$Res call({
 String key, String source, List<HomeItem> items, String? title, String? subtitle, String? rule
});




}
/// @nodoc
class _$HomeSectionCopyWithImpl<$Res>
    implements $HomeSectionCopyWith<$Res> {
  _$HomeSectionCopyWithImpl(this._self, this._then);

  final HomeSection _self;
  final $Res Function(HomeSection) _then;

/// Create a copy of HomeSection
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? key = null,Object? source = null,Object? items = null,Object? title = freezed,Object? subtitle = freezed,Object? rule = freezed,}) {
  return _then(HomeSection(
key: null == key ? _self.key : key // ignore: cast_nullable_to_non_nullable
as String,source: null == source ? _self.source : source // ignore: cast_nullable_to_non_nullable
as String,items: null == items ? _self.items : items // ignore: cast_nullable_to_non_nullable
as List<HomeItem>,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,subtitle: freezed == subtitle ? _self.subtitle : subtitle // ignore: cast_nullable_to_non_nullable
as String?,rule: freezed == rule ? _self.rule : rule // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}

}


/// Adds pattern-matching-related methods to [HomeSection].
extension HomeSectionPatterns on HomeSection {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomeSection value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomeSection() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomeSection value)  $default,){
final _that = this;
switch (_that) {
case _HomeSection():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomeSection value)?  $default,){
final _that = this;
switch (_that) {
case _HomeSection() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String key,  String source,  List<HomeItem> items,  String? title,  String? subtitle,  String? rule)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomeSection() when $default != null:
return $default(_that.key,_that.source,_that.items,_that.title,_that.subtitle,_that.rule);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String key,  String source,  List<HomeItem> items,  String? title,  String? subtitle,  String? rule)  $default,) {final _that = this;
switch (_that) {
case _HomeSection():
return $default(_that.key,_that.source,_that.items,_that.title,_that.subtitle,_that.rule);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String key,  String source,  List<HomeItem> items,  String? title,  String? subtitle,  String? rule)?  $default,) {final _that = this;
switch (_that) {
case _HomeSection() when $default != null:
return $default(_that.key,_that.source,_that.items,_that.title,_that.subtitle,_that.rule);case _:
  return null;

}
}

}

/// @nodoc


class _HomeSection implements HomeSection {
  const _HomeSection({required this.key, required this.source, required  List<HomeItem> items, this.title, this.subtitle, this.rule}): _items = items;
  

@override final  String key;
@override final  String source;
 final  List<HomeItem> _items;
@override List<HomeItem> get items {
  if (_items is EqualUnmodifiableListView) return _items;
  // ignore: implicit_dynamic_type
  return EqualUnmodifiableListView(_items);
}

@override final  String? title;
@override final  String? subtitle;
@override final  String? rule;

/// Create a copy of HomeSection
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomeSectionCopyWith<_HomeSection> get copyWith => __$HomeSectionCopyWithImpl<_HomeSection>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomeSection&&(identical(other.key, key) || other.key == key)&&(identical(other.source, source) || other.source == source)&&const DeepCollectionEquality().equals(other.items, _items)&&(identical(other.title, title) || other.title == title)&&(identical(other.subtitle, subtitle) || other.subtitle == subtitle)&&(identical(other.rule, rule) || other.rule == rule));
}


@override
int get hashCode {
    return Object.hash(runtimeType,key,source,const DeepCollectionEquality().hash(_items),title,subtitle,rule);
}

@override
String toString() {
    return 'HomeSection(key: $key, source: $source, items: $items, title: $title, subtitle: $subtitle, rule: $rule)';
}


}

/// @nodoc
abstract mixin class _$HomeSectionCopyWith<$Res> implements $HomeSectionCopyWith<$Res> {
  factory _$HomeSectionCopyWith(_HomeSection value, $Res Function(_HomeSection) _then) = __$HomeSectionCopyWithImpl;
@override @useResult
$Res call({
 String key, String source, List<HomeItem> items, String? title, String? subtitle, String? rule
});




}
/// @nodoc
class __$HomeSectionCopyWithImpl<$Res>
    implements _$HomeSectionCopyWith<$Res> {
  __$HomeSectionCopyWithImpl(this._self, this._then);

  final _HomeSection _self;
  final $Res Function(_HomeSection) _then;

/// Create a copy of HomeSection
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? key = null,Object? source = null,Object? items = null,Object? title = freezed,Object? subtitle = freezed,Object? rule = freezed,}) {
  return _then(_HomeSection(
key: null == key ? _self.key : key // ignore: cast_nullable_to_non_nullable
as String,source: null == source ? _self.source : source // ignore: cast_nullable_to_non_nullable
as String,items: null == items ? _self._items : items // ignore: cast_nullable_to_non_nullable
as List<HomeItem>,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,subtitle: freezed == subtitle ? _self.subtitle : subtitle // ignore: cast_nullable_to_non_nullable
as String?,rule: freezed == rule ? _self.rule : rule // ignore: cast_nullable_to_non_nullable
as String?,
  ));
}


}

/// @nodoc
mixin _$HomeItem {





@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeItem);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
    return 'HomeItem()';
}


}

/// @nodoc
class $HomeItemCopyWith<$Res>  {
$HomeItemCopyWith(HomeItem _, $Res Function(HomeItem) __);
}


/// Adds pattern-matching-related methods to [HomeItem].
extension HomeItemPatterns on HomeItem {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( HomeProductItem value)?  product,TResult Function( HomePlatformItem value)?  platform,TResult Function( HomeCategoryItem value)?  category,TResult Function( HomeBannerItem value)?  banner,TResult Function( HomeFaqItem value)?  faq,TResult Function( HomeReviewItem value)?  review,TResult Function( HomeUnknownItem value)?  unknown,required TResult orElse(),}){
final _that = this;
switch (_that) {
case HomeProductItem() when product != null:
return product(_that);case HomePlatformItem() when platform != null:
return platform(_that);case HomeCategoryItem() when category != null:
return category(_that);case HomeBannerItem() when banner != null:
return banner(_that);case HomeFaqItem() when faq != null:
return faq(_that);case HomeReviewItem() when review != null:
return review(_that);case HomeUnknownItem() when unknown != null:
return unknown(_that);case _:
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

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( HomeProductItem value)  product,required TResult Function( HomePlatformItem value)  platform,required TResult Function( HomeCategoryItem value)  category,required TResult Function( HomeBannerItem value)  banner,required TResult Function( HomeFaqItem value)  faq,required TResult Function( HomeReviewItem value)  review,required TResult Function( HomeUnknownItem value)  unknown,}){
final _that = this;
switch (_that) {
case HomeProductItem():
return product(_that);case HomePlatformItem():
return platform(_that);case HomeCategoryItem():
return category(_that);case HomeBannerItem():
return banner(_that);case HomeFaqItem():
return faq(_that);case HomeReviewItem():
return review(_that);case HomeUnknownItem():
return unknown(_that);}
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( HomeProductItem value)?  product,TResult? Function( HomePlatformItem value)?  platform,TResult? Function( HomeCategoryItem value)?  category,TResult? Function( HomeBannerItem value)?  banner,TResult? Function( HomeFaqItem value)?  faq,TResult? Function( HomeReviewItem value)?  review,TResult? Function( HomeUnknownItem value)?  unknown,}){
final _that = this;
switch (_that) {
case HomeProductItem() when product != null:
return product(_that);case HomePlatformItem() when platform != null:
return platform(_that);case HomeCategoryItem() when category != null:
return category(_that);case HomeBannerItem() when banner != null:
return banner(_that);case HomeFaqItem() when faq != null:
return faq(_that);case HomeReviewItem() when review != null:
return review(_that);case HomeUnknownItem() when unknown != null:
return unknown(_that);case _:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( ProductCard product)?  product,TResult Function( HomeTaxon platform)?  platform,TResult Function( HomeTaxon category)?  category,TResult Function( HomeBanner banner)?  banner,TResult Function( HomeFaq faq)?  faq,TResult Function( HomeReview review)?  review,TResult Function( String type)?  unknown,required TResult orElse(),}) {final _that = this;
switch (_that) {
case HomeProductItem() when product != null:
return product(_that.product);case HomePlatformItem() when platform != null:
return platform(_that.platform);case HomeCategoryItem() when category != null:
return category(_that.category);case HomeBannerItem() when banner != null:
return banner(_that.banner);case HomeFaqItem() when faq != null:
return faq(_that.faq);case HomeReviewItem() when review != null:
return review(_that.review);case HomeUnknownItem() when unknown != null:
return unknown(_that.type);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( ProductCard product)  product,required TResult Function( HomeTaxon platform)  platform,required TResult Function( HomeTaxon category)  category,required TResult Function( HomeBanner banner)  banner,required TResult Function( HomeFaq faq)  faq,required TResult Function( HomeReview review)  review,required TResult Function( String type)  unknown,}) {final _that = this;
switch (_that) {
case HomeProductItem():
return product(_that.product);case HomePlatformItem():
return platform(_that.platform);case HomeCategoryItem():
return category(_that.category);case HomeBannerItem():
return banner(_that.banner);case HomeFaqItem():
return faq(_that.faq);case HomeReviewItem():
return review(_that.review);case HomeUnknownItem():
return unknown(_that.type);}
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( ProductCard product)?  product,TResult? Function( HomeTaxon platform)?  platform,TResult? Function( HomeTaxon category)?  category,TResult? Function( HomeBanner banner)?  banner,TResult? Function( HomeFaq faq)?  faq,TResult? Function( HomeReview review)?  review,TResult? Function( String type)?  unknown,}) {final _that = this;
switch (_that) {
case HomeProductItem() when product != null:
return product(_that.product);case HomePlatformItem() when platform != null:
return platform(_that.platform);case HomeCategoryItem() when category != null:
return category(_that.category);case HomeBannerItem() when banner != null:
return banner(_that.banner);case HomeFaqItem() when faq != null:
return faq(_that.faq);case HomeReviewItem() when review != null:
return review(_that.review);case HomeUnknownItem() when unknown != null:
return unknown(_that.type);case _:
  return null;

}
}

}

/// @nodoc


class HomeProductItem implements HomeItem {
  const HomeProductItem(this.product);
  

 final  ProductCard product;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeProductItemCopyWith<HomeProductItem> get copyWith => _$HomeProductItemCopyWithImpl<HomeProductItem>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeProductItem&&(identical(other.product, product) || other.product == product));
}


@override
int get hashCode {
    return Object.hash(runtimeType,product);
}

@override
String toString() {
    return 'HomeItem.product(product: $product)';
}


}

/// @nodoc
abstract mixin class $HomeProductItemCopyWith<$Res> implements $HomeItemCopyWith<$Res> {
  factory $HomeProductItemCopyWith(HomeProductItem value, $Res Function(HomeProductItem) _then) = _$HomeProductItemCopyWithImpl;
@useResult
$Res call({
 ProductCard product
});


$ProductCardCopyWith<$Res> get product;

}
/// @nodoc
class _$HomeProductItemCopyWithImpl<$Res>
    implements $HomeProductItemCopyWith<$Res> {
  _$HomeProductItemCopyWithImpl(this._self, this._then);

  final HomeProductItem _self;
  final $Res Function(HomeProductItem) _then;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? product = null,}) {
  return _then(HomeProductItem(
null == product ? _self.product : product // ignore: cast_nullable_to_non_nullable
as ProductCard,
  ));
}

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$ProductCardCopyWith<$Res> get product {
  
  return $ProductCardCopyWith<$Res>(_self.product, (value) {
    return _then(_self.copyWith(product: value));
  });
}
}

/// @nodoc


class HomePlatformItem implements HomeItem {
  const HomePlatformItem(this.platform);
  

 final  HomeTaxon platform;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomePlatformItemCopyWith<HomePlatformItem> get copyWith => _$HomePlatformItemCopyWithImpl<HomePlatformItem>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is HomePlatformItem&&(identical(other.platform, platform) || other.platform == platform));
}


@override
int get hashCode {
    return Object.hash(runtimeType,platform);
}

@override
String toString() {
    return 'HomeItem.platform(platform: $platform)';
}


}

/// @nodoc
abstract mixin class $HomePlatformItemCopyWith<$Res> implements $HomeItemCopyWith<$Res> {
  factory $HomePlatformItemCopyWith(HomePlatformItem value, $Res Function(HomePlatformItem) _then) = _$HomePlatformItemCopyWithImpl;
@useResult
$Res call({
 HomeTaxon platform
});


$HomeTaxonCopyWith<$Res> get platform;

}
/// @nodoc
class _$HomePlatformItemCopyWithImpl<$Res>
    implements $HomePlatformItemCopyWith<$Res> {
  _$HomePlatformItemCopyWithImpl(this._self, this._then);

  final HomePlatformItem _self;
  final $Res Function(HomePlatformItem) _then;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? platform = null,}) {
  return _then(HomePlatformItem(
null == platform ? _self.platform : platform // ignore: cast_nullable_to_non_nullable
as HomeTaxon,
  ));
}

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$HomeTaxonCopyWith<$Res> get platform {
  
  return $HomeTaxonCopyWith<$Res>(_self.platform, (value) {
    return _then(_self.copyWith(platform: value));
  });
}
}

/// @nodoc


class HomeCategoryItem implements HomeItem {
  const HomeCategoryItem(this.category);
  

 final  HomeTaxon category;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeCategoryItemCopyWith<HomeCategoryItem> get copyWith => _$HomeCategoryItemCopyWithImpl<HomeCategoryItem>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeCategoryItem&&(identical(other.category, category) || other.category == category));
}


@override
int get hashCode {
    return Object.hash(runtimeType,category);
}

@override
String toString() {
    return 'HomeItem.category(category: $category)';
}


}

/// @nodoc
abstract mixin class $HomeCategoryItemCopyWith<$Res> implements $HomeItemCopyWith<$Res> {
  factory $HomeCategoryItemCopyWith(HomeCategoryItem value, $Res Function(HomeCategoryItem) _then) = _$HomeCategoryItemCopyWithImpl;
@useResult
$Res call({
 HomeTaxon category
});


$HomeTaxonCopyWith<$Res> get category;

}
/// @nodoc
class _$HomeCategoryItemCopyWithImpl<$Res>
    implements $HomeCategoryItemCopyWith<$Res> {
  _$HomeCategoryItemCopyWithImpl(this._self, this._then);

  final HomeCategoryItem _self;
  final $Res Function(HomeCategoryItem) _then;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? category = null,}) {
  return _then(HomeCategoryItem(
null == category ? _self.category : category // ignore: cast_nullable_to_non_nullable
as HomeTaxon,
  ));
}

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$HomeTaxonCopyWith<$Res> get category {
  
  return $HomeTaxonCopyWith<$Res>(_self.category, (value) {
    return _then(_self.copyWith(category: value));
  });
}
}

/// @nodoc


class HomeBannerItem implements HomeItem {
  const HomeBannerItem(this.banner);
  

 final  HomeBanner banner;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeBannerItemCopyWith<HomeBannerItem> get copyWith => _$HomeBannerItemCopyWithImpl<HomeBannerItem>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeBannerItem&&(identical(other.banner, banner) || other.banner == banner));
}


@override
int get hashCode {
    return Object.hash(runtimeType,banner);
}

@override
String toString() {
    return 'HomeItem.banner(banner: $banner)';
}


}

/// @nodoc
abstract mixin class $HomeBannerItemCopyWith<$Res> implements $HomeItemCopyWith<$Res> {
  factory $HomeBannerItemCopyWith(HomeBannerItem value, $Res Function(HomeBannerItem) _then) = _$HomeBannerItemCopyWithImpl;
@useResult
$Res call({
 HomeBanner banner
});


$HomeBannerCopyWith<$Res> get banner;

}
/// @nodoc
class _$HomeBannerItemCopyWithImpl<$Res>
    implements $HomeBannerItemCopyWith<$Res> {
  _$HomeBannerItemCopyWithImpl(this._self, this._then);

  final HomeBannerItem _self;
  final $Res Function(HomeBannerItem) _then;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? banner = null,}) {
  return _then(HomeBannerItem(
null == banner ? _self.banner : banner // ignore: cast_nullable_to_non_nullable
as HomeBanner,
  ));
}

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$HomeBannerCopyWith<$Res> get banner {
  
  return $HomeBannerCopyWith<$Res>(_self.banner, (value) {
    return _then(_self.copyWith(banner: value));
  });
}
}

/// @nodoc


class HomeFaqItem implements HomeItem {
  const HomeFaqItem(this.faq);
  

 final  HomeFaq faq;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeFaqItemCopyWith<HomeFaqItem> get copyWith => _$HomeFaqItemCopyWithImpl<HomeFaqItem>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeFaqItem&&(identical(other.faq, faq) || other.faq == faq));
}


@override
int get hashCode {
    return Object.hash(runtimeType,faq);
}

@override
String toString() {
    return 'HomeItem.faq(faq: $faq)';
}


}

/// @nodoc
abstract mixin class $HomeFaqItemCopyWith<$Res> implements $HomeItemCopyWith<$Res> {
  factory $HomeFaqItemCopyWith(HomeFaqItem value, $Res Function(HomeFaqItem) _then) = _$HomeFaqItemCopyWithImpl;
@useResult
$Res call({
 HomeFaq faq
});


$HomeFaqCopyWith<$Res> get faq;

}
/// @nodoc
class _$HomeFaqItemCopyWithImpl<$Res>
    implements $HomeFaqItemCopyWith<$Res> {
  _$HomeFaqItemCopyWithImpl(this._self, this._then);

  final HomeFaqItem _self;
  final $Res Function(HomeFaqItem) _then;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? faq = null,}) {
  return _then(HomeFaqItem(
null == faq ? _self.faq : faq // ignore: cast_nullable_to_non_nullable
as HomeFaq,
  ));
}

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$HomeFaqCopyWith<$Res> get faq {
  
  return $HomeFaqCopyWith<$Res>(_self.faq, (value) {
    return _then(_self.copyWith(faq: value));
  });
}
}

/// @nodoc


class HomeReviewItem implements HomeItem {
  const HomeReviewItem(this.review);
  

 final  HomeReview review;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeReviewItemCopyWith<HomeReviewItem> get copyWith => _$HomeReviewItemCopyWithImpl<HomeReviewItem>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeReviewItem&&(identical(other.review, review) || other.review == review));
}


@override
int get hashCode {
    return Object.hash(runtimeType,review);
}

@override
String toString() {
    return 'HomeItem.review(review: $review)';
}


}

/// @nodoc
abstract mixin class $HomeReviewItemCopyWith<$Res> implements $HomeItemCopyWith<$Res> {
  factory $HomeReviewItemCopyWith(HomeReviewItem value, $Res Function(HomeReviewItem) _then) = _$HomeReviewItemCopyWithImpl;
@useResult
$Res call({
 HomeReview review
});


$HomeReviewCopyWith<$Res> get review;

}
/// @nodoc
class _$HomeReviewItemCopyWithImpl<$Res>
    implements $HomeReviewItemCopyWith<$Res> {
  _$HomeReviewItemCopyWithImpl(this._self, this._then);

  final HomeReviewItem _self;
  final $Res Function(HomeReviewItem) _then;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? review = null,}) {
  return _then(HomeReviewItem(
null == review ? _self.review : review // ignore: cast_nullable_to_non_nullable
as HomeReview,
  ));
}

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$HomeReviewCopyWith<$Res> get review {
  
  return $HomeReviewCopyWith<$Res>(_self.review, (value) {
    return _then(_self.copyWith(review: value));
  });
}
}

/// @nodoc


class HomeUnknownItem implements HomeItem {
  const HomeUnknownItem(this.type);
  

 final  String type;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeUnknownItemCopyWith<HomeUnknownItem> get copyWith => _$HomeUnknownItemCopyWithImpl<HomeUnknownItem>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeUnknownItem&&(identical(other.type, type) || other.type == type));
}


@override
int get hashCode {
    return Object.hash(runtimeType,type);
}

@override
String toString() {
    return 'HomeItem.unknown(type: $type)';
}


}

/// @nodoc
abstract mixin class $HomeUnknownItemCopyWith<$Res> implements $HomeItemCopyWith<$Res> {
  factory $HomeUnknownItemCopyWith(HomeUnknownItem value, $Res Function(HomeUnknownItem) _then) = _$HomeUnknownItemCopyWithImpl;
@useResult
$Res call({
 String type
});




}
/// @nodoc
class _$HomeUnknownItemCopyWithImpl<$Res>
    implements $HomeUnknownItemCopyWith<$Res> {
  _$HomeUnknownItemCopyWithImpl(this._self, this._then);

  final HomeUnknownItem _self;
  final $Res Function(HomeUnknownItem) _then;

/// Create a copy of HomeItem
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? type = null,}) {
  return _then(HomeUnknownItem(
null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc
mixin _$HomeTaxon {

 String get name; String get slug; LinkTarget get link; MediaImage? get logo; MediaImage? get banner;
/// Create a copy of HomeTaxon
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeTaxonCopyWith<HomeTaxon> get copyWith => _$HomeTaxonCopyWithImpl<HomeTaxon>(this as HomeTaxon, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as HomeTaxon;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeTaxon&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.slug, _this.slug) || other.slug == _this.slug)&&(identical(other.link, _this.link) || other.link == _this.link)&&(identical(other.logo, _this.logo) || other.logo == _this.logo)&&(identical(other.banner, _this.banner) || other.banner == _this.banner));
}


@override
int get hashCode {
  final _this = this as HomeTaxon;
  return Object.hash(runtimeType,_this.name,_this.slug,_this.link,_this.logo,_this.banner);
}

@override
String toString() {
  final _this = this as HomeTaxon;
  return 'HomeTaxon(name: ${_this.name}, slug: ${_this.slug}, link: ${_this.link}, logo: ${_this.logo}, banner: ${_this.banner})';
}


}

/// @nodoc
abstract mixin class $HomeTaxonCopyWith<$Res>  {
  factory $HomeTaxonCopyWith(HomeTaxon value, $Res Function(HomeTaxon) _then) = _$HomeTaxonCopyWithImpl;
@useResult
$Res call({
 String name, String slug, LinkTarget link, MediaImage? logo, MediaImage? banner
});


$LinkTargetCopyWith<$Res> get link;$MediaImageCopyWith<$Res>? get logo;$MediaImageCopyWith<$Res>? get banner;

}
/// @nodoc
class _$HomeTaxonCopyWithImpl<$Res>
    implements $HomeTaxonCopyWith<$Res> {
  _$HomeTaxonCopyWithImpl(this._self, this._then);

  final HomeTaxon _self;
  final $Res Function(HomeTaxon) _then;

/// Create a copy of HomeTaxon
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? name = null,Object? slug = null,Object? link = null,Object? logo = freezed,Object? banner = freezed,}) {
  return _then(HomeTaxon(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,link: null == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTarget,logo: freezed == logo ? _self.logo : logo // ignore: cast_nullable_to_non_nullable
as MediaImage?,banner: freezed == banner ? _self.banner : banner // ignore: cast_nullable_to_non_nullable
as MediaImage?,
  ));
}
/// Create a copy of HomeTaxon
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetCopyWith<$Res> get link {
  
  return $LinkTargetCopyWith<$Res>(_self.link, (value) {
    return _then(_self.copyWith(link: value));
  });
}/// Create a copy of HomeTaxon
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
}/// Create a copy of HomeTaxon
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


/// Adds pattern-matching-related methods to [HomeTaxon].
extension HomeTaxonPatterns on HomeTaxon {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomeTaxon value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomeTaxon() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomeTaxon value)  $default,){
final _that = this;
switch (_that) {
case _HomeTaxon():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomeTaxon value)?  $default,){
final _that = this;
switch (_that) {
case _HomeTaxon() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String name,  String slug,  LinkTarget link,  MediaImage? logo,  MediaImage? banner)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomeTaxon() when $default != null:
return $default(_that.name,_that.slug,_that.link,_that.logo,_that.banner);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String name,  String slug,  LinkTarget link,  MediaImage? logo,  MediaImage? banner)  $default,) {final _that = this;
switch (_that) {
case _HomeTaxon():
return $default(_that.name,_that.slug,_that.link,_that.logo,_that.banner);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String name,  String slug,  LinkTarget link,  MediaImage? logo,  MediaImage? banner)?  $default,) {final _that = this;
switch (_that) {
case _HomeTaxon() when $default != null:
return $default(_that.name,_that.slug,_that.link,_that.logo,_that.banner);case _:
  return null;

}
}

}

/// @nodoc


class _HomeTaxon implements HomeTaxon {
  const _HomeTaxon({required this.name, required this.slug, required this.link, this.logo, this.banner});
  

@override final  String name;
@override final  String slug;
@override final  LinkTarget link;
@override final  MediaImage? logo;
@override final  MediaImage? banner;

/// Create a copy of HomeTaxon
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomeTaxonCopyWith<_HomeTaxon> get copyWith => __$HomeTaxonCopyWithImpl<_HomeTaxon>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomeTaxon&&(identical(other.name, name) || other.name == name)&&(identical(other.slug, slug) || other.slug == slug)&&(identical(other.link, link) || other.link == link)&&(identical(other.logo, logo) || other.logo == logo)&&(identical(other.banner, banner) || other.banner == banner));
}


@override
int get hashCode {
    return Object.hash(runtimeType,name,slug,link,logo,banner);
}

@override
String toString() {
    return 'HomeTaxon(name: $name, slug: $slug, link: $link, logo: $logo, banner: $banner)';
}


}

/// @nodoc
abstract mixin class _$HomeTaxonCopyWith<$Res> implements $HomeTaxonCopyWith<$Res> {
  factory _$HomeTaxonCopyWith(_HomeTaxon value, $Res Function(_HomeTaxon) _then) = __$HomeTaxonCopyWithImpl;
@override @useResult
$Res call({
 String name, String slug, LinkTarget link, MediaImage? logo, MediaImage? banner
});


@override $LinkTargetCopyWith<$Res> get link;@override $MediaImageCopyWith<$Res>? get logo;@override $MediaImageCopyWith<$Res>? get banner;

}
/// @nodoc
class __$HomeTaxonCopyWithImpl<$Res>
    implements _$HomeTaxonCopyWith<$Res> {
  __$HomeTaxonCopyWithImpl(this._self, this._then);

  final _HomeTaxon _self;
  final $Res Function(_HomeTaxon) _then;

/// Create a copy of HomeTaxon
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? name = null,Object? slug = null,Object? link = null,Object? logo = freezed,Object? banner = freezed,}) {
  return _then(_HomeTaxon(
name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,slug: null == slug ? _self.slug : slug // ignore: cast_nullable_to_non_nullable
as String,link: null == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTarget,logo: freezed == logo ? _self.logo : logo // ignore: cast_nullable_to_non_nullable
as MediaImage?,banner: freezed == banner ? _self.banner : banner // ignore: cast_nullable_to_non_nullable
as MediaImage?,
  ));
}

/// Create a copy of HomeTaxon
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetCopyWith<$Res> get link {
  
  return $LinkTargetCopyWith<$Res>(_self.link, (value) {
    return _then(_self.copyWith(link: value));
  });
}/// Create a copy of HomeTaxon
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
}/// Create a copy of HomeTaxon
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
mixin _$HomeBanner {

 int get id; String get placement; MediaImage? get desktopImage; MediaImage? get mobileImage; String? get title; String? get subtitle; String? get ctaLabel; LinkTarget? get link; DateTime? get startsAt; DateTime? get endsAt;
/// Create a copy of HomeBanner
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeBannerCopyWith<HomeBanner> get copyWith => _$HomeBannerCopyWithImpl<HomeBanner>(this as HomeBanner, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as HomeBanner;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeBanner&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.placement, _this.placement) || other.placement == _this.placement)&&(identical(other.desktopImage, _this.desktopImage) || other.desktopImage == _this.desktopImage)&&(identical(other.mobileImage, _this.mobileImage) || other.mobileImage == _this.mobileImage)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.subtitle, _this.subtitle) || other.subtitle == _this.subtitle)&&(identical(other.ctaLabel, _this.ctaLabel) || other.ctaLabel == _this.ctaLabel)&&(identical(other.link, _this.link) || other.link == _this.link)&&(identical(other.startsAt, _this.startsAt) || other.startsAt == _this.startsAt)&&(identical(other.endsAt, _this.endsAt) || other.endsAt == _this.endsAt));
}


@override
int get hashCode {
  final _this = this as HomeBanner;
  return Object.hash(runtimeType,_this.id,_this.placement,_this.desktopImage,_this.mobileImage,_this.title,_this.subtitle,_this.ctaLabel,_this.link,_this.startsAt,_this.endsAt);
}

@override
String toString() {
  final _this = this as HomeBanner;
  return 'HomeBanner(id: ${_this.id}, placement: ${_this.placement}, desktopImage: ${_this.desktopImage}, mobileImage: ${_this.mobileImage}, title: ${_this.title}, subtitle: ${_this.subtitle}, ctaLabel: ${_this.ctaLabel}, link: ${_this.link}, startsAt: ${_this.startsAt}, endsAt: ${_this.endsAt})';
}


}

/// @nodoc
abstract mixin class $HomeBannerCopyWith<$Res>  {
  factory $HomeBannerCopyWith(HomeBanner value, $Res Function(HomeBanner) _then) = _$HomeBannerCopyWithImpl;
@useResult
$Res call({
 int id, String placement, MediaImage? desktopImage, MediaImage? mobileImage, String? title, String? subtitle, String? ctaLabel, LinkTarget? link, DateTime? startsAt, DateTime? endsAt
});


$MediaImageCopyWith<$Res>? get desktopImage;$MediaImageCopyWith<$Res>? get mobileImage;$LinkTargetCopyWith<$Res>? get link;

}
/// @nodoc
class _$HomeBannerCopyWithImpl<$Res>
    implements $HomeBannerCopyWith<$Res> {
  _$HomeBannerCopyWithImpl(this._self, this._then);

  final HomeBanner _self;
  final $Res Function(HomeBanner) _then;

/// Create a copy of HomeBanner
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? placement = null,Object? desktopImage = freezed,Object? mobileImage = freezed,Object? title = freezed,Object? subtitle = freezed,Object? ctaLabel = freezed,Object? link = freezed,Object? startsAt = freezed,Object? endsAt = freezed,}) {
  return _then(HomeBanner(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,placement: null == placement ? _self.placement : placement // ignore: cast_nullable_to_non_nullable
as String,desktopImage: freezed == desktopImage ? _self.desktopImage : desktopImage // ignore: cast_nullable_to_non_nullable
as MediaImage?,mobileImage: freezed == mobileImage ? _self.mobileImage : mobileImage // ignore: cast_nullable_to_non_nullable
as MediaImage?,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,subtitle: freezed == subtitle ? _self.subtitle : subtitle // ignore: cast_nullable_to_non_nullable
as String?,ctaLabel: freezed == ctaLabel ? _self.ctaLabel : ctaLabel // ignore: cast_nullable_to_non_nullable
as String?,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTarget?,startsAt: freezed == startsAt ? _self.startsAt : startsAt // ignore: cast_nullable_to_non_nullable
as DateTime?,endsAt: freezed == endsAt ? _self.endsAt : endsAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}
/// Create a copy of HomeBanner
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageCopyWith<$Res>? get desktopImage {
    if (_self.desktopImage == null) {
    return null;
  }

  return $MediaImageCopyWith<$Res>(_self.desktopImage!, (value) {
    return _then(_self.copyWith(desktopImage: value));
  });
}/// Create a copy of HomeBanner
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageCopyWith<$Res>? get mobileImage {
    if (_self.mobileImage == null) {
    return null;
  }

  return $MediaImageCopyWith<$Res>(_self.mobileImage!, (value) {
    return _then(_self.copyWith(mobileImage: value));
  });
}/// Create a copy of HomeBanner
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


/// Adds pattern-matching-related methods to [HomeBanner].
extension HomeBannerPatterns on HomeBanner {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomeBanner value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomeBanner() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomeBanner value)  $default,){
final _that = this;
switch (_that) {
case _HomeBanner():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomeBanner value)?  $default,){
final _that = this;
switch (_that) {
case _HomeBanner() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  String placement,  MediaImage? desktopImage,  MediaImage? mobileImage,  String? title,  String? subtitle,  String? ctaLabel,  LinkTarget? link,  DateTime? startsAt,  DateTime? endsAt)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomeBanner() when $default != null:
return $default(_that.id,_that.placement,_that.desktopImage,_that.mobileImage,_that.title,_that.subtitle,_that.ctaLabel,_that.link,_that.startsAt,_that.endsAt);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  String placement,  MediaImage? desktopImage,  MediaImage? mobileImage,  String? title,  String? subtitle,  String? ctaLabel,  LinkTarget? link,  DateTime? startsAt,  DateTime? endsAt)  $default,) {final _that = this;
switch (_that) {
case _HomeBanner():
return $default(_that.id,_that.placement,_that.desktopImage,_that.mobileImage,_that.title,_that.subtitle,_that.ctaLabel,_that.link,_that.startsAt,_that.endsAt);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  String placement,  MediaImage? desktopImage,  MediaImage? mobileImage,  String? title,  String? subtitle,  String? ctaLabel,  LinkTarget? link,  DateTime? startsAt,  DateTime? endsAt)?  $default,) {final _that = this;
switch (_that) {
case _HomeBanner() when $default != null:
return $default(_that.id,_that.placement,_that.desktopImage,_that.mobileImage,_that.title,_that.subtitle,_that.ctaLabel,_that.link,_that.startsAt,_that.endsAt);case _:
  return null;

}
}

}

/// @nodoc


class _HomeBanner implements HomeBanner {
  const _HomeBanner({required this.id, required this.placement, this.desktopImage, this.mobileImage, this.title, this.subtitle, this.ctaLabel, this.link, this.startsAt, this.endsAt});
  

@override final  int id;
@override final  String placement;
@override final  MediaImage? desktopImage;
@override final  MediaImage? mobileImage;
@override final  String? title;
@override final  String? subtitle;
@override final  String? ctaLabel;
@override final  LinkTarget? link;
@override final  DateTime? startsAt;
@override final  DateTime? endsAt;

/// Create a copy of HomeBanner
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomeBannerCopyWith<_HomeBanner> get copyWith => __$HomeBannerCopyWithImpl<_HomeBanner>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomeBanner&&(identical(other.id, id) || other.id == id)&&(identical(other.placement, placement) || other.placement == placement)&&(identical(other.desktopImage, desktopImage) || other.desktopImage == desktopImage)&&(identical(other.mobileImage, mobileImage) || other.mobileImage == mobileImage)&&(identical(other.title, title) || other.title == title)&&(identical(other.subtitle, subtitle) || other.subtitle == subtitle)&&(identical(other.ctaLabel, ctaLabel) || other.ctaLabel == ctaLabel)&&(identical(other.link, link) || other.link == link)&&(identical(other.startsAt, startsAt) || other.startsAt == startsAt)&&(identical(other.endsAt, endsAt) || other.endsAt == endsAt));
}


@override
int get hashCode {
    return Object.hash(runtimeType,id,placement,desktopImage,mobileImage,title,subtitle,ctaLabel,link,startsAt,endsAt);
}

@override
String toString() {
    return 'HomeBanner(id: $id, placement: $placement, desktopImage: $desktopImage, mobileImage: $mobileImage, title: $title, subtitle: $subtitle, ctaLabel: $ctaLabel, link: $link, startsAt: $startsAt, endsAt: $endsAt)';
}


}

/// @nodoc
abstract mixin class _$HomeBannerCopyWith<$Res> implements $HomeBannerCopyWith<$Res> {
  factory _$HomeBannerCopyWith(_HomeBanner value, $Res Function(_HomeBanner) _then) = __$HomeBannerCopyWithImpl;
@override @useResult
$Res call({
 int id, String placement, MediaImage? desktopImage, MediaImage? mobileImage, String? title, String? subtitle, String? ctaLabel, LinkTarget? link, DateTime? startsAt, DateTime? endsAt
});


@override $MediaImageCopyWith<$Res>? get desktopImage;@override $MediaImageCopyWith<$Res>? get mobileImage;@override $LinkTargetCopyWith<$Res>? get link;

}
/// @nodoc
class __$HomeBannerCopyWithImpl<$Res>
    implements _$HomeBannerCopyWith<$Res> {
  __$HomeBannerCopyWithImpl(this._self, this._then);

  final _HomeBanner _self;
  final $Res Function(_HomeBanner) _then;

/// Create a copy of HomeBanner
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? placement = null,Object? desktopImage = freezed,Object? mobileImage = freezed,Object? title = freezed,Object? subtitle = freezed,Object? ctaLabel = freezed,Object? link = freezed,Object? startsAt = freezed,Object? endsAt = freezed,}) {
  return _then(_HomeBanner(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,placement: null == placement ? _self.placement : placement // ignore: cast_nullable_to_non_nullable
as String,desktopImage: freezed == desktopImage ? _self.desktopImage : desktopImage // ignore: cast_nullable_to_non_nullable
as MediaImage?,mobileImage: freezed == mobileImage ? _self.mobileImage : mobileImage // ignore: cast_nullable_to_non_nullable
as MediaImage?,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,subtitle: freezed == subtitle ? _self.subtitle : subtitle // ignore: cast_nullable_to_non_nullable
as String?,ctaLabel: freezed == ctaLabel ? _self.ctaLabel : ctaLabel // ignore: cast_nullable_to_non_nullable
as String?,link: freezed == link ? _self.link : link // ignore: cast_nullable_to_non_nullable
as LinkTarget?,startsAt: freezed == startsAt ? _self.startsAt : startsAt // ignore: cast_nullable_to_non_nullable
as DateTime?,endsAt: freezed == endsAt ? _self.endsAt : endsAt // ignore: cast_nullable_to_non_nullable
as DateTime?,
  ));
}

/// Create a copy of HomeBanner
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageCopyWith<$Res>? get desktopImage {
    if (_self.desktopImage == null) {
    return null;
  }

  return $MediaImageCopyWith<$Res>(_self.desktopImage!, (value) {
    return _then(_self.copyWith(desktopImage: value));
  });
}/// Create a copy of HomeBanner
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$MediaImageCopyWith<$Res>? get mobileImage {
    if (_self.mobileImage == null) {
    return null;
  }

  return $MediaImageCopyWith<$Res>(_self.mobileImage!, (value) {
    return _then(_self.copyWith(mobileImage: value));
  });
}/// Create a copy of HomeBanner
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
mixin _$HomeFaq {

 int get id; String get question; String get answer;
/// Create a copy of HomeFaq
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeFaqCopyWith<HomeFaq> get copyWith => _$HomeFaqCopyWithImpl<HomeFaq>(this as HomeFaq, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as HomeFaq;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeFaq&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.question, _this.question) || other.question == _this.question)&&(identical(other.answer, _this.answer) || other.answer == _this.answer));
}


@override
int get hashCode {
  final _this = this as HomeFaq;
  return Object.hash(runtimeType,_this.id,_this.question,_this.answer);
}

@override
String toString() {
  final _this = this as HomeFaq;
  return 'HomeFaq(id: ${_this.id}, question: ${_this.question}, answer: ${_this.answer})';
}


}

/// @nodoc
abstract mixin class $HomeFaqCopyWith<$Res>  {
  factory $HomeFaqCopyWith(HomeFaq value, $Res Function(HomeFaq) _then) = _$HomeFaqCopyWithImpl;
@useResult
$Res call({
 int id, String question, String answer
});




}
/// @nodoc
class _$HomeFaqCopyWithImpl<$Res>
    implements $HomeFaqCopyWith<$Res> {
  _$HomeFaqCopyWithImpl(this._self, this._then);

  final HomeFaq _self;
  final $Res Function(HomeFaq) _then;

/// Create a copy of HomeFaq
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? question = null,Object? answer = null,}) {
  return _then(HomeFaq(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,question: null == question ? _self.question : question // ignore: cast_nullable_to_non_nullable
as String,answer: null == answer ? _self.answer : answer // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [HomeFaq].
extension HomeFaqPatterns on HomeFaq {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomeFaq value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomeFaq() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomeFaq value)  $default,){
final _that = this;
switch (_that) {
case _HomeFaq():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomeFaq value)?  $default,){
final _that = this;
switch (_that) {
case _HomeFaq() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  String question,  String answer)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomeFaq() when $default != null:
return $default(_that.id,_that.question,_that.answer);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  String question,  String answer)  $default,) {final _that = this;
switch (_that) {
case _HomeFaq():
return $default(_that.id,_that.question,_that.answer);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  String question,  String answer)?  $default,) {final _that = this;
switch (_that) {
case _HomeFaq() when $default != null:
return $default(_that.id,_that.question,_that.answer);case _:
  return null;

}
}

}

/// @nodoc


class _HomeFaq implements HomeFaq {
  const _HomeFaq({required this.id, required this.question, required this.answer});
  

@override final  int id;
@override final  String question;
@override final  String answer;

/// Create a copy of HomeFaq
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomeFaqCopyWith<_HomeFaq> get copyWith => __$HomeFaqCopyWithImpl<_HomeFaq>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomeFaq&&(identical(other.id, id) || other.id == id)&&(identical(other.question, question) || other.question == question)&&(identical(other.answer, answer) || other.answer == answer));
}


@override
int get hashCode {
    return Object.hash(runtimeType,id,question,answer);
}

@override
String toString() {
    return 'HomeFaq(id: $id, question: $question, answer: $answer)';
}


}

/// @nodoc
abstract mixin class _$HomeFaqCopyWith<$Res> implements $HomeFaqCopyWith<$Res> {
  factory _$HomeFaqCopyWith(_HomeFaq value, $Res Function(_HomeFaq) _then) = __$HomeFaqCopyWithImpl;
@override @useResult
$Res call({
 int id, String question, String answer
});




}
/// @nodoc
class __$HomeFaqCopyWithImpl<$Res>
    implements _$HomeFaqCopyWith<$Res> {
  __$HomeFaqCopyWithImpl(this._self, this._then);

  final _HomeFaq _self;
  final $Res Function(_HomeFaq) _then;

/// Create a copy of HomeFaq
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? question = null,Object? answer = null,}) {
  return _then(_HomeFaq(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,question: null == question ? _self.question : question // ignore: cast_nullable_to_non_nullable
as String,answer: null == answer ? _self.answer : answer // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc
mixin _$HomeReview {

 int get id; int get rating; String get reviewer; bool get verifiedPurchase; DateTime get createdAt; String get productName; String get productSlug; String? get title; String? get body; LinkTarget? get productLink;
/// Create a copy of HomeReview
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$HomeReviewCopyWith<HomeReview> get copyWith => _$HomeReviewCopyWithImpl<HomeReview>(this as HomeReview, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as HomeReview;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is HomeReview&&(identical(other.id, _this.id) || other.id == _this.id)&&(identical(other.rating, _this.rating) || other.rating == _this.rating)&&(identical(other.reviewer, _this.reviewer) || other.reviewer == _this.reviewer)&&(identical(other.verifiedPurchase, _this.verifiedPurchase) || other.verifiedPurchase == _this.verifiedPurchase)&&(identical(other.createdAt, _this.createdAt) || other.createdAt == _this.createdAt)&&(identical(other.productName, _this.productName) || other.productName == _this.productName)&&(identical(other.productSlug, _this.productSlug) || other.productSlug == _this.productSlug)&&(identical(other.title, _this.title) || other.title == _this.title)&&(identical(other.body, _this.body) || other.body == _this.body)&&(identical(other.productLink, _this.productLink) || other.productLink == _this.productLink));
}


@override
int get hashCode {
  final _this = this as HomeReview;
  return Object.hash(runtimeType,_this.id,_this.rating,_this.reviewer,_this.verifiedPurchase,_this.createdAt,_this.productName,_this.productSlug,_this.title,_this.body,_this.productLink);
}

@override
String toString() {
  final _this = this as HomeReview;
  return 'HomeReview(id: ${_this.id}, rating: ${_this.rating}, reviewer: ${_this.reviewer}, verifiedPurchase: ${_this.verifiedPurchase}, createdAt: ${_this.createdAt}, productName: ${_this.productName}, productSlug: ${_this.productSlug}, title: ${_this.title}, body: ${_this.body}, productLink: ${_this.productLink})';
}


}

/// @nodoc
abstract mixin class $HomeReviewCopyWith<$Res>  {
  factory $HomeReviewCopyWith(HomeReview value, $Res Function(HomeReview) _then) = _$HomeReviewCopyWithImpl;
@useResult
$Res call({
 int id, int rating, String reviewer, bool verifiedPurchase, DateTime createdAt, String productName, String productSlug, String? title, String? body, LinkTarget? productLink
});


$LinkTargetCopyWith<$Res>? get productLink;

}
/// @nodoc
class _$HomeReviewCopyWithImpl<$Res>
    implements $HomeReviewCopyWith<$Res> {
  _$HomeReviewCopyWithImpl(this._self, this._then);

  final HomeReview _self;
  final $Res Function(HomeReview) _then;

/// Create a copy of HomeReview
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? id = null,Object? rating = null,Object? reviewer = null,Object? verifiedPurchase = null,Object? createdAt = null,Object? productName = null,Object? productSlug = null,Object? title = freezed,Object? body = freezed,Object? productLink = freezed,}) {
  return _then(HomeReview(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as int,reviewer: null == reviewer ? _self.reviewer : reviewer // ignore: cast_nullable_to_non_nullable
as String,verifiedPurchase: null == verifiedPurchase ? _self.verifiedPurchase : verifiedPurchase // ignore: cast_nullable_to_non_nullable
as bool,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,productName: null == productName ? _self.productName : productName // ignore: cast_nullable_to_non_nullable
as String,productSlug: null == productSlug ? _self.productSlug : productSlug // ignore: cast_nullable_to_non_nullable
as String,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,body: freezed == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String?,productLink: freezed == productLink ? _self.productLink : productLink // ignore: cast_nullable_to_non_nullable
as LinkTarget?,
  ));
}
/// Create a copy of HomeReview
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetCopyWith<$Res>? get productLink {
    if (_self.productLink == null) {
    return null;
  }

  return $LinkTargetCopyWith<$Res>(_self.productLink!, (value) {
    return _then(_self.copyWith(productLink: value));
  });
}
}


/// Adds pattern-matching-related methods to [HomeReview].
extension HomeReviewPatterns on HomeReview {
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

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _HomeReview value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _HomeReview() when $default != null:
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

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _HomeReview value)  $default,){
final _that = this;
switch (_that) {
case _HomeReview():
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

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _HomeReview value)?  $default,){
final _that = this;
switch (_that) {
case _HomeReview() when $default != null:
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

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( int id,  int rating,  String reviewer,  bool verifiedPurchase,  DateTime createdAt,  String productName,  String productSlug,  String? title,  String? body,  LinkTarget? productLink)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _HomeReview() when $default != null:
return $default(_that.id,_that.rating,_that.reviewer,_that.verifiedPurchase,_that.createdAt,_that.productName,_that.productSlug,_that.title,_that.body,_that.productLink);case _:
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

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( int id,  int rating,  String reviewer,  bool verifiedPurchase,  DateTime createdAt,  String productName,  String productSlug,  String? title,  String? body,  LinkTarget? productLink)  $default,) {final _that = this;
switch (_that) {
case _HomeReview():
return $default(_that.id,_that.rating,_that.reviewer,_that.verifiedPurchase,_that.createdAt,_that.productName,_that.productSlug,_that.title,_that.body,_that.productLink);case _:
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

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( int id,  int rating,  String reviewer,  bool verifiedPurchase,  DateTime createdAt,  String productName,  String productSlug,  String? title,  String? body,  LinkTarget? productLink)?  $default,) {final _that = this;
switch (_that) {
case _HomeReview() when $default != null:
return $default(_that.id,_that.rating,_that.reviewer,_that.verifiedPurchase,_that.createdAt,_that.productName,_that.productSlug,_that.title,_that.body,_that.productLink);case _:
  return null;

}
}

}

/// @nodoc


class _HomeReview implements HomeReview {
  const _HomeReview({required this.id, required this.rating, required this.reviewer, required this.verifiedPurchase, required this.createdAt, required this.productName, required this.productSlug, this.title, this.body, this.productLink});
  

@override final  int id;
@override final  int rating;
@override final  String reviewer;
@override final  bool verifiedPurchase;
@override final  DateTime createdAt;
@override final  String productName;
@override final  String productSlug;
@override final  String? title;
@override final  String? body;
@override final  LinkTarget? productLink;

/// Create a copy of HomeReview
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$HomeReviewCopyWith<_HomeReview> get copyWith => __$HomeReviewCopyWithImpl<_HomeReview>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _HomeReview&&(identical(other.id, id) || other.id == id)&&(identical(other.rating, rating) || other.rating == rating)&&(identical(other.reviewer, reviewer) || other.reviewer == reviewer)&&(identical(other.verifiedPurchase, verifiedPurchase) || other.verifiedPurchase == verifiedPurchase)&&(identical(other.createdAt, createdAt) || other.createdAt == createdAt)&&(identical(other.productName, productName) || other.productName == productName)&&(identical(other.productSlug, productSlug) || other.productSlug == productSlug)&&(identical(other.title, title) || other.title == title)&&(identical(other.body, body) || other.body == body)&&(identical(other.productLink, productLink) || other.productLink == productLink));
}


@override
int get hashCode {
    return Object.hash(runtimeType,id,rating,reviewer,verifiedPurchase,createdAt,productName,productSlug,title,body,productLink);
}

@override
String toString() {
    return 'HomeReview(id: $id, rating: $rating, reviewer: $reviewer, verifiedPurchase: $verifiedPurchase, createdAt: $createdAt, productName: $productName, productSlug: $productSlug, title: $title, body: $body, productLink: $productLink)';
}


}

/// @nodoc
abstract mixin class _$HomeReviewCopyWith<$Res> implements $HomeReviewCopyWith<$Res> {
  factory _$HomeReviewCopyWith(_HomeReview value, $Res Function(_HomeReview) _then) = __$HomeReviewCopyWithImpl;
@override @useResult
$Res call({
 int id, int rating, String reviewer, bool verifiedPurchase, DateTime createdAt, String productName, String productSlug, String? title, String? body, LinkTarget? productLink
});


@override $LinkTargetCopyWith<$Res>? get productLink;

}
/// @nodoc
class __$HomeReviewCopyWithImpl<$Res>
    implements _$HomeReviewCopyWith<$Res> {
  __$HomeReviewCopyWithImpl(this._self, this._then);

  final _HomeReview _self;
  final $Res Function(_HomeReview) _then;

/// Create a copy of HomeReview
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? id = null,Object? rating = null,Object? reviewer = null,Object? verifiedPurchase = null,Object? createdAt = null,Object? productName = null,Object? productSlug = null,Object? title = freezed,Object? body = freezed,Object? productLink = freezed,}) {
  return _then(_HomeReview(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as int,rating: null == rating ? _self.rating : rating // ignore: cast_nullable_to_non_nullable
as int,reviewer: null == reviewer ? _self.reviewer : reviewer // ignore: cast_nullable_to_non_nullable
as String,verifiedPurchase: null == verifiedPurchase ? _self.verifiedPurchase : verifiedPurchase // ignore: cast_nullable_to_non_nullable
as bool,createdAt: null == createdAt ? _self.createdAt : createdAt // ignore: cast_nullable_to_non_nullable
as DateTime,productName: null == productName ? _self.productName : productName // ignore: cast_nullable_to_non_nullable
as String,productSlug: null == productSlug ? _self.productSlug : productSlug // ignore: cast_nullable_to_non_nullable
as String,title: freezed == title ? _self.title : title // ignore: cast_nullable_to_non_nullable
as String?,body: freezed == body ? _self.body : body // ignore: cast_nullable_to_non_nullable
as String?,productLink: freezed == productLink ? _self.productLink : productLink // ignore: cast_nullable_to_non_nullable
as LinkTarget?,
  ));
}

/// Create a copy of HomeReview
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$LinkTargetCopyWith<$Res>? get productLink {
    if (_self.productLink == null) {
    return null;
  }

  return $LinkTargetCopyWith<$Res>(_self.productLink!, (value) {
    return _then(_self.copyWith(productLink: value));
  });
}
}

// dart format on
