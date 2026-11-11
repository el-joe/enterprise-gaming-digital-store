// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'auth_launch.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The auth feature's launch tasks, run once the app config check passed (the launch feature
/// calls this; it is the auth feature's only public entry besides its routes): reload the
/// signed-in user (email verification for the guards), decide whether the biometric offer is due,
/// and lock a signed-in session that has biometric unlock on.

@ProviderFor(AuthLaunch)
final authLaunchProvider = AuthLaunchProvider._();

/// The auth feature's launch tasks, run once the app config check passed (the launch feature
/// calls this; it is the auth feature's only public entry besides its routes): reload the
/// signed-in user (email verification for the guards), decide whether the biometric offer is due,
/// and lock a signed-in session that has biometric unlock on.
final class AuthLaunchProvider extends $NotifierProvider<AuthLaunch, void> {
  /// The auth feature's launch tasks, run once the app config check passed (the launch feature
  /// calls this; it is the auth feature's only public entry besides its routes): reload the
  /// signed-in user (email verification for the guards), decide whether the biometric offer is due,
  /// and lock a signed-in session that has biometric unlock on.
  AuthLaunchProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'authLaunchProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$authLaunchHash();

  @$internal
  @override
  AuthLaunch create() => AuthLaunch();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(void value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<void>(value),
    );
  }
}

String _$authLaunchHash() => r'5feaadf6e379fd4722793a68b838e6dc891acfa7';

/// The auth feature's launch tasks, run once the app config check passed (the launch feature
/// calls this; it is the auth feature's only public entry besides its routes): reload the
/// signed-in user (email verification for the guards), decide whether the biometric offer is due,
/// and lock a signed-in session that has biometric unlock on.

abstract class _$AuthLaunch extends $Notifier<void> {
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
