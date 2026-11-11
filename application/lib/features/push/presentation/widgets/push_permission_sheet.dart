import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/push/presentation/controllers/push_controllers.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/sheet_header.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// The push explainer (M-A18): what the app will send, before the system prompt. True when the
/// customer chose "Turn on notifications".
Future<bool> showPushPermissionSheet(BuildContext context) async =>
    await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: context.colors.surfaceRaised,
      builder: (context) => const PushPermissionSheet(),
    ) ??
    false;

class PushPermissionSheet extends StatelessWidget {
  const PushPermissionSheet({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    Widget point(IconData icon, String text) => Padding(
      padding: const EdgeInsetsDirectional.only(bottom: Spacing.s3),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 20, color: colors.accentText),
          const SizedBox(width: Spacing.s3),
          Expanded(
            child: Text(
              text,
              style: type.bodyMd.copyWith(color: colors.contentSecondary),
            ),
          ),
        ],
      ),
    );
    return SafeArea(
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            SheetHeader(
              title: l10n.pushExplainerTitle,
              onClose: () => Navigator.of(context).pop(false),
            ),
            Padding(
              padding: const EdgeInsetsDirectional.fromSTEB(
                Spacing.s4,
                Spacing.s2,
                Spacing.s4,
                Spacing.s4,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    l10n.pushExplainerBody,
                    style: type.bodyMd.copyWith(color: colors.contentPrimary),
                  ),
                  const SizedBox(height: Spacing.s4),
                  point(Icons.inventory_2_outlined, l10n.pushExplainerOrders),
                  point(
                    Icons.credit_card_off_outlined,
                    l10n.pushExplainerPayments,
                  ),
                  point(
                    Icons.support_agent_outlined,
                    l10n.pushExplainerSupport,
                  ),
                  point(Icons.lock_outline, l10n.pushExplainerNoCodes),
                  const SizedBox(height: Spacing.s2),
                  Text(
                    l10n.pushExplainerSettingsNote,
                    style: type.bodySm.copyWith(color: colors.contentMuted),
                  ),
                  const SizedBox(height: Spacing.s5),
                  AppButton(
                    label: l10n.pushExplainerAllow,
                    icon: Icons.notifications_active_outlined,
                    expand: true,
                    onPressed: () => Navigator.of(context).pop(true),
                  ),
                  const SizedBox(height: Spacing.s2),
                  AppButton(
                    label: l10n.pushExplainerNotNow,
                    variant: AppButtonVariant.ghost,
                    expand: true,
                    onPressed: () => Navigator.of(context).pop(false),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Placed on the paid result (08.1): offers the explainer once, after the first paid order, then
/// the system prompt on "Turn on". Renders nothing.
class PushOfferOnPaid extends ConsumerStatefulWidget {
  const PushOfferOnPaid({super.key});

  @override
  ConsumerState<PushOfferOnPaid> createState() => _PushOfferOnPaidState();
}

class _PushOfferOnPaidState extends ConsumerState<PushOfferOnPaid> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => unawaited(_offer()));
  }

  Future<void> _offer() async {
    final prompt = ref.read(pushPromptProvider.notifier);
    if (!mounted || !await prompt.dueAfterPurchase() || !mounted) {
      return;
    }
    if (await showPushPermissionSheet(context)) {
      await prompt.accept();
    }
  }

  @override
  Widget build(BuildContext context) => const SizedBox.shrink();
}
