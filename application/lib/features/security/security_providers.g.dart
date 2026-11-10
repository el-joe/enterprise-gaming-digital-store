// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'security_providers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(securityRemoteDataSource)
final securityRemoteDataSourceProvider = SecurityRemoteDataSourceProvider._();

final class SecurityRemoteDataSourceProvider
    extends
        $FunctionalProvider<
          SecurityRemoteDataSource,
          SecurityRemoteDataSource,
          SecurityRemoteDataSource
        >
    with $Provider<SecurityRemoteDataSource> {
  SecurityRemoteDataSourceProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'securityRemoteDataSourceProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$securityRemoteDataSourceHash();

  @$internal
  @override
  $ProviderElement<SecurityRemoteDataSource> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  SecurityRemoteDataSource create(Ref ref) {
    return securityRemoteDataSource(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(SecurityRemoteDataSource value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<SecurityRemoteDataSource>(value),
    );
  }
}

String _$securityRemoteDataSourceHash() =>
    r'43d3ffeac8586bdb95b0fe4770df73d5c83fd3ea';

@ProviderFor(securityRepository)
final securityRepositoryProvider = SecurityRepositoryProvider._();

final class SecurityRepositoryProvider
    extends
        $FunctionalProvider<
          SecurityRepository,
          SecurityRepository,
          SecurityRepository
        >
    with $Provider<SecurityRepository> {
  SecurityRepositoryProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'securityRepositoryProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$securityRepositoryHash();

  @$internal
  @override
  $ProviderElement<SecurityRepository> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  SecurityRepository create(Ref ref) {
    return securityRepository(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(SecurityRepository value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<SecurityRepository>(value),
    );
  }
}

String _$securityRepositoryHash() =>
    r'26228e55fc89a3a05e4353a418f51db7bbffb4c8';
