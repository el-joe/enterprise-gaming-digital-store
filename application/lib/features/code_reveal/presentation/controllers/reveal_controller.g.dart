// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'reveal_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// One unit's code screen (10.1 to 10.6, guest 11.4). Auto-disposed: leaving the screen drops
/// the code. While the provider lives it holds screen protection (Android `FLAG_SECURE`, iOS
/// privacy overlay) and listens to iOS capture events.
///
/// Reveal: `RevealCode` with `X-Device-Integrity`; on 423 the biometric confirmation runs first
/// (when enabled), then the password sheet; one retry. 429 shows the retry time and is never
/// retried. The code hides after [revealVisibleFor], when the app goes to the background, when
/// capture starts, and when the customer taps Hide.

@ProviderFor(RevealController)
final revealControllerProvider = RevealControllerFamily._();

/// One unit's code screen (10.1 to 10.6, guest 11.4). Auto-disposed: leaving the screen drops
/// the code. While the provider lives it holds screen protection (Android `FLAG_SECURE`, iOS
/// privacy overlay) and listens to iOS capture events.
///
/// Reveal: `RevealCode` with `X-Device-Integrity`; on 423 the biometric confirmation runs first
/// (when enabled), then the password sheet; one retry. 429 shows the retry time and is never
/// retried. The code hides after [revealVisibleFor], when the app goes to the background, when
/// capture starts, and when the customer taps Hide.
final class RevealControllerProvider
    extends $NotifierProvider<RevealController, RevealScreenState> {
  /// One unit's code screen (10.1 to 10.6, guest 11.4). Auto-disposed: leaving the screen drops
  /// the code. While the provider lives it holds screen protection (Android `FLAG_SECURE`, iOS
  /// privacy overlay) and listens to iOS capture events.
  ///
  /// Reveal: `RevealCode` with `X-Device-Integrity`; on 423 the biometric confirmation runs first
  /// (when enabled), then the password sheet; one retry. 429 shows the retry time and is never
  /// retried. The code hides after [revealVisibleFor], when the app goes to the background, when
  /// capture starts, and when the customer taps Hide.
  RevealControllerProvider._({
    required RevealControllerFamily super.from,
    required (String, String, bool) super.argument,
  }) : super(
         retry: null,
         name: r'revealControllerProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$revealControllerHash();

  @override
  String toString() {
    return r'revealControllerProvider'
        ''
        '$argument';
  }

  @$internal
  @override
  RevealController create() => RevealController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(RevealScreenState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<RevealScreenState>(value),
    );
  }

  @override
  bool operator ==(Object other) {
    return other is RevealControllerProvider && other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$revealControllerHash() => r'3f765881dc827cf571f507db905570c8b67c2d54';

/// One unit's code screen (10.1 to 10.6, guest 11.4). Auto-disposed: leaving the screen drops
/// the code. While the provider lives it holds screen protection (Android `FLAG_SECURE`, iOS
/// privacy overlay) and listens to iOS capture events.
///
/// Reveal: `RevealCode` with `X-Device-Integrity`; on 423 the biometric confirmation runs first
/// (when enabled), then the password sheet; one retry. 429 shows the retry time and is never
/// retried. The code hides after [revealVisibleFor], when the app goes to the background, when
/// capture starts, and when the customer taps Hide.

final class RevealControllerFamily extends $Family
    with
        $ClassFamilyOverride<
          RevealController,
          RevealScreenState,
          RevealScreenState,
          RevealScreenState,
          (String, String, bool)
        > {
  RevealControllerFamily._()
    : super(
        retry: null,
        name: r'revealControllerProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// One unit's code screen (10.1 to 10.6, guest 11.4). Auto-disposed: leaving the screen drops
  /// the code. While the provider lives it holds screen protection (Android `FLAG_SECURE`, iOS
  /// privacy overlay) and listens to iOS capture events.
  ///
  /// Reveal: `RevealCode` with `X-Device-Integrity`; on 423 the biometric confirmation runs first
  /// (when enabled), then the password sheet; one retry. 429 shows the retry time and is never
  /// retried. The code hides after [revealVisibleFor], when the app goes to the background, when
  /// capture starts, and when the customer taps Hide.

  RevealControllerProvider call(String order, String unit, bool guest) =>
      RevealControllerProvider._(argument: (order, unit, guest), from: this);

  @override
  String toString() => r'revealControllerProvider';
}

/// One unit's code screen (10.1 to 10.6, guest 11.4). Auto-disposed: leaving the screen drops
/// the code. While the provider lives it holds screen protection (Android `FLAG_SECURE`, iOS
/// privacy overlay) and listens to iOS capture events.
///
/// Reveal: `RevealCode` with `X-Device-Integrity`; on 423 the biometric confirmation runs first
/// (when enabled), then the password sheet; one retry. 429 shows the retry time and is never
/// retried. The code hides after [revealVisibleFor], when the app goes to the background, when
/// capture starts, and when the customer taps Hide.

abstract class _$RevealController extends $Notifier<RevealScreenState> {
  late final _$args = ref.$arg as (String, String, bool);
  String get order => _$args.$1;
  String get unit => _$args.$2;
  bool get guest => _$args.$3;

  RevealScreenState build(String order, String unit, bool guest);
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<RevealScreenState, RevealScreenState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<RevealScreenState, RevealScreenState>,
              RevealScreenState,
              Object?,
              Object?
            >;
    return element.handleCreate(
      ref,
      () => build(_$args.$1, _$args.$2, _$args.$3),
    );
  }
}
