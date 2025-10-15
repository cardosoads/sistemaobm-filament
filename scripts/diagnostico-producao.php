<?php

/**
 * Script de diagnóstico para problemas em produção
 * Execute: php scripts/diagnostico-producao.php
 */

// Verificar se estamos no ambiente correto
if (php_sapi_name() !== 'cli') {
    die("Este script deve ser executado via linha de comando\n");
}

echo "=== DIAGNÓSTICO DE PRODUÇÃO ===\n\n";

// 1. Verificar configurações básicas
echo "1. Verificando configurações básicas:\n";
$appEnv = getenv('APP_ENV');
echo "   APP_ENV: " . ($appEnv ?: 'Não definido') . "\n";
$appDebug = getenv('APP_DEBUG');
echo "   APP_DEBUG: " . ($appDebug ?: 'Não definido') . "\n";
$appUrl = getenv('APP_URL');
echo "   APP_URL: " . ($appUrl ?: 'Não definido') . "\n\n";

// 2. Verificar configurações de sessão
echo "2. Verificando configurações de sessão:\n";
$sessionSecure = getenv('SESSION_SECURE_COOKIE');
echo "   SESSION_SECURE_COOKIE: " . ($sessionSecure ?: 'Não definido') . "\n";
$sessionDomain = getenv('SESSION_DOMAIN');
echo "   SESSION_DOMAIN: " . ($sessionDomain ?: 'Não definido') . "\n";
$sessionDriver = getenv('SESSION_DRIVER');
echo "   SESSION_DRIVER: " . ($sessionDriver ?: 'Não definido') . "\n\n";

// 3. Verificar conexão com banco de dados
echo "3. Verificando conexão com banco de dados:\n";
try {
    // Carregar autoload do Laravel
    require __DIR__ . '/../vendor/autoload.php';
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Tentar conexão com o banco
    $connection = Illuminate\Support\Facades\DB::connection();
    $pdo = $connection->getPdo();
    
    echo "   ✅ Conexão com banco: OK\n";
    echo "   Database: " . getenv('DB_DATABASE') . "\n";
    echo "   Host: " . getenv('DB_HOST') . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Erro na conexão com banco: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Verificar storage e cache
echo "4. Verificando permissões de storage:\n";
$storagePath = __DIR__ . '/../storage';
$logsPath = $storagePath . '/logs';
$frameworkPath = $storagePath . '/framework';

$paths = [
    $storagePath => 'Storage',
    $logsPath => 'Storage/Logs',
    $frameworkPath => 'Storage/Framework',
    $frameworkPath . '/cache' => 'Storage/Framework/Cache',
    $frameworkPath . '/sessions' => 'Storage/Framework/Sessions',
    $frameworkPath . '/views' => 'Storage/Framework/Views'
];

foreach ($paths as $path => $label) {
    if (!file_exists($path)) {
        echo "   ❌ $label: Diretório não existe\n";
    } elseif (!is_writable($path)) {
        echo "   ❌ $label: Sem permissão de escrita\n";
    } else {
        echo "   ✅ $label: OK\n";
    }
}

echo "\n";

// 5. Verificar se há logs de erro recentes
echo "5. Verificando logs de erro:\n";
$logFile = $logsPath . '/laravel.log';
if (file_exists($logFile)) {
    $logSize = filesize($logFile);
    echo "   Log size: " . round($logSize / 1024 / 1024, 2) . " MB\n";
    
    // Ler últimas linhas do log
    $lastLines = shell_exec('tail -n 10 ' . escapeshellarg($logFile));
    if ($lastLines) {
        $lines = explode("\n", trim($lastLines));
        $errorLines = array_filter($lines, function($line) {
            return stripos($line, 'error') !== false || 
                   stripos($line, 'exception') !== false;
        });
        
        if (count($errorLines) > 0) {
            echo "   Últimos erros encontrados:\n";
            foreach (array_slice($errorLines, -3) as $error) {
                echo "     - " . substr($error, 0, 100) . "...\n";
            }
        } else {
            echo "   ✅ Nenhum erro recente encontrado\n";
        }
    }
} else {
    echo "   ℹ️  Arquivo de log não encontrado\n";
}

echo "\n=== RECOMENDAÇÕES ===\n";

if ($appEnv !== 'production') {
    echo "• ❗ Configure APP_ENV=production para ambiente de produção\n";
}

if ($appDebug === 'true') {
    echo "• ❗ Desative APP_DEBUG em produção (APP_DEBUG=false)\n";
}

if (empty($sessionDomain) && $appUrl && strpos($appUrl, 'https://') === 0) {
    $domain = parse_url($appUrl, PHP_URL_HOST);
    echo "• 🔧 Configure SESSION_DOMAIN=.{$domain} para suporte a subdomínios\n";
}

echo "• 🔧 Execute os comandos de limpeza:\n";
echo "  php artisan config:clear\n";
echo "  php artisan cache:clear\n";
echo "  php artisan view:clear\n";

echo "\nPara mais detalhes, consulte a documentação em docs/configuracao-ambiente-deploy.md\n";
?>