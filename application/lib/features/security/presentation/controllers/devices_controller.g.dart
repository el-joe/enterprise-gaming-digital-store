// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'devices_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Devices (18.5, 18.6): the signed-in app devices and, with the database session driver,
/// browser sessions. Signing one out is `DELETE /account/devices/{id}` (this device is never
/// offered: 409 `current_device`, use sign out instead; 404 means it was already gone, so the
/// list is reloaded). "Sign out of all other devices" is `POST /devices/sign-out-others` with the
/// current password; this device stays signed in.

@ProviderFor(DevicesController)
final devicesControllerProvider = DevicesControllerProvider._();

/// Devices (18.5, 18.6): the signed-in app devices and, with the database session driver,
/// browser sessions. Signing one out is `DELETE /account/devices/{id}` (this device is never
/// offered: 409 `current_device`, use sign out instead; 404 means it was already gone, so the
/// list is reloaded). "Sign out of all other devices" is `POST /devices/sign-out-others` with the
/// current password; this device stays signed in.
final class DevicesControllerProvider
    extends $NotifierProvider<DevicesController, DevicesState> {
  /// Devices (18.5, 18.6): the signed-in app devices and, with the database session driver,
  /// browser sessions. Signing one out is `DELETE /account/devices/{id}` (this device is never
  /// offered: 409 `current_device`, use sign out instead; 404 means it was already gone, so the
  /// list is reloaded). "Sign out of all other devices" is `POST /devices/sign-out-others` with the
  /// current password; this device stays signed in.
  DevicesControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'devicesControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$devicesControllerHash();

  @$internal
  @override
  DevicesController create() => DevicesController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(DevicesState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<DevicesState>(value),
    );
  }
}

String _$devicesControllerHash() => r'de6796613a95af6b593c52f357e31b4b6ee02852';

/// Devices (18.5, 18.6): the signed-in app devices and, with the database session driver,
/// browser sessions. Signing one out is `DELETE /account/devices/{id}` (this device is never
/// offered: 409 `current_device`, use sign out instead; 404 means it was already gone, so the
/// list is reloaded). "Sign out of all other devices" is `POST /devices/sign-out-others` with the
/// current password; this device stays signed in.

abstract class _$DevicesController extends $Notifier<DevicesState> {
  DevicesState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<DevicesState, DevicesState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<DevicesState, DevicesState>,
              DevicesState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
