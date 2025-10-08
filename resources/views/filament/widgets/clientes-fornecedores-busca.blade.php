<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <x-heroicon-o-magnifying-glass class="h-5 w-5 inline mr-2" />
            Busca de Clientes e Fornecedores
        </x-slot>

        <x-slot name="description">
            Busque clientes e fornecedores na base local ou através da API externa
        </x-slot>

        {{-- Formulário de busca --}}
        <form wire:submit="buscar" class="space-y-4">
            {{ $this->form }}
            
            <div class="flex gap-2 mt-4">
                <x-filament::button type="submit" icon="heroicon-m-magnifying-glass">
                    Buscar
                </x-filament::button>
                
                <x-filament::button 
                    type="button" 
                    color="gray" 
                    icon="heroicon-m-x-mark"
                    wire:click="limparBusca"
                >
                    Limpar
                </x-filament::button>
            </div>
        </form>

        {{-- Resultados da busca --}}
        @if($buscaRealizada)
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 mt-6">
                Resultados da Busca ({{ count($resultados) }})
            </h3>

            @if(count($resultados) > 0)
                @foreach($resultados as $resultado)
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-3">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    @if($resultado['tipo'] === 'cliente')
                                        <x-heroicon-o-user class="h-6 w-6 text-blue-600 flex-shrink-0" />
                                    @else
                                        <x-heroicon-o-building-office class="h-6 w-6 text-green-600 flex-shrink-0" />
                                    @endif
                                    
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-white">
                                            {{ $resultado['nome'] }}
                                        </h4>
                                        <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                            <span>{{ $resultado['cnpj_cpf'] }}</span>
                                            <span>Código: {{ $resultado['codigo'] }}</span>
                                            <span class="capitalize">{{ $resultado['tipo'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                {{-- Badge de origem --}}
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $resultado['origem'] === 'Local' 
                                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' 
                                        : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' }}">
                                    {{ $resultado['origem'] }}
                                </span>

                                {{-- Badge de status --}}
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $resultado['ativo'] 
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ $resultado['ativo'] ? 'Ativo' : 'Inativo' }}
                                </span>

                                {{-- Botão de importar (apenas para resultados da API) --}}
                                @if($resultado['origem'] === 'API')
                                    <x-filament::button
                                        size="sm"
                                        color="success"
                                        icon="heroicon-m-arrow-down-tray"
                                        wire:click="importarDaApi('{{ $resultado['id'] }}')"
                                    >
                                        Importar
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-magnifying-glass class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                        Nenhum resultado encontrado
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Tente ajustar os termos de busca ou verificar a conexão com a API.
                    </p>
                </div>
            @endif
        @endif
    </x-filament::section>
</x-filament-widgets::widget>