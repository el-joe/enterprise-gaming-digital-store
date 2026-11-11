// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'device_integrity.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Read once per launch from the native check.

@ProviderFor(deviceIntegrity)
final deviceIntegrityProvider = DeviceIntegrityProvider._();

/// Read once per launch from the native check.

final class DeviceIntegrityProvider
    extends
        $FunctionalProvider<
          AsyncValue<DeviceIntegrity>,
          DeviceIntegrity,
          FutureOr<DeviceIntegrity>
        >
    with $FutureModifier<DeviceIntegrity>, $FutureProvider<DeviceIntegrity> {
  /// Read once per launch from the native check.
  DeviceIntegrityProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'deviceIntegrityProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$deviceIntegrityHash();

  @$internal
  @override
  $FutureProviderElement<DeviceIntegrity> $createElement(
    $ProviderPointer pointer,
  ) => $FutureProviderElement(pointer);

  @override
  FutureOr<DeviceIntegrity> create(Ref ref) {
    return deviceIntegrity(ref);
  }
}

String _$deviceIntegrityHash() => r'580787552351b25b9ebe5366b17bd8fd3059df1d';
