import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:shared_preferences/shared_preferences.dart';

part 'preferences_store.g.dart';

/// Minimal key-value backend so the store can run on `SharedPreferencesWithCache` in the app and
/// on memory in tests.
abstract interface class PreferencesBackend {
  String? getString(String key);
  bool? getBool(String key);
  Future<void> setString(String key, String value);
  Future<void> setBool(String key, {required bool value});
  Future<void> remove(String key);
}

final class _SharedPreferencesBackend implements PreferencesBackend {
  _SharedPreferencesBackend(this._prefs);

  final SharedPreferencesWithCache _prefs;

  @override
  String? getString(String key) => _prefs.getString(key);

  @override
  bool? getBool(String key) => _prefs.getBool(key);

  @override
  Future<void> setString(String key, String value) =>
      _prefs.setString(key, value);

  @override
  Future<void> setBool(String key, {required bool value}) =>
      _prefs.setBool(key, value);

  @override
  Future<void> remove(String key) => _prefs.remove(key);
}

/// Non-sensitive preferences only (locale, display currency, onboarding flag). Tokens and any
/// other secret belong in `SecureStore`.
class PreferencesStore {
  PreferencesStore(this._backend);

  static const localeKey = 'prefs.locale';
  static const currencyKey = 'prefs.currency';
  static const onboardingCompletedKey = 'prefs.onboarding_completed';

  /// The biometric unlock offer was shown once after a first sign-in (owner decision 6).
  static const biometricOfferShownKey = 'prefs.biometric_offer_shown';

  /// The rooted or jailbroken device warning was shown once (owner decision 5).
  static const integrityWarningShownKey = 'prefs.integrity_warning_shown';

  static const allowedKeys = {
    localeKey,
    currencyKey,
    onboardingCompletedKey,
    biometricOfferShownKey,
    integrityWarningShownKey,
  };

  final PreferencesBackend _backend;

  static Future<PreferencesStore> open() async {
    final prefs = await SharedPreferencesWithCache.create(
      cacheOptions: const SharedPreferencesWithCacheOptions(
        allowList: allowedKeys,
      ),
    );
    return PreferencesStore(_SharedPreferencesBackend(prefs));
  }

  /// Language code chosen in the app (`en` or `ar`), or null to follow the device.
  String? get languageCode => _backend.getString(localeKey);

  Future<void> setLanguageCode(String code) =>
      _backend.setString(localeKey, code);

  /// ISO 4217 display currency, or null for the server default.
  String? get currency => _backend.getString(currencyKey);

  Future<void> setCurrency(String code) =>
      _backend.setString(currencyKey, code);

  Future<void> clearCurrency() => _backend.remove(currencyKey);

  bool get onboardingCompleted =>
      _backend.getBool(onboardingCompletedKey) ?? false;

  Future<void> setOnboardingCompleted() =>
      _backend.setBool(onboardingCompletedKey, value: true);

  bool get biometricOfferShown =>
      _backend.getBool(biometricOfferShownKey) ?? false;

  Future<void> setBiometricOfferShown() =>
      _backend.setBool(biometricOfferShownKey, value: true);

  bool get integrityWarningShown =>
      _backend.getBool(integrityWarningShownKey) ?? false;

  Future<void> setIntegrityWarningShown() =>
      _backend.setBool(integrityWarningShownKey, value: true);
}

/// Overridden in `bootstrap.dart` with the opened store.
@Riverpod(keepAlive: true)
PreferencesStore preferencesStore(Ref ref) {
  throw StateError('preferencesStoreProvider must be overridden in bootstrap.');
}

/// Display currency sent as `X-Currency`; null until the customer picks one.
@Riverpod(keepAlive: true)
class DisplayCurrency extends _$DisplayCurrency {
  @override
  String? build() => ref.watch(preferencesStoreProvider).currency;

  Future<void> select(String code) async {
    final normalised = code.toUpperCase();
    if (!RegExp(r'^[A-Z]{3}$').hasMatch(normalised)) {
      throw ArgumentError.value(code, 'code', 'Not an ISO 4217 code');
    }
    await ref.read(preferencesStoreProvider).setCurrency(normalised);
    state = normalised;
  }
}
