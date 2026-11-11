import 'package:application/core/router/deep_links.dart';
import 'package:application/features/push/domain/entities/push.dart';
import 'package:application/features/push/domain/push_routing.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/push_fakes.dart';

/// M-A18: where a tapped push leads, and the ticket link of the "support replied" email.
void main() {
  const order = '01JF0000000000000000000001';
  const ticket = '01JF0000000000000000000002';

  group('push payload routing', () {
    test('an order push opens the order', () {
      expect(deepLinkForPush(orderPush(order)), const AccountOrderLink(order));
      expect(
        deepLinkForPush(orderPush(order, type: 'payment_failed')),
        const AccountOrderLink(order),
      );
    });

    test('a ticket push opens the ticket thread', () {
      expect(
        deepLinkForPush(ticketPush(ticket)),
        const AccountTicketLink(ticket),
      );
    });

    test('without link_type, the order_id or ticket_id is used', () {
      expect(
        deepLinkForPush(const PushPayload(data: {'order_id': order})),
        const AccountOrderLink(order),
      );
      expect(
        deepLinkForPush(const PushPayload(data: {'ticket_id': ticket})),
        const AccountTicketLink(ticket),
      );
    });

    test('an unknown target, a missing id or an unsafe id opens nothing', () {
      expect(deepLinkForPush(const PushPayload()), isNull);
      expect(
        deepLinkForPush(
          const PushPayload(
            data: {'link_type': 'url', 'link_value': 'https://evil.test'},
          ),
        ),
        isNull,
      );
      expect(
        deepLinkForPush(
          const PushPayload(
            data: {'link_type': 'product', 'link_value': 'orbit-card'},
          ),
        ),
        isNull,
      );
      expect(
        deepLinkForPush(const PushPayload(data: {'link_type': 'order'})),
        isNull,
      );
      expect(
        deepLinkForPush(
          const PushPayload(
            data: {'link_type': 'order', 'link_value': '../wallet'},
          ),
        ),
        isNull,
      );
      expect(
        deepLinkForPush(
          const PushPayload(data: {'link_type': 'ticket', 'link_value': 'new'}),
        ),
        isNull,
      );
    });
  });

  group('ticket email links', () {
    DeepLink? parse(String url) =>
        parseDeepLink(Uri.parse(url), appLinkHost: 'boxcards.test');

    test(
      'the parser knows /account/tickets/{id} with and without a locale',
      () {
        expect(
          parse('https://boxcards.test/account/tickets/$ticket'),
          const AccountTicketLink(ticket),
        );
        expect(
          parse('https://boxcards.test/en/account/tickets/$ticket'),
          const AccountTicketLink(ticket),
        );
        expect(
          parse('https://boxcards.test/ar/account/tickets/$ticket'),
          const AccountTicketLink(ticket),
        );
        expect(
          parse('https://boxcards.test/account/tickets/new'),
          const AccountTicketLink(AccountTicketLink.newTicket),
        );
      },
    );

    test('other hosts and the ticket list are not app links', () {
      expect(parse('https://evil.test/account/tickets/$ticket'), isNull);
      expect(parse('http://boxcards.test/account/tickets/$ticket'), isNull);
      expect(parse('https://boxcards.test/account/tickets'), isNull);
    });
  });
}
