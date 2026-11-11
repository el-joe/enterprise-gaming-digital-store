// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'two_factor_controllers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Turn on two-step verification (18.2, 18.3): the current password starts the enrolment
/// (`POST /security/two-factor`), the QR code of the `otpauth://` URI and the key are shown with
/// copy, a 6-digit code confirms it (`POST /security/two-factor/confirm`), and the recovery codes
/// are shown once with copy until the customer says they saved them.
///
/// Nothing here is logged or stored: the password is only passed through, the secret and the
/// codes stay in this auto-disposed state, and copies go through the clipboard guard (cleared
/// after 60 s). Screen protection (`ScreenSecurity`, M-A14) is on while the key or the codes are
/// on screen.

@ProviderFor(TwoFactorSetupController)
final twoFactorSetupControllerProvider = TwoFactorSetupControllerProvider._();

/// Turn on two-step verification (18.2, 18.3): the current password starts the enrolment
/// (`POST /security/two-factor`), the QR code of the `otpauth://` URI and the key are shown with
/// copy, a 6-digit code confirms it (`POST /security/two-factor/confirm`), and the recovery codes
/// are shown once with copy until the customer says they saved them.
///
/// Nothing here is logged or stored: the password is only passed through, the secret and the
/// codes stay in this auto-disposed state, and copies go through the clipboard guard (cleared
/// after 60 s). Screen protection (`ScreenSecurity`, M-A14) is on while the key or the codes are
/// on screen.
final class TwoFactorSetupControllerProvider
    extends $NotifierProvider<TwoFactorSetupController, TwoFactorSetupState> {
  /// Turn on two-step verification (18.2, 18.3): the current password starts the enrolment
  /// (`POST /security/two-factor`), the QR code of the `otpauth://` URI and the key are shown with
  /// copy, a 6-digit code confirms it (`POST /security/two-factor/confirm`), and the recovery codes
  /// are shown once with copy until the customer says they saved them.
  ///
  /// Nothing here is logged or stored: the password is only passed through, the secret and the
  /// codes stay in this auto-disposed state, and copies go through the clipboard guard (cleared
  /// after 60 s). Screen protection (`ScreenSecurity`, M-A14) is on while the key or the codes are
  /// on screen.
  TwoFactorSetupControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'twoFactorSetupControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$twoFactorSetupControllerHash();

  @$internal
  @override
  TwoFactorSetupController create() => TwoFactorSetupController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(TwoFactorSetupState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<TwoFactorSetupState>(value),
    );
  }
}

String _$twoFactorSetupControllerHash() =>
    r'de22cbbc464296b7f9dfe8799856469cba392fbd';

/// Turn on two-step verification (18.2, 18.3): the current password starts the enrolment
/// (`POST /security/two-factor`), the QR code of the `otpauth://` URI and the key are shown with
/// copy, a 6-digit code confirms it (`POST /security/two-factor/confirm`), and the recovery codes
/// are shown once with copy until the customer says they saved them.
///
/// Nothing here is logged or stored: the password is only passed through, the secret and the
/// codes stay in this auto-disposed state, and copies go through the clipboard guard (cleared
/// after 60 s). Screen protection (`ScreenSecurity`, M-A14) is on while the key or the codes are
/// on screen.

abstract class _$TwoFactorSetupController
    extends $Notifier<TwoFactorSetupState> {
  TwoFactorSetupState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<TwoFactorSetupState, TwoFactorSetupState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<TwoFactorSetupState, TwoFactorSetupState>,
              TwoFactorSetupState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// Turn off two-step verification (18.4): `DELETE /security/two-factor` with the current
/// password. State: busy.

@ProviderFor(TwoFactorDisableController)
final twoFactorDisableControllerProvider =
    TwoFactorDisableControllerProvider._();

/// Turn off two-step verification (18.4): `DELETE /security/two-factor` with the current
/// password. State: busy.
final class TwoFactorDisableControllerProvider
    extends $NotifierProvider<TwoFactorDisableController, bool> {
  /// Turn off two-step verification (18.4): `DELETE /security/two-factor` with the current
  /// password. State: busy.
  TwoFactorDisableControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'twoFactorDisableControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$twoFactorDisableControllerHash();

  @$internal
  @override
  TwoFactorDisableController create() => TwoFactorDisableController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(bool value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<bool>(value),
    );
  }
}

String _$twoFactorDisableControllerHash() =>
    r'6a03242bec58c0b9ee3a954d89496d6bf34e513e';

/// Turn off two-step verification (18.4): `DELETE /security/two-factor` with the current
/// password. State: busy.

abstract class _$TwoFactorDisableController extends $Notifier<bool> {
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
