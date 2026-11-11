import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/account/presentation/controllers/change_password_controller.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Change password (18.7): current, new and confirmation, the server's field errors beside the
/// fields, and the notice that other devices are signed out. When biometric unlock was on, the
/// Security screen opens with the note to turn it on again (ADR-040).
class ChangePasswordPage extends ConsumerStatefulWidget {
  const ChangePasswordPage({super.key});

  @override
  ConsumerState<ChangePasswordPage> createState() => _ChangePasswordPageState();
}

class _ChangePasswordPageState extends ConsumerState<ChangePasswordPage> {
  final _current = TextEditingController();
  final _password = TextEditingController();
  final _confirmation = TextEditingController();

  @override
  void dispose() {
    _current.dispose();
    _password.dispose();
    _confirmation.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final l10n = context.l10n;
    FocusScope.of(context).unfocus();
    final changed = await ref
        .read(changePasswordControllerProvider.notifier)
        .submit(
          currentPassword: _current.text,
          password: _password.text,
          confirmation: _confirmation.text,
        );
    if (changed == null || !mounted) {
      return;
    }
    _current.clear();
    _password.clear();
    _confirmation.clear();
    showAppSnackbar(context, message: l10n.passwordChanged);
    if (context.canPop()) {
      context.pop();
    } else {
      context.go(Routes.security.path);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(changePasswordControllerProvider);
    final controller = ref.read(changePasswordControllerProvider.notifier);
    final banner = state.bannerFailure;

    String? error(String field) => switch (state.problems[field]) {
      PasswordFieldProblem.required => l10n.fieldRequired,
      PasswordFieldProblem.mismatch => l10n.passwordMismatch,
      null => state.serverError(field),
    };

    return FlowScaffold(
      appBar: MobileAppBar(title: l10n.changePasswordTitle),
      body: [
        if (banner != null) ...[
          StatusBanner(
            tone: BannerTone.danger,
            title: failureDetail(l10n, banner),
          ),
          const SizedBox(height: Spacing.s4),
        ],
        AppTextField(
          key: const ValueKey('password-current'),
          label: l10n.currentPasswordLabel,
          controller: _current,
          obscure: true,
          errorText: error('current_password'),
          autofillHints: const [AutofillHints.password],
          textInputAction: TextInputAction.next,
          onChanged: (_) => controller.edited(),
        ),
        const SizedBox(height: Spacing.s3),
        AppTextField(
          key: const ValueKey('password-new'),
          label: l10n.newPasswordLabel,
          controller: _password,
          obscure: true,
          helperText: l10n.newPasswordHelper,
          errorText: error('password'),
          autofillHints: const [AutofillHints.newPassword],
          textInputAction: TextInputAction.next,
          onChanged: (_) => controller.edited(),
        ),
        const SizedBox(height: Spacing.s3),
        AppTextField(
          key: const ValueKey('password-confirmation'),
          label: l10n.confirmNewPasswordLabel,
          controller: _confirmation,
          obscure: true,
          errorText: error('password_confirmation'),
          autofillHints: const [AutofillHints.newPassword],
          textInputAction: TextInputAction.done,
          onChanged: (_) => controller.edited(),
          onSubmitted: (_) => unawaited(_submit()),
        ),
        const SizedBox(height: Spacing.s4),
        StatusBanner(
          tone: BannerTone.info,
          title: l10n.changePasswordNoticeTitle,
          body: l10n.changePasswordNoticeBody,
        ),
      ],
      footer: [
        AppButton(
          key: const ValueKey('password-submit'),
          label: l10n.changePasswordAction,
          expand: true,
          loading: state.saving,
          onPressed: () => unawaited(_submit()),
        ),
      ],
    );
  }
}
