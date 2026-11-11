import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/guards.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/cart/cart_providers.dart';
import 'package:application/features/cart/presentation/controllers/cart_controller.dart';
import 'package:application/features/cart/presentation/widgets/cart_widgets.dart';
import 'package:application/features/checkout/checkout_providers.dart';
import 'package:application/shared/domain/entities/cart.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// The Cart tab (06.1 to 06.5): lines with quantity and remove, coupon, server totals, and the
/// checkout button (in-app checkout, or "Complete purchase on boxcards.shop" in web checkout
/// mode). All state is in [CartController]; the coupon text field is the only local state.
class CartPage extends ConsumerStatefulWidget {
  const CartPage({super.key});

  @override
  ConsumerState<CartPage> createState() => _CartPageState();
}

class _CartPageState extends ConsumerState<CartPage> {
  final _coupon = TextEditingController();

  @override
  void dispose() {
    _coupon.dispose();
    super.dispose();
  }

  Future<void> _checkout(CheckoutMode mode) async {
    final controller = ref.read(cartControllerProvider.notifier);
    switch (mode) {
      case CheckoutMode.inApp:
        // A guest is sent to sign in by the route guard and comes back (owner decision 4).
        unawaited(context.push(Routes.checkout.path));
      case CheckoutMode.web:
        final message = context.l10n.linkOpenFailed;
        final opened = await controller.openWebCheckout();
        if (!opened && mounted) {
          showAppSnackbar(context, message: message, tone: SnackbarTone.danger);
        }
      case CheckoutMode.disabled:
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(cartControllerProvider);
    final controller = ref.read(cartControllerProvider.notifier);
    final mode = ref.watch(checkoutModeProvider);
    final signedIn = ref.watch(sessionProvider) == SessionStatus.signedIn;
    final pricesUpdated = ref.watch(cartPricesUpdatedProvider);
    ref.listen(cartControllerProvider.select((s) => s.actionFailureCount), (
      previous,
      next,
    ) {
      final failure = ref.read(cartControllerProvider).actionFailure;
      if (next > (previous ?? 0) && failure != null) {
        showAppSnackbar(
          context,
          message: failureMessage(l10n, failure),
          tone: SnackbarTone.danger,
        );
      }
    });
    // A coupon that was applied or removed empties the field.
    ref.listen(cartControllerProvider.select((s) => s.cart?.coupon?.applied), (
      _,
      applied,
    ) {
      if (applied ?? false) {
        _coupon.clear();
      }
    });

    final cart = state.cart;
    final units = cart == null ? 0 : unitsIn(cart);
    return Scaffold(
      backgroundColor: context.colors.surfaceCanvas,
      appBar: MobileAppBar(
        title: units > 0 ? l10n.cartTitleCount(units) : l10n.cartTitle,
        leading: AppBarLeading.none,
      ),
      body: switch (state.content) {
        Loading() => const CartSkeleton(),
        Failed(:final failure) => FailureView(
          failure: failure,
          errorTitle: l10n.cartLoadFailedTitle,
          onRetry: () => unawaited(controller.retry()),
        ),
        Loaded() when state.isEmpty => Center(
          child: SingleChildScrollView(
            child: EmptyState(
              icon: Icons.shopping_bag_outlined,
              title: l10n.cartEmptyTitle,
              body: l10n.cartEmptyBody,
              actionLabel: l10n.cartBrowseStore,
              primaryAction: true,
              onAction: () => context.go(Routes.browse.path),
            ),
          ),
        ),
        Loaded() => _CartBody(
          cart: cart!,
          state: state,
          coupon: _coupon,
          pricesUpdated: pricesUpdated,
        ),
      },
      bottomNavigationBar: cart == null || cart.items.isEmpty
          ? null
          : _CheckoutFooter(
              state: state,
              mode: mode,
              signedIn: signedIn,
              onCheckout: () => unawaited(_checkout(mode)),
            ),
    );
  }
}

class _CartBody extends ConsumerWidget {
  const _CartBody({
    required this.cart,
    required this.state,
    required this.coupon,
    required this.pricesUpdated,
  });

  final Cart cart;
  final CartPageState state;
  final TextEditingController coupon;
  final bool pricesUpdated;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = context.l10n;
    final controller = ref.read(cartControllerProvider.notifier);
    return RefreshIndicator(
      onRefresh: controller.refresh,
      child: ListView(
        padding: const EdgeInsetsDirectional.fromSTEB(
          Spacing.s4,
          Spacing.s2,
          Spacing.s4,
          Spacing.s6,
        ),
        children: [
          if (pricesUpdated || state.pricesUpdating) ...[
            StatusBanner(
              tone: BannerTone.warning,
              title: l10n.cartPricesUpdatedTitle,
              body: l10n.cartPricesUpdatedBody,
            ),
            const SizedBox(height: Spacing.s3),
          ] else if (cart.priceMayVary) ...[
            StatusBanner(
              tone: BannerTone.info,
              title: l10n.cartPriceMayVaryTitle,
              body: l10n.cartPriceMayVaryBody,
            ),
            const SizedBox(height: Spacing.s3),
          ],
          for (final line in cart.items) ...[
            CartLineCard(
              key: ValueKey('cart-line-${line.id}'),
              line: line,
              busy: state.busyLines.contains(line.id),
              error: state.lineErrors[line.id],
              onQuantity: (quantity) =>
                  unawaited(controller.setQuantity(line, quantity)),
              onRemove: () => unawaited(controller.remove(line)),
            ),
            const SizedBox(height: Spacing.s3),
          ],
          const SizedBox(height: Spacing.s2),
          CouponSection(
            coupon: cart.coupon,
            controller: coupon,
            busy: state.couponBusy,
            error: state.couponError,
            onApply: () => unawaited(controller.applyCoupon(coupon.text)),
            onRemove: () => unawaited(controller.removeCoupon()),
          ),
          const SizedBox(height: Spacing.s4),
          CartTotalsCard(cart: cart),
        ],
      ),
    );
  }
}

/// Sticky checkout button (06.1) with the reason when it is disabled (06.2).
class _CheckoutFooter extends StatelessWidget {
  const _CheckoutFooter({
    required this.state,
    required this.mode,
    required this.signedIn,
    required this.onCheckout,
  });

  final CartPageState state;
  final CheckoutMode mode;
  final bool signedIn;
  final VoidCallback onCheckout;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final colors = context.colors;
    final type = context.type;
    final enabled = mode != CheckoutMode.disabled && state.canCheckout;
    final hint = switch (mode) {
      CheckoutMode.disabled => l10n.cartCheckoutDisabled,
      _ when !(state.cart?.checkoutReady ?? false) => l10n.cartCheckoutBlocked,
      CheckoutMode.web => l10n.cartCheckoutWebHint,
      CheckoutMode.inApp when !signedIn => l10n.cartSignInHint,
      CheckoutMode.inApp => null,
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
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (hint != null) ...[
                Text(
                  hint,
                  textAlign: TextAlign.center,
                  style: type.bodySm.copyWith(color: colors.contentSecondary),
                ),
                const SizedBox(height: Spacing.s2),
              ],
              AppButton(
                label: mode == CheckoutMode.web
                    ? l10n.cartCheckoutWeb
                    : l10n.cartCheckout,
                // The web label is long ("Complete purchase on boxcards.shop"): no icon.
                icon: mode == CheckoutMode.web
                    ? null
                    : Icons.shopping_bag_outlined,
                expand: true,
                onPressed: enabled ? onCheckout : null,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
