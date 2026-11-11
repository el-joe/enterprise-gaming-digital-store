import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/date_format.dart';
import 'package:application/features/support/domain/entities/support.dart';
import 'package:application/features/support/presentation/controllers/support_controllers.dart';
import 'package:application/features/support/presentation/widgets/support_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/paged_list_view.dart';
import 'package:application/shared/widgets/select_field.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:application/shared/widgets/subject_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Request a refund (15.1): the order card, what went wrong (10 to 2000 characters), an
/// optional linked support request, and how refunds work. The server's answers of 15.2
/// (`refund_request_open`, `refund_not_allowed`, an order that is not the customer's) show as
/// banners. The API refunds by request (staff choose the units), so there is no unit choice.
class RefundRequestPage extends ConsumerStatefulWidget {
  const RefundRequestPage({required this.orderPublicId, super.key});

  final String orderPublicId;

  @override
  ConsumerState<RefundRequestPage> createState() => _RefundRequestPageState();
}

class _RefundRequestPageState extends ConsumerState<RefundRequestPage> {
  final _reason = TextEditingController();

  RefundRequestFormControllerProvider get _provider =>
      refundRequestFormControllerProvider(widget.orderPublicId);

  @override
  void dispose() {
    _reason.dispose();
    super.dispose();
  }

  void _close() => context.canPop()
      ? context.pop()
      : context.go(Routes.orderLocation(widget.orderPublicId));

  Future<void> _submit() async {
    FocusScope.of(context).unfocus();
    final request = await ref
        .read(_provider.notifier)
        .submit(reason: _reason.text);
    if (request == null || !mounted) {
      return;
    }
    context.pushReplacement(
      Uri(
        path: Routes.refundRequests.path,
        queryParameters: {RefundRequestsPage.sentParameter: '1'},
      ).toString(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(_provider);
    final controller = ref.read(_provider.notifier);
    final appBar = MobileAppBar(
      leading: AppBarLeading.close,
      title: l10n.refundRequestTitle,
      onLeading: _close,
    );
    switch (state.data) {
      case Loading():
        return Scaffold(
          appBar: appBar,
          body: const Padding(
            padding: EdgeInsetsDirectional.all(Spacing.s4),
            child: SkeletonRegion(
              child: Column(
                children: [
                  SkeletonBlock(height: 64, radius: Radii.md),
                  SizedBox(height: Spacing.s4),
                  SkeletonBlock(height: 120, radius: Radii.md),
                ],
              ),
            ),
          ),
        );
      case Failed(failure: NotFoundFailure()):
        return Scaffold(
          appBar: appBar,
          body: Center(
            child: SingleChildScrollView(
              child: EmptyState(
                icon: Icons.search_off,
                title: l10n.orderNotFoundTitle,
                body: l10n.orderNotFoundBody,
                actionLabel: l10n.ordersTitle,
                onAction: () => context.go(Routes.orders.path),
              ),
            ),
          ),
        );
      case Failed(:final failure):
        return Scaffold(
          appBar: appBar,
          body: FailureView(
            failure: failure,
            errorTitle: l10n.orderLoadErrorTitle,
            onRetry: () => unawaited(controller.retry()),
          ),
        );
      case Loaded(value: final data):
        final summary = data.order.summary;
        final paidAt = summary.paidAt ?? summary.createdAt;
        final banner = state.bannerFailure;
        String? reasonError() => switch (state.problem) {
          FormProblem.required => l10n.fieldRequired,
          FormProblem.tooShort => l10n.refundReasonTooShort(refundReasonMin),
          null => state.serverError('reason'),
        };
        final ticketNumber = [
          for (final ticket in data.tickets)
            if (ticket.publicId == state.ticketPublicId) ticket.ticketNumber,
        ].firstOrNull;
        return FlowScaffold(
          appBar: appBar,
          body: [
            if (banner != null) ...[
              _RefundFailureBanner(failure: banner),
              const SizedBox(height: Spacing.s4),
            ],
            SubjectCard(
              icon: Icons.receipt_long_outlined,
              title: supportOrderLabel(l10n, summary.number),
              subtitle: l10n.refundOrderPaid(
                ltrIsolate(moneyText(summary.displayTotal)),
                AppDates.day(context, paidAt),
              ),
            ),
            const SizedBox(height: Spacing.s4),
            AppTextField(
              key: const ValueKey('refund-reason'),
              label: l10n.refundReasonLabel,
              controller: _reason,
              minLines: 3,
              maxLines: 8,
              maxLength: refundReasonMax,
              keyboardType: TextInputType.multiline,
              textCapitalization: TextCapitalization.sentences,
              helperText: l10n.refundReasonHelper(
                refundReasonMin,
                refundReasonMax,
              ),
              errorText: reasonError(),
              onChanged: (_) => controller.edited(),
            ),
            if (data.tickets.isNotEmpty) ...[
              const SizedBox(height: Spacing.s3),
              SelectField<String?>(
                key: const ValueKey('refund-ticket'),
                label: l10n.refundTicketLabel,
                valueText: ticketNumber == null
                    ? l10n.refundTicketNone
                    : ltrIsolate(ticketNumber),
                selected: state.ticketPublicId,
                errorText: state.serverError('support_ticket_public_id'),
                options: [
                  SelectOption(value: null, title: l10n.refundTicketNone),
                  for (final ticket in data.tickets)
                    SelectOption(
                      value: ticket.publicId,
                      title: ltrIsolate(ticket.ticketNumber),
                      detail: ticket.subject,
                    ),
                ],
                onSelected: controller.setTicket,
              ),
            ],
            const SizedBox(height: Spacing.s4),
            StatusBanner(
              tone: BannerTone.info,
              title: l10n.refundHowTitle,
              body: l10n.refundHowBody,
            ),
          ],
          footer: [
            AppButton(
              key: const ValueKey('refund-submit'),
              label: l10n.refundSendAction,
              expand: true,
              loading: state.saving,
              onPressed: () => unawaited(_submit()),
            ),
          ],
        );
    }
  }
}

/// The 15.2 failures: already open (with "View refund requests"), not refundable, and the rest.
class _RefundFailureBanner extends StatelessWidget {
  const _RefundFailureBanner({required this.failure});

  final Failure failure;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    return switch (failure) {
      ConflictFailure(code: 'refund_request_open') => Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          StatusBanner(
            tone: BannerTone.warning,
            title: l10n.refundOpenTitle,
            body: l10n.refundOpenBody,
          ),
          const SizedBox(height: Spacing.s2),
          AppButton(
            label: l10n.refundRequestsTitle,
            variant: AppButtonVariant.secondary,
            expand: true,
            onPressed: () =>
                context.pushReplacement(Routes.refundRequests.path),
          ),
        ],
      ),
      ConflictFailure(code: 'refund_not_allowed', :final message) =>
        StatusBanner(
          tone: BannerTone.danger,
          title: l10n.refundNotAllowedTitle,
          body: message,
        ),
      final ValidationFailure validation
          when validation.firstErrorFor('order_public_id') != null =>
        StatusBanner(
          tone: BannerTone.danger,
          title: l10n.refundOrderRefusedTitle,
          body: validation.firstErrorFor('order_public_id'),
        ),
      _ => StatusBanner(
        tone: BannerTone.danger,
        title: failureDetail(l10n, failure),
      ),
    };
  }
}

/// The customer's refund requests (15.2) with their status; after a request was sent, the
/// "Request sent" note on top.
class RefundRequestsPage extends ConsumerWidget {
  const RefundRequestsPage({this.sent = false, super.key});

  /// Query parameter set after a request was sent.
  static const sentParameter = 'sent';

  final bool sent;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final state = ref.watch(refundRequestsControllerProvider);
    final controller = ref.read(refundRequestsControllerProvider.notifier);
    return Scaffold(
      appBar: MobileAppBar(
        title: l10n.refundRequestsTitle,
        onLeading: () =>
            context.canPop() ? context.pop() : context.go(Routes.account.path),
      ),
      body: SafeArea(
        top: false,
        child: PagedListView<RefundRequest>(
          state: state,
          skeletonHeight: 64,
          emptyKeepsHeader: true,
          header: [
            if (sent) ...[
              StatusBanner(
                tone: BannerTone.success,
                title: l10n.refundSentTitle,
                body: l10n.refundSentBody,
              ),
              const SizedBox(height: Spacing.s3),
            ],
          ],
          errorTitle: l10n.refundRequestsLoadError,
          moreFailedTitle: l10n.listMoreFailed,
          onRetry: () => unawaited(controller.retry()),
          onRefresh: controller.refresh,
          onLoadMore: () => unawaited(controller.loadMore()),
          empty: EmptyState(
            icon: Icons.currency_exchange,
            title: l10n.refundRequestsEmptyTitle,
            body: l10n.refundRequestsEmptyBody,
          ),
          itemBuilder: (context, request) {
            final order = request.order.publicId;
            return RefundRequestTile(
              key: ValueKey('refund-request-${request.id}'),
              request: request,
              onTap: order == null
                  ? null
                  : () => unawaited(context.push(Routes.orderLocation(order))),
            );
          },
        ),
      ),
    );
  }
}
