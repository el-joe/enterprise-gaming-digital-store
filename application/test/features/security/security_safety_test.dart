import 'dart:io';

import 'package:application/core/network/interceptors/redacting_log_interceptor.dart';
import 'package:application/core/storage/secure_store.dart';
import 'package:application/features/account/presentation/controllers/change_password_controller.dart';
import 'package:application/features/security/data/datasources/security_remote_data_source.dart';
import 'package:application/features/security/presentation/controllers/two_factor_controllers.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import '../../helpers/pump_app.dart';
import '../../helpers/repo_harness.dart';
import '../../helpers/security_fakes.dart';

/// M-A16: passwords, two-factor secrets and recovery codes are never logged or stored.
void main() {
  List<File> sources(String directory) => Directory(directory)
      .listSync(recursive: true)
      .whereType<File>()
      .where(
        (f) =>
            f.path.endsWith('.dart') &&
            !f.path.endsWith('.g.dart') &&
            !f.path.endsWith('.freezed.dart'),
      )
      .toList();

  final screens = [
    ...sources('lib/features/security'),
    File(
      'lib/features/account/presentation/controllers/change_password_controller.dart',
    ),
    File('lib/features/account/presentation/pages/change_password_page.dart'),
  ];

  test('nothing in the security screens logs, stores, caches or reports', () {
    expect(screens.length, greaterThan(5));
    for (final file in screens) {
      final source = file.readAsStringSync();
      for (final call in ['print', 'debugPrint', 'log']) {
        expect(
          source,
          isNot(contains(RegExp('(^|[^A-Za-z_])$call\\('))),
          reason: '${file.path} must not call $call',
        );
      }
      for (final banned in [
        'developer',
        'secure_store',
        'preferences_store',
        'shared_preferences',
        'flutter_secure_storage',
        'analytics',
        'crashlytics',
        'sentry',
        'path_provider',
        'dart:io',
        'Clipboard.setData',
      ]) {
        expect(
          source,
          isNot(contains(banned)),
          reason: '${file.path} must not use $banned',
        );
      }
    }
  });

  test('the enrolment controller is auto-disposed and the providers are not kept alive', () {
    final controller = File(
      'lib/features/security/presentation/controllers/two_factor_controllers.g.dart',
    ).readAsStringSync();
    expect(
      RegExp('isAutoDispose: true').allMatches(controller).length,
      greaterThanOrEqualTo(2),
    );
    expect(controller, isNot(contains('isAutoDispose: false')));
    expect(
      File('lib/features/security/security_providers.dart').readAsStringSync(),
      isNot(contains('keepAlive')),
    );
  });

  test(
    'the enrolment and confirmation are logged without the key or the codes',
    () async {
      final lines = <String>[];
      final h = Harness(
        stored: {
          SecureStore.accessTokenKey: 'a',
          SecureStore.refreshTokenKey: 'r',
        },
      );
      h.dio.interceptors.add(RedactingLogInterceptor(log: lines.add));
      final remote = SecurityRemoteDataSource(h.dio);

      h.serveFixture('security/two_factor_enable_ok');
      final enrollment = await remote.enable('fixture-password');
      expect(enrollment.secret, 'fixture-secret');
      h.serveFixture('security/two_factor_confirm_ok');
      final codes = await remote.confirm('123456');
      expect(codes.codes, hasLength(8));

      final log = lines.join('\n');
      expect(lines, isNotEmpty);
      expect(log, isNot(contains('fixture-secret')));
      expect(log, isNot(contains('fixture-recovery-code')));
      expect(log, isNot(contains('fixture-password')));
      expect('$enrollment', isNot(contains('fixture-secret')));
      expect('$codes', isNot(contains('fixture-recovery-code')));
    },
  );

  test(
    'a full enrolment and a password change write nothing to storage',
    () async {
      final h = buildHarness(signedIn: true);
      addTearDown(h.dispose);
      final secureBefore = Map.of(h.secure);
      final prefsBefore = Map.of(h.prefs.values);

      final setup = h.container.listen(
        twoFactorSetupControllerProvider,
        (_, _) {},
      );
      final controller = h.read(twoFactorSetupControllerProvider.notifier);
      await controller.start('canary-password-0000');
      await controller.copySecret();
      await controller.confirm('123456');
      await controller.copyRecoveryCodes();
      controller.finish();
      setup.close();

      when(
        () => h.account.changePassword(
          currentPassword: any(named: 'currentPassword'),
          password: any(named: 'password'),
          passwordConfirmation: any(named: 'passwordConfirmation'),
        ),
      ).thenAnswer((_) async => throw StateError('not called'));
      final password = h.container.listen(
        changePasswordControllerProvider,
        (_, _) {},
      );
      await h
          .read(changePasswordControllerProvider.notifier)
          .submit(
            currentPassword: 'canary-password-0000',
            password: 'canary-new-0000',
            confirmation: 'canary-other-0000',
          );
      password.close();

      expect(h.secure, secureBefore);
      expect(h.prefs.values, prefsBefore);
      final everything = [
        ...h.secure.values,
        ...h.prefs.values.values.map((v) => '$v'),
      ].join('\n');
      for (final canary in [
        canarySecret,
        ...canaryRecoveryCodes,
        'canary-password-0000',
      ]) {
        expect(everything, isNot(contains(canary)));
      }
    },
  );
}
