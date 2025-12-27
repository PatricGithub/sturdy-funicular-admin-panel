<?php

namespace WebWizr\AdminPanel\Database\Seeders;

use WebWizr\AdminPanel\Models\ButtonSetting;
use WebWizr\AdminPanel\Models\ChatSetting;
use WebWizr\AdminPanel\Models\ChatSettingTranslation;
use WebWizr\AdminPanel\Models\ChatStep;
use WebWizr\AdminPanel\Models\ChatStepTranslation;
use WebWizr\AdminPanel\Models\GdprSetting;
use WebWizr\AdminPanel\Models\GdprSettingTranslation;
use WebWizr\AdminPanel\Models\Language;
use WebWizr\AdminPanel\Models\PopupSetting;
use WebWizr\AdminPanel\Models\PopupSettingTranslation;
use Illuminate\Database\Seeder;

class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Create default language
        $english = Language::firstOrCreate(
            ['code' => 'en'],
            [
                'name' => 'English',
                'native_name' => 'English',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 0,
            ]
        );

        // Create German as example second language
        Language::firstOrCreate(
            ['code' => 'de'],
            [
                'name' => 'German',
                'native_name' => 'Deutsch',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        // GDPR Settings
        $gdpr = GdprSetting::getInstance();
        GdprSettingTranslation::firstOrCreate(
            ['gdpr_setting_id' => $gdpr->id, 'language_id' => $english->id],
            [
                'message' => 'We use cookies to enhance your browsing experience and analyze site traffic. By clicking "Accept", you consent to our use of cookies.',
                'accept_button_text' => 'Accept',
                'decline_button_text' => 'Decline',
                'privacy_link_text' => 'Privacy Policy',
            ]
        );

        // Popup Settings
        $popup = PopupSetting::getInstance();
        PopupSettingTranslation::firstOrCreate(
            ['popup_setting_id' => $popup->id, 'language_id' => $english->id],
            [
                'headline' => 'Welcome!',
                'subheadline' => 'Subscribe to our newsletter and get 10% off your first order.',
                'cta_text' => 'Subscribe Now',
                'cta_url' => '#',
            ]
        );

        // Button Settings
        ButtonSetting::getPhone();
        ButtonSetting::getEmail();

        // Chat Settings
        $chat = ChatSetting::getInstance();
        ChatSettingTranslation::firstOrCreate(
            ['chat_setting_id' => $chat->id, 'language_id' => $english->id],
            [
                'welcome_title' => 'Hello!',
                'welcome_message' => 'How can we help you today? Answer a few quick questions.',
                'final_title' => 'Thank you!',
                'final_message' => 'We have all the information we need. Would you like to speak with us directly?',
                'final_cta_text' => 'Call Us Now',
            ]
        );

        // Create default chat steps
        $step1 = ChatStep::firstOrCreate(
            ['chat_setting_id' => $chat->id, 'sort_order' => 1],
            ['type' => 'question', 'is_active' => true]
        );
        ChatStepTranslation::firstOrCreate(
            ['chat_step_id' => $step1->id, 'language_id' => $english->id],
            [
                'question' => 'What are you looking for?',
                'options' => ['Product Information', 'Support', 'Pricing', 'Other'],
            ]
        );

        $step2 = ChatStep::firstOrCreate(
            ['chat_setting_id' => $chat->id, 'sort_order' => 2],
            ['type' => 'question', 'is_active' => true]
        );
        ChatStepTranslation::firstOrCreate(
            ['chat_step_id' => $step2->id, 'language_id' => $english->id],
            [
                'question' => 'How urgent is your request?',
                'options' => ['Urgent', 'This week', 'Just browsing'],
            ]
        );
    }
}
