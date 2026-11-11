// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'two_factor_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Second factor (02.3, 02.4): a 6-digit TOTP code or a recovery code against the pending
/// challenge. `two_factor_invalid` is shown under the field; `two_factor_expired` (or the
/// challenge's own expiry) ends the attempt and sends the customer back to sign in.

@ProviderFor(TwoFactorController)
final twoFactorControllerProvider = TwoFactorControllerProvider._();

/// Second factor (02.3, 02.4): a 6-digit TOTP code or a recovery code against the pending
/// challenge. `two_factor_invalid` is shown under the field; `two_factor_expired` (or the
/// challenge's own expiry) ends the attempt and sends the customer back to sign in.
final class TwoFactorControllerProvider
    extends $NotifierProvider<TwoFactorController, AuthFormState> {
  /// Second factor (02.3, 02.4): a 6-digit TOTP code or a recovery code against the pending
  /// challenge. `two_factor_invalid` is shown under the field; `two_factor_expired` (or the
  /// challenge's own expiry) ends the attempt and sends the customer back to sign in.
  TwoFactorControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'twoFactorControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$twoFactorControllerHash();

  @$internal
  @override
  TwoFactorController create() => TwoFactorController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AuthFormState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AuthFormState>(value),
    );
  }
}

String _$twoFactorControllerHash() =>
    r'0cae2bcbca28cedcd454a732fafe118da9b82ef9';

/// Second factor (02.3, 02.4): a 6-digit TOTP code or a recovery code against the pending
/// challenge. `two_factor_invalid` is shown under the field; `two_factor_expired` (or the
/// challenge's own expiry) ends the attempt and sends the customer back to sign in.

abstract class _$TwoFactorController extends $Notifier<AuthFormState> {
  AuthFormState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<AuthFormState, AuthFormState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<AuthFormState, AuthFormState>,
              AuthFormState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
