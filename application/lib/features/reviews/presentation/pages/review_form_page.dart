import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/reviews/presentation/controllers/reviews_controllers.dart';
import 'package:application/features/reviews/presentation/widgets/review_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:application/shared/widgets/subject_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Write a review (13.2) of an eligible order item, or edit one of the customer's reviews; the
/// errors of 13.3 (`email_unverified`, `review_exists`, `review_not_eligible`) as banners and
/// the server's field messages beside the fields.
class ReviewFormPage extends ConsumerStatefulWidget {
  const ReviewFormPage({this.orderItemId, this.reviewId, super.key});

  final int? orderItemId;
  final int? reviewId;

  @override
  ConsumerState<ReviewFormPage> createState() => _ReviewFormPageState();
}

class _ReviewFormPageState extends ConsumerState<ReviewFormPage> {
  final _title = TextEditingController();
  final _body = TextEditingController();

  ReviewFormControllerProvider get _provider => reviewFormControllerProvider(
    orderItemId: widget.orderItemId,
    reviewId: widget.reviewId,
  );

  @override
  void initState() {
    super.initState();
    // An edited review starts with its text.
    ref.listenManual(_provider.select((s) => s.subject), (_, subject) {
      final review = subject.valueOrNull?.review;
      if (review != null && _title.text.isEmpty && _body.text.isEmpty) {
        _title.text = review.title ?? '';
        _body.text = review.body ?? '';
      }
    }, fireImmediately: true);
  }

  @override
  void dispose() {
    _title.dispose();
    _body.dispose();
    super.dispose();
  }

  void _close() =>
      context.canPop() ? context.pop() : context.go(Routes.myReviews.path);

  Future<void> _submit() async {
    final l10n = context.l10n;
    FocusScope.of(context).unfocus();
    final saved = await ref
        .read(_provider.notifier)
        .submit(title: _title.text, body: _body.text);
    if (saved == null || !mounted) {
      return;
    }
    showAppSnackbar(
      context,
      message: widget.reviewId == null
          ? l10n.reviewSubmitted
          : l10n.reviewUpdated,
    );
    _close();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(_provider);
    final controller = ref.read(_provider.notifier);
    final editing = widget.reviewId != null;
    final appBar = MobileAppBar(
      leading: AppBarLeading.close,
      title: editing ? l10n.reviewEditTitle : l10n.reviewWriteTitle,
      onLeading: _close,
    );
    switch (state.subject) {
      case Loading():
        return Scaffold(
          appBar: appBar,
          body: const Padding(
            padding: EdgeInsetsDirectional.all(Spacing.s4),
            child: SkeletonRegion(
              child: Column(
                children: [
                  SkeletonBlock(height: 64, radius: Radii.md),
                  SizedBox(height: Spacing.s4),
                  SkeletonBlock(height: 160, radius: Radii.md),
                ],
              ),
            ),
          ),
        );
      case Failed(failure: NotFoundFailure()):
        return Scaffold(
          appBar: appBar,
          body: Center(
            child: SingleChildScrollView(
              child: EmptyState(
                icon: Icons.rate_review_outlined,
                title: editing
                    ? l10n.reviewNotFoundTitle
                    : l10n.reviewNotEligibleTitle,
                body: editing ? null : l10n.reviewNotEligibleBody,
                actionLabel: l10n.myReviewsTitle,
                onAction: () => context.go(Routes.myReviews.path),
              ),
            ),
          ),
        );
      case Failed(:final failure):
        return Scaffold(
          appBar: appBar,
          body: FailureView(
            failure: failure,
            errorTitle: l10n.reviewLoadError,
            onRetry: () => unawaited(controller.retry()),
          ),
        );
      case Loaded(value: final subject):
        final banner = state.bannerFailure;
        return FlowScaffold(
          appBar: appBar,
          body: [
            if (banner != null) ...[
              _FailureBanner(failure: banner),
              const SizedBox(height: Spacing.s4),
            ],
            SubjectCard(
              title: subject.productName,
              subtitle: subject.orderNumber == null
                  ? null
                  : orderNumberLabel(l10n, subject.orderNumber!),
            ),
            if (editing) ...[
              const SizedBox(height: Spacing.s3),
              StatusBanner(
                tone: BannerTone.info,
                title: l10n.reviewEditNoticeTitle,
                body: l10n.reviewEditNoticeBody,
              ),
            ],
            const SizedBox(height: Spacing.s4),
            StarRatingInput(
              rating: state.rating,
              onChanged: state.saving ? null : controller.setRating,
              errorText: state.problems['rating'] != null
                  ? l10n.reviewRatingRequired
                  : state.serverError('rating'),
            ),
            const SizedBox(height: Spacing.s3),
            AppTextField(
              key: const ValueKey('review-title'),
              label: l10n.reviewTitleLabel,
              controller: _title,
              maxLength: reviewTitleMax,
              errorText: state.serverError('title'),
              textInputAction: TextInputAction.next,
              textCapitalization: TextCapitalization.sentences,
              onChanged: (_) => controller.edited(),
            ),
            const SizedBox(height: Spacing.s3),
            AppTextField(
              key: const ValueKey('review-body'),
              label: l10n.reviewBodyLabel,
              controller: _body,
              minLines: 3,
              maxLines: 8,
              maxLength: reviewBodyMax,
              keyboardType: TextInputType.multiline,
              textCapitalization: TextCapitalization.sentences,
              helperText: l10n.reviewModerationNote,
              errorText: state.serverError('body'),
              onChanged: (_) => controller.edited(),
            ),
          ],
          footer: [
            AppButton(
              key: const ValueKey('review-submit'),
              label: editing ? l10n.reviewSaveAction : l10n.reviewSubmitAction,
              expand: true,
              loading: state.saving,
              onPressed: () => unawaited(_submit()),
            ),
          ],
        );
    }
  }
}

/// The 13.3 banners: verify the email first, already reviewed, not eligible, or the failure.
class _FailureBanner extends StatelessWidget {
  const _FailureBanner({required this.failure});

  final Failure failure;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    return switch (failure) {
      EmailUnverifiedFailure() => Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          StatusBanner(
            tone: BannerTone.danger,
            title: l10n.reviewEmailUnverifiedTitle,
            body: l10n.reviewEmailUnverifiedBody,
          ),
          const SizedBox(height: Spacing.s2),
          AppButton(
            label: l10n.revealVerifyAction,
            variant: AppButtonVariant.secondary,
            expand: true,
            onPressed: () => unawaited(context.push(Routes.verifyEmail.path)),
          ),
        ],
      ),
      ConflictFailure(code: 'review_exists') => StatusBanner(
        tone: BannerTone.info,
        title: l10n.reviewExistsTitle,
        body: l10n.reviewExistsBody,
      ),
      DomainFailure(code: 'review_not_eligible', :final message) =>
        StatusBanner(
          tone: BannerTone.danger,
          title: l10n.reviewNotEligibleTitle,
          body: message,
        ),
      _ => StatusBanner(
        tone: BannerTone.danger,
        title: failureDetail(l10n, failure),
      ),
    };
  }
}
