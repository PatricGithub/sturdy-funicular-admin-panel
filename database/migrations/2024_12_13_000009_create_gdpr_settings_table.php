<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gdpr_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('background_color')->default('#1f2937');
            $table->string('text_color')->default('#ffffff');
            $table->string('button_color')->default('#3b82f6');
            $table->string('button_text_color')->default('#ffffff');
            $table->string('link_color')->default('#60a5fa');
            $table->string('privacy_policy_url')->nullable();
            $table->timestamps();
        });

        Schema::create('gdpr_setting_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gdpr_setting_id')->constrained()->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->text('message')->nullable();
            $table->string('accept_button_text')->default('Accept');
            $table->string('decline_button_text')->default('Decline');
            $table->string('privacy_link_text')->default('Privacy Policy');
            $table->timestamps();

            $table->unique(['gdpr_setting_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gdpr_setting_translations');
        Schema::dropIfExists('gdpr_settings');
    }
};
