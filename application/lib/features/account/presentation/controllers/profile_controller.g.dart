// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'profile_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Profile (17.2): name, phone and country are edited here (`PATCH /account/profile`, only the
/// keys that changed); the email cannot be changed in the app (no verified change flow), but an
/// unverified address can get its verification email again (60 s cooldown or `Retry-After`).
/// Language and display currency use the shared settings flows.

@ProviderFor(ProfileFormController)
final profileFormControllerProvider = ProfileFormControllerProvider._();

/// Profile (17.2): name, phone and country are edited here (`PATCH /account/profile`, only the
/// keys that changed); the email cannot be changed in the app (no verified change flow), but an
/// unverified address can get its verification email again (60 s cooldown or `Retry-After`).
/// Language and display currency use the shared settings flows.
final class ProfileFormControllerProvider
    extends $NotifierProvider<ProfileFormController, ProfileFormState> {
  /// Profile (17.2): name, phone and country are edited here (`PATCH /account/profile`, only the
  /// keys that changed); the email cannot be changed in the app (no verified change flow), but an
  /// unverified address can get its verification email again (60 s cooldown or `Retry-After`).
  /// Language and display currency use the shared settings flows.
  ProfileFormControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'profileFormControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$profileFormControllerHash();

  @$internal
  @override
  ProfileFormController create() => ProfileFormController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ProfileFormState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ProfileFormState>(value),
    );
  }
}

String _$profileFormControllerHash() =>
    r'a2e19010468b5c9b8e45b7cf984df2f28f7efc5a';

/// Profile (17.2): name, phone and country are edited here (`PATCH /account/profile`, only the
/// keys that changed); the email cannot be changed in the app (no verified change flow), but an
/// unverified address can get its verification email again (60 s cooldown or `Retry-After`).
/// Language and display currency use the shared settings flows.

abstract class _$ProfileFormController extends $Notifier<ProfileFormState> {
  ProfileFormState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<ProfileFormState, ProfileFormState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<ProfileFormState, ProfileFormState>,
              ProfileFormState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
