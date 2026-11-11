// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'sign_out_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Sign out (17.4): removes this device's push token while the sign-in still works (M-A18;
/// the server also drops it with the sign-in), revokes this device's sign-in
/// (`POST /mobile/auth/logout`), wipes secure
/// storage (also when the call fails), deletes the local biometric key (the server key died with
/// the sign-in) and clears the in-memory account state. State: busy.

@ProviderFor(SignOutController)
final signOutControllerProvider = SignOutControllerProvider._();

/// Sign out (17.4): removes this device's push token while the sign-in still works (M-A18;
/// the server also drops it with the sign-in), revokes this device's sign-in
/// (`POST /mobile/auth/logout`), wipes secure
/// storage (also when the call fails), deletes the local biometric key (the server key died with
/// the sign-in) and clears the in-memory account state. State: busy.
final class SignOutControllerProvider
    extends $NotifierProvider<SignOutController, bool> {
  /// Sign out (17.4): removes this device's push token while the sign-in still works (M-A18;
  /// the server also drops it with the sign-in), revokes this device's sign-in
  /// (`POST /mobile/auth/logout`), wipes secure
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

String _$signOutControllerHash() => r'e7d461420f88c2bcaf10ee2974500876e6a2f432';

/// Sign out (17.4): removes this device's push token while the sign-in still works (M-A18;
/// the server also drops it with the sign-in), revokes this device's sign-in
/// (`POST /mobile/auth/logout`), wipes secure
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
