import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  const signedOut = GuardState(
    session: SessionStatus.signedOut,
    availability: AppAvailability.available,
  );
  const signedIn = GuardState(
    session: SessionStatus.signedIn,
    availability: AppAvailability.available,
  );

  String? go(
    GuardState state,
    String location, [
    RouteAccess access = RouteAccess.public,
  ]) =>
      redirectFor(state: state, location: Uri.parse(location), access: access);

  test('public routes are open to everyone', () {
    expect(go(signedOut, '/'), isNull);
    expect(go(signedIn, '/'), isNull);
  });

  test(
    'protected routes send signed-out users to login with a return path',
    () {
      final target = go(
        signedOut,
        '/orders/ord_1?tab=units',
        RouteAccess.signedIn,
      );
      final uri = Uri.parse(target!);
      expect(uri.path, Routes.login.path);
      expect(
        uri.queryParameters[Routes.fromParameter],
        '/orders/ord_1?tab=units',
      );
      expect(go(signedIn, '/orders/ord_1', RouteAccess.signedIn), isNull);
    },
  );

  test('verified-email routes send unverified customers to verification', () {
    const unverified = GuardState(
      session: SessionStatus.signedIn,
      availability: AppAvailability.available,
      emailVerified: false,
    );
    expect(
      go(unverified, '/reviews', RouteAccess.verifiedEmail),
      Routes.verifyEmail.path,
    );
    expect(go(unverified, '/orders', RouteAccess.signedIn), isNull);
    // Unknown status (profile not loaded) does not block.
    expect(go(signedIn, '/reviews', RouteAccess.verifiedEmail), isNull);
    expect(
      Uri.parse(go(signedOut, '/reviews', RouteAccess.verifiedEmail)!).path,
      Routes.login.path,
    );
  });

  test('signed-in users leave login for the return path', () {
    expect(go(signedIn, '/login?from=%2Forders%2F1'), '/orders/1');
    expect(go(signedIn, '/login'), Routes.home.path);
  });

  test('return path cannot leave the app', () {
    for (final from in [
      'https://evil.test/',
      '//evil.test/x',
      'javascript:alert(1)',
      '/login',
      '',
    ]) {
      expect(safeReturnLocation(from), Routes.home.path, reason: from);
    }
    expect(safeReturnLocation('/cart'), '/cart');
  });

  test('maintenance wins over everything except update required', () {
    const maintenance = GuardState(
      session: SessionStatus.signedIn,
      availability: AppAvailability.maintenance,
    );
    expect(go(maintenance, '/'), Routes.maintenance.path);
    expect(
      go(maintenance, '/orders', RouteAccess.signedIn),
      Routes.maintenance.path,
    );
    expect(go(maintenance, Routes.maintenance.path), isNull);
  });

  test('update required blocks every route', () {
    const update = GuardState(
      session: SessionStatus.signedOut,
      availability: AppAvailability.updateRequired,
    );
    expect(go(update, '/'), Routes.updateRequired.path);
    expect(go(update, Routes.maintenance.path), Routes.updateRequired.path);
    expect(go(update, Routes.updateRequired.path), isNull);
  });

  test('notice routes return home once the store is available', () {
    expect(go(signedOut, Routes.maintenance.path), Routes.home.path);
    expect(go(signedOut, Routes.updateRequired.path), Routes.home.path);
  });

  test('guest-only routes send a signed-in customer on (M-A10)', () {
    expect(
      go(signedIn, '/login/two-factor?from=%2Fcart', RouteAccess.guestOnly),
      '/cart',
    );
    expect(go(signedIn, '/forgot-password', RouteAccess.guestOnly), '/');
    // Registering leads to the verify-email prompt.
    expect(
      go(signedIn, Routes.register.path, RouteAccess.guestOnly),
      Routes.verifyEmail.path,
    );
    expect(go(signedOut, Routes.register.path, RouteAccess.guestOnly), isNull);
  });

  test('the biometric offer comes first when it is due', () {
    const due = GuardState(
      session: SessionStatus.signedIn,
      availability: AppAvailability.available,
      biometricOfferPending: true,
    );
    final target = Uri.parse(go(due, '/login?from=%2Fcart')!);
    expect(target.path, Routes.biometricOffer.path);
    expect(target.queryParameters[Routes.nextParameter], '/cart');
    expect(
      Uri.parse(go(due, Routes.register.path, RouteAccess.guestOnly)!)
          .queryParameters[Routes.nextParameter],
      Routes.verifyEmail.path,
    );
    // The offer page itself is a normal signed-in route.
    expect(go(due, Routes.biometricOffer.path, RouteAccess.signedIn), isNull);
  });

  test('a return path never lands on a guest-only route', () {
    expect(safeReturnLocation('/register'), Routes.home.path);
    expect(safeReturnLocation('/login/two-factor'), Routes.home.path);
  });

  test('route table lookup', () {
    expect(Routes.byPath('/'), Routes.home);
    expect(Routes.byPath('/verify-email')?.access, RouteAccess.signedIn);
    expect(Routes.byPath('/login')?.access, RouteAccess.guestOnly);
    expect(Routes.byPath('/splash'), Routes.splash);
    expect(Routes.byPath('/nope'), isNull);
    expect(Routes.byPath(null), isNull);
  });
}
