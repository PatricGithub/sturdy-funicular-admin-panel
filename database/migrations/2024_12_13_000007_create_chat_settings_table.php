<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->string('icon_color')->default('#ffffff');
            $table->string('background_color')->default('#3b82f6');
            $table->string('chat_background_color')->default('#ffffff');
            $table->string('chat_text_color')->default('#000000');
            $table->string('button_color')->default('#3b82f6');
            $table->string('button_text_color')->default('#ffffff');
            $table->string('final_cta_phone')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_setting_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_setting_id')->constrained()->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('welcome_title')->nullable();
            $table->text('welcome_message')->nullable();
            $table->string('final_title')->nullable();
            $table->text('final_message')->nullable();
            $table->string('final_cta_text')->nullable();
            $table->timestamps();

            $table->unique(['chat_setting_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_setting_translations');
        Schema::dropIfExists('chat_settings');
    }
};
