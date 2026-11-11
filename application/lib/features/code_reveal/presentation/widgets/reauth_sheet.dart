import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/code_reveal/presentation/controllers/reveal_controller.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/sheet_header.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// The re-auth password sheet (10.2, 10.3), bound to the reveal controller: "Confirm and reveal"
/// sends `confirm-password`; a wrong password keeps the sheet open with the server's message;
/// the controller closes the step on success. Closing the sheet cancels the step.
class ReauthSheet extends ConsumerStatefulWidget {
  const ReauthSheet({required this.provider, super.key});

  final RevealControllerProvider provider;

  @override
  ConsumerState<ReauthSheet> createState() => _ReauthSheetState();
}

class _ReauthSheetState extends ConsumerState<ReauthSheet> {
  final _password = TextEditingController();

  @override
  void dispose() {
    _password.dispose();
    super.dispose();
  }

  void _submit() => unawaited(
    ref.read(widget.provider.notifier).submitPassword(_password.text),
  );

  Future<void> _forgot() async {
    final url = ref.read(latestAppConfigProvider)?.links.forgotPassword;
    if (url != null) {
      await ref.read(nativeBridgeProvider).openExternalUrl(url);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final reauth = ref.watch(widget.provider.select((s) => s.reauth));
    // The step ended (confirmed or the screen left): close the sheet.
    ref.listen(widget.provider.select((s) => s.reauth), (previous, next) {
      // Only while this sheet is still the top route (a sheet the customer closed is already
      // popping, and a second pop would leave the code screen).
      if (previous != null &&
          next == null &&
          mounted &&
          (ModalRoute.of(context)?.isCurrent ?? false)) {
        unawaited(Navigator.of(context).maybePop());
      }
    });
    final failure = reauth?.failure;
    final error = switch (failure) {
      null => null,
      ValidationFailure() =>
        failure.firstErrorFor('password') ?? failureMessage(l10n, failure),
      _ => failureMessage(l10n, failure),
    };
    final busy = reauth?.busy ?? false;
    return Padding(
      padding: EdgeInsetsDirectional.only(
        bottom: MediaQuery.viewInsetsOf(context).bottom,
      ),
      child: SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            SheetHeader(
              title: l10n.reauthTitle,
              onClose: () => Navigator.of(context).maybePop(),
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
                    l10n.reauthBody,
                    style: context.type.bodyMd.copyWith(
                      color: context.colors.contentSecondary,
                    ),
                  ),
                  const SizedBox(height: Spacing.s4),
                  AppTextField(
                    label: l10n.passwordLabel,
                    controller: _password,
                    obscure: true,
                    errorText: error,
                    enabled: !busy,
                    autofillHints: const [AutofillHints.password],
                    textInputAction: TextInputAction.done,
                    onSubmitted: (_) => _submit(),
                  ),
                  const SizedBox(height: Spacing.s5),
                  AppButton(
                    label: l10n.reauthConfirm,
                    icon: Icons.lock_outline,
                    expand: true,
                    loading: busy,
                    onPressed: busy ? null : _submit,
                  ),
                  if (ref
                          .watch(latestAppConfigProvider)
                          ?.links
                          .forgotPassword !=
                      null) ...[
                    const SizedBox(height: Spacing.s2),
                    AppButton(
                      label: l10n.reauthForgot,
                      variant: AppButtonVariant.ghost,
                      expand: true,
                      onPressed: () => unawaited(_forgot()),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
