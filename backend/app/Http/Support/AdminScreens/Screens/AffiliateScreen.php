<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Affiliates\Enums\AffiliateStatus;
use App\Domains\Affiliates\Models\Affiliate;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use App\Http\Support\AdminTablePage;
use Illuminate\Database\Eloquent\Model;

/**
 * Affiliates (spec Phase 9.8) with commission rate and write-only payout details; commissions and their approval are on the edit page.
 */
class AffiliateScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'marketing';
    }

    public static function slug(): string
    {
        return 'affiliates';
    }

    public static function viewPermission(): string
    {
        return 'marketing.affiliates';
    }

    public static function managePermission(): string
    {
        return 'marketing.affiliates';
    }

    public static function model(): string
    {
        return Affiliate::class;
    }

    public static function table(): string
    {
        return 'affiliates';
    }

    protected function apiPath(): string
    {
        return '/admin/api/affiliates';
    }

    public static function deletable(): bool
    {
        return false;
    }

    public function panel(): ?string
    {
        return 'admin.marketing.affiliate-panel';
    }

    public function panelData(Model $record): array
    {
        return [
            'payout_configured' => $record->getRawOriginal('payout_details') !== null,
            'commissions' => app(AdminTablePage::class)->make('affiliate-commissions', $this->viewer, ['affiliate' => (string) $record->getKey()]),
        ];
    }

    public function fields(?Model $record): array
    {
        $fields = [
            Field::text('code', $this->l('code'))->required()->max(32)->ltr(),
            Field::select('status', $this->l('status'), Options::enum('affiliate_status', AffiliateStatus::cases()))->required(),
            Field::decimal('commission_pct', $this->l('commission_pct'))->required()->help(__('admin.help.percent')),
            Field::textarea('notes', $this->l('notes'))->nullable()->max(2000),
        ];

        if ($record === null) {
            array_unshift($fields, Field::number('user_id', $this->l('customer_user_id'))->required()->help(__('admin.help.customer_user_id')));
        }

        return [
            'affiliate' => $fields,
        ];
    }
}
