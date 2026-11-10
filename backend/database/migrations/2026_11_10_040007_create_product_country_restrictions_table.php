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
        Schema::create('product_country_restrictions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->char('country_code', 2);
            $table->string('mode', 32);
            $table->timestamps();
            $table->unique(['product_id', 'country_code'], 'uq_pcr');
            $table->foreign('country_code')->references('code')->on('countries')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_country_restrictions');
    }
};
