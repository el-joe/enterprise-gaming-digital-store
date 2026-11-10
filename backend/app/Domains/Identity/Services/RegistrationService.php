<?php

namespace App\Domains\Identity\Services;

use App\Domains\Customers\Models\CustomerProfile;
use App\Domains\Identity\DTOs\RegistrationData;
use App\Domains\Identity\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    /**
     * Create a customer account and sign the new customer in (web session).
     */
    public function register(RegistrationData $data): User
    {
        $user = $this->createCustomer($data);

        Auth::guard('web')->login($user);

        return $user;
    }

    /**
     * Create a customer account with its profile and send the verification email, without
     * signing anyone in. The mobile app issues tokens instead (MobileAuthService).
     */
    public function createCustomer(RegistrationData $data): User
    {
        $user = DB::transaction(static function () use ($data): User {
            $user = new User([
                'name' => $data->name,
                'email' => $data->email,
                'password' => $data->password,
            ]);
            $user->save();

            $profile = new CustomerProfile;
            $profile->user_id = $user->id;
            $profile->save();

            return $user;
        });

        event(new Registered($user));

        return $user;
    }
}
