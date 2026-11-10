// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'payments_providers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(paymentsRemoteDataSource)
final paymentsRemoteDataSourceProvider = PaymentsRemoteDataSourceProvider._();

final class PaymentsRemoteDataSourceProvider
    extends
        $FunctionalProvider<
          PaymentsRemoteDataSource,
          PaymentsRemoteDataSource,
          PaymentsRemoteDataSource
        >
    with $Provider<PaymentsRemoteDataSource> {
  PaymentsRemoteDataSourceProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'paymentsRemoteDataSourceProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$paymentsRemoteDataSourceHash();

  @$internal
  @override
  $ProviderElement<PaymentsRemoteDataSource> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  PaymentsRemoteDataSource create(Ref ref) {
    return paymentsRemoteDataSource(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(PaymentsRemoteDataSource value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<PaymentsRemoteDataSource>(value),
    );
  }
}

String _$paymentsRemoteDataSourceHash() =>
    r'94d5f100c8793814a4f25024161f80cccfdd558c';

@ProviderFor(paymentsRepository)
final paymentsRepositoryProvider = PaymentsRepositoryProvider._();

final class PaymentsRepositoryProvider
    extends
        $FunctionalProvider<
          PaymentsRepository,
          PaymentsRepository,
          PaymentsRepository
        >
    with $Provider<PaymentsRepository> {
  PaymentsRepositoryProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'paymentsRepositoryProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$paymentsRepositoryHash();

  @$internal
  @override
  $ProviderElement<PaymentsRepository> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  PaymentsRepository create(Ref ref) {
    return paymentsRepository(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(PaymentsRepository value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<PaymentsRepository>(value),
    );
  }
}

String _$paymentsRepositoryHash() =>
    r'186b5fbc61fcec863ff4a6530103ec1dc622a7cb';

@ProviderFor(resolvePayment)
final resolvePaymentProvider = ResolvePaymentProvider._();

final class ResolvePaymentProvider
    extends $FunctionalProvider<ResolvePayment, ResolvePayment, ResolvePayment>
    with $Provider<ResolvePayment> {
  ResolvePaymentProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'resolvePaymentProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$resolvePaymentHash();

  @$internal
  @override
  $ProviderElement<ResolvePayment> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  ResolvePayment create(Ref ref) {
    return resolvePayment(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ResolvePayment value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ResolvePayment>(value),
    );
  }
}

String _$resolvePaymentHash() => r'be9179ceaea4a77b24c5c152ae999d2a371b6c55';
