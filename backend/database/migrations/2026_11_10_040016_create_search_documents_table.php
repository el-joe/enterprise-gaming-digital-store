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
        Schema::create('search_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('searchable_type', 64);
            $table->unsignedBigInteger('searchable_id');
            $table->string('locale', 8);
            $table->string('title', 255);
            $table->text('keywords');
            $table->integer('boost')->default(0);
            $table->boolean('is_active');
            $table->timestamps();
            $table->unique(['searchable_type', 'searchable_id', 'locale'], 'uq_sd');
            $table->index(['locale', 'is_active', 'title'], 'idx_sd_title');
            $table->fullText(['title', 'keywords'], 'ft_sd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_documents');
    }
};
