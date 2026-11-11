import 'dart:async';

import 'package:application/core/l10n/generated/app_localizations.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/features/payments/payment_flow.dart';
import 'package:application/features/push/domain/entities/push.dart';
import 'package:application/features/push/presentation/widgets/push_permission_sheet.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/pump_app.dart';
import '../../helpers/push_fakes.dart';

/// M-A18 screens: the explainer after the first paid order (never on launch), tap routing from the
/// background and from a cold start, the foreground banner, the ticket email link and the push
/// switches in the notification settings. In English and Arabic.
void main() {
  const ticketId = '01JF0000000000000000000007';

  for (final lang in ['en', 'ar']) {
    final l10n = lookupAppLocalizations(Locale(lang));

    Future<TestHarness> launch(
      WidgetTester tester, {
      bool available = true,
      PushPermission permission = PushPermission.notDetermined,
      PushPayload? launchMessage,
      bool signedIn = true,
    }) => pumpHarness(
      tester,
      languageCode: lang,
      signedIn: signedIn,
      arrange: (h) {
        h.push
          ..available = available
          ..permissionValue = permission
          ..launchMessage = launchMessage;
      },
    );

    String path(TestHarness h) =>
        h.read(appRouterProvider).routerDelegate.currentConfiguration.uri.path;

    Future<void> paid(WidgetTester tester, TestHarness h) async {
      h.payments.verifyResults = [Ok(paidStatus)];
      unawaited(
        h
            .read(paymentFlowProvider.notifier)
            .handleReturn(orderId, cancelled: false),
      );
      h.read(appRouterProvider).go(Routes.paymentLocation(orderId));
      await tester.pumpAndSettle();
    }

    group('permission prompt ($lang)', () {
      testWidgets('never on launch', (tester) async {
        final h = await launch(tester);
        expect(find.byType(PushPermissionSheet), findsNothing);
        expect(h.push.permissionRequests, 0);
      });

      testWidgets(
        'the explainer after the first paid order, then the system prompt',
        (tester) async {
          final h = await launch(tester);
          await paid(tester, h);

          expect(find.byType(PushPermissionSheet), findsOneWidget);
          expect(find.text(l10n.pushExplainerTitle), findsOneWidget);
          expect(find.text(l10n.pushExplainerNoCodes), findsOneWidget);
          expect(
            h.push.permissionRequests,
            0,
            reason: 'the explainer comes first',
          );

          await tester.tap(find.text(l10n.pushExplainerAllow));
          await tester.pumpAndSettle();

          expect(find.byType(PushPermissionSheet), findsNothing);
          expect(h.push.permissionRequests, 1);
          expect(h.pushTokens.registered.single.token, 'fcm-token-1');
        },
      );

      testWidgets(
        '"Not now" asks nothing, and the next purchase shows nothing',
        (tester) async {
          final h = await launch(tester);
          await paid(tester, h);
          await tester.tap(find.text(l10n.pushExplainerNotNow));
          await tester.pumpAndSettle();
          expect(h.push.permissionRequests, 0);

          await h.read(paymentFlowProvider.notifier).close();
          h.read(appRouterProvider).go(Routes.home.path);
          await tester.pumpAndSettle();
          await paid(tester, h);

          expect(find.byType(PushPermissionSheet), findsNothing);
        },
      );

      for (final setup in [
        (
          name: 'not without Firebase',
          available: false,
          permission: PushPermission.notDetermined,
        ),
        (
          name: 'not when already allowed',
          available: true,
          permission: PushPermission.granted,
        ),
      ]) {
        testWidgets(setup.name, (tester) async {
          final h = await launch(
            tester,
            available: setup.available,
            permission: setup.permission,
          );
          await paid(tester, h);
          expect(find.byType(PushPermissionSheet), findsNothing);
          expect(h.push.permissionRequests, 0);
        });
      }
    });

    group('tap routing ($lang)', () {
      testWidgets('a tapped order push opens the order from the background', (
        tester,
      ) async {
        final h = await launch(tester, permission: PushPermission.granted);
        h.push.opened.add(orderPush(orderId));
        await tester.pumpAndSettle();

        expect(path(h), Routes.orderLocation(orderId));
        expect(h.orders.details, contains(orderId));
      });

      testWidgets('a cold start from a ticket push opens the ticket thread', (
        tester,
      ) async {
        final h = await launch(
          tester,
          permission: PushPermission.granted,
          launchMessage: ticketPush(ticketId),
        );
        await tester.pumpAndSettle();

        expect(path(h), Routes.ticketLocation(ticketId));
      });

      testWidgets('a push with an unknown target opens nothing', (
        tester,
      ) async {
        final h = await launch(tester, permission: PushPermission.granted);
        final before = path(h);
        h.push.opened.add(
          const PushPayload(
            data: {
              'type': 'promo',
              'link_type': 'url',
              'link_value': 'https://evil.test',
            },
          ),
        );
        await tester.pumpAndSettle();

        expect(path(h), before);
      });

      testWidgets('signed out, a tapped order push asks to sign in first', (
        tester,
      ) async {
        final h = await launch(
          tester,
          signedIn: false,
          permission: PushPermission.granted,
        );
        h.push.opened.add(orderPush(orderId));
        await tester.pumpAndSettle();

        final uri = h
            .read(appRouterProvider)
            .routerDelegate
            .currentConfiguration
            .uri;
        expect(uri.path, Routes.login.path);
        expect(
          uri.queryParameters[Routes.fromParameter],
          Routes.orderLocation(orderId),
        );
      });

      testWidgets('the ticket link of the support email opens the thread', (
        tester,
      ) async {
        final h = await launch(tester, available: false);
        h.links.open('https://boxcards.test/$lang/account/tickets/$ticketId');
        await tester.pumpAndSettle();

        expect(path(h), Routes.ticketLocation(ticketId));
      });
    });

    group('foreground ($lang)', () {
      testWidgets(
        'shows the push in the app, refreshes the badge, and "Open" opens it',
        (tester) async {
          final h = await launch(tester, permission: PushPermission.granted);
          final before = h.notifications.unreadCalls;
          h.push.foreground.add(orderPush(orderId));
          await tester.pump();
          await tester.pump(const Duration(milliseconds: 300));

          expect(find.textContaining('Order BX-1001 is ready'), findsOneWidget);
          expect(h.notifications.unreadCalls, greaterThan(before));

          await tester.tap(find.text(l10n.pushOpen));
          await tester.pumpAndSettle();
          expect(path(h), Routes.orderLocation(orderId));
        },
      );
    });

    group('notification settings ($lang)', () {
      Future<TestHarness> settings(
        WidgetTester tester, {
        bool available = true,
        PushPermission permission = PushPermission.notDetermined,
      }) async {
        final h = await launch(
          tester,
          available: available,
          permission: permission,
        );
        h.read(appRouterProvider).go(Routes.notificationSettings.path);
        await tester.pumpAndSettle();
        return h;
      }

      testWidgets('push switches for orders and support only', (tester) async {
        await settings(tester, permission: PushPermission.granted);

        expect(
          find.byKey(const ValueKey('preference-orders-push')),
          findsOneWidget,
        );
        await tester.ensureVisible(
          find.byKey(const ValueKey('preference-support-push')),
        );
        expect(
          find.byKey(const ValueKey('preference-support-push')),
          findsOneWidget,
        );
        expect(
          find.byKey(const ValueKey('preference-marketing-push')),
          findsNothing,
        );
        expect(
          find.byKey(const ValueKey('preference-security-push')),
          findsNothing,
        );
        expect(find.byKey(const ValueKey('push-allow')), findsNothing);
      });

      testWidgets('a push switch saves the push cell', (tester) async {
        final h = await settings(tester, permission: PushPermission.granted);

        await tester.tap(find.byKey(const ValueKey('preference-orders-push')));
        await tester.pumpAndSettle();

        final change = h.notifications.updates.single.single;
        expect(
          (change.category, change.channel, change.enabled),
          ('orders', 'push', false),
        );
      });

      testWidgets(
        'without permission on this phone, "Turn on" asks the system',
        (tester) async {
          final h = await settings(tester);
          await tester.ensureVisible(find.byKey(const ValueKey('push-allow')));
          await tester.pumpAndSettle();
          expect(find.text(l10n.preferencePushOffTitle), findsOneWidget);

          await tester.tap(find.byKey(const ValueKey('push-allow')));
          await tester.pumpAndSettle();

          expect(h.push.permissionRequests, 1);
          expect(find.byKey(const ValueKey('push-allow')), findsNothing);
          expect(h.pushTokens.registered.single.token, 'fcm-token-1');
        },
      );

      testWidgets(
        'without Firebase in the build, a note instead of the button',
        (tester) async {
          await settings(tester, available: false);
          await tester.ensureVisible(find.text(l10n.preferencePushUnavailable));
          expect(find.text(l10n.preferencePushUnavailable), findsOneWidget);
          expect(find.byKey(const ValueKey('push-allow')), findsNothing);
        },
      );
    });
  }
}
