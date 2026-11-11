import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/product/presentation/controllers/product_controllers.dart';
import 'package:application/features/product/presentation/widgets/review_widgets.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// All reviews of a product (05.2): rating summary and the approved reviews, newest first, with
/// infinite scroll (`meta` pagination). A failed next page shows a retry row and never retries
/// on its own.
class ProductReviewsScreen extends ConsumerWidget {
  const ProductReviewsScreen({required this.slug, super.key});

  final String slug;

  /// The next page loads this far before the end.
  static const _preload = 600.0;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final provider = productReviewsControllerProvider(slug);
    final state = ref.watch(provider);
    final controller = ref.read(provider.notifier);

    bool onScroll(ScrollMetrics metrics) {
      if (metrics.extentAfter < _preload &&
          state.hasMore &&
          !state.loadingMore &&
          state.loadMoreFailure == null) {
        unawaited(controller.loadMore());
      }
      return false;
    }

    final Widget body;
    if (state.loading) {
      body = const SingleChildScrollView(
        padding: EdgeInsetsDirectional.all(Spacing.s4),
        child: ReviewsSkeleton(count: 4),
      );
    } else if (state.failure case final failure?) {
      body = FailureView(
        failure: failure,
        onRetry: () => unawaited(controller.retry()),
      );
    } else if (state.items.isEmpty) {
      body = Center(
        child: SingleChildScrollView(
          child: EmptyState(
            icon: Icons.rate_review_outlined,
            title: l10n.reviewsEmptyTitle,
            body: l10n.reviewsEmptyBody,
          ),
        ),
      );
    } else {
      final footer = state.loadMoreFailure != null
          ? _LoadMoreError(onRetry: () => unawaited(controller.loadMore()))
          : state.loadingMore
          ? const Padding(
              padding: EdgeInsetsDirectional.all(Spacing.s4),
              child: ReviewsSkeleton(count: 1),
            )
          : null;
      body = NotificationListener<ScrollMetricsNotification>(
        onNotification: (notification) => onScroll(notification.metrics),
        child: NotificationListener<ScrollUpdateNotification>(
          onNotification: (notification) => onScroll(notification.metrics),
          child: ListView.separated(
            padding: const EdgeInsetsDirectional.all(Spacing.s4),
            itemCount: state.items.length + 1 + (footer == null ? 0 : 1),
            separatorBuilder: (_, _) => const SizedBox(height: Spacing.s3),
            itemBuilder: (context, index) {
              if (index == 0) {
                return RatingSummary(
                  average: state.ratingAvg ?? '0',
                  count: state.ratingCount,
                );
              }
              if (index > state.items.length) {
                return footer!;
              }
              return ReviewCard(review: state.items[index - 1]);
            },
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: context.colors.surfaceCanvas,
      appBar: MobileAppBar(
        title: l10n.reviewsTitle,
        onLeading: () => context.canPop()
            ? context.pop()
            : context.go(Routes.productLocation(slug)),
      ),
      body: body,
    );
  }
}

class _LoadMoreError extends StatelessWidget {
  const _LoadMoreError({required this.onRetry});

  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    return Semantics(
      liveRegion: true,
      container: true,
      child: Row(
        children: [
          Expanded(
            child: Text(
              l10n.loadMoreReviewsFailed,
              style: context.type.bodyMd.copyWith(
                color: colors.contentSecondary,
              ),
            ),
          ),
          TextButton(
            style: TextButton.styleFrom(
              minimumSize: const Size(48, 48),
              foregroundColor: colors.accentText,
            ),
            onPressed: onRetry,
            child: Text(l10n.actionRetry),
          ),
        ],
      ),
    );
  }
}
