<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_inquiry_id')->nullable()->constrained('contact_inquiries')->nullOnDelete();
            $table->foreignId('pipeline_stage_id')->constrained('pipeline_stages');
            $table->decimal('value', 12, 2)->nullable();
            $table->date('expected_close_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->string('lost_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('pipeline_stage_id');
            $table->index('assigned_to');
            $table->index('expected_close_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
