<?php

namespace App\Console\Commands\Automation;

use App\Domains\Automation\Services\AutomationOutboxDispatcher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('automation:flush-outbox {--limit= : Events to send in this run}')]
#[Description('Deliver pending automation outbox events to n8n with HMAC signatures and retry')]
class FlushOutboxCommand extends Command
{
    public function handle(AutomationOutboxDispatcher $dispatcher): int
    {
        $result = $dispatcher->flush($this->option('limit') === null ? null : max(1, (int) $this->option('limit')));

        if (! $result['configured']) {
            $this->line('Outbox delivery is not configured (N8N_WEBHOOK_URL or N8N_OUTBOUND_SECRET is empty); events stay pending.');

            return self::SUCCESS;
        }

        $this->line("Leased {$result['leased']}: {$result['delivered']} delivered, {$result['retrying']} to retry, {$result['failed']} failed.");

        return self::SUCCESS;
    }
}
