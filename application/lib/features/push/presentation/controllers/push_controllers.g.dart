// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'push_controllers.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// The platform name sent with the token.

@ProviderFor(pushPlatform)
final pushPlatformProvider = PushPlatformProvider._();

/// The platform name sent with the token.

final class PushPlatformProvider
    extends $FunctionalProvider<String, String, String>
    with $Provider<String> {
  /// The platform name sent with the token.
  PushPlatformProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'pushPlatformProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$pushPlatformHash();

  @$internal
  @override
  $ProviderElement<String> $createElement($ProviderPointer pointer) =>
      $ProviderElement(pointer);

  @override
  String create(Ref ref) {
    return pushPlatform(ref);
  }

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(String value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<String>(value),
    );
  }
}

String _$pushPlatformHash() => r'bc323ac7033206ee67ea1e2fdcf60a66eff1ff1a';

/// Push notifications (M-A18, owner decision 9, API_DOCUMENTATION.md 1h). Kept alive by the app.
///
/// - Starts the push service after launch. It never asks for permission on launch: the system
///   prompt follows the explainer after the first paid order ([PushPrompt]) or the "Allow" button
///   in the notification settings.
/// - Signed in with permission, registers this device's token, and again when FCM rotates it.
///   Sign-out removes it on the server first, then locally ([unregister]).
/// - A message in the foreground refreshes the unread badge and the inbox and is shown in the app
///   ([ForegroundPush]); a tapped notification (background or cold start) opens its order or
///   ticket through the app-link queue ([AppLinkListener.open]).

@ProviderFor(PushNotifications)
final pushNotificationsProvider = PushNotificationsProvider._();

/// Push notifications (M-A18, owner decision 9, API_DOCUMENTATION.md 1h). Kept alive by the app.
///
/// - Starts the push service after launch. It never asks for permission on launch: the system
///   prompt follows the explainer after the first paid order ([PushPrompt]) or the "Allow" button
///   in the notification settings.
/// - Signed in with permission, registers this device's token, and again when FCM rotates it.
///   Sign-out removes it on the server first, then locally ([unregister]).
/// - A message in the foreground refreshes the unread badge and the inbox and is shown in the app
///   ([ForegroundPush]); a tapped notification (background or cold start) opens its order or
///   ticket through the app-link queue ([AppLinkListener.open]).
final class PushNotificationsProvider
    extends $NotifierProvider<PushNotifications, PushState> {
  /// Push notifications (M-A18, owner decision 9, API_DOCUMENTATION.md 1h). Kept alive by the app.
  ///
  /// - Starts the push service after launch. It never asks for permission on launch: the system
  ///   prompt follows the explainer after the first paid order ([PushPrompt]) or the "Allow" button
  ///   in the notification settings.
  /// - Signed in with permission, registers this device's token, and again when FCM rotates it.
  ///   Sign-out removes it on the server first, then locally ([unregister]).
  /// - A message in the foreground refreshes the unread badge and the inbox and is shown in the app
  ///   ([ForegroundPush]); a tapped notification (background or cold start) opens its order or
  ///   ticket through the app-link queue ([AppLinkListener.open]).
  PushNotificationsProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'pushNotificationsProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$pushNotificationsHash();

  @$internal
  @override
  PushNotifications create() => PushNotifications();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(PushState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<PushState>(value),
    );
  }
}

String _$pushNotificationsHash() => r'e8e6794587e3df150e5ac51b178abdcb6a0afbc3';

/// Push notifications (M-A18, owner decision 9, API_DOCUMENTATION.md 1h). Kept alive by the app.
///
/// - Starts the push service after launch. It never asks for permission on launch: the system
///   prompt follows the explainer after the first paid order ([PushPrompt]) or the "Allow" button
///   in the notification settings.
/// - Signed in with permission, registers this device's token, and again when FCM rotates it.
///   Sign-out removes it on the server first, then locally ([unregister]).
/// - A message in the foreground refreshes the unread badge and the inbox and is shown in the app
///   ([ForegroundPush]); a tapped notification (background or cold start) opens its order or
///   ticket through the app-link queue ([AppLinkListener.open]).

abstract class _$PushNotifications extends $Notifier<PushState> {
  PushState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<PushState, PushState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<PushState, PushState>,
              PushState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

@ProviderFor(ForegroundPush)
final foregroundPushProvider = ForegroundPushProvider._();

final class ForegroundPushProvider
    extends $NotifierProvider<ForegroundPush, ForegroundPushEvent?> {
  ForegroundPushProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'foregroundPushProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$foregroundPushHash();

  @$internal
  @override
  ForegroundPush create() => ForegroundPush();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(ForegroundPushEvent? value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<ForegroundPushEvent?>(value),
    );
  }
}

String _$foregroundPushHash() => r'9fe452922edccfc004c842f94bf545141dcd0bbf';

abstract class _$ForegroundPush extends $Notifier<ForegroundPushEvent?> {
  ForegroundPushEvent? build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<ForegroundPushEvent?, ForegroundPushEvent?>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<ForegroundPushEvent?, ForegroundPushEvent?>,
              ForegroundPushEvent?,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}

/// The push explainer after the first paid order (M-A18): offered once per installation, only when
/// push runs in this build and the system has not been asked yet. Never on launch.

@ProviderFor(PushPrompt)
final pushPromptProvider = PushPromptProvider._();

/// The push explainer after the first paid order (M-A18): offered once per installation, only when
/// push runs in this build and the system has not been asked yet. Never on launch.
final class PushPromptProvider extends $NotifierProvider<PushPrompt, void> {
  /// The push explainer after the first paid order (M-A18): offered once per installation, only when
  /// push runs in this build and the system has not been asked yet. Never on launch.
  PushPromptProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'pushPromptProvider',
        isAutoDispose: false,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$pushPromptHash();

  @$internal
  @override
  PushPrompt create() => PushPrompt();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(void value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<void>(value),
    );
  }
}

String _$pushPromptHash() => r'cd24bcca7839cc6782d567186889c6cd4da2c1a3';

/// The push explainer after the first paid order (M-A18): offered once per installation, only when
/// push runs in this build and the system has not been asked yet. Never on launch.

abstract class _$PushPrompt extends $Notifier<void> {
  void build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<void, void>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<void, void>,
              void,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
