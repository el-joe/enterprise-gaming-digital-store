<?php

/**
 * The in-house QR encoder of the two-factor enrolment page (M13). The matrices were checked
 * module for module against bacon/bacon-qr-code 3.1 for 61 payloads (versions 1 to 17) during
 * M13; these hashes pin that output.
 */

use App\Domains\Shared\Services\QrCodeSvg;

function qrBits(array $matrix): string
{
    return implode('', array_map(static fn (array $row): string => implode('', array_map(static fn (bool $on): string => $on ? '1' : '0', $row)), $matrix));
}

it('encodes byte-mode level M symbols identical to the reference encoder', function (string $data, int $mask, int $size, string $hash): void {
    $matrix = (new QrCodeSvg)->matrix($data, $mask);

    expect(count($matrix))->toBe($size)
        ->and(sha1(qrBits($matrix)))->toBe($hash);
})->with([
    ['hello world', 0, 21, 'aa8388d2616c1c25b52c64a7ad2ea45b7cc248f5'],
    ['hello world', 5, 21, '9eb130e2f3a80d04de928d49d065307698977f73'],
    ['otpauth://totp/boxcards.shop:sara%40example.com?secret=JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP&issuer=boxcards.shop', 0, 45, 'cba3aca82816d0cf569d905a1132fd38fd60a485'],
    ['otpauth://totp/boxcards.shop:sara%40example.com?secret=JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP&issuer=boxcards.shop', 5, 45, 'd389c67962706a1408fb860600478fb78560d554'],
]);

it('draws the three finder patterns and renders an SVG with a quiet zone and no inline style', function (): void {
    $qr = new QrCodeSvg;
    $matrix = $qr->matrix('otpauth://totp/x');
    $size = count($matrix);

    foreach ([[0, 0], [$size - 7, 0], [0, $size - 7]] as [$x, $y]) {
        expect($matrix[$y][$x])->toBeTrue()->and($matrix[$y + 3][$x + 3])->toBeTrue()->and($matrix[$y + 1][$x + 1])->toBeFalse();
    }

    $svg = $qr->svg('otpauth://totp/x');
    expect($svg)->toStartWith('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.($size + 8).' '.($size + 8).'"')
        ->not->toContain('style=');
});

it('refuses data beyond version 20', function (): void {
    (new QrCodeSvg)->matrix(str_repeat('a', 700));
})->throws(InvalidArgumentException::class);
