import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/support/domain/entities/support.dart';
import 'package:application/features/support/presentation/controllers/support_controllers.dart';
import 'package:application/features/support/presentation/widgets/support_widgets.dart';
import 'package:application/features/support/support_providers.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/paged_list_view.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Opens the web help page, with a snackbar when it cannot.
Future<void> openWebHelp(BuildContext context, WidgetRef ref) async {
  final message = context.l10n.linkOpenFailed;
  final opened = await ref.read(openWebSupportProvider)();
  if (!opened && context.mounted) {
    showAppSnackbar(context, message: message, tone: SnackbarTone.danger);
  }
}

/// Support (14.1, 14.4): the customer's tickets with their status, newest activity first, and
/// "New request". Signed out it asks to sign in (tickets need an account) and offers the web
/// help page; with `support_enabled` off it says support is not in the app.
class SupportPage extends ConsumerWidget {
  const SupportPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final signedIn = ref.watch(sessionProvider) == SessionStatus.signedIn;
    final enabled = ref.watch(supportEnabledProvider);
    final hasWebHelp = ref.watch(supportLinkAvailableProvider);
    final appBar = MobileAppBar(
      title: l10n.accountSupport,
      onLeading: () =>
          context.canPop() ? context.pop() : context.go(Routes.account.path),
    );
    if (!enabled || !signedIn) {
      return Scaffold(
        appBar: appBar,
        body: Center(
          child: SingleChildScrollView(
            child: Column(
              children: [
                EmptyState(
                  icon: Icons.support_agent,
                  title: enabled
                      ? l10n.supportSignedOutTitle
                      : l10n.supportUnavailableTitle,
                  body: enabled
                      ? l10n.supportSignedOutBody
                      : l10n.supportUnavailableBody,
                  actionLabel: enabled ? l10n.signInAction : null,
                  onAction: enabled
                      ? () => unawaited(
                          context.push(
                            Uri(
                              path: Routes.login.path,
                              queryParameters: {
                                Routes.fromParameter:
                                    Routes.accountSupport.path,
                              },
                            ).toString(),
                          ),
                        )
                      : null,
                ),
                if (hasWebHelp)
                  Padding(
                    padding: const EdgeInsetsDirectional.symmetric(
                      horizontal: Spacing.s4,
                    ),
                    child: AppButton(
                      key: const ValueKey('support-web-help'),
                      label: l10n.settingsHelpCentre,
                      icon: Icons.open_in_new,
                      variant: AppButtonVariant.secondary,
                      expand: true,
                      onPressed: () => unawaited(openWebHelp(context, ref)),
                    ),
                  ),
              ],
            ),
          ),
        ),
      );
    }
    final state = ref.watch(ticketsControllerProvider);
    final controller = ref.read(ticketsControllerProvider.notifier);
    return Scaffold(
      appBar: appBar,
      body: SafeArea(
        top: false,
        bottom: false,
        child: PagedListView<SupportTicket>(
          state: state,
          skeletonHeight: 96,
          errorTitle: l10n.ticketsLoadError,
          moreFailedTitle: l10n.listMoreFailed,
          onRetry: () => unawaited(controller.retry()),
          onRefresh: controller.refresh,
          onLoadMore: () => unawaited(controller.loadMore()),
          empty: EmptyState(
            icon: Icons.confirmation_number_outlined,
            title: l10n.ticketsEmptyTitle,
            body: l10n.ticketsEmptyBody,
          ),
          itemBuilder: (context, ticket) => TicketTile(
            key: ValueKey('ticket-${ticket.publicId}'),
            ticket: ticket,
            onTap: () =>
                unawaited(context.push(Routes.ticketLocation(ticket.publicId))),
          ),
        ),
      ),
      bottomNavigationBar: StickyFooter(
        child: AppButton(
          key: const ValueKey('ticket-new'),
          label: l10n.ticketNewAction,
          icon: Icons.add,
          expand: true,
          onPressed: () => unawaited(context.push(Routes.newTicketLocation())),
        ),
      ),
    );
  }
}

/// A sticky footer on `surface-base` with a top hairline (the primary action of a list).
class StickyFooter extends StatelessWidget {
  const StickyFooter({required this.child, super.key});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return DecoratedBox(
      decoration: BoxDecoration(
        color: colors.surfaceBase,
        border: Border(top: BorderSide(color: colors.lineSubtle)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsetsDirectional.symmetric(
            horizontal: Spacing.s4,
            vertical: Spacing.s3,
          ),
          child: child,
        ),
      ),
    );
  }
}
