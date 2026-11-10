import 'package:application/features/home/domain/entities/home.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'home_models.freezed.dart';
part 'home_models.g.dart';

@freezed
abstract class HomeDto with _$HomeDto {
  const factory HomeDto({
    required List<HomeSectionDto> sections,
    required HomeBannersDto banners,
  }) = _HomeDto;

  const HomeDto._();

  factory HomeDto.fromJson(Map<String, dynamic> json) =>
      _$HomeDtoFromJson(json);

  HomePage toEntity({required String displayCurrency}) => HomePage(
    sections: [for (final section in sections) section.toEntity()],
    heroBanners: [for (final banner in banners.hero) banner.toEntity()],
    promoBanners: [for (final banner in banners.homePromo) banner.toEntity()],
    displayCurrency: displayCurrency,
  );
}

@freezed
abstract class HomeBannersDto with _$HomeBannersDto {
  const factory HomeBannersDto({
    @Default(<BannerDto>[]) List<BannerDto> hero,
    @Default(<BannerDto>[]) List<BannerDto> homePromo,
  }) = _HomeBannersDto;

  factory HomeBannersDto.fromJson(Map<String, dynamic> json) =>
      _$HomeBannersDtoFromJson(json);
}

@freezed
abstract class HomeSectionDto with _$HomeSectionDto {
  const factory HomeSectionDto({
    required String key,
    required String source,
    required List<HomeItemDto> items,
    String? title,
    String? subtitle,
    String? rule,
  }) = _HomeSectionDto;

  const HomeSectionDto._();

  factory HomeSectionDto.fromJson(Map<String, dynamic> json) =>
      _$HomeSectionDtoFromJson(json);

  HomeSection toEntity() => HomeSection(
    key: key,
    source: source,
    title: title,
    subtitle: subtitle,
    rule: rule,
    items: [for (final item in items) item.toEntity()],
  );
}

/// `{ type, data }`; [data] is decoded by type in [toEntity].
@freezed
abstract class HomeItemDto with _$HomeItemDto {
  const factory HomeItemDto({
    required String type,
    required Map<String, dynamic> data,
  }) = _HomeItemDto;

  const HomeItemDto._();

  factory HomeItemDto.fromJson(Map<String, dynamic> json) =>
      _$HomeItemDtoFromJson(json);

  HomeItem toEntity() => switch (type) {
    'product' => HomeItem.product(ProductCardDto.fromJson(data).toEntity()),
    'platform' => HomeItem.platform(HomeTaxonDto.fromJson(data).toEntity()),
    'category' => HomeItem.category(HomeTaxonDto.fromJson(data).toEntity()),
    'banner' => HomeItem.banner(BannerDto.fromJson(data).toEntity()),
    'faq' => HomeItem.faq(HomeFaqDto.fromJson(data).toEntity()),
    'review' => HomeItem.review(HomeReviewDto.fromJson(data).toEntity()),
    _ => HomeItem.unknown(type),
  };
}

@freezed
abstract class HomeTaxonDto with _$HomeTaxonDto {
  const factory HomeTaxonDto({
    required String name,
    required String slug,
    required LinkTargetDto link,
    MediaImageDto? logo,
    MediaImageDto? banner,
  }) = _HomeTaxonDto;

  const HomeTaxonDto._();

  factory HomeTaxonDto.fromJson(Map<String, dynamic> json) =>
      _$HomeTaxonDtoFromJson(json);

  HomeTaxon toEntity() => HomeTaxon(
    name: name,
    slug: slug,
    link: link.toEntity(),
    logo: logo?.toEntity(),
    banner: banner?.toEntity(),
  );
}

@freezed
abstract class BannerImagesDto with _$BannerImagesDto {
  const factory BannerImagesDto({
    MediaImageDto? desktop,
    MediaImageDto? mobile,
  }) = _BannerImagesDto;

  factory BannerImagesDto.fromJson(Map<String, dynamic> json) =>
      _$BannerImagesDtoFromJson(json);
}

@freezed
abstract class BannerDto with _$BannerDto {
  const factory BannerDto({
    required int id,
    required String placement,
    required BannerImagesDto image,
    String? title,
    String? subtitle,
    String? ctaLabel,
    LinkTargetDto? link,
    DateTime? startsAt,
    DateTime? endsAt,
  }) = _BannerDto;

  const BannerDto._();

  factory BannerDto.fromJson(Map<String, dynamic> json) =>
      _$BannerDtoFromJson(json);

  HomeBanner toEntity() => HomeBanner(
    id: id,
    placement: placement,
    desktopImage: image.desktop?.toEntity(),
    mobileImage: image.mobile?.toEntity(),
    title: title,
    subtitle: subtitle,
    ctaLabel: ctaLabel,
    link: link?.toEntity(),
    startsAt: startsAt,
    endsAt: endsAt,
  );
}

@freezed
abstract class HomeFaqDto with _$HomeFaqDto {
  const factory HomeFaqDto({
    required int id,
    required String question,
    required String answer,
  }) = _HomeFaqDto;

  const HomeFaqDto._();

  factory HomeFaqDto.fromJson(Map<String, dynamic> json) =>
      _$HomeFaqDtoFromJson(json);

  HomeFaq toEntity() => HomeFaq(id: id, question: question, answer: answer);
}

@freezed
abstract class HomeReviewProductDto with _$HomeReviewProductDto {
  const factory HomeReviewProductDto({
    required String name,
    required String slug,
    LinkTargetDto? link,
  }) = _HomeReviewProductDto;

  factory HomeReviewProductDto.fromJson(Map<String, dynamic> json) =>
      _$HomeReviewProductDtoFromJson(json);
}

@freezed
abstract class HomeReviewDto with _$HomeReviewDto {
  const factory HomeReviewDto({
    required int id,
    required int rating,
    required String reviewer,
    required bool verifiedPurchase,
    required DateTime createdAt,
    required HomeReviewProductDto product,
    String? title,
    String? body,
  }) = _HomeReviewDto;

  const HomeReviewDto._();

  factory HomeReviewDto.fromJson(Map<String, dynamic> json) =>
      _$HomeReviewDtoFromJson(json);

  HomeReview toEntity() => HomeReview(
    id: id,
    rating: rating,
    reviewer: reviewer,
    verifiedPurchase: verifiedPurchase,
    createdAt: createdAt,
    title: title,
    body: body,
    productName: product.name,
    productSlug: product.slug,
    productLink: product.link?.toEntity(),
  );
}
