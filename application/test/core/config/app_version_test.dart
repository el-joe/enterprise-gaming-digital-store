import 'dart:io';

import 'package:application/core/config/app_version.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('appVersion is the pubspec build name (sent to GET /app/config)', () {
    final pubspec = File('pubspec.yaml').readAsLinesSync();
    final line = pubspec.firstWhere((line) => line.startsWith('version:'));
    final buildName = line.split(':').last.trim().split('+').first;
    expect(appVersion, buildName);
  });

  test('platform names of the app config query', () {
    expect(appPlatformName(TargetPlatform.android), 'android');
    expect(appPlatformName(TargetPlatform.iOS), 'ios');
    expect(appPlatformName(TargetPlatform.linux), isNull);
  });
}
