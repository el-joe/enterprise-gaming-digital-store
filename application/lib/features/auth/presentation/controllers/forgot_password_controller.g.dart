// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'forgot_password_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Forgot password (02.8): the app has no stateless reset endpoint, so it opens the web page
/// `links.forgot_password` from `GET /app/config` in the system browser.

@ProviderFor(ForgotPasswordController)
final forgotPasswordControllerProvider = ForgotPasswordControllerProvider._();

/// Forgot password (02.8): the app has no stateless reset endpoint, so it opens the web page
/// `links.forgot_password` from `GET /app/config` in the system browser.
final class ForgotPasswordControllerProvider
    extends $NotifierProvider<ForgotPasswordController, ForgotPasswordStatus> {
  /// Forgot password (02.8): the app has no stateless reset endpoint, so it opens the web page
  /// `links.forgot_password` from `GET /app/config` in the system browser.
  ForgotPasswordControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'forgotPasswordControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$forgotPasswordControllerHash();

  @$internal
  @override
  ForgotPasswordController create() => ForgotPasswordController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ForgotPasswordStatus value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ForgotPasswordStatus>(value),
    );
  }
}

String _$forgotPasswordControllerHash() =>
    r'b7acdcab7ed30b67d2a8d8cf8fe5f9c1bde5de7f';

/// Forgot password (02.8): the app has no stateless reset endpoint, so it opens the web page
/// `links.forgot_password` from `GET /app/config` in the system browser.

abstract class _$ForgotPasswordController
    extends $Notifier<ForgotPasswordStatus> {
  ForgotPasswordStatus build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<ForgotPasswordStatus, ForgotPasswordStatus>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<ForgotPasswordStatus, ForgotPasswordStatus>,
              ForgotPasswordStatus,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
