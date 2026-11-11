import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/state/paged_list.dart';
import 'package:application/features/reviews/domain/entities/reviews.dart';
import 'package:application/features/reviews/review_eligibility.dart';
import 'package:application/features/reviews/reviews_providers.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'reviews_controllers.g.dart';

/// Longest title and body the API accepts (`StoreReviewRequest`).
const reviewTitleMax = 190;
const reviewBodyMax = 5000;

/// My reviews (13.1, 13.3): `GET /account/reviews`, newest first, 20 per page, with the
/// moderation `status` (`pending`, `approved`, `rejected`). The items ready to review come from
/// [reviewEligibilityProvider]. Signed out nothing is loaded.
@riverpod
class MyReviewsController extends _$MyReviewsController {
  late final PagedLoader<MyReview> _loader = PagedLoader(
    fetch: (page) => ref.read(reviewsRepositoryProvider).myReviews(page: page),
    read: () => state,
    write: (value) => state = value,
    mounted: () => ref.mounted,
  );

  @override
  PagedListState<MyReview> build() {
    final signedIn = ref.watch(sessionProvider) == SessionStatus.signedIn;
    if (!signedIn) {
      return const PagedListState(content: Loadable.loaded([]));
    }
    unawaited(_loader.loadFirst());
    return const PagedListState();
  }

  Future<void> retry() async {
    unawaited(ref.read(reviewEligibilityProvider.notifier).refresh());
    await _loader.retry();
  }

  /// Pull to refresh: the reviews and the items ready to review.
  Future<void> refresh() async {
    unawaited(ref.read(reviewEligibilityProvider.notifier).refresh());
    await _loader.refresh();
  }

  Future<void> loadMore() => _loader.loadMore();
}

/// What the form reviews: an eligible order item (new review) or one of the customer's
/// reviews (edit).
@immutable
final class ReviewSubject {
  const ReviewSubject({
    required this.productName,
    this.orderNumber,
    this.review,
  });

  final String productName;

  /// The order the item was bought in (new reviews; the API's review shape has no order).
  final String? orderNumber;

  /// The review being edited.
  final MyReview? review;

  bool get editing => review != null;
}

/// A local check of the form.
enum ReviewFieldProblem { ratingRequired }

@immutable
final class ReviewFormState {
  const ReviewFormState({
    this.subject = const Loadable.loading(),
    this.rating = 0,
    this.saving = false,
    this.problems = const {},
    this.failure,
  });

  /// Not found: the item is not eligible (any more) or the review is not the customer's.
  final Loadable<ReviewSubject> subject;

  /// 1 to 5; 0 until chosen.
  final int rating;
  final bool saving;
  final Map<String, ReviewFieldProblem> problems;

  /// The last attempt's failure: 422 field messages show beside `rating`, `title`, `body`;
  /// `email_unverified`, `review_exists`, `review_not_eligible` and anything else in a banner.
  final Failure? failure;

  static const fields = ['rating', 'title', 'body'];

  String? serverError(String field) => switch (failure) {
    final ValidationFailure validation => validation.firstErrorFor(field),
    _ => null,
  };

  Failure? get bannerFailure => switch (failure) {
    final ValidationFailure validation
        when fields.any((f) => validation.firstErrorFor(f) != null) =>
      null,
    _ => failure,
  };

  ReviewFormState copyWith({
    Loadable<ReviewSubject>? subject,
    int? rating,
    bool? saving,
    Map<String, ReviewFieldProblem>? problems,
    Failure? Function()? failure,
  }) => ReviewFormState(
    subject: subject ?? this.subject,
    rating: rating ?? this.rating,
    saving: saving ?? this.saving,
    problems: problems ?? this.problems,
    failure: failure == null ? this.failure : failure(),
  );
}

/// Write a review (13.2) of the eligible order item [orderItemId], or edit the review
/// [reviewId] (`PATCH /reviews/{id}`: it goes back to moderation). The subject comes from the
/// API (`/reviews/eligible`, or the customer's reviews), never from the caller. Rating 1 to 5 is
/// checked first; the server is authoritative for everything else.
@riverpod
class ReviewFormController extends _$ReviewFormController {
  @override
  ReviewFormState build({int? orderItemId, int? reviewId}) {
    unawaited(_loadSubject());
    return const ReviewFormState();
  }

  Future<void> _loadSubject() async {
    final repository = ref.read(reviewsRepositoryProvider);
    final Loadable<ReviewSubject> subject;
    var rating = 0;
    if (reviewId case final id?) {
      final found = await _findReview(id);
      subject = switch (found) {
        Ok(value: final review?) => Loadable.loaded(
          ReviewSubject(productName: review.productName, review: review),
        ),
        Ok() => const Loadable.failed(NotFoundFailure()),
        Err(:final failure) => Loadable.failed(failure),
      };
      rating = found.valueOrNull?.rating ?? 0;
    } else {
      final result = await repository.eligible();
      subject = switch (result) {
        Ok(:final value) => switch ([
          for (final item in value)
            if (item.orderItemId == orderItemId) item,
        ]) {
          [final item, ...] => Loadable.loaded(
            ReviewSubject(
              productName: item.productName,
              orderNumber: item.orderNumber,
            ),
          ),
          _ => const Loadable.failed(NotFoundFailure()),
        },
        Err(:final failure) => Loadable.failed(failure),
      };
    }
    if (ref.mounted) {
      state = state.copyWith(subject: subject, rating: rating);
    }
  }

  /// The customer's review [id], looked up in their reviews (50 per page; the API has no
  /// single-review read).
  Future<Result<MyReview?>> _findReview(int id) async {
    final repository = ref.read(reviewsRepositoryProvider);
    for (var page = 1; ; page++) {
      final result = await repository.myReviews(page: page, perPage: 50);
      switch (result) {
        case Err(:final failure):
          return Err(failure);
        case Ok(:final value):
          for (final review in value.items) {
            if (review.id == id) {
              return Ok(review);
            }
          }
          if (!value.page.hasNextPage) {
            return const Ok(null);
          }
      }
    }
  }

  /// "Try again" after the subject failed to load.
  Future<void> retry() async {
    state = const ReviewFormState();
    await _loadSubject();
  }

  void setRating(int rating) {
    if (rating < 1 || rating > 5) {
      return;
    }
    state = state.copyWith(
      rating: rating,
      problems: const {},
      failure: () => null,
    );
  }

  /// The customer typed: a shown failure goes.
  void edited() {
    if (state.failure != null || state.problems.isNotEmpty) {
      state = state.copyWith(problems: const {}, failure: () => null);
    }
  }

  /// Sends the review. Returns it (status `pending`) on success.
  Future<MyReview?> submit({
    required String title,
    required String body,
  }) async {
    final subject = state.subject.valueOrNull;
    if (state.saving || subject == null) {
      return null;
    }
    if (state.rating < 1) {
      state = state.copyWith(
        problems: const {'rating': ReviewFieldProblem.ratingRequired},
      );
      return null;
    }
    final cleanTitle = title.trim().isEmpty ? null : title.trim();
    final cleanBody = body.trim().isEmpty ? null : body.trim();
    state = state.copyWith(
      saving: true,
      problems: const {},
      failure: () => null,
    );
    final link = ref.keepAlive();
    try {
      final repository = ref.read(reviewsRepositoryProvider);
      final review = subject.review;
      final result = review != null
          ? await repository.update(
              review.id,
              rating: state.rating,
              title: cleanTitle,
              body: cleanBody,
            )
          : await repository.submit(
              orderItemId: orderItemId!,
              rating: state.rating,
              title: cleanTitle,
              body: cleanBody,
            );
      switch (result) {
        case Ok(:final value):
          ref
            ..invalidate(reviewEligibilityProvider)
            ..invalidate(myReviewsControllerProvider);
          if (ref.mounted) {
            state = state.copyWith(saving: false);
          }
          return value;
        case Err(:final failure):
          if (ref.mounted) {
            state = state.copyWith(saving: false, failure: () => failure);
          }
          return null;
      }
    } finally {
      link.close();
    }
  }
}
