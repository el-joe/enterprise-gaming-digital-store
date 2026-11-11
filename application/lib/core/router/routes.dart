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

  /// Bottom navigation tabs (M-A11). The cart is M-A13, orders M-A14 and the account hub M-A16.
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

  /// Product detail (M-A12), above the tabs; deep links to `/products/{slug}` land here.
  static const product = AppRoute(name: 'product', path: '/products/:slug');

  /// All reviews of a product (05.2), pushed from the product page.
  static const productReviews = AppRoute(
    name: 'product-reviews',
    path: '/products/:slug/reviews',
  );

  /// Checkout (M-A13, 07.x), above the tabs. Signed-in only (owner decision 4): a guest is sent
  /// to sign in and comes back here.
  static const checkout = AppRoute(
    name: 'checkout',
    path: '/checkout',
    access: RouteAccess.signedIn,
  );

  /// Payment in progress and results of an order (07.6, 08.1 to 08.4), above the tabs.
  static const payment = AppRoute(
    name: 'payment',
    path: '/payments/:order',
    access: RouteAccess.signedIn,
  );

  /// Order detail (M-A14, 09.4, 09.5), above the tabs; the delivery email's
  /// `/account/orders/{order}` link lands here.
  static const orderDetail = AppRoute(
    name: 'order-detail',
    path: '/orders/:order',
    access: RouteAccess.signedIn,
  );

  /// One unit's code (M-A14, 10.1 to 10.6), pushed from the order detail.
  static const codeReveal = AppRoute(
    name: 'code-reveal',
    path: '/orders/:order/units/:unit',
    access: RouteAccess.signedIn,
  );

  /// Refund request form (M-A17, 15.1), pushed from the order detail.
  static const refundRequest = AppRoute(
    name: 'refund-request',
    path: '/orders/:order/refund',
    access: RouteAccess.signedIn,
  );

  /// Wallet (M-A15, 12.1 to 12.3): balance and statement, read only; from the Account tab.
  static const wallet = AppRoute(
    name: 'wallet',
    path: '/wallet',
    access: RouteAccess.signedIn,
  );

  /// Profile (M-A16, 17.2), above the tabs.
  static const accountProfile = AppRoute(
    name: 'account-profile',
    path: '/account/profile',
    access: RouteAccess.signedIn,
  );

  /// Settings (17.3): language, display currency, legal links, version and sign out. Public, so
  /// a signed-out customer can change the language and read the terms.
  static const accountSettings = AppRoute(
    name: 'account-settings',
    path: '/account/settings',
  );

  /// Notifications inbox (M-A17, 16.1, 16.2); the home bell and the Account hub open it.
  static const accountNotifications = AppRoute(
    name: 'account-notifications',
    path: '/account/notifications',
    access: RouteAccess.signedIn,
  );

  /// Notification preferences (16.3): the channels and categories of the API.
  static const notificationSettings = AppRoute(
    name: 'notification-settings',
    path: '/account/notifications/settings',
    access: RouteAccess.signedIn,
  );

  /// Support tickets (M-A17, 14.1, 14.4). Public: signed out it asks to sign in and offers the
  /// web help page.
  static const accountSupport = AppRoute(
    name: 'account-support',
    path: '/account/support',
  );

  /// New support request (14.2); query parameters `order` (prefills the linked order) and
  /// `category`.
  static const newTicket = AppRoute(
    name: 'new-ticket',
    path: '/account/support/new',
    access: RouteAccess.signedIn,
  );

  /// A ticket's thread (14.3, 14.4 closed); inbox `ticket` links land here.
  static const ticket = AppRoute(
    name: 'ticket',
    path: '/account/support/:ticket',
    access: RouteAccess.signedIn,
  );

  /// My reviews (M-A17, 13.1, 13.3): items ready to review and the reviews with their status.
  static const myReviews = AppRoute(
    name: 'my-reviews',
    path: '/account/reviews',
    access: RouteAccess.signedIn,
  );

  /// Write a review of an eligible order item (13.2); the path holds the `order_item_id`.
  static const writeReview = AppRoute(
    name: 'write-review',
    path: '/account/reviews/new/:item',
    access: RouteAccess.signedIn,
  );

  /// Edit one of the customer's reviews (it goes back to moderation).
  static const editReview = AppRoute(
    name: 'edit-review',
    path: '/account/reviews/:review/edit',
    access: RouteAccess.signedIn,
  );

  /// The customer's refund requests and their status (15.2).
  static const refundRequests = AppRoute(
    name: 'refund-requests',
    path: '/account/refunds',
    access: RouteAccess.signedIn,
  );

  /// Security (18.1): two-step verification, password, biometric unlock, devices.
  static const security = AppRoute(
    name: 'security',
    path: '/account/security',
    access: RouteAccess.signedIn,
  );

  /// Change password (18.7).
  static const changePassword = AppRoute(
    name: 'change-password',
    path: '/account/security/password',
    access: RouteAccess.signedIn,
  );

  /// Turn on two-step verification (18.2, 18.3): key and recovery codes, screen protected.
  static const twoFactorSetup = AppRoute(
    name: 'two-factor-setup',
    path: '/account/security/two-factor',
    access: RouteAccess.signedIn,
  );

  /// Signed-in devices (18.5, 18.6).
  static const devices = AppRoute(
    name: 'devices',
    path: '/account/security/devices',
    access: RouteAccess.signedIn,
  );

  /// Guest order access (11.x): paste the link of the delivery email. Reached from the Account
  /// tab and the signed-out Orders tab; no guest checkout (owner decision 4).
  static const guestAccess = AppRoute(
    name: 'guest-access',
    path: '/guest-orders',
  );

  /// A guest order: email code (OTP), then the order (11.1 to 11.5). The signed delivery link
  /// lands here through the app-link listener.
  static const guestOrder = AppRoute(
    name: 'guest-order',
    path: '/guest-orders/:order',
  );

  /// One unit's code of a guest order (guest order token with the link and OTP proofs).
  static const guestReveal = AppRoute(
    name: 'guest-reveal',
    path: '/guest-orders/:order/units/:unit',
  );

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
    productReviews,
    checkout,
    payment,
    orderDetail,
    codeReveal,
    refundRequest,
    wallet,
    accountProfile,
    accountSettings,
    accountNotifications,
    notificationSettings,
    accountSupport,
    newTicket,
    ticket,
    myReviews,
    writeReview,
    editReview,
    refundRequests,
    security,
    changePassword,
    twoFactorSetup,
    devices,
    guestAccess,
    guestOrder,
    guestReveal,
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

  /// Location of [productReviews] for [slug].
  static String productReviewsLocation(String slug) =>
      '${productLocation(slug)}/reviews';

  /// Location of [payment] for an order.
  static String paymentLocation(String orderPublicId) =>
      '/payments/${Uri.encodeComponent(orderPublicId)}';

  /// Location of [orderDetail].
  static String orderLocation(String orderPublicId) =>
      '/orders/${Uri.encodeComponent(orderPublicId)}';

  /// Location of [codeReveal].
  static String codeRevealLocation(String orderPublicId, String unitPublicId) =>
      '${orderLocation(orderPublicId)}/units/${Uri.encodeComponent(unitPublicId)}';

  /// Location of [refundRequest].
  static String refundRequestLocation(String orderPublicId) =>
      '${orderLocation(orderPublicId)}/refund';

  /// Location of [newTicket], with the order to link and the topic.
  static String newTicketLocation({String? order, String? category}) {
    final query = {orderParameter: ?order, categoryParameter: ?category};
    return Uri(
      path: newTicket.path,
      queryParameters: query.isEmpty ? null : query,
    ).toString();
  }

  /// Location of [ticket].
  static String ticketLocation(String ticketPublicId) =>
      '/account/support/${Uri.encodeComponent(ticketPublicId)}';

  /// Location of [writeReview] for an order item.
  static String writeReviewLocation(int orderItemId) =>
      '/account/reviews/new/$orderItemId';

  /// Location of [editReview].
  static String editReviewLocation(int reviewId) =>
      '/account/reviews/$reviewId/edit';

  /// Path parameters of [ticket], [writeReview] and [editReview].
  static const ticketParameter = 'ticket';
  static const itemParameter = 'item';
  static const reviewParameter = 'review';

  /// Location of [guestOrder].
  static String guestOrderLocation(String orderPublicId) =>
      '/guest-orders/${Uri.encodeComponent(orderPublicId)}';

  /// Location of [guestReveal].
  static String guestRevealLocation(
    String orderPublicId,
    String unitPublicId,
  ) =>
      '${guestOrderLocation(orderPublicId)}/units/${Uri.encodeComponent(unitPublicId)}';

  /// Path parameter of [codeReveal] and [guestReveal].
  static const unitParameter = 'unit';

  /// Path parameter of [payment].
  static const orderParameter = 'order';

  /// Path parameter of [product] and [productReviews].
  static const slugParameter = 'slug';

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
