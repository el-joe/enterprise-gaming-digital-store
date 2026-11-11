// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'support_controllers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Whether the store's web help page is configured.

@ProviderFor(supportLinkAvailable)
final supportLinkAvailableProvider = SupportLinkAvailableProvider._();

/// Whether the store's web help page is configured.

final class SupportLinkAvailableProvider
    extends $FunctionalProvider<bool, bool, bool>
    with $Provider<bool> {
  /// Whether the store's web help page is configured.
  SupportLinkAvailableProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'supportLinkAvailableProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$supportLinkAvailableHash();

  @$internal
  @override
  $ProviderElement<bool> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  bool create(Ref ref) {
    return supportLinkAvailable(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(bool value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<bool>(value),
    );
  }
}

String _$supportLinkAvailableHash() =>
    r'53f11ff876871f158e2a49129c79a1166de22b2d';

/// Opens `links.support` in the system browser. False when none is configured or nothing
/// could open it.

@ProviderFor(openWebSupport)
final openWebSupportProvider = OpenWebSupportProvider._();

/// Opens `links.support` in the system browser. False when none is configured or nothing
/// could open it.

final class OpenWebSupportProvider
    extends
        $FunctionalProvider<
          Future<bool> Function(),
          Future<bool> Function(),
          Future<bool> Function()
        >
    with $Provider<Future<bool> Function()> {
  /// Opens `links.support` in the system browser. False when none is configured or nothing
  /// could open it.
  OpenWebSupportProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'openWebSupportProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$openWebSupportHash();

  @$internal
  @override
  $ProviderElement<Future<bool> Function()> $createElement(
    $ProviderPointer pointer,
  ) => $ProviderElement(pointer);

  @override
  Future<bool> Function() create(Ref ref) {
    return openWebSupport(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(Future<bool> Function() value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<Future<bool> Function()>(value),
    );
  }
}

String _$openWebSupportHash() => r'34d18d5ecf70b80a236a5a4f6a65ce16cd1298f1';

/// Support tickets (14.1, 14.4): `GET /account/tickets` (no messages), 20 per page.

@ProviderFor(TicketsController)
final ticketsControllerProvider = TicketsControllerProvider._();

/// Support tickets (14.1, 14.4): `GET /account/tickets` (no messages), 20 per page.
final class TicketsControllerProvider
    extends
        $NotifierProvider<TicketsController, PagedListState<SupportTicket>> {
  /// Support tickets (14.1, 14.4): `GET /account/tickets` (no messages), 20 per page.
  TicketsControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'ticketsControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$ticketsControllerHash();

  @$internal
  @override
  TicketsController create() => TicketsController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(PagedListState<SupportTicket> value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<PagedListState<SupportTicket>>(
        value,
      ),
    );
  }
}

String _$ticketsControllerHash() => r'de8b62b8b6e17691def71f89e8db418f2954f69c';

/// Support tickets (14.1, 14.4): `GET /account/tickets` (no messages), 20 per page.

abstract class _$TicketsController
    extends $Notifier<PagedListState<SupportTicket>> {
  PagedListState<SupportTicket> build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref =
        this.ref
            as $Ref<
              PagedListState<SupportTicket>,
              PagedListState<SupportTicket>
            >;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<
                PagedListState<SupportTicket>,
                PagedListState<SupportTicket>
              >,
              PagedListState<SupportTicket>,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// A ticket's thread (14.3) and the reply composer. A reply is `POST /tickets/{id}/messages`;
/// 409 `ticket_closed` turns the thread into the closed state (14.4): no composer, "New
/// request".

@ProviderFor(TicketThreadController)
final ticketThreadControllerProvider = TicketThreadControllerFamily._();

/// A ticket's thread (14.3) and the reply composer. A reply is `POST /tickets/{id}/messages`;
/// 409 `ticket_closed` turns the thread into the closed state (14.4): no composer, "New
/// request".
final class TicketThreadControllerProvider
    extends $NotifierProvider<TicketThreadController, TicketThreadState> {
  /// A ticket's thread (14.3) and the reply composer. A reply is `POST /tickets/{id}/messages`;
  /// 409 `ticket_closed` turns the thread into the closed state (14.4): no composer, "New
  /// request".
  TicketThreadControllerProvider._({
    required TicketThreadControllerFamily super.from,
    required String super.argument,
  }) : super(
         retry: null,
         name: r'ticketThreadControllerProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$ticketThreadControllerHash();

  @override
  String toString() {
    return r'ticketThreadControllerProvider'
        ''
        '($argument)';
  }

  @$internal
  @override
  TicketThreadController create() => TicketThreadController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(TicketThreadState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<TicketThreadState>(value),
    );
  }

  @override
  bool operator ==(Object other) {
    return other is TicketThreadControllerProvider &&
        other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$ticketThreadControllerHash() =>
    r'230fb149d9c15412e3dad0124351957adaff9015';

/// A ticket's thread (14.3) and the reply composer. A reply is `POST /tickets/{id}/messages`;
/// 409 `ticket_closed` turns the thread into the closed state (14.4): no composer, "New
/// request".

final class TicketThreadControllerFamily extends $Family
    with
        $ClassFamilyOverride<
          TicketThreadController,
          TicketThreadState,
          TicketThreadState,
          TicketThreadState,
          String
        > {
  TicketThreadControllerFamily._()
    : super(
        retry: null,
        name: r'ticketThreadControllerProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// A ticket's thread (14.3) and the reply composer. A reply is `POST /tickets/{id}/messages`;
  /// 409 `ticket_closed` turns the thread into the closed state (14.4): no composer, "New
  /// request".

  TicketThreadControllerProvider call(String ticketPublicId) =>
      TicketThreadControllerProvider._(argument: ticketPublicId, from: this);

  @override
  String toString() => r'ticketThreadControllerProvider';
}

/// A ticket's thread (14.3) and the reply composer. A reply is `POST /tickets/{id}/messages`;
/// 409 `ticket_closed` turns the thread into the closed state (14.4): no composer, "New
/// request".

abstract class _$TicketThreadController extends $Notifier<TicketThreadState> {
  late final _$args = ref.$arg as String;
  String get ticketPublicId => _$args;

  TicketThreadState build(String ticketPublicId);
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<TicketThreadState, TicketThreadState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<TicketThreadState, TicketThreadState>,
              TicketThreadState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, () => build(_$args));
  }
}

/// New support request (14.2): topic (the API's categories), an optional linked order from the
/// customer's orders, subject and message. No attachments: the API has no upload. A 422 for an
/// order that is not the customer's shows beside the order picker.

@ProviderFor(NewTicketController)
final newTicketControllerProvider = NewTicketControllerFamily._();

/// New support request (14.2): topic (the API's categories), an optional linked order from the
/// customer's orders, subject and message. No attachments: the API has no upload. A 422 for an
/// order that is not the customer's shows beside the order picker.
final class NewTicketControllerProvider
    extends $NotifierProvider<NewTicketController, NewTicketState> {
  /// New support request (14.2): topic (the API's categories), an optional linked order from the
  /// customer's orders, subject and message. No attachments: the API has no upload. A 422 for an
  /// order that is not the customer's shows beside the order picker.
  NewTicketControllerProvider._({
    required NewTicketControllerFamily super.from,
    required ({String? order, String? category}) super.argument,
  }) : super(
         retry: null,
         name: r'newTicketControllerProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$newTicketControllerHash();

  @override
  String toString() {
    return r'newTicketControllerProvider'
        ''
        '$argument';
  }

  @$internal
  @override
  NewTicketController create() => NewTicketController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(NewTicketState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<NewTicketState>(value),
    );
  }

  @override
  bool operator ==(Object other) {
    return other is NewTicketControllerProvider && other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$newTicketControllerHash() =>
    r'89f1f9a40cceb90451561009003475dd825b3b22';

/// New support request (14.2): topic (the API's categories), an optional linked order from the
/// customer's orders, subject and message. No attachments: the API has no upload. A 422 for an
/// order that is not the customer's shows beside the order picker.

final class NewTicketControllerFamily extends $Family
    with
        $ClassFamilyOverride<
          NewTicketController,
          NewTicketState,
          NewTicketState,
          NewTicketState,
          ({String? order, String? category})
        > {
  NewTicketControllerFamily._()
    : super(
        retry: null,
        name: r'newTicketControllerProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// New support request (14.2): topic (the API's categories), an optional linked order from the
  /// customer's orders, subject and message. No attachments: the API has no upload. A 422 for an
  /// order that is not the customer's shows beside the order picker.

  NewTicketControllerProvider call({String? order, String? category}) =>
      NewTicketControllerProvider._(
        argument: (order: order, category: category),
        from: this,
      );

  @override
  String toString() => r'newTicketControllerProvider';
}

/// New support request (14.2): topic (the API's categories), an optional linked order from the
/// customer's orders, subject and message. No attachments: the API has no upload. A 422 for an
/// order that is not the customer's shows beside the order picker.

abstract class _$NewTicketController extends $Notifier<NewTicketState> {
  late final _$args = ref.$arg as ({String? order, String? category});
  String? get order => _$args.order;
  String? get category => _$args.category;

  NewTicketState build({String? order, String? category});
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<NewTicketState, NewTicketState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<NewTicketState, NewTicketState>,
              NewTicketState,
              Object?,
              Object?
            >;
    return element.handleCreate(
      ref,
      () => build(order: _$args.order, category: _$args.category),
    );
  }
}

/// The customer's refund requests (15.2): `GET /account/refund-requests`, 20 per page.

@ProviderFor(RefundRequestsController)
final refundRequestsControllerProvider = RefundRequestsControllerProvider._();

/// The customer's refund requests (15.2): `GET /account/refund-requests`, 20 per page.
final class RefundRequestsControllerProvider
    extends
        $NotifierProvider<
          RefundRequestsController,
          PagedListState<RefundRequest>
        > {
  /// The customer's refund requests (15.2): `GET /account/refund-requests`, 20 per page.
  RefundRequestsControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'refundRequestsControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$refundRequestsControllerHash();

  @$internal
  @override
  RefundRequestsController create() => RefundRequestsController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(PagedListState<RefundRequest> value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<PagedListState<RefundRequest>>(
        value,
      ),
    );
  }
}

String _$refundRequestsControllerHash() =>
    r'4c36bacc4cb29701555495c68ee2d092bca23382';

/// The customer's refund requests (15.2): `GET /account/refund-requests`, 20 per page.

abstract class _$RefundRequestsController
    extends $Notifier<PagedListState<RefundRequest>> {
  PagedListState<RefundRequest> build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref =
        this.ref
            as $Ref<
              PagedListState<RefundRequest>,
              PagedListState<RefundRequest>
            >;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<
                PagedListState<RefundRequest>,
                PagedListState<RefundRequest>
              >,
              PagedListState<RefundRequest>,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// Request a refund (15.1) of [orderPublicId]: the reason (10 to 2000 characters) and an
/// optional linked ticket. The API refunds whole requests (staff choose the units), so there is
/// no unit choice. The server decides eligibility: `refund_request_open`,
/// `refund_not_allowed` (unpaid) and 422 for an order that is not the customer's.

@ProviderFor(RefundRequestFormController)
final refundRequestFormControllerProvider =
    RefundRequestFormControllerFamily._();

/// Request a refund (15.1) of [orderPublicId]: the reason (10 to 2000 characters) and an
/// optional linked ticket. The API refunds whole requests (staff choose the units), so there is
/// no unit choice. The server decides eligibility: `refund_request_open`,
/// `refund_not_allowed` (unpaid) and 422 for an order that is not the customer's.
final class RefundRequestFormControllerProvider
    extends
        $NotifierProvider<RefundRequestFormController, RefundRequestFormState> {
  /// Request a refund (15.1) of [orderPublicId]: the reason (10 to 2000 characters) and an
  /// optional linked ticket. The API refunds whole requests (staff choose the units), so there is
  /// no unit choice. The server decides eligibility: `refund_request_open`,
  /// `refund_not_allowed` (unpaid) and 422 for an order that is not the customer's.
  RefundRequestFormControllerProvider._({
    required RefundRequestFormControllerFamily super.from,
    required String super.argument,
  }) : super(
         retry: null,
         name: r'refundRequestFormControllerProvider',
         isAutoDispose: true,
         dependencies: null,
         $allTransitiveDependencies: null,
       );

  @override
  String debugGetCreateSourceHash() => _$refundRequestFormControllerHash();

  @override
  String toString() {
    return r'refundRequestFormControllerProvider'
        ''
        '($argument)';
  }

  @$internal
  @override
  RefundRequestFormController create() => RefundRequestFormController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(RefundRequestFormState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<RefundRequestFormState>(value),
    );
  }

  @override
  bool operator ==(Object other) {
    return other is RefundRequestFormControllerProvider &&
        other.argument == argument;
  }

  @override
  int get hashCode {
    return argument.hashCode;
  }
}

String _$refundRequestFormControllerHash() =>
    r'127b0b4f8e484ad9d65e41be88f52e3aba798719';

/// Request a refund (15.1) of [orderPublicId]: the reason (10 to 2000 characters) and an
/// optional linked ticket. The API refunds whole requests (staff choose the units), so there is
/// no unit choice. The server decides eligibility: `refund_request_open`,
/// `refund_not_allowed` (unpaid) and 422 for an order that is not the customer's.

final class RefundRequestFormControllerFamily extends $Family
    with
        $ClassFamilyOverride<
          RefundRequestFormController,
          RefundRequestFormState,
          RefundRequestFormState,
          RefundRequestFormState,
          String
        > {
  RefundRequestFormControllerFamily._()
    : super(
        retry: null,
        name: r'refundRequestFormControllerProvider',
        dependencies: null,
        $allTransitiveDependencies: null,
        isAutoDispose: true,
      );

  /// Request a refund (15.1) of [orderPublicId]: the reason (10 to 2000 characters) and an
  /// optional linked ticket. The API refunds whole requests (staff choose the units), so there is
  /// no unit choice. The server decides eligibility: `refund_request_open`,
  /// `refund_not_allowed` (unpaid) and 422 for an order that is not the customer's.

  RefundRequestFormControllerProvider call(String orderPublicId) =>
      RefundRequestFormControllerProvider._(
        argument: orderPublicId,
        from: this,
      );

  @override
  String toString() => r'refundRequestFormControllerProvider';
}

/// Request a refund (15.1) of [orderPublicId]: the reason (10 to 2000 characters) and an
/// optional linked ticket. The API refunds whole requests (staff choose the units), so there is
/// no unit choice. The server decides eligibility: `refund_request_open`,
/// `refund_not_allowed` (unpaid) and 422 for an order that is not the customer's.

abstract class _$RefundRequestFormController
    extends $Notifier<RefundRequestFormState> {
  late final _$args = ref.$arg as String;
  String get orderPublicId => _$args;

  RefundRequestFormState build(String orderPublicId);
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref =
        this.ref as $Ref<RefundRequestFormState, RefundRequestFormState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<RefundRequestFormState, RefundRequestFormState>,
              RefundRequestFormState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, () => build(_$args));
  }
}
