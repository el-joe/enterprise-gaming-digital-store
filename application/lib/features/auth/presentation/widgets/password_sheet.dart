import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/sheet_header.dart';
import 'package:flutter/material.dart';

/// The confirm-password sheet (re-auth, 10.2 / 10.3 style). Returns the typed password, or null
/// when dismissed. [previous] is the failure of the last attempt (wrong password).
Future<String?> showPasswordSheet(BuildContext context, {Failure? previous}) =>
    showModalBottomSheet<String>(
      context: context,
      isScrollControlled: true,
      backgroundColor: context.colors.surfaceRaised,
      builder: (context) => PasswordSheet(previous: previous),
    );

class PasswordSheet extends StatefulWidget {
  const PasswordSheet({this.previous, super.key});

  final Failure? previous;

  @override
  State<PasswordSheet> createState() => _PasswordSheetState();
}

class _PasswordSheetState extends State<PasswordSheet> {
  final _password = TextEditingController();

  @override
  void dispose() {
    _password.dispose();
    super.dispose();
  }

  void _submit() {
    if (_password.text.isNotEmpty) {
      Navigator.of(context).pop(_password.text);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final previous = widget.previous;
    final error = switch (previous) {
      null => null,
      ValidationFailure() =>
        previous.firstErrorFor('password') ?? failureMessage(l10n, previous),
      _ => failureMessage(l10n, previous),
    };
    return Padding(
      padding: EdgeInsetsDirectional.only(
        bottom: MediaQuery.viewInsetsOf(context).bottom,
      ),
      child: SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            SheetHeader(title: l10n.passwordSheetTitle),
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
                    l10n.passwordSheetBody,
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
                    autofillHints: const [AutofillHints.password],
                    textInputAction: TextInputAction.done,
                    onSubmitted: (_) => _submit(),
                  ),
                  const SizedBox(height: Spacing.s5),
                  AppButton(
                    label: l10n.confirmAction,
                    expand: true,
                    onPressed: _submit,
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
