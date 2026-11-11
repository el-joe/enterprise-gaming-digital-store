import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:flutter/material.dart';

/// A static placeholder block (Figma `Mobile / Skeleton` `76:324`). No shimmer: nothing moves,
/// so reduced motion needs no special case.
class SkeletonBlock extends StatelessWidget {
  const SkeletonBlock({
    this.width,
    this.height = 12,
    this.radius = Radii.sm,
    super.key,
  });

  final double? width;
  final double height;
  final double radius;

  @override
  Widget build(BuildContext context) => Container(
    width: width,
    height: height,
    decoration: BoxDecoration(
      color: context.colors.surfaceRaised,
      borderRadius: BorderRadius.circular(radius),
    ),
  );
}

/// Skeleton of one product tile: art block and three text lines (03.2, 04.5).
class ProductTileSkeleton extends StatelessWidget {
  const ProductTileSkeleton({super.key});

  @override
  Widget build(BuildContext context) => const Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    mainAxisSize: MainAxisSize.min,
    children: [
      SkeletonBlock(height: 120, radius: Radii.lg),
      SizedBox(height: Spacing.s3),
      FractionallySizedBox(widthFactor: 0.8, child: SkeletonBlock()),
      SizedBox(height: Spacing.s2),
      FractionallySizedBox(widthFactor: 0.5, child: SkeletonBlock()),
      SizedBox(height: Spacing.s2),
      FractionallySizedBox(widthFactor: 0.35, child: SkeletonBlock(height: 16)),
    ],
  );
}

/// Announces "Loading" once for a skeleton region and hides the blocks from screen readers.
class SkeletonRegion extends StatelessWidget {
  const SkeletonRegion({required this.child, super.key});

  final Widget child;

  @override
  Widget build(BuildContext context) => Semantics(
    label: context.l10n.loadingLabel,
    liveRegion: true,
    container: true,
    child: ExcludeSemantics(child: child),
  );
}

/// A two-column grid of tile skeletons.
class ProductGridSkeleton extends StatelessWidget {
  const ProductGridSkeleton({this.rows = 2, super.key});

  final int rows;

  @override
  Widget build(BuildContext context) => Column(
    children: [
      for (var row = 0; row < rows; row++) ...[
        if (row > 0) const SizedBox(height: Spacing.s6),
        const Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(child: ProductTileSkeleton()),
            SizedBox(width: LayoutTokens.gutterSm + 4),
            Expanded(child: ProductTileSkeleton()),
          ],
        ),
      ],
    ],
  );
}
