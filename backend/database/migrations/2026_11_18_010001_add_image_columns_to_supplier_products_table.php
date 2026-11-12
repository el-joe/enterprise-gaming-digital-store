<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Owner decision 12: the supplier's product image, downloaded once per source URL and stored as
 * optimized media (AVIF and WebP). `image_url` is the last source URL tried, `image_hash` the
 * SHA-256 of its bytes, `image_error` why the last try was refused.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('supplier_products', function (Blueprint $table): void {
            $table->string('image_url', 500)->nullable()->after('raw');
            $table->char('image_hash', 64)->nullable()->after('image_url');
            $table->foreignId('image_media_id')->nullable()->after('image_hash')->constrained('media')->nullOnDelete();
            $table->timestamp('image_synced_at')->nullable()->after('image_media_id');
            $table->string('image_error', 190)->nullable()->after('image_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('image_media_id');
            $table->dropColumn(['image_url', 'image_hash', 'image_synced_at', 'image_error']);
        });
    }
};
