#!/usr/bin/env bash
# CI gate for the Flutter app: dependencies, generated code check, format, analyze, tests with coverage.
# Run from anywhere: `bash application/tool/ci.sh`.
set -euo pipefail

cd "$(dirname "$0")/.."

echo "==> flutter pub get"
flutter pub get

echo "==> generated code is up to date"
generated_hashes() {
  find lib \( -name '*.g.dart' -o -name '*.freezed.dart' -o -path 'lib/core/l10n/generated/*' \) -type f \
    -print0 | sort -z | xargs -0 sha256sum
}
before="$(generated_hashes)"
flutter gen-l10n
dart run build_runner build
after="$(generated_hashes)"
if [ "$before" != "$after" ]; then
  echo "Committed generated files are stale. Run 'flutter gen-l10n' and 'dart run build_runner build', then commit the output." >&2
  diff <(echo "$before") <(echo "$after") >&2 || true
  exit 1
fi

echo "==> format"
dart format --output=none --set-exit-if-changed lib test

echo "==> flutter analyze"
flutter analyze --fatal-infos

echo "==> dart analyze (includes riverpod_lint)"
dart analyze --fatal-infos

echo "==> flutter test with coverage"
flutter test --coverage

echo "CI passed."
