import 'package:application/core/money/money.dart';
import 'package:freezed_annotation/freezed_annotation.dart';

part 'orders.freezed.dart';

/// An order of `GET /account/orders` (API_DOCUMENTATION.md 4c).
@freezed
abstract class OrderSummary with _$OrderSummary {
  const factory OrderSummary({
    required String publicId,
    required String number,
    required String status,

    /// `unpaid`, `processing`, `paid`, `partially_refunded`, `refunded`.
    required String paymentStatus,
    required bool isFinal,
    required Money displayTotal,
    required int itemCount,
    required DateTime createdAt,
    Money? paymentTotal,
    Uri? thumbnailUrl,
    DateTime? paidAt,
  }) = _OrderSummary;
}

/// `GET /account/orders/{id}` and the guest detail. Never a code: only [OrderUnit.maskedCode].
@freezed
abstract class OrderDetail with _$OrderDetail {
  const factory OrderDetail({
    required OrderSummary summary,
    required String displayCurrency,
    required List<OrderLine> items,
    required List<OrderPayment> payments,
    required List<OrderRefund> refunds,

    /// `none`, `pending`, `partial`, `full`.
    required String refundState,
    required List<OrderTimelineEntry> timeline,
    String? paymentCurrency,
    DateTime? completedAt,
  }) = _OrderDetail;
}

@freezed
abstract class OrderLine with _$OrderLine {
  const factory OrderLine({
    required int id,
    required String name,
    required int quantity,
    required List<OrderUnit> units,
    String? productSlug,
    String? deliveryType,
    Money? unitPrice,
    Money? lineTotal,
  }) = _OrderLine;
}

/// A delivered or pending unit. [publicId] is what reveal takes; [revealable] is a hint only.
@freezed
abstract class OrderUnit with _$OrderUnit {
  const factory OrderUnit({
    required String publicId,
    required String status,
    required bool revealable,
    String? deliveryType,
    String? maskedCode,
    DateTime? deliveredAt,
    DateTime? firstRevealedAt,
    DateTime? refundedAt,
  }) = _OrderUnit;
}

@freezed
abstract class OrderPayment with _$OrderPayment {
  const factory OrderPayment({
    required String publicId,
    required int attempt,
    required String status,
    required Money amount,
    required DateTime createdAt,
    String? provider,
    String? method,
    String? failureCode,
  }) = _OrderPayment;
}

@freezed
abstract class OrderRefund with _$OrderRefund {
  const factory OrderRefund({
    required String publicId,
    required String status,
    required String destination,
    required Money amount,
    DateTime? createdAt,
    DateTime? completedAt,
  }) = _OrderRefund;
}

@freezed
abstract class OrderTimelineEntry with _$OrderTimelineEntry {
  const factory OrderTimelineEntry({required String status, DateTime? at}) =
      _OrderTimelineEntry;
}

/// What a guest still has to prove before a reveal (`meta.guest_access`).
@freezed
abstract class GuestOrderDetail with _$GuestOrderDetail {
  const factory GuestOrderDetail({
    required OrderDetail order,
    required bool linkVerified,
    required bool otpVerified,
  }) = _GuestOrderDetail;

  const GuestOrderDetail._();

  bool get canReveal => linkVerified && otpVerified;
}

/// Filters of the order list; dates are `Y-m-d`, inclusive, by placement date.
@freezed
abstract class OrdersQuery with _$OrdersQuery {
  const factory OrdersQuery({
    String? status,
    String? from,
    String? to,
    @Default(1) int page,
    @Default(20) int perPage,
  }) = _OrdersQuery;
}
