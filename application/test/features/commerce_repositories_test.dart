import 'package:application/core/network/failure.dart';
import 'package:application/core/network/interceptors/cart_token_interceptor.dart';
import 'package:application/core/network/interceptors/guest_order_token_interceptor.dart';
import 'package:application/core/network/request_extras.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/cart/data/datasources/cart_remote_data_source.dart';
import 'package:application/features/cart/data/repositories/cart_repository_impl.dart';
import 'package:application/features/checkout/data/datasources/checkout_remote_data_source.dart';
import 'package:application/features/checkout/data/repositories/checkout_repository_impl.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/orders/data/datasources/orders_remote_data_source.dart';
import 'package:application/features/orders/data/repositories/orders_repository_impl.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/features/payments/data/datasources/payments_remote_data_source.dart';
import 'package:application/features/payments/data/repositories/payments_repository_impl.dart';
import 'package:application/shared/domain/entities/payment_next.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/repo_harness.dart';

const order1 = '01jf0000000000000000000001';
const unit2 = '01JF0000000000000000000002';

const guestRequest = PlaceOrderRequest(
  country: 'US',
  gateway: 'stripe',
  currency: 'USD',
  expectedTotal: '10.00',
  acceptTerms: true,
  email: 'guest@example.com',
);

void main() {
  group('CartRepository', () {
    CartRepositoryImpl repoOn(Harness h) =>
        CartRepositoryImpl(CartRemoteDataSource(h.dio), h.store);

    test('a guest cart: token captured, sent back, items, coupon', () async {
      final h = Harness();
      final repo = repoOn(h);

      h.serveFixture('cart/cart_empty');
      expect(okValue(await repo.get()), isNull);
      expect(
        h.adapter.sentHeaders.single.containsKey(CartTokenInterceptor.header),
        isFalse,
      );

      h.serveFixture('cart/add_item_created');
      final added = okValue(
        await repo.addItem(
          variantPublicId: '01jf0000000000000000000002',
          quantity: 1,
        ),
      );
      expect(added.items.single.deliveryValues, isEmpty);
      expect(added.totals.total.amountText, '10.00');
      expect(h.stored[SecureStore.cartTokenKey], 'fixture-cart-token');

      h.serveFixture('cart/cart_ok');
      final cart = okValue(await repo.get());
      expect(cart?.items.single.productName, 'Orbit Card');
      expect(
        h.adapter.sentHeaders.single[CartTokenInterceptor.header],
        'fixture-cart-token',
      );

      h.serveFixture('cart/update_item_ok');
      expect(
        okValue(await repo.updateItem(1, quantity: 2)).items.single.quantity,
        2,
      );

      h.serveFixture('cart/coupon_applied');
      final discounted = okValue(await repo.applyCoupon('save5'));
      expect(discounted.coupon?.applied, isTrue);
      expect(discounted.totals.discount.amountText, '5.00');

      h.serveFixture('cart/coupon_removed');
      expect(okValue(await repo.removeCoupon()).coupon, isNull);

      h.serveFixture('cart/remove_item_ok');
      expect(okValue(await repo.removeItem(1)).items, isEmpty);
    });

    test('maps cart errors', () async {
      final h = Harness(
        stored: {SecureStore.cartTokenKey: 'fixture-cart-token'},
      );
      final repo = repoOn(h);

      h.serveFixture('cart/update_item_validation_failed');
      failureOf<ValidationFailure>(await repo.updateItem(1, quantity: 50));

      h.serveFixture('cart/item_not_found');
      failureOf<NotFoundFailure>(await repo.updateItem(999999, quantity: 1));

      h.serveFixture('cart/coupon_invalid');
      final coupon = failureOf<DomainFailure>(await repo.applyCoupon('NOPE'));
      expect(coupon.code, 'coupon_invalid');
      expect(coupon.message, isNotEmpty);

      h.serveFixture('cart/add_item_validation_failed');
      final invalid = failureOf<ValidationFailure>(
        await repo.addItem(variantPublicId: '', quantity: 0),
      );
      expect(invalid.firstErrorFor('variant_id'), isNotNull);
    });

    test('a signed-in customer drops the adopted guest cart token', () async {
      final h = Harness(
        stored: {
          SecureStore.accessTokenKey: 'access',
          SecureStore.refreshTokenKey: 'refresh',
          SecureStore.cartTokenKey: 'guest-cart',
        },
      )..serveFixture('cart/add_item_customer_created');

      okValue(
        await repoOn(
          h,
        ).addItem(variantPublicId: '01jf0000000000000000000002', quantity: 1),
      );

      expect(
        h.adapter.sentHeaders.single[CartTokenInterceptor.header],
        'guest-cart',
      );
      expect(h.adapter.sentHeaders.single['Authorization'], 'Bearer access');
      expect(h.stored.containsKey(SecureStore.cartTokenKey), isFalse);
    });
  });

  group('CheckoutRepository', () {
    CheckoutRepositoryImpl repoOn(Harness h) =>
        CheckoutRepositoryImpl(CheckoutRemoteDataSource(h.dio), h.store);

    test('validates, lists payment options and maps their errors', () async {
      final h = Harness(
        stored: {SecureStore.cartTokenKey: 'fixture-cart-token'},
      );
      final repo = repoOn(h);

      h.serveFixture('checkout/validate_ok');
      expect(
        okValue(await repo.validate(country: 'US', email: 'guest@example.com'))
            .country,
        'US',
      );

      h.serveFixture('checkout/payment_options_ok');
      final options = okValue(
        await repo.paymentOptions(country: 'US', email: 'guest@example.com'),
      );
      expect(options.single.gateway, 'stripe');
      expect(options.single.paymentAmount.amountText, '10.00');
      expect(options.single.notice, isNull);

      h.serveFixture('checkout/validate_validation_failed');
      failureOf<ValidationFailure>(await repo.validate(country: 'ZZ'));

      h.serveFixture('checkout/validate_cart_empty');
      expect(
        failureOf<DomainFailure>(await repo.validate(country: 'US')).code,
        'cart_empty',
      );
    });

    test(
      'places a guest order, stores its guest token and sends the key',
      () async {
        final h = Harness(
          stored: {SecureStore.cartTokenKey: 'fixture-cart-token'},
        )..serveFixture('checkout/place_order_created');

        final placed = okValue(
          await repoOn(h).placeOrder(guestRequest, idempotencyKey: 'key-1'),
        );

        expect(placed.duplicate, isFalse);
        expect(placed.replayed, isFalse);
        expect(placed.order.orderNumber, 'FIXTURE-0001');
        final next = placed.next! as StripeNext;
        expect(next.clientSecret, 'fixture-client-secret');
        expect(next.returnUrl?.path, '/app/payments/return/$order1');
        expect(
          h.stored['${SecureStore.guestOrderTokenPrefix}$order1'],
          'fixture-token',
        );
        final request = h.adapter.requests.single;
        expect(request.headers['Idempotency-Key'], 'key-1');
        expect((request.data as Map)['client'], 'app');
      },
    );

    test('reads a replay, a duplicate and next: null', () async {
      final h = Harness(
        stored: {SecureStore.cartTokenKey: 'fixture-cart-token'},
      );
      final repo = repoOn(h);

      h.serveFixture('checkout/place_order_replayed');
      final replay = okValue(
        await repo.placeOrder(guestRequest, idempotencyKey: 'key-1'),
      );
      expect(replay.replayed, isTrue);
      expect(replay.next, isNull);

      h.serveFixture('checkout/place_order_duplicate');
      final duplicate = okValue(
        await repo.placeOrder(guestRequest, idempotencyKey: 'key-2'),
      );
      expect(duplicate.duplicate, isTrue);
      expect(duplicate.order.publicId, order1);
      expect(duplicate.order.displayTotal, isNull);

      h.serveFixture('checkout/place_order_next_null');
      expect(
        okValue(await repo.placeOrder(guestRequest, idempotencyKey: 'key-3'))
            .next,
        isNull,
      );
    });

    test('a customer order carries no guest token', () async {
      final h = Harness(
        stored: {
          SecureStore.accessTokenKey: 'access',
          SecureStore.refreshTokenKey: 'refresh',
        },
      )..serveFixture('checkout/place_order_customer_created');

      okValue(
        await repoOn(h).placeOrder(guestRequest, idempotencyKey: 'key-1'),
      );

      expect(
        h.stored.keys.where(
          (k) => k.startsWith(SecureStore.guestOrderTokenPrefix),
        ),
        isEmpty,
      );
    });

    test('maps place-order errors', () async {
      final h = Harness(
        stored: {SecureStore.cartTokenKey: 'fixture-cart-token'},
      );
      final repo = repoOn(h);

      h.serveFixture('checkout/place_order_idempotency_mismatch');
      expect(
        failureOf<ConflictFailure>(
          await repo.placeOrder(guestRequest, idempotencyKey: 'k'),
        ).code,
        'idempotency_mismatch',
      );

      h.serveFixture('checkout/place_order_price_changed');
      final changed = failureOf<DomainFailure>(
        await repo.placeOrder(
          guestRequest.copyWith(expectedTotal: '9.00'),
          idempotencyKey: 'k2',
        ),
      );
      expect(changed.code, 'price_changed');
      expect(changed.errors['expected_total'], isNotEmpty);

      h.serveFixture('checkout/place_order_validation_failed');
      failureOf<ValidationFailure>(
        await repo.placeOrder(
          guestRequest.copyWith(acceptTerms: false),
          idempotencyKey: 'k3',
        ),
      );
    });

    test('reads the status with the stored guest token', () async {
      final h = Harness(
        stored: {'${SecureStore.guestOrderTokenPrefix}$order1': 'guest-1'},
      )..serveFixture('checkout/status_ok');

      final status = okValue(await repoOn(h).status(order1));

      expect(status.isPaid, isFalse);
      expect(
        h.adapter.sentHeaders.single[GuestOrderTokenInterceptor.header],
        'guest-1',
      );

      h.serveFixture('checkout/status_not_found');
      failureOf<NotFoundFailure>(await repoOn(h).status(order1));
    });

    test('opens the delivery link, sends and verifies the email code', () async {
      final h = Harness();
      final repo = repoOn(h);

      h.serveFixture('guest_orders/access_link_ok');
      final access = okValue(
        await repo.openGuestLink(
          Uri.parse(
            'https://boxcards.test/api/v1/checkout/guest/orders/$order1/access?expires=fixture-expires&signature=fixture-signature',
          ),
        ),
      );
      expect(access.otpRequired, isTrue);
      expect(
        h.stored['${SecureStore.guestOrderTokenPrefix}$order1'],
        'fixture-token',
      );

      h.serveFixture('guest_orders/otp_send_ok');
      okValue(
        await repo.sendGuestOtp(
          orderPublicId: order1,
          email: 'guest@example.com',
        ),
      );

      h.stored['${SecureStore.guestOrderTokenPrefix}$order1'] = 'link-token';
      h.serveFixture('guest_orders/otp_invalid');
      expect(
        failureOf<DomainFailure>(
          await repo.verifyGuestOtp(orderPublicId: order1, code: '000000'),
        ).code,
        'otp_invalid',
      );
      expect(
        h.adapter.sentHeaders.single[GuestOrderTokenInterceptor.header],
        'link-token',
      );

      h.serveFixture('guest_orders/otp_verify_ok');
      okValue(await repo.verifyGuestOtp(orderPublicId: order1, code: '123456'));
      expect(
        h.stored['${SecureStore.guestOrderTokenPrefix}$order1'],
        'fixture-token',
      );
      expect(h.adapter.requests.single.extra[RequestExtras.sensitive], isTrue);
    });
  });

  group('PaymentsRepository', () {
    PaymentsRepositoryImpl repoOn(Harness h) =>
        PaymentsRepositoryImpl(PaymentsRemoteDataSource(h.dio));

    test('models every next type from retry-payment', () async {
      final h = Harness();
      final repo = repoOn(h);

      h.serveFixture('payments/retry_payment_stripe');
      expect(okValue(await repo.retryPayment(order1)), isA<StripeNext>());
      expect((h.adapter.requests.single.data as Map)['client'], 'app');
      expect(h.adapter.requests.single.extra[RequestExtras.noRetry], isTrue);

      h.serveFixture('payments/retry_payment_paypal');
      final paypal = okValue(await repo.retryPayment(order1)) as PayPalNext;
      expect(paypal.approveUrl.host, 'www.sandbox.paypal.com');
      expect(paypal.providerOrderId, 'PAYPAL-ORDER-1');

      h.serveFixture('payments/retry_payment_redirect');
      final redirect = okValue(await repo.retryPayment(order1)) as RedirectNext;
      expect(redirect.redirectUrl.host, 'accept.paymob.com');
      expect(redirect.gateway, 'paymob');
    });

    test('reads the wallet next from place-order', () async {
      final h = Harness(
        stored: {
          SecureStore.accessTokenKey: 'a',
          SecureStore.refreshTokenKey: 'r',
        },
      )..serveFixture('payments/place_order_wallet');
      final placed = okValue(
        await CheckoutRepositoryImpl(
          CheckoutRemoteDataSource(h.dio),
          h.store,
        ).placeOrder(
          guestRequest.copyWith(gateway: 'wallet', email: null),
          idempotencyKey: 'k',
        ),
      );
      expect(placed.next, isA<WalletNext>());
    });

    test(
      'verifies, captures and reads the status; only the server says paid',
      () async {
        final h = Harness();
        final repo = repoOn(h);

        h.serveFixture('payments/verify_payment_processing');
        final processing = okValue(await repo.verifyPayment(order1));
        expect(processing.isPaid, isFalse);
        expect(processing.status, 'payment_processing');

        h.serveFixture('payments/verify_payment_paid');
        expect(okValue(await repo.verifyPayment(order1)).isPaid, isTrue);

        h.serveFixture('payments/paypal_capture_ok');
        expect(okValue(await repo.capturePayPal(order1)).isPaid, isTrue);

        h.serveFixture('payments/status_paid');
        expect(okValue(await repo.status(order1)).isPaid, isTrue);
      },
    );

    test('maps payment errors', () async {
      final h = Harness();
      final repo = repoOn(h);

      h.serveFixture('payments/retry_payment_not_payable');
      expect(
        failureOf<ConflictFailure>(await repo.retryPayment(order1)).code,
        'order_not_payable',
      );

      h.serveFixture('payments/capture_not_supported');
      expect(
        failureOf<DomainFailure>(await repo.capturePayPal(order1)).code,
        'capability_not_supported',
      );

      h.serveFixture('payments/verify_payment_not_found');
      failureOf<NotFoundFailure>(await repo.verifyPayment(order1));
    });
  });

  group('OrdersRepository', () {
    OrdersRepositoryImpl repoOn(Harness h) =>
        OrdersRepositoryImpl(OrdersRemoteDataSource(h.dio));

    test('lists and filters orders', () async {
      final h = Harness()..serveFixture('orders/list_ok');
      final page = okValue(await repoOn(h).list(const OrdersQuery()));
      expect(page.items, hasLength(2));
      expect(page.items.first.paymentTotal?.amountText, '15.00');
      expect(page.items.last.paymentTotal, isNull);

      h.serveFixture('orders/list_filtered');
      okValue(
        await repoOn(h).list(
          const OrdersQuery(
            status: 'completed',
            from: '2026-11-01',
            to: '2026-11-10',
          ),
        ),
      );

      h.serveFixture('orders/list_validation_failed');
      failureOf<ValidationFailure>(
        await repoOn(h).list(const OrdersQuery(status: 'lost')),
      );
    });

    test('shows an order with units and never a code', () async {
      final h = Harness()..serveFixture('orders/detail_ok');
      final detail = okValue(await repoOn(h).detail(order1));

      final unit = detail.items.single.units.single;
      expect(unit.publicId, unit2);
      expect(unit.revealable, isTrue);
      expect(unit.maskedCode, '****0000');
      expect(detail.items.single.unitPrice?.amountText, '10.00');
      expect(detail.payments.single.provider, 'stripe');
      expect(detail.refundState, 'none');

      h.serveFixture('orders/detail_not_found');
      failureOf<NotFoundFailure>(
        await repoOn(h).detail('01JF0000000000000000000001'),
      );
    });

    test('shows a guest order with its access proofs', () async {
      final h = Harness(
        stored: {'${SecureStore.guestOrderTokenPrefix}$order1': 'otp-token'},
      )..serveFixture('guest_orders/order_detail');

      final guest = okValue(await repoOn(h).guestDetail(order1));

      expect(guest.canReveal, isTrue);
      expect(guest.order.summary.status, 'paid');
      expect(
        h.adapter.sentHeaders.single[GuestOrderTokenInterceptor.header],
        'otp-token',
      );

      h.serveFixture('guest_orders/order_detail_not_found');
      failureOf<NotFoundFailure>(await repoOn(h).guestDetail(order1));
    });
  });
}
