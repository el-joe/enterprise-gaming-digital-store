import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/cart/cart_providers.dart';
import 'package:application/features/cart/presentation/controllers/cart_controller.dart';
import 'package:application/features/checkout/checkout_providers.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/checkout/presentation/controllers/checkout_controller.dart';
import 'package:application/features/payments/payment_flow.dart';
import 'package:application/shared/domain/entities/cart.dart';
import 'package:application/shared/domain/entities/payment_next.dart';
import 'package:flutter_riverpod/misc.dart' show ProviderListenable;
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/fakes.dart';
import '../../helpers/pump_app.dart';

/// Lets queued fake futures (and the instant status polling) complete.
Future<void> settle() async {
  for (var i = 0; i < 300; i++) {
    await Future<void>.delayed(Duration.zero);
  }
}

void keep<T>(TestHarness h, ProviderListenable<T> provider) {
  final sub = h.container.listen(provider, (_, _) {});
  addTearDown(sub.close);
}

PlaceOrderResponse response({
  PlacedOrder? order,
  bool duplicate = false,
  bool replayed = false,
  Object? next = _paypal,
}) => PlaceOrderResponse(
  order: order ?? placed(),
  duplicate: duplicate,
  replayed: replayed,
  next: identical(next, _paypal) ? paypalNext : null,
);

const _paypal = Object();

void main() {
  group('cart controller', () {
    Future<TestHarness> cartHarness({
      bool signedIn = true,
      Result<Cart?>? cart,
      void Function(TestHarness h)? arrange,
    }) async {
      final h = buildHarness(signedIn: signedIn);
      addTearDown(h.dispose);
      h.cart.getResult = cart ?? Ok(cartWith());
      arrange?.call(h);
      keep(h, cartControllerProvider);
      keep(h, cartCountProvider);
      await settle();
      return h;
    }

    CartPageState stateOf(TestHarness h) => h.read(cartControllerProvider);
    CartController controllerOf(TestHarness h) =>
        h.read(cartControllerProvider.notifier);

    test('loads the cart and counts its units on the badge', () async {
      final h = await cartHarness(cart: Ok(cartWith(quantity: 3)));
      expect(stateOf(h).content, isA<Loaded<Cart?>>());
      expect(stateOf(h).cart!.items.single.quantity, 3);
      expect(stateOf(h).canCheckout, isTrue);
      expect(h.read(cartCountProvider), 3);
    });

    test(
      'a guest without a cart token has an empty cart and no call',
      () async {
        final h = await cartHarness(signedIn: false);
        expect(stateOf(h).isEmpty, isTrue);
        expect(h.cart.getCalls, 0);
      },
    );

    test('a guest with a cart token reads it', () async {
      final h = await cartHarness(
        signedIn: false,
        arrange: (h) =>
            h.secure[SecureStore.cartTokenKey] = 'fixture-cart-token',
      );
      expect(stateOf(h).cart, isNotNull);
      expect(h.cart.getCalls, greaterThan(0));
    });

    test('offline and server failures are kept until retry', () async {
      final h = await cartHarness(cart: const Err(NetworkFailure()));
      expect(
        (stateOf(h).content as Failed<Cart?>).failure,
        isA<NetworkFailure>(),
      );
      h.cart.getResult = Ok(cartWith());
      await controllerOf(h).retry();
      expect(stateOf(h).cart, isNotNull);
    });

    test(
      'quantity change calls PATCH and the badge follows the returned cart',
      () async {
        final h = await cartHarness(
          arrange: (h) =>
              h.cart.onUpdate = (_, quantity) =>
                  Ok(cartWith(quantity: quantity)),
        );
        final line = stateOf(h).cart!.items.single;
        await controllerOf(h).setQuantity(line, 2);
        expect(h.cart.updates.single, (id: line.id, quantity: 2));
        expect(stateOf(h).cart!.items.single.quantity, 2);
        expect(h.read(cartCountProvider), 2);
        // 0 and above 10 are clamped; the same quantity sends nothing.
        await controllerOf(h).setQuantity(stateOf(h).cart!.items.single, 2);
        expect(h.cart.updates, hasLength(1));
      },
    );

    test(
      'remove calls DELETE; the last line removed shows the empty cart',
      () async {
        final h = await cartHarness(
          arrange: (h) =>
              h.cart.onRemove = (_) => Ok(cartWith().copyWith(items: const [])),
        );
        await controllerOf(h).remove(stateOf(h).cart!.items.single);
        expect(h.cart.removals, hasLength(1));
        expect(stateOf(h).isEmpty, isTrue);
        expect(h.read(cartCountProvider), 0);
      },
    );

    test('a refused quantity shows errors.quantity beside its line', () async {
      final h = await cartHarness(
        arrange: (h) =>
            h.cart.onUpdate = (_, _) =>
                Err(validation({'quantity': 'At most 5 per order.'})),
      );
      final line = stateOf(h).cart!.items.single;
      await controllerOf(h).setQuantity(line, 9);
      expect(stateOf(h).lineErrors[line.id], 'At most 5 per order.');
      expect(stateOf(h).busyLines, isEmpty);
    });

    test('another write failure is reported once for the snackbar', () async {
      final h = await cartHarness(
        arrange: (h) =>
            h.cart.onRemove = (_) => const Err(ServerFailure(statusCode: 500)),
      );
      await controllerOf(h).remove(stateOf(h).cart!.items.single);
      expect(stateOf(h).actionFailureCount, 1);
      expect(stateOf(h).actionFailure, isA<ServerFailure>());
      expect(stateOf(h).cart, isNotNull);
    });

    test('coupon applied, refused (coupon_invalid) and removed', () async {
      final h = await cartHarness(
        arrange: (h) => h.cart.onApplyCoupon = (code) => code == 'SAVE5'
            ? Ok(fixtureCouponCart())
            : const Err(
                DomainFailure(
                  'coupon_invalid',
                  message: 'This coupon cannot be used.',
                  errors: {
                    'code': ['This coupon is for first orders only.'],
                  },
                ),
              ),
      );
      await controllerOf(h).applyCoupon('  FIRST50 ');
      expect(h.cart.coupons, ['FIRST50']);
      expect(stateOf(h).couponError, 'This coupon is for first orders only.');

      await controllerOf(h).applyCoupon('SAVE5');
      expect(stateOf(h).couponError, isNull);
      expect(stateOf(h).cart!.coupon!.applied, isTrue);
      expect(stateOf(h).cart!.totals.total.amountText, '15.00');

      h.cart.onRemoveCoupon = () => Ok(cartWith());
      await controllerOf(h).removeCoupon();
      expect(h.cart.couponRemovals, 1);
      expect(stateOf(h).cart!.coupon, isNull);
    });

    test(
      'a blocked line disables checkout and shows the pricing notice',
      () async {
        final h = await cartHarness(
          cart: Ok(cartWith(blockReason: 'rate_stale')),
        );
        expect(stateOf(h).canCheckout, isFalse);
        expect(stateOf(h).pricesUpdating, isTrue);
      },
    );

    test('an order placed elsewhere (CartRevision) reloads the cart', () async {
      final h = await cartHarness();
      final before = h.cart.getCalls;
      h.cart.getResult = const Ok(null);
      h.read(cartRevisionProvider.notifier).bump();
      await settle();
      expect(h.cart.getCalls, greaterThan(before));
      expect(stateOf(h).isEmpty, isTrue);
    });
  });

  group('checkout mode (owner decision 3)', () {
    test('in-app by default, web redirect, disabled', () {
      final h = buildHarness();
      addTearDown(h.dispose);
      final config = fixtureAppConfig();
      h.read(latestAppConfigProvider.notifier).set(config);
      expect(h.read(checkoutModeProvider), CheckoutMode.inApp);
      h
          .read(latestAppConfigProvider.notifier)
          .set(
            config.copyWith(
              features: config.features.copyWith(webCheckoutRedirect: true),
            ),
          );
      expect(h.read(checkoutModeProvider), CheckoutMode.web);
      h
          .read(latestAppConfigProvider.notifier)
          .set(
            config.copyWith(
              features: config.features.copyWith(checkoutEnabled: false),
            ),
          );
      expect(h.read(checkoutModeProvider), CheckoutMode.disabled);
    });

    test(
      'web redirect opens links.web_checkout in the system browser',
      () async {
        final h = buildHarness(signedIn: true);
        addTearDown(h.dispose);
        final config = fixtureAppConfig();
        h
            .read(latestAppConfigProvider.notifier)
            .set(
              config.copyWith(
                features: config.features.copyWith(webCheckoutRedirect: true),
              ),
            );
        keep(h, cartControllerProvider);
        await settle();
        expect(
          await h.read(cartControllerProvider.notifier).openWebCheckout(),
          isTrue,
        );
        expect(h.native.opened, [config.links.webCheckout]);
      },
    );

    test(
      'the in-app checkout does not load while web redirect is on',
      () async {
        final h = buildHarness(signedIn: true);
        addTearDown(h.dispose);
        final config = fixtureAppConfig();
        h
            .read(latestAppConfigProvider.notifier)
            .set(
              config.copyWith(
                features: config.features.copyWith(webCheckoutRedirect: true),
              ),
            );
        keep(h, checkoutControllerProvider);
        await settle();
        expect(h.checkout.validated, isEmpty);
        expect(h.checkout.placed, isEmpty);
      },
    );
  });

  group('checkout guard (owner decision 4)', () {
    const available = AppAvailability.available;
    test('a guest is sent to sign in with the way back', () {
      final redirect = redirectFor(
        state: const GuardState(
          session: SessionStatus.signedOut,
          availability: available,
        ),
        location: Uri.parse(Routes.checkout.path),
        access: Routes.checkout.access,
      );
      expect(redirect, '/login?from=%2Fcheckout');
    });

    test('after signing in the customer comes back to checkout', () {
      final redirect = redirectFor(
        state: const GuardState(
          session: SessionStatus.signedIn,
          availability: available,
        ),
        location: Uri.parse('/login?from=%2Fcheckout'),
        access: Routes.login.access,
      );
      expect(redirect, Routes.checkout.path);
    });

    test('the payment screen is signed-in only too', () {
      expect(Routes.payment.access, RouteAccess.signedIn);
    });
  });

  group('checkout controller', () {
    Future<TestHarness> checkoutHarness({
      void Function(TestHarness h)? arrange,
      Map<String, String> secureData = const {},
    }) async {
      final h = buildHarness(signedIn: true, secureData: secureData);
      addTearDown(h.dispose);
      arrange?.call(h);
      keep(h, checkoutControllerProvider);
      keep(h, paymentFlowProvider);
      await settle();
      return h;
    }

    CheckoutState stateOf(TestHarness h) => h.read(checkoutControllerProvider);
    CheckoutController controllerOf(TestHarness h) =>
        h.read(checkoutControllerProvider.notifier);

    Future<void> acceptAndPlace(TestHarness h) async {
      controllerOf(h).setAcceptTerms(accepted: true);
      await controllerOf(h).placeOrder();
      await settle();
    }

    test('starts with the profile country, quotes it and picks the first payable option', () async {
      final h = await checkoutHarness();
      final state = stateOf(h);
      expect(state.country, 'US');
      expect(state.email, isNotNull);
      expect(h.checkout.validated, ['US']);
      expect(h.checkout.optionCalls, ['US']);
      expect(state.loadedQuote!.displayTotal.amountText, '31.50');
      expect(state.gateway, 'paymob');
    });

    test(
      'a profile country that checkout does not sell to is not preselected',
      () async {
        final h = await checkoutHarness(
          arrange: (h) =>
              when(h.account.profile)
                  .thenAnswer((_) async => Ok(fixtureProfile(country: 'FR'))),
        );
        expect(stateOf(h).country, isNull);
        expect(stateOf(h).quote, isNull);
        await controllerOf(h).selectCountry('EG');
        expect(h.checkout.validated, ['EG']);
        expect(stateOf(h).loadedQuote, isNotNull);
      },
    );

    test('options keep display and charge apart and disabled ones cannot be chosen', () async {
      final h = await checkoutHarness(
        arrange: (h) => h.checkout.onOptions = (_) => Ok([
          option(
            'wallet',
            available: false,
            notice: 'payment_currency_below_margin',
          ),
          option('stripe'),
          option('paymob', notice: 'charged_in_payment_currency'),
        ]),
      );
      final options = stateOf(h).loadedQuote!.options;
      expect(blockOfOption(options[0]), OptionBlock.belowMargin);
      expect(blockOfOption(options[1]), OptionBlock.notInApp);
      expect(blockOfOption(options[2]), isNull);
      expect(stateOf(h).gateway, 'paymob');
      // Display total in USD; Paymob charges EGP (owner decision 1).
      final paymob = stateOf(h).selectedOption!;
      expect(paymob.displayAmount.currency, 'USD');
      expect(paymob.paymentAmount.currency, 'EGP');
      expect(paymob.paymentAmount.amountText, '1530.00');
      controllerOf(h).selectOption('stripe');
      expect(stateOf(h).gateway, 'paymob');
    });

    test('nothing is placed until the terms are accepted', () async {
      final h = await checkoutHarness();
      await controllerOf(h).placeOrder();
      expect(stateOf(h).termsError, isTrue);
      expect(h.checkout.placed, isEmpty);
    });

    test('place order sends the shown total, opens PayPal and goes to the payment screen', () async {
      final h = await checkoutHarness(
        arrange: (h) => h.checkout.placeResults.add(Ok(response())),
      );
      controllerOf(h).selectOption('paypal');
      await acceptAndPlace(h);
      final sent = h.checkout.placed.single;
      expect(sent.key, 'key-1');
      expect(sent.request.gateway, 'paypal');
      expect(sent.request.currency, 'USD');
      expect(sent.request.expectedTotal, '31.50');
      expect(sent.request.toBody()['client'], 'app');
      expect(stateOf(h).event, isA<OpenPayment>());
      final flow = h.read(paymentFlowProvider);
      expect(flow.orderPublicId, orderId);
      expect(flow.phase, PaymentPhase.awaitingProvider);
      expect(h.native.opened.single, (paypalNext as PayPalNext).approveUrl);
      // Nothing was confirmed and nothing is paid.
      expect(h.payments.calls, isEmpty);
      // The pending checkout knows the order for a restart.
      expect(h.secure[SecureStore.pendingCheckoutKey], contains(orderId));
    });

    test('a retry after a network error reuses the idempotency key', () async {
      final h = await checkoutHarness(
        arrange: (h) => h.checkout.placeResults
          ..add(const Err(NetworkFailure()))
          ..add(Ok(response())),
      );
      await acceptAndPlace(h);
      expect(stateOf(h).problem, PlaceProblem.other);
      await controllerOf(h).placeOrder();
      await settle();
      expect(h.checkout.placed.map((p) => p.key), ['key-1', 'key-1']);
      expect(h.issuedKeys, ['key-1']);
      expect(stateOf(h).event, isA<OpenPayment>());
    });

    test('a restart re-sends the same key with the same body', () async {
      final first = await checkoutHarness(
        arrange: (h) =>
            h.checkout.placeResults.add(const Err(TimeoutFailure())),
      );
      await acceptAndPlace(first);
      final stored = Map<String, String>.of(first.secure);
      expect(stored[SecureStore.pendingCheckoutKey], contains('key-1'));

      // A new app process with the same secure storage.
      final second = await checkoutHarness(
        secureData: stored,
        arrange: (h) =>
            h.checkout.placeResults.add(Ok(response(replayed: true))),
      );
      await acceptAndPlace(second);
      expect(second.checkout.placed.single.key, 'key-1');
      expect(second.issuedKeys, isEmpty);
    });

    test('next: null calls retry-payment once with the same order', () async {
      final h = await checkoutHarness(
        arrange: (h) {
          h.checkout.placeResults.add(Ok(response(next: null)));
          h.payments.retryResults = [Ok(redirectNext)];
        },
      );
      await acceptAndPlace(h);
      expect(h.payments.calls.first, 'retry:$orderId');
      expect(h.read(paymentFlowProvider).phase, PaymentPhase.awaitingProvider);
    });

    test(
      'a failed retry-payment shows "payment provider unavailable"',
      () async {
        final h = await checkoutHarness(
          arrange: (h) {
            h.checkout.placeResults.add(Ok(response(next: null)));
            h.payments.retryResults = [
              const Err(ServerFailure(statusCode: 503)),
            ];
          },
        );
        await acceptAndPlace(h);
        expect(h.read(paymentFlowProvider).phase, PaymentPhase.notStarted);
        expect(stateOf(h).event, isA<OpenPayment>());
      },
    );

    test(
      'a duplicate goes to the existing order and asks the server',
      () async {
        final h = await checkoutHarness(
          arrange: (h) {
            h.checkout.placeResults.add(
              Ok(
                response(
                  order: const PlacedOrder(
                    publicId: '01JF0000000000000000000009',
                    status: 'pending_payment',
                  ),
                  duplicate: true,
                  next: null,
                ),
              ),
            );
            h.payments.verifyResults = [Ok(paidStatus)];
          },
        );
        await acceptAndPlace(h);
        expect(
          (stateOf(h).event! as OpenPayment).orderPublicId,
          '01JF0000000000000000000009',
        );
        expect(h.payments.calls.first, 'verify:01JF0000000000000000000009');
        expect(h.payments.count('retry'), 0);
        expect(h.read(paymentFlowProvider).phase, PaymentPhase.paid);
      },
    );

    test('price_changed re-quotes, shows old and new totals and places with a new key', () async {
      final h = await checkoutHarness(
        arrange: (h) => h.checkout.placeResults
          ..add(
            const Err(
              DomainFailure(
                'price_changed',
                message: 'The order total is now 32.10 USD.',
              ),
            ),
          )
          ..add(Ok(response())),
      );
      h.checkout.onValidate = (_) => Ok(cartWith(total: '32.10'));
      await acceptAndPlace(h);
      final event = stateOf(h).event! as PriceChanged;
      expect(event.previous.amountText, '31.50');
      expect(event.current!.amountText, '32.10');
      expect(h.checkout.validated, ['US', 'US']);
      expect(h.payments.calls, isEmpty);

      await controllerOf(h).placeOrder();
      await settle();
      expect(h.checkout.placed.last.request.expectedTotal, '32.10');
      expect(h.checkout.placed.map((p) => p.key), ['key-1', 'key-2']);
    });

    test('cart_empty goes back to the cart', () async {
      final h = await checkoutHarness(
        arrange: (h) =>
            h.checkout.placeResults.add(const Err(DomainFailure('cart_empty'))),
      );
      await acceptAndPlace(h);
      expect(stateOf(h).event, isA<BackToCart>());
    });

    test('an empty cart at validate goes back to the cart', () async {
      final h = await checkoutHarness(
        arrange: (h) =>
            h.checkout.onValidate = (_) =>
                const Err(DomainFailure('cart_empty')),
      );
      expect(stateOf(h).event, isA<BackToCart>());
    });

    test('idempotency_in_progress keeps the key and says so', () async {
      final h = await checkoutHarness(
        arrange: (h) => h.checkout.placeResults
          ..add(const Err(ConflictFailure('idempotency_in_progress')))
          ..add(Ok(response())),
      );
      await acceptAndPlace(h);
      expect(stateOf(h).problem, PlaceProblem.inProgress);
      await controllerOf(h).placeOrder();
      await settle();
      expect(h.checkout.placed.map((p) => p.key), ['key-1', 'key-1']);
    });

    test(
      '401 guest_checkout_disabled signs out (the guard asks to sign in)',
      () async {
        final h = await checkoutHarness(
          arrange: (h) => h.checkout.placeResults.add(
            const Err(UnauthenticatedFailure(code: 'guest_checkout_disabled')),
          ),
        );
        await acceptAndPlace(h);
        expect(h.read(sessionProvider), SessionStatus.signedOut);
      },
    );

    test(
      'an item error in validation_failed points back to the cart',
      () async {
        final h = await checkoutHarness(
          arrange: (h) => h.checkout.placeResults.add(
            Err(validation({'items.1.delivery_values.player_id': 'Bad id.'})),
          ),
        );
        await acceptAndPlace(h);
        expect(stateOf(h).problem, PlaceProblem.cartNeedsAttention);
        expect(stateOf(h).failureText, 'Bad id.');
      },
    );
  });
}
