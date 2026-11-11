// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'checkout_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The signed-in checkout: country (from the profile when it is a checkout country), the cart
/// repriced for it (`validate`), the payment options with their charge currency and amount, the
/// terms, and place order through the `PlaceOrder` use case (idempotency key per body, duplicate,
/// `next: null`). A display currency or language change re-quotes.

@ProviderFor(CheckoutController)
final checkoutControllerProvider = CheckoutControllerProvider._();

/// The signed-in checkout: country (from the profile when it is a checkout country), the cart
/// repriced for it (`validate`), the payment options with their charge currency and amount, the
/// terms, and place order through the `PlaceOrder` use case (idempotency key per body, duplicate,
/// `next: null`). A display currency or language change re-quotes.
final class CheckoutControllerProvider
    extends $NotifierProvider<CheckoutController, CheckoutState> {
  /// The signed-in checkout: country (from the profile when it is a checkout country), the cart
  /// repriced for it (`validate`), the payment options with their charge currency and amount, the
  /// terms, and place order through the `PlaceOrder` use case (idempotency key per body, duplicate,
  /// `next: null`). A display currency or language change re-quotes.
  CheckoutControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'checkoutControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$checkoutControllerHash();

  @$internal
  @override
  CheckoutController create() => CheckoutController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(CheckoutState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<CheckoutState>(value),
    );
  }
}

String _$checkoutControllerHash() =>
    r'c3755819bb236ee6f51d94fd2135f20a992d9001';

/// The signed-in checkout: country (from the profile when it is a checkout country), the cart
/// repriced for it (`validate`), the payment options with their charge currency and amount, the
/// terms, and place order through the `PlaceOrder` use case (idempotency key per body, duplicate,
/// `next: null`). A display currency or language change re-quotes.

abstract class _$CheckoutController extends $Notifier<CheckoutState> {
  CheckoutState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<CheckoutState, CheckoutState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<CheckoutState, CheckoutState>,
              CheckoutState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
