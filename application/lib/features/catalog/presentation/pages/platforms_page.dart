import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/catalog/presentation/controllers/catalog_controllers.dart';
import 'package:application/shared/widgets/logo_slot.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/platform_tile.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Platforms (04.1): a three-column grid of plain-text Platform Chips (BD8).
class PlatformsPage extends ConsumerWidget {
  const PlatformsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final state = ref.watch(platformsControllerProvider);
    final showSlot = ref.watch(showLogoSlotsProvider);
    return Scaffold(
      appBar: MobileAppBar(
        title: l10n.platformsTitle,
        onLeading: () =>
            context.canPop() ? context.pop() : context.go(Routes.browse.path),
      ),
      body: switch (state) {
        Failed(:final failure) => FailureView(
          failure: failure,
          onRetry: () =>
              unawaited(ref.read(platformsControllerProvider.notifier).retry()),
        ),
        Loaded(:final value) when value.isEmpty => Center(
          child: EmptyState(icon: Icons.grid_view, title: l10n.platformsEmpty),
        ),
        _ => CustomScrollView(
          slivers: [
            SliverPadding(
              padding: const EdgeInsetsDirectional.fromSTEB(
                Spacing.s4,
                Spacing.s2,
                Spacing.s4,
                Spacing.s4,
              ),
              sliver: SliverToBoxAdapter(
                child: Text(
                  l10n.platformsIntro,
                  style: context.type.bodyMd.copyWith(
                    color: context.colors.contentSecondary,
                  ),
                ),
              ),
            ),
            SliverPadding(
              padding: const EdgeInsetsDirectional.fromSTEB(
                Spacing.s4,
                0,
                Spacing.s4,
                Spacing.s8,
              ),
              sliver: switch (state) {
                Loaded(:final value) => SliverGrid.builder(
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 3,
                    mainAxisSpacing: Spacing.s3,
                    crossAxisSpacing: Spacing.s3,
                    mainAxisExtent: PlatformTile.height,
                  ),
                  itemCount: value.length,
                  itemBuilder: (context, index) {
                    final platform = value[index];
                    return PlatformTile(
                      name: platform.name,
                      slug: platform.slug,
                      logoUrl: platform.logo?.url,
                      showLogoSlot: showSlot,
                      onTap: () => context.go(
                        Routes.listingLocation(platform: platform.slug),
                      ),
                    );
                  },
                ),
                _ => const SliverToBoxAdapter(
                  child: SkeletonRegion(
                    child: Column(
                      children: [
                        SkeletonBlock(
                          height: PlatformTile.height,
                          radius: Radii.lg,
                        ),
                        SizedBox(height: Spacing.s3),
                        SkeletonBlock(
                          height: PlatformTile.height,
                          radius: Radii.lg,
                        ),
                      ],
                    ),
                  ),
                ),
              },
            ),
          ],
        ),
      },
    );
  }
}
