// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'clipboard_guard.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Kept for the app's lifetime so a copy is still cleared after the code screen is left (the
/// customer typically switches to the game app to paste).

@ProviderFor(clipboardGuard)
final clipboardGuardProvider = ClipboardGuardProvider._();

/// Kept for the app's lifetime so a copy is still cleared after the code screen is left (the
/// customer typically switches to the game app to paste).

final class ClipboardGuardProvider
    extends $FunctionalProvider<ClipboardGuard, ClipboardGuard, ClipboardGuard>
    with $Provider<ClipboardGuard> {
  /// Kept for the app's lifetime so a copy is still cleared after the code screen is left (the
  /// customer typically switches to the game app to paste).
  ClipboardGuardProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'clipboardGuardProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$clipboardGuardHash();

  @$internal
  @override
  $ProviderElement<ClipboardGuard> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  ClipboardGuard create(Ref ref) {
    return clipboardGuard(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ClipboardGuard value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ClipboardGuard>(value),
    );
  }
}

String _$clipboardGuardHash() => r'dc44320426c6c9716be3b5bc3387df319f4251de';
