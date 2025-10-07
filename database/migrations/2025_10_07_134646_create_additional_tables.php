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
        // Аватары профилей
        Schema::create('profile_avatars', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            $table->integer('size');
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->index('filename');
            $table->index('active');
        });

        // Ограничения IP
        Schema::create('user_ip_restrictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('ip_address');
            $table->string('reason')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->index(['account_id', 'active']);
            $table->index('ip_address');
        });

        // Предстоящие события
        Schema::create('upcoming_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->datetime('event_date');
            $table->string('event_type');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['event_date', 'is_active']);
            $table->index('event_type');
        });

        // Шаблоны предметов
        Schema::create('site_items', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id');
            $table->string('name');
            $table->text('description');
            $table->string('icon');
            $table->integer('quality');
            $table->integer('level');
            $table->string('class');
            $table->string('subclass');
            $table->timestamps();
            
            $table->index('item_id');
            $table->index(['class', 'subclass']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_items');
        Schema::dropIfExists('upcoming_events');
        Schema::dropIfExists('user_ip_restrictions');
        Schema::dropIfExists('profile_avatars');
    }
};