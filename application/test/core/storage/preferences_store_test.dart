import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/memory_stores.dart';

void main() {
  late InMemoryPreferencesBackend backend;
  late ProviderContainer container;

  setUp(() {
    backend = InMemoryPreferencesBackend();
    container = ProviderContainer(
      overrides: [
        preferencesStoreProvider.overrideWithValue(PreferencesStore(backend)),
        deviceLocalesProvider.overrideWithValue(const [Locale('fr')]),
      ],
    );
    addTearDown(container.dispose);
  });

  test('stores only the allow-listed, non-sensitive keys', () {
    expect(PreferencesStore.allowedKeys, {
      PreferencesStore.localeKey,
      PreferencesStore.currencyKey,
      PreferencesStore.onboardingCompletedKey,
      // M-A10: two "shown once" flags; no secret, token or biometric key material.
      PreferencesStore.biometricOfferShownKey,
      PreferencesStore.integrityWarningShownKey,
      // M-A11: local search history (terms the customer typed, no secret).
      PreferencesStore.searchHistoryKey,
      // M-A18: the push explainer was offered once (a flag; the FCM token is never stored here).
      PreferencesStore.pushPromptShownKey,
    });
    for (final key in PreferencesStore.allowedKeys) {
      expect(key, isNot(matches(RegExp('token|password|secret|key'))));
    }
  });

  test('locale falls back to English and persists the choice', () async {
    expect(container.read(appLocaleProvider), AppLocales.english);
    await container
        .read(appLocaleProvider.notifier)
        .select(const Locale('ar', 'SA'));
    expect(container.read(appLocaleProvider), AppLocales.arabic);
    expect(backend.values[PreferencesStore.localeKey], 'ar');
  });

  test('display currency is validated and persisted', () async {
    expect(container.read(displayCurrencyProvider), isNull);
    await container.read(displayCurrencyProvider.notifier).select('egp');
    expect(container.read(displayCurrencyProvider), 'EGP');
    expect(backend.values[PreferencesStore.currencyKey], 'EGP');
    expect(
      () => container.read(displayCurrencyProvider.notifier).select('EURO'),
      throwsArgumentError,
    );
  });

  test('onboarding flag', () async {
    final store = PreferencesStore(backend);
    expect(store.onboardingCompleted, isFalse);
    await store.setOnboardingCompleted();
    expect(store.onboardingCompleted, isTrue);
  });
}
