import 'dart:async';

import 'package:application/core/l10n/l10n.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/search/data/search_history_store.dart';
import 'package:application/features/search/domain/entities/search.dart';
import 'package:application/features/search/search_providers.dart';
import 'package:flutter/foundation.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'search_controller.g.dart';

@immutable
final class SearchState {
  const SearchState({
    required this.history,
    required this.popular,
    this.query = '',
    this.suggestions,
    this.submitted = false,
  });

  /// The text in the field (trimmed).
  final String query;

  /// Suggestions for [query]; null while the query is shorter than
  /// [StoreSearchController.minLength].
  final Loadable<List<SearchSuggestion>>? suggestions;

  /// The customer submitted [query] (keyboard search, a history or popular term).
  final bool submitted;

  /// Local recent searches, newest first.
  final List<String> history;

  /// `GET /storefront/search/popular`.
  final Loadable<List<String>> popular;

  /// 04.7: a submitted search found nothing.
  bool get noResults =>
      submitted &&
      suggestions is Loaded<List<SearchSuggestion>> &&
      (suggestions! as Loaded<List<SearchSuggestion>>).value.isEmpty;

  SearchState copyWith({
    String? query,
    Loadable<List<SearchSuggestion>>? Function()? suggestions,
    bool? submitted,
    List<String>? history,
    Loadable<List<String>>? popular,
  }) => SearchState(
    query: query ?? this.query,
    suggestions: suggestions == null ? this.suggestions : suggestions(),
    submitted: submitted ?? this.submitted,
    history: history ?? this.history,
    popular: popular ?? this.popular,
  );
}

/// Search (04.6, 04.7): suggestions while typing (debounced, 2 to 100 characters), `commit=1`
/// on submit so the server counts the term, popular terms and the local history.
@riverpod
class StoreSearchController extends _$StoreSearchController {
  static const minLength = 2;
  static const maxLength = 100;
  static const debounce = Duration(milliseconds: 300);

  Timer? _debounce;
  int _generation = 0;

  @override
  SearchState build() {
    ref
      ..watch(appLocaleProvider)
      ..onDispose(() => _debounce?.cancel());
    unawaited(_loadPopular());
    return SearchState(
      history: ref.read(searchHistoryStoreProvider).read(),
      popular: const Loadable.loading(),
    );
  }

  /// The field changed: suggestions follow after [debounce].
  void onQueryChanged(String text) {
    final query = _clip(text);
    _debounce?.cancel();
    if (query == state.query && !state.submitted) {
      return;
    }
    _generation++;
    if (query.length < minLength) {
      state = state.copyWith(
        query: query,
        suggestions: () => null,
        submitted: false,
      );
      return;
    }
    state = state.copyWith(
      query: query,
      submitted: false,
      suggestions: () => state.suggestions ?? const Loadable.loading(),
    );
    _debounce = Timer(
      debounce,
      () => unawaited(_suggest(query, commit: false)),
    );
  }

  /// Submits [text]: counted as a search by the server and saved in the history.
  Future<void> submit(String text) async {
    final query = _clip(text);
    _debounce?.cancel();
    _generation++;
    if (query.length < minLength) {
      state = state.copyWith(query: query, suggestions: () => null);
      return;
    }
    final history = await ref.read(searchHistoryStoreProvider).add(query);
    if (!ref.mounted) {
      return;
    }
    state = state.copyWith(
      query: query,
      submitted: true,
      history: history,
      suggestions: () => const Loadable.loading(),
    );
    await _suggest(query, commit: true);
  }

  Future<void> retry() => state.submitted
      ? submit(state.query)
      : _suggest(state.query, commit: false);

  Future<void> retryPopular() async {
    state = state.copyWith(popular: const Loadable.loading());
    await _loadPopular();
  }

  Future<void> removeFromHistory(String term) async {
    final history = await ref.read(searchHistoryStoreProvider).remove(term);
    if (ref.mounted) {
      state = state.copyWith(history: history);
    }
  }

  Future<void> clearHistory() async {
    await ref.read(searchHistoryStoreProvider).clear();
    if (ref.mounted) {
      state = state.copyWith(history: const []);
    }
  }

  Future<void> _suggest(String query, {required bool commit}) async {
    final generation = ++_generation;
    final result = await ref
        .read(searchRepositoryProvider)
        .suggest(query, commit: commit);
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = state.copyWith(
      suggestions: () => result.fold(Loadable.loaded, Loadable.failed),
    );
  }

  Future<void> _loadPopular() async {
    final result = await ref.read(searchRepositoryProvider).popular();
    if (ref.mounted) {
      state = state.copyWith(
        popular: result.fold(Loadable.loaded, Loadable.failed),
      );
    }
  }

  static String _clip(String text) {
    final trimmed = text.trim();
    return trimmed.length > maxLength
        ? trimmed.substring(0, maxLength)
        : trimmed;
  }
}
