// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'guest_order_controllers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Guest order access for existing guest web orders (owner decision 4: no guest checkout; access
/// stays for orders placed as a guest). The signed delivery link (from [GuestLinkInbox]) is
/// opened through its `/mobile` twin, then the email one-time code is sent and verified; the
/// guest order token in secure storage carries both proofs, and the order detail and reveals use
/// it (`GuestOrderTokenInterceptor`). Without a waiting link the stored token is tried.

@ProviderFor(GuestOrderController)
final guestOrderControllerProvider = GuestOrderControllerFamily._();

/// Guest order access for existing guest web orders (owner decision 4: no guest checkout; access
/// stays for orders placed as a guest). The signed delivery link (from [GuestLinkInbox]) is
/// opened through its `/mobile` twin, then the email one-time code is sent and verified; the
/// guest order token in secure storage carries both proofs, and the order detail and reveals use
/// it (`GuestOrderTokenInterceptor`). Without a waiting link the stored token is tried.
final class GuestOrderControllerProvider
    extends $NotifierProvider<GuestOrderController, GuestOrderState> {
  /// Guest order access for existing guest web orders (owner decision 4: no guest checkout; access
  /// stays for orders placed as a guest). The signed delivery link (from [GuestLinkInbox]) is
  /// opened through its `/mobile` twin, then the email one-time code is sent and verified; the
  /// guest order token in secure storage carries both proofs, and the order detail and reveals use
  /// it (`GuestOrderTokenInterceptor`). Without a waiting link the stored token is tried.
  GuestOrderControllerProvider._({
    required GuestOrderControllerFamily super.from,
    required String super.argument,
  }) : super(
         retry: null,
         name: r'guestOrderControllerProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$guestOrderControllerHash();

  @override
  String toString() {
    return r'guestOrderControllerProvider'
        ''
        '($argument)';
  }

  @$internal
  @override
  GuestOrderController create() => GuestOrderController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(GuestOrderState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<GuestOrderState>(value),
    );
  }

  @override
  bool operator ==(Object other) {
    return other is GuestOrderControllerProvider && other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$guestOrderControllerHash() =>
    r'1bfc58c74790bba71a500c2ffa3f7b77b20f1645';

/// Guest order access for existing guest web orders (owner decision 4: no guest checkout; access
/// stays for orders placed as a guest). The signed delivery link (from [GuestLinkInbox]) is
/// opened through its `/mobile` twin, then the email one-time code is sent and verified; the
/// guest order token in secure storage carries both proofs, and the order detail and reveals use
/// it (`GuestOrderTokenInterceptor`). Without a waiting link the stored token is tried.

final class GuestOrderControllerFamily extends $Family
    with
        $ClassFamilyOverride<
          GuestOrderController,
          GuestOrderState,
          GuestOrderState,
          GuestOrderState,
          String
        > {
  GuestOrderControllerFamily._()
    : super(
        retry: null,
        name: r'guestOrderControllerProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// Guest order access for existing guest web orders (owner decision 4: no guest checkout; access
  /// stays for orders placed as a guest). The signed delivery link (from [GuestLinkInbox]) is
  /// opened through its `/mobile` twin, then the email one-time code is sent and verified; the
  /// guest order token in secure storage carries both proofs, and the order detail and reveals use
  /// it (`GuestOrderTokenInterceptor`). Without a waiting link the stored token is tried.

  GuestOrderControllerProvider call(String order) =>
      GuestOrderControllerProvider._(argument: order, from: this);

  @override
  String toString() => r'guestOrderControllerProvider';
}

/// Guest order access for existing guest web orders (owner decision 4: no guest checkout; access
/// stays for orders placed as a guest). The signed delivery link (from [GuestLinkInbox]) is
/// opened through its `/mobile` twin, then the email one-time code is sent and verified; the
/// guest order token in secure storage carries both proofs, and the order detail and reveals use
/// it (`GuestOrderTokenInterceptor`). Without a waiting link the stored token is tried.

abstract class _$GuestOrderController extends $Notifier<GuestOrderState> {
  late final _$args = ref.$arg as String;
  String get order => _$args;

  GuestOrderState build(String order);
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<GuestOrderState, GuestOrderState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<GuestOrderState, GuestOrderState>,
              GuestOrderState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, () => build(_$args));
  }
}

/// "Open a guest order": the customer pastes the link of the delivery email (the reveal needs
/// that link's proof, so an order number alone cannot open codes).

@ProviderFor(GuestLinkEntry)
final guestLinkEntryProvider = GuestLinkEntryProvider._();

/// "Open a guest order": the customer pastes the link of the delivery email (the reveal needs
/// that link's proof, so an order number alone cannot open codes).
final class GuestLinkEntryProvider
    extends $NotifierProvider<GuestLinkEntry, GuestLinkEntryState> {
  /// "Open a guest order": the customer pastes the link of the delivery email (the reveal needs
  /// that link's proof, so an order number alone cannot open codes).
  GuestLinkEntryProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'guestLinkEntryProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$guestLinkEntryHash();

  @$internal
  @override
  GuestLinkEntry create() => GuestLinkEntry();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(GuestLinkEntryState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<GuestLinkEntryState>(value),
    );
  }
}

String _$guestLinkEntryHash() => r'b9dba6d42fd4a566a04d70aec22a64c553acea5b';

/// "Open a guest order": the customer pastes the link of the delivery email (the reveal needs
/// that link's proof, so an order number alone cannot open codes).

abstract class _$GuestLinkEntry extends $Notifier<GuestLinkEntryState> {
  GuestLinkEntryState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<GuestLinkEntryState, GuestLinkEntryState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<GuestLinkEntryState, GuestLinkEntryState>,
              GuestLinkEntryState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
