import 'dart:async';

import 'package:app_links/app_links.dart';
import 'package:application/core/config/app_env.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/deep_links.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/features/payments/payment_flow.dart';
import 'package:go_router/go_router.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'app_link_listener.g.dart';

/// Incoming verified app links (the platform's App Links / Universal Links).
abstract interface class LinkSource {
  /// Every link the app is opened with, the launch link included.
  Stream<Uri> get links;
}

/// [LinkSource] on `app_links` (README: package choice). The plugin keeps links that arrive
/// before the first listen, so a cold-start link is not lost.
final class AppLinksSource implements LinkSource {
  @override
  Stream<Uri> get links => AppLinks().uriLinkStream;
}

@Riverpod(keepAlive: true)
LinkSource linkSource(Ref ref) => AppLinksSource();

/// Routes app links (MOBILE_APP_ROADMAP.md 2.1 `deep_links.dart`, ADR-039):
///
/// - `https://<APP_LINK_HOST>/app/payments/return/{order}` and `/cancel/{order}`: hands the order
///   to [PaymentFlow] (which only ever asks the server; the link marks nothing paid) and opens
///   its payment screen.
/// - `/products/{slug}` (also `/en|ar/products/{slug}`): pushes the product page (M-A12).
///
/// Links are parsed by [parseDeepLink] (https and the configured host only). Links that arrive
/// while the launch check, onboarding, maintenance or force update is on screen wait until the
/// app reaches its normal pages. Signed-in-only links the guards send to sign in first. Other
/// parsed links (guest order access, email verification, password reset) are not routed by the
/// platform to the app: the intent filters and associated domains cover only `/app/payments/`
/// and the product paths.
@Riverpod(keepAlive: true)
class AppLinkListener extends _$AppLinkListener {
  final _queue = <DeepLink>[];
  bool _scheduled = false;

  @override
  void build() {
    final subscription = ref
        .read(linkSourceProvider)
        .links
        .listen(_onUri, onError: (Object _) {});
    final router = ref.read(appRouterProvider);
    router.routerDelegate.addListener(_flush);
    ref.onDispose(() {
      router.routerDelegate.removeListener(_flush);
      unawaited(subscription.cancel());
    });
  }

  void _onUri(Uri uri) {
    final link = parseDeepLink(
      uri,
      appLinkHost: ref.read(appEnvProvider).appLinkHost,
    );
    if (link == null) {
      return;
    }
    _queue.add(link);
    _flush();
  }

  /// Paths on which a link has to wait (launch, onboarding, availability notices).
  static bool _blocking(String path) =>
      path == Routes.splash.path ||
      path.startsWith('/onboarding') ||
      path == Routes.maintenance.path ||
      path == Routes.updateRequired.path;

  /// Dispatches waiting links once the app shows a normal page. Runs after the current route
  /// change has finished (a navigation started inside the router's own notification would be
  /// overwritten by it).
  void _flush() {
    if (_queue.isEmpty || _scheduled) {
      return;
    }
    _scheduled = true;
    unawaited(
      Future<void>(() {
        _scheduled = false;
        if (_queue.isEmpty || !ref.mounted) {
          return;
        }
        final router = ref.read(appRouterProvider);
        final configuration = router.routerDelegate.currentConfiguration;
        if (configuration.isEmpty || _blocking(configuration.uri.path)) {
          return;
        }
        final links = List.of(_queue);
        _queue.clear();
        for (final link in links) {
          _dispatch(router, link);
        }
      }),
    );
  }

  void _dispatch(GoRouter router, DeepLink link) {
    switch (link) {
      case PaymentReturnLink(:final orderPublicId, :final cancelled):
        if (ref.read(sessionProvider) == SessionStatus.signedIn) {
          unawaited(
            ref
                .read(paymentFlowProvider.notifier)
                .handleReturn(orderPublicId, cancelled: cancelled),
          );
        }
        router.go(Routes.paymentLocation(orderPublicId));
      case ProductLink(:final location):
        unawaited(router.push(location));
      case GuestOrderAccessLink() || VerifyEmailLink() || ResetPasswordLink():
        break;
    }
  }
}
