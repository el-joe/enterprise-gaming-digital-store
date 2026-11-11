import 'dart:async';

import 'package:application/core/money/currency_info.dart';
import 'package:application/core/money/money.dart';
import 'package:application/core/money/money_formatter.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/cart/cart_providers.dart';
import 'package:application/features/wallet/domain/entities/wallet.dart';
import 'package:application/features/wallet/wallet_providers.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:decimal/decimal.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'wallet_controller.g.dart';

/// The balance and the first statement page, loaded together.
@immutable
final class WalletOverview {
  const WalletOverview({
    required this.wallet,
    required this.lines,
    required this.currency,
  });

  final Wallet wallet;

  /// Every statement line loaded so far (pages appended), newest first.
  final List<WalletTransaction> lines;

  /// How statement amounts are written: the wallet currency's symbol and exponent.
  final CurrencyInfo currency;

  bool get frozen => wallet.status == 'frozen';

  WalletOverview withLines(List<WalletTransaction> lines) =>
      WalletOverview(wallet: wallet, lines: lines, currency: currency);
}

/// State of the wallet screen (12.1 to 12.3).
@immutable
final class WalletState {
  const WalletState({
    this.enabled = true,
    this.signedIn = true,
    this.content = const Loadable.loading(),
    this.page,
    this.loadingMore = false,
    this.moreFailure,
    this.refreshing = false,
  });

  /// `features.wallet_enabled`: off, the screen says the wallet is not available.
  final bool enabled;

  /// Signed out (the guard sends the customer to sign in first): nothing is loaded.
  final bool signedIn;

  final Loadable<WalletOverview> content;

  /// The last statement page's meta.
  final PageInfo? page;
  final bool loadingMore;

  /// A next page failed: the statement stays, with a retry bar (no automatic retry loop).
  final Failure? moreFailure;
  final bool refreshing;

  List<WalletTransaction> get lines => content.valueOrNull?.lines ?? const [];

  bool get hasMore => page?.hasNextPage ?? false;

  /// Loaded with no statement line (12.3).
  bool get isEmpty => content is Loaded<WalletOverview> && lines.isEmpty;

  WalletState copyWith({
    Loadable<WalletOverview>? content,
    PageInfo? Function()? page,
    bool? loadingMore,
    Failure? Function()? moreFailure,
    bool? refreshing,
  }) => WalletState(
    enabled: enabled,
    signedIn: signedIn,
    content: content ?? this.content,
    page: page == null ? this.page : page(),
    loadingMore: loadingMore ?? this.loadingMore,
    moreFailure: moreFailure == null ? this.moreFailure : moreFailure(),
    refreshing: refreshing ?? this.refreshing,
  );
}

/// The wallet screen: `GET /account/wallet` and `GET /account/wallet/transactions` (newest
/// first, 20 per page, `meta` paging). Read only: there is no top-up (BD3). `wallet_enabled`
/// off loads nothing. A sign-in, a sign-out and [CartRevision] (an order was placed or a payment
/// answered, which can move the balance) reload it.
@riverpod
class WalletController extends _$WalletController {
  int _generation = 0;

  @override
  WalletState build() {
    final enabled = ref.watch(walletEnabledProvider);
    final session = ref.watch(sessionProvider);
    ref.watch(cartRevisionProvider);
    if (!enabled) {
      return const WalletState(enabled: false);
    }
    if (session != SessionStatus.signedIn) {
      return const WalletState(signedIn: false);
    }
    unawaited(_loadFirst());
    return const WalletState();
  }

  bool get _active => state.enabled && state.signedIn;

  Future<void> _loadFirst({bool keep = false}) async {
    final generation = ++_generation;
    final repository = ref.read(walletRepositoryProvider);
    final walletCall = repository.wallet();
    final linesCall = repository.transactions();
    final wallet = await walletCall;
    final lines = await linesCall;
    if (!ref.mounted || generation != _generation) {
      return;
    }
    final failure = wallet.failureOrNull ?? lines.failureOrNull;
    if (failure != null) {
      state = keep && state.content is Loaded<WalletOverview>
          ? state.copyWith(refreshing: false, moreFailure: () => failure)
          : state.copyWith(
              content: Loadable.failed(failure),
              page: () => null,
              refreshing: false,
            );
      return;
    }
    final value = wallet.valueOrNull!;
    final page = lines.valueOrNull!;
    state = state.copyWith(
      content: Loadable.loaded(
        WalletOverview(
          wallet: value,
          lines: page.items,
          currency: _currencyOf(value),
        ),
      ),
      page: () => page.page,
      moreFailure: () => null,
      loadingMore: false,
      refreshing: false,
    );
  }

  /// The wallet currency's symbol and exponent from the app configuration; else the code as
  /// the symbol and the balance's own scale (the server formats the balance to the exponent).
  CurrencyInfo _currencyOf(Wallet wallet) {
    final currencies =
        ref.read(latestAppConfigProvider)?.currencies ?? const [];
    for (final currency in currencies) {
      if (currency.info.code == wallet.currency) {
        return currency.info;
      }
    }
    return CurrencyInfo(
      code: wallet.currency,
      symbol: wallet.currency,
      exponent: wallet.balance.scale,
    );
  }

  /// Opens the store's support page in the system browser (frozen wallet, 12.2). False when no
  /// link is configured or nothing could open it.
  Future<bool> openSupport() async {
    final url = ref.read(latestAppConfigProvider)?.links.support;
    return url != null &&
        await ref.read(nativeBridgeProvider).openExternalUrl(url);
  }

  /// "Try again" after a failed first load.
  Future<void> retry() async {
    if (!_active) {
      return;
    }
    state = const WalletState();
    await _loadFirst();
  }

  /// Pull to refresh: the balance and the statement stay while page 1 reloads.
  Future<void> refresh() async {
    if (!_active) {
      return;
    }
    state = state.copyWith(refreshing: true);
    await _loadFirst(keep: true);
  }

  /// The next statement page, when there is one and none is loading. A failed page is kept as
  /// [WalletState.moreFailure] until the customer retries.
  Future<void> loadMore() async {
    final page = state.page;
    final overview = state.content.valueOrNull;
    if (!_active ||
        state.loadingMore ||
        page == null ||
        !page.hasNextPage ||
        overview == null) {
      return;
    }
    final generation = _generation;
    state = state.copyWith(loadingMore: true, moreFailure: () => null);
    final result = await ref
        .read(walletRepositoryProvider)
        .transactions(page: page.currentPage + 1);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = switch (result) {
      Ok(:final value) => state.copyWith(
        content: Loadable.loaded(
          overview.withLines([...state.lines, ...value.items]),
        ),
        page: () => value.page,
        loadingMore: false,
      ),
      Err(:final failure) => state.copyWith(
        loadingMore: false,
        moreFailure: () => failure,
      ),
    };
  }
}

/// Whether a support link is configured (the frozen state's "Contact support").
@riverpod
bool walletSupportAvailable(Ref ref) =>
    ref.watch(latestAppConfigProvider)?.links.support != null;

/// A ledger amount (a decimal string such as `"-10.000000"`) written in the wallet currency,
/// never through floating point: `-$10.00`, or `+$12.50` with [signed] for a credit. Western
/// digits (owner decision 2).
String walletAmountText(
  String amount,
  CurrencyInfo currency, {
  bool signed = false,
}) {
  final Money money;
  try {
    money = Money.parse(amount.trim(), currency.code);
  } on FormatException {
    return '${currency.code} $amount';
  }
  final text = MoneyFormatter.format(money, currency);
  // A "+" only on an amount that shows as more than zero.
  final shown = money.amount.truncate(scale: currency.exponent);
  return signed && shown > Decimal.zero ? '+$text' : text;
}
