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
        Schema::create('shop_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('item_id');
            $table->integer('item_count')->default(1);
            $table->integer('points_cost')->default(0);
            $table->integer('tokens_cost')->default(0);
            $table->string('category')->default('items');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_service')->default(false);
            $table->string('service_type')->nullable();
            $table->json('service_data')->nullable();
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_items');
    }
};