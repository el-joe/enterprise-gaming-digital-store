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
        Schema::create('delivery_field_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 64);
            $table->string('label', 120);
            $table->string('input_type', 32);
            $table->string('validation_regex', 255)->nullable();
            $table->json('options')->nullable();
            $table->string('help_text', 255)->nullable();
            $table->timestamps();
            $table->unique(['code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_field_definitions');
    }
};
