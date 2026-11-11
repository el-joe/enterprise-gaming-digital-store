// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'auth_providers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning

@ProviderFor(authRemoteDataSource)
final authRemoteDataSourceProvider = AuthRemoteDataSourceProvider._();

final class AuthRemoteDataSourceProvider
    extends
        $FunctionalProvider<
          AuthRemoteDataSource,
          AuthRemoteDataSource,
          AuthRemoteDataSource
        >
    with $Provider<AuthRemoteDataSource> {
  AuthRemoteDataSourceProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'authRemoteDataSourceProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$authRemoteDataSourceHash();

  @$internal
  @override
  $ProviderElement<AuthRemoteDataSource> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  AuthRemoteDataSource create(Ref ref) {
    return authRemoteDataSource(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AuthRemoteDataSource value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AuthRemoteDataSource>(value),
    );
  }
}

String _$authRemoteDataSourceHash() =>
    r'87610dee063a79a2610eeb41c4d106aa6c118706';

@ProviderFor(authRepository)
final authRepositoryProvider = AuthRepositoryProvider._();

final class AuthRepositoryProvider
    extends $FunctionalProvider<AuthRepository, AuthRepository, AuthRepository>
    with $Provider<AuthRepository> {
  AuthRepositoryProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'authRepositoryProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$authRepositoryHash();

  @$internal
  @override
  $ProviderElement<AuthRepository> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  AuthRepository create(Ref ref) {
    return authRepository(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AuthRepository value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AuthRepository>(value),
    );
  }
}

String _$authRepositoryHash() => r'c97d3bac4cf4549236b1a2527e84c25ce8f9636e';

@ProviderFor(biometricKeyStore)
final biometricKeyStoreProvider = BiometricKeyStoreProvider._();

final class BiometricKeyStoreProvider
    extends
        $FunctionalProvider<
          BiometricKeyStore,
          BiometricKeyStore,
          BiometricKeyStore
        >
    with $Provider<BiometricKeyStore> {
  BiometricKeyStoreProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'biometricKeyStoreProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$biometricKeyStoreHash();

  @$internal
  @override
  $ProviderElement<BiometricKeyStore> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  BiometricKeyStore create(Ref ref) {
    return biometricKeyStore(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(BiometricKeyStore value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<BiometricKeyStore>(value),
    );
  }
}

String _$biometricKeyStoreHash() => r'0408609d18d6c10478dc6eabe2cefce44a67c806';

@ProviderFor(biometricAuthRepository)
final biometricAuthRepositoryProvider = BiometricAuthRepositoryProvider._();

final class BiometricAuthRepositoryProvider
    extends
        $FunctionalProvider<
          BiometricAuthRepository,
          BiometricAuthRepository,
          BiometricAuthRepository
        >
    with $Provider<BiometricAuthRepository> {
  BiometricAuthRepositoryProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'biometricAuthRepositoryProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$biometricAuthRepositoryHash();

  @$internal
  @override
  $ProviderElement<BiometricAuthRepository> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  BiometricAuthRepository create(Ref ref) {
    return biometricAuthRepository(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(BiometricAuthRepository value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<BiometricAuthRepository>(value),
    );
  }
}

String _$biometricAuthRepositoryHash() =>
    r'2516455be8cf6f0ebed8316b473a24d3f31cfe87';

@ProviderFor(biometricEnrolmentStore)
final biometricEnrolmentStoreProvider = BiometricEnrolmentStoreProvider._();

final class BiometricEnrolmentStoreProvider
    extends
        $FunctionalProvider<
          BiometricEnrolmentStore,
          BiometricEnrolmentStore,
          BiometricEnrolmentStore
        >
    with $Provider<BiometricEnrolmentStore> {
  BiometricEnrolmentStoreProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'biometricEnrolmentStoreProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$biometricEnrolmentStoreHash();

  @$internal
  @override
  $ProviderElement<BiometricEnrolmentStore> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  BiometricEnrolmentStore create(Ref ref) {
    return biometricEnrolmentStore(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(BiometricEnrolmentStore value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<BiometricEnrolmentStore>(value),
    );
  }
}

String _$biometricEnrolmentStoreHash() =>
    r'4289149b66362888d4d79b4ea8d1d3040c26ce1b';

@ProviderFor(enrolBiometricUnlock)
final enrolBiometricUnlockProvider = EnrolBiometricUnlockProvider._();

final class EnrolBiometricUnlockProvider
    extends
        $FunctionalProvider<
          EnrolBiometricUnlock,
          EnrolBiometricUnlock,
          EnrolBiometricUnlock
        >
    with $Provider<EnrolBiometricUnlock> {
  EnrolBiometricUnlockProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'enrolBiometricUnlockProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$enrolBiometricUnlockHash();

  @$internal
  @override
  $ProviderElement<EnrolBiometricUnlock> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  EnrolBiometricUnlock create(Ref ref) {
    return enrolBiometricUnlock(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(EnrolBiometricUnlock value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<EnrolBiometricUnlock>(value),
    );
  }
}

String _$enrolBiometricUnlockHash() =>
    r'77aff06aa40fdeaeade1f93987c6d84e18b510a5';

@ProviderFor(disableBiometricUnlock)
final disableBiometricUnlockProvider = DisableBiometricUnlockProvider._();

final class DisableBiometricUnlockProvider
    extends
        $FunctionalProvider<
          DisableBiometricUnlock,
          DisableBiometricUnlock,
          DisableBiometricUnlock
        >
    with $Provider<DisableBiometricUnlock> {
  DisableBiometricUnlockProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'disableBiometricUnlockProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$disableBiometricUnlockHash();

  @$internal
  @override
  $ProviderElement<DisableBiometricUnlock> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  DisableBiometricUnlock create(Ref ref) {
    return disableBiometricUnlock(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(DisableBiometricUnlock value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<DisableBiometricUnlock>(value),
    );
  }
}

String _$disableBiometricUnlockHash() =>
    r'5a3627f2e773c15b6057ac90c4efe360b0c9e71a';

@ProviderFor(unlockWithBiometric)
final unlockWithBiometricProvider = UnlockWithBiometricProvider._();

final class UnlockWithBiometricProvider
    extends
        $FunctionalProvider<
          UnlockWithBiometric,
          UnlockWithBiometric,
          UnlockWithBiometric
        >
    with $Provider<UnlockWithBiometric> {
  UnlockWithBiometricProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'unlockWithBiometricProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$unlockWithBiometricHash();

  @$internal
  @override
  $ProviderElement<UnlockWithBiometric> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  UnlockWithBiometric create(Ref ref) {
    return unlockWithBiometric(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(UnlockWithBiometric value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<UnlockWithBiometric>(value),
    );
  }
}

String _$unlockWithBiometricHash() =>
    r'23f2c209ac403de3f2f27d4ea194578492a49573';

/// The biometric path of the recent-auth confirmation, for the code reveal re-auth (M-A14).

@ProviderFor(confirmRecentAuthWithBiometric)
final confirmRecentAuthWithBiometricProvider =
    ConfirmRecentAuthWithBiometricProvider._();

/// The biometric path of the recent-auth confirmation, for the code reveal re-auth (M-A14).

final class ConfirmRecentAuthWithBiometricProvider
    extends
        $FunctionalProvider<
          ConfirmRecentAuthWithBiometric,
          ConfirmRecentAuthWithBiometric,
          ConfirmRecentAuthWithBiometric
        >
    with $Provider<ConfirmRecentAuthWithBiometric> {
  /// The biometric path of the recent-auth confirmation, for the code reveal re-auth (M-A14).
  ConfirmRecentAuthWithBiometricProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'confirmRecentAuthWithBiometricProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$confirmRecentAuthWithBiometricHash();

  @$internal
  @override
  $ProviderElement<ConfirmRecentAuthWithBiometric> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  ConfirmRecentAuthWithBiometric create(Ref ref) {
    return confirmRecentAuthWithBiometric(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ConfirmRecentAuthWithBiometric value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ConfirmRecentAuthWithBiometric>(
        value,
      ),
    );
  }
}

String _$confirmRecentAuthWithBiometricHash() =>
    r'1413d56123d040f7c927c5733a408b42edb86034';
