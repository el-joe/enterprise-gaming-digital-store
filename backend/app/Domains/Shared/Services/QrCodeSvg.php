<?php

namespace App\Domains\Shared\Services;

use InvalidArgumentException;

/**
 * A minimal QR Code (ISO/IEC 18004) encoder for short ASCII payloads such as the `otpauth://`
 * URI of two-factor enrolment (M13), rendered as an SVG with one path and no inline style, so it
 * works under the storefront Content Security Policy. Byte mode, error correction level M,
 * versions 1 to 20 (up to 666 bytes), automatic mask selection by the standard penalty rules.
 * Written in-house because the project adds no dependency without approval.
 */
class QrCodeSvg
{
    /**
     * Level M: [EC codewords per block, group 1 blocks, group 1 data codewords, group 2 blocks,
     * group 2 data codewords], by version.
     *
     * @var array<int, array{int, int, int, int, int}>
     */
    private const array BLOCKS_M = [
        1 => [10, 1, 16, 0, 0], 2 => [16, 1, 28, 0, 0], 3 => [26, 1, 44, 0, 0], 4 => [18, 2, 32, 0, 0],
        5 => [24, 2, 43, 0, 0], 6 => [16, 4, 27, 0, 0], 7 => [18, 4, 31, 0, 0], 8 => [22, 2, 38, 2, 39],
        9 => [22, 3, 36, 2, 37], 10 => [26, 4, 43, 1, 44], 11 => [30, 1, 50, 4, 51], 12 => [22, 6, 36, 2, 37],
        13 => [22, 8, 37, 1, 38], 14 => [24, 4, 40, 5, 41], 15 => [24, 5, 41, 5, 42], 16 => [28, 7, 45, 3, 46],
        17 => [28, 10, 46, 1, 47], 18 => [26, 9, 43, 4, 44], 19 => [26, 3, 44, 11, 45], 20 => [26, 3, 41, 13, 42],
    ];

    /** @var list<list<bool>> */
    private array $modules = [];

    /** @var list<list<bool>> */
    private array $isFunction = [];

    private int $size = 0;

    /**
     * The module matrix (true = dark), without the quiet zone. `$mask` forces a mask pattern
     * (0 to 7); null picks the one with the lowest penalty.
     *
     * @return list<list<bool>>
     */
    public function matrix(string $data, ?int $mask = null): array
    {
        $version = $this->version(strlen($data));
        $this->size = $version * 4 + 17;
        $this->modules = array_fill(0, $this->size, array_fill(0, $this->size, false));
        $this->isFunction = $this->modules;

        $this->drawFunctionPatterns($version);
        $this->drawCodewords($this->interleave($version, $this->dataCodewords($version, $data)));

        if ($mask === null) {
            $best = PHP_INT_MAX;

            for ($candidate = 0; $candidate < 8; $candidate++) {
                $this->applyMask($candidate);
                $this->drawFormatBits($candidate);
                $penalty = $this->penalty();

                if ($penalty < $best) {
                    $best = $penalty;
                    $mask = $candidate;
                }

                $this->applyMask($candidate);
            }
        }

        $this->applyMask((int) $mask);
        $this->drawFormatBits((int) $mask);

        return $this->modules;
    }

    /**
     * The QR code as a standalone SVG document, `currentColor` free: dark modules in `$dark` on
     * a `$light` background with a four-module quiet zone.
     */
    public function svg(string $data, string $dark = '#07080b', string $light = '#ffffff'): string
    {
        $matrix = $this->matrix($data);
        $border = 4;
        $dimension = $this->size + $border * 2;
        $path = '';

        foreach ($matrix as $y => $row) {
            foreach ($row as $x => $on) {
                if ($on) {
                    $path .= 'M'.($x + $border).','.($y + $border).'h1v1h-1z';
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$dimension.' '.$dimension.'" shape-rendering="crispEdges">'
            .'<rect width="100%" height="100%" fill="'.htmlspecialchars($light, ENT_QUOTES).'"/>'
            .'<path d="'.$path.'" fill="'.htmlspecialchars($dark, ENT_QUOTES).'"/></svg>';
    }

    private function version(int $length): int
    {
        foreach (self::BLOCKS_M as $version => $blocks) {
            $capacityBits = ($blocks[1] * $blocks[2] + $blocks[3] * $blocks[4]) * 8;

            if (4 + ($version < 10 ? 8 : 16) + $length * 8 <= $capacityBits) {
                return $version;
            }
        }

        throw new InvalidArgumentException('The data is too long for a QR code.');
    }

    /**
     * @return list<int>
     */
    private function dataCodewords(int $version, string $data): array
    {
        [, $g1, $d1, $g2, $d2] = self::BLOCKS_M[$version];
        $capacity = $g1 * $d1 + $g2 * $d2;
        $bits = [];
        $append = static function (int $value, int $length) use (&$bits): void {
            for ($i = $length - 1; $i >= 0; $i--) {
                $bits[] = ($value >> $i) & 1;
            }
        };

        $append(0b0100, 4);
        $append(strlen($data), $version < 10 ? 8 : 16);

        foreach (str_split($data) as $byte) {
            $append(ord($byte), 8);
        }

        $append(0, min(4, $capacity * 8 - count($bits)));
        $append(0, (8 - count($bits) % 8) % 8);

        $codewords = [];

        foreach (array_chunk($bits, 8) as $chunk) {
            $codewords[] = (int) bindec(implode('', $chunk));
        }

        for ($pad = 0xEC; count($codewords) < $capacity; $pad ^= 0xEC ^ 0x11) {
            $codewords[] = $pad;
        }

        return $codewords;
    }

    /**
     * Split into blocks, add Reed-Solomon error correction, and interleave.
     *
     * @param  list<int>  $data
     * @return list<int>
     */
    private function interleave(int $version, array $data): array
    {
        [$ecLength, $g1, $d1, $g2, $d2] = self::BLOCKS_M[$version];
        $divisor = self::rsDivisor($ecLength);
        $blocks = [];
        $offset = 0;

        foreach ([...array_fill(0, $g1, $d1), ...array_fill(0, $g2, $d2)] as $length) {
            $chunk = array_slice($data, $offset, $length);
            $offset += $length;
            $blocks[] = [$chunk, self::rsRemainder($chunk, $divisor)];
        }

        $result = [];

        for ($i = 0; $i < max($d1, $d2); $i++) {
            foreach ($blocks as [$chunk]) {
                if ($i < count($chunk)) {
                    $result[] = $chunk[$i];
                }
            }
        }

        for ($i = 0; $i < $ecLength; $i++) {
            foreach ($blocks as [, $ec]) {
                $result[] = $ec[$i];
            }
        }

        return $result;
    }

    /**
     * @return list<int>
     */
    private static function rsDivisor(int $degree): array
    {
        $result = array_fill(0, $degree, 0);
        $result[$degree - 1] = 1;
        $root = 1;

        for ($i = 0; $i < $degree; $i++) {
            for ($j = 0; $j < $degree; $j++) {
                $result[$j] = self::gfMultiply($result[$j], $root);

                if ($j + 1 < $degree) {
                    $result[$j] ^= $result[$j + 1];
                }
            }

            $root = self::gfMultiply($root, 0x02);
        }

        return $result;
    }

    /**
     * @param  list<int>  $data
     * @param  list<int>  $divisor
     * @return list<int>
     */
    private static function rsRemainder(array $data, array $divisor): array
    {
        $result = array_fill(0, count($divisor), 0);

        foreach ($data as $byte) {
            $factor = $byte ^ array_shift($result);
            $result[] = 0;

            foreach ($divisor as $i => $coefficient) {
                $result[$i] ^= self::gfMultiply($coefficient, $factor);
            }
        }

        return $result;
    }

    private static function gfMultiply(int $x, int $y): int
    {
        $z = 0;

        for ($i = 7; $i >= 0; $i--) {
            $z = ($z << 1) ^ (($z >> 7) * 0x11D);
            $z ^= (($y >> $i) & 1) * $x;
        }

        return $z & 0xFF;
    }

    private function drawFunctionPatterns(int $version): void
    {
        for ($i = 0; $i < $this->size; $i++) {
            $this->setFunction(6, $i, $i % 2 === 0);
            $this->setFunction($i, 6, $i % 2 === 0);
        }

        $this->drawFinder(3, 3);
        $this->drawFinder($this->size - 4, 3);
        $this->drawFinder(3, $this->size - 4);

        $positions = $this->alignmentPositions($version);
        $count = count($positions);

        foreach ($positions as $i => $x) {
            foreach ($positions as $j => $y) {
                if (! ($i === 0 && $j === 0 || $i === 0 && $j === $count - 1 || $i === $count - 1 && $j === 0)) {
                    $this->drawAlignment($x, $y);
                }
            }
        }

        $this->drawFormatBits(0);
        $this->drawVersion($version);
    }

    /**
     * @return list<int>
     */
    private function alignmentPositions(int $version): array
    {
        if ($version === 1) {
            return [];
        }

        $count = intdiv($version, 7) + 2;
        $step = intdiv($version * 8 + $count * 3 + 5, $count * 4 - 4) * 2;
        $result = [6];

        for ($position = $this->size - 7; count($result) < $count; $position -= $step) {
            array_splice($result, 1, 0, [$position]);
        }

        return $result;
    }

    private function drawFormatBits(int $mask): void
    {
        $data = (0b00 << 3) | $mask;
        $remainder = $data;

        for ($i = 0; $i < 10; $i++) {
            $remainder = ($remainder << 1) ^ (($remainder >> 9) * 0x537);
        }

        $bits = (($data << 10) | $remainder) ^ 0x5412;

        for ($i = 0; $i <= 5; $i++) {
            $this->setFunction(8, $i, self::bit($bits, $i));
        }

        $this->setFunction(8, 7, self::bit($bits, 6));
        $this->setFunction(8, 8, self::bit($bits, 7));
        $this->setFunction(7, 8, self::bit($bits, 8));

        for ($i = 9; $i < 15; $i++) {
            $this->setFunction(14 - $i, 8, self::bit($bits, $i));
        }

        for ($i = 0; $i < 8; $i++) {
            $this->setFunction($this->size - 1 - $i, 8, self::bit($bits, $i));
        }

        for ($i = 8; $i < 15; $i++) {
            $this->setFunction(8, $this->size - 15 + $i, self::bit($bits, $i));
        }

        $this->setFunction(8, $this->size - 8, true);
    }

    private function drawVersion(int $version): void
    {
        if ($version < 7) {
            return;
        }

        $remainder = $version;

        for ($i = 0; $i < 12; $i++) {
            $remainder = ($remainder << 1) ^ (($remainder >> 11) * 0x1F25);
        }

        $bits = ($version << 12) | $remainder;

        for ($i = 0; $i < 18; $i++) {
            $on = self::bit($bits, $i);
            $a = $this->size - 11 + $i % 3;
            $b = intdiv($i, 3);
            $this->setFunction($a, $b, $on);
            $this->setFunction($b, $a, $on);
        }
    }

    private function drawFinder(int $x, int $y): void
    {
        for ($dy = -4; $dy <= 4; $dy++) {
            for ($dx = -4; $dx <= 4; $dx++) {
                $distance = max(abs($dx), abs($dy));
                $xx = $x + $dx;
                $yy = $y + $dy;

                if ($xx >= 0 && $xx < $this->size && $yy >= 0 && $yy < $this->size) {
                    $this->setFunction($xx, $yy, $distance !== 2 && $distance !== 4);
                }
            }
        }
    }

    private function drawAlignment(int $x, int $y): void
    {
        for ($dy = -2; $dy <= 2; $dy++) {
            for ($dx = -2; $dx <= 2; $dx++) {
                $this->setFunction($x + $dx, $y + $dy, max(abs($dx), abs($dy)) !== 1);
            }
        }
    }

    private function setFunction(int $x, int $y, bool $on): void
    {
        $this->modules[$y][$x] = $on;
        $this->isFunction[$y][$x] = true;
    }

    /**
     * @param  list<int>  $codewords
     */
    private function drawCodewords(array $codewords): void
    {
        $bitIndex = 0;
        $total = count($codewords) * 8;

        for ($right = $this->size - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right = 5;
            }

            for ($vertical = 0; $vertical < $this->size; $vertical++) {
                for ($j = 0; $j < 2; $j++) {
                    $x = $right - $j;
                    $upward = (($right + 1) & 2) === 0;
                    $y = $upward ? $this->size - 1 - $vertical : $vertical;

                    if (! $this->isFunction[$y][$x] && $bitIndex < $total) {
                        $this->modules[$y][$x] = self::bit($codewords[$bitIndex >> 3], 7 - ($bitIndex & 7));
                        $bitIndex++;
                    }
                }
            }
        }
    }

    private function applyMask(int $mask): void
    {
        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                $invert = match ($mask) {
                    0 => ($x + $y) % 2 === 0,
                    1 => $y % 2 === 0,
                    2 => $x % 3 === 0,
                    3 => ($x + $y) % 3 === 0,
                    4 => (intdiv($x, 3) + intdiv($y, 2)) % 2 === 0,
                    5 => $x * $y % 2 + $x * $y % 3 === 0,
                    6 => ($x * $y % 2 + $x * $y % 3) % 2 === 0,
                    default => (($x + $y) % 2 + $x * $y % 3) % 2 === 0,
                };

                if ($invert && ! $this->isFunction[$y][$x]) {
                    $this->modules[$y][$x] = ! $this->modules[$y][$x];
                }
            }
        }
    }

    /**
     * Penalty rules N1 to N4 (runs, 2x2 blocks, finder-like patterns, dark balance).
     */
    private function penalty(): int
    {
        $penalty = 0;
        $lines = [];

        for ($i = 0; $i < $this->size; $i++) {
            $lines[] = $this->modules[$i];
            $lines[] = array_column($this->modules, $i);
        }

        foreach ($lines as $line) {
            $run = 1;

            for ($i = 1; $i <= $this->size; $i++) {
                if ($i < $this->size && $line[$i] === $line[$i - 1]) {
                    $run++;

                    continue;
                }

                if ($run >= 5) {
                    $penalty += 3 + $run - 5;
                }

                $run = 1;
            }

            $text = implode('', array_map(static fn (bool $on): string => $on ? '1' : '0', $line));
            $penalty += 40 * (substr_count($text, '10111010000') + substr_count($text, '00001011101'));
        }

        $dark = 0;

        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                $dark += $this->modules[$y][$x] ? 1 : 0;

                if ($x < $this->size - 1 && $y < $this->size - 1) {
                    $on = $this->modules[$y][$x];

                    if ($on === $this->modules[$y][$x + 1] && $on === $this->modules[$y + 1][$x] && $on === $this->modules[$y + 1][$x + 1]) {
                        $penalty += 3;
                    }
                }
            }
        }

        $total = $this->size * $this->size;
        $penalty += 10 * intdiv(abs($dark * 20 - $total * 10) + $total - 1, $total) - 10;

        return $penalty;
    }

    private static function bit(int $value, int $index): bool
    {
        return (($value >> $index) & 1) !== 0;
    }
}
