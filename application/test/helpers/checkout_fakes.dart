import 'dart:async';

import 'package:application/core/money/money.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_link_listener.dart';
import 'package:application/features/account/data/models/account_models.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/checkout/domain/repositories/checkout_repository.dart';
import 'package:application/features/orders/data/models/order_models.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/features/orders/domain/repositories/orders_repository.dart';
import 'package:application/features/payments/domain/repositories/payments_repository.dart';
import 'package:application/shared/data/models/cart_models.dart';
import 'package:application/shared/domain/entities/cart.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:application/shared/domain/entities/payment_next.dart';

import 'fixtures.dart';

const orderId = '01JF0000000000000000000001';

/// `cart/cart_ok`: one Orbit Card line, total USD10.00.
Cart fixtureCartOk() => CartDto.fromJson(
  ApiFixture.load('cart/cart_ok').dataMap['cart'] as Map<String, dynamic>,
).toEntity();

/// `cart/coupon_applied`: SAVE5 applied, total USD15.00.
Cart fixtureCouponCart() => CartDto.fromJson(
  ApiFixture.load('cart/coupon_applied').dataMap['cart']
      as Map<String, dynamic>,
).toEntity();

/// `account/profile_ok`, optionally with a country.
Profile fixtureProfile({String? country = 'US'}) => ProfileDto.fromJson(
  ApiFixture.load('account/profile_ok').dataMap['profile']
      as Map<String, dynamic>,
).toEntity().copyWith(countryCode: country);

/// `orders/detail_ok` with its payments replaced by [payments] when given.
OrderDetail fixtureOrderDetail({List<OrderPayment>? payments}) {
  final detail = OrderDetailDto.fromJson(
    ApiFixture.load('orders/detail_ok').dataMap['order']
        as Map<String, dynamic>,
  ).toEntity();
  return payments == null ? detail : detail.copyWith(payments: payments);
}

OrderPayment attempt(int number, String status, {String provider = 'paypal'}) =>
    OrderPayment(
      publicId: '01JF00000000000000000000P$number',
      attempt: number,
      status: status,
      amount: Money.parse('10.00', 'USD', formatted: 'USD10.00'),
      createdAt: DateTime.utc(2026, 11, 11, 12),
      provider: provider,
    );

Money usd(String amount) => Money.parse(amount, 'USD', formatted: '\$$amount');

Money egp(String amount) => Money.parse(amount, 'EGP', formatted: 'E£$amount');

/// A payment option; Paymob charges EGP, every other gateway USD (owner decision 1).
PaymentOption option(
  String gateway, {
  String? label,
  bool available = true,
  String? notice,
  Money? charge,
  Money? display,
  List<PaymentMethodOption> methods = const [],
}) {
  final pay = charge ?? (gateway == 'paymob' ? egp('1530.00') : usd('31.50'));
  final shown = display ?? usd('31.50');
  return PaymentOption(
    gateway: gateway,
    label:
        label ??
        switch (gateway) {
          'paypal' => 'PayPal',
          'paymob' => 'Paymob',
          'stripe' => 'Card (Stripe)',
          'wallet' => 'Wallet',
          _ => gateway,
        },
    methods: methods,
    available: available,
    paymentCurrency: pay.currency,
    paymentAmount: pay,
    displayCurrency: shown.currency,
    displayAmount: shown,
    notice: notice,
  );
}

/// A cart with one line and [total] in USD, ready unless it has a block reason.
Cart cartWith({
  String total = '31.50',
  String? blockReason,
  int quantity = 1,
  CartCoupon? coupon,
  bool priceMayVary = false,
}) => fixtureCartOk().copyWith(
  checkoutReady: blockReason == null,
  priceMayVary: priceMayVary,
  coupon: coupon,
  items: [
    fixtureCartOk().items.first.copyWith(
      quantity: quantity,
      purchasable: blockReason == null,
      blockReason: blockReason,
      total: usd(total),
      subtotal: usd(total),
    ),
  ],
  totals: CartTotals(
    subtotal: usd(total),
    discount: usd('0.00'),
    total: usd(total),
  ),
);

PlacedOrder placed({String id = orderId}) => PlacedOrder(
  publicId: id,
  status: 'pending_payment',
  orderNumber: 'BX-1001',
  displayTotal: usd('31.50'),
  paymentTotal: usd('31.50'),
  expiresAt: DateTime.utc(2026, 11, 11, 12, 30),
);

final paypalNext = PaymentNext.paypal(
  paymentPublicId: '01JF00000000000000000000P1',
  gateway: 'paypal',
  providerOrderId: 'PAYPAL-1',
  approveUrl: Uri.parse('https://www.sandbox.paypal.com/checkoutnow?token=1'),
);

final redirectNext = PaymentNext.redirect(
  paymentPublicId: '01JF00000000000000000000P1',
  gateway: 'paymob',
  redirectUrl: Uri.parse('https://accept.paymob.com/unifiedcheckout/?x=1'),
);

const walletNext = PaymentNext.wallet(
  paymentPublicId: '01JF00000000000000000000P1',
  gateway: 'wallet',
);

OrderPaymentStatus orderStatus({
  String id = orderId,
  String status = 'pending_payment',
  String payment = 'unpaid',
  bool isFinal = false,
}) => OrderPaymentStatus(
  publicId: id,
  status: status,
  paymentStatus: payment,
  isFinal: isFinal,
);

final paidStatus = orderStatus(status: 'paid', payment: 'paid');

/// [CheckoutRepository] with scripted answers; place-order calls record the key.
class FakeCheckoutRepository implements CheckoutRepository {
  Result<Cart> Function(String country) onValidate = (_) => Ok(cartWith());
  Result<List<PaymentOption>> Function(String country) onOptions = (country) =>
      Ok([
        option('paymob', notice: 'charged_in_payment_currency'),
        option('paypal'),
        option('stripe'),
      ]);
  final placeResults = <Result<PlaceOrderResponse>>[];
  final placed = <({PlaceOrderRequest request, String key})>[];
  final validated = <String>[];
  final optionCalls = <String>[];

  /// When set, place-order waits for it.
  Completer<void>? placeGate;

  /// When set, validate waits for it.
  Completer<void>? quoteGate;

  @override
  Future<Result<Cart>> validate({
    required String country,
    String? email,
  }) async {
    validated.add(country);
    await quoteGate?.future;
    return onValidate(country);
  }

  @override
  Future<Result<List<PaymentOption>>> paymentOptions({
    required String country,
    String? email,
  }) async {
    optionCalls.add(country);
    return onOptions(country);
  }

  @override
  Future<Result<PlaceOrderResponse>> placeOrder(
    PlaceOrderRequest request, {
    required String idempotencyKey,
  }) async {
    placed.add((request: request, key: idempotencyKey));
    await placeGate?.future;
    final index = placed.length - 1;
    return placeResults[index < placeResults.length
        ? index
        : placeResults.length - 1];
  }

  @override
  Future<Result<OrderPaymentStatus>> status(String orderPublicId) async =>
      Ok(orderStatus(id: orderPublicId));

  @override
  Future<Result<void>> sendGuestOtp({
    required String orderPublicId,
    required String email,
  }) async {
    guestCalls.add('send:$email');
    return sendOtpResult;
  }

  @override
  Future<Result<void>> verifyGuestOtp({
    required String orderPublicId,
    required String code,
  }) async {
    guestCalls.add('verify:$code');
    return verifyOtpResult;
  }

  /// Guest order access (M-A14).
  Result<GuestLinkAccess> linkResult = const Ok(
    GuestLinkAccess(orderPublicId: orderId, otpRequired: true),
  );
  Result<void> sendOtpResult = const Ok(null);
  Result<void> verifyOtpResult = const Ok(null);
  final guestCalls = <String>[];

  @override
  Future<Result<GuestLinkAccess>> openGuestLink(Uri signedLink) async {
    guestCalls.add('link:${signedLink.path}');
    return linkResult;
  }
}

/// [PaymentsRepository] recording every call; answers come from the queues (the last repeats).
class FakePaymentsRepository implements PaymentsRepository {
  final calls = <String>[];
  List<Result<OrderPaymentStatus>> verifyResults = [Ok(orderStatus())];
  List<Result<OrderPaymentStatus>> captureResults = [Ok(orderStatus())];
  List<Result<OrderPaymentStatus>> statusResults = [Ok(orderStatus())];
  List<Result<PaymentNext>> retryResults = [Ok(paypalNext)];

  /// When set, verify and capture wait for it.
  Completer<void>? verifyGate;

  int _verify = 0;
  int _capture = 0;
  int _status = 0;
  int _retry = 0;

  static T _take<T>(List<T> results, int index) =>
      results[index < results.length ? index : results.length - 1];

  @override
  Future<Result<PaymentNext>> retryPayment(
    String orderPublicId, {
    String? gateway,
    String? method,
  }) async {
    calls.add('retry:$orderPublicId');
    return _take(retryResults, _retry++);
  }

  @override
  Future<Result<OrderPaymentStatus>> verifyPayment(String orderPublicId) async {
    calls.add('verify:$orderPublicId');
    await verifyGate?.future;
    return _take(verifyResults, _verify++);
  }

  @override
  Future<Result<OrderPaymentStatus>> capturePayPal(String orderPublicId) async {
    calls.add('capture:$orderPublicId');
    await verifyGate?.future;
    return _take(captureResults, _capture++);
  }

  @override
  Future<Result<OrderPaymentStatus>> status(String orderPublicId) async {
    calls.add('status:$orderPublicId');
    return _take(statusResults, _status++);
  }

  int count(String prefix) => calls.where((c) => c.startsWith(prefix)).length;
}

/// [OrdersRepository] answering the account order list and detail and the guest detail.
class FakeOrdersRepository implements OrdersRepository {
  Result<OrderDetail> detailResult = Ok(fixtureOrderDetail());
  final details = <String>[];

  /// The order list: one answer per page number (page 1 by default: the two fixture orders).
  Map<int, Result<Paged<OrderSummary>>> pages = {
    1: Ok(
      pageOf([
        fixtureOrderSummary(),
        fixtureOrderSummary(id: orderId2, number: 'BX-1003'),
      ]),
    ),
  };
  final queries = <OrdersQuery>[];

  /// When set, list waits for it.
  Completer<void>? listGate;

  Result<GuestOrderDetail> guestResult = Ok(
    GuestOrderDetail(
      order: fixtureOrderDetail(),
      linkVerified: true,
      otpVerified: true,
    ),
  );
  final guestDetails = <String>[];

  @override
  Future<Result<OrderDetail>> detail(String orderPublicId) async {
    details.add(orderPublicId);
    return detailResult;
  }

  @override
  Future<Result<GuestOrderDetail>> guestDetail(String orderPublicId) async {
    guestDetails.add(orderPublicId);
    return guestResult;
  }

  @override
  Future<Result<Paged<OrderSummary>>> list(OrdersQuery query) async {
    queries.add(query);
    await listGate?.future;
    return pages[query.page] ?? Ok(pageOf(const [], page: query.page));
  }
}

const orderId2 = '01JF0000000000000000000009';

/// An order of the list (`orders/list_ok` shape) with a number, status and total.
OrderSummary fixtureOrderSummary({
  String id = orderId,
  String number = 'BX-1004',
  String status = 'completed',
  String total = '31.50',
  int items = 2,
}) => OrderSummary(
  publicId: id,
  number: number,
  status: status,
  paymentStatus: status == 'pending_payment' ? 'unpaid' : 'paid',
  isFinal: status != 'pending_payment',
  displayTotal: usd(total),
  paymentTotal: usd(total),
  itemCount: items,
  createdAt: DateTime(2026, 11, 10, 12),
);

/// One page of [items] (20 per page, [lastPage] pages).
Paged<OrderSummary> pageOf(
  List<OrderSummary> items, {
  int page = 1,
  int lastPage = 1,
}) => Paged(
  items: items,
  page: PageInfo(
    currentPage: page,
    perPage: 20,
    total: items.length,
    lastPage: lastPage,
  ),
);

/// [LinkSource] fed by the test.
class FakeLinkSource implements LinkSource {
  final controller = StreamController<Uri>.broadcast();

  @override
  Stream<Uri> get links => controller.stream;

  void open(String url) => controller.add(Uri.parse(url));
}
