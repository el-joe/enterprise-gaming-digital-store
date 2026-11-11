// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'login_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The pending two-factor challenge of a password sign-in. Memory only: it lives 300 s and is
/// single use (API_DOCUMENTATION.md 1b).

@ProviderFor(PendingTwoFactor)
final pendingTwoFactorProvider = PendingTwoFactorProvider._();

/// The pending two-factor challenge of a password sign-in. Memory only: it lives 300 s and is
/// single use (API_DOCUMENTATION.md 1b).
final class PendingTwoFactorProvider
    extends $NotifierProvider<PendingTwoFactor, TwoFactorChallenge?> {
  /// The pending two-factor challenge of a password sign-in. Memory only: it lives 300 s and is
  /// single use (API_DOCUMENTATION.md 1b).
  PendingTwoFactorProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'pendingTwoFactorProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$pendingTwoFactorHash();

  @$internal
  @override
  PendingTwoFactor create() => PendingTwoFactor();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(TwoFactorChallenge? value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<TwoFactorChallenge?>(value),
    );
  }
}

String _$pendingTwoFactorHash() => r'e75aa500a785cd82f4b4be51f98fc93222d9f428';

/// The pending two-factor challenge of a password sign-in. Memory only: it lives 300 s and is
/// single use (API_DOCUMENTATION.md 1b).

abstract class _$PendingTwoFactor extends $Notifier<TwoFactorChallenge?> {
  TwoFactorChallenge? build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<TwoFactorChallenge?, TwoFactorChallenge?>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<TwoFactorChallenge?, TwoFactorChallenge?>,
              TwoFactorChallenge?,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// Sign-in form (02.1, 02.2): local checks, `POST /mobile/auth/login`, server field errors beside
/// the fields and the 429 countdown.

@ProviderFor(LoginController)
final loginControllerProvider = LoginControllerProvider._();

/// Sign-in form (02.1, 02.2): local checks, `POST /mobile/auth/login`, server field errors beside
/// the fields and the 429 countdown.
final class LoginControllerProvider
    extends $NotifierProvider<LoginController, AuthFormState> {
  /// Sign-in form (02.1, 02.2): local checks, `POST /mobile/auth/login`, server field errors beside
  /// the fields and the 429 countdown.
  LoginControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'loginControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$loginControllerHash();

  @$internal
  @override
  LoginController create() => LoginController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AuthFormState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AuthFormState>(value),
    );
  }
}

String _$loginControllerHash() => r'76e321babe911614ef97e8e58d0138526fce7cf6';

/// Sign-in form (02.1, 02.2): local checks, `POST /mobile/auth/login`, server field errors beside
/// the fields and the 429 countdown.

abstract class _$LoginController extends $Notifier<AuthFormState> {
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
