<?php

namespace App\Http\Controllers\Storefront\Auth;

use App\Domains\Identity\Services\EmailVerificationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EmailVerificationController extends Controller
{
    /**
     * The link from the verification email (M-A5, gap G10): the signed URL alone verifies, with or
     * without a session. A JSON client gets the envelope (403 for a bad signature, a wrong hash or
     * an unknown user); a browser is redirected to `auth.links.email_verified_url` with the status.
     */
    public function verify(Request $request, string $id, string $hash, EmailVerificationService $verification): JsonResponse|RedirectResponse
    {
        $status = $request->hasValidSignature() ? $verification->verify($id, $hash) : EmailVerificationService::INVALID;

        if (! $request->expectsJson()) {
            return redirect()->away(self::landing($status));
        }

        if ($status === EmailVerificationService::INVALID) {
            throw new AccessDeniedHttpException;
        }

        return $this->success(['status' => $status], 'Email address verified.');
    }

    public function resend(Request $request): JsonResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }

        return $this->success(message: 'Verification link sent.');
    }

    private static function landing(string $status): string
    {
        $template = config('auth.links.email_verified_url');

        if (is_string($template) && $template !== '') {
            return str_replace('{status}', rawurlencode($status), $template);
        }

        return rtrim((string) config('app.url'), '/').'/?email_verification='.rawurlencode($status);
    }
}
