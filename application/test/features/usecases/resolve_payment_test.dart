import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/features/payments/domain/repositories/payments_repository.dart';
import 'package:application/features/payments/domain/usecases/resolve_payment.dart';
import 'package:application/shared/domain/entities/payment_next.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/repo_harness.dart';

const order = '01jf0000000000000000000001';

OrderPaymentStatus pstatus(
  String paymentStatus, {
  bool isFinal = false,
  String order = 'pending_payment',
}) => OrderPaymentStatus(
  publicId: '01jf0000000000000000000001',
  status: order,
  paymentStatus: paymentStatus,
  isFinal: isFinal,
);

/// Answers status calls from [statuses] in order (the last one repeats) and records calls.
class ScriptedPayments implements PaymentsRepository {
  ScriptedPayments({
    this.statuses = const [],
    Result<OrderPaymentStatus>? confirmation,
  }) : confirmation = confirmation ?? Ok(pstatus('processing'));

  final List<Result<OrderPaymentStatus>> statuses;
  final Result<OrderPaymentStatus> confirmation;
  final List<String> calls = [];
  int _index = 0;

  @override
  Future<Result<OrderPaymentStatus>> capturePayPal(String orderPublicId) async {
    calls.add('capture');
    return confirmation;
  }

  @override
  Future<Result<OrderPaymentStatus>> verifyPayment(String orderPublicId) async {
    calls.add('verify');
    return confirmation;
  }

  @override
  Future<Result<OrderPaymentStatus>> status(String orderPublicId) async {
    calls.add('status');
    final result = statuses[_index.clamp(0, statuses.length - 1)];
    _index++;
    return result;
  }

  @override
  Future<Result<PaymentNext>> retryPayment(
    String orderPublicId, {
    String? gateway,
    String? method,
  }) => throw UnimplementedError();
}

void main() {
  late DateTime now;
  late List<Duration> waits;

  ResolvePayment build(PaymentsRepository payments) {
    now = DateTime.utc(2026, 11, 10, 12);
    waits = [];
    return ResolvePayment(
      payments,
      now: () => now,
      wait: (delay) async {
        waits.add(delay);
        now = now.add(delay);
      },
    );
  }

  const stripe = PaymentNext.stripe(
    paymentPublicId: 'p',
    gateway: 'stripe',
    clientSecret: 's',
  );

  test('polls after 2 s, 3 s, 5 s, then every 10 s for 2 minutes, then still processing', () async {
    final payments = ScriptedPayments(statuses: [Ok(pstatus('processing'))]);

    final result = okValue(await build(payments)(order, next: stripe));

    expect(result, isA<PaymentStillProcessing>());
    expect(waits.take(4), const [
      Duration(seconds: 2),
      Duration(seconds: 3),
      Duration(seconds: 5),
      Duration(seconds: 10),
    ]);
    expect(waits.skip(3), everyElement(const Duration(seconds: 10)));
    expect(
      waits.fold(Duration.zero, (a, b) => a + b),
      const Duration(minutes: 2),
    );
    expect(payments.calls.first, 'verify');
    expect(payments.calls.where((c) => c == 'status'), hasLength(waits.length));
    expect(waits, hasLength(14));
  });

  test('stops as soon as the server says paid', () async {
    final payments = ScriptedPayments(
      statuses: [
        Ok(pstatus('processing')),
        Ok(pstatus('paid', order: 'paid')),
      ],
    );

    final result = okValue(await build(payments)(order, next: stripe));

    expect(result, isA<PaymentResolved>());
    expect(waits, const [Duration(seconds: 2), Duration(seconds: 3)]);
  });

  test('is_final without payment is finished, never paid', () async {
    final payments = ScriptedPayments(
      statuses: [Ok(pstatus('unpaid', isFinal: true, order: 'expired'))],
    );

    final result = okValue(await build(payments)(order, next: stripe));

    expect(result, isA<PaymentFinished>());
    expect((result as PaymentFinished).status.isPaid, isFalse);
  });

  test('never reports paid when the order status says paid but payment_status does not', () async {
    final payments = ScriptedPayments(
      statuses: [Ok(pstatus('processing', order: 'paid'))],
    );

    expect(
      okValue(await build(payments)(order, next: stripe)),
      isA<PaymentStillProcessing>(),
    );
  });

  test('a paid verification needs no polling', () async {
    final payments = ScriptedPayments(confirmation: Ok(pstatus('paid')));

    expect(
      okValue(await build(payments)(order, next: stripe)),
      isA<PaymentResolved>(),
    );
    expect(waits, isEmpty);
  });

  test('PayPal captures, the wallet polls without a confirmation call, a resume verifies', () async {
    final paypal = ScriptedPayments(confirmation: Ok(pstatus('paid')));
    await build(paypal)(
      order,
      next: PaymentNext.paypal(
        paymentPublicId: 'p',
        gateway: 'paypal',
        providerOrderId: 'o',
        approveUrl: Uri.parse('https://paypal.test'),
      ),
    );
    expect(paypal.calls, ['capture']);

    final wallet = ScriptedPayments(statuses: [Ok(pstatus('paid'))]);
    await build(wallet)(
      order,
      next: const PaymentNext.wallet(paymentPublicId: 'p', gateway: 'wallet'),
    );
    expect(wallet.calls, ['status']);

    final resumed = ScriptedPayments(confirmation: Ok(pstatus('paid')));
    await build(resumed)(order);
    expect(resumed.calls, ['verify']);
  });

  test('transient failures keep polling; a 404 stops', () async {
    final payments = ScriptedPayments(
      confirmation: const Err(NetworkFailure()),
      statuses: [
        const Err(TimeoutFailure()),
        const Err(ServerFailure(statusCode: 502)),
        Ok(pstatus('paid')),
      ],
    );
    expect(
      okValue(await build(payments)(order, next: stripe)),
      isA<PaymentResolved>(),
    );

    final missing = ScriptedPayments(statuses: [const Err(NotFoundFailure())]);
    expect(
      failureOf<NotFoundFailure>(await build(missing)(order, next: stripe)),
      isA<NotFoundFailure>(),
    );

    final unauthorised = ScriptedPayments(
      confirmation: const Err(UnauthenticatedFailure()),
    );
    failureOf<UnauthenticatedFailure>(
      await build(unauthorised)(order, next: stripe),
    );
    expect(unauthorised.calls, ['verify']);
  });
}
