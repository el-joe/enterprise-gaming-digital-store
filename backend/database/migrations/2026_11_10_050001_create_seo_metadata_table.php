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
        Schema::create('seo_metadata', function (Blueprint $table): void {
            $table->id();
            $table->string('seoable_type', 64);
            $table->unsignedBigInteger('seoable_id');
            $table->string('locale', 8);
            $table->string('title', 190)->nullable();
            $table->string('description', 320)->nullable();
            $table->string('canonical_override', 500)->nullable();
            $table->string('robots', 64)->nullable();
            $table->foreignId('og_image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->json('schema_overrides')->nullable();
            $table->timestamps();
            $table->unique(['seoable_type', 'seoable_id', 'locale'], 'uq_sm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_metadata');
    }
};
