import 'dart:async';

import 'package:application/core/l10n/failure_messages.dart';
import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/core/state/paged_list.dart';
import 'package:application/core/theme/tokens/radii.dart';
import 'package:application/core/theme/tokens/spacing.dart';
import 'package:application/shared/widgets/app_button.dart';
import 'package:application/shared/widgets/skeleton.dart';
import 'package:application/shared/widgets/state_views.dart';
import 'package:application/shared/widgets/status_banner.dart';
import 'package:flutter/material.dart';

/// A `meta`-paged list (M-A17 screens): skeleton rows while page 1 loads, the error or offline
/// view with retry, [empty] when there is nothing, otherwise [header] then the rows with pull to
/// refresh. The next page loads 600 px before the end (and when a short page does not fill the
/// screen); a failed page shows a retry bar and is never retried on its own.
class PagedListView<T> extends StatefulWidget {
  const PagedListView({
    required this.state,
    required this.itemBuilder,
    required this.empty,
    required this.errorTitle,
    required this.moreFailedTitle,
    required this.onRetry,
    required this.onRefresh,
    required this.onLoadMore,
    this.header = const [],
    this.emptyKeepsHeader = false,
    this.skeletonHeight = 88,
    this.spacing = Spacing.s2,
    super.key,
  });

  final PagedListState<T> state;
  final Widget Function(BuildContext context, T item) itemBuilder;
  final Widget empty;
  final String errorTitle;
  final String moreFailedTitle;
  final VoidCallback onRetry;
  final Future<void> Function() onRefresh;
  final VoidCallback onLoadMore;

  /// Widgets above the rows (banners, section labels).
  final List<Widget> header;

  /// Shows [header] above [empty] too.
  final bool emptyKeepsHeader;
  final double skeletonHeight;
  final double spacing;

  @override
  State<PagedListView<T>> createState() => _PagedListViewState<T>();
}

class _PagedListViewState<T> extends State<PagedListView<T>> {
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
    final state = widget.state;
    if (_scroll.hasClients &&
        _scroll.position.extentAfter < 600 &&
        state.hasMore &&
        !state.loadingMore &&
        state.moreFailure == null) {
      widget.onLoadMore();
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = context.l10n;
    final state = widget.state;
    const padding = EdgeInsetsDirectional.fromSTEB(
      Spacing.s4,
      Spacing.s2,
      Spacing.s4,
      Spacing.s6,
    );
    switch (state.content) {
      case Loading():
        return SkeletonRegion(
          child: ListView(
            physics: const NeverScrollableScrollPhysics(),
            padding: padding,
            children: [
              for (var i = 0; i < 4; i++) ...[
                if (i > 0) SizedBox(height: widget.spacing),
                SkeletonBlock(height: widget.skeletonHeight, radius: Radii.md),
              ],
            ],
          ),
        );
      case Failed(:final failure):
        return FailureView(
          failure: failure,
          errorTitle: widget.errorTitle,
          onRetry: widget.onRetry,
        );
      case Loaded():
        break;
    }
    // A short first page that does not fill the screen still loads the next one.
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        _maybeLoadMore();
      }
    });
    final items = state.items;
    final children = <Widget>[
      if (items.isNotEmpty || widget.emptyKeepsHeader) ...widget.header,
      if (items.isEmpty)
        widget.empty
      else
        for (var i = 0; i < items.length; i++) ...[
          if (i > 0) SizedBox(height: widget.spacing),
          widget.itemBuilder(context, items[i]),
        ],
      if (state.moreFailure case final failure?) ...[
        const SizedBox(height: Spacing.s3),
        StatusBanner(
          tone: BannerTone.danger,
          title: widget.moreFailedTitle,
          body: failureDetail(l10n, failure),
        ),
        const SizedBox(height: Spacing.s2),
        AppButton(
          label: l10n.actionRetry,
          icon: Icons.refresh,
          variant: AppButtonVariant.secondary,
          expand: true,
          onPressed: state.hasMore
              ? widget.onLoadMore
              : () => unawaited(widget.onRefresh()),
        ),
      ] else if (state.loadingMore || state.hasMore) ...[
        SizedBox(height: widget.spacing),
        SkeletonRegion(
          child: SkeletonBlock(height: widget.skeletonHeight, radius: Radii.md),
        ),
      ],
    ];
    return RefreshIndicator(
      onRefresh: widget.onRefresh,
      child: ListView(
        controller: _scroll,
        physics: const AlwaysScrollableScrollPhysics(),
        padding: padding,
        children: children,
      ),
    );
  }
}
