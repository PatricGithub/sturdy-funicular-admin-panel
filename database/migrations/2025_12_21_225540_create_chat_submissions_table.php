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
        Schema::create('chat_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('move_type');
            $table->string('move_size');
            $table->string('move_date')->nullable();
            $table->string('address_from')->nullable();
            $table->string('address_to')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('preferred_contact')->default('email');
            $table->string('source')->default('chat');
            $table->string('status')->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_submissions');
    }
};
