import 'dart:async';

import 'package:application/core/money/currency_info.dart';
import 'package:application/core/money/money.dart';
import 'package:application/core/network/result.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';
import 'package:application/features/wallet/data/models/wallet_models.dart';
import 'package:application/features/wallet/domain/entities/wallet.dart';
import 'package:application/features/wallet/domain/repositories/wallet_repository.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';

import 'fakes.dart';
import 'fixtures.dart';

/// `wallet/<name>` decoded through the real DTO.
Wallet fixtureWallet([String name = 'wallet_ok']) {
  final body = ApiFixture.load('wallet/$name').body as Map<String, dynamic>;
  final data = body['data'] as Map<String, dynamic>;
  return WalletDto.fromJson(data['wallet'] as Map<String, dynamic>).toEntity();
}

/// The two lines of `wallet/transactions_ok` decoded through the real DTO: a purchase debit
/// linked to an order, then a refund credit without one.
List<WalletTransaction> fixtureWalletLines() {
  final body =
      ApiFixture.load('wallet/transactions_ok').body as Map<String, dynamic>;
  return [
    for (final line in body['data'] as List<Object?>)
      WalletTransactionDto.fromJson(line! as Map<String, dynamic>).toEntity(),
  ];
}

/// A statement line for tests.
WalletTransaction walletLine({
  required int id,
  String type = 'credit_refund',
  String amount = '12.500000',
  String balanceAfter = '40.000000',
  WalletOrderRef? order,
  String? note,
  DateTime? at,
}) => WalletTransaction(
  id: id,
  type: type,
  amount: amount,
  balanceAfter: balanceAfter,
  createdAt: at ?? DateTime.utc(2026, 11, 3, 9, 12),
  order: order,
  note: note,
);

/// One statement page of [items].
Paged<WalletTransaction> walletPageOf(
  List<WalletTransaction> items, {
  int page = 1,
  int lastPage = 1,
  int perPage = 20,
}) => Paged(
  items: items,
  page: PageInfo(
    currentPage: page,
    perPage: perPage,
    total: items.length,
    lastPage: lastPage,
  ),
);

/// Answers the wallet and its statement from fields the test sets.
class FakeWalletRepository implements WalletRepository {
  Result<Wallet> walletResult = Ok(fixtureWallet());

  /// One answer per page number; a missing page is empty.
  Map<int, Result<Paged<WalletTransaction>>> pages = {
    1: Ok(walletPageOf(fixtureWalletLines())),
  };

  var walletCalls = 0;
  final pageCalls = <int>[];

  /// When set, the balance call waits for it.
  Completer<void>? walletGate;

  /// A page number's call waits for its completer.
  final pageGates = <int, Completer<void>>{};

  @override
  Future<Result<Wallet>> wallet() async {
    walletCalls++;
    await walletGate?.future;
    return walletResult;
  }

  @override
  Future<Result<Paged<WalletTransaction>>> transactions({
    int page = 1,
    int perPage = 20,
  }) async {
    pageCalls.add(page);
    await pageGates[page]?.future;
    return pages[page] ?? Ok(walletPageOf(const [], page: page));
  }
}

/// The app configuration with `$` for USD (the contract fixture uses the code as the symbol).
AppConfig dollarConfig({bool walletEnabled = true, bool support = true}) {
  final config = fixtureAppConfig();
  return config.copyWith(
    currencies: const [
      AppCurrency(
        name: 'US Dollar',
        info: CurrencyInfo(code: 'USD', symbol: r'$', exponent: 2),
      ),
    ],
    features: config.features.copyWith(walletEnabled: walletEnabled),
    links: support ? config.links : config.links.copyWith(support: null),
  );
}

Wallet dollarWallet(String amount, {String status = 'active'}) => Wallet(
  currency: 'USD',
  balance: Money.parse(amount, 'USD', formatted: '\$$amount'),
  status: status,
  topUpAvailable: false,
);
