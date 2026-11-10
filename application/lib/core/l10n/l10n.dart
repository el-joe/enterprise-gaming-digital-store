import 'dart:ui' show PlatformDispatcher;

import 'package:application/core/l10n/generated/app_localizations.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:flutter/widgets.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

export 'package:application/core/l10n/generated/app_localizations.dart';

part 'l10n.g.dart';

extension AppLocalizationsX on BuildContext {
  AppLocalizations get l10n => AppLocalizations.of(this);
}

/// Languages the app ships. Arabic is laid out right to left.
abstract final class AppLocales {
  static const english = Locale('en');
  static const arabic = Locale('ar');
  static const supported = [english, arabic];

  static bool isRtl(Locale locale) => locale.languageCode == 'ar';

  /// First supported language among [preferred], else English.
  static Locale resolve(Iterable<Locale> preferred) {
    for (final locale in preferred) {
      for (final candidate in supported) {
        if (candidate.languageCode == locale.languageCode) {
          return candidate;
        }
      }
    }
    return english;
  }
}

/// Device locales; a provider so tests can pin them.
@Riverpod(keepAlive: true)
List<Locale> deviceLocales(Ref ref) => PlatformDispatcher.instance.locales;

/// The app language: the saved choice, else the device language when supported, else English.
@Riverpod(keepAlive: true)
class AppLocale extends _$AppLocale {
  @override
  Locale build() {
    final saved = ref.watch(preferencesStoreProvider).languageCode;
    if (saved != null) {
      return AppLocales.resolve([Locale(saved)]);
    }
    return AppLocales.resolve(ref.watch(deviceLocalesProvider));
  }

  Future<void> select(Locale locale) async {
    final resolved = AppLocales.resolve([locale]);
    await ref
        .read(preferencesStoreProvider)
        .setLanguageCode(resolved.languageCode);
    state = resolved;
  }
}
