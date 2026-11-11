import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/sheet_header.dart';
import 'package:flutter/material.dart';

/// Sends the typed password; returns null when done, else the failure to show (the sheet stays
/// open for another try).
typedef PasswordAction = Future<Failure?> Function(String password);

/// A password sheet for a protected change (18.4 turn off two-step verification, 18.5 sign out
/// of all other devices): title, explanation, the password field with the server's message for
/// `current_password`, the action and a cancel button. Returns true when the action succeeded.
Future<bool> showPasswordActionSheet(
  BuildContext context, {
  required String title,
  required String body,
  required String actionLabel,
  required String cancelLabel,
  required PasswordAction onSubmit,
  bool destructive = true,
}) async {
  final done = await showModalBottomSheet<bool>(
    context: context,
    isScrollControlled: true,
    useRootNavigator: true,
    backgroundColor: context.colors.surfaceRaised,
    builder: (context) => PasswordActionSheet(
      title: title,
      body: body,
      actionLabel: actionLabel,
      cancelLabel: cancelLabel,
      onSubmit: onSubmit,
      destructive: destructive,
    ),
  );
  return done ?? false;
}

class PasswordActionSheet extends StatefulWidget {
  const PasswordActionSheet({
    required this.title,
    required this.body,
    required this.actionLabel,
    required this.cancelLabel,
    required this.onSubmit,
    this.destructive = true,
    super.key,
  });

  final String title;
  final String body;
  final String actionLabel;
  final String cancelLabel;
  final PasswordAction onSubmit;
  final bool destructive;

  @override
  State<PasswordActionSheet> createState() => _PasswordActionSheetState();
}

class _PasswordActionSheetState extends State<PasswordActionSheet> {
  final _password = TextEditingController();
  bool _busy = false;
  Failure? _failure;

  @override
  void dispose() {
    _password.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (_busy || _password.text.isEmpty) {
      return;
    }
    setState(() {
      _busy = true;
      _failure = null;
    });
    final failure = await widget.onSubmit(_password.text);
    if (!mounted) {
      return;
    }
    if (failure == null) {
      Navigator.of(context).pop(true);
      return;
    }
    setState(() {
      _busy = false;
      _failure = failure;
    });
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final failure = _failure;
    final error = switch (failure) {
      null => null,
      ValidationFailure() =>
        failure.firstErrorFor('current_password') ??
            failure.firstErrorFor('password') ??
            failureMessage(l10n, failure),
      _ => failureDetail(l10n, failure),
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
            SheetHeader(title: widget.title),
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
                    widget.body,
                    style: context.type.bodyMd.copyWith(
                      color: context.colors.contentSecondary,
                    ),
                  ),
                  const SizedBox(height: Spacing.s4),
                  AppTextField(
                    key: const ValueKey('password-action-field'),
                    label: l10n.passwordLabel,
                    controller: _password,
                    obscure: true,
                    errorText: error,
                    autofillHints: const [AutofillHints.password],
                    textInputAction: TextInputAction.done,
                    onSubmitted: (_) => unawaited(_submit()),
                  ),
                  const SizedBox(height: Spacing.s5),
                  AppButton(
                    key: const ValueKey('password-action-submit'),
                    label: widget.actionLabel,
                    expand: true,
                    loading: _busy,
                    variant: widget.destructive
                        ? AppButtonVariant.danger
                        : AppButtonVariant.primary,
                    onPressed: () => unawaited(_submit()),
                  ),
                  const SizedBox(height: Spacing.s2),
                  AppButton(
                    label: widget.cancelLabel,
                    expand: true,
                    variant: AppButtonVariant.ghost,
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
