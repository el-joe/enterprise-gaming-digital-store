/// Who may open a route. Guards enforce it (see `guards.dart`).
enum RouteAccess {
  /// Anyone, signed in or not.
  public,

  /// A signed-in customer.
  signedIn,

  /// A signed-in customer with a verified email.
  verifiedEmail,

  /// Only while signed out (sign-in, register, two-factor, forgot password); a signed-in customer
  /// is sent on to the return location.
  guestOnly,
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

  /// Launch: the app config check (force update, maintenance, offline), M-A10.
  static const splash = AppRoute(name: 'splash', path: '/splash');

  /// Onboarding step 1: language.
  static const onboardingLanguage = AppRoute(
    name: 'onboarding-language',
    path: '/onboarding/language',
  );

  /// Onboarding step 2: display currency.
  static const onboardingCurrency = AppRoute(
    name: 'onboarding-currency',
    path: '/onboarding/currency',
  );

  /// Sign-in. Guards send protected routes here with the return location in [fromParameter].
  static const login = AppRoute(
    name: 'login',
    path: '/login',
    access: RouteAccess.guestOnly,
  );

  /// Second factor after a password sign-in (the challenge is held in memory only).
  static const twoFactor = AppRoute(
    name: 'two-factor',
    path: '/login/two-factor',
    access: RouteAccess.guestOnly,
  );

  static const register = AppRoute(
    name: 'register',
    path: '/register',
    access: RouteAccess.guestOnly,
  );

  /// Opens the web reset page from `links.forgot_password`.
  static const forgotPassword = AppRoute(
    name: 'forgot-password',
    path: '/forgot-password',
    access: RouteAccess.guestOnly,
  );

  /// Email verification prompt and resend. Guards send unverified customers here.
  static const verifyEmail = AppRoute(
    name: 'verify-email',
    path: '/verify-email',
    access: RouteAccess.signedIn,
  );

  /// The one-time biometric unlock offer after the first sign-in (owner decision 6). The location
  /// to continue to is in [nextParameter].
  static const biometricOffer = AppRoute(
    name: 'biometric-offer',
    path: '/biometric-offer',
    access: RouteAccess.signedIn,
  );

  /// Bottom navigation tabs (M-A11). Cart, orders and account are placeholders until M-A13,
  /// M-A14 and M-A16.
  static const browse = AppRoute(name: 'browse', path: '/browse');

  static const cart = AppRoute(name: 'cart', path: '/cart');

  static const orders = AppRoute(name: 'orders', path: '/orders');

  static const account = AppRoute(name: 'account', path: '/account');

  /// All platforms (04.1).
  static const platforms = AppRoute(
    name: 'platforms',
    path: '/browse/platforms',
  );

  /// Product listing with filters (04.2); query parameters `category`, `platform`, `sort`.
  static const listing = AppRoute(name: 'listing', path: '/browse/products');

  /// Search (04.6); query parameter `q` prefills and submits the term.
  static const search = AppRoute(name: 'search', path: '/search');

  /// Product detail, a placeholder until M-A12.
  static const product = AppRoute(name: 'product', path: '/products/:slug');

  static const all = [
    home,
    browse,
    cart,
    orders,
    account,
    platforms,
    listing,
    search,
    product,
    maintenance,
    updateRequired,
    splash,
    onboardingLanguage,
    onboardingCurrency,
    login,
    twoFactor,
    register,
    forgotPassword,
    verifyEmail,
    biometricOffer,
  ];

  /// Query parameter of [biometricOffer] carrying where to continue.
  static const nextParameter = 'next';

  /// Query parameter carrying the location to return to after sign-in.
  static const fromParameter = 'from';

  /// Query parameter names of [listing] and [search].
  static const categoryParameter = 'category';
  static const platformParameter = 'platform';
  static const sortParameter = 'sort';
  static const queryParameter = 'q';

  /// Location of [listing] for a category, a platform and a sort.
  static String listingLocation({
    String? category,
    String? platform,
    String? sort,
  }) {
    final query = {
      categoryParameter: ?category,
      platformParameter: ?platform,
      sortParameter: ?sort,
    };
    return Uri(
      path: listing.path,
      queryParameters: query.isEmpty ? null : query,
    ).toString();
  }

  /// Location of [search], submitting [query] when given.
  static String searchLocation([String? query]) => Uri(
    path: search.path,
    queryParameters: query == null || query.trim().isEmpty
        ? null
        : {queryParameter: query.trim()},
  ).toString();

  /// Location of [product] for [slug].
  static String productLocation(String slug) =>
      '/products/${Uri.encodeComponent(slug)}';

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
