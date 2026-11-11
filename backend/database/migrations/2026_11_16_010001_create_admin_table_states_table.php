<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * M14: the column visibility and page length each staff member chose for an admin table. Saved
 * filters live in `saved_filters`; this row is the table's current layout.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_table_states', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('table_key', 64);
            $table->json('hidden_columns')->nullable();
            $table->unsignedSmallInteger('page_length')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'table_key'], 'uq_ats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_table_states');
    }
};
