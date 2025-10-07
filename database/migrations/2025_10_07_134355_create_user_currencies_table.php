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
        Schema::create('user_currencies', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->primary();
            $table->string('username');
            $table->string('email');
            $table->integer('points')->default(0);
            $table->integer('tokens')->default(0);
            $table->string('avatar')->nullable();
            $table->string('role')->default('player');
            $table->timestamp('last_password_change')->nullable();
            $table->timestamps();
            
            $table->index('username');
            $table->index('email');
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_currencies');
    }
};