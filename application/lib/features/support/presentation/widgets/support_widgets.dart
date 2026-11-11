import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/date_format.dart';
import 'package:application/features/support/domain/entities/support.dart';
import 'package:application/shared/widgets/status_pill.dart';
import 'package:flutter/material.dart';

/// A ticket status as the customer reads it (14.1): open and `pending_staff` wait for the
/// store, `pending_customer` was answered.
(String, StatusPillTone) ticketStatusView(
  AppLocalizations l10n,
  String status,
) => switch (status) {
  'pending_customer' => (l10n.ticketStatusAnswered, StatusPillTone.accent),
  'resolved' => (l10n.ticketStatusResolved, StatusPillTone.success),
  'closed' => (l10n.ticketStatusClosed, StatusPillTone.neutral),
  'open' => (l10n.ticketStatusOpen, StatusPillTone.info),
  _ => (l10n.ticketStatusWaiting, StatusPillTone.info),
};

/// A ticket topic of the API.
String ticketCategoryLabel(AppLocalizations l10n, String category) =>
    switch (category) {
      'order' => l10n.ticketCategoryOrder,
      'payment' => l10n.ticketCategoryPayment,
      'refund' => l10n.ticketCategoryRefund,
      'account' => l10n.ticketCategoryAccount,
      _ => l10n.ticketCategoryOther,
    };

/// A refund request status (15.2).
(String, StatusPillTone) refundRequestStatusView(
  AppLocalizations l10n,
  String status,
) => switch (status) {
  'approved' => (l10n.refundStatusApproved, StatusPillTone.info),
  'rejected' => (l10n.refundStatusRejected, StatusPillTone.danger),
  'completed' => (l10n.refundStatusCompleted, StatusPillTone.success),
  _ => (l10n.refundStatusRequested, StatusPillTone.warning),
};

/// "Order BX-1002" with the number as an LTR island.
String supportOrderLabel(AppLocalizations l10n, String number) =>
    l10n.supportOrderNumber(ltrIsolate(number));

/// A row of the ticket list (14.1): number (mono, LTR), status, subject, then the order or the
/// topic and the last activity date.
class TicketTile extends StatelessWidget {
  const TicketTile({required this.ticket, required this.onTap, super.key});

  final SupportTicket ticket;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final (status, tone) = ticketStatusView(l10n, ticket.status);
    final orderNumber = ticket.order?.orderNumber;
    final topic = orderNumber != null
        ? supportOrderLabel(l10n, orderNumber)
        : ticketCategoryLabel(l10n, ticket.category);
    final when = ticket.lastActivityAt ?? ticket.createdAt;
    final meta = ticket.lastActivityAt != null
        ? l10n.ticketUpdated(topic, AppDates.day(context, when))
        : '$topic · ${AppDates.day(context, when)}';
    return Semantics(
      button: true,
      label: '${ticket.subject}, $status, ${ticket.ticketNumber}, $meta',
      excludeSemantics: true,
      onTap: onTap,
      child: Material(
        color: colors.surfaceRaised,
        borderRadius: BorderRadius.circular(Radii.md),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsetsDirectional.fromSTEB(
              Spacing.s3,
              Spacing.s3,
              Spacing.s2,
              Spacing.s3,
            ),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Align(
                              alignment: AlignmentDirectional.centerStart,
                              child: Text(
                                ticket.ticketNumber,
                                textDirection: TextDirection.ltr,
                                style: type.monoSm.copyWith(
                                  color: colors.contentMuted,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ),
                          const SizedBox(width: Spacing.s2),
                          StatusPill(label: status, tone: tone),
                        ],
                      ),
                      const SizedBox(height: Spacing.s1),
                      Text(ticket.subject, style: type.headingSm),
                      const SizedBox(height: Spacing.s0_5),
                      Text(
                        meta,
                        style: type.bodySm.copyWith(
                          color: colors.contentSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: Spacing.s1),
                Icon(Icons.chevron_right, color: colors.contentSecondary),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// One message of the thread (14.3): the customer's on the end side on a volt tint, the
/// store's on the start side; "You · 10:02" or "Support · 10:20" above the text.
class MessageBubble extends StatelessWidget {
  const MessageBubble({required this.message, super.key});

  final TicketMessage message;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final mine = message.from == 'customer';
    final author = mine ? l10n.ticketFromYou : l10n.ticketFromSupport;
    final when = AppDates.dayTime(context, message.createdAt);
    return Align(
      alignment: mine
          ? AlignmentDirectional.centerEnd
          : AlignmentDirectional.centerStart,
      child: FractionallySizedBox(
        widthFactor: 0.8,
        alignment: mine
            ? AlignmentDirectional.centerEnd
            : AlignmentDirectional.centerStart,
        child: Align(
          alignment: mine
              ? AlignmentDirectional.centerEnd
              : AlignmentDirectional.centerStart,
          child: MergeSemantics(
            child: DecoratedBox(
              decoration: BoxDecoration(
                color: mine ? colors.accentPrimaryTint : colors.surfaceRaised,
                border: mine ? Border.all(color: colors.accentPrimary) : null,
                borderRadius: BorderRadius.circular(Radii.md),
              ),
              child: Padding(
                padding: const EdgeInsetsDirectional.symmetric(
                  horizontal: Spacing.s3,
                  vertical: Spacing.s2,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '$author · $when',
                      style: type.labelSm.copyWith(color: colors.contentMuted),
                    ),
                    const SizedBox(height: Spacing.s0_5),
                    Text(message.body, style: type.bodyMd),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// A refund request row (15.2): order, status and date.
class RefundRequestTile extends StatelessWidget {
  const RefundRequestTile({required this.request, this.onTap, super.key});

  final RefundRequest request;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final (status, tone) = refundRequestStatusView(l10n, request.status);
    final number = request.order.orderNumber;
    final title = number == null
        ? l10n.refundRequestUnknownOrder
        : supportOrderLabel(l10n, number);
    final date = AppDates.day(context, request.createdAt);
    final icon = switch (request.status) {
      'completed' => Icons.check,
      'rejected' => Icons.block,
      _ => Icons.autorenew,
    };
    return Semantics(
      button: onTap != null,
      label: '$title, $status, $date, ${request.reason}',
      excludeSemantics: true,
      onTap: onTap,
      child: Material(
        color: colors.surfaceRaised,
        borderRadius: BorderRadius.circular(Radii.md),
        clipBehavior: Clip.antiAlias,
        child: InkWell(
          onTap: onTap,
          child: ConstrainedBox(
            constraints: const BoxConstraints(minHeight: 64),
            child: Padding(
              padding: const EdgeInsetsDirectional.all(Spacing.s3),
              child: Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: colors.surfaceOverlay,
                      borderRadius: BorderRadius.circular(Radii.sm),
                    ),
                    child: Icon(icon, size: 22, color: colors.contentPrimary),
                  ),
                  const SizedBox(width: Spacing.s3),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(title, style: type.headingSm),
                        Text(
                          '$date · ${request.reason}',
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: type.bodySm.copyWith(
                            color: colors.contentSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: Spacing.s2),
                  StatusPill(label: status, tone: tone),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
