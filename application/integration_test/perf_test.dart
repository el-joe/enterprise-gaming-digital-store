import 'package:application/core/network/result.dart';
import 'package:application/core/router/app_router.dart';
import 'package:application/core/router/routes.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_test/flutter_test.dart';

import '../test/helpers/catalog_fakes.dart';
import 'helpers/journey.dart';

/// Frame budgets checked in profile mode (M-A19 "list jank"). A 60 Hz frame has 16.7 ms; the
/// build and raster phases each get 16 ms at the 90th percentile, and at most 5% of the frames
/// may miss the build budget.
const p90BuildBudgetMs = 16.0;
const p90RasterBudgetMs = 16.0;
const missedBuildShare = 0.05;

/// Lightweight frame-timing check: flings the home page and a 200-product listing (infinite
/// scroll, lazily built grid) and records `FrameTiming` summaries with
/// `IntegrationTestWidgetsFlutterBinding.watchPerformance`. The numbers are reported to the
/// driver (`test_driver/perf_driver.dart` writes them to `build/`). Budgets are asserted only in
/// profile mode, which needs a physical device; a debug run records the numbers but does not
/// judge them.
///
///     flutter drive --profile --flavor dev --driver=test_driver/perf_driver.dart \
///       --target=integration_test/perf_test.dart -d <device>
void main() {
  final binding = initJourneys();

  void checkBudgets(String reportKey) {
    final summary = binding.reportData![reportKey] as Map<String, dynamic>;
    final frames = summary['frame_count'] as int;
    debugPrint(
      '$reportKey: $frames frames, '
      'p90 build ${summary['90th_percentile_frame_build_time_millis']} ms, '
      'p90 raster ${summary['90th_percentile_frame_rasterizer_time_millis']} ms, '
      'missed build ${summary['missed_frame_build_budget_count']}',
    );
    if (!kProfileMode) {
      return;
    }
    expect(
      summary['90th_percentile_frame_build_time_millis'] as num,
      lessThanOrEqualTo(p90BuildBudgetMs),
      reason: '$reportKey p90 build',
    );
    expect(
      summary['90th_percentile_frame_rasterizer_time_millis'] as num,
      lessThanOrEqualTo(p90RasterBudgetMs),
      reason: '$reportKey p90 raster',
    );
    expect(
      summary['missed_frame_build_budget_count'] as int,
      lessThanOrEqualTo((frames * missedBuildShare).ceil()),
      reason: '$reportKey missed build budget',
    );
  }

  Future<void> fling(WidgetTester tester, {int times = 6}) async {
    for (var i = 0; i < times; i++) {
      await tester.fling(
        find.byType(Scrollable).first,
        Offset(0, i.isEven ? -700 : -500),
        2400,
      );
      await tester.pumpAndSettle();
    }
  }

  testWidgets('home scrolls within the frame budget', (tester) async {
    await launchApp(
      tester,
      arrange: (h) => h.home.results = [Ok(fixtureHome())],
    );
    await binding.watchPerformance(
      () => fling(tester, times: 4),
      reportKey: 'home_scroll',
    );
    checkBudgets('home_scroll');
  });

  testWidgets('a 200-product listing scrolls within the frame budget', (
    tester,
  ) async {
    final h = await launchApp(tester, arrange: (h) => h.catalog.total = 200);
    h.read(appRouterProvider).go(Routes.listingLocation());
    await tester.pumpAndSettle();
    await binding.watchPerformance(
      () => fling(tester),
      reportKey: 'listing_scroll',
    );
    checkBudgets('listing_scroll');
  });
}
