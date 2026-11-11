import 'package:application/core/router/guards.dart';
import 'package:application/core/router/material_route.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/router/shell_pages.dart';
import 'package:application/features/account/presentation/pages/account_hub_page.dart';
import 'package:application/features/account/presentation/pages/change_password_page.dart';
import 'package:application/features/account/presentation/pages/profile_page.dart';
import 'package:application/features/account/presentation/pages/settings_page.dart';
import 'package:application/features/auth/presentation/pages/biometric_offer_page.dart';
import 'package:application/features/auth/presentation/pages/forgot_password_page.dart';
import 'package:application/features/auth/presentation/pages/login_page.dart';
import 'package:application/features/auth/presentation/pages/register_page.dart';
import 'package:application/features/auth/presentation/pages/two_factor_page.dart';
import 'package:application/features/auth/presentation/pages/verify_email_page.dart';
import 'package:application/features/cart/presentation/pages/cart_page.dart';
import 'package:application/features/catalog/presentation/pages/browse_page.dart';
import 'package:application/features/catalog/presentation/pages/listing_page.dart';
import 'package:application/features/catalog/presentation/pages/platforms_page.dart';
import 'package:application/features/catalog/presentation/widgets/currency_sheet.dart';
import 'package:application/features/checkout/presentation/pages/checkout_page.dart';
import 'package:application/features/code_reveal/presentation/pages/reveal_page.dart';
import 'package:application/features/home/presentation/pages/home_page.dart';
import 'package:application/features/launch/presentation/pages/notice_pages.dart';
import 'package:application/features/launch/presentation/pages/onboarding_pages.dart';
import 'package:application/features/launch/presentation/pages/splash_page.dart';
import 'package:application/features/notifications/presentation/pages/notification_settings_page.dart';
import 'package:application/features/notifications/presentation/pages/notifications_page.dart';
import 'package:application/features/orders/presentation/pages/guest_order_pages.dart';
import 'package:application/features/orders/presentation/pages/order_detail_page.dart';
import 'package:application/features/orders/presentation/pages/orders_page.dart';
import 'package:application/features/payments/presentation/pages/payment_page.dart';
import 'package:application/features/product/presentation/pages/product_page.dart';
import 'package:application/features/product/presentation/pages/product_reviews_page.dart';
import 'package:application/features/reviews/presentation/pages/my_reviews_page.dart';
import 'package:application/features/reviews/presentation/pages/review_form_page.dart';
import 'package:application/features/search/presentation/pages/search_page.dart';
import 'package:application/features/security/presentation/pages/devices_page.dart';
import 'package:application/features/security/presentation/pages/security_page.dart';
import 'package:application/features/security/presentation/pages/two_factor_setup_page.dart';
import 'package:application/features/support/presentation/pages/new_ticket_page.dart';
import 'package:application/features/support/presentation/pages/refund_pages.dart';
import 'package:application/features/support/presentation/pages/support_page.dart';
import 'package:application/features/support/presentation/pages/ticket_thread_page.dart';
import 'package:application/features/wallet/presentation/pages/wallet_page.dart';
import 'package:flutter/foundation.dart';
import 'package:go_router/go_router.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'app_router.g.dart';

/// The app's go_router. Guard inputs are providers; any change re-runs [redirectFor]. The app
/// starts on the splash (app config check), which continues to onboarding or home.
@Riverpod(keepAlive: true)
GoRouter appRouter(Ref ref) {
  final refresh = ValueNotifier<int>(0);
  void bump() => refresh.value++;
  ref
    ..listen(sessionProvider, (_, _) => bump())
    ..listen(availabilityProvider, (_, _) => bump())
    ..listen(emailVerificationProvider, (_, _) => bump())
    ..listen(biometricOfferDueProvider, (_, _) => bump())
    ..onDispose(refresh.dispose);

  String? from(GoRouterState state) =>
      state.uri.queryParameters[Routes.fromParameter];

  final router = GoRouter(
    initialLocation: Routes.splash.path,
    refreshListenable: refresh,
    redirect: (context, state) => redirectFor(
      state: GuardState(
        session: ref.read(sessionProvider),
        availability: ref.read(availabilityProvider),
        emailVerified: ref.read(emailVerificationProvider),
        biometricOfferPending: ref.read(biometricOfferDueProvider),
      ),
      location: state.uri,
      access: Routes.byPath(state.fullPath)?.access ?? RouteAccess.public,
    ),
    routes: [
      StatefulShellRoute.indexedStack(
        // The shell is the app's base, never pushed over another page, and its navigation shell
        // has one global key: an old shell still animating out next to a new one would clash, so
        // it appears without a transition. Pages pushed over it animate (materialRoute).
        pageBuilder: (context, state, shell) =>
            instantPage(state, MainShell(shell: shell)),
        branches: [
          StatefulShellBranch(
            routes: [
              materialRoute(
                path: Routes.home.path,
                name: Routes.home.name,
                builder: (context, state) =>
                    const HomeScreen(openCurrencySwitcher: showCurrencySheet),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              materialRoute(
                path: Routes.browse.path,
                name: Routes.browse.name,
                builder: (context, state) => const BrowsePage(),
                routes: [
                  materialRoute(
                    path: 'platforms',
                    name: Routes.platforms.name,
                    builder: (context, state) => const PlatformsPage(),
                  ),
                  materialRoute(
                    path: 'products',
                    name: Routes.listing.name,
                    builder: (context, state) {
                      final query = state.uri.queryParameters;
                      return ListingPage(
                        key: ValueKey(state.uri.query),
                        category: query[Routes.categoryParameter],
                        platform: query[Routes.platformParameter],
                        sort: query[Routes.sortParameter],
                      );
                    },
                  ),
                ],
              ),
              materialRoute(
                path: Routes.search.path,
                name: Routes.search.name,
                builder: (context, state) => SearchPage(
                  initialQuery:
                      state.uri.queryParameters[Routes.queryParameter],
                ),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              materialRoute(
                path: Routes.cart.path,
                name: Routes.cart.name,
                builder: (context, state) => const CartPage(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              materialRoute(
                path: Routes.orders.path,
                name: Routes.orders.name,
                builder: (context, state) => const OrdersPage(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              materialRoute(
                path: Routes.account.path,
                name: Routes.account.name,
                builder: (context, state) => const AccountHubPage(),
              ),
            ],
          ),
        ],
      ),
      materialRoute(
        path: Routes.product.path,
        name: Routes.product.name,
        builder: (context, state) {
          final slug = state.pathParameters[Routes.slugParameter]!;
          return ProductPage(key: ValueKey('product-$slug'), slug: slug);
        },
        routes: [
          materialRoute(
            path: 'reviews',
            name: Routes.productReviews.name,
            builder: (context, state) => ProductReviewsScreen(
              slug: state.pathParameters[Routes.slugParameter]!,
            ),
          ),
        ],
      ),
      materialRoute(
        path: Routes.checkout.path,
        name: Routes.checkout.name,
        builder: (context, state) => const CheckoutPage(),
      ),
      materialRoute(
        path: Routes.payment.path,
        name: Routes.payment.name,
        builder: (context, state) {
          final order = state.pathParameters[Routes.orderParameter]!;
          return PaymentPage(
            key: ValueKey('payment-$order'),
            orderPublicId: order,
          );
        },
      ),
      materialRoute(
        path: Routes.orderDetail.path,
        name: Routes.orderDetail.name,
        builder: (context, state) {
          final order = state.pathParameters[Routes.orderParameter]!;
          return OrderDetailPage(
            key: ValueKey('order-$order'),
            orderPublicId: order,
          );
        },
        routes: [
          materialRoute(
            path: 'units/:${Routes.unitParameter}',
            name: Routes.codeReveal.name,
            builder: (context, state) => RevealPage(
              orderPublicId: state.pathParameters[Routes.orderParameter]!,
              unitPublicId: state.pathParameters[Routes.unitParameter]!,
            ),
          ),
          materialRoute(
            path: 'refund',
            name: Routes.refundRequest.name,
            builder: (context, state) => RefundRequestPage(
              orderPublicId: state.pathParameters[Routes.orderParameter]!,
            ),
          ),
        ],
      ),
      materialRoute(
        path: Routes.wallet.path,
        name: Routes.wallet.name,
        builder: (context, state) => const WalletPage(),
      ),
      materialRoute(
        path: Routes.accountProfile.path,
        name: Routes.accountProfile.name,
        builder: (context, state) => const ProfilePage(),
      ),
      materialRoute(
        path: Routes.accountSettings.path,
        name: Routes.accountSettings.name,
        builder: (context, state) => const SettingsPage(),
      ),
      materialRoute(
        path: Routes.accountNotifications.path,
        name: Routes.accountNotifications.name,
        builder: (context, state) => const NotificationsPage(),
        routes: [
          materialRoute(
            path: 'settings',
            name: Routes.notificationSettings.name,
            builder: (context, state) => const NotificationSettingsPage(),
          ),
        ],
      ),
      materialRoute(
        path: Routes.accountSupport.path,
        name: Routes.accountSupport.name,
        builder: (context, state) => const SupportPage(),
        routes: [
          materialRoute(
            path: 'new',
            name: Routes.newTicket.name,
            builder: (context, state) => NewTicketPage(
              order: state.uri.queryParameters[Routes.orderParameter],
              category: state.uri.queryParameters[Routes.categoryParameter],
            ),
          ),
          materialRoute(
            path: ':${Routes.ticketParameter}',
            name: Routes.ticket.name,
            builder: (context, state) {
              final ticket = state.pathParameters[Routes.ticketParameter]!;
              return TicketThreadPage(
                key: ValueKey('ticket-$ticket'),
                ticketPublicId: ticket,
              );
            },
          ),
        ],
      ),
      materialRoute(
        path: Routes.myReviews.path,
        name: Routes.myReviews.name,
        builder: (context, state) => const MyReviewsPage(),
        routes: [
          materialRoute(
            path: 'new/:${Routes.itemParameter}',
            name: Routes.writeReview.name,
            builder: (context, state) {
              final item = int.tryParse(
                state.pathParameters[Routes.itemParameter]!,
              );
              return item == null
                  ? const NoticePage(kind: NoticeKind.notFound)
                  : ReviewFormPage(
                      key: ValueKey('review-item-$item'),
                      orderItemId: item,
                    );
            },
          ),
          materialRoute(
            path: ':${Routes.reviewParameter}/edit',
            name: Routes.editReview.name,
            builder: (context, state) {
              final review = int.tryParse(
                state.pathParameters[Routes.reviewParameter]!,
              );
              return review == null
                  ? const NoticePage(kind: NoticeKind.notFound)
                  : ReviewFormPage(
                      key: ValueKey('review-edit-$review'),
                      reviewId: review,
                    );
            },
          ),
        ],
      ),
      materialRoute(
        path: Routes.refundRequests.path,
        name: Routes.refundRequests.name,
        builder: (context, state) => RefundRequestsPage(
          sent:
              state.uri.queryParameters[RefundRequestsPage.sentParameter] ==
              '1',
        ),
      ),
      materialRoute(
        path: Routes.security.path,
        name: Routes.security.name,
        builder: (context, state) => const SecurityPage(),
        routes: [
          materialRoute(
            path: 'password',
            name: Routes.changePassword.name,
            builder: (context, state) => const ChangePasswordPage(),
          ),
          materialRoute(
            path: 'two-factor',
            name: Routes.twoFactorSetup.name,
            builder: (context, state) => const TwoFactorSetupPage(),
          ),
          materialRoute(
            path: 'devices',
            name: Routes.devices.name,
            builder: (context, state) => const DevicesPage(),
          ),
        ],
      ),
      materialRoute(
        path: Routes.guestAccess.path,
        name: Routes.guestAccess.name,
        builder: (context, state) => const GuestAccessPage(),
        routes: [
          materialRoute(
            path: ':${Routes.orderParameter}',
            name: Routes.guestOrder.name,
            builder: (context, state) {
              final order = state.pathParameters[Routes.orderParameter]!;
              return GuestOrderPage(
                key: ValueKey('guest-$order'),
                orderPublicId: order,
              );
            },
            routes: [
              materialRoute(
                path: 'units/:${Routes.unitParameter}',
                name: Routes.guestReveal.name,
                builder: (context, state) => RevealPage(
                  orderPublicId: state.pathParameters[Routes.orderParameter]!,
                  unitPublicId: state.pathParameters[Routes.unitParameter]!,
                  guest: true,
                ),
              ),
            ],
          ),
        ],
      ),
      // The splash is the cold start (or a relaunch of the config check): it replaces the app
      // at once and hands over to home within a frame or two, so it has no transition of its
      // own (an animated splash would keep the old shell next to the new one).
      materialRoute(
        path: Routes.splash.path,
        name: Routes.splash.name,
        transition: false,
        builder: (context, state) => const SplashPage(),
      ),
      materialRoute(
        path: Routes.maintenance.path,
        name: Routes.maintenance.name,
        builder: (context, state) => const MaintenancePage(),
      ),
      materialRoute(
        path: Routes.updateRequired.path,
        name: Routes.updateRequired.name,
        builder: (context, state) => const UpdateRequiredPage(),
      ),
      materialRoute(
        path: Routes.onboardingLanguage.path,
        name: Routes.onboardingLanguage.name,
        builder: (context, state) => const OnboardingLanguagePage(),
      ),
      materialRoute(
        path: Routes.onboardingCurrency.path,
        name: Routes.onboardingCurrency.name,
        builder: (context, state) => const OnboardingCurrencyPage(),
      ),
      materialRoute(
        path: Routes.login.path,
        name: Routes.login.name,
        builder: (context, state) => LoginPage(from: from(state)),
      ),
      materialRoute(
        path: Routes.twoFactor.path,
        name: Routes.twoFactor.name,
        builder: (context, state) => TwoFactorPage(from: from(state)),
      ),
      materialRoute(
        path: Routes.register.path,
        name: Routes.register.name,
        builder: (context, state) => RegisterPage(from: from(state)),
      ),
      materialRoute(
        path: Routes.forgotPassword.path,
        name: Routes.forgotPassword.name,
        builder: (context, state) => const ForgotPasswordPage(),
      ),
      materialRoute(
        path: Routes.verifyEmail.path,
        name: Routes.verifyEmail.name,
        builder: (context, state) => const VerifyEmailPage(),
      ),
      materialRoute(
        path: Routes.biometricOffer.path,
        name: Routes.biometricOffer.name,
        builder: (context, state) => BiometricOfferPage(
          next: state.uri.queryParameters[Routes.nextParameter],
        ),
      ),
    ],
    errorBuilder: (context, state) =>
        const NoticePage(kind: NoticeKind.notFound),
  );
  ref.onDispose(router.dispose);
  return router;
}
