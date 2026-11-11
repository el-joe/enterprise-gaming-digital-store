// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'register_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Create account (02.5, 02.6): local checks, `POST /mobile/auth/register`, the 422 field errors
/// beside each field with a summary banner. On success the customer is signed in and the guards
/// open the verify-email prompt.

@ProviderFor(RegisterController)
final registerControllerProvider = RegisterControllerProvider._();

/// Create account (02.5, 02.6): local checks, `POST /mobile/auth/register`, the 422 field errors
/// beside each field with a summary banner. On success the customer is signed in and the guards
/// open the verify-email prompt.
final class RegisterControllerProvider
    extends $NotifierProvider<RegisterController, AuthFormState> {
  /// Create account (02.5, 02.6): local checks, `POST /mobile/auth/register`, the 422 field errors
  /// beside each field with a summary banner. On success the customer is signed in and the guards
  /// open the verify-email prompt.
  RegisterControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'registerControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$registerControllerHash();

  @$internal
  @override
  RegisterController create() => RegisterController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AuthFormState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AuthFormState>(value),
    );
  }
}

String _$registerControllerHash() =>
    r'0418f4d362f0813825f7e0c6385ab8af896da923';

/// Create account (02.5, 02.6): local checks, `POST /mobile/auth/register`, the 422 field errors
/// beside each field with a summary banner. On success the customer is signed in and the guards
/// open the verify-email prompt.

abstract class _$RegisterController extends $Notifier<AuthFormState> {
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
