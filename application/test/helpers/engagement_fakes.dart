import 'dart:async';

import 'package:application/core/network/result.dart';
import 'package:application/features/notifications/data/models/notification_models.dart';
import 'package:application/features/notifications/domain/entities/notifications.dart';
import 'package:application/features/notifications/domain/repositories/notifications_repository.dart';
import 'package:application/features/reviews/data/models/review_models.dart';
import 'package:application/features/reviews/domain/entities/reviews.dart';
import 'package:application/features/reviews/domain/repositories/reviews_repository.dart';
import 'package:application/features/support/data/models/support_models.dart';
import 'package:application/features/support/domain/entities/support.dart';
import 'package:application/features/support/domain/repositories/support_repository.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';

import 'fixtures.dart';

/// M-A17 fakes: reviews, support (tickets and refund requests) and notifications, each
/// answering from fields the test sets and recording every call. Defaults are "nothing yet"
/// (no eligible item, no ticket, no refund request, an empty inbox with 0 unread), so screens
/// of earlier milestones are unchanged.

/// One page of [items].
Paged<T> pagedOf<T>(
  List<T> items, {
  int page = 1,
  int lastPage = 1,
  int perPage = 20,
}) => Paged(
  items: items,
  page: PageInfo(
    currentPage: page,
    perPage: perPage,
    total: items.length,
    lastPage: lastPage,
  ),
);

// Reviews ----------------------------------------------------------------------------------

/// `reviews/eligible_ok` through the real DTO: order item 1 (Orbit Card) of FIXTURE-0001.
List<ReviewableItem> fixtureReviewable() => [
  for (final item
      in ApiFixture.load('reviews/eligible_ok').dataMap['items']
          as List<Object?>)
    ReviewableItemDto.fromJson(item! as Map<String, dynamic>).toEntity(),
];

/// `reviews/list_ok` through the real DTO: review 1, pending, 5 stars.
List<MyReview> fixtureMyReviews() => [
  for (final item in ApiFixture.load('reviews/list_ok').data! as List<Object?>)
    MyReviewDto.fromJson(item! as Map<String, dynamic>).toEntity(),
];

ReviewableItem reviewable({
  int id = 1,
  String product = 'Orbit Card',
  String slug = 'orbit-card',
  String order = 'BX-1004',
}) => ReviewableItem(
  orderItemId: id,
  orderPublicId: '01JF000000000000000000000$id',
  orderNumber: order,
  productName: product,
  productSlug: slug,
);

MyReview myReview({
  int id = 1,
  String product = 'Pulse Wallet Code',
  int rating = 4,
  String status = 'pending',
  String? title,
  String? body = 'Fast delivery, worked first time.',
}) => MyReview(
  id: id,
  orderItemId: id + 100,
  productName: product,
  productSlug: 'pulse-wallet',
  rating: rating,
  status: status,
  createdAt: DateTime.utc(2026, 11, 10, 12),
  title: title,
  body: body,
);

class FakeReviewsRepository implements ReviewsRepository {
  Result<List<ReviewableItem>> eligibleResult = const Ok([]);
  var eligibleCalls = 0;

  Map<int, Result<Paged<MyReview>>> pages = {1: Ok(pagedOf(const []))};
  final pageCalls = <int>[];

  /// Answer of submit and update (null: the review echoed back as pending).
  Result<MyReview>? writeResult;
  final submits = <Map<String, Object?>>[];
  final updates = <Map<String, Object?>>[];

  Completer<void>? writeGate;

  @override
  Future<Result<List<ReviewableItem>>> eligible() async {
    eligibleCalls++;
    return eligibleResult;
  }

  @override
  Future<Result<Paged<MyReview>>> myReviews({
    int page = 1,
    int perPage = 20,
  }) async {
    pageCalls.add(page);
    return pages[page] ?? Ok(pagedOf(const [], page: page));
  }

  @override
  Future<Result<MyReview>> submit({
    required int orderItemId,
    required int rating,
    String? title,
    String? body,
  }) async {
    submits.add({
      'order_item_id': orderItemId,
      'rating': rating,
      'title': title,
      'body': body,
    });
    await writeGate?.future;
    return writeResult ??
        Ok(
          myReview(
            id: 50,
            rating: rating,
            title: title,
            body: body,
          ).copyWith(orderItemId: orderItemId),
        );
  }

  @override
  Future<Result<MyReview>> update(
    int id, {
    required int rating,
    String? title,
    String? body,
  }) async {
    updates.add({'id': id, 'rating': rating, 'title': title, 'body': body});
    await writeGate?.future;
    return writeResult ??
        Ok(myReview(id: id, rating: rating, title: title, body: body));
  }
}

// Support ----------------------------------------------------------------------------------

/// `support/ticket_detail` through the real DTO (two customer messages, `pending_staff`).
SupportTicket fixtureTicket([String name = 'support/ticket_detail']) =>
    SupportTicketDto.fromJson(
      ApiFixture.load(name).dataMap['ticket'] as Map<String, dynamic>,
    ).toEntity();

/// `support/refund_request_created` through the real DTO.
RefundRequest fixtureRefundRequest() => RefundRequestDto.fromJson(
  ApiFixture.load('support/refund_request_created').dataMap['refund_request']
      as Map<String, dynamic>,
).toEntity();

const ticketId = '01JF00000000000000000000T1';

SupportTicket ticket({
  String id = ticketId,
  String number = 'T-261110-0001',
  String subject = 'Code missing',
  String status = 'pending_staff',
  String category = 'order',
  OrderRef? order = const OrderRef(
    publicId: '01JF0000000000000000000001',
    orderNumber: 'BX-1002',
  ),
  List<TicketMessage> messages = const [],
  DateTime? at,
}) => SupportTicket(
  publicId: id,
  ticketNumber: number,
  category: category,
  subject: subject,
  status: status,
  createdAt: at ?? DateTime(2026, 11, 10, 10, 2),
  lastActivityAt: at ?? DateTime(2026, 11, 10, 14, 45),
  order: order,
  messages: messages,
);

TicketMessage message(
  int id,
  String body, {
  String from = 'customer',
  DateTime? at,
}) => TicketMessage(
  id: id,
  from: from,
  body: body,
  createdAt: at ?? DateTime(2026, 11, 10, 10, 2),
);

RefundRequest refundRequest({
  int id = 1,
  String order = '01JF0000000000000000000001',
  String number = 'BX-1002',
  String status = 'requested',
  String reason = 'The second code shows as already used.',
  DateTime? at,
}) => RefundRequest(
  id: id,
  order: OrderRef(publicId: order, orderNumber: number),
  reason: reason,
  status: status,
  createdAt: at ?? DateTime(2026, 11, 10, 9),
);

/// [ticket], callable inside [FakeSupportRepository] (whose `ticket` method shadows it).
const _makeTicket = ticket;

class FakeSupportRepository implements SupportRepository {
  Map<int, Result<Paged<SupportTicket>>> ticketPages = {
    1: Ok(pagedOf(const [])),
  };
  final ticketPageCalls = <({int page, int perPage})>[];

  /// The thread answers by id; a missing id is the fixture thread.
  final threads = <String, Result<SupportTicket>>{};
  final threadCalls = <String>[];

  Result<SupportTicket>? openResult;
  final opened = <Map<String, Object?>>[];

  Result<SupportTicket>? replyResult;
  final replies = <({String ticket, String body})>[];

  Map<int, Result<Paged<RefundRequest>>> refundPages = {
    1: Ok(pagedOf(const [])),
  };
  final refundPageCalls = <({int page, int perPage})>[];

  Result<RefundRequest>? refundResult;
  final refunds = <Map<String, Object?>>[];

  Completer<void>? writeGate;

  @override
  Future<Result<Paged<SupportTicket>>> tickets({
    int page = 1,
    int perPage = 20,
  }) async {
    ticketPageCalls.add((page: page, perPage: perPage));
    return ticketPages[page] ?? Ok(pagedOf(const [], page: page));
  }

  @override
  Future<Result<SupportTicket>> ticket(String publicId) async {
    threadCalls.add(publicId);
    return threads[publicId] ?? Ok(fixtureTicket());
  }

  @override
  Future<Result<SupportTicket>> openTicket({
    required String category,
    required String subject,
    required String body,
    String? orderPublicId,
  }) async {
    opened.add({
      'category': category,
      'subject': subject,
      'body': body,
      'order_public_id': orderPublicId,
    });
    await writeGate?.future;
    return openResult ??
        Ok(
          _makeTicket(
            id: '01JF00000000000000000000T9',
            subject: subject,
            status: 'open',
            category: category,
            order: orderPublicId == null
                ? null
                : OrderRef(publicId: orderPublicId, orderNumber: 'BX-1002'),
            messages: [message(1, body)],
          ),
        );
  }

  @override
  Future<Result<SupportTicket>> reply(
    String publicId, {
    required String body,
  }) async {
    replies.add((ticket: publicId, body: body));
    await writeGate?.future;
    if (replyResult case final result?) {
      return result;
    }
    final current = (threads[publicId] ?? Ok(fixtureTicket())).valueOrNull!;
    return Ok(
      current.copyWith(
        status: 'pending_staff',
        messages: [
          ...current.messages,
          message(current.messages.length + 10, body),
        ],
      ),
    );
  }

  @override
  Future<Result<Paged<RefundRequest>>> refundRequests({
    int page = 1,
    int perPage = 20,
  }) async {
    refundPageCalls.add((page: page, perPage: perPage));
    return refundPages[page] ?? Ok(pagedOf(const [], page: page));
  }

  @override
  Future<Result<RefundRequest>> requestRefund({
    required String orderPublicId,
    required String reason,
    String? supportTicketPublicId,
  }) async {
    refunds.add({
      'order_public_id': orderPublicId,
      'reason': reason,
      'support_ticket_public_id': supportTicketPublicId,
    });
    await writeGate?.future;
    return refundResult ??
        Ok(refundRequest(order: orderPublicId, reason: reason));
  }
}

// Notifications ----------------------------------------------------------------------------

/// `notifications/list_ok` through the real DTO: a ticket reply and an order delivery, unread.
List<InboxNotification> fixtureInbox() => [
  for (final item
      in ApiFixture.load('notifications/list_ok').data! as List<Object?>)
    InboxNotificationDto.fromJson(item! as Map<String, dynamic>).toEntity(),
];

/// `notifications/preferences_ok` through the real DTO.
List<NotificationPreference> fixturePreferences() => [
  for (final item
      in ApiFixture.load('notifications/preferences_ok').dataMap['preferences']
          as List<Object?>)
    NotificationPreferenceDto.fromJson(item! as Map<String, dynamic>)
        .toEntity(),
];

InboxNotification inboxItem({
  required String id,
  String type = 'order_delivered',
  String category = 'orders',
  String title = 'Order BX-1001 is ready',
  String body = 'Your order has been delivered. Open it to view your codes.',
  LinkTarget? link,
  DateTime? at,
  DateTime? readAt,
}) => InboxNotification(
  id: id,
  type: type,
  category: category,
  title: title,
  body: body,
  createdAt: at ?? DateTime.utc(2026, 11, 11, 11, 58),
  link: link,
  readAt: readAt,
);

LinkTarget orderLink(String id) => LinkTarget(
  type: 'order',
  value: id,
  url: Uri.parse('https://boxcards.test/account/orders/$id'),
);

LinkTarget ticketLink(String id) => LinkTarget(
  type: 'ticket',
  value: id,
  url: Uri.parse('https://boxcards.test/account/tickets/$id'),
);

InboxPage inboxPageOf(
  List<InboxNotification> items, {
  int page = 1,
  int lastPage = 1,
  int? unread,
}) => InboxPage(
  notifications: items,
  page: PageInfo(
    currentPage: page,
    perPage: 20,
    total: items.length,
    lastPage: lastPage,
  ),
  unreadCount: unread ?? items.where((n) => !n.isRead).length,
);

class FakeNotificationsRepository implements NotificationsRepository {
  Map<int, Result<InboxPage>> pages = {1: Ok(inboxPageOf(const []))};
  final pageCalls = <int>[];

  /// Page number's call waits for its completer.
  final pageGates = <int, Completer<void>>{};

  Result<int> unreadResult = const Ok(0);
  var unreadCalls = 0;

  /// Mark read answers by id (null: the notification echoed back as read).
  final markReadResults = <String, Result<InboxNotification>>{};
  final markedRead = <String>[];

  Result<int> markAllResult = const Ok(2);
  var markAllCalls = 0;

  Result<List<NotificationPreference>> preferencesResult = Ok(
    fixturePreferences(),
  );

  /// Answer of a save (null: the current matrix with the change applied).
  Result<List<NotificationPreference>>? updateResult;
  final updates = <List<PreferenceChange>>[];

  @override
  Future<Result<InboxPage>> inbox({
    bool unreadOnly = false,
    int page = 1,
    int perPage = 20,
  }) async {
    pageCalls.add(page);
    await pageGates[page]?.future;
    return pages[page] ?? Ok(inboxPageOf(const [], page: page));
  }

  @override
  Future<Result<int>> unreadCount() async {
    unreadCalls++;
    return unreadResult;
  }

  @override
  Future<Result<InboxNotification>> markRead(String id) async {
    markedRead.add(id);
    if (markReadResults[id] case final result?) {
      return result;
    }
    return Ok(inboxItem(id: id, readAt: DateTime.utc(2026, 11, 11, 12, 1)));
  }

  @override
  Future<Result<int>> markAllRead() async {
    markAllCalls++;
    return markAllResult;
  }

  @override
  Future<Result<List<NotificationPreference>>> preferences() async =>
      preferencesResult;

  @override
  Future<Result<List<NotificationPreference>>> updatePreferences(
    List<PreferenceChange> changes,
  ) async {
    updates.add(changes);
    if (updateResult case final result?) {
      return result;
    }
    final current = preferencesResult.valueOrNull ?? const [];
    return Ok([
      for (final p in current)
        [
              for (final c in changes)
                if (c.category == p.category && c.channel == p.channel) c,
            ].firstOrNull?.let((c) => p.copyWith(enabled: c.enabled)) ??
            p,
    ]);
  }
}

extension _Let<T> on T {
  R let<R>(R Function(T value) f) => f(this);
}
