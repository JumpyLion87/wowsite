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
        // Логи администраторов
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('action');
            $table->text('description');
            $table->json('data')->nullable();
            $table->string('ip_address');
            $table->timestamps();
            
            $table->index(['admin_id', 'created_at']);
            $table->index('action');
        });

        // Логи активности сайта
        Schema::create('website_activity_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('action');
            $table->text('description');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['account_id', 'created_at']);
            $table->index('action');
        });

        // Логи неудачных попыток входа
        Schema::create('failed_logins', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->timestamp('attempted_at');
            
            $table->index(['username', 'attempted_at']);
            $table->index('ip_address');
        });

        // Логи попыток входа
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->boolean('successful');
            $table->timestamps();
            
            $table->index(['account_id', 'created_at']);
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
        Schema::dropIfExists('failed_logins');
        Schema::dropIfExists('website_activity_log');
        Schema::dropIfExists('admin_logs');
    }
};