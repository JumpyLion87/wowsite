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
        Schema::create('news_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('news_id');
            $table->unsignedBigInteger('user_id');
            $table->text('content');
            $table->boolean('is_approved')->default(false);
            $table->unsignedBigInteger('parent_id')->nullable(); // Для ответов на комментарии
            $table->timestamps();
            
            // Пока без внешних ключей из-за проблем с типами данных
            // $table->foreign('news_id')->references('id')->on('server_news')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('parent_id')->references('id')->on('news_comments')->onDelete('cascade');
            
            $table->index(['news_id', 'is_approved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_comments');
    }
};
