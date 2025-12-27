<?php

namespace WebWizr\AdminPanel\Filament\Widgets;

use WebWizr\AdminPanel\Models\Callback;
use WebWizr\AdminPanel\Models\Mention;
use WebWizr\AdminPanel\Models\Task;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PersonalHotlistWidget extends Widget
{
    protected static string $view = 'webwizr-admin::filament.widgets.personal-hotlist';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public Collection $overdueCallbacks;
    public Collection $todayCallbacks;
    public Collection $overdueTasks;
    public Collection $todayTasks;
    public Collection $unreadMentions;

    public function mount(): void
    {
        $this->loadData();
    }

    public static function canView(): bool
    {
        $mode = config('company.crm_mode');
        if (!in_array($mode, ['full', 'light'])) {
            return false;
        }

        return Auth::user()?->hasPermission('view_crm') ?? false;
    }

    protected function loadData(): void
    {
        $user = Auth::user();
        $isAdmin = $user->isSuperAdmin() || $user->hasPermission('manage_crm');
        $mode = config('company.crm_mode');

        // Base query modifier for role-based visibility
        $userFilter = function ($query) use ($user, $isAdmin) {
            if (!$isAdmin) {
                $query->where('assigned_to', $user->id);
            }
        };

        // Callbacks
        $this->overdueCallbacks = Callback::overdue()
            ->with(['callable', 'assignedUser'])
            ->tap($userFilter)
            ->prioritySorted()
            ->limit(5)
            ->get();

        $this->todayCallbacks = Callback::today()
            ->with(['callable', 'assignedUser'])
            ->tap($userFilter)
            ->prioritySorted()
            ->limit(5)
            ->get();

        // Tasks
        $this->overdueTasks = Task::overdue()
            ->with(['taskable', 'assignedUser'])
            ->tap($userFilter)
            ->prioritySorted()
            ->limit(5)
            ->get();

        $this->todayTasks = Task::dueToday()
            ->with(['taskable', 'assignedUser'])
            ->tap($userFilter)
            ->prioritySorted()
            ->limit(5)
            ->get();

        // Mentions (only in full mode)
        if ($mode === 'full') {
            $this->unreadMentions = Mention::forUser($user->id)
                ->unread()
                ->with(['mentionedByUser', 'mentionable'])
                ->latest()
                ->limit(5)
                ->get();
        } else {
            $this->unreadMentions = collect();
        }
    }

    public function completeCallback(int $id): void
    {
        $callback = Callback::find($id);
        if ($callback) {
            $callback->markAsCompleted();
            $this->loadData();
        }
    }

    public function completeTask(int $id): void
    {
        $task = Task::find($id);
        if ($task) {
            $task->markAsCompleted();
            $this->loadData();
        }
    }

    public function markMentionRead(int $id): void
    {
        $mention = Mention::find($id);
        if ($mention && $mention->user_id === Auth::id()) {
            $mention->markAsRead();
            $this->loadData();
        }
    }

    public function getTotalCount(): int
    {
        return $this->overdueCallbacks->count()
            + $this->todayCallbacks->count()
            + $this->overdueTasks->count()
            + $this->todayTasks->count()
            + $this->unreadMentions->count();
    }
}
