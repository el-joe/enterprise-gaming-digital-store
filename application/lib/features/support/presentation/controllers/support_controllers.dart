import 'dart:async';

import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/state/paged_list.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/features/orders/orders_providers.dart';
import 'package:application/features/support/domain/entities/support.dart';
import 'package:application/features/support/refund_status.dart';
import 'package:application/features/support/support_providers.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'support_controllers.g.dart';

/// Ticket topics of the API (`SupportTicketCategory`).
const ticketCategories = ['order', 'payment', 'refund', 'account', 'other'];

/// Limits of the API (`StoreTicketRequest`, `TicketReplyRequest`, `StoreRefundRequestRequest`).
const ticketSubjectMin = 3;
const ticketSubjectMax = 190;
const ticketBodyMax = 10000;
const refundReasonMin = 10;
const refundReasonMax = 2000;

/// A local check of a form, by API field.
enum FormProblem { required, tooShort }

/// Whether the store's web help page is configured.
@riverpod
bool supportLinkAvailable(Ref ref) =>
    ref.watch(latestAppConfigProvider)?.links.support != null;

/// Opens `links.support` in the system browser. False when none is configured or nothing
/// could open it.
@riverpod
Future<bool> Function() openWebSupport(Ref ref) => () async {
  final url = ref.read(latestAppConfigProvider)?.links.support;
  return url != null &&
      await ref.read(nativeBridgeProvider).openExternalUrl(url);
};

/// Support tickets (14.1, 14.4): `GET /account/tickets` (no messages), 20 per page.
@riverpod
class TicketsController extends _$TicketsController {
  late final PagedLoader<SupportTicket> _loader = PagedLoader(
    fetch: (page) => ref.read(supportRepositoryProvider).tickets(page: page),
    read: () => state,
    write: (value) => state = value,
    mounted: () => ref.mounted,
  );

  @override
  PagedListState<SupportTicket> build() {
    final signedIn = ref.watch(sessionProvider) == SessionStatus.signedIn;
    if (!signedIn || !ref.watch(supportEnabledProvider)) {
      return const PagedListState(content: Loadable.loaded([]));
    }
    unawaited(_loader.loadFirst());
    return const PagedListState();
  }

  Future<void> retry() => _loader.retry();

  Future<void> refresh() => _loader.refresh();

  Future<void> loadMore() => _loader.loadMore();
}

@immutable
final class TicketThreadState {
  const TicketThreadState({
    this.ticket = const Loadable.loading(),
    this.sending = false,
    this.problem,
    this.failure,
    this.closedNow = false,
  });

  /// The ticket with its messages (internal staff notes are never sent by the server).
  final Loadable<SupportTicket> ticket;
  final bool sending;

  /// A local check of the reply (`body`).
  final FormProblem? problem;

  /// The last reply's failure (422 `body` beside the composer, anything else in a banner).
  final Failure? failure;

  /// The server answered `ticket_closed` to a reply: the thread shows the closed state.
  final bool closedNow;

  bool get closed => closedNow || ticket.valueOrNull?.status == 'closed';

  String? get bodyError => switch (failure) {
    final ValidationFailure validation => validation.firstErrorFor('body'),
    _ => null,
  };

  Failure? get bannerFailure => switch (failure) {
    final ValidationFailure validation
        when validation.firstErrorFor('body') != null =>
      null,
    ConflictFailure(code: 'ticket_closed') => null,
    _ => failure,
  };

  TicketThreadState copyWith({
    Loadable<SupportTicket>? ticket,
    bool? sending,
    FormProblem? Function()? problem,
    Failure? Function()? failure,
    bool? closedNow,
  }) => TicketThreadState(
    ticket: ticket ?? this.ticket,
    sending: sending ?? this.sending,
    problem: problem == null ? this.problem : problem(),
    failure: failure == null ? this.failure : failure(),
    closedNow: closedNow ?? this.closedNow,
  );
}

/// A ticket's thread (14.3) and the reply composer. A reply is `POST /tickets/{id}/messages`;
/// 409 `ticket_closed` turns the thread into the closed state (14.4): no composer, "New
/// request".
@riverpod
class TicketThreadController extends _$TicketThreadController {
  int _generation = 0;

  @override
  TicketThreadState build(String ticketPublicId) {
    unawaited(_load());
    return const TicketThreadState();
  }

  Future<void> _load({bool keep = false}) async {
    final generation = ++_generation;
    final result = await ref
        .read(supportRepositoryProvider)
        .ticket(ticketPublicId);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    switch (result) {
      case Ok(:final value):
        state = state.copyWith(ticket: Loadable.loaded(value));
      case Err(:final failure):
        if (!keep || state.ticket is! Loaded<SupportTicket>) {
          state = state.copyWith(ticket: Loadable.failed(failure));
        }
    }
  }

  Future<void> retry() async {
    state = const TicketThreadState();
    await _load();
  }

  Future<void> refresh() => _load(keep: true);

  void edited() {
    if (state.problem != null || state.failure != null) {
      state = state.copyWith(problem: () => null, failure: () => null);
    }
  }

  /// Sends [body]. True when it was sent (the page clears the composer).
  Future<bool> reply(String body) async {
    if (state.sending || state.closed || state.ticket is! Loaded) {
      return false;
    }
    final text = body.trim();
    if (text.isEmpty) {
      state = state.copyWith(problem: () => FormProblem.required);
      return false;
    }
    state = state.copyWith(
      sending: true,
      problem: () => null,
      failure: () => null,
    );
    final link = ref.keepAlive();
    try {
      final result = await ref
          .read(supportRepositoryProvider)
          .reply(ticketPublicId, body: text);
      if (!ref.mounted) {
        return false;
      }
      switch (result) {
        case Ok(:final value):
          _generation++;
          state = state.copyWith(
            ticket: Loadable.loaded(value),
            sending: false,
          );
          ref.invalidate(ticketsControllerProvider);
          return true;
        case Err(:final failure):
          state = state.copyWith(
            sending: false,
            failure: () => failure,
            closedNow:
                failure is ConflictFailure && failure.code == 'ticket_closed',
          );
          if (state.closedNow) {
            ref.invalidate(ticketsControllerProvider);
          }
          return false;
      }
    } finally {
      link.close();
    }
  }
}

@immutable
final class NewTicketState {
  const NewTicketState({
    required this.category,
    this.orderPublicId,
    this.orders = const Loadable.loading(),
    this.saving = false,
    this.problems = const {},
    this.failure,
  });

  final String category;

  /// The linked order (optional).
  final String? orderPublicId;

  /// The customer's orders for the picker (the first 50, newest first). A failure only hides
  /// the picker's list; the request can still be sent without an order.
  final Loadable<List<OrderSummary>> orders;
  final bool saving;
  final Map<String, FormProblem> problems;
  final Failure? failure;

  static const fields = ['category', 'subject', 'body', 'order_public_id'];

  /// The linked order's summary, when the picker has it.
  OrderSummary? get selectedOrder {
    for (final order in orders.valueOrNull ?? const <OrderSummary>[]) {
      if (order.publicId == orderPublicId) {
        return order;
      }
    }
    return null;
  }

  String? serverError(String field) => switch (failure) {
    final ValidationFailure validation => validation.firstErrorFor(field),
    _ => null,
  };

  Failure? get bannerFailure => switch (failure) {
    final ValidationFailure validation
        when fields.any((f) => validation.firstErrorFor(f) != null) =>
      null,
    _ => failure,
  };

  NewTicketState copyWith({
    String? category,
    String? Function()? orderPublicId,
    Loadable<List<OrderSummary>>? orders,
    bool? saving,
    Map<String, FormProblem>? problems,
    Failure? Function()? failure,
  }) => NewTicketState(
    category: category ?? this.category,
    orderPublicId: orderPublicId == null ? this.orderPublicId : orderPublicId(),
    orders: orders ?? this.orders,
    saving: saving ?? this.saving,
    problems: problems ?? this.problems,
    failure: failure == null ? this.failure : failure(),
  );
}

/// New support request (14.2): topic (the API's categories), an optional linked order from the
/// customer's orders, subject and message. No attachments: the API has no upload. A 422 for an
/// order that is not the customer's shows beside the order picker.
@riverpod
class NewTicketController extends _$NewTicketController {
  @override
  NewTicketState build({String? order, String? category}) {
    unawaited(_loadOrders());
    return NewTicketState(
      category: ticketCategories.contains(category) ? category! : 'order',
      orderPublicId: order,
    );
  }

  Future<void> _loadOrders() async {
    final result = await ref
        .read(ordersRepositoryProvider)
        .list(const OrdersQuery(perPage: 50));
    if (!ref.mounted) {
      return;
    }
    state = state.copyWith(
      orders: switch (result) {
        Ok(:final value) => Loadable.loaded(value.items),
        Err(:final failure) => Loadable.failed(failure),
      },
    );
  }

  void setCategory(String category) {
    if (ticketCategories.contains(category)) {
      state = state.copyWith(category: category, failure: () => null);
    }
  }

  void setOrder(String? orderPublicId) => state = state.copyWith(
    orderPublicId: () => orderPublicId,
    failure: () => null,
  );

  void edited() {
    if (state.failure != null || state.problems.isNotEmpty) {
      state = state.copyWith(problems: const {}, failure: () => null);
    }
  }

  /// Opens the ticket. Returns it on success.
  Future<SupportTicket?> submit({
    required String subject,
    required String body,
  }) async {
    if (state.saving) {
      return null;
    }
    final cleanSubject = subject.trim();
    final cleanBody = body.trim();
    final problems = <String, FormProblem>{
      if (cleanSubject.isEmpty)
        'subject': FormProblem.required
      else if (cleanSubject.length < ticketSubjectMin)
        'subject': FormProblem.tooShort,
      if (cleanBody.isEmpty) 'body': FormProblem.required,
    };
    if (problems.isNotEmpty) {
      state = state.copyWith(problems: problems, failure: () => null);
      return null;
    }
    state = state.copyWith(
      saving: true,
      problems: const {},
      failure: () => null,
    );
    final link = ref.keepAlive();
    try {
      final result = await ref
          .read(supportRepositoryProvider)
          .openTicket(
            category: state.category,
            subject: cleanSubject,
            body: cleanBody,
            orderPublicId: state.orderPublicId,
          );
      if (!ref.mounted) {
        return result.valueOrNull;
      }
      switch (result) {
        case Ok(:final value):
          state = state.copyWith(saving: false);
          ref.invalidate(ticketsControllerProvider);
          return value;
        case Err(:final failure):
          state = state.copyWith(saving: false, failure: () => failure);
          return null;
      }
    } finally {
      link.close();
    }
  }
}

/// The customer's refund requests (15.2): `GET /account/refund-requests`, 20 per page.
@riverpod
class RefundRequestsController extends _$RefundRequestsController {
  late final PagedLoader<RefundRequest> _loader = PagedLoader(
    fetch: (page) =>
        ref.read(supportRepositoryProvider).refundRequests(page: page),
    read: () => state,
    write: (value) => state = value,
    mounted: () => ref.mounted,
  );

  @override
  PagedListState<RefundRequest> build() {
    final signedIn = ref.watch(sessionProvider) == SessionStatus.signedIn;
    if (!signedIn) {
      return const PagedListState(content: Loadable.loaded([]));
    }
    unawaited(_loader.loadFirst());
    return const PagedListState();
  }

  Future<void> retry() => _loader.retry();

  Future<void> refresh() => _loader.refresh();

  Future<void> loadMore() => _loader.loadMore();
}

/// What the refund form needs: the order (header card) and the customer's tickets about it
/// (to link one, optional).
@immutable
final class RefundFormData {
  const RefundFormData({required this.order, required this.tickets});

  final OrderDetail order;
  final List<SupportTicket> tickets;
}

@immutable
final class RefundRequestFormState {
  const RefundRequestFormState({
    this.data = const Loadable.loading(),
    this.ticketPublicId,
    this.saving = false,
    this.problem,
    this.failure,
  });

  final Loadable<RefundFormData> data;

  /// The linked support ticket (optional).
  final String? ticketPublicId;
  final bool saving;

  /// A local check of the reason.
  final FormProblem? problem;
  final Failure? failure;

  static const fields = ['reason', 'support_ticket_public_id'];

  String? serverError(String field) => switch (failure) {
    final ValidationFailure validation => validation.firstErrorFor(field),
    _ => null,
  };

  /// Everything not shown beside a field: `refund_request_open`, `refund_not_allowed`, the 422
  /// for an order that is not the customer's (`order_public_id`), and other failures.
  Failure? get bannerFailure => switch (failure) {
    final ValidationFailure validation
        when fields.any((f) => validation.firstErrorFor(f) != null) =>
      null,
    _ => failure,
  };

  RefundRequestFormState copyWith({
    Loadable<RefundFormData>? data,
    String? Function()? ticketPublicId,
    bool? saving,
    FormProblem? Function()? problem,
    Failure? Function()? failure,
  }) => RefundRequestFormState(
    data: data ?? this.data,
    ticketPublicId: ticketPublicId == null
        ? this.ticketPublicId
        : ticketPublicId(),
    saving: saving ?? this.saving,
    problem: problem == null ? this.problem : problem(),
    failure: failure == null ? this.failure : failure(),
  );
}

/// Request a refund (15.1) of [orderPublicId]: the reason (10 to 2000 characters) and an
/// optional linked ticket. The API refunds whole requests (staff choose the units), so there is
/// no unit choice. The server decides eligibility: `refund_request_open`,
/// `refund_not_allowed` (unpaid) and 422 for an order that is not the customer's.
@riverpod
class RefundRequestFormController extends _$RefundRequestFormController {
  @override
  RefundRequestFormState build(String orderPublicId) {
    unawaited(_load());
    return const RefundRequestFormState();
  }

  Future<void> _load() async {
    final orderCall = ref.read(ordersRepositoryProvider).detail(orderPublicId);
    final ticketsCall = ref
        .read(supportRepositoryProvider)
        .tickets(perPage: 50);
    final order = await orderCall;
    final tickets = await ticketsCall;
    if (!ref.mounted) {
      return;
    }
    state = state.copyWith(
      data: switch (order) {
        Ok(:final value) => Loadable.loaded(
          RefundFormData(
            order: value,
            // A failed ticket list only hides the optional picker.
            tickets: [
              for (final ticket
                  in tickets.valueOrNull?.items ?? const <SupportTicket>[])
                if (ticket.order?.publicId == orderPublicId &&
                    ticket.status != 'closed')
                  ticket,
            ],
          ),
        ),
        Err(:final failure) => Loadable.failed(failure),
      },
    );
  }

  Future<void> retry() async {
    state = const RefundRequestFormState();
    await _load();
  }

  void setTicket(String? ticketPublicId) => state = state.copyWith(
    ticketPublicId: () => ticketPublicId,
    failure: () => null,
  );

  void edited() {
    if (state.failure != null || state.problem != null) {
      state = state.copyWith(problem: () => null, failure: () => null);
    }
  }

  /// Sends the request. Returns it (`requested`) on success.
  Future<RefundRequest?> submit({required String reason}) async {
    if (state.saving) {
      return null;
    }
    final text = reason.trim();
    final problem = text.isEmpty
        ? FormProblem.required
        : text.length < refundReasonMin
        ? FormProblem.tooShort
        : null;
    if (problem != null) {
      state = state.copyWith(problem: () => problem, failure: () => null);
      return null;
    }
    state = state.copyWith(
      saving: true,
      problem: () => null,
      failure: () => null,
    );
    final link = ref.keepAlive();
    try {
      final result = await ref
          .read(supportRepositoryProvider)
          .requestRefund(
            orderPublicId: orderPublicId,
            reason: text,
            supportTicketPublicId: state.ticketPublicId,
          );
      if (result.isOk) {
        ref
          ..invalidate(refundRequestsControllerProvider)
          ..invalidate(orderRefundRequestProvider(orderPublicId));
      }
      if (ref.mounted) {
        state = state.copyWith(
          saving: false,
          failure: () => result.failureOrNull,
        );
      }
      return result.valueOrNull;
    } finally {
      link.close();
    }
  }
}
