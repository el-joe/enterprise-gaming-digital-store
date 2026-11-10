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
        Schema::create('reconciliation_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('kind', 32);
            $table->string('subject_key', 64);
            $table->timestamp('window_start');
            $table->timestamp('window_end');
            $table->string('status', 32);
            $table->unsignedInteger('checked')->default(0);
            $table->unsignedInteger('mismatches')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->unique(['kind', 'subject_key', 'window_start', 'window_end'], 'uq_rr');
            $table->index(['kind', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliation_runs');
    }
};
