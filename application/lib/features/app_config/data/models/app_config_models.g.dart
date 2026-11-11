// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'app_config_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_AppConfigDto _$AppConfigDtoFromJson(
  Map<String, dynamic> json,
) => _AppConfigDto(
  versions: (json['versions'] as Map<String, dynamic>).map(
    (k, e) =>
        MapEntry(k, AppVersionPolicyDto.fromJson(e as Map<String, dynamic>)),
  ),
  maintenance: MaintenanceDto.fromJson(
    json['maintenance'] as Map<String, dynamic>,
  ),
  features: AppFeaturesDto.fromJson(json['features'] as Map<String, dynamic>),
  locales: LocalesDto.fromJson(json['locales'] as Map<String, dynamic>),
  currencies: CurrenciesDto.fromJson(
    json['currencies'] as Map<String, dynamic>,
  ),
  media: MediaDto.fromJson(json['media'] as Map<String, dynamic>),
  links: LinksDto.fromJson(json['links'] as Map<String, dynamic>),
  paymentGateways: _gatewaysFromJson(json['payment_gateways']),
  update: json['update'] == null
      ? null
      : AppUpdateDto.fromJson(json['update'] as Map<String, dynamic>),
);

Map<String, dynamic> _$AppConfigDtoToJson(_AppConfigDto instance) =>
    <String, dynamic>{
      'versions': instance.versions.map((k, e) => MapEntry(k, e.toJson())),
      'maintenance': instance.maintenance.toJson(),
      'features': instance.features.toJson(),
      'locales': instance.locales.toJson(),
      'currencies': instance.currencies.toJson(),
      'media': instance.media.toJson(),
      'links': instance.links.toJson(),
      'payment_gateways': instance.paymentGateways,
      'update': instance.update?.toJson(),
    };

_AppVersionPolicyDto _$AppVersionPolicyDtoFromJson(Map<String, dynamic> json) =>
    _AppVersionPolicyDto(
      minSupportedVersion: json['min_supported_version'] as String,
      latestVersion: json['latest_version'] as String,
    );

Map<String, dynamic> _$AppVersionPolicyDtoToJson(
  _AppVersionPolicyDto instance,
) => <String, dynamic>{
  'min_supported_version': instance.minSupportedVersion,
  'latest_version': instance.latestVersion,
};

_AppUpdateDto _$AppUpdateDtoFromJson(Map<String, dynamic> json) =>
    _AppUpdateDto(
      platform: json['platform'] as String,
      version: json['version'] as String,
      required: json['required'] as bool,
      available: json['available'] as bool,
    );

Map<String, dynamic> _$AppUpdateDtoToJson(_AppUpdateDto instance) =>
    <String, dynamic>{
      'platform': instance.platform,
      'version': instance.version,
      'required': instance.required,
      'available': instance.available,
    };

_MaintenanceDto _$MaintenanceDtoFromJson(Map<String, dynamic> json) =>
    _MaintenanceDto(
      enabled: json['enabled'] as bool,
      message: json['message'] as String?,
    );

Map<String, dynamic> _$MaintenanceDtoToJson(_MaintenanceDto instance) =>
    <String, dynamic>{'enabled': instance.enabled, 'message': instance.message};

_AppFeaturesDto _$AppFeaturesDtoFromJson(Map<String, dynamic> json) =>
    _AppFeaturesDto(
      checkoutEnabled: json['checkout_enabled'] as bool,
      guestCheckoutEnabled: json['guest_checkout_enabled'] as bool,
      walletEnabled: json['wallet_enabled'] as bool,
      reviewsEnabled: json['reviews_enabled'] as bool,
      supportEnabled: json['support_enabled'] as bool,
      webCheckoutRedirect: json['web_checkout_redirect'] as bool? ?? false,
    );

Map<String, dynamic> _$AppFeaturesDtoToJson(_AppFeaturesDto instance) =>
    <String, dynamic>{
      'checkout_enabled': instance.checkoutEnabled,
      'guest_checkout_enabled': instance.guestCheckoutEnabled,
      'wallet_enabled': instance.walletEnabled,
      'reviews_enabled': instance.reviewsEnabled,
      'support_enabled': instance.supportEnabled,
      'web_checkout_redirect': instance.webCheckoutRedirect,
    };

_LocalesDto _$LocalesDtoFromJson(Map<String, dynamic> json) => _LocalesDto(
  supported: (json['supported'] as List<dynamic>)
      .map((e) => e as String)
      .toList(),
  defaultLocale: json['default'] as String,
);

Map<String, dynamic> _$LocalesDtoToJson(_LocalesDto instance) =>
    <String, dynamic>{
      'supported': instance.supported,
      'default': instance.defaultLocale,
    };

_CurrencyDto _$CurrencyDtoFromJson(Map<String, dynamic> json) => _CurrencyDto(
  code: json['code'] as String,
  name: json['name'] as String,
  symbol: json['symbol'] as String,
  exponent: (json['exponent'] as num).toInt(),
);

Map<String, dynamic> _$CurrencyDtoToJson(_CurrencyDto instance) =>
    <String, dynamic>{
      'code': instance.code,
      'name': instance.name,
      'symbol': instance.symbol,
      'exponent': instance.exponent,
    };

_CurrenciesDto _$CurrenciesDtoFromJson(Map<String, dynamic> json) =>
    _CurrenciesDto(
      base: json['base'] as String,
      supported: (json['supported'] as List<dynamic>)
          .map((e) => CurrencyDto.fromJson(e as Map<String, dynamic>))
          .toList(),
    );

Map<String, dynamic> _$CurrenciesDtoToJson(_CurrenciesDto instance) =>
    <String, dynamic>{
      'base': instance.base,
      'supported': instance.supported.map((e) => e.toJson()).toList(),
    };

_MediaDto _$MediaDtoFromJson(Map<String, dynamic> json) =>
    _MediaDto(baseUrl: json['base_url'] as String);

Map<String, dynamic> _$MediaDtoToJson(_MediaDto instance) => <String, dynamic>{
  'base_url': instance.baseUrl,
};

_LinksDto _$LinksDtoFromJson(Map<String, dynamic> json) => _LinksDto(
  support: json['support'] as String?,
  terms: json['terms'] as String?,
  privacy: json['privacy'] as String?,
  refund: json['refund'] as String?,
  webCheckout: json['web_checkout'] as String?,
);

Map<String, dynamic> _$LinksDtoToJson(_LinksDto instance) => <String, dynamic>{
  'support': instance.support,
  'terms': instance.terms,
  'privacy': instance.privacy,
  'refund': instance.refund,
  'web_checkout': instance.webCheckout,
};
