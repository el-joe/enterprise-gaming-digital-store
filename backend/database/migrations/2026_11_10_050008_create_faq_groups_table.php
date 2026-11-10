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
        Schema::create('faq_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_groups');
    }
};
