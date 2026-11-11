import 'package:application/core/network/failure.dart';
import 'package:application/core/network/result.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/shared/domain/entities/catalog_refs.dart';
import 'package:flutter/foundation.dart';

/// State of a `meta`-paged list screen (tickets, refund requests, reviews, notifications):
/// the first page as a [Loadable], the pages appended so far, and the state of the next page.
@immutable
final class PagedListState<T> {
  const PagedListState({
    this.content = const Loadable.loading(),
    this.page,
    this.loadingMore = false,
    this.moreFailure,
    this.refreshing = false,
  });

  /// Every item loaded so far (pages appended), or the first page's loading or failure.
  final Loadable<List<T>> content;

  /// The last loaded page's meta.
  final PageInfo? page;
  final bool loadingMore;

  /// A next page failed: the list stays, with a retry bar (no automatic retry loop).
  final Failure? moreFailure;
  final bool refreshing;

  List<T> get items => content.valueOrNull ?? const [];

  bool get hasMore => page?.hasNextPage ?? false;

  /// Loaded with no item.
  bool get isEmpty => content is Loaded<List<T>> && items.isEmpty;

  PagedListState<T> copyWith({
    Loadable<List<T>>? content,
    PageInfo? Function()? page,
    bool? loadingMore,
    Failure? Function()? moreFailure,
    bool? refreshing,
  }) => PagedListState(
    content: content ?? this.content,
    page: page == null ? this.page : page(),
    loadingMore: loadingMore ?? this.loadingMore,
    moreFailure: moreFailure == null ? this.moreFailure : moreFailure(),
    refreshing: refreshing ?? this.refreshing,
  );

  /// The same pages with [items] replaced (an item marked read, a review edited).
  PagedListState<T> withItems(List<T> items) => content is Loaded<List<T>>
      ? copyWith(content: Loadable.loaded(items))
      : this;
}

/// The paging rules shared by the list controllers, as M-A14 and M-A15 built them: page 1 on
/// load, retry and pull to refresh (the list stays while it reloads, and a failed refresh keeps
/// it), the next page only while `meta.current_page < last_page` and none is loading, a failed
/// page kept until the customer retries, and a late answer from before a reload dropped.
///
/// A controller owns one, passing its state accessors, so the controller stays the only writer.
final class PagedLoader<T> {
  PagedLoader({
    required this.fetch,
    required this.read,
    required this.write,
    required this.mounted,
  });

  /// One page (1-based).
  final Future<Result<Paged<T>>> Function(int page) fetch;
  final PagedListState<T> Function() read;
  final void Function(PagedListState<T> state) write;
  final bool Function() mounted;

  int _generation = 0;

  /// Drops any answer still on its way (a sign-out, a reset).
  void cancel() => _generation++;

  /// Loads page 1. With [keep] (pull to refresh) a loaded list stays on screen while it reloads
  /// and a failure only shows as [PagedListState.moreFailure].
  Future<void> loadFirst({bool keep = false}) async {
    final generation = ++_generation;
    final result = await fetch(1);
    if (!mounted() || generation != _generation) {
      return;
    }
    final state = read();
    switch (result) {
      case Ok(:final value):
        write(
          state.copyWith(
            content: Loadable.loaded(value.items),
            page: () => value.page,
            loadingMore: false,
            moreFailure: () => null,
            refreshing: false,
          ),
        );
      case Err(:final failure):
        write(
          keep && state.content is Loaded<List<T>>
              ? state.copyWith(refreshing: false, moreFailure: () => failure)
              : state.copyWith(
                  content: Loadable.failed(failure),
                  page: () => null,
                  refreshing: false,
                ),
        );
    }
  }

  /// "Try again" after a failed first page.
  Future<void> retry() async {
    write(PagedListState<T>());
    await loadFirst();
  }

  /// Pull to refresh.
  Future<void> refresh() async {
    write(read().copyWith(refreshing: true));
    await loadFirst(keep: true);
  }

  /// The next page, when there is one and none is loading.
  Future<void> loadMore() async {
    final state = read();
    final page = state.page;
    if (state.loadingMore ||
        page == null ||
        !page.hasNextPage ||
        state.content is! Loaded<List<T>>) {
      return;
    }
    final generation = _generation;
    write(state.copyWith(loadingMore: true, moreFailure: () => null));
    final result = await fetch(page.currentPage + 1);
    if (!mounted() || generation != _generation) {
      return;
    }
    final current = read();
    write(switch (result) {
      Ok(:final value) => current.copyWith(
        content: Loadable.loaded([...current.items, ...value.items]),
        page: () => value.page,
        loadingMore: false,
      ),
      Err(:final failure) => current.copyWith(
        loadingMore: false,
        moreFailure: () => failure,
      ),
    });
  }
}
