import 'package:integration_test/integration_test_driver.dart';

/// Host side of the frame-timing check (`integration_test/perf_test.dart`): writes each
/// `watchPerformance` summary to `build/<report key>.timeline_summary.json`.
Future<void> main() => integrationDriver(
  responseDataCallback: (data) async {
    if (data == null) {
      return;
    }
    for (final entry in data.entries) {
      await writeResponseData(
        entry.value as Map<String, dynamic>,
        testOutputFilename: '${entry.key}.timeline_summary',
      );
    }
  },
);
