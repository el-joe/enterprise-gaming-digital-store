import 'dart:convert';

import 'package:application/core/network/api_decoding.dart';
import 'package:application/core/network/api_error.dart';
import 'package:application/core/network/error_mapper.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/features/account/data/models/account_models.dart';
import 'package:application/features/app_config/data/models/app_config_models.dart';
import 'package:application/features/auth/data/models/auth_models.dart';
import 'package:application/features/catalog/data/models/catalog_models.dart';
import 'package:application/features/checkout/data/models/checkout_models.dart';
import 'package:application/features/code_reveal/data/models/reveal_models.dart';
import 'package:application/features/home/data/models/home_models.dart';
import 'package:application/features/notifications/data/models/notification_models.dart';
import 'package:application/features/orders/data/models/order_models.dart';
import 'package:application/features/product/data/models/product_models.dart';
import 'package:application/features/reviews/data/models/review_models.dart';
import 'package:application/features/search/data/models/search_models.dart';
import 'package:application/features/security/data/models/two_factor_models.dart';
import 'package:application/features/support/data/models/support_models.dart';
import 'package:application/features/wallet/data/models/wallet_models.dart';
import 'package:application/shared/data/models/cart_models.dart';
import 'package:application/shared/data/models/catalog_ref_models.dart';
import 'package:application/shared/data/models/payment_models.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';

import '../helpers/fixtures.dart';

/// A round-trip check of one decoded DTO.
typedef Check = void Function();

/// Decodes the `data` of a success fixture into its DTOs; returns one round-trip check per DTO.
typedef Decoder = List<Check> Function(Object? data);

typedef FromJson<T> = T Function(Map<String, dynamic> json);

/// Decodes [json] with [from] and checks that what the DTO writes reads back to an equal DTO.
List<Check> _rt<T>(
  Map<String, dynamic> json,
  FromJson<T> from,
  Object? Function(T) to,
) {
  final dto = from(json);
  return [
    () {
      final written = jsonDecode(jsonEncode(to(dto))) as Map<String, dynamic>;
      expect(from(written), dto);
    },
  ];
}

List<Check> _rtList<T>(
  Object? list,
  FromJson<T> from,
  Object? Function(T) to,
) => [for (final item in listOf(list, (json) => json)) ..._rt(item, from, to)];

List<Check> _empty(Object? data) {
  expect(data, isA<Map<String, dynamic>>());
  return const [];
}

List<Check> _tokens(Object? d) =>
    _rt(asJsonObject(d), AuthSessionDto.fromJson, (v) => v.toJson());
List<Check> _login(Object? d) =>
    _rt(asJsonObject(d), LoginResponseDto.fromJson, (v) => v.toJson());
List<Check> _user(Object? d) =>
    _rt(objectAt(d, 'user'), UserDto.fromJson, (v) => v.toJson());
List<Check> _config(Object? d) =>
    _rt(objectAt(d, 'config'), AppConfigDto.fromJson, (v) => v.toJson());
List<Check> _home(Object? d) =>
    _rt(asJsonObject(d), HomeDto.fromJson, (v) => v.toJson());
List<Check> _cards(Object? d) =>
    _rtList(d, ProductCardDto.fromJson, (v) => v.toJson());
List<Check> _platforms(Object? d) => _rtList(
  asJsonObject(d)['platforms'],
  PlatformDto.fromJson,
  (v) => v.toJson(),
);
List<Check> _categories(Object? d) => _rtList(
  asJsonObject(d)['categories'],
  CategoryDto.fromJson,
  (v) => v.toJson(),
);
List<Check> _countries(Object? d) => _rtList(
  asJsonObject(d)['countries'],
  CountryDto.fromJson,
  (v) => v.toJson(),
);
List<Check> _currencies(Object? d) => _rtList(
  asJsonObject(d)['currencies'],
  DisplayCurrencyDto.fromJson,
  (v) => v.toJson(),
);
List<Check> _detail(Object? d) =>
    _rt(objectAt(d, 'product'), ProductDetailDto.fromJson, (v) => v.toJson());
List<Check> _variants(Object? d) => _rtList(
  asJsonObject(d)['variants'],
  VariantOfferDto.fromJson,
  (v) => v.toJson(),
);
List<Check> _productReviews(Object? d) =>
    _rtList(d, ProductReviewDto.fromJson, (v) => v.toJson());
List<Check> _suggest(Object? d) => _rtList(
  asJsonObject(d)['suggestions'],
  SearchSuggestionDto.fromJson,
  (v) => v.toJson(),
);
List<Check> _popular(Object? d) {
  expect(asJsonObject(d)['terms'], everyElement(isA<String>()));
  return const <Check>[];
}

List<Check> _cart(Object? d) =>
    _rt(objectAt(d, 'cart'), CartDto.fromJson, (v) => v.toJson());
List<Check> _cartOrNull(Object? d) {
  final cart = asJsonObject(d)['cart'];
  return cart == null ? const <Check>[] : _cart(d);
}

List<Check> _options(Object? d) => _rtList(
  asJsonObject(d)['options'],
  PaymentOptionDto.fromJson,
  (v) => v.toJson(),
);
List<Check> _placed(Object? d) =>
    _rt(asJsonObject(d), PlaceOrderDataDto.fromJson, (v) => v.toJson());
List<Check> _status(Object? d) =>
    _rt(asJsonObject(d), OrderPaymentStatusDto.fromJson, (v) => v.toJson());
List<Check> _next(Object? d) =>
    _rt(objectAt(d, 'next'), PaymentNextDto.fromJson, (v) => v.toJson());
List<Check> _access(Object? d) =>
    _rt(asJsonObject(d), GuestAccessDto.fromJson, (v) => v.toJson());
List<Check> _otp(Object? d) =>
    _rt(asJsonObject(d), OtpVerifyDto.fromJson, (v) => v.toJson());
List<Check> _orderDetail(Object? d) =>
    _rt(objectAt(d, 'order'), OrderDetailDto.fromJson, (v) => v.toJson());
List<Check> _orders(Object? d) =>
    _rtList(d, OrderSummaryDto.fromJson, (v) => v.toJson());
List<Check> _reveal(Object? d) {
  RevealedCodeDto.fromJson(asJsonObject(d));
  return const <Check>[];
}

List<Check> _confirm(Object? d) {
  expect(asJsonObject(d)['confirmed_for_minutes'], isA<int>());
  return const <Check>[];
}

List<Check> _wallet(Object? d) =>
    _rt(objectAt(d, 'wallet'), WalletDto.fromJson, (v) => v.toJson());
List<Check> _walletTx(Object? d) =>
    _rtList(d, WalletTransactionDto.fromJson, (v) => v.toJson());
List<Check> _eligible(Object? d) => _rtList(
  asJsonObject(d)['items'],
  ReviewableItemDto.fromJson,
  (v) => v.toJson(),
);
List<Check> _review(Object? d) =>
    _rt(objectAt(d, 'review'), MyReviewDto.fromJson, (v) => v.toJson());
List<Check> _reviews(Object? d) =>
    _rtList(d, MyReviewDto.fromJson, (v) => v.toJson());
List<Check> _ticket(Object? d) =>
    _rt(objectAt(d, 'ticket'), SupportTicketDto.fromJson, (v) => v.toJson());
List<Check> _tickets(Object? d) =>
    _rtList(d, SupportTicketDto.fromJson, (v) => v.toJson());
List<Check> _refund(Object? d) => _rt(
  objectAt(d, 'refund_request'),
  RefundRequestDto.fromJson,
  (v) => v.toJson(),
);
List<Check> _refunds(Object? d) =>
    _rtList(d, RefundRequestDto.fromJson, (v) => v.toJson());
List<Check> _inbox(Object? d) =>
    _rtList(d, InboxNotificationDto.fromJson, (v) => v.toJson());
List<Check> _notification(Object? d) => _rt(
  objectAt(d, 'notification'),
  InboxNotificationDto.fromJson,
  (v) => v.toJson(),
);
List<Check> _count(Object? d) {
  expect(asJsonObject(d)['unread_count'], isA<int>());
  return const <Check>[];
}

List<Check> _prefs(Object? d) => _rtList(
  asJsonObject(d)['preferences'],
  NotificationPreferenceDto.fromJson,
  (v) => v.toJson(),
);
List<Check> _profile(Object? d) =>
    _rt(objectAt(d, 'profile'), ProfileDto.fromJson, (v) => v.toJson());
List<Check> _signedOut(Object? d) =>
    _rt(objectAt(d, 'signed_out'), SignedOutDto.fromJson, (v) => v.toJson());
List<Check> _devices(Object? d) =>
    _rtList(asJsonObject(d)['devices'], DeviceDto.fromJson, (v) => v.toJson());
List<Check> _enroll(Object? d) {
  TwoFactorModels.enrollment(asJsonObject(d));
  return const <Check>[];
}

List<Check> _recovery(Object? d) {
  TwoFactorModels.recoveryCodes(asJsonObject(d));
  return const <Check>[];
}

/// Every success fixture and the DTOs it decodes into.
final Map<String, Decoder> decoders = {
  'account/device_revoked': _empty,
  'account/devices_ok': _devices,
  'account/password_updated': _signedOut,
  'account/profile_ok': _profile,
  'account/profile_updated': _profile,
  'account/verification_notification_ok': _empty,
  'app_config/config_ar': _config,
  'app_config/config_ok': _config,
  'auth/login_ok': _login,
  'auth/login_two_factor_required': _login,
  'auth/logout_all_ok': _empty,
  'auth/logout_ok': _empty,
  'auth/me_ok': _user,
  'auth/refresh_ok': (d) =>
      _rt(objectAt(d, 'tokens'), TokensDto.fromJson, (v) => v.toJson()),
  'auth/register_created': _tokens,
  'auth/two_factor_challenge_ok': _tokens,
  'cart/add_item_created': _cart,
  'cart/add_item_customer_created': _cart,
  'cart/cart_empty': _cartOrNull,
  'cart/cart_ok': _cart,
  'cart/coupon_applied': _cart,
  'cart/coupon_removed': _cart,
  'cart/remove_item_ok': _cart,
  'cart/update_item_ok': _cart,
  'catalog/categories_ar': _categories,
  'catalog/categories_en': _categories,
  'catalog/countries_ar': _countries,
  'catalog/countries_en': _countries,
  'catalog/currencies': _currencies,
  'catalog/platforms_ar': _platforms,
  'catalog/platforms_en': _platforms,
  'catalog/products_ar': _cards,
  'catalog/products_en': _cards,
  'catalog/products_filtered_egp': _cards,
  'checkout/payment_options_ok': _options,
  'checkout/place_order_created': _placed,
  'checkout/place_order_customer_created': _placed,
  'checkout/place_order_duplicate': _placed,
  'checkout/place_order_next_null': _placed,
  'checkout/place_order_replayed': _placed,
  'checkout/status_ok': _status,
  'checkout/validate_ok': _cart,
  'code_reveal/confirm_password_ok': _confirm,
  'code_reveal/guest_reveal_ok': _reveal,
  'code_reveal/reveal_ok': _reveal,
  'guest_orders/access_link_ok': _access,
  'guest_orders/order_detail': _orderDetail,
  'guest_orders/otp_send_ok': _empty,
  'guest_orders/otp_verify_ok': _otp,
  'home/home_ar': _home,
  'home/home_en': _home,
  'notifications/list_ok': _inbox,
  'notifications/mark_read_ok': _notification,
  'notifications/preferences_ok': _prefs,
  'notifications/preferences_updated': _prefs,
  'notifications/read_all_ok': _count,
  'notifications/unread_count_ok': _count,
  'orders/detail_ok': _orderDetail,
  'orders/list_filtered': _orders,
  'orders/list_ok': _orders,
  'payments/paypal_capture_ok': _status,
  'payments/place_order_wallet': _placed,
  'payments/retry_payment_paypal': _next,
  'payments/retry_payment_redirect': _next,
  'payments/retry_payment_stripe': _next,
  'payments/status_paid': _status,
  'payments/verify_payment_paid': _status,
  'payments/verify_payment_processing': _status,
  'product/detail_ar': _detail,
  'product/detail_en': _detail,
  'product/reviews_ar': _productReviews,
  'product/reviews_en': _productReviews,
  'product/variants_ar': _variants,
  'product/variants_en': _variants,
  'reviews/create_created': _review,
  'reviews/eligible_ok': _eligible,
  'reviews/list_ok': _reviews,
  'search/popular': _popular,
  'search/suggest': _suggest,
  'security/two_factor_confirm_ok': _recovery,
  'security/two_factor_disable_ok': _empty,
  'security/two_factor_enable_ok': _enroll,
  'support/message_created': _ticket,
  'support/refund_request_created': _refund,
  'support/refund_requests_list': _refunds,
  'support/ticket_created': _ticket,
  'support/ticket_detail': _ticket,
  'support/tickets_list': _tickets,
  'wallet/transactions_ok': _walletTx,
  'wallet/wallet_empty': _wallet,
  'wallet/wallet_ok': _wallet,
};

void main() {
  final names = ApiFixture.all();

  test('the app copy holds every recorded fixture', () {
    expect(names.length, greaterThan(100));
  });

  test('every success fixture has a decoder and every decoder a fixture', () {
    final success = {
      for (final name in names)
        if (ApiFixture.load(name).status < 300) name,
    };
    expect(decoders.keys.toSet(), success);
  });

  group('success fixtures decode into their DTOs', () {
    for (final entry in decoders.entries) {
      test(entry.key, () {
        final fixture = ApiFixture.load(entry.key);
        final checks = decodeEnvelope(fixture.body, entry.value).data;
        for (final check in checks) {
          check();
        }
      });
    }
  });

  group('error fixtures are error envelopes that map to a typed failure', () {
    for (final name in names) {
      final fixture = ApiFixture.load(name);
      if (fixture.status < 400) {
        continue;
      }
      test(name, () {
        final error = ApiError.tryParse(fixture.body);
        expect(error, isNotNull);
        expect(error!.requestId, isNotNull);
        final failure = mapErrorResponse(
          statusCode: fixture.status,
          body: fixture.body,
          headers: Headers.fromMap({
            for (final entry in fixture.headers.entries)
              entry.key: [entry.value],
          }),
        );
        final expected = switch (fixture.status) {
          401 => isA<UnauthenticatedFailure>(),
          403 when error.code == 'email_unverified' =>
            isA<EmailUnverifiedFailure>(),
          403 => isA<ForbiddenFailure>(),
          404 => isA<NotFoundFailure>(),
          409 => isA<ConflictFailure>(),
          422 when error.code == 'validation_failed' =>
            isA<ValidationFailure>(),
          422 => isA<DomainFailure>(),
          423 => isA<RecentAuthRequiredFailure>(),
          429 => isA<RateLimitedFailure>(),
          _ => isA<Failure>(),
        };
        expect(failure, expected);
      });
    }
  });
}
