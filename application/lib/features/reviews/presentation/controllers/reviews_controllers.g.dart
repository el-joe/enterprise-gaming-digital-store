// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'reviews_controllers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// My reviews (13.1, 13.3): `GET /account/reviews`, newest first, 20 per page, with the
/// moderation `status` (`pending`, `approved`, `rejected`). The items ready to review come from
/// [reviewEligibilityProvider]. Signed out nothing is loaded.

@ProviderFor(MyReviewsController)
final myReviewsControllerProvider = MyReviewsControllerProvider._();

/// My reviews (13.1, 13.3): `GET /account/reviews`, newest first, 20 per page, with the
/// moderation `status` (`pending`, `approved`, `rejected`). The items ready to review come from
/// [reviewEligibilityProvider]. Signed out nothing is loaded.
final class MyReviewsControllerProvider
    extends $NotifierProvider<MyReviewsController, PagedListState<MyReview>> {
  /// My reviews (13.1, 13.3): `GET /account/reviews`, newest first, 20 per page, with the
  /// moderation `status` (`pending`, `approved`, `rejected`). The items ready to review come from
  /// [reviewEligibilityProvider]. Signed out nothing is loaded.
  MyReviewsControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'myReviewsControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$myReviewsControllerHash();

  @$internal
  @override
  MyReviewsController create() => MyReviewsController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(PagedListState<MyReview> value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<PagedListState<MyReview>>(value),
    );
  }
}

String _$myReviewsControllerHash() =>
    r'4f540393e3a18ece555699e4319196affd25a9a3';

/// My reviews (13.1, 13.3): `GET /account/reviews`, newest first, 20 per page, with the
/// moderation `status` (`pending`, `approved`, `rejected`). The items ready to review come from
/// [reviewEligibilityProvider]. Signed out nothing is loaded.

abstract class _$MyReviewsController
    extends $Notifier<PagedListState<MyReview>> {
  PagedListState<MyReview> build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref =
        this.ref as $Ref<PagedListState<MyReview>, PagedListState<MyReview>>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<PagedListState<MyReview>, PagedListState<MyReview>>,
              PagedListState<MyReview>,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// Write a review (13.2) of the eligible order item [orderItemId], or edit the review
/// [reviewId] (`PATCH /reviews/{id}`: it goes back to moderation). The subject comes from the
/// API (`/reviews/eligible`, or the customer's reviews), never from the caller. Rating 1 to 5 is
/// checked first; the server is authoritative for everything else.

@ProviderFor(ReviewFormController)
final reviewFormControllerProvider = ReviewFormControllerFamily._();

/// Write a review (13.2) of the eligible order item [orderItemId], or edit the review
/// [reviewId] (`PATCH /reviews/{id}`: it goes back to moderation). The subject comes from the
/// API (`/reviews/eligible`, or the customer's reviews), never from the caller. Rating 1 to 5 is
/// checked first; the server is authoritative for everything else.
final class ReviewFormControllerProvider
    extends $NotifierProvider<ReviewFormController, ReviewFormState> {
  /// Write a review (13.2) of the eligible order item [orderItemId], or edit the review
  /// [reviewId] (`PATCH /reviews/{id}`: it goes back to moderation). The subject comes from the
  /// API (`/reviews/eligible`, or the customer's reviews), never from the caller. Rating 1 to 5 is
  /// checked first; the server is authoritative for everything else.
  ReviewFormControllerProvider._({
    required ReviewFormControllerFamily super.from,
    required ({int? orderItemId, int? reviewId}) super.argument,
  }) : super(
         retry: null,
         name: r'reviewFormControllerProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$reviewFormControllerHash();

  @override
  String toString() {
    return r'reviewFormControllerProvider'
        ''
        '$argument';
  }

  @$internal
  @override
  ReviewFormController create() => ReviewFormController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ReviewFormState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ReviewFormState>(value),
    );
  }

  @override
  bool operator ==(Object other) {
    return other is ReviewFormControllerProvider && other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$reviewFormControllerHash() =>
    r'88aced01771f9e45c68f950464b1b744aed4fdb6';

/// Write a review (13.2) of the eligible order item [orderItemId], or edit the review
/// [reviewId] (`PATCH /reviews/{id}`: it goes back to moderation). The subject comes from the
/// API (`/reviews/eligible`, or the customer's reviews), never from the caller. Rating 1 to 5 is
/// checked first; the server is authoritative for everything else.

final class ReviewFormControllerFamily extends $Family
    with
        $ClassFamilyOverride<
          ReviewFormController,
          ReviewFormState,
          ReviewFormState,
          ReviewFormState,
          ({int? orderItemId, int? reviewId})
        > {
  ReviewFormControllerFamily._()
    : super(
        retry: null,
        name: r'reviewFormControllerProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// Write a review (13.2) of the eligible order item [orderItemId], or edit the review
  /// [reviewId] (`PATCH /reviews/{id}`: it goes back to moderation). The subject comes from the
  /// API (`/reviews/eligible`, or the customer's reviews), never from the caller. Rating 1 to 5 is
  /// checked first; the server is authoritative for everything else.

  ReviewFormControllerProvider call({int? orderItemId, int? reviewId}) =>
      ReviewFormControllerProvider._(
        argument: (orderItemId: orderItemId, reviewId: reviewId),
        from: this,
      );

  @override
  String toString() => r'reviewFormControllerProvider';
}

/// Write a review (13.2) of the eligible order item [orderItemId], or edit the review
/// [reviewId] (`PATCH /reviews/{id}`: it goes back to moderation). The subject comes from the
/// API (`/reviews/eligible`, or the customer's reviews), never from the caller. Rating 1 to 5 is
/// checked first; the server is authoritative for everything else.

abstract class _$ReviewFormController extends $Notifier<ReviewFormState> {
  late final _$args = ref.$arg as ({int? orderItemId, int? reviewId});
  int? get orderItemId => _$args.orderItemId;
  int? get reviewId => _$args.reviewId;

  ReviewFormState build({int? orderItemId, int? reviewId});
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<ReviewFormState, ReviewFormState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<ReviewFormState, ReviewFormState>,
              ReviewFormState,
              Object?,
              Object?
            >;
    return element.handleCreate(
      ref,
      () => build(orderItemId: _$args.orderItemId, reviewId: _$args.reviewId),
    );
  }
}
