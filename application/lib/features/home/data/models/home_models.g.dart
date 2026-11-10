// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'home_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_HomeDto _$HomeDtoFromJson(Map<String, dynamic> json) => _HomeDto(
  sections: (json['sections'] as List<dynamic>)
      .map((e) => HomeSectionDto.fromJson(e as Map<String, dynamic>))
      .toList(),
  banners: HomeBannersDto.fromJson(json['banners'] as Map<String, dynamic>),
);

Map<String, dynamic> _$HomeDtoToJson(_HomeDto instance) => <String, dynamic>{
  'sections': instance.sections.map((e) => e.toJson()).toList(),
  'banners': instance.banners.toJson(),
};

_HomeBannersDto _$HomeBannersDtoFromJson(Map<String, dynamic> json) =>
    _HomeBannersDto(
      hero:
          (json['hero'] as List<dynamic>?)
              ?.map((e) => BannerDto.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <BannerDto>[],
      homePromo:
          (json['home_promo'] as List<dynamic>?)
              ?.map((e) => BannerDto.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const <BannerDto>[],
    );

Map<String, dynamic> _$HomeBannersDtoToJson(_HomeBannersDto instance) =>
    <String, dynamic>{
      'hero': instance.hero.map((e) => e.toJson()).toList(),
      'home_promo': instance.homePromo.map((e) => e.toJson()).toList(),
    };

_HomeSectionDto _$HomeSectionDtoFromJson(Map<String, dynamic> json) =>
    _HomeSectionDto(
      key: json['key'] as String,
      source: json['source'] as String,
      items: (json['items'] as List<dynamic>)
          .map((e) => HomeItemDto.fromJson(e as Map<String, dynamic>))
          .toList(),
      title: json['title'] as String?,
      subtitle: json['subtitle'] as String?,
      rule: json['rule'] as String?,
    );

Map<String, dynamic> _$HomeSectionDtoToJson(_HomeSectionDto instance) =>
    <String, dynamic>{
      'key': instance.key,
      'source': instance.source,
      'items': instance.items.map((e) => e.toJson()).toList(),
      'title': instance.title,
      'subtitle': instance.subtitle,
      'rule': instance.rule,
    };

_HomeItemDto _$HomeItemDtoFromJson(Map<String, dynamic> json) => _HomeItemDto(
  type: json['type'] as String,
  data: json['data'] as Map<String, dynamic>,
);

Map<String, dynamic> _$HomeItemDtoToJson(_HomeItemDto instance) =>
    <String, dynamic>{'type': instance.type, 'data': instance.data};

_HomeTaxonDto _$HomeTaxonDtoFromJson(Map<String, dynamic> json) =>
    _HomeTaxonDto(
      name: json['name'] as String,
      slug: json['slug'] as String,
      link: LinkTargetDto.fromJson(json['link'] as Map<String, dynamic>),
      logo: json['logo'] == null
          ? null
          : MediaImageDto.fromJson(json['logo'] as Map<String, dynamic>),
      banner: json['banner'] == null
          ? null
          : MediaImageDto.fromJson(json['banner'] as Map<String, dynamic>),
    );

Map<String, dynamic> _$HomeTaxonDtoToJson(_HomeTaxonDto instance) =>
    <String, dynamic>{
      'name': instance.name,
      'slug': instance.slug,
      'link': instance.link.toJson(),
      'logo': instance.logo?.toJson(),
      'banner': instance.banner?.toJson(),
    };

_BannerImagesDto _$BannerImagesDtoFromJson(Map<String, dynamic> json) =>
    _BannerImagesDto(
      desktop: json['desktop'] == null
          ? null
          : MediaImageDto.fromJson(json['desktop'] as Map<String, dynamic>),
      mobile: json['mobile'] == null
          ? null
          : MediaImageDto.fromJson(json['mobile'] as Map<String, dynamic>),
    );

Map<String, dynamic> _$BannerImagesDtoToJson(_BannerImagesDto instance) =>
    <String, dynamic>{
      'desktop': instance.desktop?.toJson(),
      'mobile': instance.mobile?.toJson(),
    };

_BannerDto _$BannerDtoFromJson(Map<String, dynamic> json) => _BannerDto(
  id: (json['id'] as num).toInt(),
  placement: json['placement'] as String,
  image: BannerImagesDto.fromJson(json['image'] as Map<String, dynamic>),
  title: json['title'] as String?,
  subtitle: json['subtitle'] as String?,
  ctaLabel: json['cta_label'] as String?,
  link: json['link'] == null
      ? null
      : LinkTargetDto.fromJson(json['link'] as Map<String, dynamic>),
  startsAt: json['starts_at'] == null
      ? null
      : DateTime.parse(json['starts_at'] as String),
  endsAt: json['ends_at'] == null
      ? null
      : DateTime.parse(json['ends_at'] as String),
);

Map<String, dynamic> _$BannerDtoToJson(_BannerDto instance) =>
    <String, dynamic>{
      'id': instance.id,
      'placement': instance.placement,
      'image': instance.image.toJson(),
      'title': instance.title,
      'subtitle': instance.subtitle,
      'cta_label': instance.ctaLabel,
      'link': instance.link?.toJson(),
      'starts_at': instance.startsAt?.toIso8601String(),
      'ends_at': instance.endsAt?.toIso8601String(),
    };

_HomeFaqDto _$HomeFaqDtoFromJson(Map<String, dynamic> json) => _HomeFaqDto(
  id: (json['id'] as num).toInt(),
  question: json['question'] as String,
  answer: json['answer'] as String,
);

Map<String, dynamic> _$HomeFaqDtoToJson(_HomeFaqDto instance) =>
    <String, dynamic>{
      'id': instance.id,
      'question': instance.question,
      'answer': instance.answer,
    };

_HomeReviewProductDto _$HomeReviewProductDtoFromJson(
  Map<String, dynamic> json,
) => _HomeReviewProductDto(
  name: json['name'] as String,
  slug: json['slug'] as String,
  link: json['link'] == null
      ? null
      : LinkTargetDto.fromJson(json['link'] as Map<String, dynamic>),
);

Map<String, dynamic> _$HomeReviewProductDtoToJson(
  _HomeReviewProductDto instance,
) => <String, dynamic>{
  'name': instance.name,
  'slug': instance.slug,
  'link': instance.link?.toJson(),
};

_HomeReviewDto _$HomeReviewDtoFromJson(Map<String, dynamic> json) =>
    _HomeReviewDto(
      id: (json['id'] as num).toInt(),
      rating: (json['rating'] as num).toInt(),
      reviewer: json['reviewer'] as String,
      verifiedPurchase: json['verified_purchase'] as bool,
      createdAt: DateTime.parse(json['created_at'] as String),
      product: HomeReviewProductDto.fromJson(
        json['product'] as Map<String, dynamic>,
      ),
      title: json['title'] as String?,
      body: json['body'] as String?,
    );

Map<String, dynamic> _$HomeReviewDtoToJson(_HomeReviewDto instance) =>
    <String, dynamic>{
      'id': instance.id,
      'rating': instance.rating,
      'reviewer': instance.reviewer,
      'verified_purchase': instance.verifiedPurchase,
      'created_at': instance.createdAt.toIso8601String(),
      'product': instance.product.toJson(),
      'title': instance.title,
      'body': instance.body,
    };
