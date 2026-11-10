import 'package:application/core/network/result.dart';
import 'package:application/features/account/domain/entities/account.dart';
import 'package:application/features/account/domain/repositories/account_repository.dart';

/// Saves the language and display currency on the profile, then applies them to the app (the
/// `Accept-Language` and `X-Currency` the client sends). Local preferences change only after the
/// server accepted the values.
class UpdateRegionalSettings {
  UpdateRegionalSettings(
    this._account, {
    required this._applyLocale,
    required this._applyCurrency,
  });

  final AccountRepository _account;
  final Future<void> Function(String languageCode) _applyLocale;
  final Future<void> Function(String currency) _applyCurrency;

  Future<Result<Profile>> call({
    String? locale,
    String? displayCurrency,
  }) async {
    final result = await _account.updateProfile(
      ProfileUpdate(
        locale: locale == null ? null : FieldUpdate(locale),
        displayCurrency: displayCurrency == null
            ? null
            : FieldUpdate(displayCurrency),
      ),
    );
    if (result case Ok(:final value)) {
      if (value.locale != null) {
        await _applyLocale(value.locale!);
      }
      if (value.displayCurrency != null) {
        await _applyCurrency(value.displayCurrency!);
      }
    }
    return result;
  }
}
