<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-link class="w-3 h-3 text-primary-500" style="width: 0.75rem !important; height: 0.75rem !important;" />
                Conexão Omie API
            </div>
        </x-slot>

        <div class="space-y-4">
            <!-- Status da Conexão -->
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex items-center gap-3">
                    @if($isLoading)
                        <x-heroicon-o-arrow-path class="w-3 h-3 text-blue-500 animate-spin" style="width: 0.75rem !important; height: 0.75rem !important;" />
                    @else
                        <x-dynamic-component 
                            :component="$this->getConnectionStatusIcon()" 
                            :class="'w-3 h-3 ' . ($connectionStatus === null ? 'text-gray-500' : ($connectionStatus === true ? 'text-green-500' : 'text-red-500'))"
                            style="width: 0.75rem !important; height: 0.75rem !important;"
                        />
                    @endif
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">
                            Status: {{ $isLoading ? 'Testando...' : $this->getConnectionStatusText() }}
                        </p>
                        @if($lastCheck && !$isLoading)
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Último teste: {{ $lastCheck->diffForHumans() }}
                            </p>
                        @endif
                    </div>
                </div>
                
                <x-filament::badge 
                    :color="$isLoading ? 'gray' : $this->getConnectionStatusColor()"
                    size="md"
                >
                    {{ $isLoading ? 'Testando...' : $this->getConnectionStatusText() }}
                </x-filament::badge>
            </div>

            <!-- Mensagem de Erro -->
            @if($errorMessage && !$isLoading)
                <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-exclamation-triangle class="w-3 h-3 text-red-500 mt-0.5 flex-shrink-0" style="width: 0.75rem !important; height: 0.75rem !important;" />
                        <div>
                            <p class="font-medium text-red-800 dark:text-red-200">Erro de Conexão</p>
                            <p class="text-sm text-red-700 dark:text-red-300 mt-1">{{ $errorMessage }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>