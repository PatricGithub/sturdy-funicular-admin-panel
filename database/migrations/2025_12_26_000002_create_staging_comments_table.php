<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staging_comments', function (Blueprint $table) {
            $table->id();
            $table->string('session_id'); // For non-authenticated users
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();

            // Position data
            $table->string('page_url');
            $table->string('section_selector'); // CSS selector or data-section attribute
            $table->json('position')->nullable(); // {x: %, y: %, width: px, height: px}

            // Comment content
            $table->text('content');
            $table->json('attachments')->nullable(); // [{type: 'image'|'video', url: '...'}]

            // AI suggestion
            $table->longText('ai_suggestion')->nullable();
            $table->boolean('ai_suggestion_approved')->default(false);

            // Status
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'rejected'])->default('pending');
            $table->text('admin_response')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['status']);
            $table->index('session_id');
            $table->index('page_url');
        });

        // Settings for the review system
        Schema::create('staging_review_settings', function (Blueprint $table) {
            $table->id();

            $table->boolean('review_mode_enabled')->default(true);
            $table->boolean('allow_direct_changes')->default(false);
            $table->integer('ai_requests_per_day')->default(10);
            $table->integer('ai_requests_used_today')->default(0);
            $table->date('ai_requests_reset_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staging_review_settings');
        Schema::dropIfExists('staging_comments');
    }
};
