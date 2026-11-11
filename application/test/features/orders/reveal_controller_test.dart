import 'dart:async';

import 'package:application/core/network/dio_client.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/security/clipboard_guard.dart';
import 'package:application/core/security/device_integrity.dart';
import 'package:application/core/security/security_channel.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/code_reveal/data/datasources/code_reveal_remote_data_source.dart';
import 'package:application/features/code_reveal/presentation/controllers/reveal_controller.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/checkout_fakes.dart';
import '../../helpers/fake_http_adapter.dart';
import '../../helpers/fixtures.dart';
import '../../helpers/pump_app.dart';
import '../../helpers/reveal_fakes.dart';

const _prompt = BiometricPromptText(title: 'Confirm', cancel: 'Cancel');

Future<void> settle() async {
  for (var i = 0; i < 100; i++) {
    await Future<void>.delayed(Duration.zero);
  }
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  final provider = revealControllerProvider(orderId, fixtureUnitId, false);

  ({TestHarness h, RevealController c, RevealScreenState Function() s}) open({
    bool biometricEnabled = false,
    String integrity = 'ok',
    void Function(TestHarness h)? arrange,
  }) {
    final h = buildHarness(
      signedIn: true,
      biometricEnabled: biometricEnabled,
      integrity: integrity,
    );
    addTearDown(h.dispose);
    arrange?.call(h);
    final sub = h.container.listen(provider, (_, _) {});
    addTearDown(sub.close);
    return (h: h, c: h.read(provider.notifier), s: () => h.read(provider));
  }

  group('loading the unit', () {
    test('finds the unit in the order detail; never a code', () async {
      final r = open();
      await settle();
      final info = r.s().unit.valueOrNull!;
      expect(info.productName, 'Orbit Card - 10 USD');
      expect(info.index, 1);
      expect(info.count, 1);
      expect(info.maskedCode, '****0000');
      expect(r.s().phase, RevealPhase.masked);
      expect(r.s().code, isNull);
    });

    test('an unknown unit is not found', () async {
      final h = buildHarness(signedIn: true);
      addTearDown(h.dispose);
      final other = revealControllerProvider(orderId, 'nope', false);
      final sub = h.container.listen(other, (_, _) {});
      addTearDown(sub.close);
      await settle();
      expect(h.read(other).unit, isA<Failed<RevealUnitInfo>>());
    });
  });

  group('reveal and re-auth', () {
    test('masked by default; a reveal sends X-Device-Integrity and shows the '
        'code for 60 s', () async {
      final r = open(integrity: 'compromised');
      await settle();
      await r.c.reveal(_prompt);
      expect(r.s().phase, RevealPhase.revealed);
      expect(r.s().code!.code, canaryCode);
      expect(r.s().hideAt, r.h.now.value.add(const Duration(seconds: 60)));
      expect(r.h.reveal.integrity, ['compromised']);
      expect(r.s().integrity, DeviceIntegrity.compromised);
    });

    test(
      '423 -> biometric success: confirm-biometric, then one retry',
      () async {
        final r = open(
          biometricEnabled: true,
          arrange: (h) {
            h.keys.hasKey = true;
            h.reveal.reveals = [
              const Err(RecentAuthRequiredFailure()),
              Ok(canaryReveal()),
            ];
          },
        );
        await settle();
        await r.c.reveal(_prompt);
        expect(r.h.biometricServer.confirmed, hasLength(1));
        expect(r.h.reveal.calls, ['reveal', 'reveal']);
        expect(r.h.reveal.integrity, ['ok', 'ok']);
        expect(r.s().reauth, isNull);
        expect(r.s().phase, RevealPhase.revealed);
      },
    );

    test('423 -> biometric fails -> password sheet -> confirm-password -> '
        'retry', () async {
      final r = open(
        biometricEnabled: true,
        arrange: (h) {
          h.keys.hasKey = true;
          h.keys.signResults.add(
            const BiometricFailed(BiometricIssue.lockedOut),
          );
          h.reveal.reveals = [
            const Err(RecentAuthRequiredFailure()),
            Ok(canaryReveal()),
          ];
        },
      );
      await settle();
      final revealing = r.c.reveal(_prompt);
      await settle();
      expect(r.s().reauth, isNotNull, reason: 'the password sheet is asked');
      expect(r.h.biometricServer.confirmed, isEmpty);
      await r.c.submitPassword('correct horse');
      await revealing;
      expect(r.h.reveal.calls, ['reveal', 'confirm:correct horse', 'reveal']);
      expect(r.s().reauth, isNull);
      expect(r.s().phase, RevealPhase.revealed);
    });

    test(
      'without biometric unlock the password sheet comes straight away',
      () async {
        final r = open(
          arrange: (h) => h.reveal.reveals = [
            const Err(RecentAuthRequiredFailure()),
            Ok(canaryReveal()),
          ],
        );
        await settle();
        unawaited(r.c.reveal(_prompt));
        await settle();
        expect(r.s().reauth, isNotNull);
        expect(r.h.keys.signedPayloads, isEmpty);
      },
    );

    test(
      'wrong password: the sheet stays open with the error; no reveal',
      () async {
        final r = open(
          arrange: (h) {
            h.reveal.reveals = [
              const Err(RecentAuthRequiredFailure()),
              Ok(canaryReveal()),
            ];
            h.reveal.confirmations = [
              const Err(
                ValidationFailure({
                  'password': ['The password is incorrect.'],
                }),
              ),
              const Ok(null),
            ];
          },
        );
        await settle();
        final revealing = r.c.reveal(_prompt);
        await settle();
        await r.c.submitPassword('wrong');
        final reauth = r.s().reauth!;
        expect(
          (reauth.failure! as ValidationFailure).firstErrorFor('password'),
          'The password is incorrect.',
        );
        expect(r.h.reveal.count('reveal'), 1);

        await r.c.submitPassword('right');
        await revealing;
        expect(r.h.reveal.calls, [
          'reveal',
          'confirm:wrong',
          'confirm:right',
          'reveal',
        ]);
        expect(r.s().phase, RevealPhase.revealed);
      },
    );

    test('closing the sheet goes back to masked without a retry', () async {
      final r = open(
        arrange: (h) =>
            h.reveal.reveals = [const Err(RecentAuthRequiredFailure())],
      );
      await settle();
      final revealing = r.c.reveal(_prompt);
      await settle();
      r.c.cancelReauth();
      await revealing;
      expect(r.s().phase, RevealPhase.masked);
      expect(r.s().failure, isNull);
      expect(r.h.reveal.calls, ['reveal']);
    });

    test('429: rate limited with the retry time; never retried', () async {
      final r = open(
        arrange: (h) => h.reveal.reveals = [
          const Err(RateLimitedFailure(retryAfter: Duration(minutes: 42))),
        ],
      );
      await settle();
      await r.c.reveal(_prompt);
      expect(r.s().phase, RevealPhase.rateLimited);
      expect(r.s().retryAt, r.h.now.value.add(const Duration(minutes: 42)));
      await settle();
      expect(r.h.reveal.calls, ['reveal']);

      // A tap while the window is open sends nothing.
      await r.c.reveal(_prompt);
      expect(r.h.reveal.calls, ['reveal']);

      r.h.now.value = r.h.now.value.add(const Duration(minutes: 43));
      r.c.rateLimitOver();
      expect(r.s().phase, RevealPhase.masked);
    });

    test('403 reveal_denied and 404 have their own states', () async {
      final r = open(
        arrange: (h) => h.reveal.reveals = [
          const Err(ForbiddenFailure('reveal_denied')),
          const Err(NotFoundFailure()),
        ],
      );
      await settle();
      await r.c.reveal(_prompt);
      expect(r.s().phase, RevealPhase.denied);
      await r.c.reveal(_prompt);
      expect(r.s().phase, RevealPhase.notFound);
    });

    test('an unverified email is flagged before any reveal', () async {
      final h = buildHarness(signedIn: true);
      addTearDown(h.dispose);
      h.read(emailVerificationProvider.notifier).update(verified: false);
      final sub = h.container.listen(provider, (_, _) {});
      addTearDown(sub.close);
      expect(h.read(provider).emailUnverified, isTrue);
    });
  });

  group('hiding', () {
    testWidgets('auto-hides at 60 s on the clock and drops the code', (
      tester,
    ) async {
      final r = open();
      await tester.runAsync(settle);
      await r.c.reveal(_prompt);
      expect(r.s().isRevealed, isTrue);
      await tester.pump(const Duration(seconds: 59));
      expect(r.s().isRevealed, isTrue);
      await tester.pump(const Duration(seconds: 1));
      expect(r.s().isRevealed, isFalse);
      expect(r.s().code, isNull);
      expect(r.s().lastHide, RevealHideReason.timeout);
      expect('${r.s()}', isNot(contains(canaryCode)));
    });

    test('hides on lifecycle pause and covers while inactive', () async {
      final r = open();
      await settle();
      await r.c.reveal(_prompt);

      r.c.lifecycleChanged(AppLifecycleState.inactive);
      expect(r.s().obscured, isTrue);
      expect(r.s().isRevealed, isTrue, reason: 'inactive only covers');

      r.c.lifecycleChanged(AppLifecycleState.hidden);
      r.c.lifecycleChanged(AppLifecycleState.paused);
      expect(r.s().isRevealed, isFalse);
      expect(r.s().code, isNull);
      expect(r.s().lastHide, RevealHideReason.background);

      r.c.lifecycleChanged(AppLifecycleState.resumed);
      expect(r.s().obscured, isFalse);
      expect(r.s().phase, RevealPhase.masked);
    });

    test(
      'iOS capture hides the code and blocks reveal until it stops',
      () async {
        final r = open();
        await settle();
        await r.c.reveal(_prompt);
        r.h.security.capture.add(true);
        await settle();
        expect(r.s().captured, isTrue);
        expect(r.s().isRevealed, isFalse);
        expect(r.s().lastHide, RevealHideReason.capture);

        await r.c.reveal(_prompt);
        expect(r.h.reveal.count('reveal'), 1);

        r.h.security.capture.add(false);
        await settle();
        expect(r.s().captured, isFalse);
      },
    );

    test('Hide drops the code at once', () async {
      final r = open();
      await settle();
      await r.c.reveal(_prompt);
      r.c.hide();
      expect(r.s().code, isNull);
      expect(r.s().lastHide, RevealHideReason.customer);
    });
  });

  group('screen protection', () {
    test(
      'setSecure on while the controller lives, off once it is disposed',
      () async {
        final h = buildHarness(signedIn: true);
        addTearDown(h.dispose);
        final sub = h.container.listen(provider, (_, _) {});
        await settle();
        expect(h.security.secureCalls, [true]);
        sub.close();
        await settle();
        expect(h.read(provider.exists), isFalse);
        expect(h.security.secureCalls, [true, false]);
      },
    );
  });

  group('copy and links', () {
    test('copy hands the code to the clipboard guard', () async {
      final r = open();
      await settle();
      await r.c.copyCode();
      expect(r.h.clipboard.copies, 0, reason: 'nothing to copy while masked');
      await r.c.reveal(_prompt);
      await r.c.copyCode();
      expect(r.h.clipboard.copies, 1);
      expect(r.s().copyCount, 1);
    });

    testWidgets(
      'the clipboard is cleared 60 s after a copy (real guard, Android)',
      (tester) async {
        final channelCalls = <String>[];
        final messenger =
            TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
              ..setMockMethodCallHandler(securityChannel, (call) async {
                channelCalls.add(call.method);
                return true;
              });
        addTearDown(
          () => messenger.setMockMethodCallHandler(securityChannel, null),
        );
        final r = open(
          arrange: (h) => h.clipboard.delegate = ClipboardGuard(
            platform: () => TargetPlatform.android,
          ),
        );
        await tester.runAsync(settle);
        await r.c.reveal(_prompt);
        await r.c.copyCode();
        expect(channelCalls, ['copySensitive']);
        // Leaving the screen does not cancel the clear.
        r.c.hide();
        await tester.pump(const Duration(seconds: 60));
        expect(channelCalls, ['copySensitive', 'clearSensitive']);
      },
    );

    test('the redemption link opens only over https', () async {
      final r = open(
        arrange: (h) => h.reveal.reveals = [
          Ok(canaryReveal(redemptionUrl: Uri.parse('http://insecure.test/x'))),
          Ok(canaryReveal()),
        ],
      );
      await settle();
      await r.c.reveal(_prompt);
      await r.c.openRedemption();
      expect(r.h.native.opened, isEmpty);
      r.c.hide();
      await r.c.reveal(_prompt);
      await r.c.openRedemption();
      expect(r.h.native.opened, [
        Uri.parse('https://redeem.example.test/orbit'),
      ]);
    });
  });

  test('X-Device-Integrity reaches the wire on the reveal request', () async {
    final h = buildHarness(signedIn: true, integrity: 'compromised');
    addTearDown(h.dispose);
    final requests = <({String path, Map<String, dynamic> headers})>[];
    final reveal = ApiFixture.load('code_reveal/reveal_ok');
    h.read(apiClientProvider).httpClientAdapter = FakeHttpAdapter((
      options,
      _,
    ) async {
      requests.add((path: options.path, headers: options.headers));
      return reveal.response();
    });
    final source = CodeRevealRemoteDataSource(h.read(apiClientProvider));
    await source.revealAccount(
      orderId,
      fixtureUnitId,
      deviceIntegrity: DeviceIntegrity.compromised.headerValue,
    );
    await source.revealGuest(orderId, fixtureUnitId, deviceIntegrity: 'ok');
    expect(requests.map((r) => r.headers['X-Device-Integrity']), [
      'compromised',
      'ok',
    ]);
    expect(requests.first.headers['Cache-Control'], 'no-store');
  });
}
