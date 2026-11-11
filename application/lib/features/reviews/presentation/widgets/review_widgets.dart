import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/reviews/domain/entities/reviews.dart';
import 'package:application/shared/widgets/status_pill.dart';
import 'package:flutter/material.dart';

/// The moderation status of a review: Pending, Published or Not published.
(String, StatusPillTone) reviewStatusView(
  AppLocalizations l10n,
  String status,
) => switch (status) {
  'approved' => (l10n.reviewStatusApproved, StatusPillTone.success),
  'rejected' => (l10n.reviewStatusRejected, StatusPillTone.danger),
  _ => (l10n.reviewStatusPending, StatusPillTone.warning),
};

/// "Order BX-1004" with the number as an LTR island.
String orderNumberLabel(AppLocalizations l10n, String number) =>
    l10n.reviewOrderNumber(ltrIsolate(number));

/// Read-only stars: [rating] of five filled from the start side.
class StaticStars extends StatelessWidget {
  const StaticStars({required this.rating, this.size = 18, super.key});

  final int rating;

  /// Star size in logical pixels (`num`: feature code keeps floating-point types out, rule 6).
  final num size;

  @override
  Widget build(BuildContext context) {
    final color = context.colors.accentText;
    return Semantics(
      label: context.l10n.reviewsRating(rating),
      excludeSemantics: true,
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          for (var star = 1; star <= 5; star++)
            Icon(
              star <= rating ? Icons.star : Icons.star_border,
              size: size.toDouble(),
              color: color,
            ),
        ],
      ),
    );
  }
}

/// The rating input of 13.2: five 48dp stars; each says "n of 5 stars" and whether it is the
/// chosen rating.
class StarRatingInput extends StatelessWidget {
  const StarRatingInput({
    required this.rating,
    required this.onChanged,
    this.errorText,
    super.key,
  });

  final int rating;
  final ValueChanged<int>? onChanged;
  final String? errorText;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final error = errorText;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          l10n.reviewRatingLabel,
          style: context.type.labelMd.copyWith(color: colors.contentSecondary),
        ),
        const SizedBox(height: Spacing.s1),
        Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            for (var star = 1; star <= 5; star++)
              Semantics(
                button: true,
                selected: star == rating,
                label: l10n.reviewRateStars(star),
                excludeSemantics: true,
                onTap: onChanged == null ? null : () => onChanged!(star),
                child: SizedBox.square(
                  dimension: 48,
                  child: IconButton(
                    key: ValueKey('review-star-$star'),
                    padding: EdgeInsets.zero,
                    onPressed: onChanged == null
                        ? null
                        : () => onChanged!(star),
                    icon: Icon(
                      star <= rating ? Icons.star : Icons.star_border,
                      size: 36,
                      color: star <= rating
                          ? colors.accentText
                          : colors.contentMuted,
                    ),
                  ),
                ),
              ),
          ],
        ),
        if (error != null)
          Semantics(
            liveRegion: true,
            child: Text(
              error,
              style: context.type.bodyCaption.copyWith(
                color: colors.statusDanger,
              ),
            ),
          ),
      ],
    );
  }
}

/// An item ready to review (13.1 "Ready to review"): opens the form.
class ReviewableItemTile extends StatelessWidget {
  const ReviewableItemTile({
    required this.item,
    required this.onTap,
    super.key,
  });

  final ReviewableItem item;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final order = orderNumberLabel(l10n, item.orderNumber);
    return Semantics(
      button: true,
      label: '${item.productName}, $order',
      hint: l10n.reviewWriteHint,
      excludeSemantics: true,
      onTap: onTap,
      child: Material(
        color: colors.surfaceRaised,
        borderRadius: BorderRadius.circular(Radii.md),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          child: ConstrainedBox(
            constraints: const BoxConstraints(minHeight: 64),
            child: Padding(
              padding: const EdgeInsetsDirectional.all(Spacing.s3),
              child: Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: colors.surfaceOverlay,
                      borderRadius: BorderRadius.circular(Radii.sm),
                    ),
                    child: Icon(
                      Icons.star_border,
                      size: 22,
                      color: colors.contentPrimary,
                    ),
                  ),
                  const SizedBox(width: Spacing.s3),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(item.productName, style: type.headingSm),
                        Text(
                          order,
                          style: type.bodySm.copyWith(
                            color: colors.contentSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Icon(Icons.chevron_right, color: colors.contentSecondary),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// One of the customer's reviews (13.1): product, status, stars, title and text, and "Edit"
/// (the edit goes back to moderation).
class MyReviewCard extends StatelessWidget {
  const MyReviewCard({required this.review, required this.onEdit, super.key});

  final MyReview review;
  final VoidCallback onEdit;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final (status, tone) = reviewStatusView(l10n, review.status);
    final title = review.title;
    final body = review.body;
    return DecoratedBox(
      decoration: BoxDecoration(
        color: colors.surfaceRaised,
        borderRadius: BorderRadius.circular(Radii.md),
      ),
      child: Padding(
        padding: const EdgeInsetsDirectional.fromSTEB(
          Spacing.s3,
          Spacing.s3,
          Spacing.s1,
          Spacing.s1,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            MergeSemantics(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Padding(
                    padding: const EdgeInsetsDirectional.only(end: Spacing.s2),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Text(
                            review.productName,
                            style: type.headingSm,
                          ),
                        ),
                        const SizedBox(width: Spacing.s2),
                        StatusPill(label: status, tone: tone),
                      ],
                    ),
                  ),
                  const SizedBox(height: Spacing.s1),
                  StaticStars(rating: review.rating.clamp(0, 5)),
                  if (title != null && title.isNotEmpty) ...[
                    const SizedBox(height: Spacing.s1),
                    Text(title, style: type.labelLg),
                  ],
                  if (body != null && body.isNotEmpty) ...[
                    const SizedBox(height: Spacing.s1),
                    Padding(
                      padding: const EdgeInsetsDirectional.only(
                        end: Spacing.s2,
                      ),
                      child: Text(
                        body,
                        style: type.bodyMd.copyWith(
                          color: colors.contentSecondary,
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),
            Align(
              alignment: AlignmentDirectional.centerEnd,
              child: TextButton.icon(
                key: ValueKey('review-edit-${review.id}'),
                style: TextButton.styleFrom(
                  minimumSize: const Size(48, 48),
                  foregroundColor: colors.accentText,
                ),
                onPressed: onEdit,
                icon: const Icon(Icons.edit_outlined, size: 18),
                label: Text(
                  l10n.reviewEditAction,
                  semanticsLabel: l10n.reviewEditSemantics(review.productName),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
