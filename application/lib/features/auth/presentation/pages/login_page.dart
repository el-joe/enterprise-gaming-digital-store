import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/auth/presentation/controllers/login_controller.dart';
import 'package:application/features/auth/presentation/widgets/auth_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Sign in (02.1, 02.2). [from] is the protected location to return to.
class LoginPage extends ConsumerStatefulWidget {
  const LoginPage({this.from, super.key});

  final String? from;

  @override
  ConsumerState<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends ConsumerState<LoginPage> {
  final _email = TextEditingController();
  final _password = TextEditingController();

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  Map<String, String> get _carry => {
    if (widget.from != null) Routes.fromParameter: widget.from!,
  };

  Future<void> _submit() async {
    FocusScope.of(context).unfocus();
    final step = await ref
        .read(loginControllerProvider.notifier)
        .submit(email: _email.text, password: _password.text);
    if (step == LoginStep.twoFactor && mounted) {
      await context.push(
        Uri(path: Routes.twoFactor.path, queryParameters: _carry).toString(),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(loginControllerProvider);
    final controller = ref.read(loginControllerProvider.notifier);
    final clock = ref.watch(clockProvider);
    final type = context.type;
    final colors = context.colors;

    return FlowScaffold(
      appBar: MobileAppBar(
        leading: AppBarLeading.close,
        onLeading: () =>
            context.canPop() ? context.pop() : context.go(Routes.home.path),
      ),
      body: [
        FlowHeading(title: l10n.loginTitle, body: l10n.loginSubtitle),
        const SizedBox(height: Spacing.s4),
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
                label: l10n.emailLabel,
                controller: _email,
                ltr: true,
                keyboardType: TextInputType.emailAddress,
                textInputAction: TextInputAction.next,
                autofillHints: const [AutofillHints.email],
                errorText: fieldIssueText(l10n, state.fieldErrors['email']),
                onChanged: (_) => controller.edited('email'),
              ),
              const SizedBox(height: Spacing.s4),
              AppTextField(
                label: l10n.passwordLabel,
                controller: _password,
                obscure: true,
                textInputAction: TextInputAction.done,
                autofillHints: const [AutofillHints.password],
                errorText: fieldIssueText(l10n, state.fieldErrors['password']),
                onChanged: (_) => controller.edited('password'),
                onSubmitted: (_) => unawaited(_submit()),
              ),
            ],
          ),
        ),
        Align(
          alignment: AlignmentDirectional.centerEnd,
          child: AuthLink(
            label: l10n.forgotPasswordLink,
            onTap: () => context.push(Routes.forgotPassword.path),
          ),
        ),
        const SizedBox(height: Spacing.s12),
        Wrap(
          alignment: WrapAlignment.center,
          crossAxisAlignment: WrapCrossAlignment.center,
          children: [
            Text(
              l10n.newHere,
              style: type.bodyMd.copyWith(color: colors.contentSecondary),
            ),
            const SizedBox(width: Spacing.s1),
            AuthLink(
              label: l10n.createAccountLink,
              onTap: () => context.go(
                Uri(
                  path: Routes.register.path,
                  queryParameters: _carry,
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
            label: l10n.signInAction,
            expand: true,
            loading: state.submitting,
            onPressed: () => unawaited(_submit()),
          ),
        ),
      ],
    );
  }
}
