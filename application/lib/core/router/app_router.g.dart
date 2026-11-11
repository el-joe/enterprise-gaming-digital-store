// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'app_router.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The app's go_router. Guard inputs are providers; any change re-runs [redirectFor]. The app
/// starts on the splash (app config check), which continues to onboarding or home.

@ProviderFor(appRouter)
final appRouterProvider = AppRouterProvider._();

/// The app's go_router. Guard inputs are providers; any change re-runs [redirectFor]. The app
/// starts on the splash (app config check), which continues to onboarding or home.

final class AppRouterProvider
    extends $FunctionalProvider<GoRouter, GoRouter, GoRouter>
    with $Provider<GoRouter> {
  /// The app's go_router. Guard inputs are providers; any change re-runs [redirectFor]. The app
  /// starts on the splash (app config check), which continues to onboarding or home.
  AppRouterProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'appRouterProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$appRouterHash();

  @$internal
  @override
  $ProviderElement<GoRouter> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  GoRouter create(Ref ref) {
    return appRouter(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(GoRouter value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<GoRouter>(value),
    );
  }
}

String _$appRouterHash() => r'49c77cb1a71306ae8002a21fad6029c83f0708f3';
