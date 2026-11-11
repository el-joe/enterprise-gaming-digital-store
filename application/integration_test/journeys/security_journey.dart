import 'package:application/core/network/result.dart';
import 'package:application/core/router/routes.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../test/helpers/pump_app.dart';
import '../../test/helpers/security_fakes.dart';
import '../../test/helpers/wallet_fakes.dart';
import '../helpers/journey.dart';

/// Account -> security -> turn on two-step verification (password, key, code) -> recovery
/// codes -> "I saved them" -> two-step on.
void securityJourney(String lang) {
  final l10n = l10nFor(lang);

  testWidgets('account, security, turn on two-step, recovery codes ($lang)', (
    tester,
  ) async {
    final h = await launchApp(
      tester,
      lang: lang,
      signedIn: true,
      config: [Ok(dollarConfig())],
    );

    await openTab(tester, l10n.navAccount);
    await tapVisible(tester, byKey('account-security'));
    expect(currentPath(h), Routes.security.path);

    await tapVisible(tester, byKey('security-two-factor'));
    expect(currentPath(h), Routes.twoFactorSetup.path);

    // Password first; then the key (shown under screen protection) and its QR code.
    await tester.enterText(byKey('two-factor-password'), 'right');
    await tapVisible(tester, byKey('two-factor-start'));
    expect(byKey('two-factor-qr'), findsOneWidget);
    expect(h.security.secureCalls, [true]);

    // The six-digit code from the authenticator confirms it.
    await tester.enterText(find.byType(TextField).last, '123456');
    await tester.pumpAndSettle();
    expect(h.twoFactor.codes, ['123456']);
    expect(find.text(l10n.recoveryCodesTitle), findsOneWidget);
    for (final code in canaryRecoveryCodes) {
      expect(find.text(code), findsOneWidget);
    }

    // Saved: back on the security page, two-step on, codes gone, protection off.
    await tapVisible(tester, byKey('recovery-codes-saved'));
    expect(currentPath(h), Routes.security.path);
    expect(find.text(l10n.twoFactorEnabled), findsOneWidget);
    expect(find.text(canaryRecoveryCodes.first), findsNothing);
    expect(h.security.secureCalls, [true, false]);
  });
}
