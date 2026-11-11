import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/support/domain/entities/support.dart';
import 'package:application/features/support/presentation/controllers/support_controllers.dart';
import 'package:application/features/support/presentation/pages/support_page.dart';
import 'package:application/features/support/presentation/widgets/support_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/busy_indicator.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:application/shared/widgets/status_pill.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// A ticket's thread (14.3): status, the linked order, the messages (the customer's on the end
/// side) and the reply composer. Closed (14.4): the notice and "New request" instead of the
/// composer. Internal staff notes never reach the app (the server leaves them out).
class TicketThreadPage extends ConsumerStatefulWidget {
  const TicketThreadPage({required this.ticketPublicId, super.key});

  final String ticketPublicId;

  @override
  ConsumerState<TicketThreadPage> createState() => _TicketThreadPageState();
}

class _TicketThreadPageState extends ConsumerState<TicketThreadPage> {
  final _reply = TextEditingController();

  TicketThreadControllerProvider get _provider =>
      ticketThreadControllerProvider(widget.ticketPublicId);

  @override
  void dispose() {
    _reply.dispose();
    super.dispose();
  }

  void _back() =>
      context.canPop() ? context.pop() : context.go(Routes.accountSupport.path);

  Future<void> _send() async {
    final sent = await ref.read(_provider.notifier).reply(_reply.text);
    if (sent && mounted) {
      _reply.clear();
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(_provider);
    final controller = ref.read(_provider.notifier);
    final ticket = state.ticket.valueOrNull;
    final appBar = MobileAppBar(
      onLeading: _back,
      titleWidget: ticket == null
          ? null
          : Semantics(
              header: true,
              label: l10n.ticketThreadSemantics(ticket.ticketNumber),
              excludeSemantics: true,
              child: Text(
                ticket.ticketNumber,
                textDirection: TextDirection.ltr,
                style: context.type.headingMd,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
    );
    final Widget body;
    Widget? footer;
    switch (state.ticket) {
      case Loading():
        body = const Padding(
          padding: EdgeInsetsDirectional.all(Spacing.s4),
          child: SkeletonRegion(
            child: Column(
              children: [
                SkeletonBlock(height: 64, radius: Radii.md),
                SizedBox(height: Spacing.s3),
                SkeletonBlock(height: 96, radius: Radii.md),
              ],
            ),
          ),
        );
      case Failed(failure: NotFoundFailure()):
        body = Center(
          child: SingleChildScrollView(
            child: EmptyState(
              icon: Icons.search_off,
              title: l10n.ticketNotFoundTitle,
              actionLabel: l10n.accountSupport,
              onAction: () => context.go(Routes.accountSupport.path),
            ),
          ),
        );
      case Failed(:final failure):
        body = FailureView(
          failure: failure,
          errorTitle: l10n.ticketLoadError,
          onRetry: () => unawaited(controller.retry()),
        );
      case Loaded(value: final loaded):
        body = RefreshIndicator(
          onRefresh: controller.refresh,
          child: _Thread(ticket: loaded, closed: state.closed),
        );
        footer = state.closed
            ? StickyFooter(
                child: AppButton(
                  key: const ValueKey('ticket-closed-new'),
                  label: l10n.ticketNewAction,
                  icon: Icons.add,
                  expand: true,
                  onPressed: () => context.pushReplacement(
                    Routes.newTicketLocation(
                      order: loaded.order?.publicId,
                      category: loaded.category,
                    ),
                  ),
                ),
              )
            : _Composer(
                controller: _reply,
                sending: state.sending,
                errorText: state.problem != null
                    ? l10n.ticketReplyRequired
                    : state.bodyError,
                banner: state.bannerFailure == null
                    ? null
                    : failureDetail(l10n, state.bannerFailure!),
                onChanged: controller.edited,
                onSend: () => unawaited(_send()),
              );
    }
    return Scaffold(
      appBar: appBar,
      body: SafeArea(top: false, bottom: footer == null, child: body),
      bottomNavigationBar: footer,
    );
  }
}

class _Thread extends StatelessWidget {
  const _Thread({required this.ticket, required this.closed});

  final SupportTicket ticket;
  final bool closed;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final (status, tone) = closed
        ? (l10n.ticketStatusClosed, StatusPillTone.neutral)
        : ticketStatusView(l10n, ticket.status);
    final orderNumber = ticket.order?.orderNumber;
    final orderId = ticket.order?.publicId;
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsetsDirectional.fromSTEB(
        Spacing.s4,
        Spacing.s2,
        Spacing.s4,
        Spacing.s6,
      ),
      children: [
        if (closed) ...[
          StatusBanner(
            tone: BannerTone.info,
            title: l10n.ticketClosedTitle,
            body: l10n.ticketClosedBody,
          ),
          const SizedBox(height: Spacing.s3),
        ],
        // The status at the start and the order link at the end; on a narrow screen or with
        // large text the link wraps under the status instead of overflowing.
        Wrap(
          alignment: WrapAlignment.spaceBetween,
          crossAxisAlignment: WrapCrossAlignment.center,
          spacing: Spacing.s2,
          children: [
            StatusPill(label: status, tone: tone),
            if (orderNumber != null && orderId != null)
              TextButton(
                key: const ValueKey('ticket-order-link'),
                style: TextButton.styleFrom(
                  minimumSize: const Size(48, 48),
                  foregroundColor: colors.accentText,
                ),
                onPressed: () =>
                    unawaited(context.push(Routes.orderLocation(orderId))),
                child: Text(supportOrderLabel(l10n, orderNumber)),
              ),
          ],
        ),
        const SizedBox(height: Spacing.s1),
        Semantics(
          header: true,
          child: Text(ticket.subject, style: type.headingSm),
        ),
        Text(
          ticketCategoryLabel(l10n, ticket.category),
          style: type.bodySm.copyWith(color: colors.contentSecondary),
        ),
        for (final message in ticket.messages) ...[
          const SizedBox(height: Spacing.s3),
          MessageBubble(
            key: ValueKey('ticket-message-${message.id}'),
            message: message,
          ),
        ],
      ],
    );
  }
}

/// The reply composer (14.3): a multi-line field and the 48dp volt send button.
class _Composer extends StatelessWidget {
  const _Composer({
    required this.controller,
    required this.sending,
    required this.onChanged,
    required this.onSend,
    this.errorText,
    this.banner,
  });

  final TextEditingController controller;
  final bool sending;
  final VoidCallback onChanged;
  final VoidCallback onSend;
  final String? errorText;
  final String? banner;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final error = errorText;
    return DecoratedBox(
      decoration: BoxDecoration(
        color: colors.surfaceBase,
        border: Border(top: BorderSide(color: colors.lineSubtle)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsetsDirectional.fromSTEB(
            Spacing.s4,
            Spacing.s3,
            Spacing.s4,
            Spacing.s3,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (banner != null) ...[
                StatusBanner(tone: BannerTone.danger, title: banner!),
                const SizedBox(height: Spacing.s2),
              ],
              Row(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Expanded(
                    child: Semantics(
                      label: l10n.ticketReplyHint,
                      textField: true,
                      child: TextField(
                        key: const ValueKey('ticket-reply'),
                        controller: controller,
                        enabled: !sending,
                        minLines: 1,
                        maxLines: 5,
                        maxLength: ticketBodyMax,
                        maxLengthEnforcement: MaxLengthEnforcement.enforced,
                        keyboardType: TextInputType.multiline,
                        textCapitalization: TextCapitalization.sentences,
                        style: type.bodyMd,
                        cursorColor: colors.accentPrimary,
                        onChanged: (_) => onChanged(),
                        decoration: InputDecoration(
                          hintText: l10n.ticketReplyHint,
                          counterText: '',
                          isDense: true,
                          // A 48dp tap target even on one line (Android and WCAG target size).
                          constraints: const BoxConstraints(minHeight: 48),
                          filled: true,
                          fillColor: colors.surfaceSunken,
                          contentPadding: const EdgeInsetsDirectional.symmetric(
                            horizontal: Spacing.s3,
                            vertical: Spacing.s3,
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(Radii.sm),
                            borderSide: BorderSide(
                              color: error == null
                                  ? colors.lineStrong
                                  : colors.statusDanger,
                            ),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(Radii.sm),
                            borderSide: BorderSide(
                              color: colors.focusRing,
                              width: 1.5,
                            ),
                          ),
                          disabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(Radii.sm),
                            borderSide: BorderSide(color: colors.lineSubtle),
                          ),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: Spacing.s2),
                  SizedBox.square(
                    dimension: 48,
                    child: IconButton.filled(
                      key: const ValueKey('ticket-send'),
                      tooltip: l10n.ticketSendReply,
                      style: IconButton.styleFrom(
                        backgroundColor: colors.accentPrimary,
                        foregroundColor: colors.contentOnAccent,
                      ),
                      onPressed: sending ? null : onSend,
                      icon: sending
                          ? BusyIndicator(
                              color: colors.contentOnAccent,
                              excludeSemantics: true,
                            )
                          : const Icon(Icons.send_outlined),
                    ),
                  ),
                ],
              ),
              if (error != null) ...[
                const SizedBox(height: Spacing.s1),
                Semantics(
                  liveRegion: true,
                  child: Text(
                    error,
                    style: type.bodyCaption.copyWith(
                      color: colors.statusDanger,
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
