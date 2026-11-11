import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/router/shell_pages.dart';
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
import 'package:application/features/orders/presentation/pages/guest_order_pages.dart';
import 'package:application/features/orders/presentation/pages/order_detail_page.dart';
import 'package:application/features/orders/presentation/pages/orders_page.dart';
import 'package:application/features/payments/presentation/pages/payment_page.dart';
import 'package:application/features/product/presentation/pages/product_page.dart';
import 'package:application/features/product/presentation/pages/product_reviews_page.dart';
import 'package:application/features/search/presentation/pages/search_page.dart';
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
        builder: (context, state, shell) => MainShell(shell: shell),
        branches: [
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: Routes.home.path,
                name: Routes.home.name,
                builder: (context, state) =>
                    const HomeScreen(openCurrencySwitcher: showCurrencySheet),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: Routes.browse.path,
                name: Routes.browse.name,
                builder: (context, state) => const BrowsePage(),
                routes: [
                  GoRoute(
                    path: 'platforms',
                    name: Routes.platforms.name,
                    builder: (context, state) => const PlatformsPage(),
                  ),
                  GoRoute(
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
              GoRoute(
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
              GoRoute(
                path: Routes.cart.path,
                name: Routes.cart.name,
                builder: (context, state) => const CartPage(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: Routes.orders.path,
                name: Routes.orders.name,
                builder: (context, state) => const OrdersPage(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: Routes.account.path,
                name: Routes.account.name,
                builder: (context, state) => const AccountTabPage(),
              ),
            ],
          ),
        ],
      ),
      GoRoute(
        path: Routes.product.path,
        name: Routes.product.name,
        builder: (context, state) {
          final slug = state.pathParameters[Routes.slugParameter]!;
          return ProductPage(key: ValueKey('product-$slug'), slug: slug);
        },
        routes: [
          GoRoute(
            path: 'reviews',
            name: Routes.productReviews.name,
            builder: (context, state) => ProductReviewsScreen(
              slug: state.pathParameters[Routes.slugParameter]!,
            ),
          ),
        ],
      ),
      GoRoute(
        path: Routes.checkout.path,
        name: Routes.checkout.name,
        builder: (context, state) => const CheckoutPage(),
      ),
      GoRoute(
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
      GoRoute(
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
          GoRoute(
            path: 'units/:${Routes.unitParameter}',
            name: Routes.codeReveal.name,
            builder: (context, state) => RevealPage(
              orderPublicId: state.pathParameters[Routes.orderParameter]!,
              unitPublicId: state.pathParameters[Routes.unitParameter]!,
            ),
          ),
          GoRoute(
            path: 'refund',
            name: Routes.refundRequest.name,
            builder: (context, state) => ComingSoonPage(
              title: context.l10n.refundRequestTitle,
              root: false,
            ),
          ),
        ],
      ),
      GoRoute(
        path: Routes.guestAccess.path,
        name: Routes.guestAccess.name,
        builder: (context, state) => const GuestAccessPage(),
        routes: [
          GoRoute(
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
              GoRoute(
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
      GoRoute(
        path: Routes.splash.path,
        name: Routes.splash.name,
        builder: (context, state) => const SplashPage(),
      ),
      GoRoute(
        path: Routes.maintenance.path,
        name: Routes.maintenance.name,
        builder: (context, state) => const MaintenancePage(),
      ),
      GoRoute(
        path: Routes.updateRequired.path,
        name: Routes.updateRequired.name,
        builder: (context, state) => const UpdateRequiredPage(),
      ),
      GoRoute(
        path: Routes.onboardingLanguage.path,
        name: Routes.onboardingLanguage.name,
        builder: (context, state) => const OnboardingLanguagePage(),
      ),
      GoRoute(
        path: Routes.onboardingCurrency.path,
        name: Routes.onboardingCurrency.name,
        builder: (context, state) => const OnboardingCurrencyPage(),
      ),
      GoRoute(
        path: Routes.login.path,
        name: Routes.login.name,
        builder: (context, state) => LoginPage(from: from(state)),
      ),
      GoRoute(
        path: Routes.twoFactor.path,
        name: Routes.twoFactor.name,
        builder: (context, state) => TwoFactorPage(from: from(state)),
      ),
      GoRoute(
        path: Routes.register.path,
        name: Routes.register.name,
        builder: (context, state) => RegisterPage(from: from(state)),
      ),
      GoRoute(
        path: Routes.forgotPassword.path,
        name: Routes.forgotPassword.name,
        builder: (context, state) => const ForgotPasswordPage(),
      ),
      GoRoute(
        path: Routes.verifyEmail.path,
        name: Routes.verifyEmail.name,
        builder: (context, state) => const VerifyEmailPage(),
      ),
      GoRoute(
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
