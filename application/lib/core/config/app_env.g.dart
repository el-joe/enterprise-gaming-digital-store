// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'app_env.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The running environment. Overridden once in `bootstrap.dart`.

@ProviderFor(appEnv)
final appEnvProvider = AppEnvProvider._();

/// The running environment. Overridden once in `bootstrap.dart`.

final class AppEnvProvider extends $FunctionalProvider<AppEnv, AppEnv, AppEnv>
    with $Provider<AppEnv> {
  /// The running environment. Overridden once in `bootstrap.dart`.
  AppEnvProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'appEnvProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$appEnvHash();

  @$internal
  @override
  $ProviderElement<AppEnv> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  AppEnv create(Ref ref) {
    return appEnv(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AppEnv value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AppEnv>(value),
    );
  }
}

String _$appEnvHash() => r'fd038e9ffc16e19485f2525a94fc15e9a3bab650';
