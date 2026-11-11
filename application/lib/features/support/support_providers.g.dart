// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'support_providers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(supportRemoteDataSource)
final supportRemoteDataSourceProvider = SupportRemoteDataSourceProvider._();

final class SupportRemoteDataSourceProvider
    extends
        $FunctionalProvider<
          SupportRemoteDataSource,
          SupportRemoteDataSource,
          SupportRemoteDataSource
        >
    with $Provider<SupportRemoteDataSource> {
  SupportRemoteDataSourceProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'supportRemoteDataSourceProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$supportRemoteDataSourceHash();

  @$internal
  @override
  $ProviderElement<SupportRemoteDataSource> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  SupportRemoteDataSource create(Ref ref) {
    return supportRemoteDataSource(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(SupportRemoteDataSource value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<SupportRemoteDataSource>(value),
    );
  }
}

String _$supportRemoteDataSourceHash() =>
    r'0db3215ca8c074c326d160cde5e0c70069097bc6';

@ProviderFor(supportRepository)
final supportRepositoryProvider = SupportRepositoryProvider._();

final class SupportRepositoryProvider
    extends
        $FunctionalProvider<
          SupportRepository,
          SupportRepository,
          SupportRepository
        >
    with $Provider<SupportRepository> {
  SupportRepositoryProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'supportRepositoryProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$supportRepositoryHash();

  @$internal
  @override
  $ProviderElement<SupportRepository> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  SupportRepository create(Ref ref) {
    return supportRepository(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(SupportRepository value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<SupportRepository>(value),
    );
  }
}

String _$supportRepositoryHash() => r'f6a7dc76292ef1c8d1ebfba9d00dd1f9cd474a8c';

/// `features.support_enabled` of `GET /app/config`. Off: the support screens say support is not
/// available in the app and "Get help" opens the web help page. Before the launch check has a
/// configuration support is shown.

@ProviderFor(supportEnabled)
final supportEnabledProvider = SupportEnabledProvider._();

/// `features.support_enabled` of `GET /app/config`. Off: the support screens say support is not
/// available in the app and "Get help" opens the web help page. Before the launch check has a
/// configuration support is shown.

final class SupportEnabledProvider extends $FunctionalProvider<bool, bool, bool>
    with $Provider<bool> {
  /// `features.support_enabled` of `GET /app/config`. Off: the support screens say support is not
  /// available in the app and "Get help" opens the web help page. Before the launch check has a
  /// configuration support is shown.
  SupportEnabledProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'supportEnabledProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$supportEnabledHash();

  @$internal
  @override
  $ProviderElement<bool> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  bool create(Ref ref) {
    return supportEnabled(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(bool value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<bool>(value),
    );
  }
}

String _$supportEnabledHash() => r'cf28e392619954ee3d7b2416e5cea342e01af4e2';
