import 'dart:async';

import 'package:application/core/money/money.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/cart/cart_providers.dart';
import 'package:application/features/checkout/checkout_providers.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/features/orders/orders_providers.dart';
import 'package:application/features/payments/domain/usecases/resolve_payment.dart';
import 'package:application/features/payments/payments_providers.dart';
import 'package:application/shared/domain/entities/payment_next.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'payment_flow.g.dart';

/// Where the payment of the current order is (07.6, 08.1 to 08.4).
enum PaymentPhase {
  /// Nothing started for [PaymentFlowState.orderPublicId] yet (a link opened the screen while
  /// signed out, for example): the screen offers "Check payment status".
  idle,

  /// Opening the provider's page in the system browser.
  opening,

  /// The provider's page is open (07.6): "Return to PayPal", "Check payment status".
  awaitingProvider,

  /// `verify-payment` / `paypal/capture`, then status polling (`ResolvePayment`).
  confirming,

  /// The server says `payment_status: paid` (08.1). The only paid state.
  paid,

  /// Two minutes of polling without a final answer (08.2).
  processing,

  /// The attempt failed or the customer cancelled (08.3); [PaymentFlowState.retryable] tells
  /// whether retry-payment can be offered.
  failed,

  /// The order was cancelled because its payment window ran out (08.4, `order_expired`).
  expired,

  /// The order exists but no payment attempt could be started (07.7, `gateway_unavailable`).
  notStarted,

  /// A step this app cannot launch (Stripe without the native SDK, an unknown `next.type`).
  unsupported,
}

/// State of [PaymentFlow]. Never paid unless the server said so ([PaymentPhase.paid]).
@immutable
final class PaymentFlowState {
  const PaymentFlowState({
    this.orderPublicId,
    this.phase = PaymentPhase.idle,
    this.next,
    this.gatewayLabel,
    this.orderNumber,
    this.paymentTotal,
    this.expiresAt,
    this.attempt,
    this.lastStatus,
    this.lastCheckedAt,
    this.failure,
    this.openFailed = false,
    this.retryable = false,
    this.cancelledByCustomer = false,
    this.attemptsExhausted = false,
    this.retrying = false,
  });

  final String? orderPublicId;
  final PaymentPhase phase;

  /// The provider step of the current attempt (null after a restart: the app verifies instead).
  final PaymentNext? next;

  /// The gateway's label from payment options or the order's payment (`PayPal`, `Paymob`).
  final String? gatewayLabel;
  final String? orderNumber;

  /// What is charged, in the gateway's currency (owner decision 1).
  final Money? paymentTotal;

  /// End of the payment window (`order.expires_at`).
  final DateTime? expiresAt;

  /// Number of the latest payment attempt, from the order detail.
  final int? attempt;

  /// The server's last answer (status, verify or capture).
  final OrderPaymentStatus? lastStatus;
  final DateTime? lastCheckedAt;

  /// The last call that failed (shown in a banner; the phase keeps its meaning).
  final Failure? failure;

  /// The system browser could not open the provider's page.
  final bool openFailed;

  /// A failed payment can be tried again with retry-payment (the order is still payable).
  final bool retryable;

  /// The customer came back through the provider's cancel link.
  final bool cancelledByCustomer;

  /// 409 `payment_attempts_exhausted`: place the order again from the cart.
  final bool attemptsExhausted;

  /// retry-payment is running.
  final bool retrying;

  /// A provider page can be opened again ("Return to PayPal").
  Uri? get providerUrl => switch (next) {
    PayPalNext(:final approveUrl) => approveUrl,
    RedirectNext(:final redirectUrl) => redirectUrl,
    _ => null,
  };

  /// The screen has an answer and leaving it ends the flow.
  bool get isResult => switch (phase) {
    PaymentPhase.paid ||
    PaymentPhase.processing ||
    PaymentPhase.failed ||
    PaymentPhase.expired ||
    PaymentPhase.notStarted ||
    PaymentPhase.unsupported => true,
    _ => false,
  };

  PaymentFlowState copyWith({
    PaymentPhase? phase,
    PaymentNext? Function()? next,
    String? Function()? gatewayLabel,
    String? Function()? orderNumber,
    Money? Function()? paymentTotal,
    DateTime? Function()? expiresAt,
    int? Function()? attempt,
    OrderPaymentStatus? Function()? lastStatus,
    DateTime? Function()? lastCheckedAt,
    Failure? Function()? failure,
    bool? openFailed,
    bool? retryable,
    bool? cancelledByCustomer,
    bool? attemptsExhausted,
    bool? retrying,
  }) => PaymentFlowState(
    orderPublicId: orderPublicId,
    phase: phase ?? this.phase,
    next: next == null ? this.next : next(),
    gatewayLabel: gatewayLabel == null ? this.gatewayLabel : gatewayLabel(),
    orderNumber: orderNumber == null ? this.orderNumber : orderNumber(),
    paymentTotal: paymentTotal == null ? this.paymentTotal : paymentTotal(),
    expiresAt: expiresAt == null ? this.expiresAt : expiresAt(),
    attempt: attempt == null ? this.attempt : attempt(),
    lastStatus: lastStatus == null ? this.lastStatus : lastStatus(),
    lastCheckedAt: lastCheckedAt == null ? this.lastCheckedAt : lastCheckedAt(),
    failure: failure == null ? this.failure : failure(),
    openFailed: openFailed ?? this.openFailed,
    retryable: retryable ?? this.retryable,
    cancelledByCustomer: cancelledByCustomer ?? this.cancelledByCustomer,
    attemptsExhausted: attemptsExhausted ?? this.attemptsExhausted,
    retrying: retrying ?? this.retrying,
  );
}

/// The payment of one order after place-order (MOBILE_APP_ROADMAP.md 5, ADR-039). The payments
/// feature's public entry: checkout starts it, the app-link listener hands it return links, and
/// the launch resumes it from the pending checkout after a restart.
///
/// - PayPal and Paymob pages open in the system browser; the app link
///   `/app/payments/{return,cancel}/{order}` (or "Check payment status") brings the customer back.
/// - A return link proves nothing: it only triggers `verify-payment` / `paypal/capture` and the
///   status polling of `ResolvePayment`. Paid is shown only when the server says `paid`.
/// - A final answer (paid, failed, expired) clears the pending checkout.
@Riverpod(keepAlive: true)
class PaymentFlow extends _$PaymentFlow {
  int _generation = 0;
  bool _confirming = false;

  @override
  PaymentFlowState build() => const PaymentFlowState();

  /// After place-order or retry-payment: launches [next] for [orderPublicId].
  Future<void> start({
    required String orderPublicId,
    required PaymentNext next,
    String? gatewayLabel,
    String? orderNumber,
    Money? paymentTotal,
    DateTime? expiresAt,
  }) async {
    _generation++;
    _confirming = false;
    state = PaymentFlowState(
      orderPublicId: orderPublicId,
      next: next,
      gatewayLabel: gatewayLabel,
      orderNumber: orderNumber,
      paymentTotal: paymentTotal,
      expiresAt: expiresAt,
    );
    await _launch(next);
  }

  /// "Retry payment" on the order detail (M-A14): opens the flow for an unpaid order whose
  /// latest attempt ended and starts a new attempt on its gateway (`retry-payment`,
  /// `client: app`), exactly as "Try payment again" on the failed result does.
  Future<void> retryFromOrder({
    required String orderPublicId,
    String? gatewayLabel,
    String? orderNumber,
    Money? paymentTotal,
    int? attempt,
  }) async {
    _generation++;
    _confirming = false;
    state = PaymentFlowState(
      orderPublicId: orderPublicId,
      phase: PaymentPhase.failed,
      retryable: true,
      gatewayLabel: gatewayLabel,
      orderNumber: orderNumber,
      paymentTotal: paymentTotal,
      attempt: attempt,
    );
    await retryPayment();
  }

  /// The order exists but no attempt could be started (`next: null` and retry-payment failed).
  void notStarted({
    required String orderPublicId,
    required Failure failure,
    String? gatewayLabel,
    String? orderNumber,
    Money? paymentTotal,
    DateTime? expiresAt,
  }) {
    _generation++;
    _confirming = false;
    state = PaymentFlowState(
      orderPublicId: orderPublicId,
      phase: PaymentPhase.notStarted,
      gatewayLabel: gatewayLabel,
      orderNumber: orderNumber,
      paymentTotal: paymentTotal,
      expiresAt: expiresAt,
      failure: failure,
      retryable: true,
    );
  }

  /// Resumes [orderPublicId] without a provider step: after a restart (pending checkout) or for
  /// a duplicate order (`meta.duplicate: true`). Asks the server (verify, then polling).
  Future<void> resume(String orderPublicId) async {
    if (state.orderPublicId != orderPublicId) {
      _generation++;
      _confirming = false;
      state = PaymentFlowState(orderPublicId: orderPublicId);
    }
    await _confirm();
  }

  /// A payment return or cancel app link for [orderPublicId]. It never marks anything paid: a
  /// return asks the server to verify (or capture) and polls; a cancel reads the status once.
  Future<void> handleReturn(
    String orderPublicId, {
    required bool cancelled,
  }) async {
    if (state.orderPublicId != orderPublicId) {
      _generation++;
      _confirming = false;
      state = PaymentFlowState(orderPublicId: orderPublicId);
    }
    if (cancelled) {
      await _afterCancel();
    } else {
      await _confirm();
    }
  }

  /// "Return to PayPal": opens the provider's page again.
  Future<void> reopenProvider() async {
    final next = state.next;
    if (next != null) {
      await _launch(next);
    }
  }

  /// "Check payment status" / "Check again".
  Future<void> checkStatus() => _confirm();

  /// "Try payment again": a new attempt on the order's gateway (`retry-payment`, `client: app`).
  Future<void> retryPayment() async {
    final order = state.orderPublicId;
    if (order == null || state.retrying) {
      return;
    }
    final generation = _generation;
    state = state.copyWith(retrying: true, failure: () => null);
    final result = await ref
        .read(paymentsRepositoryProvider)
        .retryPayment(order);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    switch (result) {
      case Ok(:final value):
        state = state.copyWith(
          retrying: false,
          next: () => value,
          openFailed: false,
          cancelledByCustomer: false,
        );
        await _launch(value);
      case Err(failure: ConflictFailure(code: 'order_expired')):
        state = state.copyWith(
          retrying: false,
          phase: PaymentPhase.expired,
          retryable: false,
        );
        await _finish();
      case Err(failure: ConflictFailure(code: 'order_not_payable')):
        // Already paid, or no longer payable: ask the server what happened.
        state = state.copyWith(retrying: false);
        await _confirm();
      case Err(failure: final ConflictFailure failure)
          when failure.code == 'payment_attempts_exhausted':
        state = state.copyWith(
          retrying: false,
          phase: PaymentPhase.failed,
          retryable: false,
          attemptsExhausted: true,
          failure: () => failure,
        );
      case Err(:final failure):
        state = state.copyWith(retrying: false, failure: () => failure);
    }
  }

  /// The customer leaves a result screen: a final answer clears the pending checkout, so the
  /// next launch does not resume this order (it stays in Orders).
  Future<void> close() async {
    if (state.isResult) {
      await _finish();
      _generation++;
      _confirming = false;
      state = const PaymentFlowState();
    }
  }

  Future<void> _launch(PaymentNext next) async {
    final generation = _generation;
    switch (next) {
      case WalletNext():
        // Paid from the balance at placement; the status poll still has the last word.
        await _confirm();
      case PayPalNext(:final approveUrl):
        await _open(approveUrl, generation);
      case RedirectNext(:final redirectUrl):
        await _open(redirectUrl, generation);
      case StripeNext() || UnknownNext():
        state = state.copyWith(phase: PaymentPhase.unsupported);
    }
  }

  Future<void> _open(Uri url, int generation) async {
    state = state.copyWith(
      phase: PaymentPhase.opening,
      openFailed: false,
      failure: () => null,
    );
    final opened = await ref.read(nativeBridgeProvider).openExternalUrl(url);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = state.copyWith(
      phase: PaymentPhase.awaitingProvider,
      openFailed: !opened,
    );
  }

  /// verify-payment (or capture for a PayPal step started in this session), then polling.
  Future<void> _confirm() async {
    final order = state.orderPublicId;
    if (order == null || _confirming) {
      return;
    }
    _confirming = true;
    final generation = _generation;
    final before = state.phase;
    state = state.copyWith(phase: PaymentPhase.confirming, failure: () => null);
    try {
      final result = await ref
          .read(resolvePaymentProvider)
          .call(order, next: state.next);
      if (!ref.mounted || generation != _generation) {
        return;
      }
      state = state.copyWith(lastCheckedAt: () => ref.read(clockProvider)());
      switch (result) {
        case Ok(value: PaymentResolved(:final status)):
          state = state.copyWith(
            phase: PaymentPhase.paid,
            lastStatus: () => status,
            retryable: false,
          );
          await _finish();
        case Ok(value: PaymentFinished(:final status)):
          state = state.copyWith(
            phase: status.status == 'cancelled'
                ? PaymentPhase.expired
                : PaymentPhase.failed,
            lastStatus: () => status,
            retryable: false,
          );
          await _finish();
        case Ok(value: PaymentStillProcessing(:final lastKnown)):
          state = state.copyWith(lastStatus: () => lastKnown);
          final detail = await _loadDetail(order, generation);
          if (!ref.mounted || generation != _generation) {
            return;
          }
          final attemptEnded =
              lastKnown?.status == 'pending_payment' &&
              _latestAttemptEnded(detail);
          state = state.copyWith(
            phase: attemptEnded ? PaymentPhase.failed : PaymentPhase.processing,
            retryable: attemptEnded,
          );
          return;
        case Err(:final failure):
          state = state.copyWith(
            phase: switch (before) {
              PaymentPhase.awaitingProvider ||
              PaymentPhase.failed ||
              PaymentPhase.notStarted ||
              PaymentPhase.unsupported => before,
              _ => PaymentPhase.processing,
            },
            failure: () => failure,
          );
          return;
      }
      await _loadDetail(order, generation);
    } finally {
      if (generation == _generation) {
        _confirming = false;
      }
    }
  }

  /// The cancel link: one status read; still payable means "not completed, try again".
  Future<void> _afterCancel() async {
    final order = state.orderPublicId;
    if (order == null || _confirming) {
      return;
    }
    _confirming = true;
    final generation = _generation;
    state = state.copyWith(phase: PaymentPhase.confirming, failure: () => null);
    try {
      final result = await ref.read(paymentsRepositoryProvider).status(order);
      if (!ref.mounted || generation != _generation) {
        return;
      }
      state = state.copyWith(lastCheckedAt: () => ref.read(clockProvider)());
      switch (result) {
        case Ok(:final value) when value.isPaid:
          state = state.copyWith(
            phase: PaymentPhase.paid,
            lastStatus: () => value,
          );
          await _finish();
        case Ok(:final value) when value.isFinal:
          state = state.copyWith(
            phase: value.status == 'cancelled'
                ? PaymentPhase.expired
                : PaymentPhase.failed,
            lastStatus: () => value,
            retryable: false,
          );
          await _finish();
        case Ok(:final value):
          state = state.copyWith(
            phase: PaymentPhase.failed,
            lastStatus: () => value,
            retryable: true,
            cancelledByCustomer: true,
          );
        case Err(:final failure):
          state = state.copyWith(
            phase: PaymentPhase.failed,
            retryable: true,
            cancelledByCustomer: true,
            failure: () => failure,
          );
      }
      await _loadDetail(order, generation);
    } finally {
      if (generation == _generation) {
        _confirming = false;
      }
    }
  }

  /// Fills the summary (number, amount, gateway, attempt, window) from the account order detail.
  Future<OrderDetail?> _loadDetail(String order, int generation) async {
    if (ref.read(sessionProvider) != SessionStatus.signedIn) {
      return null;
    }
    final result = await ref.read(ordersRepositoryProvider).detail(order);
    final detail = result.valueOrNull;
    if (detail == null || !ref.mounted || generation != _generation) {
      return detail;
    }
    final latest = _latestAttempt(detail);
    final summary = detail.summary;
    state = state.copyWith(
      orderNumber: () => state.orderNumber ?? summary.number,
      paymentTotal: () => state.paymentTotal ?? summary.paymentTotal,
      gatewayLabel: () => state.gatewayLabel ?? latest?.provider,
      attempt: () => latest?.attempt ?? state.attempt,
    );
    return detail;
  }

  static OrderPayment? _latestAttempt(OrderDetail? detail) {
    OrderPayment? latest;
    for (final payment in detail?.payments ?? const <OrderPayment>[]) {
      if (latest == null || payment.attempt > latest.attempt) {
        latest = payment;
      }
    }
    return latest;
  }

  static bool _latestAttemptEnded(OrderDetail? detail) =>
      switch (_latestAttempt(detail)?.status) {
        'failed' || 'cancelled' || 'expired' => true,
        _ => false,
      };

  /// Forgets the pending checkout and lets the cart and its badge reload (the order consumed it).
  Future<void> _finish() async {
    await ref.read(placeOrderProvider).finish();
    if (!ref.mounted) {
      return;
    }
    ref.read(cartRevisionProvider.notifier).bump();
    unawaited(ref.read(cartCountProvider.notifier).refresh());
  }
}

/// The launch hook of the payments feature (called by the launch check, like `auth_launch.dart`):
/// a checkout interrupted after placement (app killed while paying) resumes at its payment screen
/// and verifies again.
@Riverpod(keepAlive: true)
class PaymentLaunch extends _$PaymentLaunch {
  @override
  void build() {}

  /// The order to resume, after starting its verification; null when there is none (signed out,
  /// or nothing was placed).
  Future<String?> resumeOrder() async {
    if (ref.read(sessionProvider) != SessionStatus.signedIn) {
      return null;
    }
    final order = await ref.read(placeOrderProvider).pendingOrder();
    if (order == null || !ref.mounted) {
      return null;
    }
    unawaited(ref.read(paymentFlowProvider.notifier).resume(order));
    return order;
  }
}
