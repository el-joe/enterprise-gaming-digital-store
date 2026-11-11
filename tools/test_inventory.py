#!/usr/bin/env python3
"""Build docs/TEST_INVENTORY.md from the test sources WITHOUT running any test.

Backend: Pest files under backend/tests (describe / it / test, and whether a dataset is attached).
App:     Flutter files under application/test (group / test / testWidgets).

Usage:  python3 tools/test_inventory.py      (writes docs/TEST_INVENTORY.md)
"""
from __future__ import annotations

import re
from datetime import date
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / "docs" / "TEST_INVENTORY.md"

# A quoted string literal: '...' or "..." (no escaped-quote handling needed for test names here).
STR = r"""(?P<q>['"])(?P<name>(?:\\.|(?!(?P=q)).)*?)(?P=q)"""
PHP_CALL = re.compile(r"\b(?P<kind>describe|it|test)\(\s*" + STR, re.S)
DART_CALL = re.compile(r"\b(?P<kind>group|test|testWidgets)\(\s*" + STR, re.S)


def clean(name: str) -> str:
    return re.sub(r"\s+", " ", name.replace("\\'", "'").replace('\\"', '"')).strip()


def php_entries(text: str) -> list[tuple[str, str, bool]]:
    """(kind, name, has_dataset) in source order. Datasets: a `->with(` before the next call."""
    matches = list(PHP_CALL.finditer(text))
    entries = []
    for i, m in enumerate(matches):
        end = matches[i + 1].start() if i + 1 < len(matches) else len(text)
        has_dataset = m.group("kind") != "describe" and "->with(" in text[m.end():end]
        entries.append((m.group("kind"), clean(m.group("name")), has_dataset))
    return entries


def dart_entries(text: str) -> list[tuple[str, str, bool]]:
    return [(m.group("kind"), clean(m.group("name")), False) for m in DART_CALL.finditer(text)]


def collect(base: Path, pattern: str, parser) -> list[tuple[Path, list[tuple[str, str, bool]]]]:
    files = sorted(p for p in base.rglob(pattern) if "/build/" not in str(p))
    return [(f, parser(f.read_text(encoding="utf-8", errors="replace"))) for f in files]


def area_of(path: Path, base: Path, depth: int) -> str:
    parts = path.relative_to(base).parts[:-1]
    return "/".join(parts[:depth]) or "(root)"


def section(title: str, base: Path, data, depth: int, case_kinds: set[str], group_kind: str) -> list[str]:
    lines: list[str] = [f"## {title}", ""]
    areas: dict[str, list] = {}
    for f, entries in data:
        areas.setdefault(area_of(f, base, depth), []).append((f, entries))

    total_files = sum(len(v) for v in areas.values())
    total_cases = sum(1 for _, es in data for k, _, _ in es if k in case_kinds)
    total_ds = sum(1 for _, es in data for k, _, d in es if k in case_kinds and d)
    lines += [
        f"{total_files} files, {total_cases} declared test cases"
        + (f" ({total_ds} with datasets, which run once per dataset row)" if total_ds else "")
        + ".",
        "",
        "| Area | Files | Declared cases |",
        "|---|---:|---:|",
    ]
    for area, items in sorted(areas.items()):
        cases = sum(1 for _, es in items for k, _, _ in es if k in case_kinds)
        lines.append(f"| `{area}` | {len(items)} | {cases} |")
    lines.append("")

    for area, items in sorted(areas.items()):
        lines += [f"### `{area}`", ""]
        for f, entries in items:
            rel = f.relative_to(ROOT)
            cases = [e for e in entries if e[0] in case_kinds]
            lines.append(f"<details><summary><code>{rel}</code> ({len(cases)})</summary>")
            lines.append("")
            for kind, name, has_ds in entries:
                if kind == group_kind:
                    lines.append(f"- **{name}**")
                else:
                    lines.append(f"  - {name}" + (" _(dataset)_" if has_ds else ""))
            lines += ["", "</details>", ""]
    return lines


def main() -> None:
    backend = collect(ROOT / "backend" / "tests", "*Test.php", php_entries)
    app = collect(ROOT / "application" / "test", "*_test.dart", dart_entries)
    goldens = sorted((ROOT / "application" / "test" / "goldens").rglob("*.png"))

    lines = [
        "# Test inventory",
        "",
        f"Generated {date.today().isoformat()} by `python3 tools/test_inventory.py` from the test sources. "
        "No test was run to produce it. Regenerate before the final run.",
        "",
        "Owner instruction (2026-11-11): tests are written with each milestone but are run only once, "
        "after every milestone is finished. The commands for that single run are at the end of this file.",
        "",
        "Last measured results, before the no-run rule: backend 2,842 passed (M-A15, commit `c7ac8f6`); "
        "app 1,268 passed (M-A16, commit `0bd6b36`). Counts below are declared cases. They expand at run time: Pest datasets run "
        "once per row, and many app tests are declared inside loops over locales (en, ar) and directions (LTR, RTL), "
        "so both runs report more tests than are declared here.",
        "",
    ]
    lines += section("Backend (Laravel, Pest)", ROOT / "backend" / "tests", backend, 2, {"it", "test"}, "describe")
    lines += section("App (Flutter)", ROOT / "application" / "test", app, 2, {"test", "testWidgets"}, "group")
    lines += [
        "## App golden images",
        "",
        f"{len(goldens)} committed reference images under `application/test/goldens/`. "
        "Goldens written after the no-run rule have no image yet; the final run creates them with `--update-goldens`, "
        "and each new image must be reviewed before it is committed.",
        "",
        "## The single final run",
        "",
        "Run in this order, each on its own; never with `--parallel` (the suites share the `gaming-store_testing` database).",
        "",
        "```bash",
        "cd /var/www/enterprise-gaming-digital-store/backend && vendor/bin/pint --test && php artisan test --compact",
        "```",
        "",
        "```bash",
        "cd /var/www/enterprise-gaming-digital-store/application && PATH=/home/joe/flutter/bin:$PATH flutter test --update-goldens test/goldens",
        "```",
        "",
        "```bash",
        "cd /var/www/enterprise-gaming-digital-store/application && PATH=/home/joe/flutter/bin:$PATH bash tool/ci.sh",
        "```",
        "",
    ]
    OUT.write_text("\n".join(lines), encoding="utf-8")
    print(f"wrote {OUT.relative_to(ROOT)}: backend {len(backend)} files, app {len(app)} files, {len(goldens)} goldens")


if __name__ == "__main__":
    main()
