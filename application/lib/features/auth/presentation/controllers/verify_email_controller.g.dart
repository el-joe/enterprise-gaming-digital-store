// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'verify_email_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Verify-email prompt (02.7): resend through `POST /mobile/account/email/verification-notification`
/// with a 60 s cooldown (or the server's `Retry-After`), and "I've verified" re-reads the user.

@ProviderFor(VerifyEmailController)
final verifyEmailControllerProvider = VerifyEmailControllerProvider._();

/// Verify-email prompt (02.7): resend through `POST /mobile/account/email/verification-notification`
/// with a 60 s cooldown (or the server's `Retry-After`), and "I've verified" re-reads the user.
final class VerifyEmailControllerProvider
    extends $NotifierProvider<VerifyEmailController, VerifyEmailState> {
  /// Verify-email prompt (02.7): resend through `POST /mobile/account/email/verification-notification`
  /// with a 60 s cooldown (or the server's `Retry-After`), and "I've verified" re-reads the user.
  VerifyEmailControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'verifyEmailControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$verifyEmailControllerHash();

  @$internal
  @override
  VerifyEmailController create() => VerifyEmailController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(VerifyEmailState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<VerifyEmailState>(value),
    );
  }
}

String _$verifyEmailControllerHash() =>
    r'5f23c9873846aa04a92474a50a926757445b500a';

/// Verify-email prompt (02.7): resend through `POST /mobile/account/email/verification-notification`
/// with a 60 s cooldown (or the server's `Retry-After`), and "I've verified" re-reads the user.

abstract class _$VerifyEmailController extends $Notifier<VerifyEmailState> {
  VerifyEmailState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<VerifyEmailState, VerifyEmailState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<VerifyEmailState, VerifyEmailState>,
              VerifyEmailState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
