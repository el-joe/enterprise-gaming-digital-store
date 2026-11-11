import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/auth/presentation/controllers/session_user.dart';
import 'package:application/features/auth/presentation/controllers/sign_out_controller.dart';
import 'package:application/features/auth/presentation/widgets/biometric_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Account card on the foundation home until the Account tab and Security screen exist
/// (M-A16): sign in, or the signed-in email, the biometric unlock switch and sign out.
class AccountCard extends ConsumerWidget {
  const AccountCard({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final signedIn = ref.watch(sessionProvider) == SessionStatus.signedIn;
    final email = ref.watch(currentUserProvider)?.email;
    final signingOut = ref.watch(signOutControllerProvider);

    return DecoratedBox(
      decoration: BoxDecoration(
        color: colors.surfaceRaised,
        border: Border.all(color: colors.lineSubtle),
        borderRadius: BorderRadius.circular(Radii.md),
      ),
      child: Padding(
        padding: const EdgeInsetsDirectional.all(Spacing.s4),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Semantics(
              header: true,
              child: Text(l10n.homeAccountTitle, style: type.headingSm),
            ),
            const SizedBox(height: Spacing.s1),
            Text(
              !signedIn
                  ? l10n.homeSignedOut
                  : email == null
                  ? l10n.homeSignedIn
                  : l10n.homeSignedInAs(email),
              style: type.bodySm.copyWith(color: colors.contentSecondary),
            ),
            const SizedBox(height: Spacing.s3),
            if (!signedIn)
              AppButton(
                label: l10n.signInAction,
                onPressed: () => context.push(Routes.login.path),
              )
            else ...[
              const BiometricUnlockTile(),
              const SizedBox(height: Spacing.s2),
              AppButton(
                label: l10n.signOutAction,
                variant: AppButtonVariant.secondary,
                loading: signingOut,
                onPressed: () => unawaited(confirmSignOut(context, ref)),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

/// The sign-out confirmation (17.4), then sign out.
Future<void> confirmSignOut(BuildContext context, WidgetRef ref) async {
  final l10n = context.l10n;
  final confirmed = await showDialog<bool>(
    context: context,
    builder: (context) => AlertDialog(
      backgroundColor: context.colors.surfaceRaised,
      title: Text(l10n.signOutConfirmTitle, style: context.type.headingMd),
      content: Text(
        l10n.signOutConfirmBody,
        style: context.type.bodyMd.copyWith(
          color: context.colors.contentSecondary,
        ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(false),
          child: Text(l10n.actionCancel),
        ),
        FilledButton(
          onPressed: () => Navigator.of(context).pop(true),
          child: Text(l10n.signOutAction),
        ),
      ],
    ),
  );
  if (confirmed != true) {
    return;
  }
  await ref.read(signOutControllerProvider.notifier).signOut();
  if (context.mounted) {
    showAppSnackbar(context, message: l10n.signedOutMessage);
  }
}
