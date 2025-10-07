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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('site_name');
            $table->string('site_url');
            $table->integer('points_reward')->default(0);
            $table->integer('tokens_reward')->default(0);
            $table->timestamp('voted_at');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->boolean('is_processed')->default(false);
            $table->string('mmotop_vote_id')->nullable();
            $table->timestamps();
            
            $table->index(['account_id', 'voted_at']);
            $table->index(['site_name', 'voted_at']);
            $table->index('is_processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};