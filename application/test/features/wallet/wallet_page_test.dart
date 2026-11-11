import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';
import 'package:application/features/wallet/domain/entities/wallet.dart';
import 'package:application/features/wallet/presentation/widgets/wallet_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/pump_app.dart';
import '../../helpers/wallet_fakes.dart';

const linkedOrder = WalletOrderRef(publicId: orderId, orderNumber: 'BX-0998');

/// [amount] as the balance card's figure.
Finder balance(String amount) => find.descendant(
  of: find.byType(WalletBalanceCard),
  matching: find.text(amount),
);

void main() {
  for (final lang in ['en', 'ar']) {
    final l10n = l10nFor(lang);

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      bool signedIn = true,
      AppConfig? config,
      void Function(TestHarness h)? arrange,
      Size size = const Size(390, 844),
      bool settle = true,
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: signedIn,
        size: size,
        config: [Ok(config ?? dollarConfig())],
        arrange: (h) {
          h.wallet
            ..walletResult = Ok(dollarWallet('40.00'))
            ..pages = {
              1: Ok(
                walletPageOf([
                  walletLine(
                    id: 3,
                    order: linkedOrder.copyWith(orderNumber: 'BX-1002'),
                  ),
                  walletLine(
                    id: 2,
                    type: 'debit_purchase',
                    amount: '-10.000000',
                    balanceAfter: '27.500000',
                    order: linkedOrder,
                  ),
                  walletLine(
                    id: 1,
                    type: 'credit_admin',
                    amount: '25.000000',
                    balanceAfter: '37.500000',
                    note: 'Goodwill credit',
                  ),
                ]),
              ),
            };
          arrange?.call(h);
        },
        settle: false,
      );
      await tester.pumpAndSettle();
      h.read(appRouterProvider).go(location);
      if (settle) {
        await tester.pumpAndSettle();
      } else {
        await tester.pump();
      }
      return h;
    }

    String path(TestHarness h) => h.read(appRouterProvider).state.uri.path;

    group('wallet ($lang)', () {
      testWidgets('the Account tab opens the wallet', (tester) async {
        final h = await at(tester, Routes.account.path);
        // M-A16: the hub's Wallet row shows the balance (17.1).
        expect(find.text('\u2066\$40.00\u2069'), findsOneWidget);
        await tester.tap(find.text(l10n.walletTitle));
        await tester.pumpAndSettle();
        expect(path(h), Routes.wallet.path);
        expect(balance(r'$40.00'), findsOneWidget);
        expect(find.text(l10n.walletStatusActive), findsOneWidget);
        expect(find.text(l10n.walletBalanceNote), findsOneWidget);
        expect(find.text(l10n.walletStatement), findsOneWidget);

        await tester.tap(find.byTooltip(l10n.actionBack).first);
        await tester.pumpAndSettle();
        expect(path(h), Routes.account.path);
      });

      testWidgets('statement: type, order, signed amounts and balance after', (
        tester,
      ) async {
        await at(tester, Routes.wallet.path);
        expect(
          find.text(
            l10n.walletLineWithOrder(
              l10n.walletTypeCreditRefund,
              '\u2066BX-1002\u2069',
            ),
          ),
          findsOneWidget,
        );
        expect(
          find.text(
            l10n.walletLineWithOrder(
              l10n.walletTypeDebitPurchase,
              '\u2066BX-0998\u2069',
            ),
          ),
          findsOneWidget,
        );
        expect(find.text(l10n.walletTypeCreditAdmin), findsOneWidget);
        expect(find.text('Goodwill credit'), findsOneWidget);
        expect(find.text(r'+$12.50'), findsOneWidget);
        expect(find.text(r'-$10.00'), findsOneWidget);
        expect(find.text(r'+$25.00'), findsOneWidget);
        expect(find.text(r'$27.50'), findsOneWidget);
        // Amounts never mirror: every amount is an LTR text in both locales.
        for (final amount in [
          find.text(r'+$12.50'),
          find.text(r'-$10.00'),
          balance(r'$40.00'),
        ]) {
          expect(tester.widget<Text>(amount).textDirection, TextDirection.ltr);
        }
        expect(
          Directionality.of(tester.element(find.text(r'-$10.00'))),
          lang == 'ar' ? TextDirection.rtl : TextDirection.ltr,
        );
      });

      testWidgets('a line with an order opens the M-A14 order detail', (
        tester,
      ) async {
        final h = await at(tester, Routes.wallet.path);
        await tester.tap(find.text(r'-$10.00'));
        await tester.pumpAndSettle();
        expect(path(h), Routes.orderLocation(orderId));
        expect(h.orders.details, [orderId]);
      });

      testWidgets('a line without an order is not a button', (tester) async {
        final h = await at(tester, Routes.wallet.path);
        await tester.tap(find.text(r'+$25.00'));
        await tester.pumpAndSettle();
        expect(path(h), Routes.wallet.path);
      });

      testWidgets('loading shows the skeleton, announced once', (tester) async {
        final gate = Completer<void>();
        await at(
          tester,
          Routes.wallet.path,
          settle: false,
          arrange: (h) => h.wallet.walletGate = gate,
        );
        expect(find.byType(WalletSkeleton), findsOneWidget);
        expect(find.bySemanticsLabel(l10n.loadingLabel), findsOneWidget);
        gate.complete();
        await tester.pumpAndSettle();
        expect(find.byType(WalletSkeleton), findsNothing);
        expect(balance(r'$40.00'), findsOneWidget);
      });

      testWidgets('empty: zero balance and no transactions (12.3)', (
        tester,
      ) async {
        await at(
          tester,
          Routes.wallet.path,
          arrange: (h) => h.wallet
            ..walletResult = Ok(dollarWallet('0.00'))
            ..pages = {1: Ok(walletPageOf(const []))},
        );
        expect(balance(r'$0.00'), findsOneWidget);
        expect(find.text(l10n.walletEmptyTitle), findsOneWidget);
        expect(find.text(l10n.walletEmptyBody), findsOneWidget);
        expect(find.text(l10n.walletStatement), findsNothing);
      });

      testWidgets('frozen: banner, badge and contact support (12.2)', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.wallet.path,
          arrange: (h) => h.wallet.walletResult = Ok(
            dollarWallet('40.00', status: 'frozen'),
          ),
        );
        expect(find.text(l10n.walletFrozenTitle), findsOneWidget);
        expect(find.text(l10n.walletFrozenBody), findsOneWidget);
        expect(find.text(l10n.walletStatusFrozen), findsOneWidget);
        // The statement stays readable while frozen.
        expect(find.text(r'-$10.00'), findsOneWidget);
        await tester.tap(find.text(l10n.walletContactSupport));
        await tester.pumpAndSettle();
        expect(h.native.opened.single, Uri.parse('https://help.boxcards.test'));
      });

      testWidgets('frozen without a support link has no footer', (
        tester,
      ) async {
        await at(
          tester,
          Routes.wallet.path,
          config: dollarConfig(support: false),
          arrange: (h) => h.wallet.walletResult = Ok(
            dollarWallet('40.00', status: 'frozen'),
          ),
        );
        expect(find.text(l10n.walletFrozenTitle), findsOneWidget);
        expect(find.text(l10n.walletContactSupport), findsNothing);
      });

      testWidgets('server error shows the request id; Try again reloads', (
        tester,
      ) async {
        final h = await at(
          tester,
          Routes.wallet.path,
          arrange: (h) => h.wallet.walletResult = const Err(
            ServerFailure(statusCode: 500, requestId: 'req-wallet-1'),
          ),
        );
        expect(find.text(l10n.walletLoadErrorTitle), findsOneWidget);
        expect(find.text(l10n.errorServerBody('req-wallet-1')), findsOneWidget);
        h.wallet.walletResult = Ok(dollarWallet('40.00'));
        await tester.tap(find.text(l10n.actionTryAgain));
        await tester.pumpAndSettle();
        expect(balance(r'$40.00'), findsOneWidget);
      });

      testWidgets('offline shows the banner; Retry reloads', (tester) async {
        final h = await at(
          tester,
          Routes.wallet.path,
          arrange: (h) => h.wallet.pages = {1: const Err(NetworkFailure())},
        );
        expect(find.text(l10n.offlineBannerTitle), findsOneWidget);
        expect(find.text(l10n.noConnectionTitle), findsOneWidget);
        h.wallet.pages = {
          1: Ok(walletPageOf([walletLine(id: 7)])),
        };
        await tester.tap(find.text(l10n.actionRetry));
        await tester.pumpAndSettle();
        expect(find.text(r'+$12.50'), findsOneWidget);
      });

      testWidgets(
        'scrolling loads the next page; a failed page has a retry bar',
        (tester) async {
          final h = await at(
            tester,
            Routes.wallet.path,
            arrange: (h) => h.wallet.pages = {
              1: Ok(
                walletPageOf([
                  for (var i = 1; i <= 20; i++) walletLine(id: i),
                ], lastPage: 3),
              ),
              2: const Err(NetworkFailure()),
            },
          );
          await tester.drag(find.byType(ListView).last, const Offset(0, -4000));
          await tester.pumpAndSettle();
          expect(h.wallet.pageCalls, [1, 2]);
          await tester.drag(find.byType(ListView).last, const Offset(0, -4000));
          await tester.pumpAndSettle();
          expect(find.text(l10n.walletMoreFailed), findsOneWidget);
          expect(h.wallet.pageCalls, [1, 2], reason: 'no retry loop');

          h.wallet.pages[2] = Ok(
            walletPageOf(
              [walletLine(id: 21, amount: '3.000000')],
              page: 2,
              lastPage: 2,
            ),
          );
          await tester.tap(find.text(l10n.actionRetry));
          await tester.pumpAndSettle();
          await tester.drag(find.byType(ListView).last, const Offset(0, -4000));
          await tester.pumpAndSettle();
          expect(find.text(r'+$3.00'), findsOneWidget);
          expect(find.text(l10n.walletMoreFailed), findsNothing);
        },
      );

      testWidgets('pull to refresh reloads the balance', (tester) async {
        final h = await at(tester, Routes.wallet.path);
        h.wallet.walletResult = Ok(dollarWallet('52.50'));
        await tester.fling(
          find.byType(ListView).last,
          const Offset(0, 500),
          1000,
        );
        await tester.pumpAndSettle();
        expect(balance(r'$52.50'), findsOneWidget);
        expect(h.wallet.walletCalls, 2);
      });

      testWidgets(
        'wallet_enabled off: no Account entry; the screen is unavailable',
        (tester) async {
          final h = await at(
            tester,
            Routes.account.path,
            config: dollarConfig(walletEnabled: false),
          );
          expect(find.text(l10n.walletAccountSubtitle), findsNothing);
          h.read(appRouterProvider).go(Routes.wallet.path);
          await tester.pumpAndSettle();
          expect(find.text(l10n.walletUnavailableTitle), findsOneWidget);
          expect(h.wallet.walletCalls, 0);
        },
      );

      testWidgets('signed out, the wallet asks to sign in first', (
        tester,
      ) async {
        final h = await at(tester, Routes.wallet.path, signedIn: false);
        expect(path(h), Routes.login.path);
        expect(
          h
              .read(appRouterProvider)
              .state
              .uri
              .queryParameters[Routes.fromParameter],
          Routes.wallet.path,
        );
        expect(h.wallet.walletCalls, 0);
      });

      testWidgets('screen reader labels and 48dp targets', (tester) async {
        final handle = tester.ensureSemantics();
        await at(tester, Routes.wallet.path);
        expect(
          find.bySemanticsLabel(
            l10n.walletBalanceSemantics(r'$40.00', l10n.walletStatusActive),
          ),
          findsOneWidget,
        );
        final line = find.byKey(const ValueKey('wallet-line-2'));
        final semantics = tester.getSemantics(
          find.descendant(of: line, matching: find.byType(Semantics)).first,
        );
        expect(semantics.label, contains(r'-$10.00'));
        expect(semantics.label, contains(r'$27.50'));
        expect(semantics.hint, l10n.walletLineOpenOrderHint);
        expect(tester.getSize(line).height, greaterThanOrEqualTo(64));
        await expectLater(tester, meetsGuideline(androidTapTargetGuideline));
        await expectLater(tester, meetsGuideline(labeledTapTargetGuideline));
        handle.dispose();
      });

      testWidgets('fits 360 wide with a long balance', (tester) async {
        await at(
          tester,
          Routes.wallet.path,
          size: const Size(360, 800),
          arrange: (h) =>
              h.wallet.walletResult = Ok(dollarWallet('1234567890.00')),
        );
        expect(tester.takeException(), isNull);
        expect(balance(r'$1234567890.00'), findsOneWidget);
      });

      testWidgets('checkout shows the wallet balance in the wallet option', (
        tester,
      ) async {
        await at(
          tester,
          Routes.checkout.path,
          arrange: (h) =>
              h.checkout.onOptions = (_) =>
                  Ok([option('wallet'), option('paypal')]),
        );
        expect(
          find.text(l10n.checkoutWalletBalance('\u2066\$40.00\u2069')),
          findsOneWidget,
        );
      });

      testWidgets(
        'checkout hides the wallet option when wallet_enabled is off',
        (tester) async {
          await at(
            tester,
            Routes.checkout.path,
            config: dollarConfig(walletEnabled: false),
            arrange: (h) =>
                h.checkout.onOptions = (_) =>
                    Ok([option('wallet'), option('paypal')]),
          );
          expect(find.byKey(const ValueKey('option-wallet')), findsNothing);
          expect(find.byKey(const ValueKey('option-paypal')), findsOneWidget);
        },
      );
    });
  }
}
