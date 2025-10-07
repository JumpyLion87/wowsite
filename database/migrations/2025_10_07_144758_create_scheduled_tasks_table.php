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
        Schema::connection('mysql')->create('scheduled_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('task');
            $table->json('data')->nullable();
            $table->timestamp('scheduled_at');
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('error')->nullable();

            $table->index(['status', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('scheduled_tasks');
    }
};
