import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/features/catalog/presentation/controllers/catalog_controllers.dart';
import 'package:application/features/catalog/presentation/widgets/currency_sheet.dart';
import 'package:application/shared/widgets/logo_slot.dart';
import 'package:application/shared/widgets/pill_button.dart';
import 'package:application/shared/widgets/platform_tile.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/store_header.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Browse tab (04.8): categories (cards with product counts, children as chips) and a row of
/// platforms with "See all". Plain text cards: no category art exists in the API (BD8).
class BrowsePage extends ConsumerWidget {
  const BrowsePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final categories = ref.watch(categoriesControllerProvider);
    final platforms = ref.watch(platformsControllerProvider);
    final failure = switch ((categories, platforms)) {
      (Failed(:final failure), _) => failure,
      (_, Failed(:final failure)) => failure,
      _ => null,
    };
    return Scaffold(
      body: SafeArea(
        bottom: false,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            StoreHeader(
              onSearch: () => context.go(Routes.search.path),
              currencyCode: ref.watch(displayCurrencyProvider),
              onCurrency: () => unawaited(showCurrencySheet(context)),
            ),
            Expanded(
              child: failure != null
                  ? FailureView(
                      failure: failure,
                      onRetry: () {
                        if (categories is Failed) {
                          unawaited(
                            ref
                                .read(categoriesControllerProvider.notifier)
                                .retry(),
                          );
                        }
                        if (platforms is Failed) {
                          unawaited(
                            ref
                                .read(platformsControllerProvider.notifier)
                                .retry(),
                          );
                        }
                      },
                    )
                  : ListView(
                      padding: const EdgeInsetsDirectional.fromSTEB(
                        Spacing.s4,
                        0,
                        Spacing.s4,
                        Spacing.s8,
                      ),
                      children: [
                        SectionHeader(title: l10n.categoriesTitle),
                        ...switch (categories) {
                          Loaded(:final value) => [
                            for (final group in groupCategories(value))
                              Padding(
                                padding: const EdgeInsetsDirectional.only(
                                  bottom: Spacing.s3,
                                ),
                                child: _CategoryCard(group: group),
                              ),
                          ],
                          _ => [
                            const SkeletonRegion(
                              child: Column(
                                children: [
                                  SkeletonBlock(height: 96, radius: Radii.md),
                                  SizedBox(height: Spacing.s3),
                                  SkeletonBlock(height: 96, radius: Radii.md),
                                ],
                              ),
                            ),
                          ],
                        },
                        const SizedBox(height: Spacing.s3),
                        SectionHeader(
                          title: l10n.platformsTitle,
                          onSeeAll: () => context.go(Routes.platforms.path),
                        ),
                        switch (platforms) {
                          Loaded(:final value) => _PlatformStrip(
                            platforms: value,
                          ),
                          _ => const SkeletonRegion(
                            child: SkeletonBlock(
                              height: PlatformTile.height,
                              radius: Radii.lg,
                            ),
                          ),
                        },
                      ],
                    ),
            ),
          ],
        ),
      ),
    );
  }
}

class _CategoryCard extends StatelessWidget {
  const _CategoryCard({required this.group});

  final CategoryGroup group;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final parent = group.parent;
    void open(Category category) =>
        context.go(Routes.listingLocation(category: category.slug));
    return Material(
      color: colors.surfaceRaised,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(Radii.md),
        side: BorderSide(color: colors.lineSubtle),
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Semantics(
            button: true,
            label: '${parent.name}, ${l10n.productCount(parent.productCount)}',
            excludeSemantics: true,
            onTap: () => open(parent),
            child: InkWell(
              onTap: () => open(parent),
              child: ConstrainedBox(
                constraints: const BoxConstraints(minHeight: 96),
                child: Padding(
                  padding: const EdgeInsetsDirectional.all(Spacing.s4),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(parent.name, style: type.headingMd),
                      const SizedBox(height: Spacing.s1),
                      Text(
                        l10n.productCount(parent.productCount),
                        style: type.bodySm.copyWith(
                          color: colors.contentSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
          if (group.children.isNotEmpty)
            Padding(
              padding: const EdgeInsetsDirectional.fromSTEB(
                Spacing.s3,
                0,
                Spacing.s3,
                Spacing.s2,
              ),
              child: Wrap(
                spacing: Spacing.s2,
                children: [
                  for (final child in group.children)
                    PillButton(label: child.name, onTap: () => open(child)),
                ],
              ),
            ),
        ],
      ),
    );
  }
}

class _PlatformStrip extends ConsumerWidget {
  const _PlatformStrip({required this.platforms});

  final List<Platform> platforms;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final showSlot = ref.watch(showLogoSlotsProvider);
    if (platforms.isEmpty) {
      return Text(
        context.l10n.platformsEmpty,
        style: context.type.bodyMd.copyWith(
          color: context.colors.contentSecondary,
        ),
      );
    }
    return SizedBox(
      height: PlatformTile.height,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
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
            onTap: () =>
                context.go(Routes.listingLocation(platform: platform.slug)),
          );
        },
      ),
    );
  }
}
