<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE crm_activities MODIFY COLUMN type ENUM('note', 'status_change', 'assignment', 'callback_created', 'task_created', 'email_sent', 'deal_stage_change', 'phone_call_initiated')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE crm_activities MODIFY COLUMN type ENUM('note', 'status_change', 'assignment', 'callback_created', 'task_created', 'email_sent', 'deal_stage_change')");
    }
};
