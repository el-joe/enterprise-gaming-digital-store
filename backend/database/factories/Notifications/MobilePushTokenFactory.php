<?php

namespace Database\Factories\Notifications;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Enums\PushPlatform;
use App\Domains\Notifications\Models\MobilePushToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MobilePushToken>
 */
class MobilePushTokenFactory extends Factory
{
    /**
     * Define the model's default state. The token is a made-up value shaped like an FCM token.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $token = 'fcm-test-'.Str::random(32).':APA91b'.Str::random(64);

        return [
            'user_id' => User::factory(),
            'family_id' => (string) Str::ulid(),
            'platform' => PushPlatform::Android,
            'token' => $token,
            'token_hash' => MobilePushToken::hashToken($token),
        ];
    }

    public function ios(): static
    {
        return $this->state(fn (): array => ['platform' => PushPlatform::Ios]);
    }
}
