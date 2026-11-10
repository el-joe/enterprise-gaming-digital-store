import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'home.freezed.dart';

/// `GET /storefront/home` (API_DOCUMENTATION.md 2c).
@freezed
abstract class HomePage with _$HomePage {
  const factory HomePage({
    required List<HomeSection> sections,
    required List<HomeBanner> heroBanners,
    required List<HomeBanner> promoBanners,
    required String displayCurrency,
  }) = _HomePage;
}

@freezed
abstract class HomeSection with _$HomeSection {
  const factory HomeSection({
    required String key,
    required String source,
    required List<HomeItem> items,
    String? title,
    String? subtitle,
    String? rule,
  }) = _HomeSection;
}

/// One item of a section, by `type`. Unknown types are kept as [HomeItem.unknown] and skipped
/// by the UI, so a new server type never breaks the page.
@freezed
sealed class HomeItem with _$HomeItem {
  const factory HomeItem.product(ProductCard product) = HomeProductItem;
  const factory HomeItem.platform(HomeTaxon platform) = HomePlatformItem;
  const factory HomeItem.category(HomeTaxon category) = HomeCategoryItem;
  const factory HomeItem.banner(HomeBanner banner) = HomeBannerItem;
  const factory HomeItem.faq(HomeFaq faq) = HomeFaqItem;
  const factory HomeItem.review(HomeReview review) = HomeReviewItem;
  const factory HomeItem.unknown(String type) = HomeUnknownItem;
}

/// A platform (with [logo]) or a category item.
@freezed
abstract class HomeTaxon with _$HomeTaxon {
  const factory HomeTaxon({
    required String name,
    required String slug,
    required LinkTarget link,
    MediaImage? logo,
    MediaImage? banner,
  }) = _HomeTaxon;
}

@freezed
abstract class HomeBanner with _$HomeBanner {
  const factory HomeBanner({
    required int id,
    required String placement,
    MediaImage? desktopImage,
    MediaImage? mobileImage,
    String? title,
    String? subtitle,
    String? ctaLabel,
    LinkTarget? link,
    DateTime? startsAt,
    DateTime? endsAt,
  }) = _HomeBanner;
}

@freezed
abstract class HomeFaq with _$HomeFaq {
  const factory HomeFaq({
    required int id,
    required String question,
    required String answer,
  }) = _HomeFaq;
}

@freezed
abstract class HomeReview with _$HomeReview {
  const factory HomeReview({
    required int id,
    required int rating,
    required String reviewer,
    required bool verifiedPurchase,
    required DateTime createdAt,
    required String productName,
    required String productSlug,
    String? title,
    String? body,
    LinkTarget? productLink,
  }) = _HomeReview;
}
