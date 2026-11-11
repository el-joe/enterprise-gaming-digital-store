import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/auth/presentation/controllers/sign_out_controller.dart';
import 'package:application/features/notifications/notification_badge.dart';
import 'package:application/features/push/domain/entities/push.dart';
import 'package:application/features/push/presentation/controllers/push_controllers.dart';
import 'package:flutter_riverpod/misc.dart' show ProviderListenable;
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/pump_app.dart';
import '../../helpers/push_fakes.dart';

Future<void> settle() async {
  for (var i = 0; i < 100; i++) {
    await Future<void>.delayed(Duration.zero);
  }
}

void keep<T>(TestHarness h, ProviderListenable<T> provider) {
  final sub = h.container.listen(provider, (_, _) {});
  addTearDown(sub.close);
}

/// M-A18: push token lifecycle, the permission prompt after the first purchase, and foreground
/// messages, on the fakes (no Firebase).
void main() {
  TestHarness harness({
    bool signedIn = true,
    bool available = true,
    PushPermission permission = PushPermission.granted,
    Map<String, Object> preferences = const {},
  }) {
    final h = buildHarness(signedIn: signedIn, preferences: preferences);
    addTearDown(h.dispose);
    h.push
      ..available = available
      ..permissionValue = permission;
    return h;
  }

  group('token lifecycle', () {
    test(
      'signed in with permission, registers the token with the platform',
      () async {
        final h = harness();
        keep(h, pushNotificationsProvider);
        await settle();

        expect(h.read(pushNotificationsProvider).granted, isTrue);
        expect(h.pushTokens.registered, [
          (token: 'fcm-token-1', platform: 'android'),
        ]);
      },
    );

    test(
      'a rotated token is registered again; the same token is not',
      () async {
        final h = harness();
        keep(h, pushNotificationsProvider);
        await settle();

        h.push.refreshes.add('fcm-token-1');
        await settle();
        h.push.refreshes.add('fcm-token-2');
        await settle();

        expect(h.pushTokens.registered.map((call) => call.token), [
          'fcm-token-1',
          'fcm-token-2',
        ]);
      },
    );

    test('a failed registration is tried again on the next refresh', () async {
      final h = harness();
      h.pushTokens.registerResult = pushRegistrationOffline;
      keep(h, pushNotificationsProvider);
      await settle();
      h.pushTokens.registerResult = const Ok(null);
      h.push.refreshes.add('fcm-token-1');
      await settle();

      expect(h.pushTokens.registered.map((call) => call.token), [
        'fcm-token-1',
        'fcm-token-1',
      ]);
    });

    test('nothing is registered without permission, signed out, or without Firebase', () async {
      for (final h in [
        harness(permission: PushPermission.notDetermined),
        harness(permission: PushPermission.denied),
        harness(signedIn: false),
        harness(available: false),
      ]) {
        keep(h, pushNotificationsProvider);
        await settle();
        expect(h.pushTokens.registered, isEmpty);
        expect(h.push.permissionRequests, 0, reason: 'never asks on launch');
      }
    });

    test('signing in later registers the token', () async {
      final h = harness(signedIn: false);
      keep(h, pushNotificationsProvider);
      await settle();
      expect(h.pushTokens.registered, isEmpty);

      h.read(sessionProvider.notifier).markSignedIn();
      await settle();

      expect(h.pushTokens.registered.single.token, 'fcm-token-1');
    });

    test(
      'sign-out removes the token on the server first, then on the device',
      () async {
        final h = harness();
        keep(h, pushNotificationsProvider);
        keep(h, signOutControllerProvider);
        await settle();

        await h.read(signOutControllerProvider.notifier).signOut();

        expect(h.pushTokens.unregisterCalls, 1);
        expect(h.push.deleteTokenCalls, 1);
        expect(h.auth.logoutCalls, 1);
      },
    );

    test('sign out everywhere removes it too', () async {
      final h = harness();
      keep(h, pushNotificationsProvider);
      keep(h, signOutControllerProvider);
      await settle();

      await h.read(signOutControllerProvider.notifier).signOutEverywhere();

      expect(h.pushTokens.unregisterCalls, 1);
      expect(h.auth.logoutAllCalls, 1);
    });

    test('without Firebase, sign-out makes no push call', () async {
      final h = harness(available: false);
      keep(h, pushNotificationsProvider);
      keep(h, signOutControllerProvider);
      await settle();

      await h.read(signOutControllerProvider.notifier).signOut();

      expect(h.pushTokens.unregisterCalls, 0);
      expect(h.push.deleteTokenCalls, 0);
      expect(h.auth.logoutCalls, 1);
    });
  });

  group('permission prompt after the first purchase', () {
    test(
      'due once when the system has not been asked, then never again',
      () async {
        final h = harness(permission: PushPermission.notDetermined);
        keep(h, pushPromptProvider);
        final prompt = h.read(pushPromptProvider.notifier);

        expect(await prompt.dueAfterPurchase(), isTrue);
        expect(h.prefs.values[PreferencesStore.pushPromptShownKey], isTrue);
        expect(await prompt.dueAfterPurchase(), isFalse);
        expect(
          h.push.permissionRequests,
          0,
          reason: 'only the explainer decides to ask',
        );
      },
    );

    test('accepting shows the system prompt and registers the token', () async {
      final h = harness(permission: PushPermission.notDetermined);
      keep(h, pushPromptProvider);
      keep(h, pushNotificationsProvider);
      final prompt = h.read(pushPromptProvider.notifier);
      await prompt.dueAfterPurchase();

      expect(await prompt.accept(), PushPermission.granted);
      await settle();

      expect(h.push.permissionRequests, 1);
      expect(h.pushTokens.registered.single.token, 'fcm-token-1');
    });

    test(
      'not due when already answered, without Firebase, or once shown',
      () async {
        for (final h in [
          harness(),
          harness(permission: PushPermission.denied),
          harness(available: false, permission: PushPermission.notDetermined),
          harness(
            permission: PushPermission.notDetermined,
            preferences: {PreferencesStore.pushPromptShownKey: true},
          ),
        ]) {
          keep(h, pushPromptProvider);
          expect(
            await h.read(pushPromptProvider.notifier).dueAfterPurchase(),
            isFalse,
          );
        }
      },
    );
  });

  group('foreground messages', () {
    test('refresh the unread badge and become a foreground event', () async {
      final h = harness();
      keep(h, pushNotificationsProvider);
      keep(h, unreadNotificationsProvider);
      keep(h, foregroundPushProvider);
      await settle();
      final before = h.notifications.unreadCalls;
      h.notifications.unreadResult = const Ok(3);

      h.push.foreground.add(orderPush('01JF0000000000000000000001'));
      await settle();

      expect(h.notifications.unreadCalls, before + 1);
      expect(h.read(unreadNotificationsProvider), 3);
      expect(h.read(foregroundPushProvider)?.payload.type, 'order_delivered');
    });

    test('two equal messages are two events', () async {
      final h = harness();
      keep(h, pushNotificationsProvider);
      keep(h, foregroundPushProvider);
      await settle();
      final push = orderPush('01JF0000000000000000000001');

      h.push.foreground.add(push);
      await settle();
      final first = h.read(foregroundPushProvider);
      h.push.foreground.add(push);
      await settle();

      expect(h.read(foregroundPushProvider), isNot(first));
    });
  });
}
