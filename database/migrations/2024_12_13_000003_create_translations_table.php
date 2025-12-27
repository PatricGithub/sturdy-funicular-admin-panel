<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('group');
            $table->string('key');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['group', 'key', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
