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
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id');
            $table->foreignId('gaming_platform_id')->constrained('gaming_platforms')->restrictOnDelete();
            $table->foreignId('primary_category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name', 190);
            $table->string('slug', 210);
            $table->string('sku', 64);
            $table->string('short_description', 500)->nullable();
            $table->mediumText('description')->nullable();
            $table->text('instructions')->nullable();
            $table->text('terms')->nullable();
            $table->string('delivery_type', 32);
            $table->unsignedInteger('delivery_time_minutes')->nullable();
            $table->unsignedTinyInteger('max_quantity_per_order')->default(10);
            $table->string('status', 32)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->decimal('rating_avg', 3, 2)->default('0');
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('sales_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['public_id']);
            $table->unique(['slug']);
            $table->unique(['sku']);
            $table->index(['status', 'gaming_platform_id', 'primary_category_id', 'sales_count'], 'idx_p_listing');
            $table->index(['status', 'is_featured', 'sales_count'], 'idx_p_featured');
            $table->index(['status', 'published_at'], 'idx_p_recent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
