// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'catalog_controllers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Platforms (04.1, 04.8), translated by `Accept-Language`.

@ProviderFor(PlatformsController)
final platformsControllerProvider = PlatformsControllerProvider._();

/// Platforms (04.1, 04.8), translated by `Accept-Language`.
final class PlatformsControllerProvider
    extends $NotifierProvider<PlatformsController, Loadable<List<Platform>>> {
  /// Platforms (04.1, 04.8), translated by `Accept-Language`.
  PlatformsControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'platformsControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$platformsControllerHash();

  @$internal
  @override
  PlatformsController create() => PlatformsController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(Loadable<List<Platform>> value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<Loadable<List<Platform>>>(value),
    );
  }
}

String _$platformsControllerHash() =>
    r'5e785761b728060041c74e12c6f46657731b312b';

/// Platforms (04.1, 04.8), translated by `Accept-Language`.

abstract class _$PlatformsController
    extends $Notifier<Loadable<List<Platform>>> {
  Loadable<List<Platform>> build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref =
        this.ref as $Ref<Loadable<List<Platform>>, Loadable<List<Platform>>>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<Loadable<List<Platform>>, Loadable<List<Platform>>>,
              Loadable<List<Platform>>,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// Categories (04.8), parents first as the API sends them.

@ProviderFor(CategoriesController)
final categoriesControllerProvider = CategoriesControllerProvider._();

/// Categories (04.8), parents first as the API sends them.
final class CategoriesControllerProvider
    extends $NotifierProvider<CategoriesController, Loadable<List<Category>>> {
  /// Categories (04.8), parents first as the API sends them.
  CategoriesControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'categoriesControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$categoriesControllerHash();

  @$internal
  @override
  CategoriesController create() => CategoriesController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(Loadable<List<Category>> value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<Loadable<List<Category>>>(value),
    );
  }
}

String _$categoriesControllerHash() =>
    r'64ec5d68cec8cba893d4b11d01f4d306c1f982c5';

/// Categories (04.8), parents first as the API sends them.

abstract class _$CategoriesController
    extends $Notifier<Loadable<List<Category>>> {
  Loadable<List<Category>> build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref =
        this.ref as $Ref<Loadable<List<Category>>, Loadable<List<Category>>>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<Loadable<List<Category>>, Loadable<List<Category>>>,
              Loadable<List<Category>>,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// The listing title: category and platform names joined with " · " (04.2), from the loaded
/// taxonomies; the slug until they load.

@ProviderFor(listingTitle)
final listingTitleProvider = ListingTitleFamily._();

/// The listing title: category and platform names joined with " · " (04.2), from the loaded
/// taxonomies; the slug until they load.

final class ListingTitleProvider
    extends $FunctionalProvider<String, String, String>
    with $Provider<String> {
  /// The listing title: category and platform names joined with " · " (04.2), from the loaded
  /// taxonomies; the slug until they load.
  ListingTitleProvider._({
    required ListingTitleFamily super.from,
    required ({String? category, String? platform}) super.argument,
  }) : super(
         retry: null,
         name: r'listingTitleProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$listingTitleHash();

  @override
  String toString() {
    return r'listingTitleProvider'
        ''
        '$argument';
  }

  @$internal
  @override
  $ProviderElement<String> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  String create(Ref ref) {
    final argument = this.argument as ({String? category, String? platform});
    return listingTitle(
      ref,
      category: argument.category,
      platform: argument.platform,
    );
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(String value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<String>(value),
    );
  }

  @override
  bool operator ==(Object other) {
    return other is ListingTitleProvider && other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$listingTitleHash() => r'cb75a101045f93e71348608b1ef260c29746270b';

/// The listing title: category and platform names joined with " · " (04.2), from the loaded
/// taxonomies; the slug until they load.

final class ListingTitleFamily extends $Family
    with
        $FunctionalFamilyOverride<
          String,
          ({String? category, String? platform})
        > {
  ListingTitleFamily._()
    : super(
        retry: null,
        name: r'listingTitleProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// The listing title: category and platform names joined with " · " (04.2), from the loaded
  /// taxonomies; the slug until they load.

  ListingTitleProvider call({String? category, String? platform}) =>
      ListingTitleProvider._(
        argument: (category: category, platform: platform),
        from: this,
      );

  @override
  String toString() => r'listingTitleProvider';
}

/// The listing for a base query (category, platform, sort from the route). Pages are fetched
/// with `page` and `per_page`; [loadMore] appends the next page while `meta` says one exists.
/// A currency or language change ([pricingContextProvider]) starts again from page 1.

@ProviderFor(ListingController)
final listingControllerProvider = ListingControllerFamily._();

/// The listing for a base query (category, platform, sort from the route). Pages are fetched
/// with `page` and `per_page`; [loadMore] appends the next page while `meta` says one exists.
/// A currency or language change ([pricingContextProvider]) starts again from page 1.
final class ListingControllerProvider
    extends $NotifierProvider<ListingController, ListingState> {
  /// The listing for a base query (category, platform, sort from the route). Pages are fetched
  /// with `page` and `per_page`; [loadMore] appends the next page while `meta` says one exists.
  /// A currency or language change ([pricingContextProvider]) starts again from page 1.
  ListingControllerProvider._({
    required ListingControllerFamily super.from,
    required CatalogQuery super.argument,
  }) : super(
         retry: null,
         name: r'listingControllerProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$listingControllerHash();

  @override
  String toString() {
    return r'listingControllerProvider'
        ''
        '($argument)';
  }

  @$internal
  @override
  ListingController create() => ListingController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ListingState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ListingState>(value),
    );
  }

  @override
  bool operator ==(Object other) {
    return other is ListingControllerProvider && other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$listingControllerHash() => r'dd3beeff5beea6e9b6c2d69c3d033d3c71f0c271';

/// The listing for a base query (category, platform, sort from the route). Pages are fetched
/// with `page` and `per_page`; [loadMore] appends the next page while `meta` says one exists.
/// A currency or language change ([pricingContextProvider]) starts again from page 1.

final class ListingControllerFamily extends $Family
    with
        $ClassFamilyOverride<
          ListingController,
          ListingState,
          ListingState,
          ListingState,
          CatalogQuery
        > {
  ListingControllerFamily._()
    : super(
        retry: null,
        name: r'listingControllerProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// The listing for a base query (category, platform, sort from the route). Pages are fetched
  /// with `page` and `per_page`; [loadMore] appends the next page while `meta` says one exists.
  /// A currency or language change ([pricingContextProvider]) starts again from page 1.

  ListingControllerProvider call(CatalogQuery base) =>
      ListingControllerProvider._(argument: base, from: this);

  @override
  String toString() => r'listingControllerProvider';
}

/// The listing for a base query (category, platform, sort from the route). Pages are fetched
/// with `page` and `per_page`; [loadMore] appends the next page while `meta` says one exists.
/// A currency or language change ([pricingContextProvider]) starts again from page 1.

abstract class _$ListingController extends $Notifier<ListingState> {
  late final _$args = ref.$arg as CatalogQuery;
  CatalogQuery get base => _$args;

  ListingState build(CatalogQuery base);
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<ListingState, ListingState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<ListingState, ListingState>,
              ListingState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, () => build(_$args));
  }
}
