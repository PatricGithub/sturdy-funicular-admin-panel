<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('taskable'); // taskable_type, taskable_id
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['email', 'document_review', 'follow_up', 'internal', 'other'])->default('other');
            $table->enum('status', ['open', 'in_progress', 'completed'])->default('open');
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->date('due_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('due_date');
            $table->index('status');
            $table->index('assigned_to');
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
