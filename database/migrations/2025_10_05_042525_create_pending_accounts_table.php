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
        Schema::connection('mysql')->create('pending_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('username', 32);
            $table->string('email', 255);
            $table->binary('salt', 32);
            $table->binary('verifier', 32);
            $table->string('token', 64)->unique();
            $table->boolean('activated')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            
            $table->index(['username', 'email']);
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_accounts');
    }
};
