<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-shield-check class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                        Akses Admin
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Anda sedang mengakses panel staff sebagai administrator
                    </p>
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('filament.admin.pages.admin-dashboard') }}"
                    class="inline-flex items-center px-3 py-1.5 bg-amber-600 border border-transparent rounded-md text-xs font-medium text-white hover:bg-amber-700 focus:bg-amber-700 active:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <x-heroicon-o-home class="h-3 w-3 mr-1" />
                    Dashboard Admin
                </a>
                <a href="{{ route('filament.admin.pages.dashboard') }}"
                    class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded-md text-xs font-medium text-white hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <x-heroicon-o-cog-6-tooth class="h-3 w-3 mr-1" />
                    Panel Admin
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

