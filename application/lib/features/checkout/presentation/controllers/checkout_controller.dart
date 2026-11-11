import 'dart:async';

import 'package:application/core/money/money.dart';
import 'package:application/core/money/pricing_context.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/account/account_providers.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/cart/cart_providers.dart';
import 'package:application/features/catalog/catalog_providers.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/features/checkout/checkout_providers.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/checkout/domain/usecases/place_order.dart';
import 'package:application/features/payments/payment_flow.dart';
import 'package:application/shared/domain/entities/cart.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'checkout_controller.g.dart';

/// The repriced cart and the payment options for one country.
@immutable
final class CheckoutQuote {
  const CheckoutQuote({required this.cart, required this.options});

  /// `POST /checkout/validate`: totals in the display currency.
  final Cart cart;

  /// `GET /checkout/payment-options`, in the server's order.
  final List<PaymentOption> options;

  /// What the customer is shown and sends back as `currency` / `expected_total`.
  Money get displayTotal => cart.totals.total;

  int get units => cart.items.fold(0, (sum, line) => sum + line.quantity);
}

/// Why an option cannot be chosen (07.3: disabled with a reason).
enum OptionBlock {
  /// `available: false` with `payment_currency_below_margin`.
  belowMargin,

  /// `available: false` for any other reason.
  unavailable,

  /// Stripe needs the native PaymentSheet SDK, which this app does not include (README).
  notInApp,
}

/// Null when [option] can be chosen.
OptionBlock? blockOfOption(PaymentOption option) {
  if (option.gateway == 'stripe') {
    return OptionBlock.notInApp;
  }
  if (!option.available) {
    return option.notice == 'payment_currency_below_margin'
        ? OptionBlock.belowMargin
        : OptionBlock.unavailable;
  }
  return null;
}

/// One-off things the page acts on (navigation and dialogs), counted so each fires once.
@immutable
sealed class CheckoutEvent {
  const CheckoutEvent();
}

/// Go to the payment screen of [orderPublicId] (placed, duplicate or not started).
final class OpenPayment extends CheckoutEvent {
  const OpenPayment(this.orderPublicId);

  final String orderPublicId;
}

/// `cart_empty` (or nothing left to buy): back to the cart.
final class BackToCart extends CheckoutEvent {
  const BackToCart();
}

/// `price_changed` (07.5): the total was re-quoted from [previous] to [current]. Nothing was
/// charged.
final class PriceChanged extends CheckoutEvent {
  const PriceChanged({required this.previous, required this.current});

  final Money previous;
  final Money? current;
}

/// Why the last place-order failed, for the banner.
enum PlaceProblem {
  /// 409 `idempotency_in_progress`: the same order is still being placed.
  inProgress,

  /// The cart needs attention (an item is unavailable or has invalid delivery values).
  cartNeedsAttention,

  /// Exchange rate missing or stale for the display currency (re-quoted).
  rateStale,

  /// Any other failure: the localized failure message or the server's domain text.
  other,
}

/// State of the checkout page (07.1 to 07.7).
@immutable
final class CheckoutState {
  const CheckoutState({
    this.countries = const Loadable.loading(),
    this.country,
    this.email,
    this.quote,
    this.gateway,
    this.method,
    this.acceptTerms = false,
    this.termsError = false,
    this.placing = false,
    this.problem,
    this.failure,
    this.failureText,
    this.event,
    this.eventCount = 0,
  });

  final Loadable<List<Country>> countries;

  /// ISO code; null until chosen.
  final String? country;

  /// The account email codes go to (customers use their account email).
  final String? email;

  /// Null until a country is chosen.
  final Loadable<CheckoutQuote>? quote;

  final String? gateway;
  final String? method;
  final bool acceptTerms;

  /// Pay was tapped without accepting the terms (or the server said `consents.terms`).
  final bool termsError;
  final bool placing;

  final PlaceProblem? problem;
  final Failure? failure;

  /// The server's own text for [failure] (domain message, item error), when it sent one.
  final String? failureText;

  final CheckoutEvent? event;
  final int eventCount;

  CheckoutQuote? get loadedQuote => quote?.valueOrNull;

  Country? get selectedCountry {
    for (final item in countries.valueOrNull ?? const <Country>[]) {
      if (item.code == country) {
        return item;
      }
    }
    return null;
  }

  PaymentOption? get selectedOption {
    for (final option in loadedQuote?.options ?? const <PaymentOption>[]) {
      if (option.gateway == gateway) {
        return option;
      }
    }
    return null;
  }

  /// Every listed option is disabled (or none was listed).
  bool get noPayableOption =>
      loadedQuote != null &&
      loadedQuote!.options.every((option) => blockOfOption(option) != null);

  bool get canPlace =>
      !placing &&
      loadedQuote != null &&
      selectedOption != null &&
      blockOfOption(selectedOption!) == null;

  CheckoutState copyWith({
    Loadable<List<Country>>? countries,
    String? Function()? country,
    String? Function()? email,
    Loadable<CheckoutQuote>? Function()? quote,
    String? Function()? gateway,
    String? Function()? method,
    bool? acceptTerms,
    bool? termsError,
    bool? placing,
    PlaceProblem? Function()? problem,
    Failure? Function()? failure,
    String? Function()? failureText,
    CheckoutEvent? event,
    int? eventCount,
  }) => CheckoutState(
    countries: countries ?? this.countries,
    country: country == null ? this.country : country(),
    email: email == null ? this.email : email(),
    quote: quote == null ? this.quote : quote(),
    gateway: gateway == null ? this.gateway : gateway(),
    method: method == null ? this.method : method(),
    acceptTerms: acceptTerms ?? this.acceptTerms,
    termsError: termsError ?? this.termsError,
    placing: placing ?? this.placing,
    problem: problem == null ? this.problem : problem(),
    failure: failure == null ? this.failure : failure(),
    failureText: failureText == null ? this.failureText : failureText(),
    event: event ?? this.event,
    eventCount: eventCount ?? this.eventCount,
  );
}

/// The signed-in checkout: country (from the profile when it is a checkout country), the cart
/// repriced for it (`validate`), the payment options with their charge currency and amount, the
/// terms, and place order through the `PlaceOrder` use case (idempotency key per body, duplicate,
/// `next: null`). A display currency or language change re-quotes.
@riverpod
class CheckoutController extends _$CheckoutController {
  int _generation = 0;

  @override
  CheckoutState build() {
    ref.watch(pricingContextProvider);
    unawaited(_start());
    return const CheckoutState();
  }

  Future<void> retry() async {
    state = const CheckoutState();
    await _start();
  }

  /// Re-quotes the current country (after a failed quote, or "Try again").
  Future<void> requote() async {
    final country = state.country;
    if (country != null) {
      await _quote(country);
    }
  }

  Future<void> selectCountry(String code) async {
    if (code == state.country) {
      return;
    }
    state = state.copyWith(
      country: () => code,
      problem: () => null,
      failure: () => null,
      failureText: () => null,
    );
    await _quote(code);
  }

  void selectOption(String gateway) {
    final option = state.loadedQuote?.options
        .where((o) => o.gateway == gateway)
        .firstOrNull;
    if (option == null || blockOfOption(option) != null) {
      return;
    }
    state = state.copyWith(
      gateway: () => gateway,
      method: () => gateway == state.gateway ? state.method : null,
    );
  }

  void selectMethod(String code) => state = state.copyWith(method: () => code);

  void setAcceptTerms({required bool accepted}) => state = state.copyWith(
    acceptTerms: accepted,
    termsError: accepted ? false : state.termsError,
  );

  /// Places the order with the amounts the customer was shown. The payment flow takes over on
  /// success ([OpenPayment]).
  Future<void> placeOrder() async {
    final quote = state.loadedQuote;
    final option = state.selectedOption;
    final country = state.country;
    if (!state.canPlace || quote == null || option == null || country == null) {
      return;
    }
    if (!state.acceptTerms) {
      state = state.copyWith(termsError: true);
      return;
    }
    final generation = _generation;
    state = state.copyWith(
      placing: true,
      problem: () => null,
      failure: () => null,
      failureText: () => null,
    );
    final total = quote.displayTotal;
    final result = await ref
        .read(placeOrderProvider)
        .call(
          PlaceOrderRequest(
            country: country,
            gateway: option.gateway,
            method: state.method,
            currency: total.currency,
            expectedTotal: total.amountText,
            acceptTerms: true,
          ),
        );
    if (!ref.mounted || generation != _generation) {
      return;
    }
    switch (result) {
      case Ok(:final value):
        await _placed(value, option);
      case Err(:final failure):
        await _refused(failure, previous: total);
    }
  }

  Future<void> _placed(PlaceOrderOutcome outcome, PaymentOption option) async {
    final flow = ref.read(paymentFlowProvider.notifier);
    // The order consumed the cart: the cart tab and its badge reload.
    ref.read(cartRevisionProvider.notifier).bump();
    unawaited(ref.read(cartCountProvider.notifier).refresh());
    switch (outcome) {
      case PlaceOrderPay(:final order, :final next):
        unawaited(
          flow.start(
            orderPublicId: order.publicId,
            next: next,
            gatewayLabel: option.label,
            orderNumber: order.orderNumber,
            paymentTotal: order.paymentTotal ?? option.paymentAmount,
            expiresAt: order.expiresAt,
          ),
        );
        _emit(OpenPayment(order.publicId), placing: false);
      case PlaceOrderDuplicate(:final order):
        unawaited(flow.resume(order.publicId));
        _emit(OpenPayment(order.publicId), placing: false);
      case PlaceOrderPaymentNotStarted(:final order, :final failure):
        flow.notStarted(
          orderPublicId: order.publicId,
          failure: failure,
          gatewayLabel: option.label,
          orderNumber: order.orderNumber,
          paymentTotal: order.paymentTotal ?? option.paymentAmount,
          expiresAt: order.expiresAt,
        );
        _emit(OpenPayment(order.publicId), placing: false);
    }
  }

  Future<void> _refused(Failure failure, {required Money previous}) async {
    switch (failure) {
      case DomainFailure(code: 'price_changed'):
        state = state.copyWith(placing: false);
        await requote();
        if (!ref.mounted) {
          return;
        }
        ref.read(cartRevisionProvider.notifier).bump();
        _emit(
          PriceChanged(
            previous: previous,
            current: state.loadedQuote?.displayTotal,
          ),
        );
      case DomainFailure(code: 'cart_empty'):
        state = state.copyWith(placing: false);
        ref.read(cartRevisionProvider.notifier).bump();
        _emit(const BackToCart());
      case DomainFailure(code: 'rate_stale' || 'rate_missing'):
        state = state.copyWith(
          placing: false,
          problem: () => PlaceProblem.rateStale,
          failure: () => failure,
        );
        await requote();
      case DomainFailure(code: 'item_unavailable', :final message):
        state = state.copyWith(
          placing: false,
          problem: () => PlaceProblem.cartNeedsAttention,
          failure: () => failure,
          failureText: () => message,
        );
      case DomainFailure(:final message):
        // gateway_not_eligible, coupon_invalid, order_total_invalid, payment_currency_below_margin:
        // the server's localized text, and fresh options.
        state = state.copyWith(
          placing: false,
          problem: () => PlaceProblem.other,
          failure: () => failure,
          failureText: () => message,
        );
        await requote();
      case ValidationFailure(:final fieldErrors):
        final itemError = fieldErrors.entries
            .where((e) => e.key.startsWith('items.') && e.value.isNotEmpty)
            .map((e) => e.value.first)
            .firstOrNull;
        final termsError = fieldErrors.containsKey('consents.terms');
        final other = fieldErrors.entries
            .where(
              (e) =>
                  !e.key.startsWith('items.') &&
                  e.key != 'consents.terms' &&
                  e.value.isNotEmpty,
            )
            .map((e) => e.value.first)
            .firstOrNull;
        state = state.copyWith(
          placing: false,
          termsError: termsError,
          acceptTerms: termsError ? false : state.acceptTerms,
          problem: () => itemError != null
              ? PlaceProblem.cartNeedsAttention
              : other != null
              ? PlaceProblem.other
              : null,
          failure: () => itemError != null || other != null ? failure : null,
          failureText: () => itemError ?? other,
        );
      case ConflictFailure(code: 'idempotency_in_progress'):
        state = state.copyWith(
          placing: false,
          problem: () => PlaceProblem.inProgress,
          failure: () => failure,
        );
      case UnauthenticatedFailure():
        // `guest_checkout_disabled` or an expired session (owner decision 4): sign in again; the
        // guard sends the customer to sign in and back to checkout.
        state = state.copyWith(placing: false);
        await ref.read(sessionProvider.notifier).signOut();
      case _:
        state = state.copyWith(
          placing: false,
          problem: () => PlaceProblem.other,
          failure: () => failure,
        );
    }
  }

  void _emit(CheckoutEvent event, {bool? placing}) => state = state.copyWith(
    event: event,
    eventCount: state.eventCount + 1,
    placing: placing,
  );

  Future<void> _start() async {
    final generation = ++_generation;
    final config = ref.read(latestAppConfigProvider);
    if (config != null &&
        (!config.features.checkoutEnabled ||
            config.features.webCheckoutRedirect)) {
      // The page shows the disabled or "Complete purchase on boxcards.shop" state instead.
      return;
    }
    final countriesCall = ref.read(catalogRepositoryProvider).countries();
    final profileCall = ref.read(accountRepositoryProvider).profile();
    final countries = await countriesCall;
    final profile = (await profileCall).valueOrNull;
    if (!ref.mounted || generation != _generation) {
      return;
    }
    switch (countries) {
      case Err(:final failure):
        state = state.copyWith(countries: Failed(failure));
        return;
      case Ok(:final value):
        final profileCountry = profile?.countryCode;
        final initial = value.any((c) => c.code == profileCountry)
            ? profileCountry
            : null;
        state = state.copyWith(
          countries: Loaded(value),
          email: () => profile?.email,
          country: () => state.country ?? initial,
        );
    }
    final country = state.country;
    if (country != null) {
      await _quote(country);
    }
  }

  Future<void> _quote(String country) async {
    final generation = ++_generation;
    state = state.copyWith(quote: () => const Loadable.loading());
    final checkout = ref.read(checkoutRepositoryProvider);
    final validated = await checkout.validate(country: country);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    final Cart cart;
    switch (validated) {
      case Err(failure: DomainFailure(code: 'cart_empty')):
        state = state.copyWith(quote: () => null);
        _emit(const BackToCart());
        return;
      case Err(:final failure):
        state = state.copyWith(quote: () => Failed(failure));
        return;
      case Ok(:final value):
        cart = value;
    }
    final options = await checkout.paymentOptions(country: country);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    switch (options) {
      case Err(:final failure):
        state = state.copyWith(quote: () => Failed(failure));
      case Ok(:final value):
        final keep = value
            .where(
              (o) => o.gateway == state.gateway && blockOfOption(o) == null,
            )
            .firstOrNull;
        final first = value.where((o) => blockOfOption(o) == null).firstOrNull;
        final chosen = keep ?? first;
        state = state.copyWith(
          quote: () => Loaded(CheckoutQuote(cart: cart, options: value)),
          gateway: () => chosen?.gateway,
          method: () => keep != null ? state.method : null,
        );
    }
  }
}
