// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'refund_status.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The support feature's public entry for the order detail: the customer's latest refund
/// request for [orderPublicId], read from `GET /account/refund-requests` (newest first, the
/// first 50; the order detail itself carries no request). Null when there is none, signed out,
/// or when the list could not be read (the order detail then shows no request).

@ProviderFor(orderRefundRequest)
final orderRefundRequestProvider = OrderRefundRequestFamily._();

/// The support feature's public entry for the order detail: the customer's latest refund
/// request for [orderPublicId], read from `GET /account/refund-requests` (newest first, the
/// first 50; the order detail itself carries no request). Null when there is none, signed out,
/// or when the list could not be read (the order detail then shows no request).

final class OrderRefundRequestProvider
    extends
        $FunctionalProvider<
          AsyncValue<RefundRequest?>,
          RefundRequest?,
          FutureOr<RefundRequest?>
        >
    with $FutureModifier<RefundRequest?>, $FutureProvider<RefundRequest?> {
  /// The support feature's public entry for the order detail: the customer's latest refund
  /// request for [orderPublicId], read from `GET /account/refund-requests` (newest first, the
  /// first 50; the order detail itself carries no request). Null when there is none, signed out,
  /// or when the list could not be read (the order detail then shows no request).
  OrderRefundRequestProvider._({
    required OrderRefundRequestFamily super.from,
    required String super.argument,
  }) : super(
         retry: null,
         name: r'orderRefundRequestProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$orderRefundRequestHash();

  @override
  String toString() {
    return r'orderRefundRequestProvider'
        ''
        '($argument)';
  }

  @$internal
  @override
  $FutureProviderElement<RefundRequest?> $createElement(
    $ProviderPointer pointer,
  ) => $FutureProviderElement(pointer);

  @override
  FutureOr<RefundRequest?> create(Ref ref) {
    final argument = this.argument as String;
    return orderRefundRequest(ref, argument);
  }

  @override
  bool operator ==(Object other) {
    return other is OrderRefundRequestProvider && other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$orderRefundRequestHash() =>
    r'df7b57731d8bba9ed98a0e4668086770a4604dfa';

/// The support feature's public entry for the order detail: the customer's latest refund
/// request for [orderPublicId], read from `GET /account/refund-requests` (newest first, the
/// first 50; the order detail itself carries no request). Null when there is none, signed out,
/// or when the list could not be read (the order detail then shows no request).

final class OrderRefundRequestFamily extends $Family
    with $FunctionalFamilyOverride<FutureOr<RefundRequest?>, String> {
  OrderRefundRequestFamily._()
    : super(
        retry: null,
        name: r'orderRefundRequestProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// The support feature's public entry for the order detail: the customer's latest refund
  /// request for [orderPublicId], read from `GET /account/refund-requests` (newest first, the
  /// first 50; the order detail itself carries no request). Null when there is none, signed out,
  /// or when the list could not be read (the order detail then shows no request).

  OrderRefundRequestProvider call(String orderPublicId) =>
      OrderRefundRequestProvider._(argument: orderPublicId, from: this);

  @override
  String toString() => r'orderRefundRequestProvider';
}
