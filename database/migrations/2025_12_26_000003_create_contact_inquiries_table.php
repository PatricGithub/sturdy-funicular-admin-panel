<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('home_form'); // home_form, chat_widget, consultation, lead_magnet
            $table->string('move_type')->nullable();
            $table->string('move_size')->nullable();
            $table->string('move_date')->nullable();
            $table->string('address_from')->nullable();
            $table->string('address_to')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('preferred_contact')->default('email');
            $table->text('message')->nullable();
            $table->string('status')->default('new'); // new, contacted, qualified, converted, lost (full) OR new, in_progress, done (light)
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('source');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_inquiries');
    }
};
