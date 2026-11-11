// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'guards.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Session at launch, read from secure storage in `bootstrap.dart`.

@ProviderFor(initialSession)
final initialSessionProvider = InitialSessionProvider._();

/// Session at launch, read from secure storage in `bootstrap.dart`.

final class InitialSessionProvider
    extends $FunctionalProvider<SessionStatus, SessionStatus, SessionStatus>
    with $Provider<SessionStatus> {
  /// Session at launch, read from secure storage in `bootstrap.dart`.
  InitialSessionProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'initialSessionProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$initialSessionHash();

  @$internal
  @override
  $ProviderElement<SessionStatus> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  SessionStatus create(Ref ref) {
    return initialSession(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(SessionStatus value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<SessionStatus>(value),
    );
  }
}

String _$initialSessionHash() => r'9098649a31a4849e6f5157fa61b4004ee05da5e1';

@ProviderFor(Session)
final sessionProvider = SessionProvider._();

final class SessionProvider extends $NotifierProvider<Session, SessionStatus> {
  SessionProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'sessionProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$sessionHash();

  @$internal
  @override
  Session create() => Session();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(SessionStatus value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<SessionStatus>(value),
    );
  }
}

String _$sessionHash() => r'c8692d5d7b5ec1ad671d801e67ce793940a85100';

abstract class _$Session extends $Notifier<SessionStatus> {
  SessionStatus build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<SessionStatus, SessionStatus>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<SessionStatus, SessionStatus>,
              SessionStatus,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

@ProviderFor(EmailVerification)
final emailVerificationProvider = EmailVerificationProvider._();

final class EmailVerificationProvider
    extends $NotifierProvider<EmailVerification, bool?> {
  EmailVerificationProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'emailVerificationProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$emailVerificationHash();

  @$internal
  @override
  EmailVerification create() => EmailVerification();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(bool? value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<bool?>(value),
    );
  }
}

String _$emailVerificationHash() => r'62a69dd525304b4e24399c3da3e0b50713782c22';

abstract class _$EmailVerification extends $Notifier<bool?> {
  bool? build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<bool?, bool?>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<bool?, bool?>,
              bool?,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

@ProviderFor(Availability)
final availabilityProvider = AvailabilityProvider._();

final class AvailabilityProvider
    extends $NotifierProvider<Availability, AppAvailability> {
  AvailabilityProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'availabilityProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$availabilityHash();

  @$internal
  @override
  Availability create() => Availability();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(AppAvailability value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<AppAvailability>(value),
    );
  }
}

String _$availabilityHash() => r'd5168a2ef17eca866beca7a728c5aebfa8758e7f';

abstract class _$Availability extends $Notifier<AppAvailability> {
  AppAvailability build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<AppAvailability, AppAvailability>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<AppAvailability, AppAvailability>,
              AppAvailability,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// Whether the one-time biometric unlock offer is due at the next sign-in (owner decision 6). Set
/// by the auth feature at launch from the device's support and the saved flags.

@ProviderFor(BiometricOfferDue)
final biometricOfferDueProvider = BiometricOfferDueProvider._();

/// Whether the one-time biometric unlock offer is due at the next sign-in (owner decision 6). Set
/// by the auth feature at launch from the device's support and the saved flags.
final class BiometricOfferDueProvider
    extends $NotifierProvider<BiometricOfferDue, bool> {
  /// Whether the one-time biometric unlock offer is due at the next sign-in (owner decision 6). Set
  /// by the auth feature at launch from the device's support and the saved flags.
  BiometricOfferDueProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'biometricOfferDueProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$biometricOfferDueHash();

  @$internal
  @override
  BiometricOfferDue create() => BiometricOfferDue();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(bool value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<bool>(value),
    );
  }
}

String _$biometricOfferDueHash() => r'391c0a53f7325405f18eb3496da54850ffa6249e';

/// Whether the one-time biometric unlock offer is due at the next sign-in (owner decision 6). Set
/// by the auth feature at launch from the device's support and the saved flags.

abstract class _$BiometricOfferDue extends $Notifier<bool> {
  bool build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<bool, bool>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<bool, bool>,
              bool,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
