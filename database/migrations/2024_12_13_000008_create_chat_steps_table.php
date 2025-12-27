<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_setting_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('question');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('chat_step_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_step_id')->constrained()->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->text('question')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();

            $table->unique(['chat_step_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_step_translations');
        Schema::dropIfExists('chat_steps');
    }
};
