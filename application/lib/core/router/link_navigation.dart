import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/router/link_targets.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Follows a storefront [link] (banner, home item): in-app targets through go_router, `url`
/// targets in the system browser (https only). Unknown targets do nothing.
void followLink(BuildContext context, WidgetRef ref, LinkTarget link) {
  switch (destinationFor(link)) {
    case InAppDestination(:final location):
      openLocation(context, location);
    case ExternalDestination(:final url):
      final message = context.l10n.linkOpenFailed;
      unawaited(
        ref.read(nativeBridgeProvider).openExternalUrl(url).then((opened) {
          if (!opened && context.mounted) {
            showAppSnackbar(
              context,
              message: message,
              tone: SnackbarTone.danger,
            );
          }
        }),
      );
    case null:
      break;
  }
}

/// Opens an in-app [location]: product detail is pushed above the tabs (back returns here);
/// tab locations switch to their tab.
void openLocation(BuildContext context, String location) {
  if (location.startsWith('/products/')) {
    unawaited(context.push(location));
  } else {
    context.go(location);
  }
}
