import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/auth/presentation/controllers/auth_form_state.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/countdown.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';

/// The text of a field error: the server's message, or the translation of a local check.
String? fieldIssueText(AppLocalizations l10n, FieldIssue? issue) {
  if (issue == null) {
    return null;
  }
  return issue.serverMessage ??
      switch (issue.local!) {
        LocalFieldIssue.required => l10n.fieldRequired,
        LocalFieldIssue.invalidEmail => l10n.emailInvalid,
        LocalFieldIssue.passwordMismatch => l10n.passwordMismatch,
        LocalFieldIssue.termsRequired => l10n.termsRequired,
        LocalFieldIssue.otpIncomplete => l10n.otpIncomplete,
        LocalFieldIssue.codeInvalid => l10n.twoFactorInvalid,
      };
}

/// The banner of a failed auth form: the 429 countdown ("Too many attempts / Try again in N
/// seconds", announced once) or the mapped failure with its request id.
class AuthFailureBanner extends StatelessWidget {
  const AuthFailureBanner({
    required this.state,
    required this.clock,
    required this.title,
    this.onRateLimitEnded,
    super.key,
  });

  final AuthFormState state;
  final DateTime Function() clock;
  final String title;
  final VoidCallback? onRateLimitEnded;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final retryAt = state.retryAt;
    final failure = state.failure;
    if (retryAt != null && state.isRateLimited(clock())) {
      return Padding(
        padding: const EdgeInsetsDirectional.only(bottom: Spacing.s4),
        child: CountdownBuilder(
          until: retryAt,
          clock: clock,
          onDone: onRateLimitEnded,
          builder: (context, remaining) => StatusBanner(
            tone: BannerTone.danger,
            title: l10n.rateLimitedTitle,
            body: l10n.rateLimitedBody(remaining.inSeconds),
          ),
        ),
      );
    }
    if (failure == null || failure is RateLimitedFailure) {
      return const SizedBox.shrink();
    }
    return Padding(
      padding: const EdgeInsetsDirectional.only(bottom: Spacing.s4),
      child: StatusBanner(
        tone: failure is NetworkFailure || failure is TimeoutFailure
            ? BannerTone.offline
            : BannerTone.danger,
        title: title,
        body: [
          failureMessage(l10n, failure),
          if (failure.requestId != null && failure is ServerFailure)
            l10n.requestReference(failure.requestId!),
        ].join('\n'),
      ),
    );
  }
}

/// A 48dp text link in the accent colour.
class AuthLink extends StatelessWidget {
  const AuthLink({required this.label, required this.onTap, super.key});

  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => Semantics(
    link: true,
    child: InkWell(
      onTap: onTap,
      child: ConstrainedBox(
        constraints: const BoxConstraints(minHeight: 48),
        child: Padding(
          padding: const EdgeInsetsDirectional.symmetric(
            horizontal: Spacing.s1,
          ),
          child: Align(
            widthFactor: 1,
            child: Text(
              label,
              style: context.type.labelMd.copyWith(
                color: context.colors.accentText,
              ),
            ),
          ),
        ),
      ),
    ),
  );
}

/// The primary footer button of an auth form: disabled with "Try again in m:ss" during a rate
/// limit.
class RateLimitedButton extends StatelessWidget {
  const RateLimitedButton({
    required this.child,
    required this.state,
    required this.clock,
    super.key,
  });

  final Widget child;
  final AuthFormState state;
  final DateTime Function() clock;

  @override
  Widget build(BuildContext context) {
    final retryAt = state.retryAt;
    if (retryAt == null || !state.isRateLimited(clock())) {
      return child;
    }
    return CountdownBuilder(
      until: retryAt,
      clock: clock,
      builder: (context, remaining) => AppButton(
        label: context.l10n.tryAgainIn(formatCountdown(remaining)),
        expand: true,
        onPressed: null,
      ),
    );
  }
}
