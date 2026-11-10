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
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->ulid('public_id');
            $table->string('disk', 32);
            $table->string('path', 500);
            $table->string('original_filename', 255);
            $table->string('mime', 100);
            $table->unsignedInteger('size_bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt', 190)->nullable();
            $table->json('conversions')->nullable();
            $table->string('folder', 190)->nullable();
            $table->string('source', 32);
            $table->string('license_note', 255)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['public_id']);
            $table->index(['folder', 'created_at'], 'idx_m_folder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
