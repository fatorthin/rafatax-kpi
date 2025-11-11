<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Page Header dengan Period Info --}}
        <div class="bg-white rounded-lg shadow p-6 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Job Description Statistics
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Periode: {{ $this->getSelectedPeriod() }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ now()->format('d F Y') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        @php
            $stats = $this->getStatsCards();
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-6 dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 rounded-md bg-blue-50 dark:bg-blue-900">
                        <x-heroicon-o-briefcase class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Job Descriptions</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_jobs'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 rounded-md bg-green-50 dark:bg-green-900">
                        <x-heroicon-o-document-text class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total LogBooks</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_logbooks'] }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 rounded-md bg-yellow-50 dark:bg-yellow-900">
                        <x-heroicon-o-star class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Points</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_points'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="p-2 rounded-md bg-purple-50 dark:bg-purple-900">
                        <x-heroicon-o-calculator class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg LogBooks/Job</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $stats['avg_logbooks_per_job'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Job Description Table --}}
        <div class="bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Detail Job Description</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Daftar job description dengan jumlah logbook dan point untuk periode
                    {{ $this->getSelectedPeriod() }}
                </p>
            </div>
            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>

        {{-- Performance Indicator --}}
        <div class="bg-white rounded-lg shadow p-6 dark:bg-gray-800">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Performance Indicator</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 border border-red-200 rounded-lg bg-red-50 dark:bg-red-900 dark:border-red-800">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-red-100 dark:bg-red-800">
                            <x-heroicon-o-x-circle class="w-5 h-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800 dark:text-red-200">Needs Attention</p>
                            <p class="text-xs text-red-600 dark:text-red-300">0 LogBooks</p>
                        </div>
                    </div>
                </div>

                <div
                    class="p-4 border border-yellow-200 rounded-lg bg-yellow-50 dark:bg-yellow-900 dark:border-yellow-800">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-yellow-100 dark:bg-yellow-800">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Below Average</p>
                            <p class="text-xs text-yellow-600 dark:text-yellow-300">1-4 LogBooks</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900 dark:border-green-800">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-green-100 dark:bg-green-800">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">Good Performance</p>
                            <p class="text-xs text-green-600 dark:text-green-300">5+ LogBooks</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
