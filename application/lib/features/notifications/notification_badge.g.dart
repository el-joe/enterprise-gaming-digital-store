// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'notification_badge.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The notifications feature's public entry for the home header and the Account hub: the
/// number of unread inbox notifications (`GET /notifications/unread-count`), read when the
/// customer is signed in and kept current by the inbox (its `meta.unread_count`, mark read and
/// mark all read). Signed out it is 0. A failed read keeps the last count. Push (M-A18) is not
/// part of it: new notifications show after the next read.

@ProviderFor(UnreadNotifications)
final unreadNotificationsProvider = UnreadNotificationsProvider._();

/// The notifications feature's public entry for the home header and the Account hub: the
/// number of unread inbox notifications (`GET /notifications/unread-count`), read when the
/// customer is signed in and kept current by the inbox (its `meta.unread_count`, mark read and
/// mark all read). Signed out it is 0. A failed read keeps the last count. Push (M-A18) is not
/// part of it: new notifications show after the next read.
final class UnreadNotificationsProvider
    extends $NotifierProvider<UnreadNotifications, int> {
  /// The notifications feature's public entry for the home header and the Account hub: the
  /// number of unread inbox notifications (`GET /notifications/unread-count`), read when the
  /// customer is signed in and kept current by the inbox (its `meta.unread_count`, mark read and
  /// mark all read). Signed out it is 0. A failed read keeps the last count. Push (M-A18) is not
  /// part of it: new notifications show after the next read.
  UnreadNotificationsProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'unreadNotificationsProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$unreadNotificationsHash();

  @$internal
  @override
  UnreadNotifications create() => UnreadNotifications();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(int value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<int>(value),
    );
  }
}

String _$unreadNotificationsHash() =>
    r'fd0d9b055b5ff36d57f8d07c2a7d63696095993a';

/// The notifications feature's public entry for the home header and the Account hub: the
/// number of unread inbox notifications (`GET /notifications/unread-count`), read when the
/// customer is signed in and kept current by the inbox (its `meta.unread_count`, mark read and
/// mark all read). Signed out it is 0. A failed read keeps the last count. Push (M-A18) is not
/// part of it: new notifications show after the next read.

abstract class _$UnreadNotifications extends $Notifier<int> {
  int build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<int, int>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<int, int>,
              int,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
