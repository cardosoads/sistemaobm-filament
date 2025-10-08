<div style="display: flex; flex-direction: column; gap: 20px;" wire:poll.2s="loadProgress">
    @if($error)
        <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 2px solid #fca5a5; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.1);">
            <div style="display: flex; align-items: center;">
                <div style="background: #ef4444; border-radius: 8px; padding: 8px; margin-right: 12px;">
                    <svg style="width: 20px; height: 20px; color: white;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 style="font-size: 16px; font-weight: 600; color: #991b1b; margin: 0;">Erro na sincronização</h3>
            </div>
            <div style="margin-top: 12px; font-size: 14px; color: #b91c1c; line-height: 1.5;">
                {{ $error }}
            </div>
        </div>
    @endif

    @if($completed && !$error)
        <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #86efac; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.1);">
            <div style="display: flex; align-items: center;">
                <div style="background: #22c55e; border-radius: 8px; padding: 8px; margin-right: 12px;">
                    <svg style="width: 20px; height: 20px; color: white;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 style="font-size: 16px; font-weight: 600; color: #14532d; margin: 0;">Sincronização concluída com sucesso!</h3>
            </div>
            <div style="margin-top: 16px; font-size: 14px; color: #166534; line-height: 1.6;">
                <ul style="margin: 0; padding-left: 20px; list-style-type: disc;">
                    @if(($stats['clientes_criados'] ?? 0) > 0)
                        <li style="margin-bottom: 6px;">{{ $stats['clientes_criados'] ?? 0 }} clientes criados</li>
                    @endif
                    @if(($stats['clientes_atualizados'] ?? 0) > 0)
                        <li style="margin-bottom: 6px;">{{ $stats['clientes_atualizados'] ?? 0 }} clientes atualizados</li>
                    @endif
                    @if(($stats['fornecedores_criados'] ?? 0) > 0)
                        <li style="margin-bottom: 6px;">{{ $stats['fornecedores_criados'] ?? 0 }} fornecedores criados</li>
                    @endif
                    @if(($stats['fornecedores_atualizados'] ?? 0) > 0)
                        <li style="margin-bottom: 6px;">{{ $stats['fornecedores_atualizados'] ?? 0 }} fornecedores atualizados</li>
                    @endif
                    @if(($stats['erros'] ?? 0) > 0)
                        <li style="margin-bottom: 0; color: #ea580c;">{{ $stats['erros'] ?? 0 }} erros encontrados</li>
                    @endif
                </ul>
            </div>
        </div>
    @endif

    @if($isRunning || $completed)
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <!-- Status atual -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <span style="font-size: 14px; font-weight: 600; color: #374151;">{{ $currentStep }}</span>
                <span style="font-size: 14px; color: #6b7280; font-weight: 500;">{{ $progress }}%</span>
            </div>

            <!-- Barra de progresso -->
            <div style="width: 100%; background: #e5e7eb; border-radius: 12px; height: 12px; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);">
                <div style="background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%); height: 12px; border-radius: 12px; transition: all 0.3s ease-out; width: {{ $progress }}%; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);"></div>
            </div>

            <!-- Informações detalhadas -->
            @if($totalRecords > 0)
                <div style="font-size: 14px; color: #6b7280; text-align: center; background: #f1f5f9; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1;">
                    {{ $processedRecords }} de {{ $totalRecords }} registros processados
                </div>
            @endif

            <!-- Estatísticas em tempo real -->
            @if($isRunning && (($stats['clientes_criados'] ?? 0) > 0 || ($stats['clientes_atualizados'] ?? 0) > 0 || ($stats['fornecedores_criados'] ?? 0) > 0 || ($stats['fornecedores_atualizados'] ?? 0) > 0))
                <div style="display: grid; grid-template-columns: 1fr; gap: 16px; font-size: 14px;">
                    <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); padding: 16px; border-radius: 12px; border: 1px solid #93c5fd; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);">
                        <div style="font-weight: 600; color: #1e40af; margin-bottom: 4px;">Clientes</div>
                        <div style="color: #2563eb; line-height: 1.4;">
                            {{ $stats['clientes_criados'] ?? 0 }} criados, {{ $stats['clientes_atualizados'] ?? 0 }} atualizados
                        </div>
                    </div>
                    <div style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); padding: 16px; border-radius: 12px; border: 1px solid #86efac; box-shadow: 0 2px 8px rgba(34, 197, 94, 0.1);">
                        <div style="font-weight: 600; color: #14532d; margin-bottom: 4px;">Fornecedores</div>
                        <div style="color: #16a34a; line-height: 1.4;">
                            {{ $stats['fornecedores_criados'] ?? 0 }} criados, {{ $stats['fornecedores_atualizados'] ?? 0 }} atualizados
                        </div>
                    </div>
                </div>
            @endif

            <!-- Indicador de carregamento -->
            @if($isRunning)
                <div style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #f0f9ff; padding: 16px; border-radius: 10px; border: 1px solid #bae6fd;">
                    <svg style="animation: spin 1s linear infinite; height: 16px; width: 16px; color: #3b82f6;" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span style="font-size: 14px; color: #1e40af; font-weight: 500;">Sincronizando...</span>
                </div>
            @endif
        </div>
    @endif

    @if(!$isRunning && !$completed && !$error)
        <div style="text-align: center; padding: 32px 16px; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 16px; border: 2px dashed #cbd5e1;">
            <div style="background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%); border-radius: 16px; padding: 16px; display: inline-block; margin-bottom: 16px;">
                <svg style="margin: 0 auto; height: 48px; width: 48px; color: #9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
            <h3 style="margin: 8px 0; font-size: 18px; font-weight: 600; color: #111827;">Aguardando sincronização</h3>
            <p style="margin: 4px 0 0 0; font-size: 14px; color: #6b7280; line-height: 1.5;">A sincronização é executada automaticamente pelo sistema</p>
        </div>
    @endif

    @push('scripts')
    <style>
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    </style>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('progress-updated', () => {
                // Força a atualização da interface
            });
            
            Livewire.on('sync-completed', (stats) => {
                // Pode adicionar lógica adicional quando a sincronização for concluída
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            });
        });
    </script>
    @endpush
</div>
