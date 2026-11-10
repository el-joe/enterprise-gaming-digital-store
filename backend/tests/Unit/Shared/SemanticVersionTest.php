<?php

use App\Domains\Shared\ValueObjects\SemanticVersion;

it('orders versions by SemVer precedence, not as strings', function (string $lower, string $higher): void {
    expect(SemanticVersion::of($lower)->compare(SemanticVersion::of($higher)))->toBe(-1)
        ->and(SemanticVersion::of($higher)->compare(SemanticVersion::of($lower)))->toBe(1)
        ->and(SemanticVersion::of($lower)->lessThan(SemanticVersion::of($higher)))->toBeTrue();
})->with([
    'patch' => ['1.4.0', '1.4.1'],
    'numeric minor' => ['1.9.0', '1.10.0'],
    'numeric major' => ['2.0.0', '10.0.0'],
    'prerelease below release' => ['1.4.0-beta.1', '1.4.0'],
    'prerelease numeric part' => ['1.0.0-beta.2', '1.0.0-beta.11'],
    'numeric below word' => ['1.0.0-1', '1.0.0-alpha'],
    'longer prerelease set' => ['1.0.0-alpha', '1.0.0-alpha.1'],
    'words in ASCII order' => ['1.0.0-alpha.beta', '1.0.0-beta'],
]);

it('treats build metadata as equal', function (): void {
    expect(SemanticVersion::of('1.4.0+build.7')->compare(SemanticVersion::of('1.4.0')))->toBe(0)
        ->and((string) SemanticVersion::of('1.4.0-rc.1+abc'))->toBe('1.4.0-rc.1');
});

it('refuses what is not a semantic version', function (string $value): void {
    expect(SemanticVersion::isValid($value))->toBeFalse()
        ->and(fn () => SemanticVersion::of($value))->toThrow(InvalidArgumentException::class);
})->with(['1.4', '01.4.0', '1.4.0.1', 'v1.4.0', '1.4.0-', '', '1.4.0-01']);
