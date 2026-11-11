import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/product/domain/entities/product.dart';
import 'package:application/features/product/presentation/controllers/product_controllers.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:flutter/material.dart';

/// Five stars for [full] whole stars and an optional half.
class RatingStars extends StatelessWidget {
  const RatingStars({required this.full, this.half = false, super.key});

  final int full;
  final bool half;

  @override
  Widget build(BuildContext context) {
    final color = context.colors.accentText;
    return ExcludeSemantics(
      // Stars fill from the start side in both directions (the half star mirrors in RTL).
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          for (var star = 1; star <= 5; star++)
            Icon(
              star <= full
                  ? Icons.star
                  : star == full + 1 && half
                  ? Icons.star_half
                  : Icons.star_border,
              size: 16,
              color: color,
            ),
        ],
      ),
    );
  }
}

/// Rating summary (05.2): the average as a large number, its stars and the review count.
class RatingSummary extends StatelessWidget {
  const RatingSummary({required this.average, required this.count, super.key});

  /// Decimal string from `meta.rating_avg`.
  final String average;
  final int count;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final type = context.type;
    final stars = ratingStars(average);
    return Semantics(
      label:
          '${l10n.productAverageRating(ratingLabel(average))}, '
          '${l10n.productReviewsCount(count)}',
      excludeSemantics: true,
      child: Row(
        children: [
          Text(
            ratingLabel(average),
            style: type.displaySm,
            textDirection: TextDirection.ltr,
          ),
          const SizedBox(width: Spacing.s3),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              RatingStars(full: stars.full, half: stars.half),
              Text(
                l10n.productReviewsCount(count),
                style: type.bodyCaption.copyWith(
                  color: context.colors.contentMuted,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

/// One approved review (library `Review Card`): stars, title, body, reviewer (first name and
/// initial, as the API sends it) and "Verified purchase".
class ReviewCard extends StatelessWidget {
  const ReviewCard({required this.review, super.key});

  final ProductReview review;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    return MergeSemantics(
      child: DecoratedBox(
        decoration: BoxDecoration(
          color: colors.surfaceRaised,
          border: Border.all(color: colors.lineSubtle),
          borderRadius: BorderRadius.circular(Radii.md),
        ),
        child: Padding(
          padding: const EdgeInsetsDirectional.all(Spacing.s4),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Semantics(
                label: l10n.reviewsRating(review.rating),
                child: RatingStars(full: review.rating.clamp(0, 5)),
              ),
              if (review.title != null && review.title!.isNotEmpty) ...[
                const SizedBox(height: Spacing.s2),
                Text(review.title!, style: type.labelLg),
              ],
              if (review.body != null && review.body!.isNotEmpty) ...[
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
                  if (review.verifiedPurchase) l10n.verifiedPurchase,
                ].join(' · '),
                style: type.labelSm.copyWith(color: colors.contentMuted),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Skeleton of a few review cards.
class ReviewsSkeleton extends StatelessWidget {
  const ReviewsSkeleton({this.count = 2, super.key});

  final int count;

  @override
  Widget build(BuildContext context) => SkeletonRegion(
    child: Column(
      children: [
        for (var i = 0; i < count; i++) ...[
          if (i > 0) const SizedBox(height: Spacing.s3),
          const SkeletonBlock(height: 96, radius: Radii.md),
        ],
      ],
    ),
  );
}
