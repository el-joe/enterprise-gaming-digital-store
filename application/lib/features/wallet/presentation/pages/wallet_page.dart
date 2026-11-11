import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/wallet/presentation/controllers/wallet_controller.dart';
import 'package:application/features/wallet/presentation/widgets/wallet_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_snackbar.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Wallet (12.1 to 12.3): the balance as server money, the frozen notice with "Contact
/// support", and the paged statement (credit or debit, type, linked order, date). Loading,
/// empty, error, offline, and "not available" when `features.wallet_enabled` is off. Read only:
/// there is no top-up (BD3).
class WalletPage extends ConsumerStatefulWidget {
  const WalletPage({super.key});

  @override
  ConsumerState<WalletPage> createState() => _WalletPageState();
}

class _WalletPageState extends ConsumerState<WalletPage> {
  final _scroll = ScrollController();

  @override
  void initState() {
    super.initState();
    _scroll.addListener(_maybeLoadMore);
  }

  @override
  void dispose() {
    _scroll.dispose();
    super.dispose();
  }

  void _maybeLoadMore() {
    if (_scroll.hasClients &&
        _scroll.position.extentAfter < 600 &&
        ref.read(walletControllerProvider).moreFailure == null) {
      unawaited(ref.read(walletControllerProvider.notifier).loadMore());
    }
  }

  void _back() =>
      context.canPop() ? context.pop() : context.go(Routes.account.path);

  Future<void> _contactSupport() async {
    final message = context.l10n.linkOpenFailed;
    final opened = await ref
        .read(walletControllerProvider.notifier)
        .openSupport();
    if (!opened && mounted) {
      showAppSnackbar(context, message: message, tone: SnackbarTone.danger);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(walletControllerProvider);
    final frozen = state.content.valueOrNull?.frozen ?? false;
    final support = ref.watch(walletSupportAvailableProvider);
    // A short first page that does not fill the screen still loads the next one.
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        _maybeLoadMore();
      }
    });
    return Scaffold(
      appBar: MobileAppBar(title: l10n.walletTitle, onLeading: _back),
      body: SafeArea(
        top: false,
        bottom: !(frozen && support),
        child: _body(context, state),
      ),
      bottomNavigationBar: frozen && support
          ? _Footer(onContactSupport: () => unawaited(_contactSupport()))
          : null,
    );
  }

  Widget _body(BuildContext context, WalletState state) {
    final l10n = context.l10n;
    final controller = ref.read(walletControllerProvider.notifier);
    if (!state.enabled) {
      return Center(
        child: SingleChildScrollView(
          child: EmptyState(
            icon: Icons.account_balance_wallet_outlined,
            title: l10n.walletUnavailableTitle,
            body: l10n.walletUnavailableBody,
          ),
        ),
      );
    }
    if (!state.signedIn) {
      // The route guard sends a signed-out customer to sign in first.
      return const SizedBox.shrink();
    }
    switch (state.content) {
      case Loading():
        return const WalletSkeleton();
      case Failed(:final failure):
        return FailureView(
          failure: failure,
          errorTitle: l10n.walletLoadErrorTitle,
          onRetry: () => unawaited(controller.retry()),
        );
      case Loaded(value: final overview):
        final lines = overview.lines;
        return RefreshIndicator(
          onRefresh: controller.refresh,
          child: ListView(
            controller: _scroll,
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsetsDirectional.fromSTEB(
              Spacing.s4,
              Spacing.s2,
              Spacing.s4,
              Spacing.s6,
            ),
            children: [
              if (overview.frozen) ...[
                StatusBanner(
                  tone: BannerTone.warning,
                  title: l10n.walletFrozenTitle,
                  body: l10n.walletFrozenBody,
                ),
                const SizedBox(height: Spacing.s3),
              ],
              WalletBalanceCard(
                wallet: overview.wallet,
                currency: overview.currency,
              ),
              const SizedBox(height: Spacing.s3),
              if (state.isEmpty)
                EmptyState(
                  icon: Icons.receipt_long_outlined,
                  title: l10n.walletEmptyTitle,
                  body: l10n.walletEmptyBody,
                )
              else ...[
                ConstrainedBox(
                  constraints: const BoxConstraints(minHeight: 36),
                  child: Align(
                    alignment: AlignmentDirectional.centerStart,
                    child: Semantics(
                      header: true,
                      child: Text(
                        l10n.walletStatement,
                        style: context.type.headingSm,
                      ),
                    ),
                  ),
                ),
                for (var i = 0; i < lines.length; i++) ...[
                  if (i > 0)
                    Divider(height: 1, color: context.colors.lineSubtle),
                  WalletStatementRow(
                    key: ValueKey('wallet-line-${lines[i].id}'),
                    line: lines[i],
                    currency: overview.currency,
                    onOpenOrder: lines[i].order == null
                        ? null
                        : () => unawaited(
                            context.push(
                              Routes.orderLocation(lines[i].order!.publicId),
                            ),
                          ),
                  ),
                ],
                const SizedBox(height: Spacing.s3),
                _MoreFooter(state: state, onRetry: controller.loadMore),
              ],
            ],
          ),
        );
    }
  }
}

class _MoreFooter extends StatelessWidget {
  const _MoreFooter({required this.state, required this.onRetry});

  final WalletState state;
  final Future<void> Function() onRetry;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final failure = state.moreFailure;
    if (failure != null) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          StatusBanner(
            tone: BannerTone.danger,
            title: l10n.walletMoreFailed,
            body: failureDetail(l10n, failure),
          ),
          const SizedBox(height: Spacing.s2),
          AppButton(
            label: l10n.actionRetry,
            icon: Icons.refresh,
            variant: AppButtonVariant.secondary,
            expand: true,
            onPressed: () => unawaited(onRetry()),
          ),
        ],
      );
    }
    if (state.loadingMore || state.hasMore) {
      return const WalletRowSkeleton();
    }
    return const SizedBox.shrink();
  }
}

/// Sticky footer of the frozen wallet (12.2): "Contact support" on `surface-base` with a top
/// hairline.
class _Footer extends StatelessWidget {
  const _Footer({required this.onContactSupport});

  final VoidCallback onContactSupport;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
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
            label: context.l10n.walletContactSupport,
            icon: Icons.help_outline,
            variant: AppButtonVariant.secondary,
            expand: true,
            onPressed: onContactSupport,
          ),
        ),
      ),
    );
  }
}
