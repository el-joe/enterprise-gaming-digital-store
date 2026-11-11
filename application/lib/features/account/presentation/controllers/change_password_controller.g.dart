// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'change_password_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Change password (18.7): `PUT /account/password` with the current, new and confirmed password.
/// The passwords are only passed through (never kept in the state, logged or stored). On
/// success every other device is signed out and every biometric key of the account is revoked
/// (ADR-040), so the local key and flag are removed as well.

@ProviderFor(ChangePasswordController)
final changePasswordControllerProvider = ChangePasswordControllerProvider._();

/// Change password (18.7): `PUT /account/password` with the current, new and confirmed password.
/// The passwords are only passed through (never kept in the state, logged or stored). On
/// success every other device is signed out and every biometric key of the account is revoked
/// (ADR-040), so the local key and flag are removed as well.
final class ChangePasswordControllerProvider
    extends $NotifierProvider<ChangePasswordController, ChangePasswordState> {
  /// Change password (18.7): `PUT /account/password` with the current, new and confirmed password.
  /// The passwords are only passed through (never kept in the state, logged or stored). On
  /// success every other device is signed out and every biometric key of the account is revoked
  /// (ADR-040), so the local key and flag are removed as well.
  ChangePasswordControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'changePasswordControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$changePasswordControllerHash();

  @$internal
  @override
  ChangePasswordController create() => ChangePasswordController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ChangePasswordState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ChangePasswordState>(value),
    );
  }
}

String _$changePasswordControllerHash() =>
    r'07e000ebd908f1f401644514d5eb1f86492d35da';

/// Change password (18.7): `PUT /account/password` with the current, new and confirmed password.
/// The passwords are only passed through (never kept in the state, logged or stored). On
/// success every other device is signed out and every biometric key of the account is revoked
/// (ADR-040), so the local key and flag are removed as well.

abstract class _$ChangePasswordController
    extends $Notifier<ChangePasswordState> {
  ChangePasswordState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<ChangePasswordState, ChangePasswordState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<ChangePasswordState, ChangePasswordState>,
              ChangePasswordState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// Set when a password change turned biometric unlock off (ADR-040): Security (18.1) shows the
/// note to turn it on again until it is on again or dismissed. In memory only.

@ProviderFor(BiometricResetNotice)
final biometricResetNoticeProvider = BiometricResetNoticeProvider._();

/// Set when a password change turned biometric unlock off (ADR-040): Security (18.1) shows the
/// note to turn it on again until it is on again or dismissed. In memory only.
final class BiometricResetNoticeProvider
    extends $NotifierProvider<BiometricResetNotice, bool> {
  /// Set when a password change turned biometric unlock off (ADR-040): Security (18.1) shows the
  /// note to turn it on again until it is on again or dismissed. In memory only.
  BiometricResetNoticeProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'biometricResetNoticeProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$biometricResetNoticeHash();

  @$internal
  @override
  BiometricResetNotice create() => BiometricResetNotice();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(bool value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<bool>(value),
    );
  }
}

String _$biometricResetNoticeHash() =>
    r'599be87db9f1699aa8fe04e8310b3bacce6158c6';

/// Set when a password change turned biometric unlock off (ADR-040): Security (18.1) shows the
/// note to turn it on again until it is on again or dismissed. In memory only.

abstract class _$BiometricResetNotice extends $Notifier<bool> {
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
