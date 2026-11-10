<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Account landing (`/account`). The account area (orders, codes, wallet, reviews, tickets,
 * notifications, security) is milestone M13; until then this page confirms the sign-in and
 * links to what exists.
 */
class AccountController extends Controller
{
    public function show(Request $request): View
    {
        return view('storefront.account', ['user' => $request->user()]);
    }
}
