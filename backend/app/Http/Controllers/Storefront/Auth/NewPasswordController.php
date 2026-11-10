<?php

namespace App\Http\Controllers\Storefront\Auth;

use App\Domains\Identity\Services\PasswordResetService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NewPasswordController extends Controller
{
    /**
     * The link in the reset email (M-A5, gap G10). With `auth.links.password_reset_url` set, a
     * redirect to that operator-configured page or app link with `{token}` and `{email}` filled
     * in. Otherwise a browser gets the reset page (M12) and a JSON client the reset parameters.
     */
    public function show(Request $request, string $token): JsonResponse|RedirectResponse|Response
    {
        $email = $request->query('email');
        $email = is_string($email) ? $email : null;
        $template = config('auth.links.password_reset_url');

        if (is_string($template) && $template !== '') {
            return redirect()->away(str_replace(['{token}', '{email}'], [rawurlencode($token), rawurlencode((string) $email)], $template))
                ->header('Cache-Control', 'no-store, private')
                ->header('Referrer-Policy', 'no-referrer');
        }

        if (! $request->expectsJson()) {
            return response()->view('storefront.auth.reset-password', ['token' => $token, 'email' => $email])
                ->header('Cache-Control', 'no-store, private')
                ->header('Referrer-Policy', 'no-referrer');
        }

        return $this->success(['token' => $token, 'email' => $email]);
    }

    public function store(ResetPasswordRequest $request, PasswordResetService $passwordReset): JsonResponse
    {
        $passwordReset->reset($request->toData());

        return $this->success(message: 'Your password has been reset.');
    }
}
