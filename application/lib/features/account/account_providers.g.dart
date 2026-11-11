// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'account_providers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(accountRemoteDataSource)
final accountRemoteDataSourceProvider = AccountRemoteDataSourceProvider._();

final class AccountRemoteDataSourceProvider
    extends
        $FunctionalProvider<
          AccountRemoteDataSource,
          AccountRemoteDataSource,
          AccountRemoteDataSource
        >
    with $Provider<AccountRemoteDataSource> {
  AccountRemoteDataSourceProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'accountRemoteDataSourceProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$accountRemoteDataSourceHash();

  @$internal
  @override
  $ProviderElement<AccountRemoteDataSource> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  AccountRemoteDataSource create(Ref ref) {
    return accountRemoteDataSource(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AccountRemoteDataSource value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AccountRemoteDataSource>(value),
    );
  }
}

String _$accountRemoteDataSourceHash() =>
    r'862c5f5f4e2f65671508c74acf47fa17e9d9bf41';

@ProviderFor(accountRepository)
final accountRepositoryProvider = AccountRepositoryProvider._();

final class AccountRepositoryProvider
    extends
        $FunctionalProvider<
          AccountRepository,
          AccountRepository,
          AccountRepository
        >
    with $Provider<AccountRepository> {
  AccountRepositoryProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'accountRepositoryProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$accountRepositoryHash();

  @$internal
  @override
  $ProviderElement<AccountRepository> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  AccountRepository create(Ref ref) {
    return accountRepository(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AccountRepository value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AccountRepository>(value),
    );
  }
}

String _$accountRepositoryHash() => r'8d84815f49173b78281da2be3b5e50c5899d1eff';

/// Kept alive: its callbacks run after the profile call returns (an auto-disposed provider would
/// be gone by then, M-A16 fix).

@ProviderFor(updateRegionalSettings)
final updateRegionalSettingsProvider = UpdateRegionalSettingsProvider._();

/// Kept alive: its callbacks run after the profile call returns (an auto-disposed provider would
/// be gone by then, M-A16 fix).

final class UpdateRegionalSettingsProvider
    extends
        $FunctionalProvider<
          UpdateRegionalSettings,
          UpdateRegionalSettings,
          UpdateRegionalSettings
        >
    with $Provider<UpdateRegionalSettings> {
  /// Kept alive: its callbacks run after the profile call returns (an auto-disposed provider would
  /// be gone by then, M-A16 fix).
  UpdateRegionalSettingsProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'updateRegionalSettingsProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$updateRegionalSettingsHash();

  @$internal
  @override
  $ProviderElement<UpdateRegionalSettings> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  UpdateRegionalSettings create(Ref ref) {
    return updateRegionalSettings(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(UpdateRegionalSettings value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<UpdateRegionalSettings>(value),
    );
  }
}

String _$updateRegionalSettingsHash() =>
    r'363254ad479c6b976434344f5a42c251864e70a4';
