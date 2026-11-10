import 'package:application/core/router/deep_links.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  DeepLink? parse(String link) =>
      parseDeepLink(Uri.parse(link), appLinkHost: 'boxcards.shop');

  test('payment return ignores provider query parameters', () {
    expect(
      parse(
        'https://boxcards.shop/app/payments/return/ord_01JABC?success=true&id=9',
      ),
      const PaymentReturnLink('ord_01JABC'),
    );
  });

  test('signed guest access keeps the full signed URL', () {
    const link =
        'https://boxcards.shop/api/v1/checkout/guest/orders/ord_1/access?expires=1700000000&signature=abc';
    final parsed = parse(link);
    expect(parsed, isA<GuestOrderAccessLink>());
    final access = parsed! as GuestOrderAccessLink;
    expect(access.orderPublicId, 'ord_1');
    expect(access.signedUri.toString(), link);
    expect(
      parse('https://boxcards.shop/api/v1/checkout/guest/orders/ord_1/access'),
      isNull,
    );
  });

  test('email verification needs the signature', () {
    expect(
      parse(
        'https://boxcards.shop/email/verify/42/abcdef0123?expires=1&signature=s',
      ),
      isA<VerifyEmailLink>()
          .having((l) => l.userId, 'id', '42')
          .having((l) => l.hash, 'hash', 'abcdef0123'),
    );
    expect(parse('https://boxcards.shop/email/verify/42/abcdef0123'), isNull);
  });

  test('password reset with optional email', () {
    expect(
      parse('https://boxcards.shop/reset-password/tok123?email=a%40b.test'),
      const ResetPasswordLink('tok123', 'a@b.test'),
    );
    expect(
      parse('https://boxcards.shop/reset-password/tok123'),
      const ResetPasswordLink('tok123', null),
    );
  });

  test('rejects other hosts, schemes, ports and paths', () {
    for (final link in [
      'http://boxcards.shop/app/payments/return/ord_1',
      'https://evil.test/app/payments/return/ord_1',
      'https://boxcards.shop.evil.test/app/payments/return/ord_1',
      'https://boxcards.shop:8443/app/payments/return/ord_1',
      'boxcards://app/payments/return/ord_1',
      'https://boxcards.shop/app/payments/return/ord_1/extra',
      'https://boxcards.shop/app/payments/return/..%2F..',
      'https://boxcards.shop/products/fifa',
      'https://boxcards.shop/',
    ]) {
      expect(parse(link), isNull, reason: link);
    }
  });

  test('host match is case-insensitive', () {
    expect(parse('https://BoxCards.Shop/app/payments/return/ord_1'), isNotNull);
  });
}
