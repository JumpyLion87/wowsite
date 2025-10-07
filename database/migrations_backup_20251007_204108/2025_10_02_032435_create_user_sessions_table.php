<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSessionsTable extends Migration
{
    public function up()
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('device_type'); // e.g., 'desktop', 'mobile'
            $table->string('ip_address');
            $table->timestamp('last_active')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_sessions');
    }
}