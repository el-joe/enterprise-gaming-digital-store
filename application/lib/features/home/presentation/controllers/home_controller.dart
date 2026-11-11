import 'dart:async';

import 'package:application/core/money/pricing_context.dart';
import 'package:application/core/state/loadable.dart';
import 'package:application/features/home/domain/entities/home.dart';
import 'package:application/features/home/home_providers.dart';
import 'package:riverpod_annotation/riverpod_annotation.dart';

part 'home_controller.g.dart';

/// Home (03.1 to 03.4): `GET /storefront/home`. Reloads when the display currency or the
/// language changes ([pricingContextProvider]); a failure stays until [retry].
@riverpod
class HomeController extends _$HomeController {
  int _generation = 0;

  @override
  Loadable<HomePage> build() {
    ref.watch(pricingContextProvider);
    unawaited(_load());
    return const Loadable.loading();
  }

  Future<void> retry() async {
    state = const Loadable.loading();
    await _load();
  }

  /// Pull to refresh: keeps the page on screen while it reloads.
  Future<void> refresh() => _load();

  Future<void> _load() async {
    final generation = ++_generation;
    final result = await ref.read(homeRepositoryProvider).fetch();
    if (!ref.mounted || generation != _generation) {
      return;
    }
    state = result.fold(Loadable.loaded, Loadable.failed);
  }
}

/// Sections the home renders: the items the app knows (unknown types dropped) and only
/// sections left with at least one item.
List<HomeSection> visibleSections(HomePage page) => [
  for (final section in page.sections)
    if (section.items.any((item) => item is! HomeUnknownItem))
      section.copyWith(
        items: [
          for (final item in section.items)
            if (item is! HomeUnknownItem) item,
        ],
      ),
];
