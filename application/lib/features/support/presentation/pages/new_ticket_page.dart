import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/features/support/presentation/controllers/support_controllers.dart';
import 'package:application/features/support/presentation/widgets/support_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_chip.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/select_field.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// "BX-1002 · $25.00" for the order picker (number and amount as LTR islands).
String orderOptionLabel(OrderSummary order) =>
    '${ltrIsolate(order.number)} · ${ltrIsolate(moneyText(order.displayTotal))}';

/// New support request (14.2): topic chips, the optional linked order, subject and message.
/// No attachments (the API has no upload): the note says so.
class NewTicketPage extends ConsumerStatefulWidget {
  const NewTicketPage({this.order, this.category, super.key});

  final String? order;
  final String? category;

  @override
  ConsumerState<NewTicketPage> createState() => _NewTicketPageState();
}

class _NewTicketPageState extends ConsumerState<NewTicketPage> {
  final _subject = TextEditingController();
  final _body = TextEditingController();

  NewTicketControllerProvider get _provider => newTicketControllerProvider(
    order: widget.order,
    category: widget.category,
  );

  @override
  void dispose() {
    _subject.dispose();
    _body.dispose();
    super.dispose();
  }

  void _close() =>
      context.canPop() ? context.pop() : context.go(Routes.accountSupport.path);

  Future<void> _submit() async {
    FocusScope.of(context).unfocus();
    final ticket = await ref
        .read(_provider.notifier)
        .submit(subject: _subject.text, body: _body.text);
    if (ticket == null || !mounted) {
      return;
    }
    context.pushReplacement(Routes.ticketLocation(ticket.publicId));
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(_provider);
    final controller = ref.read(_provider.notifier);
    final banner = state.bannerFailure;
    final orders = state.orders.valueOrNull ?? const <OrderSummary>[];
    final selected = state.selectedOrder;

    String? error(String field) => switch (state.problems[field]) {
      FormProblem.required => l10n.fieldRequired,
      FormProblem.tooShort => l10n.ticketSubjectTooShort(ticketSubjectMin),
      null => state.serverError(field),
    };

    return FlowScaffold(
      appBar: MobileAppBar(
        leading: AppBarLeading.close,
        title: l10n.ticketNewTitle,
        onLeading: _close,
      ),
      body: [
        if (banner != null) ...[
          StatusBanner(
            tone: BannerTone.danger,
            title: failureDetail(l10n, banner),
          ),
          const SizedBox(height: Spacing.s4),
        ],
        Text(
          l10n.ticketTopicLabel,
          style: context.type.labelMd.copyWith(
            color: context.colors.contentSecondary,
          ),
        ),
        const SizedBox(height: Spacing.s1),
        Wrap(
          spacing: Spacing.s2,
          runSpacing: Spacing.s2,
          children: [
            for (final category in ticketCategories)
              AppChip(
                key: ValueKey('ticket-category-$category'),
                label: ticketCategoryLabel(l10n, category),
                selected: state.category == category,
                onTap: () => controller.setCategory(category),
              ),
          ],
        ),
        if (error('category') case final message?) ...[
          const SizedBox(height: Spacing.s1),
          Text(
            message,
            style: context.type.bodyCaption.copyWith(
              color: context.colors.statusDanger,
            ),
          ),
        ],
        const SizedBox(height: Spacing.s4),
        SelectField<String?>(
          key: const ValueKey('ticket-order'),
          label: l10n.ticketOrderLabel,
          valueText: selected != null
              ? orderOptionLabel(selected)
              : state.orderPublicId != null
              ? l10n.ticketOrderLinked
              : l10n.ticketOrderNone,
          selected: state.orderPublicId,
          enabled: state.orders is Loaded,
          errorText: error('order_public_id'),
          helperText: state.orders is Failed ? l10n.ticketOrdersFailed : null,
          options: [
            SelectOption(value: null, title: l10n.ticketOrderNone),
            for (final order in orders)
              SelectOption(
                value: order.publicId,
                title: orderOptionLabel(order),
              ),
          ],
          onSelected: controller.setOrder,
        ),
        const SizedBox(height: Spacing.s3),
        AppTextField(
          key: const ValueKey('ticket-subject'),
          label: l10n.ticketSubjectLabel,
          controller: _subject,
          maxLength: ticketSubjectMax,
          errorText: error('subject'),
          textCapitalization: TextCapitalization.sentences,
          textInputAction: TextInputAction.next,
          onChanged: (_) => controller.edited(),
        ),
        const SizedBox(height: Spacing.s3),
        AppTextField(
          key: const ValueKey('ticket-body'),
          label: l10n.ticketMessageLabel,
          controller: _body,
          minLines: 4,
          maxLines: 10,
          maxLength: ticketBodyMax,
          keyboardType: TextInputType.multiline,
          textCapitalization: TextCapitalization.sentences,
          helperText: l10n.ticketMessageHelper,
          errorText: error('body'),
          onChanged: (_) => controller.edited(),
        ),
      ],
      footer: [
        AppButton(
          key: const ValueKey('ticket-submit'),
          label: l10n.ticketSendAction,
          icon: Icons.send_outlined,
          expand: true,
          loading: state.saving,
          onPressed: () => unawaited(_submit()),
        ),
      ],
    );
  }
}
