// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'account_controllers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The signed-in customer's profile (`GET /account/profile`) for the Account hub (17.1),
/// Profile (17.2) and Security (18.1). Loaded when signed in, cleared on sign-out; a failed load
/// stays failed until [reload] (the hub falls back to the signed-in user).

@ProviderFor(AccountProfileController)
final accountProfileControllerProvider = AccountProfileControllerProvider._();

/// The signed-in customer's profile (`GET /account/profile`) for the Account hub (17.1),
/// Profile (17.2) and Security (18.1). Loaded when signed in, cleared on sign-out; a failed load
/// stays failed until [reload] (the hub falls back to the signed-in user).
final class AccountProfileControllerProvider
    extends $NotifierProvider<AccountProfileController, Loadable<Profile>?> {
  /// The signed-in customer's profile (`GET /account/profile`) for the Account hub (17.1),
  /// Profile (17.2) and Security (18.1). Loaded when signed in, cleared on sign-out; a failed load
  /// stays failed until [reload] (the hub falls back to the signed-in user).
  AccountProfileControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'accountProfileControllerProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$accountProfileControllerHash();

  @$internal
  @override
  AccountProfileController create() => AccountProfileController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(Loadable<Profile>? value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<Loadable<Profile>?>(value),
    );
  }
}

String _$accountProfileControllerHash() =>
    r'427cd67aaccd22b7665141e95cdbf6db40db8a40';

/// The signed-in customer's profile (`GET /account/profile`) for the Account hub (17.1),
/// Profile (17.2) and Security (18.1). Loaded when signed in, cleared on sign-out; a failed load
/// stays failed until [reload] (the hub falls back to the signed-in user).

abstract class _$AccountProfileController
    extends $Notifier<Loadable<Profile>?> {
  Loadable<Profile>? build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<Loadable<Profile>?, Loadable<Profile>?>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<Loadable<Profile>?, Loadable<Profile>?>,
              Loadable<Profile>?,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

@ProviderFor(accountHeader)
final accountHeaderProvider = AccountHeaderProvider._();

final class AccountHeaderProvider
    extends $FunctionalProvider<AccountHeader?, AccountHeader?, AccountHeader?>
    with $Provider<AccountHeader?> {
  AccountHeaderProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'accountHeaderProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$accountHeaderHash();

  @$internal
  @override
  $ProviderElement<AccountHeader?> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  AccountHeader? create(Ref ref) {
    return accountHeader(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AccountHeader? value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AccountHeader?>(value),
    );
  }
}

String _$accountHeaderHash() => r'7bd4cb7f8837c1c44ca016db64e06a3c59985a42';

/// The wallet balance on the hub's Wallet row (17.1), as the server's money text; null while
/// loading, on a failure, signed out or with `wallet_enabled` off (the row then shows a chevron).

@ProviderFor(accountWalletBalance)
final accountWalletBalanceProvider = AccountWalletBalanceProvider._();

/// The wallet balance on the hub's Wallet row (17.1), as the server's money text; null while
/// loading, on a failure, signed out or with `wallet_enabled` off (the row then shows a chevron).

final class AccountWalletBalanceProvider
    extends $FunctionalProvider<AsyncValue<String?>, String?, FutureOr<String?>>
    with $FutureModifier<String?>, $FutureProvider<String?> {
  /// The wallet balance on the hub's Wallet row (17.1), as the server's money text; null while
  /// loading, on a failure, signed out or with `wallet_enabled` off (the row then shows a chevron).
  AccountWalletBalanceProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'accountWalletBalanceProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$accountWalletBalanceHash();

  @$internal
  @override
  $FutureProviderElement<String?> $createElement($ProviderPointer pointer) =>
      $FutureProviderElement(pointer);

  @override
  FutureOr<String?> create(Ref ref) {
    return accountWalletBalance(ref);
  }
}

String _$accountWalletBalanceHash() =>
    r'453344b59c16241a111f9f5de9b39dfe4c69be09';

@ProviderFor(accountLinks)
final accountLinksProvider = AccountLinksProvider._();

final class AccountLinksProvider
    extends $FunctionalProvider<AccountLinks, AccountLinks, AccountLinks>
    with $Provider<AccountLinks> {
  AccountLinksProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'accountLinksProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$accountLinksHash();

  @$internal
  @override
  $ProviderElement<AccountLinks> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  AccountLinks create(Ref ref) {
    return accountLinks(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AccountLinks value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AccountLinks>(value),
    );
  }
}

String _$accountLinksHash() => r'209a18a7c93207737074d44803fd946000868105';

/// Opens an account link in the system browser (https only). Returns false when nothing opened.

@ProviderFor(AccountLinkOpener)
final accountLinkOpenerProvider = AccountLinkOpenerProvider._();

/// Opens an account link in the system browser (https only). Returns false when nothing opened.
final class AccountLinkOpenerProvider
    extends $NotifierProvider<AccountLinkOpener, void> {
  /// Opens an account link in the system browser (https only). Returns false when nothing opened.
  AccountLinkOpenerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'accountLinkOpenerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$accountLinkOpenerHash();

  @$internal
  @override
  AccountLinkOpener create() => AccountLinkOpener();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(void value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<void>(value),
    );
  }
}

String _$accountLinkOpenerHash() => r'e54dec812d7fb30200339e9b2cf04ff9e9551e35';

/// Opens an account link in the system browser (https only). Returns false when nothing opened.

abstract class _$AccountLinkOpener extends $Notifier<void> {
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

/// The language setting (17.2, 17.3; owner decision 1 keeps the display currency in the M-A11
/// currency sheet). Signed in, the choice is saved on the profile first (`PATCH /profile`
/// `locale`, through `UpdateRegionalSettings`) and applied only after the server accepted it;
/// signed out, it is saved on the device.

@ProviderFor(RegionalSettingsController)
final regionalSettingsControllerProvider =
    RegionalSettingsControllerProvider._();

/// The language setting (17.2, 17.3; owner decision 1 keeps the display currency in the M-A11
/// currency sheet). Signed in, the choice is saved on the profile first (`PATCH /profile`
/// `locale`, through `UpdateRegionalSettings`) and applied only after the server accepted it;
/// signed out, it is saved on the device.
final class RegionalSettingsControllerProvider
    extends
        $NotifierProvider<RegionalSettingsController, RegionalSettingsState> {
  /// The language setting (17.2, 17.3; owner decision 1 keeps the display currency in the M-A11
  /// currency sheet). Signed in, the choice is saved on the profile first (`PATCH /profile`
  /// `locale`, through `UpdateRegionalSettings`) and applied only after the server accepted it;
  /// signed out, it is saved on the device.
  RegionalSettingsControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'regionalSettingsControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$regionalSettingsControllerHash();

  @$internal
  @override
  RegionalSettingsController create() => RegionalSettingsController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(RegionalSettingsState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<RegionalSettingsState>(value),
    );
  }
}

String _$regionalSettingsControllerHash() =>
    r'7233bf7ae69fcadc00f222f1c89564c0726c8e48';

/// The language setting (17.2, 17.3; owner decision 1 keeps the display currency in the M-A11
/// currency sheet). Signed in, the choice is saved on the profile first (`PATCH /profile`
/// `locale`, through `UpdateRegionalSettings`) and applied only after the server accepted it;
/// signed out, it is saved on the device.

abstract class _$RegionalSettingsController
    extends $Notifier<RegionalSettingsState> {
  RegionalSettingsState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<RegionalSettingsState, RegionalSettingsState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<RegionalSettingsState, RegionalSettingsState>,
              RegionalSettingsState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
