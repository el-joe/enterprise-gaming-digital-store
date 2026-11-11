import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/router/shell_pages.dart';
import 'package:application/features/auth/presentation/pages/biometric_offer_page.dart';
import 'package:application/features/auth/presentation/pages/forgot_password_page.dart';
import 'package:application/features/auth/presentation/pages/login_page.dart';
import 'package:application/features/auth/presentation/pages/register_page.dart';
import 'package:application/features/auth/presentation/pages/two_factor_page.dart';
import 'package:application/features/auth/presentation/pages/verify_email_page.dart';
import 'package:application/features/launch/presentation/pages/notice_pages.dart';
import 'package:application/features/launch/presentation/pages/onboarding_pages.dart';
import 'package:application/features/launch/presentation/pages/splash_page.dart';
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
      ShellRoute(
        builder: (context, state, child) => AppShell(child: child),
        routes: [
          GoRoute(
            path: Routes.home.path,
            name: Routes.home.name,
            builder: (context, state) => const FoundationHomePage(),
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
