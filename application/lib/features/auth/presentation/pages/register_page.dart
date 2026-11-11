import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/auth/presentation/controllers/register_controller.dart';
import 'package:application/features/auth/presentation/widgets/auth_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Create account (02.5, 02.6).
class RegisterPage extends ConsumerStatefulWidget {
  const RegisterPage({this.from, super.key});

  final String? from;

  @override
  ConsumerState<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends ConsumerState<RegisterPage> {
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _confirmation = TextEditingController();
  bool _terms = false;

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _password.dispose();
    _confirmation.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    FocusScope.of(context).unfocus();
    await ref
        .read(registerControllerProvider.notifier)
        .submit(
          RegisterInput(
            name: _name.text,
            email: _email.text,
            password: _password.text,
            passwordConfirmation: _confirmation.text,
            termsAccepted: _terms,
          ),
        );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final type = context.type;
    final colors = context.colors;
    final state = ref.watch(registerControllerProvider);
    final controller = ref.read(registerControllerProvider.notifier);
    final clock = ref.watch(clockProvider);
    final terms = ref.watch(latestAppConfigProvider)?.links.terms;
    String? errorOf(String field) =>
        fieldIssueText(l10n, state.fieldErrors[field]);
    final serverErrors = state.fieldErrors.values
        .where((issue) => issue.serverMessage != null)
        .length;
    final termsError = errorOf('terms');

    return FlowScaffold(
      appBar: MobileAppBar(
        title: l10n.registerTitle,
        onLeading: () =>
            context.canPop() ? context.pop() : context.go(Routes.login.path),
      ),
      body: [
        if (serverErrors > 0) ...[
          StatusBanner(
            tone: BannerTone.danger,
            title: l10n.formErrorsTitle(serverErrors),
            body: l10n.formErrorsBody,
          ),
          const SizedBox(height: Spacing.s4),
        ],
        AuthFailureBanner(
          state: state,
          clock: clock,
          title: l10n.signInFailedTitle,
          onRateLimitEnded: controller.rateLimitEnded,
        ),
        AutofillGroup(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              AppTextField(
                label: l10n.fullNameLabel,
                controller: _name,
                textInputAction: TextInputAction.next,
                textCapitalization: TextCapitalization.words,
                autofillHints: const [AutofillHints.name],
                errorText: errorOf('name'),
                onChanged: (_) => controller.edited('name'),
              ),
              const SizedBox(height: Spacing.s4),
              AppTextField(
                label: l10n.emailLabel,
                controller: _email,
                ltr: true,
                keyboardType: TextInputType.emailAddress,
                textInputAction: TextInputAction.next,
                autofillHints: const [AutofillHints.email],
                errorText: errorOf('email'),
                onChanged: (_) => controller.edited('email'),
              ),
              const SizedBox(height: Spacing.s4),
              AppTextField(
                label: l10n.passwordLabel,
                controller: _password,
                obscure: true,
                textInputAction: TextInputAction.next,
                autofillHints: const [AutofillHints.newPassword],
                helperText: l10n.passwordHelper,
                errorText: errorOf('password'),
                onChanged: (_) => controller.edited('password'),
              ),
              const SizedBox(height: Spacing.s4),
              AppTextField(
                label: l10n.confirmPasswordLabel,
                controller: _confirmation,
                obscure: true,
                textInputAction: TextInputAction.done,
                autofillHints: const [AutofillHints.newPassword],
                errorText: errorOf('password_confirmation'),
                onChanged: (_) => controller.edited('password_confirmation'),
                onSubmitted: (_) => unawaited(_submit()),
              ),
            ],
          ),
        ),
        const SizedBox(height: Spacing.s3),
        MergeSemantics(
          child: InkWell(
            onTap: () {
              setState(() => _terms = !_terms);
              controller.edited('terms');
            },
            child: ConstrainedBox(
              constraints: const BoxConstraints(minHeight: 48),
              child: Row(
                children: [
                  Checkbox(
                    value: _terms,
                    onChanged: (value) {
                      setState(() => _terms = value ?? false);
                      controller.edited('terms');
                    },
                  ),
                  Expanded(child: Text(l10n.acceptTerms, style: type.bodyMd)),
                ],
              ),
            ),
          ),
        ),
        if (termsError != null)
          Padding(
            padding: const EdgeInsetsDirectional.only(start: Spacing.s3),
            child: Semantics(
              liveRegion: true,
              child: Text(
                termsError,
                style: type.bodyCaption.copyWith(color: colors.statusDanger),
              ),
            ),
          ),
        if (terms != null)
          Align(
            alignment: AlignmentDirectional.centerStart,
            child: AuthLink(
              label: l10n.readTerms,
              onTap: () => unawaited(
                ref.read(nativeBridgeProvider).openExternalUrl(terms),
              ),
            ),
          ),
        const SizedBox(height: Spacing.s4),
        Wrap(
          alignment: WrapAlignment.center,
          crossAxisAlignment: WrapCrossAlignment.center,
          children: [
            Text(
              l10n.haveAccount,
              style: type.bodyMd.copyWith(color: colors.contentSecondary),
            ),
            const SizedBox(width: Spacing.s1),
            AuthLink(
              label: l10n.signInAction,
              onTap: () => context.go(
                Uri(
                  path: Routes.login.path,
                  queryParameters: {
                    if (widget.from != null) Routes.fromParameter: widget.from!,
                  },
                ).toString(),
              ),
            ),
          ],
        ),
      ],
      footer: [
        RateLimitedButton(
          state: state,
          clock: clock,
          child: AppButton(
            label: l10n.registerTitle,
            expand: true,
            loading: state.submitting,
            onPressed: () => unawaited(_submit()),
          ),
        ),
      ],
    );
  }
}
