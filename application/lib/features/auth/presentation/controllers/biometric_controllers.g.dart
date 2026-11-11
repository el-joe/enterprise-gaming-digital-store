// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'biometric_controllers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Biometric unlock on or off for this sign-in (owner decision 6). The Security screen (M-A16)
/// uses the same controller for its switch; until then the account card on home shows it.

@ProviderFor(BiometricUnlockSettings)
final biometricUnlockSettingsProvider = BiometricUnlockSettingsProvider._();

/// Biometric unlock on or off for this sign-in (owner decision 6). The Security screen (M-A16)
/// uses the same controller for its switch; until then the account card on home shows it.
final class BiometricUnlockSettingsProvider
    extends $AsyncNotifierProvider<BiometricUnlockSettings, bool> {
  /// Biometric unlock on or off for this sign-in (owner decision 6). The Security screen (M-A16)
  /// uses the same controller for its switch; until then the account card on home shows it.
  BiometricUnlockSettingsProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'biometricUnlockSettingsProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$biometricUnlockSettingsHash();

  @$internal
  @override
  BiometricUnlockSettings create() => BiometricUnlockSettings();
}

String _$biometricUnlockSettingsHash() =>
    r'a7988abe2953489275d5991bb4e59c96f1f47551';

/// Biometric unlock on or off for this sign-in (owner decision 6). The Security screen (M-A16)
/// uses the same controller for its switch; until then the account card on home shows it.

abstract class _$BiometricUnlockSettings extends $AsyncNotifier<bool> {
  FutureOr<bool> build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<AsyncValue<bool>, bool>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<AsyncValue<bool>, bool>,
              AsyncValue<bool>,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// The one-time offer after the first sign-in (owner decision 6). State: enrolling.

@ProviderFor(BiometricOfferController)
final biometricOfferControllerProvider = BiometricOfferControllerProvider._();

/// The one-time offer after the first sign-in (owner decision 6). State: enrolling.
final class BiometricOfferControllerProvider
    extends $NotifierProvider<BiometricOfferController, bool> {
  /// The one-time offer after the first sign-in (owner decision 6). State: enrolling.
  BiometricOfferControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'biometricOfferControllerProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$biometricOfferControllerHash();

  @$internal
  @override
  BiometricOfferController create() => BiometricOfferController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(bool value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<bool>(value),
    );
  }
}

String _$biometricOfferControllerHash() =>
    r'2a42227795d45eea0f99cd74b0c04265022a4bac';

/// The one-time offer after the first sign-in (owner decision 6). State: enrolling.

abstract class _$BiometricOfferController extends $Notifier<bool> {
  bool build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<bool, bool>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<bool, bool>,
              bool,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// App lock (owner decision 6): with biometric unlock on, the app locks after [timeout] in the
/// background (and at a cold start) and opens again with a biometric prompt or the password
/// (`confirm-password`). It gates opening the app only; the server's recent-auth check for code
/// reveal is unchanged.

@ProviderFor(AppLock)
final appLockProvider = AppLockProvider._();

/// App lock (owner decision 6): with biometric unlock on, the app locks after [timeout] in the
/// background (and at a cold start) and opens again with a biometric prompt or the password
/// (`confirm-password`). It gates opening the app only; the server's recent-auth check for code
/// reveal is unchanged.
final class AppLockProvider extends $NotifierProvider<AppLock, AppLockState> {
  /// App lock (owner decision 6): with biometric unlock on, the app locks after [timeout] in the
  /// background (and at a cold start) and opens again with a biometric prompt or the password
  /// (`confirm-password`). It gates opening the app only; the server's recent-auth check for code
  /// reveal is unchanged.
  AppLockProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'appLockProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$appLockHash();

  @$internal
  @override
  AppLock create() => AppLock();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AppLockState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AppLockState>(value),
    );
  }
}

String _$appLockHash() => r'35adfa015835c8e577240d4c1b80a4b54fb81e63';

/// App lock (owner decision 6): with biometric unlock on, the app locks after [timeout] in the
/// background (and at a cold start) and opens again with a biometric prompt or the password
/// (`confirm-password`). It gates opening the app only; the server's recent-auth check for code
/// reveal is unchanged.

abstract class _$AppLock extends $Notifier<AppLockState> {
  AppLockState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<AppLockState, AppLockState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<AppLockState, AppLockState>,
              AppLockState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// Whether this device can use biometric unlock (hides the switch where it cannot).

@ProviderFor(biometricSupport)
final biometricSupportProvider = BiometricSupportProvider._();

/// Whether this device can use biometric unlock (hides the switch where it cannot).

final class BiometricSupportProvider
    extends
        $FunctionalProvider<
          AsyncValue<BiometricSupport>,
          BiometricSupport,
          FutureOr<BiometricSupport>
        >
    with $FutureModifier<BiometricSupport>, $FutureProvider<BiometricSupport> {
  /// Whether this device can use biometric unlock (hides the switch where it cannot).
  BiometricSupportProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'biometricSupportProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$biometricSupportHash();

  @$internal
  @override
  $FutureProviderElement<BiometricSupport> $createElement(
    $ProviderPointer pointer,
  ) => $FutureProviderElement(pointer);

  @override
  FutureOr<BiometricSupport> create(Ref ref) {
    return biometricSupport(ref);
  }
}

String _$biometricSupportHash() => r'7df67e21537c3b6b8c1714a792b89f63471f39ac';
