<?php

namespace App\Http\Controllers\Web;

use App\Domains\Identity\Services\AuthenticationService;
use App\Http\Controllers\Controller;
use App\Http\Support\LocalizedRoutes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Sign-in, registration, two-factor challenge, forgotten password and email verification pages
 * (M12). They replace the JSON landing pages: every form posts to the existing session routes
 * (`/login`, `/register`, `/two-factor-challenge`, `/forgot-password`,
 * `/email/verification-notification`) and shows their envelope errors beside the fields.
 */
class AuthPageController extends Controller
{
    public function login(Request $request): View|RedirectResponse
    {
        return $request->user() !== null ? $this->signedIn() : view('storefront.auth.login', ['intended' => $this->intended($request)]);
    }

    public function register(Request $request): View|RedirectResponse
    {
        return $request->user() !== null ? $this->signedIn() : view('storefront.auth.register', ['intended' => $this->intended($request)]);
    }

    /**
     * Only while a sign-in waits for its second factor; otherwise back to sign in.
     */
    public function twoFactor(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has(AuthenticationService::PENDING_KEY)) {
            return redirect()->to(LocalizedRoutes::url('storefront.login'));
        }

        return view('storefront.auth.two-factor', ['intended' => $this->intended($request)]);
    }

    public function forgotPassword(): View
    {
        return view('storefront.auth.forgot-password');
    }

    public function verifyEmail(Request $request): View|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail() ? $this->signedIn() : view('storefront.auth.verify-email');
    }

    private function signedIn(): RedirectResponse
    {
        return redirect()->to(LocalizedRoutes::url('storefront.account'));
    }

    /**
     * Where to go after signing in: the page that sent the shopper here (checkout), if it is on
     * this site; otherwise the account page.
     */
    private function intended(Request $request): string
    {
        $intended = $request->session()->get('url.intended');
        $root = $request->root();

        return is_string($intended) && ($intended === $root || str_starts_with($intended, $root.'/'))
            ? $intended
            : LocalizedRoutes::url('storefront.account');
    }
}
