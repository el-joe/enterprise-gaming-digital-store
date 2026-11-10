import 'package:application/core/money/money.dart';
import 'package:application/core/money/money_json.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'order_models.freezed.dart';
part 'order_models.g.dart';

@freezed
abstract class OrderTotalsDto with _$OrderTotalsDto {
  const factory OrderTotalsDto({
    @MoneyConverter() required Money display,
    @NullableMoneyConverter() Money? payment,
  }) = _OrderTotalsDto;

  factory OrderTotalsDto.fromJson(Map<String, dynamic> json) =>
      _$OrderTotalsDtoFromJson(json);
}

@freezed
abstract class OrderSummaryDto with _$OrderSummaryDto {
  const factory OrderSummaryDto({
    required String publicId,
    required String number,
    required String status,
    required String paymentStatus,
    required bool isFinal,
    required OrderTotalsDto totals,
    required int itemCount,
    required DateTime createdAt,
    String? thumbnailUrl,
    DateTime? paidAt,
  }) = _OrderSummaryDto;

  const OrderSummaryDto._();

  factory OrderSummaryDto.fromJson(Map<String, dynamic> json) =>
      _$OrderSummaryDtoFromJson(json);

  OrderSummary toEntity() => OrderSummary(
    publicId: publicId,
    number: number,
    status: status,
    paymentStatus: paymentStatus,
    isFinal: isFinal,
    displayTotal: totals.display,
    paymentTotal: totals.payment,
    itemCount: itemCount,
    createdAt: createdAt,
    thumbnailUrl: thumbnailUrl == null ? null : Uri.parse(thumbnailUrl!),
    paidAt: paidAt,
  );
}

@freezed
abstract class OrderCurrencyDto with _$OrderCurrencyDto {
  const factory OrderCurrencyDto({required String display, String? payment}) =
      _OrderCurrencyDto;

  factory OrderCurrencyDto.fromJson(Map<String, dynamic> json) =>
      _$OrderCurrencyDtoFromJson(json);
}

@freezed
abstract class OrderProductRefDto with _$OrderProductRefDto {
  const factory OrderProductRefDto({String? slug}) = _OrderProductRefDto;

  factory OrderProductRefDto.fromJson(Map<String, dynamic> json) =>
      _$OrderProductRefDtoFromJson(json);
}

@freezed
abstract class OrderUnitDto with _$OrderUnitDto {
  const factory OrderUnitDto({
    required String publicId,
    required String status,
    required bool revealable,
    String? deliveryType,
    String? maskedCode,
    DateTime? deliveredAt,
    DateTime? firstRevealedAt,
    DateTime? refundedAt,
  }) = _OrderUnitDto;

  const OrderUnitDto._();

  factory OrderUnitDto.fromJson(Map<String, dynamic> json) =>
      _$OrderUnitDtoFromJson(json);

  OrderUnit toEntity() => OrderUnit(
    publicId: publicId,
    status: status,
    revealable: revealable,
    deliveryType: deliveryType,
    maskedCode: maskedCode,
    deliveredAt: deliveredAt,
    firstRevealedAt: firstRevealedAt,
    refundedAt: refundedAt,
  );
}

@freezed
abstract class OrderItemDto with _$OrderItemDto {
  const factory OrderItemDto({
    required int id,
    required String name,
    required int quantity,
    required List<OrderUnitDto> units,
    OrderProductRefDto? product,
    String? deliveryType,
    @NullableMoneyConverter() Money? unitPrice,
    @NullableMoneyConverter() Money? lineTotal,
  }) = _OrderItemDto;

  const OrderItemDto._();

  factory OrderItemDto.fromJson(Map<String, dynamic> json) =>
      _$OrderItemDtoFromJson(json);

  OrderLine toEntity() => OrderLine(
    id: id,
    name: name,
    quantity: quantity,
    units: [for (final unit in units) unit.toEntity()],
    productSlug: product?.slug,
    deliveryType: deliveryType,
    unitPrice: unitPrice,
    lineTotal: lineTotal,
  );
}

@freezed
abstract class OrderPaymentDto with _$OrderPaymentDto {
  const factory OrderPaymentDto({
    required String publicId,
    required int attempt,
    required String status,
    @MoneyConverter() required Money amount,
    required DateTime createdAt,
    String? provider,
    String? method,
    String? failureCode,
  }) = _OrderPaymentDto;

  const OrderPaymentDto._();

  factory OrderPaymentDto.fromJson(Map<String, dynamic> json) =>
      _$OrderPaymentDtoFromJson(json);

  OrderPayment toEntity() => OrderPayment(
    publicId: publicId,
    attempt: attempt,
    status: status,
    amount: amount,
    createdAt: createdAt,
    provider: provider,
    method: method,
    failureCode: failureCode,
  );
}

@freezed
abstract class OrderRefundDto with _$OrderRefundDto {
  const factory OrderRefundDto({
    required String publicId,
    required String status,
    required String destination,
    @MoneyConverter() required Money amount,
    DateTime? createdAt,
    DateTime? completedAt,
  }) = _OrderRefundDto;

  const OrderRefundDto._();

  factory OrderRefundDto.fromJson(Map<String, dynamic> json) =>
      _$OrderRefundDtoFromJson(json);

  OrderRefund toEntity() => OrderRefund(
    publicId: publicId,
    status: status,
    destination: destination,
    amount: amount,
    createdAt: createdAt,
    completedAt: completedAt,
  );
}

@freezed
abstract class OrderTimelineDto with _$OrderTimelineDto {
  const factory OrderTimelineDto({required String status, DateTime? at}) =
      _OrderTimelineDto;

  factory OrderTimelineDto.fromJson(Map<String, dynamic> json) =>
      _$OrderTimelineDtoFromJson(json);
}

@freezed
abstract class OrderDetailDto with _$OrderDetailDto {
  const factory OrderDetailDto({
    required String publicId,
    required String number,
    required String status,
    required String paymentStatus,
    required bool isFinal,
    required OrderTotalsDto totals,
    required int itemCount,
    required DateTime createdAt,
    required OrderCurrencyDto currency,
    required List<OrderItemDto> items,
    required List<OrderPaymentDto> payments,
    required List<OrderRefundDto> refunds,
    required String refundState,
    required List<OrderTimelineDto> timeline,
    String? thumbnailUrl,
    DateTime? paidAt,
    DateTime? completedAt,
  }) = _OrderDetailDto;

  const OrderDetailDto._();

  factory OrderDetailDto.fromJson(Map<String, dynamic> json) =>
      _$OrderDetailDtoFromJson(json);

  OrderDetail toEntity() => OrderDetail(
    summary: OrderSummaryDto(
      publicId: publicId,
      number: number,
      status: status,
      paymentStatus: paymentStatus,
      isFinal: isFinal,
      totals: totals,
      itemCount: itemCount,
      createdAt: createdAt,
      thumbnailUrl: thumbnailUrl,
      paidAt: paidAt,
    ).toEntity(),
    displayCurrency: currency.display,
    paymentCurrency: currency.payment,
    items: [for (final item in items) item.toEntity()],
    payments: [for (final payment in payments) payment.toEntity()],
    refunds: [for (final refund in refunds) refund.toEntity()],
    refundState: refundState,
    timeline: [
      for (final entry in timeline)
        OrderTimelineEntry(status: entry.status, at: entry.at),
    ],
    completedAt: completedAt,
  );
}
