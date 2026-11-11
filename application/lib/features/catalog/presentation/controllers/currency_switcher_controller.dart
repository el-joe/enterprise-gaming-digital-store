import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/account/account_providers.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/catalog/catalog_providers.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'currency_switcher_controller.g.dart';

@immutable
final class CurrencySwitcherState {
  const CurrencySwitcherState({
    required this.options,
    this.selected,
    this.applying = false,
    this.failure,
  });

  /// Active display currencies (`GET /storefront/currencies`, else `GET /app/config`).
  final Loadable<List<DisplayCurrencyOption>> options;

  /// The highlighted code; null until chosen when the server picked the currency.
  final String? selected;
  final bool applying;
  final Failure? failure;

  CurrencySwitcherState copyWith({
    Loadable<List<DisplayCurrencyOption>>? options,
    String? selected,
    bool? applying,
    Failure? Function()? failure,
  }) => CurrencySwitcherState(
    options: options ?? this.options,
    selected: selected ?? this.selected,
    applying: applying ?? this.applying,
    failure: failure == null ? this.failure : failure(),
  );
}

/// Currency switcher sheet (03.5). Applying saves the choice (on the profile when signed in,
/// through `UpdateRegionalSettings`; locally otherwise) and changes the `X-Currency` the app
/// sends. Every priced provider watches `pricingContextProvider`, so they all reload in the new
/// currency (owner decision 1: prices always come from the server in the display currency).
@riverpod
class CurrencySwitcherController extends _$CurrencySwitcherController {
  @override
  CurrencySwitcherState build() {
    unawaited(_load());
    return CurrencySwitcherState(
      options: const Loadable.loading(),
      selected: ref.read(displayCurrencyProvider),
    );
  }

  Future<void> retry() async {
    state = state.copyWith(options: const Loadable.loading());
    await _load();
  }

  void choose(String code) =>
      state = state.copyWith(selected: code, failure: () => null);

  /// Saves the chosen currency. Returns whether the sheet can close.
  Future<bool> apply() async {
    final code = state.selected;
    if (code == null || state.applying) {
      return false;
    }
    if (code == ref.read(displayCurrencyProvider)) {
      return true;
    }
    state = state.copyWith(applying: true, failure: () => null);
    final Failure? failure;
    if (ref.read(sessionProvider) == SessionStatus.signedIn) {
      final result = await ref
          .read(updateRegionalSettingsProvider)
          .call(displayCurrency: code);
      failure = result.failureOrNull;
    } else {
      await ref.read(displayCurrencyProvider.notifier).select(code);
      failure = null;
    }
    if (!ref.mounted) {
      return failure == null;
    }
    state = state.copyWith(applying: false, failure: () => failure);
    return failure == null;
  }

  /// `GET /storefront/currencies` (the active currencies with fresh rates, roadmap 4.1); when it
  /// fails, the currencies of the last `GET /app/config`, else the failure.
  Future<void> _load() async {
    final result = await ref.read(catalogRepositoryProvider).currencies();
    if (!ref.mounted) {
      return;
    }
    final configured =
        ref.read(latestAppConfigProvider)?.currencies ?? const [];
    state = state.copyWith(
      options: switch (result) {
        Ok(:final value) => Loadable.loaded(value),
        Err() when configured.isNotEmpty => Loadable.loaded([
          for (final currency in configured)
            DisplayCurrencyOption(name: currency.name, info: currency.info),
        ]),
        Err(:final failure) => Loadable.failed(failure),
      },
    );
  }
}
