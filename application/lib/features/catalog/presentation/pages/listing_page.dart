import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/router/routes.dart';
import 'package:application/core/theme/theme_extensions.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/features/catalog/domain/entities/catalog.dart';
import 'package:application/features/catalog/presentation/controllers/catalog_controllers.dart';
import 'package:application/features/catalog/presentation/widgets/filter_sheets.dart';
import 'package:application/shared/widgets/mobile_app_bar.dart';
import 'package:application/shared/widgets/pill_button.dart';
import 'package:application/shared/widgets/product_grid.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

/// Product listing (04.2 loaded, 04.4 empty, 04.5 skeleton and load-more error) for a
/// category and/or platform, with filters, sort and infinite scroll (the next page loads when
/// the end comes within [loadMoreExtent]).
class ListingPage extends ConsumerStatefulWidget {
  const ListingPage({this.category, this.platform, this.sort, super.key});

  final String? category;
  final String? platform;
  final String? sort;

  /// Distance from the end of the list that triggers the next page.
  static const loadMoreExtent = 600.0;

  @override
  ConsumerState<ListingPage> createState() => _ListingPageState();
}

class _ListingPageState extends ConsumerState<ListingPage> {
  final _scroll = ScrollController();

  CatalogQuery get _base => CatalogQuery(
    category: widget.category,
    platform: widget.platform,
    sort: widget.sort,
  );

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
    if (!_scroll.hasClients) {
      return;
    }
    // After a failed page only the Retry button loads again (no automatic retry loop).
    if (ref.read(listingControllerProvider(_base)).loadMoreFailure != null) {
      return;
    }
    final position = _scroll.position;
    if (position.extentAfter < ListingPage.loadMoreExtent) {
      unawaited(ref.read(listingControllerProvider(_base).notifier).loadMore());
    }
  }

  Future<void> _openFilters(ListingState state) async {
    final filters = await showFiltersSheet(
      context,
      query: state.query,
      currency: state.displayCurrency,
    );
    if (filters == null || !mounted) {
      return;
    }
    await ref
        .read(listingControllerProvider(_base).notifier)
        .applyFilters(
          deliveryType: filters.deliveryType,
          sort: filters.sort,
          minPrice: filters.minPrice,
          maxPrice: filters.maxPrice,
        );
  }

  Future<void> _openSort(ListingState state) async {
    final sort = await showSortSheet(context, current: state.query.sort);
    if (sort == null || !mounted) {
      return;
    }
    final query = state.query;
    await ref
        .read(listingControllerProvider(_base).notifier)
        .applyFilters(
          deliveryType: query.deliveryType,
          minPrice: query.minPrice,
          maxPrice: query.maxPrice,
          sort: sort,
        );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = ref.watch(listingControllerProvider(_base));
    final controller = ref.read(listingControllerProvider(_base).notifier);
    final title = ref.watch(
      listingTitleProvider(
        category: widget.category,
        platform: widget.platform,
      ),
    );
    // A short first page cannot be scrolled: load on until the list fills the screen.
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        _maybeLoadMore();
      }
    });
    final filterCount = activeFilterCount(state.query);
    return Scaffold(
      appBar: MobileAppBar(
        title: title.isEmpty ? l10n.allProductsTitle : title,
        onLeading: () =>
            context.canPop() ? context.pop() : context.go(Routes.browse.path),
      ),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsetsDirectional.symmetric(
              horizontal: Spacing.s4,
            ),
            child: Row(
              children: [
                PillButton(
                  label: filterCount == 0
                      ? l10n.filtersChip
                      : l10n.filtersChipCount(filterCount),
                  icon: filterCount == 0 ? Icons.tune : Icons.check,
                  highlighted: filterCount > 0,
                  onTap: () => unawaited(_openFilters(state)),
                ),
                const SizedBox(width: Spacing.s2),
                PillButton(
                  label: l10n.sortChip(sortLabel(l10n, state.query.sort)),
                  onTap: () => unawaited(_openSort(state)),
                ),
              ],
            ),
          ),
          Expanded(child: _body(context, state, controller, filterCount)),
        ],
      ),
    );
  }

  Widget _body(
    BuildContext context,
    ListingState state,
    ListingController controller,
    int filterCount,
  ) {
    final l10n = context.l10n;
    final failure = state.failure;
    if (state.loading) {
      return const SkeletonRegion(
        child: SingleChildScrollView(
          physics: NeverScrollableScrollPhysics(),
          padding: EdgeInsetsDirectional.all(Spacing.s4),
          child: ProductGridSkeleton(),
        ),
      );
    }
    if (failure != null) {
      return FailureView(
        failure: failure,
        onRetry: () => unawaited(controller.retry()),
      );
    }
    if (state.isEmpty) {
      return Center(
        child: SingleChildScrollView(
          child: filterCount > 0
              ? EmptyState(
                  icon: Icons.search_off,
                  title: l10n.listingEmptyTitle,
                  body: l10n.listingEmptyBody,
                  actionLabel: l10n.clearFilters,
                  onAction: () => unawaited(controller.clearFilters()),
                )
              : EmptyState(
                  icon: Icons.inventory_2_outlined,
                  title: l10n.catalogEmptyTitle,
                  body: l10n.catalogEmptyBody,
                ),
        ),
      );
    }
    final items = state.items;
    final rows = (items.length + 1) ~/ 2;
    final loadMoreFailure = state.loadMoreFailure;
    return Stack(
      children: [
        RefreshIndicator(
          onRefresh: controller.retry,
          child: ListView.builder(
            controller: _scroll,
            padding: const EdgeInsetsDirectional.fromSTEB(
              Spacing.s4,
              0,
              Spacing.s4,
              Spacing.s20,
            ),
            itemCount: rows + 2,
            itemBuilder: (context, index) {
              if (index == 0) {
                return Padding(
                  padding: const EdgeInsetsDirectional.only(
                    top: Spacing.s1,
                    bottom: Spacing.s3,
                  ),
                  child: Semantics(
                    liveRegion: true,
                    child: Text(
                      l10n.productCount(state.total),
                      style: context.type.bodySm.copyWith(
                        color: context.colors.contentMuted,
                      ),
                    ),
                  ),
                );
              }
              if (index == rows + 1) {
                return state.loadingMore
                    ? const Padding(
                        padding: EdgeInsetsDirectional.only(top: Spacing.s4),
                        child: SkeletonRegion(
                          child: ProductGridSkeleton(rows: 1),
                        ),
                      )
                    : const SizedBox.shrink();
              }
              final start = (index - 1) * 2;
              return Padding(
                padding: EdgeInsetsDirectional.only(
                  top: index == 1 ? 0 : Spacing.s4,
                ),
                child: ProductPairRow(
                  products: items.sublist(
                    start,
                    start + 2 > items.length ? items.length : start + 2,
                  ),
                ),
              );
            },
          ),
        ),
        if (loadMoreFailure != null)
          PositionedDirectional(
            start: Spacing.s4,
            end: Spacing.s4,
            bottom: Spacing.s4,
            child: _LoadMoreError(
              message: l10n.loadMoreFailed,
              detail: failureMessage(l10n, loadMoreFailure),
              onRetry: () => unawaited(controller.loadMore()),
            ),
          ),
      ],
    );
  }
}

/// 04.5: inline bar when the next page fails; the loaded products stay.
class _LoadMoreError extends StatelessWidget {
  const _LoadMoreError({
    required this.message,
    required this.detail,
    required this.onRetry,
  });

  final String message;
  final String detail;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    final colors = context.colors;
    return Semantics(
      liveRegion: true,
      container: true,
      child: Material(
        color: colors.surfaceOverlay,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(Radii.md),
          side: BorderSide(color: colors.lineSubtle),
        ),
        child: Padding(
          padding: const EdgeInsetsDirectional.only(
            start: Spacing.s4,
            end: Spacing.s1,
          ),
          child: Row(
            children: [
              Icon(
                Icons.warning_amber_rounded,
                size: 20,
                color: colors.statusDanger,
              ),
              const SizedBox(width: Spacing.s3),
              Expanded(
                child: Text(
                  message,
                  semanticsLabel: '$message $detail',
                  style: context.type.bodyMd,
                ),
              ),
              TextButton(
                onPressed: onRetry,
                style: TextButton.styleFrom(
                  minimumSize: const Size(48, 48),
                  foregroundColor: colors.accentText,
                ),
                child: Text(context.l10n.actionRetry),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
