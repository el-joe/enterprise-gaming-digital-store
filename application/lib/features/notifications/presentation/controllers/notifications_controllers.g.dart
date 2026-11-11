// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'notifications_controllers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The inbox (16.1, 16.2): `GET /notifications`, newest first, 20 per page; every page's
/// `meta.unread_count` updates the bell badge ([UnreadNotifications]). Opening an unread
/// notification marks it read (`POST /notifications/{id}/read`, idempotent).

@ProviderFor(NotificationsInboxController)
final notificationsInboxControllerProvider =
    NotificationsInboxControllerProvider._();

/// The inbox (16.1, 16.2): `GET /notifications`, newest first, 20 per page; every page's
/// `meta.unread_count` updates the bell badge ([UnreadNotifications]). Opening an unread
/// notification marks it read (`POST /notifications/{id}/read`, idempotent).
final class NotificationsInboxControllerProvider
    extends
        $NotifierProvider<
          NotificationsInboxController,
          PagedListState<InboxNotification>
        > {
  /// The inbox (16.1, 16.2): `GET /notifications`, newest first, 20 per page; every page's
  /// `meta.unread_count` updates the bell badge ([UnreadNotifications]). Opening an unread
  /// notification marks it read (`POST /notifications/{id}/read`, idempotent).
  NotificationsInboxControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'notificationsInboxControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$notificationsInboxControllerHash();

  @$internal
  @override
  NotificationsInboxController create() => NotificationsInboxController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(PagedListState<InboxNotification> value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<PagedListState<InboxNotification>>(
        value,
      ),
    );
  }
}

String _$notificationsInboxControllerHash() =>
    r'f9cc778a15c1aa3ecd70fec713c32ca65025b8aa';

/// The inbox (16.1, 16.2): `GET /notifications`, newest first, 20 per page; every page's
/// `meta.unread_count` updates the bell badge ([UnreadNotifications]). Opening an unread
/// notification marks it read (`POST /notifications/{id}/read`, idempotent).

abstract class _$NotificationsInboxController
    extends $Notifier<PagedListState<InboxNotification>> {
  PagedListState<InboxNotification> build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref =
        this.ref
            as $Ref<
              PagedListState<InboxNotification>,
              PagedListState<InboxNotification>
            >;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<
                PagedListState<InboxNotification>,
                PagedListState<InboxNotification>
              >,
              PagedListState<InboxNotification>,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// "Mark all read" (16.1): `POST /notifications/read-all`. True while it runs; a failure is
/// returned to the page for a snackbar.

@ProviderFor(MarkAllRead)
final markAllReadProvider = MarkAllReadProvider._();

/// "Mark all read" (16.1): `POST /notifications/read-all`. True while it runs; a failure is
/// returned to the page for a snackbar.
final class MarkAllReadProvider extends $NotifierProvider<MarkAllRead, bool> {
  /// "Mark all read" (16.1): `POST /notifications/read-all`. True while it runs; a failure is
  /// returned to the page for a snackbar.
  MarkAllReadProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'markAllReadProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$markAllReadHash();

  @$internal
  @override
  MarkAllRead create() => MarkAllRead();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(bool value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<bool>(value),
    );
  }
}

String _$markAllReadHash() => r'a6c7683036d593fcc7d8ee781de0c3d304c6af90';

/// "Mark all read" (16.1): `POST /notifications/read-all`. True while it runs; a failure is
/// returned to the page for a snackbar.

abstract class _$MarkAllRead extends $Notifier<bool> {
  bool build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<bool, bool>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<bool, bool>,
              bool,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// Notification preferences (16.3): `GET /notification-preferences`, and every switch is saved
/// at once (`PATCH` with the one cell; the server answers with the full matrix). Mandatory
/// cells (`orders.mail`, `security.mail`) are on and cannot be switched off; a refused save
/// (422 `notification_preference_mandatory` or any failure) puts the switch back.

@ProviderFor(NotificationPreferencesController)
final notificationPreferencesControllerProvider =
    NotificationPreferencesControllerProvider._();

/// Notification preferences (16.3): `GET /notification-preferences`, and every switch is saved
/// at once (`PATCH` with the one cell; the server answers with the full matrix). Mandatory
/// cells (`orders.mail`, `security.mail`) are on and cannot be switched off; a refused save
/// (422 `notification_preference_mandatory` or any failure) puts the switch back.
final class NotificationPreferencesControllerProvider
    extends
        $NotifierProvider<NotificationPreferencesController, PreferencesState> {
  /// Notification preferences (16.3): `GET /notification-preferences`, and every switch is saved
  /// at once (`PATCH` with the one cell; the server answers with the full matrix). Mandatory
  /// cells (`orders.mail`, `security.mail`) are on and cannot be switched off; a refused save
  /// (422 `notification_preference_mandatory` or any failure) puts the switch back.
  NotificationPreferencesControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'notificationPreferencesControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() =>
      _$notificationPreferencesControllerHash();

  @$internal
  @override
  NotificationPreferencesController create() =>
      NotificationPreferencesController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(PreferencesState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<PreferencesState>(value),
    );
  }
}

String _$notificationPreferencesControllerHash() =>
    r'5e1a0600e0d2bb9f3cb91b837dc79427c1a3845d';

/// Notification preferences (16.3): `GET /notification-preferences`, and every switch is saved
/// at once (`PATCH` with the one cell; the server answers with the full matrix). Mandatory
/// cells (`orders.mail`, `security.mail`) are on and cannot be switched off; a refused save
/// (422 `notification_preference_mandatory` or any failure) puts the switch back.

abstract class _$NotificationPreferencesController
    extends $Notifier<PreferencesState> {
  PreferencesState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<PreferencesState, PreferencesState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<PreferencesState, PreferencesState>,
              PreferencesState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
