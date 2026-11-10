<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Fulfillment\Models\DigitalCode;
use Illuminate\Support\Facades\DB;

/**
 * Re-encrypts every code not on the active key, in chunks, each row under its own lock.
 */
class DigitalCodeKeyRotator
{
    public function __construct(private DigitalCodeVault $vault, private DigitalCodeKeyring $keys) {}

    public function rotate(int $chunk = 200): int
    {
        $active = $this->keys->activeKid();
        $rotated = 0;

        DigitalCode::query()->where('key_version', '!=', $active)->select('id')->chunkById($chunk, function ($rows) use (&$rotated): void {
            foreach ($rows as $row) {
                $rotated += DB::transaction(function () use ($row): int {
                    $code = DigitalCode::query()->lockForUpdate()->find($row->id);

                    return $code !== null && $this->vault->reencrypt($code) ? 1 : 0;
                });
            }
        });

        return $rotated;
    }
}
