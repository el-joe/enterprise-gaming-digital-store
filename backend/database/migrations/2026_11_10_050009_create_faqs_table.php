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
        Schema::create('faqs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('faq_group_id')->constrained('faq_groups')->cascadeOnDelete();
            $table->string('question', 500);
            $table->text('answer');
            $table->boolean('show_on_homepage')->default(false);
            $table->boolean('is_active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index(['faq_group_id', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
