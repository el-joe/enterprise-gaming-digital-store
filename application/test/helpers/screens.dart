import 'dart:async';

import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/storage/preferences_store.dart';
import 'package:application/features/payments/payment_flow.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_test/flutter_test.dart';

import 'catalog_fakes.dart';
import 'checkout_fakes.dart';
import 'engagement_fakes.dart';
import 'product_fakes.dart';
import 'pump_app.dart';
import 'reveal_fakes.dart';
import 'wallet_fakes.dart';

/// One main screen of the app for the M-A19 sweeps (semantics guidelines, text scaling,
/// reduced motion): where it is, whether it needs a session, and anything to set up first.
@immutable
final class ScreenCase {
  const ScreenCase(
    this.name,
    this.location, {
    this.signedIn = true,
    this.key = false,
    this.before,
  });

  final String name;
  final String location;
  final bool signedIn;

  /// One of the key screens checked at 200% text.
  final bool key;

  /// Runs after launch, before the router goes to [location].
  final void Function(TestHarness h)? before;

  @override
  String toString() => name;
}

const _slug = 'orbit-card';

/// Every main page built in M-A10 to M-A17, in a state with content.
final List<ScreenCase> mainScreens = [
  // M-A10 auth and onboarding.
  ScreenCase('login', Routes.login.path, signedIn: false, key: true),
  ScreenCase('register', Routes.register.path, signedIn: false, key: true),
  ScreenCase('forgot password', Routes.forgotPassword.path, signedIn: false),
  ScreenCase(
    'onboarding language',
    Routes.onboardingLanguage.path,
    signedIn: false,
  ),
  ScreenCase(
    'onboarding currency',
    Routes.onboardingCurrency.path,
    signedIn: false,
  ),
  // M-A11 storefront.
  ScreenCase('home', Routes.home.path, key: true),
  ScreenCase('browse', Routes.browse.path, key: true),
  ScreenCase('listing', Routes.listingLocation(), key: true),
  ScreenCase('search', Routes.searchLocation(), key: true),
  // M-A12 product.
  ScreenCase('product', Routes.productLocation(_slug), key: true),
  ScreenCase('product reviews', Routes.productReviewsLocation(_slug)),
  // M-A13 cart, checkout, payment.
  ScreenCase('cart', Routes.cart.path, key: true),
  ScreenCase('checkout', Routes.checkout.path, key: true),
  ScreenCase(
    'payment result',
    Routes.paymentLocation(orderId),
    key: true,
    before: (h) {
      h.payments.verifyResults = [Ok(paidStatus)];
      unawaited(h.read(paymentFlowProvider.notifier).resume(orderId));
    },
  ),
  // M-A14 orders and code reveal.
  ScreenCase('orders', Routes.orders.path, key: true),
  ScreenCase('order detail', Routes.orderLocation(orderId), key: true),
  ScreenCase(
    'code reveal',
    Routes.codeRevealLocation(orderId, fixtureUnitId),
    key: true,
  ),
  // M-A15 wallet.
  ScreenCase('wallet', Routes.wallet.path, key: true),
  // M-A16 account and security.
  ScreenCase('account', Routes.account.path, key: true),
  ScreenCase('profile', Routes.accountProfile.path),
  ScreenCase('settings', Routes.accountSettings.path),
  ScreenCase('security', Routes.security.path, key: true),
  ScreenCase('two-step setup', Routes.twoFactorSetup.path, key: true),
  ScreenCase('change password', Routes.changePassword.path),
  ScreenCase('devices', Routes.devices.path),
  // M-A17 support, reviews, refunds, notifications.
  ScreenCase('support', Routes.accountSupport.path, key: true),
  ScreenCase('new request', Routes.newTicketLocation(), key: true),
  ScreenCase('ticket', Routes.ticketLocation(ticketId), key: true),
  ScreenCase('refund request', Routes.refundRequestLocation(orderId)),
  ScreenCase('refund requests', Routes.refundRequests.path),
  ScreenCase('my reviews', Routes.myReviews.path),
  ScreenCase('notifications', Routes.accountNotifications.path, key: true),
  ScreenCase('notification settings', Routes.notificationSettings.path),
];

/// The key screens of the 200% text check.
List<ScreenCase> get keyScreens =>
    mainScreens.where((screen) => screen.key).toList();

/// Pumps the app in [lang] with content on every fake (contract fixtures), then opens [screen].
Future<TestHarness> openScreen(
  WidgetTester tester,
  ScreenCase screen,
  String lang, {
  Size size = const Size(390, 844),
}) async {
  final h = await pumpHarness(
    tester,
    languageCode: lang,
    signedIn: screen.signedIn,
    size: size,
    config: [Ok(dollarConfig())],
    preferences: const {PreferencesStore.integrityWarningShownKey: true},
    settle: false,
    arrange: (h) {
      h.home.results = [Ok(fixtureHome(lang))];
      h.product
        ..onDetail = ((_) => Ok(fixtureProduct(lang)))
        ..onVariants = ((_) => Ok(fixtureVariants(lang)))
        ..onReviews = ((_) => Ok(fixtureReviews(lang)));
      h.cart.getResult = Ok(cartWith());
      h.notifications
        ..unreadResult = const Ok(2)
        ..pages = {1: Ok(inboxPageOf(fixtureInbox()))};
      h.support.ticketPages = {
        1: Ok(pagedOf([ticket()])),
      };
      h.reveal.reveals = [Ok(canaryReveal())];
    },
  );
  await tester.pumpAndSettle();
  screen.before?.call(h);
  h.read(appRouterProvider).go(screen.location);
  await tester.pumpAndSettle();
  return h;
}
