// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'payment_flow.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The payment of one order after place-order (MOBILE_APP_ROADMAP.md 5, ADR-039). The payments
/// feature's public entry: checkout starts it, the app-link listener hands it return links, and
/// the launch resumes it from the pending checkout after a restart.
///
/// - PayPal and Paymob pages open in the system browser; the app link
///   `/app/payments/{return,cancel}/{order}` (or "Check payment status") brings the customer back.
/// - A return link proves nothing: it only triggers `verify-payment` / `paypal/capture` and the
///   status polling of `ResolvePayment`. Paid is shown only when the server says `paid`.
/// - A final answer (paid, failed, expired) clears the pending checkout.

@ProviderFor(PaymentFlow)
final paymentFlowProvider = PaymentFlowProvider._();

/// The payment of one order after place-order (MOBILE_APP_ROADMAP.md 5, ADR-039). The payments
/// feature's public entry: checkout starts it, the app-link listener hands it return links, and
/// the launch resumes it from the pending checkout after a restart.
///
/// - PayPal and Paymob pages open in the system browser; the app link
///   `/app/payments/{return,cancel}/{order}` (or "Check payment status") brings the customer back.
/// - A return link proves nothing: it only triggers `verify-payment` / `paypal/capture` and the
///   status polling of `ResolvePayment`. Paid is shown only when the server says `paid`.
/// - A final answer (paid, failed, expired) clears the pending checkout.
final class PaymentFlowProvider
    extends $NotifierProvider<PaymentFlow, PaymentFlowState> {
  /// The payment of one order after place-order (MOBILE_APP_ROADMAP.md 5, ADR-039). The payments
  /// feature's public entry: checkout starts it, the app-link listener hands it return links, and
  /// the launch resumes it from the pending checkout after a restart.
  ///
  /// - PayPal and Paymob pages open in the system browser; the app link
  ///   `/app/payments/{return,cancel}/{order}` (or "Check payment status") brings the customer back.
  /// - A return link proves nothing: it only triggers `verify-payment` / `paypal/capture` and the
  ///   status polling of `ResolvePayment`. Paid is shown only when the server says `paid`.
  /// - A final answer (paid, failed, expired) clears the pending checkout.
  PaymentFlowProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'paymentFlowProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$paymentFlowHash();

  @$internal
  @override
  PaymentFlow create() => PaymentFlow();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(PaymentFlowState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<PaymentFlowState>(value),
    );
  }
}

String _$paymentFlowHash() => r'7fd055138a61e974b90096a20736787594d44198';

/// The payment of one order after place-order (MOBILE_APP_ROADMAP.md 5, ADR-039). The payments
/// feature's public entry: checkout starts it, the app-link listener hands it return links, and
/// the launch resumes it from the pending checkout after a restart.
///
/// - PayPal and Paymob pages open in the system browser; the app link
///   `/app/payments/{return,cancel}/{order}` (or "Check payment status") brings the customer back.
/// - A return link proves nothing: it only triggers `verify-payment` / `paypal/capture` and the
///   status polling of `ResolvePayment`. Paid is shown only when the server says `paid`.
/// - A final answer (paid, failed, expired) clears the pending checkout.

abstract class _$PaymentFlow extends $Notifier<PaymentFlowState> {
  PaymentFlowState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<PaymentFlowState, PaymentFlowState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<PaymentFlowState, PaymentFlowState>,
              PaymentFlowState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// The launch hook of the payments feature (called by the launch check, like `auth_launch.dart`):
/// a checkout interrupted after placement (app killed while paying) resumes at its payment screen
/// and verifies again.

@ProviderFor(PaymentLaunch)
final paymentLaunchProvider = PaymentLaunchProvider._();

/// The launch hook of the payments feature (called by the launch check, like `auth_launch.dart`):
/// a checkout interrupted after placement (app killed while paying) resumes at its payment screen
/// and verifies again.
final class PaymentLaunchProvider
    extends $NotifierProvider<PaymentLaunch, void> {
  /// The launch hook of the payments feature (called by the launch check, like `auth_launch.dart`):
  /// a checkout interrupted after placement (app killed while paying) resumes at its payment screen
  /// and verifies again.
  PaymentLaunchProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'paymentLaunchProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$paymentLaunchHash();

  @$internal
  @override
  PaymentLaunch create() => PaymentLaunch();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(void value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<void>(value),
    );
  }
}

String _$paymentLaunchHash() => r'95b790e89f0873aecdd253d4dec89bbd74ad331a';

/// The launch hook of the payments feature (called by the launch check, like `auth_launch.dart`):
/// a checkout interrupted after placement (app killed while paying) resumes at its payment screen
/// and verifies again.

abstract class _$PaymentLaunch extends $Notifier<void> {
  void build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<void, void>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<void, void>,
              void,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
