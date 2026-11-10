#!/usr/bin/env bash
# Copies the backend contract fixtures (docs/api-fixtures, written by the Pest suite
# backend/tests/Feature/MobileContract, gap G11) into test/fixtures/api.
#
#   bash tool/sync_fixtures.sh          copy (replaces test/fixtures/api)
#   bash tool/sync_fixtures.sh --check  exit 1 when the app copy differs from docs/api-fixtures
#
# The app copy is committed so Flutter CI does not need PHP; tool/ci.sh runs --check whenever
# ../docs/api-fixtures exists (a full repository checkout).
set -euo pipefail

cd "$(dirname "$0")/.."

source_dir="../docs/api-fixtures"
target_dir="test/fixtures/api"

if [ ! -d "$source_dir" ]; then
  echo "No $source_dir: run the backend contract tests with UPDATE_API_FIXTURES=1 first." >&2
  exit 1
fi

if [ "${1:-}" = "--check" ]; then
  if ! diff -r "$source_dir" "$target_dir" >/dev/null 2>&1; then
    echo "test/fixtures/api is out of sync with docs/api-fixtures. Run 'bash tool/sync_fixtures.sh' and commit the result." >&2
    diff -rq "$source_dir" "$target_dir" >&2 || true
    exit 1
  fi
  echo "Fixtures in sync ($(find "$target_dir" -name '*.json' | wc -l) files)."
  exit 0
fi

rm -rf "$target_dir"
mkdir -p "$target_dir"
cp -R "$source_dir"/. "$target_dir"/
echo "Copied $(find "$target_dir" -name '*.json' | wc -l) fixtures into $target_dir."
