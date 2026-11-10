<?php

/**
 * Runs the static n8n workflow validator (n8n/scripts/validate-workflows.mjs) as part of the
 * suite: every workflow parses, has the required structure, Z01 as its error workflow, no
 * credential values or secrets, and a document with every required heading. The workflows are
 * authored and validated, not executed: n8n is not installed here.
 */

use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\ExecutableFinder;

it('validates every n8n workflow and its documentation', function (): void {
    $node = (new ExecutableFinder)->find('node');
    expect($node)->not->toBeNull('Node.js is required to run n8n/scripts/validate-workflows.mjs');

    $result = Process::path(dirname(base_path()))->timeout(60)->run([$node, 'n8n/scripts/validate-workflows.mjs']);

    expect($result->exitCode())->toBe(0, $result->errorOutput().$result->output())
        ->and($result->output())->toContain('58 workflows, 58 documents');
});

it('covers exactly the seeded workflow inventory', function (): void {
    $inventory = require database_path('seeders/data/automation_workflows.php');
    $files = collect(glob(dirname(base_path()).'/n8n/workflows/*/*.json'))
        ->map(fn (string $path): string => substr(basename($path), 0, 3))
        ->sort()
        ->values()
        ->all();

    expect($files)->toBe(collect(array_keys($inventory))->sort()->values()->all());
});
