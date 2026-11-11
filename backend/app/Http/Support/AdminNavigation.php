<?php

namespace App\Http\Support;

use App\DataTables\Orders\OrdersDataTable;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Services\AdminOrderReader;
use Illuminate\Support\Facades\Route;

/**
 * The admin sidebar and the payments tabs (M14, `config/admin.php`). An entry is listed only
 * when its route exists and the staff member holds one of its permissions; the route still
 * checks its own permission (SECURITY.md section 4: hidden navigation is a convenience).
 */
class AdminNavigation
{
    public function __construct(private AdminOrderReader $orders) {}

    /**
     * @return list<array{key: string, icon: string, url: string, route: string, badge: ?int}>
     */
    public function items(User $user): array
    {
        $items = [];

        foreach ((array) config('admin.navigation') as $item) {
            if ($item['route'] === null || ! Route::has($item['route']) || ! $this->allowed($user, $item['permissions'])) {
                continue;
            }

            $route = $item['key'] === 'payments' ? ($this->paymentTabs($user)[0]['route'] ?? null) : $item['route'];

            if ($route === null) {
                continue;
            }

            $items[] = [
                'key' => $item['key'],
                'icon' => $item['icon'],
                'url' => route($route),
                'route' => $route,
                'badge' => ($item['badge'] ?? null) === 'orders_attention' ? $this->orders->countInStatuses(OrdersDataTable::ATTENTION) : null,
            ];
        }

        return $items;
    }

    /**
     * @return list<array{key: string, route: string, url: string}>
     */
    public function paymentTabs(User $user): array
    {
        $tabs = [];

        foreach ((array) config('admin.payment_tabs') as $tab) {
            if (Route::has($tab['route']) && $this->allowed($user, $tab['permissions'])) {
                $tabs[] = ['key' => $tab['key'], 'route' => $tab['route'], 'url' => route($tab['route'])];
            }
        }

        return $tabs;
    }

    /**
     * @param  list<string>  $permissions
     */
    private function allowed(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }
}
