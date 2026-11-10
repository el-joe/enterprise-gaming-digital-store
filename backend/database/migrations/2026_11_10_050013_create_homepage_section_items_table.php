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
        Schema::create('homepage_section_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('homepage_section_id')->constrained('homepage_sections')->cascadeOnDelete();
            $table->string('item_type', 64);
            $table->unsignedBigInteger('item_id');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['homepage_section_id', 'item_type', 'item_id'], 'uq_hsi_homepa_item_t_item_i');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_section_items');
    }
};
