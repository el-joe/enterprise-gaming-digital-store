<?php

namespace App\Http\Support;

use App\DataTables\Orders\OrdersDataTable;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Services\AdminOrderReader;
use Illuminate\Support\Facades\Route;

/**
 * The admin sidebar and the section tabs (M14, M15, `config/admin.php`). An entry is listed only
 * when its route exists and the staff member holds one of its permissions; a section entry
 * opens its first permitted tab. The route still checks its own permission (SECURITY.md
 * section 4: hidden navigation is a convenience).
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
            if (isset($item['tabs'])) {
                $route = $this->tabs($user, $item['tabs'])[0]['route'] ?? null;
            } else {
                $route = $item['route'] !== null && Route::has($item['route']) && $this->allowed($user, $item['permissions']) ? $item['route'] : null;
            }

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
     * The tabs of a section the staff member may open, in order.
     *
     * @return list<array{key: string, route: string, url: string}>
     */
    public function tabs(User $user, string $section): array
    {
        $tabs = [];

        foreach ((array) config("admin.sections.{$section}", []) as $tab) {
            if (Route::has($tab['route']) && $this->allowed($user, $tab['permissions'])) {
                $tabs[] = ['key' => $tab['key'], 'route' => $tab['route'], 'url' => route($tab['route'])];
            }
        }

        return $tabs;
    }

    /**
     * @return list<array{key: string, route: string, url: string}>
     */
    public function paymentTabs(User $user): array
    {
        return $this->tabs($user, 'payments');
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
