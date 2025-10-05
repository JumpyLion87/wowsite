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
        Schema::table('shop_items', function (Blueprint $table) {
            $table->tinyInteger('race_change')->nullable()->comment('Новая раса для смены');
            $table->string('name_change', 100)->nullable()->comment('Новое имя для смены');
            $table->tinyInteger('gender_change')->nullable()->comment('Новый пол для смены');
            $table->tinyInteger('faction_change')->nullable()->comment('Новая фракция для смены');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_items', function (Blueprint $table) {
            $table->dropColumn(['race_change', 'name_change', 'gender_change', 'faction_change']);
        });
    }
};
