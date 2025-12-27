<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentions', function (Blueprint $table) {
            $table->id();
            $table->morphs('mentionable'); // where mention occurred
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // mentioned user
            $table->foreignId('mentioned_by')->constrained('users')->cascadeOnDelete();
            $table->text('note_content');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentions');
    }
};
