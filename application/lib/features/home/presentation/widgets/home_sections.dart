import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/link_navigation.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/home/domain/entities/home.dart';
import 'package:application/shared/widgets/logo_slot.dart';
import 'package:application/shared/widgets/network_image_box.dart';
import 'package:application/shared/widgets/pill_button.dart';
import 'package:application/shared/widgets/platform_tile.dart';
import 'package:application/shared/widgets/product_grid.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// One server section of the home (`sections[]`, in server order). Items of the same type are
/// grouped in their order of first appearance: products as a two-column grid of Product Tiles,
/// platforms as a row of Platform Chips, categories as chips, banners as cards, FAQ as
/// expandable rows and reviews as cards.
class HomeSectionView extends ConsumerWidget {
  const HomeSectionView({required this.section, super.key});

  final HomeSection section;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final groups = <Type, List<HomeItem>>{};
    for (final item in section.items) {
      (groups[item.runtimeType] ??= []).add(item);
    }
    final title = section.title;
    final first = section.items.first;
    final seeAll = switch (first) {
      HomeProductItem() => Routes.listingLocation(),
      HomePlatformItem() => Routes.platforms.path,
      HomeCategoryItem() => Routes.browse.path,
      _ => null,
    };
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (title != null && title.isNotEmpty)
          Padding(
            padding: const EdgeInsetsDirectional.only(
              start: Spacing.s4,
              end: Spacing.s2,
            ),
            child: SectionHeader(
              title: title,
              onSeeAll: seeAll == null ? null : () => context.go(seeAll),
            ),
          ),
        if (section.subtitle case final subtitle? when subtitle.isNotEmpty)
          Padding(
            padding: const EdgeInsetsDirectional.symmetric(
              horizontal: Spacing.s4,
            ),
            child: Text(
              subtitle,
              style: context.type.bodySm.copyWith(
                color: context.colors.contentSecondary,
              ),
            ),
          ),
        const SizedBox(height: Spacing.s2),
        for (final group in groups.values) ...[
          _group(context, ref, group),
          const SizedBox(height: Spacing.s3),
        ],
      ],
    );
  }

  Widget _group(BuildContext context, WidgetRef ref, List<HomeItem> items) =>
      switch (items.first) {
        HomeProductItem() => ProductGrid(
          products: [
            for (final item in items) (item as HomeProductItem).product,
          ],
        ),
        HomePlatformItem() => _PlatformRow(
          platforms: [
            for (final item in items) (item as HomePlatformItem).platform,
          ],
        ),
        HomeCategoryItem() => _ChipRow(
          categories: [
            for (final item in items) (item as HomeCategoryItem).category,
          ],
        ),
        HomeBannerItem() => Column(
          children: [
            for (final item in items)
              Padding(
                padding: const EdgeInsetsDirectional.fromSTEB(
                  Spacing.s4,
                  0,
                  Spacing.s4,
                  Spacing.s3,
                ),
                child: PromoBannerCard(banner: (item as HomeBannerItem).banner),
              ),
          ],
        ),
        HomeFaqItem() => Column(
          children: [
            for (final item in items) _FaqTile(faq: (item as HomeFaqItem).faq),
          ],
        ),
        HomeReviewItem() => Column(
          children: [
            for (final item in items)
              Padding(
                padding: const EdgeInsetsDirectional.fromSTEB(
                  Spacing.s4,
                  0,
                  Spacing.s4,
                  Spacing.s3,
                ),
                child: _ReviewCard(review: (item as HomeReviewItem).review),
              ),
          ],
        ),
        HomeUnknownItem() => const SizedBox.shrink(),
      };
}

class _PlatformRow extends ConsumerWidget {
  const _PlatformRow({required this.platforms});

  final List<HomeTaxon> platforms;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final showSlot = ref.watch(showLogoSlotsProvider);
    return SizedBox(
      height: PlatformTile.height,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsetsDirectional.symmetric(horizontal: Spacing.s4),
        itemCount: platforms.length,
        separatorBuilder: (_, _) => const SizedBox(width: Spacing.s3),
        itemBuilder: (context, index) {
          final platform = platforms[index];
          return PlatformTile(
            width: 112,
            name: platform.name,
            slug: platform.slug,
            logoUrl: platform.logo?.url,
            showLogoSlot: showSlot,
            onTap: () => followLink(context, ref, platform.link),
          );
        },
      ),
    );
  }
}

class _ChipRow extends ConsumerWidget {
  const _ChipRow({required this.categories});

  final List<HomeTaxon> categories;

  @override
  Widget build(BuildContext context, WidgetRef ref) => SingleChildScrollView(
    scrollDirection: Axis.horizontal,
    padding: const EdgeInsetsDirectional.symmetric(horizontal: Spacing.s4),
    child: Row(
      children: [
        for (final category in categories) ...[
          PillButton(
            label: category.name,
            onTap: () => followLink(context, ref, category.link),
          ),
          const SizedBox(width: Spacing.s2),
        ],
      ],
    ),
  );
}

/// A promo banner (`banners.home_promo` and banner items): the API image with the title over a
/// scrim; opens its link.
class PromoBannerCard extends ConsumerWidget {
  const PromoBannerCard({required this.banner, super.key});

  final HomeBanner banner;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final colors = context.colors;
    final link = banner.link;
    final image = banner.mobileImage ?? banner.desktopImage;
    final onTap = link == null ? null : () => followLink(context, ref, link);
    return Semantics(
      button: onTap != null,
      label: [?banner.title, ?banner.subtitle].join(', '),
      excludeSemantics: true,
      onTap: onTap,
      child: Material(
        color: colors.surfaceRaised,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(Radii.md),
          side: BorderSide(color: colors.lineSubtle),
        ),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          // Full width; 112dp tall at default text size, taller with large text instead of
          // cutting the title (M-A19).
          child: FractionallySizedBox(
            widthFactor: 1,
            child: ConstrainedBox(
              constraints: const BoxConstraints(minHeight: 112),
              child: Stack(
                alignment: AlignmentDirectional.centerStart,
                children: [
                  Positioned.fill(
                    child: NetworkImageBox(
                      url: image?.url,
                      fallback: ColoredBox(color: colors.surfaceRaised),
                    ),
                  ),
                  Positioned.fill(
                    child: DecoratedBox(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: AlignmentDirectional.centerStart,
                          end: AlignmentDirectional.centerEnd,
                          colors: [
                            colors.surfaceCanvas.withValues(alpha: 0.8),
                            colors.surfaceCanvas.withValues(alpha: 0),
                          ],
                        ),
                      ),
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsetsDirectional.all(Spacing.s4),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (banner.title != null)
                          Text(
                            banner.title!,
                            style: context.type.headingSm,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        if (banner.subtitle != null)
                          Text(
                            banner.subtitle!,
                            style: context.type.bodySm.copyWith(
                              color: colors.contentSecondary,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _FaqTile extends StatelessWidget {
  const _FaqTile({required this.faq});

  final HomeFaq faq;

  @override
  Widget build(BuildContext context) => Theme(
    data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
    child: ExpansionTile(
      tilePadding: const EdgeInsetsDirectional.symmetric(
        horizontal: Spacing.s4,
      ),
      childrenPadding: const EdgeInsetsDirectional.fromSTEB(
        Spacing.s4,
        0,
        Spacing.s4,
        Spacing.s3,
      ),
      expandedCrossAxisAlignment: CrossAxisAlignment.start,
      title: Text(faq.question, style: context.type.labelLg),
      children: [
        Text(
          faq.answer,
          style: context.type.bodyMd.copyWith(
            color: context.colors.contentSecondary,
          ),
        ),
      ],
    ),
  );
}

class _ReviewCard extends ConsumerWidget {
  const _ReviewCard({required this.review});

  final HomeReview review;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final link = review.productLink;
    return Material(
      color: colors.surfaceRaised,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(Radii.md),
        side: BorderSide(color: colors.lineSubtle),
      ),
      child: InkWell(
        onTap: link == null ? null : () => followLink(context, ref, link),
        borderRadius: BorderRadius.circular(Radii.md),
        child: Padding(
          padding: const EdgeInsetsDirectional.all(Spacing.s4),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Semantics(
                label: l10n.reviewsRating(review.rating),
                excludeSemantics: true,
                child: Row(
                  children: [
                    for (var star = 1; star <= 5; star++)
                      Icon(
                        star <= review.rating ? Icons.star : Icons.star_border,
                        size: 16,
                        color: colors.accentText,
                      ),
                  ],
                ),
              ),
              if (review.title != null) ...[
                const SizedBox(height: Spacing.s2),
                Text(review.title!, style: type.labelLg),
              ],
              if (review.body != null) ...[
                const SizedBox(height: Spacing.s1),
                Text(
                  review.body!,
                  style: type.bodyMd.copyWith(color: colors.contentSecondary),
                ),
              ],
              const SizedBox(height: Spacing.s2),
              Text(
                [
                  review.reviewer,
                  review.productName,
                  if (review.verifiedPurchase) l10n.verifiedPurchase,
                ].join(' · '),
                style: type.bodyCaption.copyWith(color: colors.contentMuted),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
