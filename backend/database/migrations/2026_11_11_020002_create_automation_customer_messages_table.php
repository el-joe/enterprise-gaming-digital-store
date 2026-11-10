<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * M17: secondary-channel messages sent by n8n customer workflows (D01 to D09), logged through
 * POST /api/v1/automation/customers/messages/log. The dedupe key stops the same reminder,
 * review request or event message from being logged, and offered again, twice. No message text
 * or contact details are stored.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_customer_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('workflow_id', 16);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel', 16);
            $table->string('category', 32);
            $table->string('dedupe_key', 128);
            $table->ulid('event_id')->nullable();
            $table->string('status', 16);
            $table->string('provider_message_id', 128)->nullable();
            $table->string('error', 500)->nullable();
            $table->timestamps();
            $table->unique(['dedupe_key'], 'uq_acm_dedupe');
            $table->index(['user_id', 'category', 'created_at'], 'idx_acm_user');
            $table->index(['workflow_id', 'created_at'], 'idx_acm_workflow');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_customer_messages');
    }
};
