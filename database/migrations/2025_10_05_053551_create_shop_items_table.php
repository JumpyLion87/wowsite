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
            $table->unsignedInteger('item_id')->primary();
            $table->string('category', 50);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->unsignedInteger('point_cost')->default(0);
            $table->unsignedInteger('token_cost')->default(0);
            $table->unsignedInteger('stock')->nullable();
            $table->unsignedInteger('entry')->nullable();
            $table->integer('gold_amount')->default(0);
            $table->unsignedSmallInteger('level_boost')->nullable();
            $table->unsignedTinyInteger('at_login_flags')->default(0);
            $table->unsignedTinyInteger('is_item')->default(0);
            $table->timestamp('last_updated')->useCurrent()->useCurrentOnUpdate();
            
            $table->index('category');
            $table->index('entry');
            
            // $table->foreign('entry')->references('entry')->on('site_items')->onDelete('set null')->onUpdate('cascade');
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
