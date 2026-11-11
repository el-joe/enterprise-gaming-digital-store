import 'package:flutter_test/flutter_test.dart';

import 'helpers/journey.dart';
import 'journeys/auth_journey.dart';
import 'journeys/notifications_journey.dart';
import 'journeys/reveal_journey.dart';
import 'journeys/security_journey.dart';
import 'journeys/shopping_journey.dart';
import 'journeys/support_journey.dart';

/// M-A19 critical journeys on an Android emulator or a device, on the fake repositories and the
/// compiled contract fixtures (no backend). Every journey runs in English; the purchase journey
/// also runs in Arabic (RTL). One app build runs them all:
///
///     flutter test integration_test/app_test.dart --flavor dev -d <device>
///
/// See `application/README.md` (Integration tests) for the full commands.
void main() {
  initJourneys();

  group('EN journeys', () {
    authJourney('en');
    shoppingJourney('en');
    revealJourney('en');
    securityJourney('en');
    supportJourney('en');
    notificationsJourney('en');
  });

  group('AR journey (RTL)', () {
    shoppingJourney('ar');
  });
}
