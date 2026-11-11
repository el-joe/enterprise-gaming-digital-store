// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'native_bridge.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(nativeBridge)
final nativeBridgeProvider = NativeBridgeProvider._();

final class NativeBridgeProvider
    extends $FunctionalProvider<NativeBridge, NativeBridge, NativeBridge>
    with $Provider<NativeBridge> {
  NativeBridgeProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'nativeBridgeProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$nativeBridgeHash();

  @$internal
  @override
  $ProviderElement<NativeBridge> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  NativeBridge create(Ref ref) {
    return nativeBridge(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(NativeBridge value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<NativeBridge>(value),
    );
  }
}

String _$nativeBridgeHash() => r'4496685842178c01251d017f78fdbf4ccd155d70';

/// `device_name` sent on login and register.

@ProviderFor(deviceName)
final deviceNameProvider = DeviceNameProvider._();

/// `device_name` sent on login and register.

final class DeviceNameProvider
    extends $FunctionalProvider<AsyncValue<String>, String, FutureOr<String>>
    with $FutureModifier<String>, $FutureProvider<String> {
  /// `device_name` sent on login and register.
  DeviceNameProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'deviceNameProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$deviceNameHash();

  @$internal
  @override
  $FutureProviderElement<String> $createElement($ProviderPointer pointer) =>
      $FutureProviderElement(pointer);

  @override
  FutureOr<String> create(Ref ref) {
    return deviceName(ref);
  }
}

String _$deviceNameHash() => r'b9f6eeeae222ebfe0db016ce6a41fcd8c73c9fe6';
