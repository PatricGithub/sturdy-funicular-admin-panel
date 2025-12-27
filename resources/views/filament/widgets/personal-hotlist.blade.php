<x-filament-widgets::widget>
    @if($this->getTotalCount() > 0)
        <x-filament::section>
            <x-slot name="heading">
                {{ __('admin.personal_hotlist') }}
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Overdue Callbacks --}}
                @if($overdueCallbacks->isNotEmpty())
                    <div>
                        <h4 class="text-sm font-medium text-danger-600 dark:text-danger-400 mb-3 flex items-center gap-2">
                            <x-heroicon-m-exclamation-circle class="w-4 h-4" />
                            {{ __('admin.overdue_callbacks') }}
                        </h4>
                        <ul class="space-y-2">
                            @foreach($overdueCallbacks as $callback)
                                <li class="bg-danger-50 dark:bg-danger-950 rounded-lg p-3 border border-danger-200 dark:border-danger-800">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white truncate">
                                                {{ $callback->callable?->name ?? 'Unknown' }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $callback->scheduled_at->format('d.m.Y H:i') }}
                                            </p>
                                        </div>
                                        <button
                                            wire:click="completeCallback({{ $callback->id }})"
                                            class="text-success-600 hover:text-success-700 dark:text-success-400"
                                            title="{{ __('admin.mark_complete') }}"
                                        >
                                            <x-heroicon-m-check-circle class="w-5 h-5" />
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Today's Callbacks --}}
                @if($todayCallbacks->isNotEmpty())
                    <div>
                        <h4 class="text-sm font-medium text-warning-600 dark:text-warning-400 mb-3 flex items-center gap-2">
                            <x-heroicon-m-phone class="w-4 h-4" />
                            {{ __('admin.today_callbacks') }}
                        </h4>
                        <ul class="space-y-2">
                            @foreach($todayCallbacks as $callback)
                                <li class="bg-warning-50 dark:bg-warning-950 rounded-lg p-3 border border-warning-200 dark:border-warning-800">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white truncate">
                                                {{ $callback->callable?->name ?? 'Unknown' }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $callback->scheduled_at->format('H:i') }}
                                            </p>
                                        </div>
                                        <button
                                            wire:click="completeCallback({{ $callback->id }})"
                                            class="text-success-600 hover:text-success-700 dark:text-success-400"
                                            title="{{ __('admin.mark_complete') }}"
                                        >
                                            <x-heroicon-m-check-circle class="w-5 h-5" />
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Overdue Tasks --}}
                @if($overdueTasks->isNotEmpty())
                    <div>
                        <h4 class="text-sm font-medium text-danger-600 dark:text-danger-400 mb-3 flex items-center gap-2">
                            <x-heroicon-m-clipboard-document-check class="w-4 h-4" />
                            {{ __('admin.overdue_tasks') }}
                        </h4>
                        <ul class="space-y-2">
                            @foreach($overdueTasks as $task)
                                <li class="bg-danger-50 dark:bg-danger-950 rounded-lg p-3 border border-danger-200 dark:border-danger-800">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white truncate">
                                                {{ $task->title }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $task->due_at->format('d.m.Y') }}
                                            </p>
                                        </div>
                                        <button
                                            wire:click="completeTask({{ $task->id }})"
                                            class="text-success-600 hover:text-success-700 dark:text-success-400"
                                            title="{{ __('admin.mark_complete') }}"
                                        >
                                            <x-heroicon-m-check-circle class="w-5 h-5" />
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Today's Tasks --}}
                @if($todayTasks->isNotEmpty())
                    <div>
                        <h4 class="text-sm font-medium text-primary-600 dark:text-primary-400 mb-3 flex items-center gap-2">
                            <x-heroicon-m-clipboard-document-list class="w-4 h-4" />
                            {{ __('admin.today_tasks') }}
                        </h4>
                        <ul class="space-y-2">
                            @foreach($todayTasks as $task)
                                <li class="bg-primary-50 dark:bg-primary-950 rounded-lg p-3 border border-primary-200 dark:border-primary-800">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white truncate">
                                                {{ $task->title }}
                                            </p>
                                            @if($task->taskable)
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $task->taskable->name ?? '' }}
                                                </p>
                                            @endif
                                        </div>
                                        <button
                                            wire:click="completeTask({{ $task->id }})"
                                            class="text-success-600 hover:text-success-700 dark:text-success-400"
                                            title="{{ __('admin.mark_complete') }}"
                                        >
                                            <x-heroicon-m-check-circle class="w-5 h-5" />
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Unread Mentions --}}
                @if($unreadMentions->isNotEmpty())
                    <div>
                        <h4 class="text-sm font-medium text-info-600 dark:text-info-400 mb-3 flex items-center gap-2">
                            <x-heroicon-m-at-symbol class="w-4 h-4" />
                            {{ __('admin.unread_mentions') }}
                        </h4>
                        <ul class="space-y-2">
                            @foreach($unreadMentions as $mention)
                                <li class="bg-info-50 dark:bg-info-950 rounded-lg p-3 border border-info-200 dark:border-info-800">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white truncate">
                                                {{ $mention->mentionedByUser?->name ?? 'Someone' }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $mention->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        <button
                                            wire:click="markMentionRead({{ $mention->id }})"
                                            class="text-success-600 hover:text-success-700 dark:text-success-400"
                                            title="{{ __('admin.mark_read') }}"
                                        >
                                            <x-heroicon-m-check class="w-5 h-5" />
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </x-filament::section>
    @endif
</x-filament-widgets::widget>
