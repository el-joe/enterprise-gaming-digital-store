import 'dart:async';

import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/reviews/domain/entities/reviews.dart';
import 'package:application/features/reviews/reviews_providers.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'review_eligibility.g.dart';

/// The reviews feature's public entry for other features (product page, order detail): which
/// order items the customer may review, as the API says (`GET /reviews/eligible`: items of paid
/// orders with a delivered unit and no review yet, a verified purchase). Nothing is guessed on
/// the device: an item that is not in the list gets no "Write a review".
///
/// Auto-disposed, so every screen that shows the entry reads a fresh list. Signed out, with
/// `reviews_enabled` off or after a failed read, the list is empty (no entry is shown).
@riverpod
class ReviewEligibility extends _$ReviewEligibility {
  int _generation = 0;

  @override
  Loadable<List<ReviewableItem>> build() {
    final signedIn = ref.watch(sessionProvider) == SessionStatus.signedIn;
    final enabled = ref.watch(reviewsEnabledProvider);
    if (!signedIn || !enabled) {
      return const Loadable.loaded([]);
    }
    unawaited(_load());
    return const Loadable.loading();
  }

  Future<void> _load() async {
    final generation = ++_generation;
    final result = await ref.read(reviewsRepositoryProvider).eligible();
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = switch (result) {
      Ok(:final value) => Loadable.loaded(value),
      Err(:final failure) => Loadable.failed(failure),
    };
  }

  /// Reads the list again (after a review was submitted).
  Future<void> refresh() => _load();
}

/// The newest eligible order item of the product [slug], if any (the product page's "Write a
/// review").
@riverpod
ReviewableItem? eligibleReviewForProduct(Ref ref, String slug) {
  final items = ref.watch(reviewEligibilityProvider).valueOrNull ?? const [];
  for (final item in items) {
    if (item.productSlug == slug) {
      return item;
    }
  }
  return null;
}

/// The eligible order item [orderItemId] (a delivered order line), if the API lists it.
@riverpod
ReviewableItem? eligibleReviewForOrderItem(Ref ref, int orderItemId) {
  final items = ref.watch(reviewEligibilityProvider).valueOrNull ?? const [];
  for (final item in items) {
    if (item.orderItemId == orderItemId) {
      return item;
    }
  }
  return null;
}
