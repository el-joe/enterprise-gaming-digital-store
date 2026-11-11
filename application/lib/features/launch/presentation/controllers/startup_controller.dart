import 'package:application/core/config/app_version.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/security/device_integrity.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/app_config/domain/entities/app_config.dart';
import 'package:application/features/auth/auth_launch.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'startup_controller.g.dart';

/// Where the launch check is.
sealed class StartupState {
  const StartupState();
}

final class StartupChecking extends StartupState {
  const StartupChecking();
}

/// No connection (01.2): retry.
final class StartupOffline extends StartupState {
  const StartupOffline(this.failure);

  final Failure failure;
}

/// Any other failure of `GET /app/config`: retry, request id shown.
final class StartupFailed extends StartupState {
  const StartupFailed(this.failure);

  final Failure failure;
}

/// Config received; [next] is where the splash continues when the store is available.
final class StartupReady extends StartupState {
  const StartupReady(this.config, {required this.next});

  final AppConfig config;
  final String next;
}

/// The launch check (01.1 to 01.4): `GET /app/config` with this build's platform and version,
/// then force update (`update.required`), maintenance, or onward to onboarding or home. The guards
/// show the update and maintenance screens from [availabilityProvider].
@Riverpod(keepAlive: true)
class StartupController extends _$StartupController {
  @override
  StartupState build() => const StartupChecking();

  bool _running = false;

  Future<void> run({@visibleForTesting TargetPlatform? platform}) async {
    if (_running) {
      return;
    }
    _running = true;
    state = const StartupChecking();
    try {
      final platformName = appPlatformName(platform);
      final result = await ref
          .read(appConfigRepositoryProvider)
          .fetch(
            platform: platformName,
            version: platformName == null ? null : appVersion,
          );
      switch (result) {
        case Ok(value: final config):
          ref.read(latestAppConfigProvider.notifier).set(config);
          ref.read(availabilityProvider.notifier).update(_availability(config));
          await ref.read(authLaunchProvider.notifier).run();
          state = StartupReady(
            config,
            next: ref.read(preferencesStoreProvider).onboardingCompleted
                ? Routes.home.path
                : Routes.onboardingLanguage.path,
          );
        case Err(failure: final MaintenanceFailure failure):
          ref
              .read(availabilityProvider.notifier)
              .update(AppAvailability.maintenance);
          state = StartupFailed(failure);
        case Err(failure: NetworkFailure() || TimeoutFailure()):
          state = StartupOffline(result.failureOrNull!);
        case Err(:final failure):
          state = StartupFailed(failure);
      }
    } finally {
      _running = false;
    }
  }

  /// Force update: opens this app's store listing. False when there is none to open.
  Future<bool> openStoreListing() =>
      ref.read(nativeBridgeProvider).openStoreListing();

  /// Maintenance: opens `links.support` in the browser. False when none is configured.
  Future<bool> openSupport() async {
    final url = ref.read(latestAppConfigProvider)?.links.support;
    return url != null &&
        await ref.read(nativeBridgeProvider).openExternalUrl(url);
  }

  static AppAvailability _availability(AppConfig config) {
    if (config.update?.required ?? false) {
      return AppAvailability.updateRequired;
    }
    if (config.maintenanceEnabled) {
      return AppAvailability.maintenance;
    }
    return AppAvailability.available;
  }
}

/// The rooted or jailbroken device sheet, shown once per install (owner decision 5).
@Riverpod(keepAlive: true)
class IntegrityWarningController extends _$IntegrityWarningController {
  @override
  void build() {}

  Future<bool> shouldWarn() async {
    if (ref.read(preferencesStoreProvider).integrityWarningShown) {
      return false;
    }
    return await ref.read(deviceIntegrityProvider.future) ==
        DeviceIntegrity.compromised;
  }

  Future<void> acknowledged() =>
      ref.read(preferencesStoreProvider).setIntegrityWarningShown();
}
