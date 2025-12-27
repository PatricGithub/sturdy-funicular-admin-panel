<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#6366f1');
            $table->integer('order')->default(0);
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default stages
        DB::table('pipeline_stages')->insert([
            ['name' => 'Lead', 'slug' => 'lead', 'color' => '#6b7280', 'order' => 1, 'is_won' => false, 'is_lost' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Qualifiziert', 'slug' => 'qualified', 'color' => '#3b82f6', 'order' => 2, 'is_won' => false, 'is_lost' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Angebot', 'slug' => 'proposal', 'color' => '#f59e0b', 'order' => 3, 'is_won' => false, 'is_lost' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Verhandlung', 'slug' => 'negotiation', 'color' => '#8b5cf6', 'order' => 4, 'is_won' => false, 'is_lost' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gewonnen', 'slug' => 'won', 'color' => '#10b981', 'order' => 5, 'is_won' => true, 'is_lost' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Verloren', 'slug' => 'lost', 'color' => '#ef4444', 'order' => 6, 'is_won' => false, 'is_lost' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
    }
};
