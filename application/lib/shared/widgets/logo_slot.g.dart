// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'logo_slot.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Whether empty Logo Slots are drawn (dev and staging); production shows plain-text tiles only.

@ProviderFor(showLogoSlots)
final showLogoSlotsProvider = ShowLogoSlotsProvider._();

/// Whether empty Logo Slots are drawn (dev and staging); production shows plain-text tiles only.

final class ShowLogoSlotsProvider extends $FunctionalProvider<bool, bool, bool>
    with $Provider<bool> {
  /// Whether empty Logo Slots are drawn (dev and staging); production shows plain-text tiles only.
  ShowLogoSlotsProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'showLogoSlotsProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$showLogoSlotsHash();

  @$internal
  @override
  $ProviderElement<bool> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  bool create(Ref ref) {
    return showLogoSlots(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(bool value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<bool>(value),
    );
  }
}

String _$showLogoSlotsHash() => r'2a0ec60e5b0cabf1198c58ad2231dcac59eadc86';
