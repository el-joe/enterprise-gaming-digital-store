import 'package:application/core/money/money.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'wallet.freezed.dart';

/// Store credit (API_DOCUMENTATION.md 4b). Read only: there is no top-up (BD3).
@freezed
abstract class Wallet with _$Wallet {
  const factory Wallet({
    required String currency,
    required Money balance,

    /// `active` or `frozen`.
    required String status,
    required bool topUpAvailable,
  }) = _Wallet;
}

/// A statement line. [amount] (signed) and [balanceAfter] are decimal strings in the wallet
/// currency, exactly as the ledger stores them (6 dp); format them with the wallet's currency.
@freezed
abstract class WalletTransaction with _$WalletTransaction {
  const factory WalletTransaction({
    required int id,
    required String type,
    required String amount,
    required String balanceAfter,
    required DateTime createdAt,
    String? note,
  }) = _WalletTransaction;
}
