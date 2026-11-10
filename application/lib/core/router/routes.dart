/// Who may open a route. Guards enforce it (see `guards.dart`).
enum RouteAccess {
  /// Anyone, signed in or not.
  public,

  /// A signed-in customer.
  signedIn,

  /// A signed-in customer with a verified email.
  verifiedEmail,
}

/// A named route with its path and access rule.
final class AppRoute {
  const AppRoute({
    required this.name,
    required this.path,
    this.access = RouteAccess.public,
  });

  final String name;
  final String path;
  final RouteAccess access;
}

/// Route table. Feature milestones add their routes here with the page that serves them.
abstract final class Routes {
  static const home = AppRoute(name: 'home', path: '/');

  /// Shown while the API answers 503 `maintenance`.
  static const maintenance = AppRoute(
    name: 'maintenance',
    path: '/maintenance',
  );

  /// Shown when this build is below the supported minimum version.
  static const updateRequired = AppRoute(
    name: 'update-required',
    path: '/update-required',
  );

  /// Sign-in. Guards send protected routes here; its page is built in M-A10.
  static const login = AppRoute(name: 'login', path: '/login');

  /// Email verification prompt. Guards send unverified customers here; its page is built in M-A10.
  static const verifyEmail = AppRoute(
    name: 'verify-email',
    path: '/verify-email',
    access: RouteAccess.signedIn,
  );

  static const all = [home, maintenance, updateRequired, login, verifyEmail];

  /// Query parameter carrying the location to return to after sign-in.
  static const fromParameter = 'from';

  /// The route whose path pattern equals [path] (go_router's `fullPath`), if any.
  static AppRoute? byPath(String? path) {
    for (final route in all) {
      if (route.path == path) {
        return route;
      }
    }
    return null;
  }
}
