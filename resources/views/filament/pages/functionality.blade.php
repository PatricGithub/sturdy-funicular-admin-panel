<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <form wire:submit="savePopup">
                {{ $this->popupForm }}
                <div class="mt-4">
                    <x-filament::button type="submit" size="sm">
                        {{ __('filament-panels::resources/pages/edit-record.form.actions.save.label') }}
                    </x-filament::button>
                </div>
            </form>
        </div>

        <div>
            <form wire:submit="savePhone">
                {{ $this->phoneForm }}
                <div class="mt-4">
                    <x-filament::button type="submit" size="sm">
                        {{ __('filament-panels::resources/pages/edit-record.form.actions.save.label') }}
                    </x-filament::button>
                </div>
            </form>
        </div>

        <div>
            <form wire:submit="saveEmail">
                {{ $this->emailForm }}
                <div class="mt-4">
                    <x-filament::button type="submit" size="sm">
                        {{ __('filament-panels::resources/pages/edit-record.form.actions.save.label') }}
                    </x-filament::button>
                </div>
            </form>
        </div>

        <div>
            <form wire:submit="saveChat">
                {{ $this->chatForm }}
                <div class="mt-4">
                    <x-filament::button type="submit" size="sm">
                        {{ __('filament-panels::resources/pages/edit-record.form.actions.save.label') }}
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
