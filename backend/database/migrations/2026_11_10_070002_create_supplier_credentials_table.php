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
        Schema::create('supplier_credentials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('mode', 32);
            $table->text('credentials');
            $table->text('webhook_secret')->nullable();
            $table->timestamp('rotated_at')->nullable();
            $table->timestamps();
            $table->unique(['supplier_id', 'mode'], 'uq_sc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_credentials');
    }
};
