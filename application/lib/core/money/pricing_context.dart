import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'pricing_context.g.dart';

/// What every priced or translated response depends on: the display currency sent as
/// `X-Currency` (null: the server picks, owner decision 1) and the language sent as
/// `Accept-Language`.
typedef PricingContext = ({String? currency, String language});

/// Every provider that holds prices (home, listings) watches this, so a currency switch or a
/// language change invalidates all priced data at once and each screen reloads from page 1.
/// Records compare by value, so dependants rebuild only on a real change.
@Riverpod(keepAlive: true)
PricingContext pricingContext(Ref ref) => (
  currency: ref.watch(displayCurrencyProvider),
  language: ref.watch(appLocaleProvider).languageCode,
);
