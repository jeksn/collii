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
        Schema::create('feed_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('url');
            $table->string('guid')->index();
            $table->string('author')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->string('image_url')->nullable();
            $table->string('video_url')->nullable(); // For YouTube videos
            $table->integer('duration')->nullable(); // Video duration in seconds
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['feed_id', 'guid']);
            $table->index(['feed_id', 'published_at']);
            $table->index(['feed_id', 'is_read']);
            $table->index(['feed_id', 'is_starred']);
            $table->index(['published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_items');
    }
};
