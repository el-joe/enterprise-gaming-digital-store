<?php

namespace App\Domains\Identity\Services;

use App\Domains\Identity\DTOs\PasswordResetData;
use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    /**
     * Send a reset link when the account exists. Callers respond identically either way.
     */
    public function sendResetLink(string $email): void
    {
        Password::broker()->sendResetLink(['email' => $email]);
    }

    /**
     * Reset a password. The stricter staff policy is applied only after the token is proven valid,
     * so the response cannot reveal which addresses belong to staff.
     */
    public function reset(PasswordResetData $data): void
    {
        $status = Password::broker()->reset(
            ['email' => $data->email, 'token' => $data->token, 'password' => $data->password],
            function (User $user, string $password): void {
                if ($user->type === UserType::Staff) {
                    Validator::make(['password' => $password], ['password' => [PasswordPolicy::forStaff()]])->validate();
                }

                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }
    }
}
