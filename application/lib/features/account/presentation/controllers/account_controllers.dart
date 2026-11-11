import 'dart:async';
import 'dart:ui';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/account/account_providers.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/auth/presentation/controllers/session_user.dart';
import 'package:application/features/wallet/wallet_providers.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'account_controllers.g.dart';

/// The signed-in customer's profile (`GET /account/profile`) for the Account hub (17.1),
/// Profile (17.2) and Security (18.1). Loaded when signed in, cleared on sign-out; a failed load
/// stays failed until [reload] (the hub falls back to the signed-in user).
@Riverpod(keepAlive: true)
class AccountProfileController extends _$AccountProfileController {
  int _generation = 0;

  @override
  Loadable<Profile>? build() {
    final session = ref.watch(sessionProvider);
    if (session != SessionStatus.signedIn) {
      _generation++;
      return null;
    }
    unawaited(_load());
    return const Loadable.loading();
  }

  Future<void> reload() async {
    if (ref.read(sessionProvider) != SessionStatus.signedIn) {
      return;
    }
    state = const Loadable.loading();
    await _load();
  }

  Future<void> _load() async {
    final generation = ++_generation;
    final result = await ref.read(accountRepositoryProvider).profile();
    if (!ref.mounted || generation != _generation) {
      return;
    }
    switch (result) {
      case Ok(:final value):
        replace(value);
      case Err(:final failure):
        state = Loadable.failed(failure);
    }
  }

  /// Takes a profile the server returned (after an update) and keeps the signed-in user in step
  /// (name, email verification and two-step verification).
  void replace(Profile profile) {
    _generation++;
    state = Loadable.loaded(profile);
    final user = ref.read(currentUserProvider);
    if (user != null) {
      ref
          .read(currentUserProvider.notifier)
          .signedIn(
            user.copyWith(
              name: profile.name,
              email: profile.email,
              emailVerified: profile.emailVerified,
              twoFactorEnabled: profile.twoFactorEnabled,
            ),
          );
    }
  }

  /// Two-step verification was turned on or off on this device.
  void setTwoFactor({required bool enabled}) {
    final profile = state?.valueOrNull;
    if (profile != null) {
      replace(profile.copyWith(twoFactorEnabled: enabled));
      return;
    }
    final user = ref.read(currentUserProvider);
    if (user != null) {
      ref
          .read(currentUserProvider.notifier)
          .signedIn(user.copyWith(twoFactorEnabled: enabled));
    }
  }
}

/// What the hub header (17.1) and the Security row show: the profile when loaded, else the
/// signed-in user. Null when signed out.
@immutable
final class AccountHeader {
  const AccountHeader({
    required this.name,
    required this.email,
    required this.emailVerified,
    required this.twoFactorEnabled,
  });

  final String name;
  final String email;
  final bool emailVerified;
  final bool twoFactorEnabled;

  /// Up to two initials for the avatar ("Sara Khalil" -> "SK"). Arabic letters are kept apart
  /// with a space so they do not join into one word ("س خ").
  String get initials => initialsOf(name);
}

/// Initials of [name]: the first letter of the first and the last word, upper-cased.
String initialsOf(String name) {
  final words = name
      .trim()
      .split(RegExp(r'\s+'))
      .where((word) => word.isNotEmpty)
      .toList();
  if (words.isEmpty) {
    return '?';
  }
  String first(String word) => String.fromCharCode(word.runes.first);
  final letters = [first(words.first), if (words.length > 1) first(words.last)];
  final joiningScript = letters.any(
    (letter) => letter.runes.first >= 0x0600 && letter.runes.first <= 0x06FF,
  );
  return letters.map((l) => l.toUpperCase()).join(joiningScript ? ' ' : '');
}

@riverpod
AccountHeader? accountHeader(Ref ref) {
  if (ref.watch(sessionProvider) != SessionStatus.signedIn) {
    return null;
  }
  final profile = ref.watch(accountProfileControllerProvider)?.valueOrNull;
  if (profile != null) {
    return AccountHeader(
      name: profile.name,
      email: profile.email,
      emailVerified: profile.emailVerified,
      twoFactorEnabled: profile.twoFactorEnabled,
    );
  }
  final user = ref.watch(currentUserProvider);
  if (user == null) {
    return null;
  }
  return AccountHeader(
    name: user.name,
    email: user.email,
    emailVerified: user.emailVerified,
    twoFactorEnabled: user.twoFactorEnabled,
  );
}

/// The wallet balance on the hub's Wallet row (17.1), as the server's money text; null while
/// loading, on a failure, signed out or with `wallet_enabled` off (the row then shows a chevron).
@riverpod
Future<String?> accountWalletBalance(Ref ref) async {
  if (!ref.watch(walletEnabledProvider) ||
      ref.watch(sessionProvider) != SessionStatus.signedIn) {
    return null;
  }
  final result = await ref.watch(walletRepositoryProvider).wallet();
  return switch (result) {
    Ok(:final value) => moneyText(value.balance),
    Err() => null,
  };
}

/// The legal and help pages of `GET /app/config` `links` shown in Settings (17.3); a link the
/// server does not configure is not shown.
@immutable
final class AccountLinks {
  const AccountLinks({this.terms, this.privacy, this.refund, this.support});

  final Uri? terms;
  final Uri? privacy;
  final Uri? refund;
  final Uri? support;
}

@riverpod
AccountLinks accountLinks(Ref ref) {
  final links = ref.watch(latestAppConfigProvider)?.links;
  Uri? https(Uri? uri) => uri != null && uri.scheme == 'https' ? uri : null;
  return AccountLinks(
    terms: https(links?.terms),
    privacy: https(links?.privacy),
    refund: https(links?.refund),
    support: https(links?.support),
  );
}

/// Opens an account link in the system browser (https only). Returns false when nothing opened.
@riverpod
class AccountLinkOpener extends _$AccountLinkOpener {
  @override
  void build() {}

  Future<bool> open(Uri? url) async {
    if (url == null) {
      return false;
    }
    return ref.read(nativeBridgeProvider).openExternalUrl(url);
  }
}

@immutable
final class RegionalSettingsState {
  const RegionalSettingsState({this.applying = false, this.failure});

  final bool applying;
  final Failure? failure;
}

/// The language setting (17.2, 17.3; owner decision 1 keeps the display currency in the M-A11
/// currency sheet). Signed in, the choice is saved on the profile first (`PATCH /profile`
/// `locale`, through `UpdateRegionalSettings`) and applied only after the server accepted it;
/// signed out, it is saved on the device.
@riverpod
class RegionalSettingsController extends _$RegionalSettingsController {
  @override
  RegionalSettingsState build() => const RegionalSettingsState();

  /// Returns whether the language changed.
  Future<bool> selectLanguage(String languageCode) async {
    if (state.applying) {
      return false;
    }
    if (ref.read(appLocaleProvider).languageCode == languageCode) {
      return true;
    }
    if (ref.read(sessionProvider) != SessionStatus.signedIn) {
      await ref.read(appLocaleProvider.notifier).select(Locale(languageCode));
      return true;
    }
    state = const RegionalSettingsState(applying: true);
    final link = ref.keepAlive();
    try {
      final result = await ref
          .read(updateRegionalSettingsProvider)
          .call(locale: languageCode);
      if (result case Ok(:final value)) {
        ref.read(accountProfileControllerProvider.notifier).replace(value);
      }
      if (ref.mounted) {
        state = RegionalSettingsState(failure: result.failureOrNull);
      }
      return result.isOk;
    } finally {
      link.close();
    }
  }
}
