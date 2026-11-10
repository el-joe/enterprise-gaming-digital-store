<?php

namespace App\Domains\Automation\Enums;

/**
 * Scheduled tasks that n8n may drive instead of the Laravel scheduler (ADR-014). The driver of
 * each is `settings.automation.task_drivers.{task}`: `scheduler` (default) or `n8n`.
 */
enum AutomationTask: string
{
    case CatalogSync = 'catalog_sync';
    case ExchangeRates = 'exchange_rates';
    case PaymentReconciliation = 'payment_reconciliation';

    /**
     * The artisan command that does the work, whoever drives it.
     */
    public function command(): string
    {
        return match ($this) {
            self::CatalogSync => 'suppliers:sync-catalog',
            self::ExchangeRates => 'currency:fetch-rates',
            self::PaymentReconciliation => 'payments:reconcile-daily',
        };
    }

    /**
     * The n8n workflow that drives the task when the driver is `n8n`.
     */
    public function workflowId(): string
    {
        return match ($this) {
            self::CatalogSync => 'A09',
            self::ExchangeRates => 'A10',
            self::PaymentReconciliation => 'E02',
        };
    }
}
