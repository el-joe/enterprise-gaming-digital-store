import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/link_inbox.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/checkout_fakes.dart';
import '../helpers/pump_app.dart';
import '../helpers/reveal_fakes.dart';

/// Key M-A14 states in LTR (en) and RTL (ar), compared with the approved M-A8 frames (09.1 to
/// 09.5, 10.1 to 10.6, 11.1 to 11.5 and the 19.4 Android 360x800 code reveal check) by review.
/// Only the dummy canary code `FIXTURE-CODE-0000` is ever shown.
void main() {
  setUpAll(loadAppFonts);

  for (final lang in ['en', 'ar']) {
    final dir = lang == 'ar' ? 'rtl' : 'ltr';
    final l10n = l10nFor(lang);

    Future<void> snap(WidgetTester tester, String name) async {
      await precacheImages(tester);
      await expectLater(
        find.byType(MaterialApp),
        matchesGoldenFile('m_a14/${name}_$dir.png'),
      );
    }

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      void Function(TestHarness h)? arrange,
      Size size = const Size(390, 844),
      bool signedIn = true,
      bool settle = true,
      String integrity = 'ok',
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: signedIn,
        size: size,
        integrity: integrity,
        // The one-time rooted-device sheet (M-A10) is not part of these frames.
        preferences: const {PreferencesStore.integrityWarningShownKey: true},
        arrange: (h) {
          h.now.value = DateTime(2026, 11, 11, 12);
          h.orders.pages = {
            1: Ok(
              pageOf([
                fixtureOrderSummary(),
                fixtureOrderSummary(
                  id: orderId2,
                  number: 'BX-1003',
                  status: 'processing',
                  total: '9.99',
                  items: 1,
                ),
                fixtureOrderSummary(
                  id: '01JF0000000000000000000008',
                  number: 'BX-1002',
                  status: 'partially_refunded',
                  total: '25.00',
                  items: 1,
                ),
                fixtureOrderSummary(
                  id: '01JF0000000000000000000007',
                  number: 'BX-1001',
                  status: 'failed',
                  total: '29.99',
                  items: 1,
                ),
              ], lastPage: 2),
            ),
          };
          h.orders.detailResult = Ok(richDetail());
          arrange?.call(h);
        },
      );
      h.read(appRouterProvider).go(location);
      if (settle) {
        await tester.pumpAndSettle();
      } else {
        await tester.pump();
        await tester.pump(const Duration(milliseconds: 400));
      }
      return h;
    }

    final reveal = Routes.codeRevealLocation(orderId, fixtureUnitId);

    Future<void> leave(WidgetTester tester, TestHarness h) async {
      // Leave the code screen so its timers and protection end with the test.
      h.read(appRouterProvider).go(Routes.home.path);
      await tester.pumpAndSettle();
    }

    testWidgets('09.1 orders ($dir)', (tester) async {
      await at(tester, Routes.orders.path);
      await snap(tester, 'orders');
    });

    testWidgets('09.2 orders empty ($dir)', (tester) async {
      await at(
        tester,
        Routes.orders.path,
        arrange: (h) => h.orders.pages = {1: Ok(pageOf(const []))},
      );
      await snap(tester, 'orders_empty');
    });

    testWidgets('09.3 orders loading ($dir)', (tester) async {
      final h = await at(
        tester,
        Routes.orders.path,
        settle: false,
        arrange: (h) => h.orders.listGate = Completer<void>(),
      );
      await snap(tester, 'orders_loading');
      h.orders.listGate!.complete();
      await tester.pumpAndSettle();
    });

    testWidgets('09.3 orders error ($dir)', (tester) async {
      await at(
        tester,
        Routes.orders.path,
        arrange: (h) => h.orders.pages = {
          1: const Err(
            ServerFailure(
              statusCode: 500,
              requestId: '01JF00000000000000000000RQ',
            ),
          ),
        },
      );
      await snap(tester, 'orders_error');
    });

    testWidgets('orders signed out ($dir)', (tester) async {
      await at(tester, Routes.orders.path, signedIn: false);
      await snap(tester, 'orders_signed_out');
    });

    testWidgets('09.4 order detail ($dir)', (tester) async {
      await at(tester, Routes.orderLocation(orderId));
      await snap(tester, 'order_detail');
    });

    testWidgets('09.5 partial refund and timeline ($dir)', (tester) async {
      await at(
        tester,
        Routes.orderLocation(orderId),
        arrange: (h) => h.orders.detailResult = Ok(refundedDetail()),
      );
      await snap(tester, 'order_refunded');
    });

    testWidgets('order awaiting payment with retry ($dir)', (tester) async {
      await at(
        tester,
        Routes.orderLocation(orderId),
        arrange: (h) => h.orders.detailResult = Ok(unpaidDetail()),
      );
      await snap(tester, 'order_retry_payment');
    });

    testWidgets('10.1 code masked ($dir)', (tester) async {
      final h = await at(tester, reveal);
      await snap(tester, 'code_masked');
      await leave(tester, h);
    });

    testWidgets('10.2 re-auth sheet ($dir)', (tester) async {
      final h = await at(
        tester,
        reveal,
        arrange: (h) => h.reveal.reveals = [
          const Err(RecentAuthRequiredFailure()),
          Ok(canaryReveal()),
        ],
      );
      await tester.tap(find.text(l10n.revealButton));
      await tester.pumpAndSettle();
      await snap(tester, 'code_reauth');
      await leave(tester, h);
    });

    testWidgets('10.3 wrong password ($dir)', (tester) async {
      final h = await at(
        tester,
        reveal,
        arrange: (h) {
          h.reveal.reveals = [const Err(RecentAuthRequiredFailure())];
          h.reveal.confirmations = [
            const Err(
              ValidationFailure({
                'password': ['The password is incorrect.'],
              }),
            ),
          ];
        },
      );
      await tester.tap(find.text(l10n.revealButton));
      await tester.pumpAndSettle();
      await tester.enterText(find.byType(EditableText).last, 'wrong-pass');
      await tester.tap(find.text(l10n.reauthConfirm));
      await tester.pumpAndSettle();
      FocusManager.instance.primaryFocus?.unfocus();
      await tester.pumpAndSettle();
      await snap(tester, 'code_wrong_password');
      await leave(tester, h);
    });

    testWidgets('10.4 code revealed and copied ($dir)', (tester) async {
      final h = await at(tester, reveal);
      await tester.tap(find.text(l10n.revealButton));
      await tester.pumpAndSettle();
      await tester.tap(find.text(l10n.revealCopy));
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 400));
      await snap(tester, 'code_revealed');
      await leave(tester, h);
    });

    testWidgets('10.5 hidden during capture ($dir)', (tester) async {
      final h = await at(
        tester,
        reveal,
        arrange: (h) => h.security.capturedNow = true,
      );
      await snap(tester, 'code_capture_hidden');
      await leave(tester, h);
    });

    testWidgets('10.6 denied and device warning ($dir)', (tester) async {
      final h = await at(
        tester,
        reveal,
        integrity: 'compromised',
        arrange: (h) =>
            h.reveal.reveals = [const Err(ForbiddenFailure('reveal_denied'))],
      );
      h.read(emailVerificationProvider.notifier).update(verified: true);
      await tester.tap(find.text(l10n.revealButton));
      await tester.pumpAndSettle();
      await snap(tester, 'code_denied');
      await leave(tester, h);
    });

    testWidgets('10.6 rate limited ($dir)', (tester) async {
      final h = await at(
        tester,
        reveal,
        arrange: (h) => h.reveal.reveals = [
          const Err(RateLimitedFailure(retryAfter: Duration(minutes: 42))),
        ],
      );
      await tester.tap(find.text(l10n.revealButton));
      await tester.pumpAndSettle();
      await snap(tester, 'code_rate_limited');
      await leave(tester, h);
    });

    testWidgets('19.4 code revealed at 360x800 ($dir)', (tester) async {
      final h = await at(tester, reveal, size: const Size(360, 800));
      await tester.tap(find.text(l10n.revealButton));
      await tester.pumpAndSettle();
      await snap(tester, 'code_revealed_360');
      await leave(tester, h);
    });

    Future<TestHarness> guest(
      WidgetTester tester, {
      void Function(TestHarness h)? arrange,
    }) => at(
      tester,
      Routes.guestOrderLocation(orderId),
      signedIn: false,
      arrange: (h) {
        h.orders.guestResult = Ok(
          GuestOrderDetail(
            order: richDetail(),
            linkVerified: true,
            otpVerified: true,
          ),
        );
        h
            .read(guestLinkInboxProvider)
            .put(
              orderId,
              Uri.parse(
                'https://boxcards.test/api/v1/checkout/guest/orders/$orderId/access?expires=1&signature=abc',
              ),
            );
        arrange?.call(h);
      },
    );

    testWidgets('11.1 guest verify email ($dir)', (tester) async {
      await guest(tester);
      await snap(tester, 'guest_email');
    });

    testWidgets('11.2 guest enter code ($dir)', (tester) async {
      await guest(tester);
      await tester.enterText(
        find.byType(EditableText).first,
        'sara@example.test',
      );
      await tester.tap(find.text(l10n.guestSendCode));
      await tester.pumpAndSettle();
      FocusManager.instance.primaryFocus?.unfocus();
      await tester.pumpAndSettle();
      await snap(tester, 'guest_code');
    });

    testWidgets('11.3 guest code error ($dir)', (tester) async {
      await guest(
        tester,
        arrange: (h) => h.checkout.verifyOtpResult = const Err(
          DomainFailure(
            'otp_invalid',
            message: 'The code is not valid. 3 attempts left.',
          ),
        ),
      );
      await tester.enterText(
        find.byType(EditableText).first,
        'sara@example.test',
      );
      await tester.tap(find.text(l10n.guestSendCode));
      await tester.pumpAndSettle();
      await tester.enterText(find.byType(EditableText).first, '000000');
      await tester.pumpAndSettle();
      FocusManager.instance.primaryFocus?.unfocus();
      await tester.pumpAndSettle();
      await snap(tester, 'guest_code_error');
    });

    testWidgets('11.4 guest order ($dir)', (tester) async {
      await guest(
        tester,
        arrange: (h) => h.checkout.linkResult = const Ok(
          GuestLinkAccess(orderPublicId: orderId, otpRequired: false),
        ),
      );
      await snap(tester, 'guest_order');
    });

    testWidgets('11.5 guest link expired ($dir)', (tester) async {
      await guest(
        tester,
        arrange: (h) =>
            h.checkout.linkResult = const Err(ForbiddenFailure('forbidden')),
      );
      await snap(tester, 'guest_expired');
    });

    testWidgets('guest paste link ($dir)', (tester) async {
      await at(tester, Routes.guestAccess.path, signedIn: false);
      await snap(tester, 'guest_paste_link');
    });
  }
}

OrderDetail richDetail() {
  final base = fixtureOrderDetail();
  final line = base.items.first;
  // Local wall-clock times, so the frames do not depend on the machine's time zone.
  final delivered = DateTime(2026, 11, 10, 12);
  final unit = line.units.first.copyWith(deliveredAt: delivered);
  return base.copyWith(
    summary: base.summary.copyWith(
      number: 'BX-1004',
      createdAt: delivered,
      displayTotal: usd('31.50'),
      paymentTotal: usd('31.50'),
    ),
    items: [
      line.copyWith(
        quantity: 2,
        units: [
          unit,
          unit.copyWith(
            publicId: '01JF0000000000000000000005',
            maskedCode: '****7777',
            firstRevealedAt: DateTime(2026, 11, 10, 13),
          ),
        ],
        unitPrice: usd('10.00'),
        lineTotal: usd('20.00'),
      ),
      line.copyWith(
        id: 2,
        name: 'Pulse Wallet Code - 25 USD',
        units: [
          unit.copyWith(
            publicId: '01JF0000000000000000000006',
            maskedCode: '****4410',
          ),
        ],
        lineTotal: usd('11.50'),
      ),
    ],
  );
}

OrderDetail refundedDetail() {
  final base = richDetail();
  final line = base.items[1];
  return base.copyWith(
    summary: base.summary.copyWith(
      number: 'BX-1002',
      status: 'partially_refunded',
      paymentStatus: 'partially_refunded',
    ),
    refundState: 'partial',
    refunds: [
      OrderRefund(
        publicId: '01JF00000000000000000000R1',
        status: 'succeeded',
        destination: 'wallet',
        amount: usd('12.50'),
        createdAt: DateTime(2026, 11, 3, 9, 12),
      ),
    ],
    items: [
      line.copyWith(
        quantity: 2,
        units: [
          line.units.first,
          line.units.first.copyWith(
            publicId: '01JF0000000000000000000004',
            status: 'refunded',
            revealable: false,
            maskedCode: null,
          ),
        ],
      ),
    ],
    timeline: [
      OrderTimelineEntry(
        status: 'pending_payment',
        at: DateTime(2026, 11, 2, 10),
      ),
      OrderTimelineEntry(status: 'paid', at: DateTime(2026, 11, 2, 10, 1)),
      OrderTimelineEntry(
        status: 'partially_fulfilled',
        at: DateTime(2026, 11, 2, 10, 5),
      ),
      OrderTimelineEntry(
        status: 'partially_refunded',
        at: DateTime(2026, 11, 3, 9, 12),
      ),
    ],
  );
}

OrderDetail unpaidDetail() {
  final base = fixtureOrderDetail(payments: [attempt(1, 'failed')]);
  final line = base.items.first;
  return base.copyWith(
    summary: base.summary.copyWith(
      number: 'BX-1006',
      createdAt: DateTime(2026, 11, 11, 11),
      status: 'pending_payment',
      paymentStatus: 'unpaid',
    ),
    items: [
      line.copyWith(
        units: [
          line.units.first.copyWith(
            status: 'pending',
            revealable: false,
            maskedCode: null,
            deliveredAt: null,
          ),
        ],
      ),
    ],
  );
}
