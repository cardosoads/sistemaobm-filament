<?php

namespace App\Filament\Widgets;

use App\Services\OmieService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Exception;

class OmieConnectionWidget extends Widget
{
    protected static ?int $sort = 1;
    
    public $connectionStatus = null;
    public $lastCheck = null;
    public $errorMessage = null;
    public $isLoading = false;
    
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament.widgets.omie-connection-widget');
    }
    
    public function mount(): void
    {
        $this->loadConnectionStatus();
        
        // Se não há status em cache ou é muito antigo (mais de 5 minutos), testa automaticamente
        if ($this->connectionStatus === null || $this->shouldAutoTest()) {
            $this->testConnectionAutomatically();
        }
    }
    
    private function shouldAutoTest(): bool
    {
        if (!$this->lastCheck) {
            return true;
        }
        
        // Testa automaticamente se o último teste foi há mais de 5 minutos
        return $this->lastCheck->diffInMinutes(now()) > 5;
    }
    
    private function testConnectionAutomatically(): void
    {
        // Executa o teste automaticamente após um pequeno delay
        $this->js('setTimeout(() => { $wire.testConnection(); }, 1000);');
    }
    
    public function testConnection()
    {
        $this->isLoading = true;
        
        try {
            $omieService = app(OmieService::class);
            $result = $omieService->testConnection();
            
            $this->connectionStatus = $result['success'];
            $this->errorMessage = $result['success'] ? null : ($result['message'] ?? 'Erro desconhecido');
            $this->lastCheck = now();
            
            // Cache o resultado por 5 minutos
            Cache::put('omie_connection_status', [
                'status' => $this->connectionStatus,
                'error' => $this->errorMessage,
                'checked_at' => $this->lastCheck
            ], 300);
            
            if ($this->connectionStatus) {
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Conexão com Omie estabelecida com sucesso!'
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Falha na conexão com Omie: ' . $this->errorMessage
                ]);
            }
            
        } catch (Exception $e) {
            $this->connectionStatus = false;
            $this->errorMessage = $e->getMessage();
            $this->lastCheck = now();
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erro ao testar conexão: ' . $e->getMessage()
            ]);
        }
        
        $this->isLoading = false;
    }
    

    
    private function loadConnectionStatus(): void
    {
        $cached = Cache::get('omie_connection_status');
        
        if ($cached) {
            $this->connectionStatus = $cached['status'];
            $this->errorMessage = $cached['error'];
            $this->lastCheck = $cached['checked_at'];
        }
    }
    
    public function getConnectionStatusColor(): string
    {
        if ($this->connectionStatus === null) {
            return 'gray';
        }
        
        return $this->connectionStatus ? 'success' : 'danger';
    }
    
    public function getConnectionStatusText(): string
    {
        if ($this->connectionStatus === null) {
            return 'Não testado';
        }
        
        return $this->connectionStatus ? 'Conectado' : 'Desconectado';
    }
    
    public function getConnectionStatusIcon(): string
    {
        if ($this->connectionStatus === null) {
            return 'heroicon-o-question-mark-circle';
        }
        
        return $this->connectionStatus ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle';
    }
}