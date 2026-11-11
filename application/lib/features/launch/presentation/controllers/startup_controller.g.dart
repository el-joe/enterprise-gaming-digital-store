// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'startup_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The launch check (01.1 to 01.4): `GET /app/config` with this build's platform and version,
/// then force update (`update.required`), maintenance, or onward to onboarding or home. The guards
/// show the update and maintenance screens from [availabilityProvider].

@ProviderFor(StartupController)
final startupControllerProvider = StartupControllerProvider._();

/// The launch check (01.1 to 01.4): `GET /app/config` with this build's platform and version,
/// then force update (`update.required`), maintenance, or onward to onboarding or home. The guards
/// show the update and maintenance screens from [availabilityProvider].
final class StartupControllerProvider
    extends $NotifierProvider<StartupController, StartupState> {
  /// The launch check (01.1 to 01.4): `GET /app/config` with this build's platform and version,
  /// then force update (`update.required`), maintenance, or onward to onboarding or home. The guards
  /// show the update and maintenance screens from [availabilityProvider].
  StartupControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'startupControllerProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$startupControllerHash();

  @$internal
  @override
  StartupController create() => StartupController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(StartupState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<StartupState>(value),
    );
  }
}

String _$startupControllerHash() => r'1ffc7002a2b5b015fa135383b94a28c15ca2ecbd';

/// The launch check (01.1 to 01.4): `GET /app/config` with this build's platform and version,
/// then force update (`update.required`), maintenance, or onward to onboarding or home. The guards
/// show the update and maintenance screens from [availabilityProvider].

abstract class _$StartupController extends $Notifier<StartupState> {
  StartupState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<StartupState, StartupState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<StartupState, StartupState>,
              StartupState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// The rooted or jailbroken device sheet, shown once per install (owner decision 5).

@ProviderFor(IntegrityWarningController)
final integrityWarningControllerProvider =
    IntegrityWarningControllerProvider._();

/// The rooted or jailbroken device sheet, shown once per install (owner decision 5).
final class IntegrityWarningControllerProvider
    extends $NotifierProvider<IntegrityWarningController, void> {
  /// The rooted or jailbroken device sheet, shown once per install (owner decision 5).
  IntegrityWarningControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'integrityWarningControllerProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$integrityWarningControllerHash();

  @$internal
  @override
  IntegrityWarningController create() => IntegrityWarningController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(void value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<void>(value),
    );
  }
}

String _$integrityWarningControllerHash() =>
    r'c82e263b6df55401809a957ab731c7acd66cbc8d';

/// The rooted or jailbroken device sheet, shown once per install (owner decision 5).

abstract class _$IntegrityWarningController extends $Notifier<void> {
  void build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<void, void>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<void, void>,
              void,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
