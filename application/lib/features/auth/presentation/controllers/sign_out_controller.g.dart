// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'sign_out_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Sign out (17.4): revokes this device's sign-in (`POST /mobile/auth/logout`), wipes secure
/// storage (also when the call fails), deletes the local biometric key (the server key died with
/// the sign-in) and clears the in-memory account state. State: busy.

@ProviderFor(SignOutController)
final signOutControllerProvider = SignOutControllerProvider._();

/// Sign out (17.4): revokes this device's sign-in (`POST /mobile/auth/logout`), wipes secure
/// storage (also when the call fails), deletes the local biometric key (the server key died with
/// the sign-in) and clears the in-memory account state. State: busy.
final class SignOutControllerProvider
    extends $NotifierProvider<SignOutController, bool> {
  /// Sign out (17.4): revokes this device's sign-in (`POST /mobile/auth/logout`), wipes secure
  /// storage (also when the call fails), deletes the local biometric key (the server key died with
  /// the sign-in) and clears the in-memory account state. State: busy.
  SignOutControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'signOutControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$signOutControllerHash();

  @$internal
  @override
  SignOutController create() => SignOutController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(bool value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<bool>(value),
    );
  }
}

String _$signOutControllerHash() => r'b351567c096f8023925be73cd89c474679f9c3d2';

/// Sign out (17.4): revokes this device's sign-in (`POST /mobile/auth/logout`), wipes secure
/// storage (also when the call fails), deletes the local biometric key (the server key died with
/// the sign-in) and clears the in-memory account state. State: busy.

abstract class _$SignOutController extends $Notifier<bool> {
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
