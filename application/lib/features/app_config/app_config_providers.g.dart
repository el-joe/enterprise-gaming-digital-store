// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'app_config_providers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(appConfigRemoteDataSource)
final appConfigRemoteDataSourceProvider = AppConfigRemoteDataSourceProvider._();

final class AppConfigRemoteDataSourceProvider
    extends
        $FunctionalProvider<
          AppConfigRemoteDataSource,
          AppConfigRemoteDataSource,
          AppConfigRemoteDataSource
        >
    with $Provider<AppConfigRemoteDataSource> {
  AppConfigRemoteDataSourceProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'appConfigRemoteDataSourceProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$appConfigRemoteDataSourceHash();

  @$internal
  @override
  $ProviderElement<AppConfigRemoteDataSource> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  AppConfigRemoteDataSource create(Ref ref) {
    return appConfigRemoteDataSource(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AppConfigRemoteDataSource value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AppConfigRemoteDataSource>(value),
    );
  }
}

String _$appConfigRemoteDataSourceHash() =>
    r'bd6a33987caa69954ddf7ec63a2037608cddee21';

@ProviderFor(appConfigRepository)
final appConfigRepositoryProvider = AppConfigRepositoryProvider._();

final class AppConfigRepositoryProvider
    extends
        $FunctionalProvider<
          AppConfigRepository,
          AppConfigRepository,
          AppConfigRepository
        >
    with $Provider<AppConfigRepository> {
  AppConfigRepositoryProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'appConfigRepositoryProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$appConfigRepositoryHash();

  @$internal
  @override
  $ProviderElement<AppConfigRepository> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  AppConfigRepository create(Ref ref) {
    return appConfigRepository(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AppConfigRepository value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AppConfigRepository>(value),
    );
  }
}

String _$appConfigRepositoryHash() =>
    r'8a23caff9e4845be0d9d005d72a5d7e87d7e218a';

/// The last configuration the launch check received (null before it). Other features read links
/// and currencies from here (for example the forgot-password page and onboarding).

@ProviderFor(LatestAppConfig)
final latestAppConfigProvider = LatestAppConfigProvider._();

/// The last configuration the launch check received (null before it). Other features read links
/// and currencies from here (for example the forgot-password page and onboarding).
final class LatestAppConfigProvider
    extends $NotifierProvider<LatestAppConfig, AppConfig?> {
  /// The last configuration the launch check received (null before it). Other features read links
  /// and currencies from here (for example the forgot-password page and onboarding).
  LatestAppConfigProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'latestAppConfigProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$latestAppConfigHash();

  @$internal
  @override
  LatestAppConfig create() => LatestAppConfig();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AppConfig? value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AppConfig?>(value),
    );
  }
}

String _$latestAppConfigHash() => r'4202a164a01f90a7ae97fa1734cf3b47d466b3c2';

/// The last configuration the launch check received (null before it). Other features read links
/// and currencies from here (for example the forgot-password page and onboarding).

abstract class _$LatestAppConfig extends $Notifier<AppConfig?> {
  AppConfig? build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<AppConfig?, AppConfig?>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<AppConfig?, AppConfig?>,
              AppConfig?,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
