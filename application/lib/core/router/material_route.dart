import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

/// A [GoRoute] whose page is Flutter's own [MaterialPage].
///
/// go_router 18 detects the app type with the separate `material_ui` package, whose `MaterialApp`
/// is not Flutter's: with a plain `builder` it does not recognise the app and gives every route a
/// `NoTransitionPage`. That loses the platform page transitions, the iOS edge swipe back and the
/// reduced-motion cross-fade of the theme (`ReducedMotionPageTransitionsBuilder`, M-A19). A
/// [MaterialPage] uses the app theme's `pageTransitionsTheme` again.
GoRoute materialRoute({
  required String path,
  required String name,
  required GoRouterWidgetBuilder builder,
  List<RouteBase> routes = const [],
  bool transition = true,
}) => GoRoute(
  path: path,
  name: name,
  routes: routes,
  pageBuilder: (context, state) => transition
      ? materialPage(state, builder(context, state))
      : instantPage(state, builder(context, state)),
);

/// The [MaterialPage] for [state], keyed and named as go_router keys and names its own pages.
MaterialPage<void> materialPage(GoRouterState state, Widget child) =>
    MaterialPage<void>(
      key: state.pageKey,
      name: state.name ?? state.path,
      arguments: <String, String>{
        ...state.pathParameters,
        ...state.uri.queryParameters,
      },
      restorationId: state.pageKey.value,
      child: child,
    );

/// A page that appears and leaves without a transition, keyed and named like [materialPage].
NoTransitionPage<void> instantPage(GoRouterState state, Widget child) =>
    NoTransitionPage<void>(
      key: state.pageKey,
      name: state.name ?? state.path,
      arguments: <String, String>{
        ...state.pathParameters,
        ...state.uri.queryParameters,
      },
      restorationId: state.pageKey.value,
      child: child,
    );
