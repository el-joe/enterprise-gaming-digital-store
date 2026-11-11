import 'package:application/core/storage/preferences_store.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'search_history_store.g.dart';

/// Local search history (no API exists for it): newest first, case-insensitive duplicates
/// collapsed, at most [capacity] terms of at most [maxLength] characters. Stored in the
/// non-sensitive preferences, never in secure storage, and cleared by the customer.
class SearchHistoryStore {
  SearchHistoryStore(this._prefs);

  static const capacity = 10;
  static const maxLength = 100;

  final PreferencesStore _prefs;

  List<String> read() => List.unmodifiable(_prefs.searchHistory.take(capacity));

  /// Puts [term] first and returns the new history. Blank terms are ignored.
  Future<List<String>> add(String term) async {
    final trimmed = term.trim();
    if (trimmed.isEmpty) {
      return read();
    }
    final value = trimmed.length > maxLength
        ? trimmed.substring(0, maxLength)
        : trimmed;
    final key = value.toLowerCase();
    final next = [
      value,
      ...read().where((existing) => existing.toLowerCase() != key),
    ].take(capacity).toList();
    await _prefs.setSearchHistory(next);
    return List.unmodifiable(next);
  }

  Future<List<String>> remove(String term) async {
    final key = term.toLowerCase();
    final next = read()
        .where((existing) => existing.toLowerCase() != key)
        .toList();
    await _prefs.setSearchHistory(next);
    return List.unmodifiable(next);
  }

  Future<void> clear() => _prefs.setSearchHistory(const []);
}

@Riverpod(keepAlive: true)
SearchHistoryStore searchHistoryStore(Ref ref) =>
    SearchHistoryStore(ref.watch(preferencesStoreProvider));
