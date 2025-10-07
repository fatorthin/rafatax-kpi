<x-filament-panels::page.simple>
    <div class="w-full max-w-md mx-auto">
        <!-- Login Form -->
        <x-filament-panels::form wire:submit="authenticate" class="space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold py-3 rounded-lg transition-colors duration-200">
                Login
            </x-filament::button>
        </x-filament-panels::form>

    </div>
</x-filament-panels::page.simple>
