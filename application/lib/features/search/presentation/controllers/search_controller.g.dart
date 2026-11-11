// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'search_controller.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint, type=warning
/// Search (04.6, 04.7): suggestions while typing (debounced, 2 to 100 characters), `commit=1`
/// on submit so the server counts the term, popular terms and the local history.

@ProviderFor(StoreSearchController)
final storeSearchControllerProvider = StoreSearchControllerProvider._();

/// Search (04.6, 04.7): suggestions while typing (debounced, 2 to 100 characters), `commit=1`
/// on submit so the server counts the term, popular terms and the local history.
final class StoreSearchControllerProvider
    extends $NotifierProvider<StoreSearchController, SearchState> {
  /// Search (04.6, 04.7): suggestions while typing (debounced, 2 to 100 characters), `commit=1`
  /// on submit so the server counts the term, popular terms and the local history.
  StoreSearchControllerProvider._()
    : super(
        from: null,
        argument: null,
        retry: null,
        name: r'storeSearchControllerProvider',
        isAutoDispose: true,
        dependencies: null,
        $allTransitiveDependencies: null,
      );

  @override
  String debugGetCreateSourceHash() => _$storeSearchControllerHash();

  @$internal
  @override
  StoreSearchController create() => StoreSearchController();

  /// {@macro riverpod.override_with_value}
  Override overrideWithValue(SearchState value) {
    return $ProviderOverride(
      origin: this,
      providerOverride: $SyncValueProvider<SearchState>(value),
    );
  }
}

String _$storeSearchControllerHash() =>
    r'bb10fd0fcf93d4d479db26d4f0afe8fe62651d33';

/// Search (04.6, 04.7): suggestions while typing (debounced, 2 to 100 characters), `commit=1`
/// on submit so the server counts the term, popular terms and the local history.

abstract class _$StoreSearchController extends $Notifier<SearchState> {
  SearchState build();
  @$mustCallSuper
  @override
  WhenComplete runBuild() {
    final ref = this.ref as $Ref<SearchState, SearchState>;
    final element =
        ref.element
            as $ClassProviderElement<
              AnyNotifier<SearchState, SearchState>,
              SearchState,
              Object?,
              Object?
            >;
    return element.handleCreate(ref, build);
  }
}
