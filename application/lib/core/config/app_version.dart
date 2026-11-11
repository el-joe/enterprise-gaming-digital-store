import 'package:flutter/foundation.dart';

/// The installed version sent to `GET /app/config` for the force-update verdict (SemVer, the
/// pubspec build name without the build number). `test/core/config/app_version_test.dart` keeps
/// it equal to `pubspec.yaml`, so a release bump that forgets it fails CI.
const appVersion = '1.0.0';

/// `android` or `ios` for `GET /app/config`; null on other platforms (tests, desktop).
String? appPlatformName([TargetPlatform? platform]) =>
    switch (platform ?? defaultTargetPlatform) {
      TargetPlatform.android => 'android',
      TargetPlatform.iOS => 'ios',
      _ => null,
    };
