import 'package:application/core/money/currency_info.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'app_config.freezed.dart';

/// `GET /app/config` (API_DOCUMENTATION.md 1d): public configuration, no secrets.
@freezed
abstract class AppConfig with _$AppConfig {
  const factory AppConfig({
    required Map<String, AppVersionPolicy> versions,
    required bool maintenanceEnabled,
    required AppFeatures features,
    required List<String> supportedLocales,
    required String defaultLocale,
    required String baseCurrency,
    required List<AppCurrency> currencies,
    required Uri mediaBaseUrl,
    required AppLinks links,

    /// Active gateways keyed by gateway key, each with its public identifiers only
    /// (`stripe.publishable_key`, `paypal.client_id`, `paymob.public_key`).
    required Map<String, Map<String, String?>> paymentGateways,
    String? maintenanceMessage,
    AppUpdate? update,
  }) = _AppConfig;

  const AppConfig._();

  /// Stripe is unavailable when the gateway is absent or its key is null.
  String? get stripePublishableKey =>
      paymentGateways['stripe']?['publishable_key'];

  bool isGatewayActive(String key) => paymentGateways.containsKey(key);
}

@freezed
abstract class AppVersionPolicy with _$AppVersionPolicy {
  const factory AppVersionPolicy({
    required String minSupportedVersion,
    required String latestVersion,
  }) = _AppVersionPolicy;
}

/// The server's verdict for the version the app sent.
@freezed
abstract class AppUpdate with _$AppUpdate {
  const factory AppUpdate({
    required String platform,
    required String version,
    required bool required,
    required bool available,
  }) = _AppUpdate;
}

@freezed
abstract class AppFeatures with _$AppFeatures {
  const factory AppFeatures({
    required bool checkoutEnabled,
    required bool guestCheckoutEnabled,
    required bool walletEnabled,
    required bool reviewsEnabled,
    required bool supportEnabled,

    /// OD-1 (owner decision 3): checkout opens [AppLinks.webCheckout] in the
    /// system browser instead of the in-app checkout.
    @Default(false) bool webCheckoutRedirect,
  }) = _AppFeatures;
}

@freezed
abstract class AppCurrency with _$AppCurrency {
  const factory AppCurrency({
    required String name,
    required CurrencyInfo info,
  }) = _AppCurrency;
}

@freezed
abstract class AppLinks with _$AppLinks {
  const factory AppLinks({
    Uri? support,
    Uri? terms,
    Uri? privacy,
    Uri? refund,
    Uri? webCheckout,
  }) = _AppLinks;
}
