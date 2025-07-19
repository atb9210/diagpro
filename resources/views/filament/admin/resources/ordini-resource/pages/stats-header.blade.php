<div class="mb-4">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Statistiche Ordini</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400">Periodo: {{ $stats['periodo'] }}</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <!-- Totale Ordini -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Totale Ordini</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['totale_ordini']) }}</p>
            </div>
        </div>
    </div>

    <!-- Totale Venduto -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Totale Venduto</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">€{{ number_format($stats['totale_venduto'], 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Totale Profitto -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 {{ $stats['totale_profitto'] >= 0 ? 'text-green-500' : 'text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Totale Profitto</p>
                <p class="text-2xl font-semibold {{ $stats['totale_profitto'] >= 0 ? 'text-green-600' : 'text-red-600' }}">€{{ number_format($stats['totale_profitto'], 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Margine % -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 {{ $stats['margine_percentuale'] >= 20 ? 'text-green-500' : ($stats['margine_percentuale'] >= 10 ? 'text-yellow-500' : 'text-red-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Margine %</p>
                <p class="text-2xl font-semibold {{ $stats['margine_percentuale'] >= 20 ? 'text-green-600' : ($stats['margine_percentuale'] >= 10 ? 'text-yellow-600' : 'text-red-600') }}">{{ number_format($stats['margine_percentuale'], 1) }}%</p>
            </div>
        </div>
    </div>

    <!-- Obiettivo -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Obiettivo</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">€{{ number_format($stats['obiettivo'], 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Percentuale Obiettivo -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="w-8 h-8 {{ $stats['percentuale_obiettivo'] >= 100 ? 'text-green-500' : ($stats['percentuale_obiettivo'] >= 75 ? 'text-yellow-500' : 'text-red-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">% Obiettivo</p>
                <p class="text-2xl font-semibold {{ $stats['percentuale_obiettivo'] >= 100 ? 'text-green-600' : ($stats['percentuale_obiettivo'] >= 75 ? 'text-yellow-600' : 'text-red-600') }}">{{ number_format($stats['percentuale_obiettivo'], 1) }}%</p>
                @if($stats['percentuale_obiettivo'] > 0)
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="{{ $stats['percentuale_obiettivo'] >= 100 ? 'bg-green-600' : ($stats['percentuale_obiettivo'] >= 75 ? 'bg-yellow-600' : 'bg-red-600') }} h-2 rounded-full" style="width: {{ min($stats['percentuale_obiettivo'], 100) }}%"></div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>