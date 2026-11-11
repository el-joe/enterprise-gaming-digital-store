// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'session_user.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The signed-in customer (`UserResource`), in memory only. Null until a sign-in or `me`.

@ProviderFor(CurrentUser)
final currentUserProvider = CurrentUserProvider._();

/// The signed-in customer (`UserResource`), in memory only. Null until a sign-in or `me`.
final class CurrentUserProvider
    extends $NotifierProvider<CurrentUser, AuthUser?> {
  /// The signed-in customer (`UserResource`), in memory only. Null until a sign-in or `me`.
  CurrentUserProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'currentUserProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$currentUserHash();

  @$internal
  @override
  CurrentUser create() => CurrentUser();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AuthUser? value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AuthUser?>(value),
    );
  }
}

String _$currentUserHash() => r'fef70cc735429fbafdfbd51ee790c05599c93f2c';

/// The signed-in customer (`UserResource`), in memory only. Null until a sign-in or `me`.

abstract class _$CurrentUser extends $Notifier<AuthUser?> {
  AuthUser? build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<AuthUser?, AuthUser?>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<AuthUser?, AuthUser?>,
              AuthUser?,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
