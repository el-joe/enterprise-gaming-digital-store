<?php

namespace App\Domains\Shared\ValueObjects;

use InvalidArgumentException;
use Stringable;

/**
 * A Semantic Versioning 2.0.0 version (`MAJOR.MINOR.PATCH`, optional `-prerelease` and ignored
 * `+build`), compared by SemVer precedence: numeric parts numerically (1.10.0 > 1.9.0), a
 * prerelease below its release (1.0.0-beta < 1.0.0), prerelease identifiers numerically when
 * both are numeric, else in ASCII order, and a numeric identifier below a word.
 */
final readonly class SemanticVersion implements Stringable
{
    public const string PATTERN = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[A-Za-z-][0-9A-Za-z-]*)(?:\.(?:0|[1-9]\d*|\d*[A-Za-z-][0-9A-Za-z-]*))*))?(?:\+([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?$/';

    /**
     * @param  list<string>  $prerelease
     */
    private function __construct(
        public int $major,
        public int $minor,
        public int $patch,
        public array $prerelease,
    ) {}

    public static function isValid(string $value): bool
    {
        return strlen($value) <= 64 && preg_match(self::PATTERN, $value) === 1;
    }

    public static function of(string $value): self
    {
        if (! self::isValid($value) || preg_match(self::PATTERN, $value, $matches) !== 1) {
            throw new InvalidArgumentException("Invalid semantic version [{$value}].");
        }

        $prerelease = isset($matches[4]) && $matches[4] !== '' ? explode('.', $matches[4]) : [];

        return new self((int) $matches[1], (int) $matches[2], (int) $matches[3], $prerelease);
    }

    /**
     * -1, 0 or 1 as this version is lower than, equal to or higher than the other.
     */
    public function compare(self $other): int
    {
        foreach (['major', 'minor', 'patch'] as $part) {
            if ($this->{$part} !== $other->{$part}) {
                return $this->{$part} <=> $other->{$part};
            }
        }

        if ($this->prerelease === [] && $other->prerelease === []) {
            return 0;
        }

        if ($this->prerelease === [] || $other->prerelease === []) {
            return $this->prerelease === [] ? 1 : -1;
        }

        foreach ($this->prerelease as $index => $identifier) {
            if (! array_key_exists($index, $other->prerelease)) {
                return 1;
            }

            $theirs = $other->prerelease[$index];
            $mineNumeric = ctype_digit($identifier);
            $theirsNumeric = ctype_digit($theirs);

            $result = match (true) {
                $mineNumeric && $theirsNumeric => (int) $identifier <=> (int) $theirs,
                $mineNumeric => -1,
                $theirsNumeric => 1,
                default => strcmp($identifier, $theirs) <=> 0,
            };

            if ($result !== 0) {
                return $result;
            }
        }

        return count($this->prerelease) < count($other->prerelease) ? -1 : 0;
    }

    public function lessThan(self $other): bool
    {
        return $this->compare($other) < 0;
    }

    public function __toString(): string
    {
        return "{$this->major}.{$this->minor}.{$this->patch}".($this->prerelease === [] ? '' : '-'.implode('.', $this->prerelease));
    }
}
