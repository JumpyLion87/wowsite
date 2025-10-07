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
        Schema::create('purchases', function (Blueprint $table) {
            $table->unsignedInteger('purchase_id')->primary();
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('point_cost')->default(0);
            $table->unsignedInteger('token_cost')->default(0);
            $table->timestamp('purchase_date')->useCurrent();
            
            $table->index('account_id');
            $table->index('item_id');
            
            // $table->foreign('account_id')->references('account_id')->on('user_currencies')->onDelete('cascade')->onUpdate('cascade');
            // $table->foreign('item_id')->references('item_id')->on('shop_items')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
