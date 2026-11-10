import 'package:application/core/money/money.dart';
import 'package:application/core/money/money_json.dart';
import 'package:application/features/wallet/domain/entities/wallet.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'wallet_models.freezed.dart';
part 'wallet_models.g.dart';

@freezed
abstract class WalletDto with _$WalletDto {
  const factory WalletDto({
    required String currency,
    @MoneyConverter() required Money balance,
    required String status,
    required bool topUpAvailable,
  }) = _WalletDto;

  const WalletDto._();

  factory WalletDto.fromJson(Map<String, dynamic> json) =>
      _$WalletDtoFromJson(json);

  Wallet toEntity() => Wallet(
    currency: currency,
    balance: balance,
    status: status,
    topUpAvailable: topUpAvailable,
  );
}

@freezed
abstract class WalletTransactionDto with _$WalletTransactionDto {
  const factory WalletTransactionDto({
    required int id,
    required String type,
    required String amount,
    required String balanceAfter,
    required DateTime createdAt,
    String? note,
  }) = _WalletTransactionDto;

  const WalletTransactionDto._();

  factory WalletTransactionDto.fromJson(Map<String, dynamic> json) =>
      _$WalletTransactionDtoFromJson(json);

  WalletTransaction toEntity() => WalletTransaction(
    id: id,
    type: type,
    amount: amount,
    balanceAfter: balanceAfter,
    createdAt: createdAt,
    note: note,
  );
}
