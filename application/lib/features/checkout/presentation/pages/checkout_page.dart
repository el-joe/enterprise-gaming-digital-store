import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/money/money_display.dart';
import 'package:application/core/platform/native_bridge.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/app_config/app_config_providers.dart';
import 'package:application/features/cart/cart_providers.dart';
import 'package:application/features/checkout/checkout_providers.dart';
import 'package:application/features/checkout/domain/entities/checkout.dart';
import 'package:application/features/checkout/presentation/controllers/checkout_controller.dart';
import 'package:application/features/checkout/presentation/widgets/checkout_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Checkout (07.2 to 07.7, 19.3 at 360 wide), signed in only: account email, country, payment
/// options with their charge amount and notices, terms, the display total and the exact amount
/// charged (owner decision 1), and the pay button. State is in [CheckoutController]; the page
/// renders it, forwards taps and acts on its events (payment screen, back to cart, the price
/// changed dialog).
class CheckoutPage extends ConsumerWidget {
  const CheckoutPage({super.key});

  void _back(BuildContext context) =>
      context.canPop() ? context.pop() : context.go(Routes.cart.path);

  Future<void> _openLink(BuildContext context, WidgetRef ref, Uri? url) async {
    final message = context.l10n.linkOpenFailed;
    final opened =
        url != null &&
        await ref.read(nativeBridgeProvider).openExternalUrl(url);
    if (!opened && context.mounted) {
      showAppSnackbar(context, message: message, tone: SnackbarTone.danger);
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final mode = ref.watch(checkoutModeProvider);
    final links = ref.watch(latestAppConfigProvider)?.links;
    final appBar = MobileAppBar(
      title: l10n.checkoutTitle,
      onLeading: () => _back(context),
    );
    if (mode != CheckoutMode.inApp) {
      return Scaffold(
        backgroundColor: context.colors.surfaceCanvas,
        appBar: appBar,
        body: Center(
          child: SingleChildScrollView(
            child: mode == CheckoutMode.web
                ? EmptyState(
                    icon: Icons.open_in_new,
                    title: l10n.checkoutWebTitle,
                    body: l10n.checkoutWebBody,
                    actionLabel: l10n.cartCheckoutWeb,
                    primaryAction: true,
                    onAction: () =>
                        unawaited(_openLink(context, ref, links?.webCheckout)),
                  )
                : EmptyState(
                    icon: Icons.block,
                    title: l10n.checkoutDisabledTitle,
                    body: l10n.cartCheckoutDisabled,
                    actionLabel: l10n.checkoutBackToCart,
                    onAction: () => context.go(Routes.cart.path),
                  ),
          ),
        ),
      );
    }

    final state = ref.watch(checkoutControllerProvider);
    final controller = ref.read(checkoutControllerProvider.notifier);
    ref.listen(checkoutControllerProvider.select((s) => s.eventCount), (
      previous,
      next,
    ) {
      if (next <= (previous ?? 0)) {
        return;
      }
      switch (ref.read(checkoutControllerProvider).event) {
        case OpenPayment(:final orderPublicId):
          context.go(Routes.paymentLocation(orderPublicId));
        case BackToCart():
          context.go(Routes.cart.path);
        case PriceChanged(:final previous, :final current):
          unawaited(
            _showPriceChanged(
              context,
              previous: moneyText(previous),
              current: current == null ? null : moneyText(current),
              onBackToCart: () {
                ref.read(cartPricesUpdatedProvider.notifier).flag();
                context.go(Routes.cart.path);
              },
            ),
          );
        case null:
          break;
      }
    });

    return Scaffold(
      backgroundColor: context.colors.surfaceCanvas,
      appBar: appBar,
      body: switch (state.countries) {
        Loading() => const Padding(
          padding: EdgeInsetsDirectional.all(Spacing.s4),
          child: OptionsSkeleton(),
        ),
        Failed(:final failure) => FailureView(
          failure: failure,
          errorTitle: l10n.checkoutQuoteFailedTitle,
          onRetry: () => unawaited(controller.retry()),
        ),
        Loaded(value: final countries) => ListView(
          padding: const EdgeInsetsDirectional.fromSTEB(
            Spacing.s4,
            Spacing.s2,
            Spacing.s4,
            Spacing.s6,
          ),
          children: [
            if (state.email != null) ...[
              _EmailCard(email: state.email!),
              const SizedBox(height: Spacing.s4),
            ],
            CountryField(
              label: l10n.checkoutCountry,
              countries: countries,
              selected: state.selectedCountry,
              enabled: !state.placing,
              onSelected: (code) => unawaited(controller.selectCountry(code)),
            ),
            const SizedBox(height: Spacing.s5),
            Semantics(
              header: true,
              child: Text(l10n.checkoutPayment, style: context.type.headingSm),
            ),
            const SizedBox(height: Spacing.s3),
            ..._payment(context, ref, state),
          ],
        ),
      },
      bottomNavigationBar: _Footer(state: state, onPay: controller.placeOrder),
    );
  }

  List<Widget> _payment(
    BuildContext context,
    WidgetRef ref,
    CheckoutState state,
  ) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final controller = ref.read(checkoutControllerProvider.notifier);
    final links = ref.read(latestAppConfigProvider)?.links;
    final quote = state.quote;
    switch (quote) {
      case null:
        return [
          Text(
            l10n.checkoutCountryHint,
            style: type.bodyMd.copyWith(color: colors.contentSecondary),
          ),
        ];
      case Loading():
        return const [OptionsSkeleton()];
      case Failed(:final failure):
        return [
          StatusBanner(
            tone: BannerTone.danger,
            title: l10n.checkoutQuoteFailedTitle,
            body: failureDetail(l10n, failure),
          ),
          const SizedBox(height: Spacing.s3),
          AppButton(
            label: l10n.actionTryAgain,
            icon: Icons.refresh,
            variant: AppButtonVariant.secondary,
            expand: true,
            onPressed: () => unawaited(controller.requote()),
          ),
        ];
      case Loaded(value: final quote):
        final selected = state.selectedOption;
        // Stripe is always "not in the app yet"; the banner is for the server's own refusals.
        final someBlocked = quote.options.any(
          (o) =>
              blockOfOption(o) != null &&
              blockOfOption(o) != OptionBlock.notInApp,
        );
        return [
          for (final option in quote.options) ...[
            PaymentOptionTile(
              key: ValueKey('option-${option.gateway}'),
              option: option,
              selected: option.gateway == state.gateway,
              onTap: () => controller.selectOption(option.gateway),
            ),
            if (option.gateway == state.gateway &&
                option.methods.length > 1) ...[
              const SizedBox(height: Spacing.s2),
              _Methods(
                option: option,
                selected: state.method,
                onSelected: controller.selectMethod,
              ),
            ],
            if (option.gateway == state.gateway &&
                option.notice == 'charged_in_payment_currency') ...[
              const SizedBox(height: Spacing.s2),
              StatusBanner(
                tone: BannerTone.info,
                title: l10n.checkoutChargeNoticeTitle(
                  option.label,
                  option.paymentCurrency,
                ),
                body: l10n.checkoutChargeNoticeBody(
                  option.displayCurrency,
                  option.label,
                  ltrIsolate(moneyText(option.paymentAmount)),
                ),
              ),
            ],
            const SizedBox(height: Spacing.s2),
          ],
          if (state.noPayableOption) ...[
            const SizedBox(height: Spacing.s1),
            StatusBanner(
              tone: BannerTone.danger,
              title: l10n.checkoutNoOptionsTitle,
              body: l10n.checkoutNoOptionsBody,
            ),
            if (links?.webCheckout != null) ...[
              const SizedBox(height: Spacing.s3),
              AppButton(
                label: l10n.cartCheckoutWeb,
                icon: Icons.open_in_new,
                variant: AppButtonVariant.secondary,
                expand: true,
                onPressed: () =>
                    unawaited(_openLink(context, ref, links!.webCheckout)),
              ),
            ],
          ] else if (someBlocked) ...[
            const SizedBox(height: Spacing.s1),
            StatusBanner(
              tone: BannerTone.warning,
              title: l10n.checkoutSomeUnavailableTitle,
              body: l10n.checkoutSomeUnavailableBody(
                state.selectedCountry?.name ?? state.country ?? '',
              ),
            ),
          ],
          const SizedBox(height: Spacing.s3),
          _Terms(
            accepted: state.acceptTerms,
            error: state.termsError,
            onChanged: (accepted) =>
                controller.setAcceptTerms(accepted: accepted),
            onRead: links?.terms == null
                ? null
                : () => unawaited(_openLink(context, ref, links!.terms)),
          ),
          const SizedBox(height: Spacing.s3),
          _Summary(quote: quote, option: selected),
          if (state.problem != null) ...[
            const SizedBox(height: Spacing.s3),
            _ProblemBanner(state: state),
          ],
        ];
    }
  }
}

Future<void> _showPriceChanged(
  BuildContext context, {
  required String previous,
  required String? current,
  required VoidCallback onBackToCart,
}) => showDialog<void>(
  context: context,
  builder: (dialog) {
    final l10n = dialog.l10n;
    final colors = dialog.colors;
    return AlertDialog(
      backgroundColor: colors.surfaceRaised,
      icon: Icon(Icons.warning_amber_rounded, color: colors.statusWarning),
      title: Text(l10n.checkoutPriceChangedTitle),
      content: Text(
        current == null
            ? l10n.checkoutPriceChangedBodyUnknown(ltrIsolate(previous))
            : l10n.checkoutPriceChangedBody(
                ltrIsolate(current),
                ltrIsolate(previous),
              ),
      ),
      actions: [
        AppButton(
          label: l10n.checkoutReviewNewTotal,
          expand: true,
          onPressed: () => Navigator.of(dialog).pop(),
        ),
        const SizedBox(height: Spacing.s2),
        AppButton(
          label: l10n.checkoutBackToCart,
          variant: AppButtonVariant.ghost,
          expand: true,
          onPressed: () {
            Navigator.of(dialog).pop();
            onBackToCart();
          },
        ),
      ],
    );
  },
);

class _EmailCard extends StatelessWidget {
  const _EmailCard({required this.email});

  final String email;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    final type = context.type;
    return MergeSemantics(
      child: DecoratedBox(
        decoration: BoxDecoration(
          color: colors.surfaceRaised,
          borderRadius: BorderRadius.circular(Radii.md),
        ),
        child: Padding(
          padding: const EdgeInsetsDirectional.all(Spacing.s3),
          child: Row(
            children: [
              Icon(Icons.mail_outline, color: colors.contentSecondary),
              const SizedBox(width: Spacing.s3),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      email,
                      textDirection: TextDirection.ltr,
                      style: type.labelLg,
                    ),
                    Text(
                      context.l10n.checkoutEmailNote,
                      style: type.bodySm.copyWith(
                        color: colors.contentSecondary,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _Methods extends StatelessWidget {
  const _Methods({
    required this.option,
    required this.selected,
    required this.onSelected,
  });

  final PaymentOption option;
  final String? selected;
  final ValueChanged<String> onSelected;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsetsDirectional.only(start: Spacing.s6),
    child: Column(
      children: [
        for (final method in option.methods) ...[
          OptionTileCompact(
            label: method.label,
            selected: method.code == selected,
            onTap: () => onSelected(method.code),
          ),
        ],
      ],
    ),
  );
}

/// A 48dp radio row for a payment method of the selected gateway.
class OptionTileCompact extends StatelessWidget {
  const OptionTileCompact({
    required this.label,
    required this.selected,
    required this.onTap,
    super.key,
  });

  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return Semantics(
      inMutuallyExclusiveGroup: true,
      checked: selected,
      button: true,
      label: label,
      excludeSemantics: true,
      onTap: onTap,
      child: InkWell(
        onTap: onTap,
        child: ConstrainedBox(
          constraints: const BoxConstraints(minHeight: 48),
          child: Row(
            children: [
              Icon(
                selected
                    ? Icons.radio_button_checked
                    : Icons.radio_button_unchecked,
                size: 20,
                color: selected ? colors.accentPrimary : colors.lineStrong,
              ),
              const SizedBox(width: Spacing.s2),
              Expanded(child: Text(label, style: context.type.bodyMd)),
            ],
          ),
        ),
      ),
    );
  }
}

class _Terms extends StatelessWidget {
  const _Terms({
    required this.accepted,
    required this.error,
    required this.onChanged,
    this.onRead,
  });

  final bool accepted;
  final bool error;
  final ValueChanged<bool> onChanged;
  final VoidCallback? onRead;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        MergeSemantics(
          child: InkWell(
            onTap: () => onChanged(!accepted),
            child: ConstrainedBox(
              constraints: const BoxConstraints(minHeight: 48),
              child: Row(
                children: [
                  Checkbox(
                    value: accepted,
                    onChanged: (value) => onChanged(value ?? false),
                    isError: error && !accepted,
                  ),
                  Expanded(
                    child: Text(l10n.checkoutAcceptTerms, style: type.bodyMd),
                  ),
                ],
              ),
            ),
          ),
        ),
        if (error && !accepted)
          Semantics(
            liveRegion: true,
            child: Text(
              l10n.checkoutTermsRequired,
              style: type.bodySm.copyWith(color: colors.statusDanger),
            ),
          ),
        if (onRead != null)
          Align(
            alignment: AlignmentDirectional.centerStart,
            child: TextButton(
              onPressed: onRead,
              style: TextButton.styleFrom(minimumSize: const Size(48, 48)),
              child: Text(l10n.checkoutReadTerms),
            ),
          ),
      ],
    );
  }
}

/// Owner decision 1: the display total and, beside it, the exact amount and currency charged.
class _Summary extends StatelessWidget {
  const _Summary({required this.quote, required this.option});

  final CheckoutQuote quote;
  final PaymentOption? option;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final option = this.option;
    return Semantics(
      container: true,
      liveRegion: true,
      child: DecoratedBox(
        decoration: BoxDecoration(
          color: colors.surfaceRaised,
          borderRadius: BorderRadius.circular(Radii.md),
        ),
        child: Padding(
          padding: const EdgeInsetsDirectional.all(Spacing.s3),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      l10n.checkoutItems(quote.units),
                      style: type.bodySm.copyWith(
                        color: colors.contentSecondary,
                      ),
                    ),
                  ),
                  Text(
                    moneyText(quote.displayTotal),
                    textDirection: TextDirection.ltr,
                    style: type.priceMd,
                  ),
                ],
              ),
              if (option != null && blockOfOption(option) == null) ...[
                const SizedBox(height: Spacing.s2),
                Text(
                  l10n.checkoutChargedLine(
                    ltrIsolate(moneyText(option.paymentAmount)),
                    option.paymentCurrency,
                    option.label,
                  ),
                  key: const ValueKey('charged-line'),
                  style: type.labelMd,
                ),
              ],
              const SizedBox(height: Spacing.s2),
              Text(
                l10n.checkoutFeesNote,
                style: type.bodyCaption.copyWith(color: colors.contentMuted),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ProblemBanner extends StatelessWidget {
  const _ProblemBanner({required this.state});

  final CheckoutState state;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final failure = state.failure;
    final detail =
        state.failureText ??
        (failure == null ? null : failureDetail(l10n, failure));
    final (tone, title, body) = switch (state.problem!) {
      PlaceProblem.inProgress => (
        BannerTone.info,
        l10n.checkoutInProgressTitle,
        l10n.checkoutInProgressBody,
      ),
      PlaceProblem.cartNeedsAttention => (
        BannerTone.warning,
        l10n.checkoutCartAttentionTitle,
        detail,
      ),
      PlaceProblem.rateStale => (
        BannerTone.warning,
        l10n.checkoutRateStaleTitle,
        l10n.checkoutRateStaleBody,
      ),
      PlaceProblem.other => (
        BannerTone.danger,
        l10n.checkoutFailedTitle,
        detail,
      ),
    };
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        StatusBanner(tone: tone, title: title, body: body),
        if (state.problem == PlaceProblem.cartNeedsAttention)
          Align(
            alignment: AlignmentDirectional.centerStart,
            child: TextButton(
              onPressed: () => context.go(Routes.cart.path),
              style: TextButton.styleFrom(minimumSize: const Size(48, 48)),
              child: Text(l10n.checkoutBackToCart),
            ),
          ),
      ],
    );
  }
}

/// Sticky footer (07.1 to 07.3): the pay button, labelled with the amount and the gateway.
class _Footer extends StatelessWidget {
  const _Footer({required this.state, required this.onPay});

  final CheckoutState state;
  final Future<void> Function() onPay;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final option = state.selectedOption;
    final amount = option == null
        ? null
        : ltrIsolate(moneyText(option.paymentAmount));
    final label = switch (option?.gateway) {
      null => l10n.checkoutPayButton,
      'paypal' => l10n.checkoutContinueTo(option!.label),
      'wallet' => l10n.checkoutPayWallet(amount!),
      _ => l10n.checkoutPay(amount!),
    };
    return DecoratedBox(
      decoration: BoxDecoration(
        color: colors.surfaceBase,
        border: Border(top: BorderSide(color: colors.lineSubtle)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsetsDirectional.symmetric(
            horizontal: Spacing.s4,
            vertical: Spacing.s3,
          ),
          child: AppButton(
            label: label,
            icon: option?.gateway == 'wallet'
                ? Icons.account_balance_wallet_outlined
                : Icons.lock_outline,
            expand: true,
            loading: state.placing,
            onPressed: state.canPlace ? () => unawaited(onPay()) : null,
          ),
        ),
      ),
    );
  }
}
