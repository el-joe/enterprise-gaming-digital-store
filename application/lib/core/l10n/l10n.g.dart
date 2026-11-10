// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'l10n.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Device locales; a provider so tests can pin them.

@ProviderFor(deviceLocales)
final deviceLocalesProvider = DeviceLocalesProvider._();

/// Device locales; a provider so tests can pin them.

final class DeviceLocalesProvider
    extends $FunctionalProvider<List<Locale>, List<Locale>, List<Locale>>
    with $Provider<List<Locale>> {
  /// Device locales; a provider so tests can pin them.
  DeviceLocalesProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'deviceLocalesProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$deviceLocalesHash();

  @$internal
  @override
  $ProviderElement<List<Locale>> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  List<Locale> create(Ref ref) {
    return deviceLocales(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(List<Locale> value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<List<Locale>>(value),
    );
  }
}

String _$deviceLocalesHash() => r'f107d8a8ee86e8592a70d7764cd33fc5c090e675';

/// The app language: the saved choice, else the device language when supported, else English.

@ProviderFor(AppLocale)
final appLocaleProvider = AppLocaleProvider._();

/// The app language: the saved choice, else the device language when supported, else English.
final class AppLocaleProvider extends $NotifierProvider<AppLocale, Locale> {
  /// The app language: the saved choice, else the device language when supported, else English.
  AppLocaleProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'appLocaleProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$appLocaleHash();

  @$internal
  @override
  AppLocale create() => AppLocale();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(Locale value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<Locale>(value),
    );
  }
}

String _$appLocaleHash() => r'6fecc9cdf79e73213cb5456df4a262dadd2c7840';

/// The app language: the saved choice, else the device language when supported, else English.

abstract class _$AppLocale extends $Notifier<Locale> {
  Locale build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<Locale, Locale>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<Locale, Locale>,
              Locale,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
