import 'dart:io';

import 'package:flutter_test/flutter_test.dart';

/// Money is never a `double` (MOBILE_APP_ROADMAP.md rule 6): no model, entity, data source,
/// repository or use case of the app uses the type, generated code included.
void main() {
  test('no feature or shared source uses double', () {
    final offenders = <String>[];
    for (final root in [
      'lib/features',
      'lib/shared/data',
      'lib/shared/domain',
      'lib/core/money',
    ]) {
      for (final entity in Directory(root).listSync(recursive: true)) {
        if (entity is! File || !entity.path.endsWith('.dart')) {
          continue;
        }
        final lines = entity.readAsLinesSync();
        for (var i = 0; i < lines.length; i++) {
          final line = lines[i];
          if (RegExp(r'\bdouble\b').hasMatch(line) &&
              !line.trimLeft().startsWith('///') &&
              !line.trimLeft().startsWith('//')) {
            offenders.add('${entity.path}:${i + 1}: $line');
          }
        }
      }
    }
    expect(offenders, isEmpty);
  });
}
