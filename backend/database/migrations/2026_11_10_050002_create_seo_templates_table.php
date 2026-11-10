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
        Schema::create('seo_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('page_type', 32);
            $table->string('locale', 8);
            $table->string('title_template', 255);
            $table->string('description_template', 500);
            $table->timestamps();
            $table->unique(['page_type', 'locale'], 'uq_st');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_templates');
    }
};
