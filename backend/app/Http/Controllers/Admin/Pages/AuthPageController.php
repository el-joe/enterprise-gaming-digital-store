<?php

namespace App\Http\Controllers\Admin\Pages;

use App\Domains\Identity\Services\AuthenticationService;
use App\Domains\Identity\Services\TwoFactorService;
use App\Domains\Shared\Services\QrCodeSvg;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Admin sign-in pages (M14). The forms post to the existing session routes (`/login`,
 * `/two-factor-challenge`, `/api/v1/account/security/two-factor[/confirm]`); staff must finish
 * two-step verification before any admin page or API answers (SECURITY.md section 1).
 */
class AuthPageController extends Controller
{
    public function login(Request $request): View|RedirectResponse
    {
        if ($this->verifiedStaff($request)) {
            return redirect()->route('admin.home');
        }

        return view('admin.auth.login');
    }

    /**
     * Only while a sign-in waits for its second factor.
     */
    public function twoFactor(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has(AuthenticationService::PENDING_KEY)) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.two-factor');
    }

    /**
     * First sign-in of a staff member without two-step verification: set it up here. A member
     * who has it but whose session never passed the challenge signs in again.
     */
    public function setup(Request $request, AuthenticationService $authentication): Response|RedirectResponse
    {
        if ($request->user()->hasTwoFactorEnabled()) {
            if ($request->session()->has(TwoFactorService::SESSION_PASSED_KEY)) {
                return redirect()->route('admin.home');
            }

            $authentication->logout($request->session());

            return redirect()->route('admin.login');
        }

        return response()->view('admin.auth.setup')->header('Cache-Control', 'no-store, private');
    }

    public function setupQr(Request $request, TwoFactorService $twoFactor, QrCodeSvg $qr): Response
    {
        $uri = $twoFactor->pendingOtpauthUri($request->user());

        abort_if($uri === null, 404);

        return response($qr->svg($uri), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function verifiedStaff(Request $request): bool
    {
        $user = $request->user();

        return $user !== null && $user->isStaff() && $user->hasTwoFactorEnabled() && $request->session()->has(TwoFactorService::SESSION_PASSED_KEY);
    }
}
