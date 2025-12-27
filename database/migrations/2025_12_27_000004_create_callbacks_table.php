<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('callbacks', function (Blueprint $table) {
            $table->id();
            $table->morphs('callable'); // callable_type, callable_id
            $table->dateTime('scheduled_at');
            $table->dateTime('completed_at')->nullable();
            $table->enum('status', ['open', 'completed', 'cancelled'])->default('open');
            $table->enum('priority', ['normal', 'high'])->default('normal');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('scheduled_at');
            $table->index('status');
            $table->index('assigned_to');
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('callbacks');
    }
};
