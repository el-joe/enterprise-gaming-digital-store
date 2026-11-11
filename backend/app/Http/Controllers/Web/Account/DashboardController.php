<?php

namespace App\Http\Controllers\Web\Account;

use App\Domains\Catalog\DTOs\ProductListingFilter;
use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Domains\Orders\Repositories\CustomerOrderRepository;
use App\Domains\Wallet\Services\WalletService;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Account\Concerns\PresentsAccountData;
use App\Http\Resources\Account\WalletResource;
use App\Http\Support\StorefrontRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Account dashboard (`/account`, M13, Figma "Account Dashboard"): order, wallet and sealed-code
 * figures, a notice for the newest sealed code, recent orders and featured products.
 */
class DashboardController extends Controller
{
    use PresentsAccountData;

    private const int RECENT_ORDERS = 4;

    private const int PICKED = 3;

    public function show(
        Request $request,
        CustomerOrderRepository $orders,
        WalletService $wallets,
        StorefrontCatalogService $catalog,
        ContentTranslator $translator,
        StorefrontRequest $storefront,
    ): View {
        $user = $request->user();
        $picked = $catalog->topProducts(new ProductListingFilter(flag: 'featured'), self::PICKED);

        if ($picked->isEmpty()) {
            $picked = $catalog->topProducts(new ProductListingFilter, self::PICKED);
        }

        $sealed = $orders->latestSealedUnit($user->id);

        return view('account.dashboard', [
            'user' => $user,
            'counts' => $orders->dashboardCounts($user->id),
            'wallet' => (new WalletResource($wallets->summary($user)))->resolve($request),
            'sealed' => $sealed === null ? null : [
                'name' => $sealed->orderItem?->name_snapshot,
                'order_public_id' => $sealed->order?->public_id,
                'order_number' => $sealed->order?->order_number,
            ],
            'orders' => $this->orderRows($orders->recentForCustomer($user->id, self::RECENT_ORDERS), $request),
            'picked' => $this->productCards($catalog->cards($picked, $storefront->currency($request)), $translator, $request),
        ]);
    }
}
