// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'review_eligibility.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The reviews feature's public entry for other features (product page, order detail): which
/// order items the customer may review, as the API says (`GET /reviews/eligible`: items of paid
/// orders with a delivered unit and no review yet, a verified purchase). Nothing is guessed on
/// the device: an item that is not in the list gets no "Write a review".
///
/// Auto-disposed, so every screen that shows the entry reads a fresh list. Signed out, with
/// `reviews_enabled` off or after a failed read, the list is empty (no entry is shown).

@ProviderFor(ReviewEligibility)
final reviewEligibilityProvider = ReviewEligibilityProvider._();

/// The reviews feature's public entry for other features (product page, order detail): which
/// order items the customer may review, as the API says (`GET /reviews/eligible`: items of paid
/// orders with a delivered unit and no review yet, a verified purchase). Nothing is guessed on
/// the device: an item that is not in the list gets no "Write a review".
///
/// Auto-disposed, so every screen that shows the entry reads a fresh list. Signed out, with
/// `reviews_enabled` off or after a failed read, the list is empty (no entry is shown).
final class ReviewEligibilityProvider
    extends
        $NotifierProvider<ReviewEligibility, Loadable<List<ReviewableItem>>> {
  /// The reviews feature's public entry for other features (product page, order detail): which
  /// order items the customer may review, as the API says (`GET /reviews/eligible`: items of paid
  /// orders with a delivered unit and no review yet, a verified purchase). Nothing is guessed on
  /// the device: an item that is not in the list gets no "Write a review".
  ///
  /// Auto-disposed, so every screen that shows the entry reads a fresh list. Signed out, with
  /// `reviews_enabled` off or after a failed read, the list is empty (no entry is shown).
  ReviewEligibilityProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'reviewEligibilityProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$reviewEligibilityHash();

  @$internal
  @override
  ReviewEligibility create() => ReviewEligibility();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(Loadable<List<ReviewableItem>> value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<Loadable<List<ReviewableItem>>>(
        value,
      ),
    );
  }
}

String _$reviewEligibilityHash() => r'8d4d7592913416b649c85e5b0632ce65dcb59da7';

/// The reviews feature's public entry for other features (product page, order detail): which
/// order items the customer may review, as the API says (`GET /reviews/eligible`: items of paid
/// orders with a delivered unit and no review yet, a verified purchase). Nothing is guessed on
/// the device: an item that is not in the list gets no "Write a review".
///
/// Auto-disposed, so every screen that shows the entry reads a fresh list. Signed out, with
/// `reviews_enabled` off or after a failed read, the list is empty (no entry is shown).

abstract class _$ReviewEligibility
    extends $Notifier<Loadable<List<ReviewableItem>>> {
  Loadable<List<ReviewableItem>> build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref =
        this.ref
            as $Ref<
              Loadable<List<ReviewableItem>>,
              Loadable<List<ReviewableItem>>
            >;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<
                Loadable<List<ReviewableItem>>,
                Loadable<List<ReviewableItem>>
              >,
              Loadable<List<ReviewableItem>>,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// The newest eligible order item of the product [slug], if any (the product page's "Write a
/// review").

@ProviderFor(eligibleReviewForProduct)
final eligibleReviewForProductProvider = EligibleReviewForProductFamily._();

/// The newest eligible order item of the product [slug], if any (the product page's "Write a
/// review").

final class EligibleReviewForProductProvider
    extends
        $FunctionalProvider<ReviewableItem?, ReviewableItem?, ReviewableItem?>
    with $Provider<ReviewableItem?> {
  /// The newest eligible order item of the product [slug], if any (the product page's "Write a
  /// review").
  EligibleReviewForProductProvider._({
    required EligibleReviewForProductFamily super.from,
    required String super.argument,
  }) : super(
         retry: null,
         name: r'eligibleReviewForProductProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$eligibleReviewForProductHash();

  @override
  String toString() {
    return r'eligibleReviewForProductProvider'
        ''
        '($argument)';
  }

  @$internal
  @override
  $ProviderElement<ReviewableItem?> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  ReviewableItem? create(Ref ref) {
    final argument = this.argument as String;
    return eligibleReviewForProduct(ref, argument);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ReviewableItem? value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ReviewableItem?>(value),
    );
  }

  @override
  bool operator ==(Object other) {
    return other is EligibleReviewForProductProvider &&
        other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$eligibleReviewForProductHash() =>
    r'42946903ef04c729d3859b34976dec67fb9c314d';

/// The newest eligible order item of the product [slug], if any (the product page's "Write a
/// review").

final class EligibleReviewForProductFamily extends $Family
    with $FunctionalFamilyOverride<ReviewableItem?, String> {
  EligibleReviewForProductFamily._()
    : super(
        retry: null,
        name: r'eligibleReviewForProductProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// The newest eligible order item of the product [slug], if any (the product page's "Write a
  /// review").

  EligibleReviewForProductProvider call(String slug) =>
      EligibleReviewForProductProvider._(argument: slug, from: this);

  @override
  String toString() => r'eligibleReviewForProductProvider';
}

/// The eligible order item [orderItemId] (a delivered order line), if the API lists it.

@ProviderFor(eligibleReviewForOrderItem)
final eligibleReviewForOrderItemProvider = EligibleReviewForOrderItemFamily._();

/// The eligible order item [orderItemId] (a delivered order line), if the API lists it.

final class EligibleReviewForOrderItemProvider
    extends
        $FunctionalProvider<ReviewableItem?, ReviewableItem?, ReviewableItem?>
    with $Provider<ReviewableItem?> {
  /// The eligible order item [orderItemId] (a delivered order line), if the API lists it.
  EligibleReviewForOrderItemProvider._({
    required EligibleReviewForOrderItemFamily super.from,
    required int super.argument,
  }) : super(
         retry: null,
         name: r'eligibleReviewForOrderItemProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$eligibleReviewForOrderItemHash();

  @override
  String toString() {
    return r'eligibleReviewForOrderItemProvider'
        ''
        '($argument)';
  }

  @$internal
  @override
  $ProviderElement<ReviewableItem?> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  ReviewableItem? create(Ref ref) {
    final argument = this.argument as int;
    return eligibleReviewForOrderItem(ref, argument);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ReviewableItem? value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ReviewableItem?>(value),
    );
  }

  @override
  bool operator ==(Object other) {
    return other is EligibleReviewForOrderItemProvider &&
        other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$eligibleReviewForOrderItemHash() =>
    r'9361fb483a0d309f96c9f9e16f096dc0cca98333';

/// The eligible order item [orderItemId] (a delivered order line), if the API lists it.

final class EligibleReviewForOrderItemFamily extends $Family
    with $FunctionalFamilyOverride<ReviewableItem?, int> {
  EligibleReviewForOrderItemFamily._()
    : super(
        retry: null,
        name: r'eligibleReviewForOrderItemProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// The eligible order item [orderItemId] (a delivered order line), if the API lists it.

  EligibleReviewForOrderItemProvider call(int orderItemId) =>
      EligibleReviewForOrderItemProvider._(argument: orderItemId, from: this);

  @override
  String toString() => r'eligibleReviewForOrderItemProvider';
}
