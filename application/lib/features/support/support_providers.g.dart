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
