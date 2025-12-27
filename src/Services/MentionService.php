<?php

namespace WebWizr\AdminPanel\Services;

use WebWizr\AdminPanel\Models\CrmNotification;
use WebWizr\AdminPanel\Models\Mention;
use WebWizr\AdminPanel\Models\User;
use Illuminate\Database\Eloquent\Model;

class MentionService
{
    /**
     * Parse text for @mentions and create mention records
     */
    public function parseAndCreate(string $text, Model $mentionable, User $mentionedBy): array
    {
        $mentions = [];

        // Match @username patterns (alphanumeric and underscores)
        preg_match_all('/@([\w]+)/', $text, $matches);

        if (empty($matches[1])) {
            return $mentions;
        }

        $usernames = array_unique($matches[1]);

        foreach ($usernames as $username) {
            // Find user by name (case-insensitive partial match)
            $user = User::where('name', 'like', '%' . $username . '%')
                ->where('id', '!=', $mentionedBy->id) // Don't mention yourself
                ->first();

            if ($user) {
                // Create mention record
                $mention = Mention::create([
                    'mentionable_type' => get_class($mentionable),
                    'mentionable_id' => $mentionable->id,
                    'user_id' => $user->id,
                    'mentioned_by' => $mentionedBy->id,
                    'note_content' => $text,
                    'is_read' => false,
                ]);

                // Create notification
                CrmNotification::create([
                    'user_id' => $user->id,
                    'type' => 'mention',
                    'notifiable_type' => get_class($mentionable),
                    'notifiable_id' => $mentionable->id,
                    'title' => __('admin.you_were_mentioned', ['name' => $mentionedBy->name]),
                    'message' => $this->truncateText($text, 100),
                ]);

                $mentions[] = $mention;
            }
        }

        return $mentions;
    }

    /**
     * Get mention suggestions for autocomplete
     */
    public function getSuggestions(string $query = ''): array
    {
        return User::query()
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%');
            })
            ->whereHas('permissions', function ($q) {
                $q->where('name', 'view_crm');
            })
            ->orWhere('is_super_admin', true)
            ->limit(10)
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Truncate text to max length
     */
    private function truncateText(string $text, int $maxLength): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }

        return substr($text, 0, $maxLength - 3) . '...';
    }
}
