<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Storefront v2 (docs/STOREFRONT_IA.md): where a category shows outside its own page. The
 * header row lists up to 8 categories with `featured_in_header`, by `header_order`; the home page
 * adds one product rail per category with `show_on_home`, by `home_position`, of `home_limit`
 * products (4 to 24, default 12), after the managed homepage sections.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->boolean('featured_in_header')->default(false)->after('sort_order');
            $table->unsignedSmallInteger('header_order')->default(0)->after('featured_in_header');
            $table->boolean('show_on_home')->default(false)->after('header_order');
            $table->unsignedSmallInteger('home_position')->default(0)->after('show_on_home');
            $table->unsignedTinyInteger('home_limit')->default(12)->after('home_position');
            $table->index(['featured_in_header', 'header_order'], 'idx_cat_header');
            $table->index(['show_on_home', 'home_position'], 'idx_cat_home');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex('idx_cat_header');
            $table->dropIndex('idx_cat_home');
            $table->dropColumn(['featured_in_header', 'header_order', 'show_on_home', 'home_position', 'home_limit']);
        });
    }
};
