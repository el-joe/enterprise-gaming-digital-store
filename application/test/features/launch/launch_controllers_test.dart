import 'package:application/core/config/app_version.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/auth/domain/entities/biometric.dart';
import 'package:application/features/auth/presentation/controllers/biometric_controllers.dart';
import 'package:application/features/auth/presentation/controllers/session_user.dart';
import 'package:application/features/launch/presentation/controllers/onboarding_controller.dart';
import 'package:application/features/launch/presentation/controllers/startup_controller.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../helpers/fakes.dart';
import '../../helpers/pump_app.dart';

void main() {
  late TestHarness h;

  tearDown(() => h.dispose());

  Future<StartupState> run() async {
    await h
        .read(startupControllerProvider.notifier)
        .run(platform: TargetPlatform.android);
    return h.read(startupControllerProvider);
  }

  group('StartupController', () {
    test('sends the platform and version and continues home', () async {
      h = buildHarness();
      final state = await run();
      expect(h.appConfig.calls.single, (
        platform: 'android',
        version: appVersion,
      ));
      expect(state, isA<StartupReady>());
      expect((state as StartupReady).next, Routes.home.path);
      expect(h.read(availabilityProvider), AppAvailability.available);
      expect(h.read(latestAppConfigProvider), isNotNull);
    });

    test('a first launch continues to onboarding', () async {
      h = buildHarness(onboarded: false);
      expect(
        (await run() as StartupReady).next,
        Routes.onboardingLanguage.path,
      );
    });

    test('update.required forces the update screen', () async {
      h = buildHarness(config: [Ok(fixtureAppConfig(updateRequired: true))]);
      await run();
      expect(h.read(availabilityProvider), AppAvailability.updateRequired);
    });

    test('maintenance from the config or a 503 shows maintenance', () async {
      h = buildHarness(
        config: [
          Ok(fixtureAppConfig(maintenance: true)),
          const Err(MaintenanceFailure()),
          Ok(fixtureAppConfig()),
        ],
      );
      await run();
      expect(h.read(availabilityProvider), AppAvailability.maintenance);
      expect(await run(), isA<StartupFailed>());
      expect(h.read(availabilityProvider), AppAvailability.maintenance);
      await run();
      expect(h.read(availabilityProvider), AppAvailability.available);
    });

    test(
      'no connection is offline; other failures keep their request id',
      () async {
        h = buildHarness(
          config: [
            const Err(NetworkFailure()),
            const Err(ServerFailure(statusCode: 500, requestId: 'req-1')),
          ],
        );
        expect(await run(), isA<StartupOffline>());
        final failed = await run();
        expect((failed as StartupFailed).failure.requestId, 'req-1');
      },
    );

    test(
      'a signed-in launch reloads the user and locks when unlock is on',
      () async {
        h = buildHarness(
          signedIn: true,
          biometricEnabled: true,
          biometricSupport: BiometricSupport.available,
        );
        await run();
        expect(h.auth.meCalls, 1);
        expect(h.read(currentUserProvider), testUser);
        expect(h.read(appLockProvider).locked, isTrue);
        // Already on: no offer.
        expect(h.read(biometricOfferDueProvider), isFalse);
      },
    );

    test(
      'a signed-out launch on a biometric device makes the offer due',
      () async {
        h = buildHarness(biometricSupport: BiometricSupport.available);
        await run();
        expect(h.read(biometricOfferDueProvider), isTrue);
        expect(h.read(appLockProvider).locked, isFalse);
      },
    );
  });

  group('IntegrityWarningController', () {
    test('warns once on a compromised device', () async {
      h = buildHarness(integrity: 'compromised');
      final controller = h.read(integrityWarningControllerProvider.notifier);
      expect(await controller.shouldWarn(), isTrue);
      await controller.acknowledged();
      expect(await controller.shouldWarn(), isFalse);
    });

    test('never warns on a clean or unknown device', () async {
      h = buildHarness(integrity: 'unknown');
      expect(
        await h.read(integrityWarningControllerProvider.notifier).shouldWarn(),
        isFalse,
      );
    });
  });

  group('OnboardingController', () {
    setUp(() {
      h = buildHarness(onboarded: false);
      h.read(latestAppConfigProvider.notifier).set(fixtureAppConfig());
      h.container.listen(onboardingControllerProvider, (_, _) {});
    });

    test('applies the language at once', () async {
      await h.read(onboardingControllerProvider.notifier).chooseLanguage('ar');
      expect(h.read(appLocaleProvider), const Locale('ar'));
      expect(h.prefs.values[PreferencesStore.localeKey], 'ar');
    });

    test('saves the chosen currency and completes onboarding', () async {
      final controller = h.read(onboardingControllerProvider.notifier);
      expect(h.read(onboardingControllerProvider).currencies, isNotEmpty);
      final code = h
          .read(onboardingControllerProvider)
          .currencies
          .first
          .info
          .code;
      controller.chooseCurrency(code);
      await controller.finish(saveCurrency: true);
      expect(h.read(displayCurrencyProvider), code);
      expect(h.prefs.values[PreferencesStore.onboardingCompletedKey], isTrue);
    });

    test('skip keeps the server currency', () async {
      final controller = h.read(onboardingControllerProvider.notifier)
        ..chooseCurrency('USD');
      await controller.finish(saveCurrency: false);
      expect(h.read(displayCurrencyProvider), isNull);
      expect(h.prefs.values[PreferencesStore.onboardingCompletedKey], isTrue);
    });
  });
}
