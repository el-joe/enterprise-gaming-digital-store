// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'home_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Home (03.1 to 03.4): `GET /storefront/home`. Reloads when the display currency or the
/// language changes ([pricingContextProvider]); a failure stays until [retry].

@ProviderFor(HomeController)
final homeControllerProvider = HomeControllerProvider._();

/// Home (03.1 to 03.4): `GET /storefront/home`. Reloads when the display currency or the
/// language changes ([pricingContextProvider]); a failure stays until [retry].
final class HomeControllerProvider
    extends $NotifierProvider<HomeController, Loadable<HomePage>> {
  /// Home (03.1 to 03.4): `GET /storefront/home`. Reloads when the display currency or the
  /// language changes ([pricingContextProvider]); a failure stays until [retry].
  HomeControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'homeControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$homeControllerHash();

  @$internal
  @override
  HomeController create() => HomeController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(Loadable<HomePage> value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<Loadable<HomePage>>(value),
    );
  }
}

String _$homeControllerHash() => r'015811358a13b60501270730f11e85bda80b3585';

/// Home (03.1 to 03.4): `GET /storefront/home`. Reloads when the display currency or the
/// language changes ([pricingContextProvider]); a failure stays until [retry].

abstract class _$HomeController extends $Notifier<Loadable<HomePage>> {
  Loadable<HomePage> build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<Loadable<HomePage>, Loadable<HomePage>>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<Loadable<HomePage>, Loadable<HomePage>>,
              Loadable<HomePage>,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
