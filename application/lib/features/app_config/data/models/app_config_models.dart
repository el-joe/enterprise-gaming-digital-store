import 'package:application/core/money/currency_info.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'app_config_models.freezed.dart';
part 'app_config_models.g.dart';

@freezed
abstract class AppConfigDto with _$AppConfigDto {
  const factory AppConfigDto({
    required Map<String, AppVersionPolicyDto> versions,
    required MaintenanceDto maintenance,
    required AppFeaturesDto features,
    required LocalesDto locales,
    required CurrenciesDto currencies,
    required MediaDto media,
    required LinksDto links,
    @JsonKey(fromJson: _gatewaysFromJson)
    required Map<String, Map<String, String?>> paymentGateways,
    AppUpdateDto? update,
  }) = _AppConfigDto;

  const AppConfigDto._();

  factory AppConfigDto.fromJson(Map<String, dynamic> json) =>
      _$AppConfigDtoFromJson(json);

  AppConfig toEntity() => AppConfig(
    versions: versions.map((key, value) => MapEntry(key, value.toEntity())),
    maintenanceEnabled: maintenance.enabled,
    maintenanceMessage: maintenance.message,
    features: features.toEntity(),
    supportedLocales: locales.supported,
    defaultLocale: locales.defaultLocale,
    baseCurrency: currencies.base,
    currencies: [
      for (final currency in currencies.supported)
        AppCurrency(
          name: currency.name,
          info: CurrencyInfo(
            code: currency.code,
            symbol: currency.symbol,
            exponent: currency.exponent,
          ),
        ),
    ],
    mediaBaseUrl: Uri.parse(media.baseUrl),
    links: AppLinks(
      support: _uri(links.support),
      terms: _uri(links.terms),
      privacy: _uri(links.privacy),
      refund: _uri(links.refund),
      webCheckout: _uri(links.webCheckout),
    ),
    paymentGateways: paymentGateways,
    update: update?.toEntity(),
  );
}

Uri? _uri(String? value) => value == null ? null : Uri.parse(value);

/// Gateways map; an entry without public values (the wallet) is `{}` or, from PHP, `[]`.
Map<String, Map<String, String?>> _gatewaysFromJson(Object? json) {
  if (json is List && json.isEmpty) {
    return const {};
  }
  if (json is! Map<String, dynamic>) {
    throw const FormatException('payment_gateways must be an object.');
  }
  return json.map((key, value) {
    if (value is List && value.isEmpty) {
      return MapEntry(key, const <String, String?>{});
    }
    if (value is! Map<String, dynamic>) {
      throw FormatException('payment_gateways.$key must be an object.');
    }
    return MapEntry(key, value.map((k, v) => MapEntry(k, v as String?)));
  });
}

@freezed
abstract class AppVersionPolicyDto with _$AppVersionPolicyDto {
  const factory AppVersionPolicyDto({
    required String minSupportedVersion,
    required String latestVersion,
  }) = _AppVersionPolicyDto;

  const AppVersionPolicyDto._();

  factory AppVersionPolicyDto.fromJson(Map<String, dynamic> json) =>
      _$AppVersionPolicyDtoFromJson(json);

  AppVersionPolicy toEntity() => AppVersionPolicy(
    minSupportedVersion: minSupportedVersion,
    latestVersion: latestVersion,
  );
}

@freezed
abstract class AppUpdateDto with _$AppUpdateDto {
  const factory AppUpdateDto({
    required String platform,
    required String version,
    required bool required,
    required bool available,
  }) = _AppUpdateDto;

  const AppUpdateDto._();

  factory AppUpdateDto.fromJson(Map<String, dynamic> json) =>
      _$AppUpdateDtoFromJson(json);

  AppUpdate toEntity() => AppUpdate(
    platform: platform,
    version: version,
    required: this.required,
    available: available,
  );
}

@freezed
abstract class MaintenanceDto with _$MaintenanceDto {
  const factory MaintenanceDto({required bool enabled, String? message}) =
      _MaintenanceDto;

  factory MaintenanceDto.fromJson(Map<String, dynamic> json) =>
      _$MaintenanceDtoFromJson(json);
}

@freezed
abstract class AppFeaturesDto with _$AppFeaturesDto {
  const factory AppFeaturesDto({
    required bool checkoutEnabled,
    required bool guestCheckoutEnabled,
    required bool walletEnabled,
    required bool reviewsEnabled,
    required bool supportEnabled,
    // OD-1 (owner decision 3): "Complete purchase on boxcards.shop"; absent on older servers.
    @Default(false) bool webCheckoutRedirect,
  }) = _AppFeaturesDto;

  const AppFeaturesDto._();

  factory AppFeaturesDto.fromJson(Map<String, dynamic> json) =>
      _$AppFeaturesDtoFromJson(json);

  AppFeatures toEntity() => AppFeatures(
    checkoutEnabled: checkoutEnabled,
    guestCheckoutEnabled: guestCheckoutEnabled,
    walletEnabled: walletEnabled,
    reviewsEnabled: reviewsEnabled,
    supportEnabled: supportEnabled,
    webCheckoutRedirect: webCheckoutRedirect,
  );
}

@freezed
abstract class LocalesDto with _$LocalesDto {
  const factory LocalesDto({
    required List<String> supported,
    @JsonKey(name: 'default') required String defaultLocale,
  }) = _LocalesDto;

  factory LocalesDto.fromJson(Map<String, dynamic> json) =>
      _$LocalesDtoFromJson(json);
}

@freezed
abstract class CurrencyDto with _$CurrencyDto {
  const factory CurrencyDto({
    required String code,
    required String name,
    required String symbol,
    required int exponent,
  }) = _CurrencyDto;

  factory CurrencyDto.fromJson(Map<String, dynamic> json) =>
      _$CurrencyDtoFromJson(json);
}

@freezed
abstract class CurrenciesDto with _$CurrenciesDto {
  const factory CurrenciesDto({
    required String base,
    required List<CurrencyDto> supported,
  }) = _CurrenciesDto;

  factory CurrenciesDto.fromJson(Map<String, dynamic> json) =>
      _$CurrenciesDtoFromJson(json);
}

@freezed
abstract class MediaDto with _$MediaDto {
  const factory MediaDto({required String baseUrl}) = _MediaDto;

  factory MediaDto.fromJson(Map<String, dynamic> json) =>
      _$MediaDtoFromJson(json);
}

@freezed
abstract class LinksDto with _$LinksDto {
  const factory LinksDto({
    String? support,
    String? terms,
    String? privacy,
    String? refund,
    String? webCheckout,
  }) = _LinksDto;

  factory LinksDto.fromJson(Map<String, dynamic> json) =>
      _$LinksDtoFromJson(json);
}
