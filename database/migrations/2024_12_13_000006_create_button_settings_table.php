<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('button_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->boolean('is_active')->default(false);
            $table->string('value')->nullable();
            $table->string('icon_color')->default('#ffffff');
            $table->string('background_color')->default('#3b82f6');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('button_settings');
    }
};
