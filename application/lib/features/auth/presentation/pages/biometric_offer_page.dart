import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/auth/presentation/controllers/biometric_controllers.dart';
import 'package:application/features/auth/presentation/widgets/biometric_widgets.dart';
import 'package:application/features/auth/presentation/widgets/password_sheet.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/icon_badge.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// The one-time biometric unlock offer after the first sign-in (owner decision 6; not drawn in
/// M-A8, built from the auth page pattern). [next] is where the customer continues.
class BiometricOfferPage extends ConsumerWidget {
  const BiometricOfferPage({this.next, super.key});

  final String? next;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final enrolling = ref.watch(biometricOfferControllerProvider);
    final controller = ref.read(biometricOfferControllerProvider.notifier);
    final target = safeReturnLocation(next ?? Routes.home.path);

    Future<void> decline() async {
      await controller.decline();
      if (context.mounted) {
        context.go(target);
      }
    }

    Future<void> accept() async {
      final outcome = await controller.accept(
        prompt: biometricPrompt(l10n, l10n.biometricPromptEnrol),
        askPassword: (previous) =>
            showPasswordSheet(context, previous: previous),
      );
      if (context.mounted) {
        showEnrolOutcome(context, outcome);
        context.go(target);
      }
    }

    return FlowScaffold(
      appBar: MobileAppBar(
        leading: AppBarLeading.close,
        onLeading: () => unawaited(decline()),
      ),
      body: [
        const Align(
          alignment: AlignmentDirectional.centerStart,
          child: IconBadge(icon: Icons.fingerprint),
        ),
        const SizedBox(height: Spacing.s4),
        FlowHeading(
          title: l10n.biometricOfferTitle,
          body: l10n.biometricOfferBody,
        ),
      ],
      footer: [
        AppButton(
          label: l10n.biometricTurnOn,
          icon: Icons.fingerprint,
          expand: true,
          loading: enrolling,
          onPressed: () => unawaited(accept()),
        ),
        AppButton(
          label: l10n.actionNotNow,
          variant: AppButtonVariant.ghost,
          expand: true,
          onPressed: enrolling ? null : () => unawaited(decline()),
        ),
      ],
    );
  }
}
