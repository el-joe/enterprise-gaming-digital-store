import 'package:application/core/utils/safe_html.dart';
import 'package:application/features/product/domain/delivery_field_rules.dart';
import 'package:application/features/product/domain/entities/product.dart';
import 'package:application/features/product/domain/variant_options.dart';
import 'package:application/features/product/presentation/controllers/product_controllers.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/product_fakes.dart';

const _text = DeliveryField(
  key: 'nickname',
  label: 'Nickname',
  type: DeliveryFieldType.text,
  required: false,
  maxLength: 12,
);

const _number = DeliveryField(
  key: 'server_id',
  label: 'Server ID',
  type: DeliveryFieldType.number,
  required: true,
  maxLength: 255,
);

const _email = DeliveryField(
  key: 'account_email',
  label: 'Account email',
  type: DeliveryFieldType.email,
  required: true,
  maxLength: 255,
);

const _select = DeliveryField(
  key: 'zone',
  label: 'Zone',
  type: DeliveryFieldType.select,
  required: true,
  maxLength: 255,
  options: [
    DeliveryFieldOption(value: 'eu', label: 'Europe'),
    DeliveryFieldOption(value: 'na', label: 'North America'),
  ],
);

const _pattern = DeliveryField(
  key: 'player_id',
  label: 'Player ID',
  type: DeliveryFieldType.text,
  required: true,
  maxLength: 255,
  pattern: '[0-9]{6,10}',
);

void main() {
  group('delivery field validation mirrors DeliveryFieldValidator', () {
    final player = fixtureProduct().deliveryFields.single;

    // (field, raw value, expected error); every type of the server, the fixture's own field
    // first.
    final matrix = <(DeliveryField, String, DeliveryFieldError?)>[
      // Fixture `product/detail_en`: text, required, max 255, no pattern.
      (player, '', DeliveryFieldError.required),
      (player, '   \t\n', DeliveryFieldError.required),
      (player, '4817 2290 31', null),
      (player, 'x' * 255, null),
      (player, 'x' * 256, DeliveryFieldError.tooLong),
      (player, '${'é' * 255}  ', null), // code points, after trimming
      // text, optional, max 12
      (_text, '', null),
      (_text, 'abc', null),
      (_text, 'abcdefghijklm', DeliveryFieldError.tooLong),
      // number: ASCII digits only
      (_number, '123456', null),
      (_number, ' 123 ', null),
      (_number, '12 34', DeliveryFieldError.notDigits),
      (_number, '-12', DeliveryFieldError.notDigits),
      (_number, '1.5', DeliveryFieldError.notDigits),
      (_number, '١٢٣', DeliveryFieldError.notDigits),
      (_number, '', DeliveryFieldError.required),
      // email
      (_email, 'player@example.com', null),
      (_email, 'first.last+tag@mail.example.co', null),
      (_email, 'player@', DeliveryFieldError.invalidEmail),
      (_email, 'player.example.com', DeliveryFieldError.invalidEmail),
      (_email, 'a@b@c.com', DeliveryFieldError.invalidEmail),
      (_email, 'player@-bad.com', DeliveryFieldError.invalidEmail),
      (_email, '${'a' * 65}@example.com', DeliveryFieldError.invalidEmail),
      // select: one of options[].value (not the label)
      (_select, 'eu', null),
      (_select, 'Europe', DeliveryFieldError.invalidOption),
      (_select, 'EU', DeliveryFieldError.invalidOption),
      (_select, '', DeliveryFieldError.required),
      // pattern matches the whole trimmed value
      (_pattern, '123456', null),
      (_pattern, ' 1234567890 ', null),
      (_pattern, '12345', DeliveryFieldError.invalidFormat),
      (_pattern, '12345678901', DeliveryFieldError.invalidFormat),
      (_pattern, 'a123456', DeliveryFieldError.invalidFormat),
      (_pattern, '123456\n7', DeliveryFieldError.invalidFormat),
    ];
    for (final (field, value, expected) in matrix) {
      test(
        '${field.type} ${field.key} "${value.length > 20 ? '${value.substring(0, 20)}…' : value}" -> $expected',
        () {
          expect(validateDeliveryValue(field, value), expected);
        },
      );
    }

    test('the type check runs before the pattern, as on the server', () {
      const numberWithPattern = DeliveryField(
        key: 'id',
        label: 'ID',
        type: DeliveryFieldType.number,
        required: true,
        pattern: '[0-9]{3}',
      );
      expect(
        validateDeliveryValue(numberWithPattern, 'abc'),
        DeliveryFieldError.notDigits,
      );
      expect(
        validateDeliveryValue(numberWithPattern, '1234'),
        DeliveryFieldError.invalidFormat,
      );
    });

    test('a pattern Dart cannot compile is left to the server', () {
      const possessive = DeliveryField(
        key: 'id',
        label: 'ID',
        type: DeliveryFieldType.text,
        required: true,
        pattern: '[0-9]++(',
      );
      expect(compileDeliveryPattern('[0-9]++('), isNull);
      expect(validateDeliveryValue(possessive, 'anything'), isNull);
    });

    test('PHP trim keeps Unicode spaces the server keeps', () {
      expect(phpTrim(' \t\n\r\u0000\u000Bvalue\n '), 'value');
      expect(phpTrim(' value'), ' value');
    });

    test(
      'validates every field and sends only trimmed, known, non-empty values',
      () {
        final fields = [_pattern, _text, _select];
        final values = {
          'player_id': ' 1234567 ',
          'nickname': '  ',
          'zone': 'na',
          'unknown': 'x',
        };
        expect(validateDeliveryValues(fields, values), isEmpty);
        expect(deliveryValuesToSend(fields, values), {
          'player_id': '1234567',
          'zone': 'na',
        });
        expect(validateDeliveryValues(fields, const {}), {
          'player_id': DeliveryFieldError.required,
          'zone': DeliveryFieldError.required,
        });
      },
    );

    test('Arabic-Indic and Persian digits become Western digits', () {
      expect(westernDigits('١٢٣٤٥٦٧٨٩٠'), '1234567890');
      expect(westernDigits('۱۲۳'), '123');
      expect(westernDigits('a1٢'), 'a12');
    });
  });

  group('variant options', () {
    final regions = fixtureProduct().regions; // US, GL

    test('the fixture: US 10 USD in stock, GL 25 USD limited', () {
      final variants = fixtureVariants().variants;
      expect(regionOptions(variants, regions), [
        (code: 'US', name: 'United States', available: true),
        (code: 'GL', name: 'Global', available: true),
      ]);
      expect(initialSelection(variants, regions), (
        region: 'US',
        variantId: variants.first.publicId,
      ));
    });

    test('blocked variants carry their reason', () {
      expect(blockOf(offer('a')), isNull);
      expect(
        blockOf(offer('a', availability: 'out_of_stock')),
        VariantBlock.soldOut,
      );
      expect(
        blockOf(offer('a', customAmount: true)),
        VariantBlock.customAmount,
      );
      expect(blockOf(offer('a', purchasable: false)), VariantBlock.unavailable);
      expect(blockOf(offer('a', price: null)), VariantBlock.unavailable);
      expect(blockOf(offer('a', availability: 'limited')), isNull);
    });

    test('starts in the first region that has something to buy', () {
      final variants = [
        offer('us-10', availability: 'out_of_stock'),
        offer('gl-10', region: 'GL'),
        offer('gl-25', region: 'GL', value: '25.000000'),
      ];
      expect(initialSelection(variants, regions), (
        region: 'GL',
        variantId: 'gl-10',
      ));
    });

    test('nothing to buy: first region, no variant', () {
      final variants = [offer('us-10', purchasable: false)];
      expect(initialSelection(variants, regions), (
        region: 'US',
        variantId: null,
      ));
      expect(initialSelection(const [], regions), (
        region: null,
        variantId: null,
      ));
    });

    test(
      'changing region keeps the denomination when it can be bought there',
      () {
        final variants = [
          offer('us-10'),
          offer('us-25', value: '25.000000'),
          offer('gl-10', region: 'GL'),
          offer('gl-25', region: 'GL', value: '25.000000'),
          offer(
            'eu-25',
            region: 'EU',
            value: '25.000000',
            availability: 'out_of_stock',
          ),
          offer('eu-50', region: 'EU', value: '50.000000'),
        ];
        expect(variantAfterRegionChange(variants, 'GL', variants[1]), 'gl-25');
        expect(variantAfterRegionChange(variants, 'EU', variants[1]), 'eu-50');
        expect(variantAfterRegionChange(variants, 'XX', variants[1]), isNull);
      },
    );

    test('regions without a name and variants without a region', () {
      final variants = [offer('any', region: null), offer('eu', region: 'EU')];
      expect(regionOptions(variants, regions), [
        (code: null, name: null, available: true),
        (code: 'EU', name: 'EU', available: true),
      ]);
    });

    test(
      'quantity limit: the product limit, never above the request limit 10',
      () {
        final product = fixtureProduct();
        expect(maxQuantityFor(product), 10);
        expect(maxQuantityFor(product.copyWith(maxQuantityPerOrder: 3)), 3);
        expect(maxQuantityFor(product.copyWith(maxQuantityPerOrder: 50)), 10);
        expect(maxQuantityFor(product.copyWith(maxQuantityPerOrder: 0)), 1);
        expect(maxQuantityFor(product.copyWith(maxQuantityPerOrder: null)), 10);
      },
    );
  });

  group('ratings on decimal strings', () {
    test('labels drop trailing zeros', () {
      expect(ratingLabel('4.50'), '4.5');
      expect(ratingLabel('5.00'), '5');
      expect(ratingLabel('n/a'), 'n/a');
    });

    test('stars round down with a half from .5', () {
      expect(ratingStars('4.50'), (full: 4, half: true));
      expect(ratingStars('4.49'), (full: 4, half: false));
      expect(ratingStars('0.00'), (full: 0, half: false));
      expect(ratingStars('7'), (full: 5, half: false));
      expect(ratingStars('x'), (full: 0, half: false));
    });
  });

  group('product description HTML as text', () {
    test('the fixture description: one paragraph with a bold run', () {
      expect(parseSafeHtml(fixtureProduct().description!), [
        const HtmlBlock([
          HtmlSpan('Load your '),
          HtmlSpan('wallet', bold: true),
          HtmlSpan('.'),
        ]),
      ]);
      expect(
        parseSafeHtml(fixtureProduct('ar').description!).single.text,
        'اشحن محفظتك.',
      );
    });

    test('headings, lists, line breaks and entities', () {
      final blocks = parseSafeHtml(
        '<h2>How&nbsp;it works</h2><ol><li>Open &amp; sign in</li>'
        '<li><p>Enter the code</p></li></ol><ul><li>Fast</li></ul>'
        '<p>Line one<br>Line   two &#8212; &#x41;</p>',
      );
      expect(blocks.map((b) => (b.headingLevel, b.bullet, b.text)).toList(), [
        (2, null, 'How it works'),
        (0, '1.', 'Open & sign in'),
        (0, '2.', 'Enter the code'),
        (0, '•', 'Fast'),
        (0, null, 'Line one\nLine two — A'),
      ]);
    });

    test(
      'scripts, styles, images and links never become anything but text',
      () {
        final blocks = parseSafeHtml(
          '<p>Safe<script>alert(1)</script><style>p{}</style>'
          '<img src="https://x.test/a.png" onerror="alert(1)"> '
          '<a href="javascript:alert(1)">link</a><!-- note --></p>',
        );
        expect(blocks.single.text, 'Safe link');
      },
    );

    test('table cells are joined on one line', () {
      expect(
        parseSafeHtml('<table><tr><td>Region</td><td>Global</td></tr></table>')
            .single
            .text,
        'Region · Global',
      );
    });

    test('plain text without tags is one paragraph', () {
      expect(parseSafeHtml('Just text').single.text, 'Just text');
      expect(parseSafeHtml('  '), isEmpty);
    });
  });
}
