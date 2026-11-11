<?php

namespace App\View\Composers;

use App\Domains\Identity\Models\User;
use App\Http\Support\AdminNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Data of the admin layout (M14): navigation for the signed-in staff member, the current
 * section, the user menu, the language and direction.
 */
class AdminLayoutComposer
{
    public function __construct(private Request $request, private AdminNavigation $navigation) {}

    public function compose(View $view): void
    {
        $view->with('admin', $this->request->attributes->get('admin.layout') ?? $this->build());
    }

    /**
     * Built once per request: the layout, the page and its partials all read it.
     *
     * @return array<string, mixed>
     */
    private function build(): array
    {
        $user = $this->request->user();
        $locale = app()->getLocale();
        $routeName = (string) $this->request->route()?->getName();

        $data = [
            'locale' => $locale,
            'dir' => $locale === 'ar' ? 'rtl' : 'ltr',
            'section' => explode('.', Str::after($routeName, 'admin.'))[0],
            'user' => $user instanceof User ? ['name' => $user->name, 'email' => $user->email, 'initials' => $this->initials((string) $user->name), 'roles' => $user->getRoleNames()->all()] : null,
            'navigation' => $user instanceof User && $user->isStaff() ? $this->navigation->items($user) : [],
            'payment_tabs' => $user instanceof User && $user->isStaff() ? $this->navigation->paymentTabs($user) : [],
        ];

        $this->request->attributes->set('admin.layout', $data);

        return $data;
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];

        return mb_strtoupper(implode('', array_map(static fn (string $part): string => mb_substr($part, 0, 1), array_slice($parts, 0, 2))));
    }
}
