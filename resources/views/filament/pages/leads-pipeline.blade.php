<x-filament-panels::page>
    <div class="leads-pipeline" x-data="{ dragging: null }">
        <div class="flex gap-4 overflow-x-auto pb-4">
            @foreach($statuses as $status)
                <div
                    class="flex-shrink-0 w-80 bg-gray-50 dark:bg-gray-900 rounded-xl"
                    x-on:dragover.prevent
                    x-on:drop="$wire.moveInquiry(dragging, '{{ $status->key }}')"
                >
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-3 h-3 rounded-full"
                                style="background-color: {{ $status->color }}"
                            ></span>
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                {{ $status->label }}
                            </h3>
                            <span class="ml-auto text-sm text-gray-500 dark:text-gray-400">
                                {{ $status->inquiries->count() }}
                            </span>
                        </div>
                    </div>

                    <div class="p-2 space-y-2 min-h-[200px]">
                        @foreach($status->inquiries as $inquiry)
                            <div
                                class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700 cursor-move hover:shadow-md transition-shadow"
                                draggable="true"
                                x-on:dragstart="dragging = {{ $inquiry->id }}"
                                x-on:dragend="dragging = null"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-white truncate">
                                            {{ $inquiry->name }}
                                        </h4>
                                        @if($inquiry->email)
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                                {{ $inquiry->email }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                @if($inquiry->phone)
                                    <a
                                        href="tel:{{ $inquiry->phone }}"
                                        class="mt-2 inline-flex items-center gap-1 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400"
                                        wire:click="trackPhoneClick({{ $inquiry->id }})"
                                    >
                                        <x-heroicon-m-phone class="w-4 h-4" />
                                        {{ $inquiry->phone }}
                                    </a>
                                @endif

                                <div class="mt-3 flex items-center justify-between text-xs text-gray-400 dark:text-gray-500">
                                    <span>{{ $inquiry->created_at->diffForHumans() }}</span>
                                    @if($inquiry->assignedUser)
                                        <span class="flex items-center gap-1">
                                            <x-heroicon-m-user class="w-3 h-3" />
                                            {{ $inquiry->assignedUser->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
