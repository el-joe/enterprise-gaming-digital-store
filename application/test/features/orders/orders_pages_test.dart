import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/link_inbox.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/code_reveal/presentation/controllers/reveal_controller.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/shared/widgets/app_chip.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/pump_app.dart';
import '../../helpers/reveal_fakes.dart';

OrderSummary numbered(int n) => fixtureOrderSummary(
  id: '01JF00000000000000000${n.toString().padLeft(5, '0')}',
  number: 'BX-$n',
);

const signedLink =
    'https://boxcards.test/api/v1/checkout/guest/orders/$orderId/access?expires=1&signature=abc';

void main() {
  for (final lang in ['en', 'ar']) {
    final l10n = l10nFor(lang);

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      bool signedIn = true,
      String integrity = 'ok',
      void Function(TestHarness h)? arrange,
      Size size = const Size(390, 844),
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: signedIn,
        integrity: integrity,
        size: size,
        preferences: const {PreferencesStore.integrityWarningShownKey: true},
        arrange: arrange,
      );
      h.read(appRouterProvider).go(location);
      await tester.pumpAndSettle();
      return h;
    }

    // The top route, including pushed ones.
    String path(TestHarness h) => h.read(appRouterProvider).state.uri.path;

    Future<void> leave(WidgetTester tester, TestHarness h) async {
      h.read(appRouterProvider).go(Routes.home.path);
      await tester.pumpAndSettle();
    }

    final reveal = Routes.codeRevealLocation(orderId, fixtureUnitId);

    group('orders tab ($lang)', () {
      testWidgets('lists orders and opens the detail', (tester) async {
        final h = await at(tester, Routes.orders.path);
        expect(find.text('BX-1004'), findsOneWidget);
        expect(find.text(l10n.orderStatusCompleted), findsWidgets);
        await tester.tap(find.text('BX-1004'));
        await tester.pumpAndSettle();
        expect(path(h), Routes.orderLocation(orderId));
        expect(h.orders.details, [orderId]);
      });

      testWidgets('loads the next page near the end', (tester) async {
        final h = await at(
          tester,
          Routes.orders.path,
          arrange: (h) => h.orders.pages = {
            1: Ok(
              pageOf([for (var i = 1; i <= 20; i++) numbered(i)], lastPage: 2),
            ),
            2: Ok(pageOf([numbered(21)], page: 2, lastPage: 2)),
          },
        );
        await tester.drag(find.byType(ListView).last, const Offset(0, -3000));
        await tester.pumpAndSettle();
        expect(h.orders.queries.map((q) => q.page), [1, 2]);
        await tester.drag(find.byType(ListView).last, const Offset(0, -3000));
        await tester.pumpAndSettle();
        expect(find.text('BX-21'), findsOneWidget);
      });

      testWidgets('a status chip filters', (tester) async {
        final h = await at(tester, Routes.orders.path);
        await tester.tap(
          find.widgetWithText(AppChip, l10n.orderStatusProcessing),
        );
        await tester.pumpAndSettle();
        expect(h.orders.queries.last.status, 'processing');
      });

      testWidgets('empty', (tester) async {
        await at(
          tester,
          Routes.orders.path,
          arrange: (h) => h.orders.pages = {1: Ok(pageOf(const []))},
        );
        expect(find.text(l10n.ordersEmptyTitle), findsOneWidget);
      });

      testWidgets('offline, then retry', (tester) async {
        final h = await at(
          tester,
          Routes.orders.path,
          arrange: (h) => h.orders.pages = {1: const Err(NetworkFailure())},
        );
        expect(find.text(l10n.noConnectionTitle), findsOneWidget);
        h.orders.pages = {
          1: Ok(pageOf([numbered(7)])),
        };
        await tester.tap(find.text(l10n.actionRetry));
        await tester.pumpAndSettle();
        expect(find.text('BX-7'), findsOneWidget);
      });

      testWidgets('signed out: sign in and guest access', (tester) async {
        final h = await at(tester, Routes.orders.path, signedIn: false);
        expect(find.text(l10n.ordersSignedOutTitle), findsOneWidget);
        expect(h.orders.queries, isEmpty);
        await tester.tap(find.text(l10n.guestAccessEntry));
        await tester.pumpAndSettle();
        expect(path(h), Routes.guestAccess.path);
      });
    });

    group('order detail ($lang)', () {
      testWidgets(
        'a unit opens its code screen; refund opens the request form',
        (tester) async {
          final h = await at(tester, Routes.orderLocation(orderId));
          expect(find.text('****0000'), findsOneWidget);
          await tester.tap(find.text('****0000'));
          await tester.pumpAndSettle();
          expect(path(h), reveal);
          h.read(appRouterProvider).pop();
          await tester.pumpAndSettle();
          await tester.tap(find.text(l10n.orderRequestRefund));
          await tester.pumpAndSettle();
          expect(path(h), Routes.refundRequestLocation(orderId));
          expect(find.text(l10n.refundReasonLabel), findsOneWidget);
        },
      );

      testWidgets('retry payment goes through the payment flow', (
        tester,
      ) async {
        final base = fixtureOrderDetail(payments: [attempt(1, 'failed')]);
        final h = await at(
          tester,
          Routes.orderLocation(orderId),
          arrange: (h) => h.orders.detailResult = Ok(
            base.copyWith(
              summary: base.summary.copyWith(
                status: 'pending_payment',
                paymentStatus: 'unpaid',
              ),
            ),
          ),
        );
        await tester.tap(find.text(l10n.orderRetryPayment));
        await tester.pumpAndSettle();
        expect(path(h), Routes.paymentLocation(orderId));
        expect(h.payments.calls.first, 'retry:$orderId');
      });

      testWidgets('not found', (tester) async {
        await at(
          tester,
          Routes.orderLocation(orderId),
          arrange: (h) => h.orders.detailResult = const Err(NotFoundFailure()),
        );
        expect(find.text(l10n.orderNotFoundTitle), findsOneWidget);
      });

      testWidgets('"View order" on a paid result lands on the detail', (
        tester,
      ) async {
        final h = await at(tester, Routes.paymentLocation(orderId));
        // Idle screen; ask the server, which says paid.
        h.payments.verifyResults = [Ok(paidStatus)];
        await tester.tap(find.text(l10n.paymentCheckStatus));
        await tester.pumpAndSettle();
        await tester.tap(find.text(l10n.paymentViewOrder));
        await tester.pumpAndSettle();
        expect(path(h), Routes.orderLocation(orderId));
      });
    });

    group('code reveal ($lang)', () {
      testWidgets(
        'masked by default; reveal -> password sheet -> code; copy; hide',
        (tester) async {
          final h = await at(
            tester,
            reveal,
            arrange: (h) => h.reveal.reveals = [
              const Err(RecentAuthRequiredFailure()),
              Ok(canaryReveal()),
            ],
          );
          expect(find.text(canaryCode), findsNothing);
          expect(h.security.secureCalls, [true]);

          await tester.tap(find.text(l10n.revealButton));
          await tester.pumpAndSettle();
          expect(find.text(l10n.reauthTitle), findsOneWidget);
          await tester.enterText(find.byType(EditableText).last, 'right-pass');
          await tester.tap(find.text(l10n.reauthConfirm));
          await tester.pumpAndSettle();
          expect(find.text(l10n.reauthTitle), findsNothing);
          expect(find.text(canaryCode), findsOneWidget);
          expect(h.reveal.calls, ['reveal', 'confirm:right-pass', 'reveal']);
          expect(h.reveal.integrity, ['ok', 'ok']);

          await tester.tap(find.text(l10n.revealCopy));
          await tester.pump();
          expect(h.clipboard.copies, 1);
          expect(find.text(l10n.revealCopied), findsOneWidget);
          await tester.pumpAndSettle();

          await tester.tap(find.text(l10n.revealHide));
          await tester.pumpAndSettle();
          expect(find.text(canaryCode), findsNothing);
          await leave(tester, h);
          expect(h.security.secureCalls, [true, false]);
        },
      );

      testWidgets('wrong password keeps the sheet with the error', (
        tester,
      ) async {
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
        await tester.enterText(find.byType(EditableText).last, 'wrong');
        await tester.tap(find.text(l10n.reauthConfirm));
        await tester.pumpAndSettle();
        expect(find.text('The password is incorrect.'), findsOneWidget);
        expect(find.text(l10n.reauthTitle), findsOneWidget);
        expect(h.reveal.count('reveal'), 1);
        // Closing the sheet goes back to masked.
        await tester.tap(find.byIcon(Icons.close).last);
        await tester.pumpAndSettle();
        expect(find.text(l10n.reauthTitle), findsNothing);
        expect(find.text(l10n.revealButton), findsOneWidget);
        await leave(tester, h);
      });

      testWidgets('reveal_denied, rate limit and the device warning', (
        tester,
      ) async {
        final h = await at(
          tester,
          reveal,
          integrity: 'compromised',
          arrange: (h) => h.reveal.reveals = [
            const Err(ForbiddenFailure('reveal_denied')),
            const Err(RateLimitedFailure(retryAfter: Duration(minutes: 42))),
          ],
        );
        await tester.tap(find.text(l10n.revealButton));
        await tester.pumpAndSettle();
        expect(find.text(l10n.revealDeniedTitle), findsOneWidget);
        expect(find.text(l10n.revealIntegrityTitle), findsOneWidget);
        await tester.tap(find.text(l10n.revealButton));
        await tester.pumpAndSettle();
        expect(find.text(l10n.revealRateLimitedTitle), findsOneWidget);
        expect(find.text(l10n.revealRateLimitedBody(42)), findsOneWidget);
        expect(find.text(l10n.revealTryAgainIn('42:00')), findsOneWidget);
        expect(h.reveal.count('reveal'), 2);
        await leave(tester, h);
      });

      testWidgets('auto-hides after 60 s and on background', (tester) async {
        final h = await at(tester, reveal);
        await tester.tap(find.text(l10n.revealButton));
        await tester.pumpAndSettle();
        expect(find.text(canaryCode), findsOneWidget);
        h.now.value = h.now.value.add(const Duration(seconds: 60));
        await tester.pump(const Duration(seconds: 60));
        await tester.pumpAndSettle();
        expect(find.text(canaryCode), findsNothing);

        await tester.tap(find.text(l10n.revealButton));
        await tester.pumpAndSettle();
        expect(find.text(canaryCode), findsOneWidget);
        tester.binding.handleAppLifecycleStateChanged(
          AppLifecycleState.inactive,
        );
        await tester.pump();
        expect(find.text(l10n.revealObscured), findsNothing);
        expect(find.bySemanticsLabel(l10n.revealObscured), findsOneWidget);
        tester.binding.handleAppLifecycleStateChanged(AppLifecycleState.hidden);
        tester.binding.handleAppLifecycleStateChanged(AppLifecycleState.paused);
        await tester.pump();
        tester.binding
          ..handleAppLifecycleStateChanged(AppLifecycleState.hidden)
          ..handleAppLifecycleStateChanged(AppLifecycleState.inactive)
          ..handleAppLifecycleStateChanged(AppLifecycleState.resumed);
        await tester.pumpAndSettle();
        expect(find.text(canaryCode), findsNothing);
        expect(find.text(l10n.revealButton), findsOneWidget);
        await leave(tester, h);
      });

      testWidgets('iOS capture hides the code', (tester) async {
        final h = await at(tester, reveal);
        await tester.tap(find.text(l10n.revealButton));
        await tester.pumpAndSettle();
        h.security.capture.add(true);
        await tester.pumpAndSettle();
        expect(find.text(canaryCode), findsNothing);
        expect(find.text(l10n.revealCaptureTitle), findsOneWidget);
        expect(find.text(l10n.revealCaptureHidden), findsOneWidget);
        await leave(tester, h);
      });

      testWidgets(
        'screen readers: labelled buttons; the code is announced only '
        'on request',
        (tester) async {
          final announcements = <String>[];
          tester.binding.defaultBinaryMessenger
              .setMockDecodedMessageHandler<Object?>(
                SystemChannels.accessibility,
                (message) async {
                  final data = (message! as Map<Object?, Object?>)['data'];
                  if (data is Map && data['message'] is String) {
                    announcements.add(data['message']! as String);
                  }
                  return null;
                },
              );
          addTearDown(
            () => tester.binding.defaultBinaryMessenger
                .setMockDecodedMessageHandler<Object?>(
                  SystemChannels.accessibility,
                  null,
                ),
          );
          final semantics = tester.ensureSemantics();
          final h = await at(tester, reveal);
          await tester.tap(find.text(l10n.revealButton));
          await tester.pumpAndSettle();
          expect(
            find.bySemanticsLabel(l10n.revealCopySemantics),
            findsOneWidget,
          );
          expect(
            find.bySemanticsLabel(l10n.revealHideSemantics),
            findsOneWidget,
          );
          expect(
            find.bySemanticsLabel(l10n.revealShownSemantics),
            findsOneWidget,
          );
          expect(find.bySemanticsLabel(RegExp(canaryCode)), findsNothing);
          expect(announcements.join(), isNot(contains('F I X')));

          await tester.tap(find.text(l10n.revealReadAloud));
          await tester.pump();
          expect(announcements.last, canaryCode.split('').join(' '));

          await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
          await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
          await leave(tester, h);
          semantics.dispose();
        },
      );

      testWidgets('the 360 wide screen fits', (tester) async {
        final h = await at(tester, reveal, size: const Size(360, 800));
        await tester.tap(find.text(l10n.revealButton));
        await tester.pumpAndSettle();
        expect(tester.takeException(), isNull);
        await leave(tester, h);
      });

      testWidgets('leaving drops the code: provider disposed, nothing stored', (
        tester,
      ) async {
        final h = await at(tester, reveal);
        await tester.tap(find.text(l10n.revealButton));
        await tester.pumpAndSettle();
        final provider = revealControllerProvider(
          orderId,
          fixtureUnitId,
          false,
        );
        expect(h.read(provider.exists), isTrue);
        await leave(tester, h);
        await tester.pump();
        expect(h.read(provider.exists), isFalse);
        expect(h.secure.values.join(), isNot(contains(canaryCode)));
        expect(
          h.prefs.values.values.map((v) => '$v').join(),
          isNot(contains(canaryCode)),
        );
      });
    });

    group('guest access ($lang)', () {
      testWidgets('delivery link -> email -> code -> order -> guest reveal', (
        tester,
      ) async {
        final h = await at(tester, Routes.home.path, signedIn: false);
        h.links.open(signedLink);
        await tester.pumpAndSettle();
        expect(find.text(l10n.guestVerifyTitle), findsOneWidget);
        await tester.enterText(
          find.byType(EditableText).first,
          'sara@example.test',
        );
        await tester.tap(find.text(l10n.guestSendCode));
        await tester.pumpAndSettle();
        expect(find.text(l10n.guestCodeTitle), findsOneWidget);
        await tester.enterText(find.byType(EditableText).first, '123456');
        await tester.pumpAndSettle();
        expect(h.checkout.guestCalls.last, 'verify:123456');
        expect(find.text('****0000'), findsOneWidget);
        expect(find.text(l10n.guestCreateAccount), findsOneWidget);

        await tester.tap(find.text('****0000'));
        await tester.pumpAndSettle();
        expect(path(h), Routes.guestRevealLocation(orderId, fixtureUnitId));
        await tester.tap(find.text(l10n.revealButton));
        await tester.pumpAndSettle();
        expect(h.reveal.calls, ['guest-reveal']);
        expect(find.text(canaryCode), findsOneWidget);
        await leave(tester, h);
      });

      testWidgets('expired link', (tester) async {
        await at(
          tester,
          Routes.guestOrderLocation(orderId),
          signedIn: false,
          arrange: (h) {
            h.checkout.linkResult = const Err(ForbiddenFailure('forbidden'));
            h.read(guestLinkInboxProvider).put(orderId, Uri.parse(signedLink));
          },
        );
        expect(find.text(l10n.guestLinkExpiredTitle), findsOneWidget);
      });

      testWidgets('paste a link from the Account tab', (tester) async {
        final h = await at(tester, Routes.account.path, signedIn: false);
        await tester.tap(find.text(l10n.guestAccessEntry));
        await tester.pumpAndSettle();
        await tester.enterText(find.byType(EditableText).first, 'not a link');
        await tester.tap(find.text(l10n.guestEntryOpen));
        await tester.pumpAndSettle();
        expect(find.text(l10n.guestEntryInvalid), findsOneWidget);
        await tester.enterText(find.byType(EditableText).first, signedLink);
        await tester.tap(find.text(l10n.guestEntryOpen));
        await tester.pumpAndSettle();
        expect(path(h), Routes.guestOrderLocation(orderId));
        expect(find.text(l10n.guestVerifyTitle), findsOneWidget);
        expect(
          h.checkout.linkResult,
          const Ok(GuestLinkAccess(orderPublicId: orderId, otpRequired: true)),
        );
      });
    });

    group('accessibility ($lang)', () {
      for (final location in [
        Routes.orders.path,
        Routes.orderLocation(orderId),
      ]) {
        testWidgets('$location meets tap-target and label guidelines', (
          tester,
        ) async {
          final semantics = tester.ensureSemantics();
          await at(tester, location);
          await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
          await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
          semantics.dispose();
        });
      }
    });
  }
}
