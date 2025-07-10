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
        Schema::create('feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('url');
            $table->string('feed_url');
            $table->text('description')->nullable();
            $table->string('site_url')->nullable();
            $table->string('language')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamp('last_fetched_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('fetch_interval')->default(3600); // seconds
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'feed_url']);
            $table->index(['user_id', 'is_active']);
            $table->index(['last_fetched_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
};
