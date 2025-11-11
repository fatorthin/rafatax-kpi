<div class="space-y-4">
    @if ($logbooks->count() > 0)
        {{-- Summary Info --}}
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total LogBooks</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $logbooks->count() }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Job Point</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $jobDescription->point }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Points</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ $logbooks->count() * $jobDescription->point }}</p>
                </div>
            </div>
        </div>

        {{-- LogBook List --}}
        <div class="space-y-3">
            @foreach ($logbooks as $logbook)
                <div
                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-calendar class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($logbook->date)->format('d M Y') }}
                                    </span>
                                </div>
                                @if ($logbook->staff)
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-user class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $logbook->staff->name }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {{ $logbook->description }}
                            </p>

                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-clipboard-document-list class="w-4 h-4 text-gray-400" />
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        Count: {{ $logbook->count_task }}
                                    </span>
                                </div>

                                @if ($logbook->is_approved)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <x-heroicon-o-check-circle class="w-3 h-3" />
                                        Approved
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        <x-heroicon-o-clock class="w-3 h-3" />
                                        Pending
                                    </span>
                                @endif
                            </div>

                            @if ($logbook->comment)
                                <div
                                    class="mt-3 p-2 bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 dark:border-blue-600">
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-o-chat-bubble-left-ellipsis
                                            class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                                        <div>
                                            <p class="text-xs font-medium text-blue-900 dark:text-blue-200">Admin
                                                Comment:</p>
                                            <p class="text-sm text-blue-800 dark:text-blue-300">{{ $logbook->comment }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="text-center py-12">
            <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No LogBooks</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Tidak ada LogBook untuk job description ini pada periode yang dipilih.
            </p>
        </div>
    @endif
</div>
