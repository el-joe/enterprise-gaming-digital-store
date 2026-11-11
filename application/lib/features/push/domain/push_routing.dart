import 'package:application/core/router/deep_links.dart';
import 'package:application/features/push/domain/entities/push.dart';

final _publicId = RegExp(r'^[A-Za-z0-9_-]{1,128}$');

/// Where a tapped push leads (M-A18): the order (`link_type` `order`, or an `order_id`) or the
/// ticket thread (`ticket`, or a `ticket_id`), through the app-link routes. Anything else, or an
/// id that is not a plain public id, opens nothing.
DeepLink? deepLinkForPush(PushPayload payload) {
  final data = payload.data;
  final type = data['link_type'];
  final value = data['link_value'];
  String? valid(String? id) =>
      id != null && _publicId.hasMatch(id) && id != AccountTicketLink.newTicket
      ? id
      : null;

  final order = switch (type) {
    'order' => valid(value),
    null => valid(data['order_id']),
    _ => null,
  };
  if (order != null) {
    return AccountOrderLink(order);
  }
  final ticket = switch (type) {
    'ticket' => valid(value),
    null => valid(data['ticket_id']),
    _ => null,
  };
  if (ticket != null) {
    return AccountTicketLink(ticket);
  }
  return null;
}
