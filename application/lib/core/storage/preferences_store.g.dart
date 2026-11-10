// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'preferences_store.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Overridden in `bootstrap.dart` with the opened store.

@ProviderFor(preferencesStore)
final preferencesStoreProvider = PreferencesStoreProvider._();

/// Overridden in `bootstrap.dart` with the opened store.

final class PreferencesStoreProvider
    extends
        $FunctionalProvider<
          PreferencesStore,
          PreferencesStore,
          PreferencesStore
        >
    with $Provider<PreferencesStore> {
  /// Overridden in `bootstrap.dart` with the opened store.
  PreferencesStoreProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'preferencesStoreProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$preferencesStoreHash();

  @$internal
  @override
  $ProviderElement<PreferencesStore> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  PreferencesStore create(Ref ref) {
    return preferencesStore(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(PreferencesStore value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<PreferencesStore>(value),
    );
  }
}

String _$preferencesStoreHash() => r'c41716d6dd14846253878e8b4f1ea4fb7d78bfd3';

/// Display currency sent as `X-Currency`; null until the customer picks one.

@ProviderFor(DisplayCurrency)
final displayCurrencyProvider = DisplayCurrencyProvider._();

/// Display currency sent as `X-Currency`; null until the customer picks one.
final class DisplayCurrencyProvider
    extends $NotifierProvider<DisplayCurrency, String?> {
  /// Display currency sent as `X-Currency`; null until the customer picks one.
  DisplayCurrencyProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'displayCurrencyProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$displayCurrencyHash();

  @$internal
  @override
  DisplayCurrency create() => DisplayCurrency();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(String? value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<String?>(value),
    );
  }
}

String _$displayCurrencyHash() => r'c4170fe4daf75868a3bfad248f8ae40e82889e4b';

/// Display currency sent as `X-Currency`; null until the customer picks one.

abstract class _$DisplayCurrency extends $Notifier<String?> {
  String? build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<String?, String?>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<String?, String?>,
              String?,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
