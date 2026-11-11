import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/network/failure.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/core/utils/clock.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/orders/presentation/controllers/guest_order_controllers.dart';
import 'package:application/features/orders/presentation/pages/order_detail_page.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_text_field.dart';
import 'package:application/shared/widgets/busy_indicator.dart';
import 'package:application/shared/widgets/countdown.dart';
import 'package:application/shared/widgets/flow_scaffold.dart';
import 'package:application/shared/widgets/icon_badge.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/otp_input.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

void _close(BuildContext context) =>
    context.canPop() ? context.pop() : context.go(Routes.account.path);

/// "Open a guest order": paste the delivery email's link (reached from the Account tab and the
/// signed-out Orders tab).
class GuestAccessPage extends ConsumerStatefulWidget {
  const GuestAccessPage({super.key});

  @override
  ConsumerState<GuestAccessPage> createState() => _GuestAccessPageState();
}

class _GuestAccessPageState extends ConsumerState<GuestAccessPage> {
  final _link = TextEditingController();

  @override
  void dispose() {
    _link.dispose();
    super.dispose();
  }

  void _open() {
    final order = ref.read(guestLinkEntryProvider.notifier).submit(_link.text);
    if (order != null) {
      unawaited(context.push(Routes.guestOrderLocation(order)));
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(guestLinkEntryProvider);
    return FlowScaffold(
      appBar: MobileAppBar(
        title: l10n.guestEntryTitle,
        onLeading: () => _close(context),
      ),
      body: [
        const IconBadge(icon: Icons.link),
        const SizedBox(height: Spacing.s4),
        FlowHeading(title: l10n.guestEntryTitle, body: l10n.guestEntryBody),
        const SizedBox(height: Spacing.s5),
        AppTextField(
          label: l10n.guestEntryLabel,
          controller: _link,
          keyboardType: TextInputType.url,
          ltr: true,
          errorText: state.invalid ? l10n.guestEntryInvalid : null,
          textInputAction: TextInputAction.go,
          onChanged: (_) =>
              ref.read(guestLinkEntryProvider.notifier).clearError(),
          onSubmitted: (_) => _open(),
        ),
      ],
      footer: [
        AppButton(
          label: l10n.guestEntryOpen,
          icon: Icons.open_in_new,
          expand: true,
          onPressed: _open,
        ),
      ],
    );
  }
}

/// A guest order (11.1 to 11.5): verify by email code, then the order with its codes.
class GuestOrderPage extends ConsumerStatefulWidget {
  const GuestOrderPage({required this.orderPublicId, super.key});

  final String orderPublicId;

  @override
  ConsumerState<GuestOrderPage> createState() => _GuestOrderPageState();
}

class _GuestOrderPageState extends ConsumerState<GuestOrderPage> {
  final _email = TextEditingController();
  final _code = TextEditingController();

  late final GuestOrderControllerProvider _provider =
      guestOrderControllerProvider(widget.orderPublicId);

  @override
  void dispose() {
    _email.dispose();
    _code.dispose();
    super.dispose();
  }

  GuestOrderController get _controller => ref.read(_provider.notifier);

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(_provider);
    final number = state.order?.order.summary.number;
    final appBar = MobileAppBar(
      leading: AppBarLeading.close,
      onLeading: () => _close(context),
      title: number == null
          ? l10n.guestOrderTitle
          : l10n.guestOrderNumberTitle(number),
    );
    switch (state.step) {
      case GuestStep.loading:
        return Scaffold(
          appBar: appBar,
          body: const Center(child: BusyIndicator(size: 36, strokeWidth: 4)),
        );
      case GuestStep.failed:
        return Scaffold(
          appBar: appBar,
          body: FailureView(
            failure: state.failure ?? const UnexpectedFailure(),
            onRetry: () => unawaited(_controller.retry()),
          ),
        );
      case GuestStep.linkExpired || GuestStep.needLink:
        final expired = state.step == GuestStep.linkExpired;
        final support = ref.watch(latestAppConfigProvider)?.links.support;
        return Scaffold(
          appBar: appBar,
          body: Center(
            child: SingleChildScrollView(
              child: EmptyState(
                icon: expired ? Icons.link_off : Icons.mail_outline,
                title: expired
                    ? l10n.guestLinkExpiredTitle
                    : l10n.guestNeedLinkTitle,
                body: expired
                    ? l10n.guestLinkExpiredBody
                    : l10n.guestNeedLinkBody,
                actionLabel: expired && support != null
                    ? l10n.contactSupport
                    : null,
                onAction: expired && support != null
                    ? () => unawaited(openSupport(context, ref))
                    : null,
              ),
            ),
          ),
        );
      case GuestStep.email:
        final failure = state.failure;
        final emailError = switch (failure) {
          ValidationFailure() =>
            failure.firstErrorFor('email') ?? failureMessage(l10n, failure),
          null => null,
          _ => failureMessage(l10n, failure),
        };
        return FlowScaffold(
          appBar: appBar,
          body: [
            const IconBadge(icon: Icons.mail_outline),
            const SizedBox(height: Spacing.s4),
            FlowHeading(
              title: l10n.guestVerifyTitle,
              body: l10n.guestVerifyBody,
            ),
            const SizedBox(height: Spacing.s4),
            StatusBanner(
              tone: BannerTone.info,
              title: l10n.guestLinkVerifiedTitle,
              body: l10n.guestLinkVerifiedBody,
            ),
            const SizedBox(height: Spacing.s4),
            AppTextField(
              label: l10n.guestEmailLabel,
              controller: _email,
              keyboardType: TextInputType.emailAddress,
              autofillHints: const [AutofillHints.email],
              ltr: true,
              errorText: emailError,
              textInputAction: TextInputAction.send,
              onSubmitted: (value) => unawaited(_controller.sendCode(value)),
            ),
          ],
          footer: [
            AppButton(
              label: l10n.guestSendCode,
              icon: Icons.send_outlined,
              expand: true,
              loading: state.busy,
              onPressed: state.busy
                  ? null
                  : () => unawaited(_controller.sendCode(_email.text)),
            ),
          ],
        );
      case GuestStep.code:
        final failure = state.failure;
        final resendAt = state.resendAt;
        final codeError = state.codeError;
        return FlowScaffold(
          appBar: appBar,
          body: [
            const IconBadge(icon: Icons.pin_outlined),
            const SizedBox(height: Spacing.s4),
            FlowHeading(
              title: l10n.guestCodeTitle,
              body: l10n.guestCodeBody(ltrIsolate(state.email)),
            ),
            const SizedBox(height: Spacing.s5),
            OtpInput(
              semanticsLabel: l10n.guestCodeSemantics,
              controller: _code,
              hasError: codeError != null,
              onCompleted: (code) => unawaited(_controller.verifyCode(code)),
            ),
            if (codeError != null && codeError.isNotEmpty) ...[
              const SizedBox(height: Spacing.s2),
              Semantics(
                liveRegion: true,
                child: Text(
                  codeError,
                  style: context.type.bodySm.copyWith(
                    color: context.colors.statusDanger,
                  ),
                ),
              ),
            ],
            if (failure != null) ...[
              const SizedBox(height: Spacing.s3),
              StatusBanner(
                tone: BannerTone.danger,
                title: failureDetail(l10n, failure),
              ),
            ],
            const SizedBox(height: Spacing.s3),
            Wrap(
              alignment: WrapAlignment.spaceBetween,
              crossAxisAlignment: WrapCrossAlignment.center,
              children: [
                if (resendAt != null)
                  CountdownBuilder(
                    until: resendAt,
                    clock: ref.watch(clockProvider),
                    builder: (context, remaining) => TextButton(
                      onPressed: remaining > Duration.zero || state.busy
                          ? null
                          : () => unawaited(_controller.resendCode()),
                      style: TextButton.styleFrom(
                        minimumSize: const Size(48, 48),
                      ),
                      child: Text(
                        remaining > Duration.zero
                            ? l10n.guestResendIn(formatCountdown(remaining))
                            : l10n.guestResend,
                      ),
                    ),
                  ),
                TextButton(
                  onPressed: state.busy ? null : _controller.changeEmail,
                  style: TextButton.styleFrom(minimumSize: const Size(48, 48)),
                  child: Text(l10n.guestChangeEmail),
                ),
              ],
            ),
          ],
          footer: [
            AppButton(
              label: l10n.guestVerify,
              expand: true,
              loading: state.busy,
              onPressed: state.busy
                  ? null
                  : () => unawaited(_controller.verifyCode(_code.text)),
            ),
          ],
        );
      case GuestStep.order:
        final guestOrder = state.order!;
        final signedIn = ref.watch(sessionProvider) == SessionStatus.signedIn;
        return Scaffold(
          appBar: appBar,
          body: OrderDetailBody(
            detail: guestOrder.order,
            onOpenUnit: (unit) => unawaited(
              context.push(
                Routes.guestRevealLocation(widget.orderPublicId, unit.publicId),
              ),
            ),
            header: [
              if (!guestOrder.canReveal) ...[
                StatusBanner(
                  tone: BannerTone.warning,
                  title: l10n.guestLinkProofMissing,
                ),
                const SizedBox(height: Spacing.s3),
              ],
              Align(
                alignment: AlignmentDirectional.centerEnd,
                child: Text(
                  l10n.guestOrderLabel,
                  style: context.type.bodySm.copyWith(
                    color: context.colors.contentMuted,
                  ),
                ),
              ),
              const SizedBox(height: Spacing.s1),
            ],
            footer: [
              if (!signedIn) ...[
                const SizedBox(height: Spacing.s5),
                StatusBanner(
                  tone: BannerTone.info,
                  title: l10n.guestAccountPromptTitle,
                  body: l10n.guestAccountPromptBody,
                ),
                const SizedBox(height: Spacing.s3),
                AppButton(
                  label: l10n.guestCreateAccount,
                  variant: AppButtonVariant.secondary,
                  expand: true,
                  onPressed: () => context.go(Routes.register.path),
                ),
              ],
            ],
          ),
        );
    }
  }
}
