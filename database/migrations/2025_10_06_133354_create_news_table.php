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
        Schema::create('server_news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('image_url')->nullable();
            $table->string('posted_by');
            $table->timestamp('post_date');
            $table->boolean('is_important')->default(false);
            $table->string('category')->default('general');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            
            $table->index(['is_published', 'post_date']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
