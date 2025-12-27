<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->morphs('activitable'); // Customer, Deal, ContactInquiry
            $table->enum('type', ['note', 'status_change', 'assignment', 'callback_created', 'task_created', 'email_sent', 'deal_stage_change']);
            $table->text('description')->nullable();
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            // morphs() already creates index on activitable_type, activitable_id
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
    }
};
