<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Owner decision 1 (OD-1): each gateway charges one fixed currency (`default_currency`), so the
 * Paymob-only `settings.charge_display_currency_only` restriction no longer applies. Data only:
 * the key is removed from every gateway that has it; nothing else in `settings` changes.
 */
return new class extends Migration
{
    private const string KEY = 'charge_display_currency_only';

    public function up(): void
    {
        DB::table('payment_gateways')->whereNotNull('settings')->orderBy('id')->each(function (object $row): void {
            $settings = json_decode((string) $row->settings, true);

            if (! is_array($settings) || ! array_key_exists(self::KEY, $settings)) {
                return;
            }

            unset($settings[self::KEY]);

            DB::table('payment_gateways')->where('id', $row->id)->update([
                'settings' => $settings === [] ? null : json_encode($settings, JSON_THROW_ON_ERROR),
            ]);
        });
    }

    /**
     * Restores the former restriction on Paymob only, as the M10 seeder set it.
     */
    public function down(): void
    {
        DB::table('payment_gateways')->where('key', 'paymob')->orderBy('id')->each(function (object $row): void {
            $settings = json_decode((string) ($row->settings ?? 'null'), true);
            $settings = is_array($settings) ? $settings : [];
            $settings[self::KEY] = true;

            DB::table('payment_gateways')->where('id', $row->id)->update(['settings' => json_encode($settings, JSON_THROW_ON_ERROR)]);
        });
    }
};
