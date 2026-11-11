// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'wallet_providers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(walletRemoteDataSource)
final walletRemoteDataSourceProvider = WalletRemoteDataSourceProvider._();

final class WalletRemoteDataSourceProvider
    extends
        $FunctionalProvider<
          WalletRemoteDataSource,
          WalletRemoteDataSource,
          WalletRemoteDataSource
        >
    with $Provider<WalletRemoteDataSource> {
  WalletRemoteDataSourceProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'walletRemoteDataSourceProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$walletRemoteDataSourceHash();

  @$internal
  @override
  $ProviderElement<WalletRemoteDataSource> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  WalletRemoteDataSource create(Ref ref) {
    return walletRemoteDataSource(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(WalletRemoteDataSource value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<WalletRemoteDataSource>(value),
    );
  }
}

String _$walletRemoteDataSourceHash() =>
    r'd5018cdb3a78b0c6e580d3575dae2e13a8a27587';

@ProviderFor(walletRepository)
final walletRepositoryProvider = WalletRepositoryProvider._();

final class WalletRepositoryProvider
    extends
        $FunctionalProvider<
          WalletRepository,
          WalletRepository,
          WalletRepository
        >
    with $Provider<WalletRepository> {
  WalletRepositoryProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'walletRepositoryProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$walletRepositoryHash();

  @$internal
  @override
  $ProviderElement<WalletRepository> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  WalletRepository create(Ref ref) {
    return walletRepository(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(WalletRepository value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<WalletRepository>(value),
    );
  }
}

String _$walletRepositoryHash() => r'149a52f4578e1e4694f8a1fa3cda9c75afd5ce2f';

/// `features.wallet_enabled` of `GET /app/config` (M-A15). Off: the Account tab hides the wallet
/// entry, the wallet screen shows "not available" and loads nothing, and checkout drops the
/// wallet option. Before the launch check has a configuration the wallet is shown (the server
/// still decides whether the wallet can pay).

@ProviderFor(walletEnabled)
final walletEnabledProvider = WalletEnabledProvider._();

/// `features.wallet_enabled` of `GET /app/config` (M-A15). Off: the Account tab hides the wallet
/// entry, the wallet screen shows "not available" and loads nothing, and checkout drops the
/// wallet option. Before the launch check has a configuration the wallet is shown (the server
/// still decides whether the wallet can pay).

final class WalletEnabledProvider extends $FunctionalProvider<bool, bool, bool>
    with $Provider<bool> {
  /// `features.wallet_enabled` of `GET /app/config` (M-A15). Off: the Account tab hides the wallet
  /// entry, the wallet screen shows "not available" and loads nothing, and checkout drops the
  /// wallet option. Before the launch check has a configuration the wallet is shown (the server
  /// still decides whether the wallet can pay).
  WalletEnabledProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'walletEnabledProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$walletEnabledHash();

  @$internal
  @override
  $ProviderElement<bool> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  bool create(Ref ref) {
    return walletEnabled(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(bool value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<bool>(value),
    );
  }
}

String _$walletEnabledHash() => r'3d349a2454d3d5fd4abe2b84751a83d9307dd7dd';
