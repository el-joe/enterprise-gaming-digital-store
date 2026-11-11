// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'currency_switcher_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Currency switcher sheet (03.5). Applying saves the choice (on the profile when signed in,
/// through `UpdateRegionalSettings`; locally otherwise) and changes the `X-Currency` the app
/// sends. Every priced provider watches `pricingContextProvider`, so they all reload in the new
/// currency (owner decision 1: prices always come from the server in the display currency).

@ProviderFor(CurrencySwitcherController)
final currencySwitcherControllerProvider =
    CurrencySwitcherControllerProvider._();

/// Currency switcher sheet (03.5). Applying saves the choice (on the profile when signed in,
/// through `UpdateRegionalSettings`; locally otherwise) and changes the `X-Currency` the app
/// sends. Every priced provider watches `pricingContextProvider`, so they all reload in the new
/// currency (owner decision 1: prices always come from the server in the display currency).
final class CurrencySwitcherControllerProvider
    extends
        $NotifierProvider<CurrencySwitcherController, CurrencySwitcherState> {
  /// Currency switcher sheet (03.5). Applying saves the choice (on the profile when signed in,
  /// through `UpdateRegionalSettings`; locally otherwise) and changes the `X-Currency` the app
  /// sends. Every priced provider watches `pricingContextProvider`, so they all reload in the new
  /// currency (owner decision 1: prices always come from the server in the display currency).
  CurrencySwitcherControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'currencySwitcherControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$currencySwitcherControllerHash();

  @$internal
  @override
  CurrencySwitcherController create() => CurrencySwitcherController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(CurrencySwitcherState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<CurrencySwitcherState>(value),
    );
  }
}

String _$currencySwitcherControllerHash() =>
    r'aef98514cc55e83e4ddeb0983f3714e833632e9d';

/// Currency switcher sheet (03.5). Applying saves the choice (on the profile when signed in,
/// through `UpdateRegionalSettings`; locally otherwise) and changes the `X-Currency` the app
/// sends. Every priced provider watches `pricingContextProvider`, so they all reload in the new
/// currency (owner decision 1: prices always come from the server in the display currency).

abstract class _$CurrencySwitcherController
    extends $Notifier<CurrencySwitcherState> {
  CurrencySwitcherState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<CurrencySwitcherState, CurrencySwitcherState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<CurrencySwitcherState, CurrencySwitcherState>,
              CurrencySwitcherState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
