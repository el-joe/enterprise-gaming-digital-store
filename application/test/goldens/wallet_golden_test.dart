import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';
import 'package:application/features/wallet/domain/entities/wallet.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/checkout_fakes.dart';
import '../helpers/pump_app.dart';
import '../helpers/wallet_fakes.dart';

/// The 12.1 statement as drawn: a refund, a payment and a staff credit, in local time so the
/// dates do not move with the machine's time zone.
List<WalletTransaction> drawnLines() => [
  walletLine(
    id: 3,
    order: const WalletOrderRef(publicId: orderId2, orderNumber: 'BX-1002'),
    at: DateTime(2026, 11, 3, 9, 12),
  ),
  walletLine(
    id: 2,
    type: 'debit_purchase',
    amount: '-10.000000',
    balanceAfter: '27.500000',
    order: const WalletOrderRef(publicId: orderId, orderNumber: 'BX-0998'),
    at: DateTime(2026, 10, 28, 18, 40),
  ),
  walletLine(
    id: 1,
    type: 'credit_admin',
    amount: '25.000000',
    balanceAfter: '37.500000',
    at: DateTime(2026, 10, 20, 11, 3),
  ),
];

/// Key M-A15 states in LTR (en) and RTL (ar), compared with the approved M-A8 frames (12.1 to
/// 12.3 and the 07.2 wallet option) by review.
void main() {
  setUpAll(loadAppFonts);

  for (final lang in ['en', 'ar']) {
    final dir = lang == 'ar' ? 'rtl' : 'ltr';

    Future<void> snap(WidgetTester tester, String name) async {
      await precacheImages(tester);
      await expectLater(
        find.byType(MaterialApp),
        matchesGoldenFile('m_a15/${name}_$dir.png'),
      );
    }

    Future<TestHarness> at(
      WidgetTester tester,
      String location, {
      AppConfig? config,
      void Function(TestHarness h)? arrange,
      Size size = const Size(390, 844),
      bool settle = true,
    }) async {
      final h = await pumpHarness(
        tester,
        languageCode: lang,
        signedIn: true,
        size: size,
        config: [Ok(config ?? dollarConfig())],
        arrange: (h) {
          h.wallet
            ..walletResult = Ok(dollarWallet('40.00'))
            ..pages = {1: Ok(walletPageOf(drawnLines()))};
          arrange?.call(h);
        },
      );
      h.read(appRouterProvider).go(location);
      if (settle) {
        await tester.pumpAndSettle();
      } else {
        await tester.pump();
      }
      return h;
    }

    group('M-A15 goldens ($dir)', () {
      testWidgets('12.1 wallet with statement and next page', (tester) async {
        final h = await at(
          tester,
          Routes.wallet.path,
          arrange: (h) {
            h.wallet.pages = {1: Ok(walletPageOf(drawnLines(), lastPage: 2))};
            // The next page is still loading: its skeleton row shows (as drawn).
            h.wallet.pageGates[2] = Completer<void>();
          },
        );
        await snap(tester, 'wallet');
        h.wallet.pageGates[2]!.complete();
        await tester.pumpAndSettle();
      });

      testWidgets('12.2 wallet frozen', (tester) async {
        await at(
          tester,
          Routes.wallet.path,
          arrange: (h) => h.wallet.walletResult = Ok(
            dollarWallet('40.00', status: 'frozen'),
          ),
        );
        await snap(tester, 'wallet_frozen');
      });

      testWidgets('12.3 wallet empty', (tester) async {
        await at(
          tester,
          Routes.wallet.path,
          arrange: (h) => h.wallet
            ..walletResult = Ok(dollarWallet('0.00'))
            ..pages = {1: Ok(walletPageOf(const []))},
        );
        await snap(tester, 'wallet_empty');
      });

      testWidgets('wallet loading', (tester) async {
        final gate = Completer<void>();
        await at(
          tester,
          Routes.wallet.path,
          settle: false,
          arrange: (h) => h.wallet.walletGate = gate,
        );
        await tester.pump(const Duration(milliseconds: 50));
        await snap(tester, 'wallet_loading');
        gate.complete();
        await tester.pumpAndSettle();
      });

      testWidgets('wallet server error', (tester) async {
        await at(
          tester,
          Routes.wallet.path,
          arrange: (h) => h.wallet.walletResult = const Err(
            ServerFailure(
              statusCode: 500,
              requestId: '01JFREQ0000000000000000000',
            ),
          ),
        );
        await snap(tester, 'wallet_error');
      });

      testWidgets('wallet offline', (tester) async {
        await at(
          tester,
          Routes.wallet.path,
          arrange: (h) => h.wallet.pages = {1: const Err(NetworkFailure())},
        );
        await snap(tester, 'wallet_offline');
      });

      testWidgets('wallet next page failed', (tester) async {
        await at(
          tester,
          Routes.wallet.path,
          arrange: (h) => h.wallet.pages = {
            1: Ok(walletPageOf(drawnLines(), lastPage: 2)),
            2: const Err(NetworkFailure()),
          },
        );
        await snap(tester, 'wallet_more_failed');
      });

      testWidgets('wallet not available (wallet_enabled off)', (tester) async {
        await at(
          tester,
          Routes.wallet.path,
          config: dollarConfig(walletEnabled: false),
        );
        await snap(tester, 'wallet_unavailable');
      });

      testWidgets('wallet at 360x800', (tester) async {
        await at(tester, Routes.wallet.path, size: const Size(360, 800));
        await snap(tester, 'wallet_360');
      });

      testWidgets('account tab with the wallet entry', (tester) async {
        await at(tester, Routes.account.path);
        await snap(tester, 'account_wallet_entry');
      });

      testWidgets('07.2 checkout wallet option with balance', (tester) async {
        await at(
          tester,
          Routes.checkout.path,
          arrange: (h) =>
              h.checkout.onOptions = (_) =>
                  Ok([option('wallet'), option('stripe'), option('paypal')]),
        );
        await snap(tester, 'checkout_wallet');
      });
    });
  }
}
