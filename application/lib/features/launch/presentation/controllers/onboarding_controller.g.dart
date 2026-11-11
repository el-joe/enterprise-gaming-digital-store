// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'onboarding_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Onboarding (01.5, 01.6): the language applies at once (the screen mirrors); the display
/// currency is saved (sent as `X-Currency`) only on "Start shopping". "Skip" keeps the server's
/// country-based currency (owner decision 1).

@ProviderFor(OnboardingController)
final onboardingControllerProvider = OnboardingControllerProvider._();

/// Onboarding (01.5, 01.6): the language applies at once (the screen mirrors); the display
/// currency is saved (sent as `X-Currency`) only on "Start shopping". "Skip" keeps the server's
/// country-based currency (owner decision 1).
final class OnboardingControllerProvider
    extends $NotifierProvider<OnboardingController, OnboardingState> {
  /// Onboarding (01.5, 01.6): the language applies at once (the screen mirrors); the display
  /// currency is saved (sent as `X-Currency`) only on "Start shopping". "Skip" keeps the server's
  /// country-based currency (owner decision 1).
  OnboardingControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'onboardingControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$onboardingControllerHash();

  @$internal
  @override
  OnboardingController create() => OnboardingController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(OnboardingState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<OnboardingState>(value),
    );
  }
}

String _$onboardingControllerHash() =>
    r'0f684b03774a00b4748f3137ec483ca6b2484532';

/// Onboarding (01.5, 01.6): the language applies at once (the screen mirrors); the display
/// currency is saved (sent as `X-Currency`) only on "Start shopping". "Skip" keeps the server's
/// country-based currency (owner decision 1).

abstract class _$OnboardingController extends $Notifier<OnboardingState> {
  OnboardingState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<OnboardingState, OnboardingState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<OnboardingState, OnboardingState>,
              OnboardingState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
