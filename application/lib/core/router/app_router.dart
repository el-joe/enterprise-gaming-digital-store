import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/router/shell_pages.dart';
import 'package:flutter/foundation.dart';
import 'package:go_router/go_router.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'app_router.g.dart';

/// The app's go_router. Guard inputs are providers; any change re-runs [redirectFor].
@Riverpod(keepAlive: true)
GoRouter appRouter(Ref ref) {
  final refresh = ValueNotifier<int>(0);
  void bump() => refresh.value++;
  ref
    ..listen(sessionProvider, (_, _) => bump())
    ..listen(availabilityProvider, (_, _) => bump())
    ..listen(emailVerificationProvider, (_, _) => bump())
    ..onDispose(refresh.dispose);

  final router = GoRouter(
    initialLocation: Routes.home.path,
    refreshListenable: refresh,
    redirect: (context, state) => redirectFor(
      state: GuardState(
        session: ref.read(sessionProvider),
        availability: ref.read(availabilityProvider),
        emailVerified: ref.read(emailVerificationProvider),
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
        path: Routes.maintenance.path,
        name: Routes.maintenance.name,
        builder: (context, state) =>
            const NoticePage(kind: NoticeKind.maintenance),
      ),
      GoRoute(
        path: Routes.updateRequired.path,
        name: Routes.updateRequired.name,
        builder: (context, state) =>
            const NoticePage(kind: NoticeKind.updateRequired),
      ),
    ],
    errorBuilder: (context, state) =>
        const NoticePage(kind: NoticeKind.notFound),
  );
  ref.onDispose(router.dispose);
  return router;
}
