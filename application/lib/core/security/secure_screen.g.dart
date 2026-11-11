// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'secure_screen.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// One instance for the app, so nested code screens count correctly.

@ProviderFor(screenSecurity)
final screenSecurityProvider = ScreenSecurityProvider._();

/// One instance for the app, so nested code screens count correctly.

final class ScreenSecurityProvider
    extends $FunctionalProvider<ScreenSecurity, ScreenSecurity, ScreenSecurity>
    with $Provider<ScreenSecurity> {
  /// One instance for the app, so nested code screens count correctly.
  ScreenSecurityProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'screenSecurityProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$screenSecurityHash();

  @$internal
  @override
  $ProviderElement<ScreenSecurity> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  ScreenSecurity create(Ref ref) {
    return screenSecurity(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ScreenSecurity value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ScreenSecurity>(value),
    );
  }
}

String _$screenSecurityHash() => r'34b5c72a2532b76145de7b18fa2700324c08e954';
