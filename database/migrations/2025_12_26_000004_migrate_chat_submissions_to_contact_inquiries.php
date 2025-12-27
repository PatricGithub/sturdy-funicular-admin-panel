<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only run if chat_submissions table exists and has data
        if (!Schema::hasTable('chat_submissions')) {
            return;
        }

        $chatSubmissions = DB::table('chat_submissions')->get();

        foreach ($chatSubmissions as $submission) {
            DB::table('contact_inquiries')->insert([
                'source' => 'chat_widget',
                'move_type' => $submission->move_type,
                'move_size' => $submission->move_size,
                'move_date' => $submission->move_date,
                'address_from' => $submission->address_from,
                'address_to' => $submission->address_to,
                'name' => $submission->name,
                'email' => $submission->email,
                'phone' => $submission->phone,
                'preferred_contact' => $submission->preferred_contact ?? 'email',
                'message' => null,
                'status' => $submission->status ?? 'new',
                'assigned_to' => null,
                'notes' => $submission->notes,
                'created_at' => $submission->created_at,
                'updated_at' => $submission->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        // Remove migrated chat_widget entries
        DB::table('contact_inquiries')
            ->where('source', 'chat_widget')
            ->delete();
    }
};
