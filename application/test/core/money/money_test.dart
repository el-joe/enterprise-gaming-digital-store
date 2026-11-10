import 'package:application/core/money/currency_info.dart';
import 'package:application/core/money/money.dart';
import 'package:application/core/money/money_formatter.dart';
import 'package:decimal/decimal.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  const usd = CurrencyInfo(code: 'USD', symbol: r'$', exponent: 2);
  const jpy = CurrencyInfo(code: 'JPY', symbol: '¥', exponent: 0);
  const kwd = CurrencyInfo(code: 'KWD', symbol: 'KD', exponent: 3);
  const egp = CurrencyInfo(code: 'EGP', symbol: 'E£', exponent: 2);

  group('Money.fromJson', () {
    test('accepts a decimal string amount', () {
      final money = Money.fromJson({
        'amount': '12.50',
        'currency': 'USD',
        'formatted': r'$12.50',
      });
      expect(money.amount, Decimal.parse('12.5'));
      expect(money.currency, 'USD');
      expect(money.formatted, r'$12.50');
    });

    test('rejects a JSON integer amount', () {
      expect(
        () => Money.fromJson({'amount': 12, 'currency': 'USD'}),
        throwsFormatException,
      );
    });

    test('rejects a JSON double amount', () {
      expect(
        () => Money.fromJson({'amount': 12.5, 'currency': 'USD'}),
        throwsFormatException,
      );
    });

    test('rejects malformed strings, currencies and shapes', () {
      for (final json in <Object?>[
        {'amount': '12,50', 'currency': 'USD'},
        {'amount': '1e3', 'currency': 'USD'},
        {'amount': ' 12.50', 'currency': 'USD'},
        {'amount': '', 'currency': 'USD'},
        {'amount': '12.50', 'currency': 'usd'},
        {'amount': '12.50'},
        {'amount': '12.50', 'currency': 'USD', 'formatted': 12.5},
        '12.50',
        null,
      ]) {
        expect(
          () => Money.fromJson(json),
          throwsFormatException,
          reason: '$json',
        );
      }
    });

    test('preserves 6 decimal places exactly', () {
      final money = Money.fromJson({
        'amount': '1234.567891',
        'currency': 'USD',
      });
      expect(money.amount, Decimal.parse('1234.567891'));
      expect(money.amount.toString(), '1234.567891');
      expect(money.scale, 6);
      expect(money.toJson()['amount'], '1234.567891');
    });

    test(
      'keeps the server string for round trips (trailing zeros included)',
      () {
        final money = Money.fromJson({
          'amount': '10.500000',
          'currency': 'USD',
        });
        expect(money.amountText, '10.500000');
        expect(money.toJson(), {'amount': '10.500000', 'currency': 'USD'});
        expect(money, Money.parse('10.5', 'USD'));
      },
    );

    test('sums of 6 dp values are exact (no binary floating point)', () {
      final a = Money.parse('0.100000', 'USD').amount;
      final b = Money.parse('0.200000', 'USD').amount;
      expect(a + b, Decimal.parse('0.3'));
    });
  });

  group('MoneyFormatter', () {
    test('formats by currency exponent like CurrencyConverter::present', () {
      expect(MoneyFormatter.format(Money.parse('12.5', 'USD'), usd), r'$12.50');
      expect(MoneyFormatter.format(Money.parse('1500', 'JPY'), jpy), '¥1500');
      expect(MoneyFormatter.format(Money.parse('1.5', 'KWD'), kwd), 'KD1.500');
    });

    test('cuts extra digits toward zero, as bcadd does', () {
      expect(
        MoneyFormatter.format(Money.parse('12.349999', 'USD'), usd),
        r'$12.34',
      );
      expect(
        MoneyFormatter.format(Money.parse('-12.349999', 'USD'), usd),
        r'-$12.34',
      );
    });

    test('handles values beyond double precision', () {
      final money = Money.parse('123456789012345678.987654', 'USD');
      expect(MoneyFormatter.format(money, usd), r'$123456789012345678.98');
    });

    test('groups thousands when asked', () {
      expect(
        MoneyFormatter.format(
          Money.parse('1234567.891', 'USD'),
          usd,
          grouping: true,
        ),
        r'$1,234,567.89',
      );
      expect(
        MoneyFormatter.formatAmount(
          Decimal.parse('999'),
          exponent: 0,
          grouping: true,
        ),
        '999',
      );
    });

    test('Arabic-Indic digits option', () {
      expect(
        MoneyFormatter.format(
          Money.parse('1234.5', 'EGP'),
          egp,
          digits: MoneyDigits.arabicIndic,
          grouping: true,
        ),
        'E£١٬٢٣٤٫٥٠',
      );
      expect(
        MoneyFormatter.formatAmount(
          Decimal.parse('9876543210'),
          exponent: 0,
          digits: MoneyDigits.arabicIndic,
        ),
        '٩٨٧٦٥٤٣٢١٠',
      );
    });

    test('negative zero after truncation prints without a sign', () {
      expect(
        MoneyFormatter.formatAmount(Decimal.parse('-0.001'), exponent: 2),
        '0.00',
      );
    });

    test('refuses a currency that does not match', () {
      expect(
        () => MoneyFormatter.format(Money.parse('1', 'USD'), egp),
        throwsArgumentError,
      );
    });
  });

  group('CurrencyInfo.fromJson', () {
    test('parses the storefront currency shape', () {
      expect(
        CurrencyInfo.fromJson({
          'code': 'SAR',
          'symbol': 'SAR ',
          'exponent': 2,
          'name': 'Saudi Riyal',
        }),
        const CurrencyInfo(code: 'SAR', symbol: 'SAR ', exponent: 2),
      );
    });

    test('rejects an invalid exponent', () {
      expect(
        () => CurrencyInfo.fromJson({
          'code': 'USD',
          'symbol': r'$',
          'exponent': '2',
        }),
        throwsFormatException,
      );
    });
  });
}
