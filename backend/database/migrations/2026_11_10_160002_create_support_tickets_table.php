<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id');
            $table->string('ticket_number', 24);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_email', 190)->nullable();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('category', 32);
            $table->string('subject', 190);
            $table->string('status', 32);
            $table->string('priority', 32);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_activity_at');
            $table->timestamps();
            $table->unique(['public_id']);
            $table->unique(['ticket_number']);
            $table->index(['status', 'priority', 'last_activity_at'], 'idx_st_queue');
            $table->index(['user_id', 'created_at'], 'idx_st_user');
            $table->index(['order_id'], 'idx_st_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
