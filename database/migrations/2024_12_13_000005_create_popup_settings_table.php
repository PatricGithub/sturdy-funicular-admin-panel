<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popup_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->integer('scroll_percentage')->default(30);
            $table->string('image_path')->nullable();
            $table->string('background_color')->default('#ffffff');
            $table->string('text_color')->default('#000000');
            $table->string('button_color')->default('#3b82f6');
            $table->string('button_text_color')->default('#ffffff');
            $table->timestamps();
        });

        Schema::create('popup_setting_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('popup_setting_id')->constrained()->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('headline')->nullable();
            $table->text('subheadline')->nullable();
            $table->string('cta_text')->nullable();
            $table->string('cta_url')->nullable();
            $table->timestamps();

            $table->unique(['popup_setting_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_setting_translations');
        Schema::dropIfExists('popup_settings');
    }
};
