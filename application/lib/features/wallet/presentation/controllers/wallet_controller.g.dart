// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'wallet_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The wallet screen: `GET /account/wallet` and `GET /account/wallet/transactions` (newest
/// first, 20 per page, `meta` paging). Read only: there is no top-up (BD3). `wallet_enabled`
/// off loads nothing. A sign-in, a sign-out and [CartRevision] (an order was placed or a payment
/// answered, which can move the balance) reload it.

@ProviderFor(WalletController)
final walletControllerProvider = WalletControllerProvider._();

/// The wallet screen: `GET /account/wallet` and `GET /account/wallet/transactions` (newest
/// first, 20 per page, `meta` paging). Read only: there is no top-up (BD3). `wallet_enabled`
/// off loads nothing. A sign-in, a sign-out and [CartRevision] (an order was placed or a payment
/// answered, which can move the balance) reload it.
final class WalletControllerProvider
    extends $NotifierProvider<WalletController, WalletState> {
  /// The wallet screen: `GET /account/wallet` and `GET /account/wallet/transactions` (newest
  /// first, 20 per page, `meta` paging). Read only: there is no top-up (BD3). `wallet_enabled`
  /// off loads nothing. A sign-in, a sign-out and [CartRevision] (an order was placed or a payment
  /// answered, which can move the balance) reload it.
  WalletControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'walletControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$walletControllerHash();

  @$internal
  @override
  WalletController create() => WalletController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(WalletState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<WalletState>(value),
    );
  }
}

String _$walletControllerHash() => r'689bfb7429495f05cafc3c7740c0b583c623386e';

/// The wallet screen: `GET /account/wallet` and `GET /account/wallet/transactions` (newest
/// first, 20 per page, `meta` paging). Read only: there is no top-up (BD3). `wallet_enabled`
/// off loads nothing. A sign-in, a sign-out and [CartRevision] (an order was placed or a payment
/// answered, which can move the balance) reload it.

abstract class _$WalletController extends $Notifier<WalletState> {
  WalletState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<WalletState, WalletState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<WalletState, WalletState>,
              WalletState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// Whether a support link is configured (the frozen state's "Contact support").

@ProviderFor(walletSupportAvailable)
final walletSupportAvailableProvider = WalletSupportAvailableProvider._();

/// Whether a support link is configured (the frozen state's "Contact support").

final class WalletSupportAvailableProvider
    extends $FunctionalProvider<bool, bool, bool>
    with $Provider<bool> {
  /// Whether a support link is configured (the frozen state's "Contact support").
  WalletSupportAvailableProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'walletSupportAvailableProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$walletSupportAvailableHash();

  @$internal
  @override
  $ProviderElement<bool> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  bool create(Ref ref) {
    return walletSupportAvailable(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(bool value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<bool>(value),
    );
  }
}

String _$walletSupportAvailableHash() =>
    r'a1cbb44e0b22dc53ba575e11bb2cb7334834c2a1';
