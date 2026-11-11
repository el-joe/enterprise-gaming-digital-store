import 'dart:ui';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'onboarding_controller.g.dart';

@immutable
final class OnboardingState {
  const OnboardingState({required this.currencies, this.selectedCurrency});

  /// Active currencies from `GET /app/config`.
  final List<AppCurrency> currencies;
  final String? selectedCurrency;
}

/// Onboarding (01.5, 01.6): the language applies at once (the screen mirrors); the display
/// currency is saved (sent as `X-Currency`) only on "Start shopping". "Skip" keeps the server's
/// country-based currency (owner decision 1).
@riverpod
class OnboardingController extends _$OnboardingController {
  @override
  OnboardingState build() {
    final config = ref.watch(latestAppConfigProvider);
    return OnboardingState(
      currencies: config?.currencies ?? const [],
      selectedCurrency: ref.read(displayCurrencyProvider),
    );
  }

  Future<void> chooseLanguage(String languageCode) =>
      ref.read(appLocaleProvider.notifier).select(Locale(languageCode));

  void chooseCurrency(String code) {
    state = OnboardingState(
      currencies: state.currencies,
      selectedCurrency: code,
    );
  }

  Future<void> finish({required bool saveCurrency}) async {
    final currency = state.selectedCurrency;
    if (saveCurrency && currency != null) {
      await ref.read(displayCurrencyProvider.notifier).select(currency);
    }
    await ref.read(preferencesStoreProvider).setOnboardingCompleted();
  }
}
