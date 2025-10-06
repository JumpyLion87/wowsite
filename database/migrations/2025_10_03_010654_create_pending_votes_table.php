<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pending_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('token')->unique();
            $table->timestamp('created_at');
            $table->timestamp('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pending_votes');
    }
};
