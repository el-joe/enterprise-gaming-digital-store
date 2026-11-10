// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'dio_client.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Refresh-token exchange. The mobile refresh endpoint does not exist yet (gap G1, M-A2), so the
/// foundation refuses every refresh: a 401 signs the user out. M-A7 overrides this provider with
/// the data source call to `POST /api/v1/mobile/auth/refresh`.

@ProviderFor(tokenRefresher)
final tokenRefresherProvider = TokenRefresherProvider._();

/// Refresh-token exchange. The mobile refresh endpoint does not exist yet (gap G1, M-A2), so the
/// foundation refuses every refresh: a 401 signs the user out. M-A7 overrides this provider with
/// the data source call to `POST /api/v1/mobile/auth/refresh`.

final class TokenRefresherProvider
    extends $FunctionalProvider<TokenRefresher, TokenRefresher, TokenRefresher>
    with $Provider<TokenRefresher> {
  /// Refresh-token exchange. The mobile refresh endpoint does not exist yet (gap G1, M-A2), so the
  /// foundation refuses every refresh: a 401 signs the user out. M-A7 overrides this provider with
  /// the data source call to `POST /api/v1/mobile/auth/refresh`.
  TokenRefresherProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'tokenRefresherProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$tokenRefresherHash();

  @$internal
  @override
  $ProviderElement<TokenRefresher> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  TokenRefresher create(Ref ref) {
    return tokenRefresher(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(TokenRefresher value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<TokenRefresher>(value),
    );
  }
}

String _$tokenRefresherHash() => r'a217b058ca1ce22a82d47920e83c888e496dce7c';

@ProviderFor(apiClient)
final apiClientProvider = ApiClientProvider._();

final class ApiClientProvider extends $FunctionalProvider<Dio, Dio, Dio>
    with $Provider<Dio> {
  ApiClientProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'apiClientProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$apiClientHash();

  @$internal
  @override
  $ProviderElement<Dio> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  Dio create(Ref ref) {
    return apiClient(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(Dio value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<Dio>(value),
    );
  }
}

String _$apiClientHash() => r'3422090399ad13a0b04220553037f0ec20c6ea5b';
