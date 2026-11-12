<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Supplier images for products, platforms (brand logos) and categories: one candidate per subject,
 * supplier and kind. The subject's own image field stays the active image (product primary image,
 * `gaming_platforms.logo_media_id`, `categories.banner_media_id`); `image_source` on the subject
 * says who set it: `staff` (never replaced automatically) or `supplier:{id}`. Existing images are
 * marked `staff`, except product images that are synced supplier media.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplier_image_candidates', function (Blueprint $table): void {
            $table->id();
            $table->string('subject_type', 40);
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('kind', 30);
            $table->string('source_url', 500)->nullable();
            $table->char('image_hash', 64)->nullable();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->timestamp('fetched_at')->nullable();
            $table->string('error', 190)->nullable();
            $table->timestamps();

            $table->unique(['subject_type', 'subject_id', 'supplier_id', 'kind'], 'uq_sic_subject_supplier_kind');
            $table->index(['subject_type', 'subject_id'], 'ix_sic_subject');
        });

        foreach (['products', 'gaming_platforms', 'categories'] as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->string('image_source', 40)->nullable();
            });
        }

        DB::table('gaming_platforms')->whereNotNull('logo_media_id')->update(['image_source' => 'staff']);
        DB::table('categories')->whereNotNull('banner_media_id')->update(['image_source' => 'staff']);

        DB::table('products')->whereExists(static fn (Builder $query) => $query->select(DB::raw(1))->from('product_images')->whereColumn('product_images.product_id', 'products.id'))
            ->select('id')->chunkById(500, static function ($products): void {
                foreach ($products as $product) {
                    $primary = DB::table('product_images')->join('media', 'media.id', '=', 'product_images.media_id')
                        ->where('product_images.product_id', $product->id)->orderBy('product_images.sort_order')->orderBy('product_images.id')
                        ->first(['media.id', 'media.source']);
                    $supplier = $primary?->source === 'supplier'
                        ? DB::table('supplier_products')->where('image_media_id', $primary->id)->value('supplier_id')
                        : null;

                    DB::table('products')->where('id', $product->id)->update(['image_source' => $supplier !== null ? 'supplier:'.$supplier : 'staff']);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['products', 'gaming_platforms', 'categories'] as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropColumn('image_source');
            });
        }

        Schema::dropIfExists('supplier_image_candidates');
    }
};
