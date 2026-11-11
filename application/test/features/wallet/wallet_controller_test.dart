import 'dart:async';

import 'package:application/core/money/currency_info.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/cart/cart_providers.dart';
import 'package:application/features/checkout/presentation/controllers/checkout_controller.dart';
import 'package:application/features/wallet/domain/entities/wallet.dart';
import 'package:application/features/wallet/presentation/controllers/wallet_controller.dart';
import 'package:application/features/wallet/wallet_providers.dart';
import 'package:flutter_riverpod/misc.dart' show ProviderListenable;
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/fakes.dart';
import '../../helpers/pump_app.dart';
import '../../helpers/wallet_fakes.dart';

Future<void> settle() async {
  for (var i = 0; i < 100; i++) {
    await Future<void>.delayed(Duration.zero);
  }
}

void keep<T>(TestHarness h, ProviderListenable<T> provider) {
  final sub = h.container.listen(provider, (_, _) {});
  addTearDown(sub.close);
}

List<WalletTransaction> lines(int from, int to) => [
  for (var i = from; i <= to; i++) walletLine(id: i),
];

/// Sets the configuration the launch check would have stored, with [walletEnabled].
void configure(TestHarness h, {required bool walletEnabled}) {
  final config = fixtureAppConfig();
  h
      .read(latestAppConfigProvider.notifier)
      .set(
        config.copyWith(
          features: config.features.copyWith(walletEnabled: walletEnabled),
        ),
      );
}

void main() {
  TestHarness harness({bool signedIn = true}) {
    final h = buildHarness(signedIn: signedIn);
    addTearDown(h.dispose);
    return h;
  }

  WalletState stateOf(TestHarness h) => h.read(walletControllerProvider);
  WalletController controllerOf(TestHarness h) =>
      h.read(walletControllerProvider.notifier);

  group('wallet controller', () {
    test('loads the balance and the first page together', () async {
      final h = harness();
      keep(h, walletControllerProvider);
      expect(stateOf(h).content, isA<Loading<WalletOverview>>());
      await settle();
      final overview = stateOf(h).content.valueOrNull!;
      expect(overview.wallet.balance.amountText, '15.00');
      expect(overview.frozen, isFalse);
      expect(overview.lines.map((l) => l.type), [
        'debit_purchase',
        'credit_refund',
      ]);
      expect(overview.lines.first.order?.publicId, isNotNull);
      expect(h.wallet.walletCalls, 1);
      expect(h.wallet.pageCalls, [1]);
      expect(stateOf(h).hasMore, isFalse);
    });

    test('pages through meta: appends, stops at the last page', () async {
      final h = harness();
      h.wallet.pages = {
        1: Ok(walletPageOf(lines(1, 20), lastPage: 2)),
        2: Ok(walletPageOf(lines(21, 25), page: 2, lastPage: 2)),
      };
      keep(h, walletControllerProvider);
      await settle();
      expect(stateOf(h).lines, hasLength(20));
      expect(stateOf(h).hasMore, isTrue);

      await controllerOf(h).loadMore();
      expect(stateOf(h).lines, hasLength(25));
      expect(stateOf(h).lines.last.id, 25);
      expect(stateOf(h).hasMore, isFalse);
      await controllerOf(h).loadMore();
      expect(h.wallet.pageCalls, [1, 2]);
      expect(h.wallet.walletCalls, 1, reason: 'paging reads no balance');
    });

    test(
      'a failed next page keeps the statement and is not retried by itself',
      () async {
        final h = harness();
        h.wallet.pages = {
          1: Ok(walletPageOf(lines(1, 20), lastPage: 3)),
          2: const Err(NetworkFailure()),
        };
        keep(h, walletControllerProvider);
        await settle();
        await controllerOf(h).loadMore();
        expect(stateOf(h).lines, hasLength(20));
        expect(stateOf(h).moreFailure, isA<NetworkFailure>());
        expect(h.wallet.pageCalls, [1, 2]);

        h.wallet.pages[2] = Ok(
          walletPageOf(lines(21, 40), page: 2, lastPage: 3),
        );
        await controllerOf(h).loadMore();
        expect(stateOf(h).moreFailure, isNull);
        expect(stateOf(h).lines, hasLength(40));
      },
    );

    test('one page loads at a time', () async {
      final h = harness();
      h.wallet.pages = {
        1: Ok(walletPageOf(lines(1, 20), lastPage: 2)),
        2: Ok(walletPageOf(lines(21, 22), page: 2, lastPage: 2)),
      };
      keep(h, walletControllerProvider);
      await settle();
      final first = controllerOf(h).loadMore();
      final second = controllerOf(h).loadMore();
      await Future.wait([first, second]);
      expect(h.wallet.pageCalls, [1, 2]);
    });

    test(
      'refresh reloads page 1 and the balance and keeps the list while it runs',
      () async {
        final h = harness();
        h.wallet.pages = {
          1: Ok(walletPageOf(lines(1, 20), lastPage: 2)),
          2: Ok(walletPageOf(lines(21, 22), page: 2, lastPage: 2)),
        };
        keep(h, walletControllerProvider);
        await settle();
        await controllerOf(h).loadMore();
        expect(stateOf(h).lines, hasLength(22));

        h.wallet.walletResult = Ok(fixtureWallet('wallet_frozen'));
        h.wallet.pages = {
          1: Ok(walletPageOf([walletLine(id: 99)])),
        };
        final refreshing = controllerOf(h).refresh();
        expect(stateOf(h).refreshing, isTrue);
        expect(stateOf(h).lines, hasLength(22));
        await refreshing;
        expect(stateOf(h).refreshing, isFalse);
        expect(stateOf(h).lines.single.id, 99);
        expect(stateOf(h).content.valueOrNull!.frozen, isTrue);
        expect(h.wallet.walletCalls, 2);
      },
    );

    test('a failed refresh keeps what was shown', () async {
      final h = harness();
      keep(h, walletControllerProvider);
      await settle();
      h.wallet.walletResult = const Err(NetworkFailure());
      await controllerOf(h).refresh();
      expect(stateOf(h).content, isA<Loaded<WalletOverview>>());
      expect(stateOf(h).lines, hasLength(2));
      expect(stateOf(h).moreFailure, isA<NetworkFailure>());
    });

    test(
      'a failed balance or statement fails the screen until retry',
      () async {
        for (final breakBalance in [true, false]) {
          final h = harness();
          if (breakBalance) {
            h.wallet.walletResult = const Err(
              ServerFailure(statusCode: 500, requestId: 'req-1'),
            );
          } else {
            h.wallet.pages = {1: const Err(NetworkFailure())};
          }
          keep(h, walletControllerProvider);
          await settle();
          expect(stateOf(h).content, isA<Failed<WalletOverview>>());

          h.wallet
            ..walletResult = Ok(fixtureWallet())
            ..pages = {1: Ok(walletPageOf(fixtureWalletLines()))};
          await controllerOf(h).retry();
          expect(stateOf(h).content, isA<Loaded<WalletOverview>>());
        }
      },
    );

    test('an empty wallet is loaded and empty', () async {
      final h = harness();
      h.wallet
        ..walletResult = Ok(fixtureWallet('wallet_empty'))
        ..pages = {1: Ok(walletPageOf(const []))};
      keep(h, walletControllerProvider);
      await settle();
      expect(stateOf(h).isEmpty, isTrue);
      expect(stateOf(h).content.valueOrNull!.wallet.balance.amountText, '0.00');
    });

    test(
      'wallet_enabled off loads nothing and says it is not available',
      () async {
        final h = harness();
        configure(h, walletEnabled: false);
        keep(h, walletControllerProvider);
        await settle();
        expect(stateOf(h).enabled, isFalse);
        expect(h.wallet.walletCalls, 0);
        expect(h.wallet.pageCalls, isEmpty);
        await controllerOf(h).loadMore();
        await controllerOf(h).refresh();
        await controllerOf(h).retry();
        expect(h.wallet.walletCalls, 0);

        configure(h, walletEnabled: true);
        await settle();
        expect(stateOf(h).enabled, isTrue);
        expect(stateOf(h).content, isA<Loaded<WalletOverview>>());
      },
    );

    test('signed out loads nothing; signing in loads it', () async {
      final h = harness(signedIn: false);
      keep(h, walletControllerProvider);
      await settle();
      expect(stateOf(h).signedIn, isFalse);
      expect(h.wallet.walletCalls, 0);

      h.read(sessionProvider.notifier).markSignedIn();
      await settle();
      expect(stateOf(h).signedIn, isTrue);
      expect(stateOf(h).lines, hasLength(2));
    });

    test(
      'a placed order or payment answer (CartRevision) reloads the balance',
      () async {
        final h = harness();
        keep(h, walletControllerProvider);
        await settle();
        h.read(cartRevisionProvider.notifier).bump();
        await settle();
        expect(h.wallet.walletCalls, 2);
        expect(h.wallet.pageCalls, [1, 1]);
      },
    );

    test('a late answer from before a reload is dropped', () async {
      final h = harness();
      keep(h, walletControllerProvider);
      await settle();
      h.wallet.pages = {
        1: Ok(walletPageOf(lines(1, 20), lastPage: 2)),
        2: Ok(walletPageOf([walletLine(id: 500)], page: 2, lastPage: 2)),
      };
      await controllerOf(h).refresh();
      final gate = Completer<void>();
      h.wallet.pageGates[2] = gate;
      final more = controllerOf(h).loadMore();
      await controllerOf(h).refresh();
      gate.complete();
      await more;
      expect(stateOf(h).lines.where((l) => l.id == 500), isEmpty);
    });

    test('the wallet currency comes from the app configuration', () async {
      final h = harness();
      final config = fixtureAppConfig();
      h.read(latestAppConfigProvider.notifier).set(config);
      keep(h, walletControllerProvider);
      await settle();
      expect(
        stateOf(h).content.valueOrNull!.currency,
        config.currencies.firstWhere((c) => c.info.code == 'USD').info,
      );
    });

    test(
      'without a configured currency the code and the balance scale are used',
      () async {
        final h = harness();
        keep(h, walletControllerProvider);
        await settle();
        expect(
          stateOf(h).content.valueOrNull!.currency,
          const CurrencyInfo(code: 'USD', symbol: 'USD', exponent: 2),
        );
      },
    );

    test('contact support opens the configured https link only', () async {
      final h = harness();
      keep(h, walletControllerProvider);
      await settle();
      expect(await controllerOf(h).openSupport(), isFalse);
      expect(h.native.opened, isEmpty);

      final config = fixtureAppConfig();
      h
          .read(latestAppConfigProvider.notifier)
          .set(
            config.copyWith(
              links: config.links.copyWith(
                support: Uri.parse('https://boxcards.test/support'),
              ),
            ),
          );
      expect(h.read(walletSupportAvailableProvider), isTrue);
      expect(await controllerOf(h).openSupport(), isTrue);
      expect(
        h.native.opened.single.toString(),
        'https://boxcards.test/support',
      );
    });

    test('the wallet screen is for signed-in customers only', () {
      expect(Routes.wallet.access, RouteAccess.signedIn);
    });
  });

  group('statement amounts', () {
    const usd = CurrencyInfo(code: 'USD', symbol: r'$', exponent: 2);
    const kwd = CurrencyInfo(code: 'KWD', symbol: 'KD', exponent: 3);

    test(
      'signed credit and debit at the currency exponent, never rounded up',
      () {
        expect(walletAmountText('12.500000', usd, signed: true), r'+$12.50');
        expect(walletAmountText('-10.000000', usd, signed: true), r'-$10.00');
        expect(walletAmountText('40.000000', usd), r'$40.00');
        expect(walletAmountText('0.009999', usd, signed: true), r'$0.00');
        expect(walletAmountText('1.239999', usd), r'$1.23');
        expect(walletAmountText('-0.125000', kwd, signed: true), '-KD0.125');
        expect(walletAmountText('123456789.990000', usd), r'$123456789.99');
      },
    );

    test('a malformed amount is shown as sent, with the code', () {
      expect(walletAmountText('abc', usd), 'USD abc');
      expect(walletAmountText('1e3', usd), 'USD 1e3');
    });

    test('credit or debit follows the sign of the amount', () {
      expect(walletLine(id: 1, amount: '0.000000').isCredit, isTrue);
      expect(walletLine(id: 1, amount: '-0.010000').isCredit, isFalse);
    });
  });

  group('checkout wallet option (M-A13 follow-up)', () {
    Future<TestHarness> checkoutHarness({bool? walletEnabled}) async {
      final h = buildHarness(signedIn: true);
      addTearDown(h.dispose);
      if (walletEnabled != null) {
        configure(h, walletEnabled: walletEnabled);
      }
      h.checkout.onOptions = (_) => Ok([option('wallet'), option('paypal')]);
      keep(h, checkoutControllerProvider);
      await settle();
      return h;
    }

    test('shows the wallet balance from GET /account/wallet', () async {
      final h = await checkoutHarness(walletEnabled: true);
      final state = h.read(checkoutControllerProvider);
      expect(state.loadedQuote!.options.map((o) => o.gateway), [
        'wallet',
        'paypal',
      ]);
      expect(state.gateway, 'wallet');
      expect(state.walletBalance?.amountText, '15.00');
      expect(h.wallet.walletCalls, 1);
    });

    test(
      'wallet_enabled off drops the wallet option and reads no balance',
      () async {
        final h = await checkoutHarness(walletEnabled: false);
        final state = h.read(checkoutControllerProvider);
        expect(state.loadedQuote!.options.map((o) => o.gateway), ['paypal']);
        expect(state.gateway, 'paypal');
        expect(state.walletBalance, isNull);
        expect(h.wallet.walletCalls, 0);
        h.read(checkoutControllerProvider.notifier).selectOption('wallet');
        expect(h.read(checkoutControllerProvider).gateway, 'paypal');
      },
    );

    test('no wallet option, no balance call', () async {
      final h = buildHarness(signedIn: true);
      addTearDown(h.dispose);
      keep(h, checkoutControllerProvider);
      await settle();
      expect(h.read(checkoutControllerProvider).loadedQuote, isNotNull);
      expect(h.wallet.walletCalls, 0);
    });

    test('a failed balance only hides the balance', () async {
      final h = buildHarness(signedIn: true);
      addTearDown(h.dispose);
      h.wallet.walletResult = const Err(NetworkFailure());
      h.checkout.onOptions = (_) => Ok([option('wallet')]);
      keep(h, checkoutControllerProvider);
      await settle();
      final state = h.read(checkoutControllerProvider);
      expect(state.gateway, 'wallet');
      expect(state.walletBalance, isNull);
      expect(state.canPlace, isTrue);
    });

    test('before the launch check the wallet counts as enabled', () {
      final h = buildHarness(signedIn: true);
      addTearDown(h.dispose);
      expect(h.read(walletEnabledProvider), isTrue);
      configure(h, walletEnabled: false);
      expect(h.read(walletEnabledProvider), isFalse);
    });
  });
}
