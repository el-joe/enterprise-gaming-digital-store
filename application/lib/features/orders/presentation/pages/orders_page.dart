import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/orders/domain/entities/orders.dart';
import 'package:application/features/orders/presentation/controllers/orders_controllers.dart';
import 'package:application/features/orders/presentation/widgets/order_widgets.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/app_chip.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// The Orders tab (09.1 to 09.3): status chips, the paged list, empty, loading, error and
/// offline states; signed out it offers sign-in and guest order access.
class OrdersPage extends ConsumerStatefulWidget {
  const OrdersPage({super.key});

  @override
  ConsumerState<OrdersPage> createState() => _OrdersPageState();
}

class _OrdersPageState extends ConsumerState<OrdersPage> {
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
        ref.read(ordersListControllerProvider).moreFailure == null) {
      unawaited(ref.read(ordersListControllerProvider.notifier).loadMore());
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(ordersListControllerProvider);
    final controller = ref.read(ordersListControllerProvider.notifier);
    // A short first page that does not fill the screen still loads the next one.
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        _maybeLoadMore();
      }
    });
    return Scaffold(
      appBar: MobileAppBar(
        title: l10n.ordersTitle,
        leading: AppBarLeading.none,
      ),
      body: !state.signedIn
          ? const _SignedOut()
          : Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                _Filters(
                  selected: state.status,
                  onSelect: controller.selectStatus,
                ),
                Expanded(child: _body(context, state, controller)),
              ],
            ),
    );
  }

  Widget _body(
    BuildContext context,
    OrdersListState state,
    OrdersListController controller,
  ) {
    final l10n = context.l10n;
    switch (state.content) {
      case Loading():
        return ListView(
          padding: const EdgeInsetsDirectional.all(Spacing.s4),
          children: [
            for (var i = 0; i < 5; i++) ...[
              const OrderTileSkeleton(),
              const SizedBox(height: Spacing.s3),
            ],
          ],
        );
      case Failed(:final failure):
        return FailureView(
          failure: failure,
          errorTitle: l10n.ordersLoadErrorTitle,
          onRetry: () => unawaited(controller.retry()),
        );
      case Loaded():
        if (state.isEmpty) {
          return Center(
            child: SingleChildScrollView(
              child: state.status == null
                  ? EmptyState(
                      icon: Icons.receipt_long_outlined,
                      title: l10n.ordersEmptyTitle,
                      body: l10n.ordersEmptyBody,
                      actionLabel: l10n.ordersEmptyAction,
                      primaryAction: true,
                      onAction: () => context.go(Routes.browse.path),
                    )
                  : EmptyState(
                      icon: Icons.filter_alt_off_outlined,
                      title: l10n.ordersEmptyFilteredTitle,
                      actionLabel: l10n.ordersShowAll,
                      onAction: () => unawaited(controller.selectStatus(null)),
                    ),
            ),
          );
        }
        final orders = state.orders;
        return RefreshIndicator(
          onRefresh: controller.refresh,
          child: ListView.separated(
            controller: _scroll,
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsetsDirectional.fromSTEB(
              Spacing.s4,
              Spacing.s2,
              Spacing.s4,
              Spacing.s6,
            ),
            itemCount: orders.length + 1,
            separatorBuilder: (_, _) => const SizedBox(height: Spacing.s3),
            itemBuilder: (context, index) {
              if (index < orders.length) {
                final OrderSummary order = orders[index];
                return OrderTile(
                  order: order,
                  onTap: () => unawaited(
                    context.push(Routes.orderLocation(order.publicId)),
                  ),
                );
              }
              return _Footer(state: state, onRetry: controller.loadMore);
            },
          ),
        );
    }
  }
}

class _Footer extends StatelessWidget {
  const _Footer({required this.state, required this.onRetry});

  final OrdersListState state;
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
            title: l10n.ordersMoreFailed,
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
      return const OrderTileSkeleton();
    }
    return const SizedBox.shrink();
  }
}

class _Filters extends StatelessWidget {
  const _Filters({required this.selected, required this.onSelect});

  final String? selected;
  final Future<void> Function(String? status) onSelect;

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    return SizedBox(
      height: 56,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsetsDirectional.symmetric(horizontal: Spacing.s4),
        itemCount: orderStatusFilters.length,
        separatorBuilder: (_, _) => const SizedBox(width: Spacing.s2),
        itemBuilder: (context, index) {
          final status = orderStatusFilters[index];
          return AppChip(
            label: orderFilterLabel(l10n, status),
            selected: status == selected,
            onTap: () => unawaited(onSelect(status)),
          );
        },
      ),
    );
  }
}

class _SignedOut extends StatelessWidget {
  const _SignedOut();

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    return Center(
      child: SingleChildScrollView(
        child: Column(
          children: [
            EmptyState(
              icon: Icons.lock_outline,
              title: l10n.ordersSignedOutTitle,
              body: l10n.ordersSignedOutBody,
              actionLabel: l10n.ordersSignIn,
              primaryAction: true,
              onAction: () => context.go(
                Uri(
                  path: Routes.login.path,
                  queryParameters: {Routes.fromParameter: Routes.orders.path},
                ).toString(),
              ),
            ),
            Padding(
              padding: const EdgeInsetsDirectional.symmetric(
                horizontal: Spacing.s6,
              ),
              child: Column(
                children: [
                  Text(
                    l10n.guestAccessEntryBody,
                    style: context.type.bodySm.copyWith(
                      color: context.colors.contentSecondary,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: Spacing.s2),
                  AppButton(
                    label: l10n.guestAccessEntry,
                    variant: AppButtonVariant.ghost,
                    expand: true,
                    onPressed: () =>
                        unawaited(context.push(Routes.guestAccess.path)),
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
