import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/reviews/domain/entities/reviews.dart';
import 'package:application/features/reviews/presentation/controllers/reviews_controllers.dart';
import 'package:application/features/reviews/presentation/widgets/review_widgets.dart';
import 'package:application/features/reviews/review_eligibility.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/paged_list_view.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// My reviews (13.1, 13.3): the items ready to review (verified purchases the API lists) and the
/// customer's reviews with their moderation status and "Edit". Empty, loading, error and
/// offline states.
class MyReviewsPage extends ConsumerWidget {
  const MyReviewsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final state = ref.watch(myReviewsControllerProvider);
    final controller = ref.read(myReviewsControllerProvider.notifier);
    final ready =
        ref.watch(reviewEligibilityProvider).valueOrNull ??
        const <ReviewableItem>[];
    void open(String location) => unawaited(context.push(location));
    final header = <Widget>[
      if (ready.isNotEmpty) ...[
        _SectionLabel(l10n.reviewsReadyTitle),
        for (final item in ready) ...[
          ReviewableItemTile(
            key: ValueKey('reviewable-${item.orderItemId}'),
            item: item,
            onTap: () => open(Routes.writeReviewLocation(item.orderItemId)),
          ),
          const SizedBox(height: Spacing.s2),
        ],
        const SizedBox(height: Spacing.s2),
      ],
      if (state.items.isNotEmpty) _SectionLabel(l10n.reviewsYoursTitle),
    ];
    return Scaffold(
      appBar: MobileAppBar(
        title: l10n.myReviewsTitle,
        onLeading: () =>
            context.canPop() ? context.pop() : context.go(Routes.account.path),
      ),
      body: SafeArea(
        top: false,
        child: PagedListView<MyReview>(
          state: state,
          header: header,
          emptyKeepsHeader: true,
          errorTitle: l10n.myReviewsLoadError,
          moreFailedTitle: l10n.listMoreFailed,
          onRetry: () => unawaited(controller.retry()),
          onRefresh: controller.refresh,
          onLoadMore: () => unawaited(controller.loadMore()),
          empty: ready.isNotEmpty
              ? const SizedBox.shrink()
              : EmptyState(
                  icon: Icons.rate_review_outlined,
                  title: l10n.myReviewsEmptyTitle,
                  body: l10n.myReviewsEmptyBody,
                ),
          itemBuilder: (context, review) => MyReviewCard(
            key: ValueKey('my-review-${review.id}'),
            review: review,
            onEdit: () => open(Routes.editReviewLocation(review.id)),
          ),
        ),
      ),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  const _SectionLabel(this.text);

  final String text;

  @override
  Widget build(BuildContext context) => ConstrainedBox(
    constraints: const BoxConstraints(minHeight: 40),
    child: Align(
      alignment: AlignmentDirectional.centerStart,
      child: Semantics(
        header: true,
        child: Text(text, style: context.type.headingSm),
      ),
    ),
  );
}
