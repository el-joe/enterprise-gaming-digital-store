// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'code_reveal_providers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(codeRevealRemoteDataSource)
final codeRevealRemoteDataSourceProvider =
    CodeRevealRemoteDataSourceProvider._();

final class CodeRevealRemoteDataSourceProvider
    extends
        $FunctionalProvider<
          CodeRevealRemoteDataSource,
          CodeRevealRemoteDataSource,
          CodeRevealRemoteDataSource
        >
    with $Provider<CodeRevealRemoteDataSource> {
  CodeRevealRemoteDataSourceProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'codeRevealRemoteDataSourceProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$codeRevealRemoteDataSourceHash();

  @$internal
  @override
  $ProviderElement<CodeRevealRemoteDataSource> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  CodeRevealRemoteDataSource create(Ref ref) {
    return codeRevealRemoteDataSource(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(CodeRevealRemoteDataSource value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<CodeRevealRemoteDataSource>(value),
    );
  }
}

String _$codeRevealRemoteDataSourceHash() =>
    r'c7a8e96c80c29e2acc0b02d0242ba19459eb6017';

@ProviderFor(codeRevealRepository)
final codeRevealRepositoryProvider = CodeRevealRepositoryProvider._();

final class CodeRevealRepositoryProvider
    extends
        $FunctionalProvider<
          CodeRevealRepository,
          CodeRevealRepository,
          CodeRevealRepository
        >
    with $Provider<CodeRevealRepository> {
  CodeRevealRepositoryProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'codeRevealRepositoryProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$codeRevealRepositoryHash();

  @$internal
  @override
  $ProviderElement<CodeRevealRepository> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  CodeRevealRepository create(Ref ref) {
    return codeRevealRepository(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(CodeRevealRepository value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<CodeRevealRepository>(value),
    );
  }
}

String _$codeRevealRepositoryHash() =>
    r'1a7501008d4ae7ad0106a4545be9d3fa6d20af30';

@ProviderFor(revealCode)
final revealCodeProvider = RevealCodeProvider._();

final class RevealCodeProvider
    extends $FunctionalProvider<RevealCode, RevealCode, RevealCode>
    with $Provider<RevealCode> {
  RevealCodeProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'revealCodeProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$revealCodeHash();

  @$internal
  @override
  $ProviderElement<RevealCode> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  RevealCode create(Ref ref) {
    return revealCode(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(RevealCode value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<RevealCode>(value),
    );
  }
}

String _$revealCodeHash() => r'e534042afe013907e2705557ac28888bbbceb81e';
