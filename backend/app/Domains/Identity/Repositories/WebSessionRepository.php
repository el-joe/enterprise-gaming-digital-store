<?php

namespace App\Domains\Identity\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The customer's browser sessions, readable only with the `database` session driver (production
 * default, `SESSION_DRIVER=database`). With any other driver sessions cannot be listed per user,
 * so every method returns nothing (M-A5). A session id is a bearer secret: it never leaves this
 * class except as an opaque HMAC (`publicId`).
 */
class WebSessionRepository
{
    public function available(): bool
    {
        return config('session.driver') === 'database';
    }

    /**
     * Live sessions of the user (within `session.lifetime`), newest activity first.
     *
     * @return Collection<int, object{id: string, ip_address: string|null, user_agent: string|null, last_activity: int}>
     */
    public function forUser(int $userId): Collection
    {
        if (! $this->available()) {
            return collect();
        }

        return DB::table($this->table())
            ->where('user_id', $userId)
            ->where('last_activity', '>=', now()->subMinutes((int) config('session.lifetime'))->getTimestamp())
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity']);
    }

    /**
     * Delete the user's session with this public id; false when none matches.
     */
    public function deleteByPublicId(int $userId, string $publicId): bool
    {
        $session = $this->forUser($userId)->first(fn (object $row): bool => hash_equals(self::publicId($row->id), $publicId));

        return $session !== null && DB::table($this->table())->where('id', $session->id)->where('user_id', $userId)->delete() > 0;
    }

    /**
     * Delete every session of the user except one (the caller's); returns how many.
     */
    public function deleteAllExcept(int $userId, ?string $keptSessionId): int
    {
        if (! $this->available()) {
            return 0;
        }

        return DB::table($this->table())
            ->where('user_id', $userId)
            ->when($keptSessionId !== null, static fn ($sessions) => $sessions->where('id', '!=', $keptSessionId))
            ->delete();
    }

    /**
     * The opaque id a session is shown and revoked by.
     */
    public static function publicId(string $sessionId): string
    {
        return 'web_'.substr(hash_hmac('sha256', 'session:'.$sessionId, (string) config('app.key')), 0, 32);
    }

    private function table(): string
    {
        return (string) config('session.table', 'sessions');
    }
}
