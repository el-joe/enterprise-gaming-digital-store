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

    /// `credit_refund`, `credit_admin`, `debit_purchase`, `debit_admin` or `reversal`.
    required String type,
    required String amount,
    required String balanceAfter,
    required DateTime createdAt,

    /// The server's note (English only): the staff reason of an adjustment, or a system note.
    String? note,

    /// The customer's own order behind a purchase or a refund (M-A15); null otherwise.
    WalletOrderRef? order,
  }) = _WalletTransaction;

  const WalletTransaction._();

  /// A credit (money in): the amount is not negative.
  bool get isCredit => !amount.trimLeft().startsWith('-');
}

/// The order a statement line belongs to: its public id (opens the order detail) and number.
@freezed
abstract class WalletOrderRef with _$WalletOrderRef {
  const factory WalletOrderRef({
    required String publicId,
    required String orderNumber,
  }) = _WalletOrderRef;
}
