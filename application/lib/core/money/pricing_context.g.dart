// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'pricing_context.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Every provider that holds prices (home, listings) watches this, so a currency switch or a
/// language change invalidates all priced data at once and each screen reloads from page 1.
/// Records compare by value, so dependants rebuild only on a real change.

@ProviderFor(pricingContext)
final pricingContextProvider = PricingContextProvider._();

/// Every provider that holds prices (home, listings) watches this, so a currency switch or a
/// language change invalidates all priced data at once and each screen reloads from page 1.
/// Records compare by value, so dependants rebuild only on a real change.

final class PricingContextProvider
    extends $FunctionalProvider<PricingContext, PricingContext, PricingContext>
    with $Provider<PricingContext> {
  /// Every provider that holds prices (home, listings) watches this, so a currency switch or a
  /// language change invalidates all priced data at once and each screen reloads from page 1.
  /// Records compare by value, so dependants rebuild only on a real change.
  PricingContextProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'pricingContextProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$pricingContextHash();

  @$internal
  @override
  $ProviderElement<PricingContext> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  PricingContext create(Ref ref) {
    return pricingContext(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(PricingContext value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<PricingContext>(value),
    );
  }
}

String _$pricingContextHash() => r'2f18e27e5743d11219550105bce28b69c9986348';
