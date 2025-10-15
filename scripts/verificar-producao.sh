#!/bin/bash

echo "=== VERIFICAÇÃO DO AMBIENTE DE PRODUÇÃO ==="
echo ""

# 1. Verificar se o arquivo .env existe
if [ ! -f ".env" ]; then
    echo "❌ ARQUIVO .env NÃO ENCONTRADO"
    echo "   Crie o arquivo .env a partir do .env.example"
    echo "   cp .env.example .env"
    echo "   php artisan key:generate"
    exit 1
fi

echo "✅ Arquivo .env encontrado"

# 2. Verificar configurações básicas
echo ""
echo "📋 CONFIGURAÇÕES BÁSICAS:"
APP_ENV=$(grep -E "^APP_ENV=" .env | cut -d '=' -f2)
APP_DEBUG=$(grep -E "^APP_DEBUG=" .env | cut -d '=' -f2)
APP_URL=$(grep -E "^APP_URL=" .env | cut -d '=' -f2)

echo "   APP_ENV: $APP_ENV"
echo "   APP_DEBUG: $APP_DEBUG"
echo "   APP_URL: $APP_URL"

# 3. Verificar configurações de banco
echo ""
echo "🗄️  CONFIGURAÇÕES DO BANCO:"
DB_HOST=$(grep -E "^DB_HOST=" .env | cut -d '=' -f2)
DB_PORT=$(grep -E "^DB_PORT=" .env | cut -d '=' -f2)
DB_DATABASE=$(grep -E "^DB_DATABASE=" .env | cut -d '=' -f2)
DB_USERNAME=$(grep -E "^DB_USERNAME=" .env | cut -d '=' -f2)

echo "   DB_HOST: $DB_HOST"
echo "   DB_PORT: $DB_PORT"
echo "   DB_DATABASE: $DB_DATABASE"
echo "   DB_USERNAME: $DB_USERNAME"

# 4. Verificar se o MySQL está acessível
echo ""
echo "🔍 TESTANDO CONEXÃO COM MYSQL:"
if command -v nc &> /dev/null; then
    if nc -z -w5 "$DB_HOST" "$DB_PORT"; then
        echo "✅ MySQL está acessível em $DB_HOST:$DB_PORT"
    else
        echo "❌ Não foi possível conectar ao MySQL em $DB_HOST:$DB_PORT"
        echo "   Verifique se o serviço MySQL está rodando"
        echo "   Verifique firewall e permissões de rede"
    fi
else
    echo "ℹ️  Comando 'nc' não disponível, pulando teste de conexão"
fi

# 5. Verificar permissões
echo ""
echo "🔐 VERIFICANDO PERMISSÕES:"
if [ -w "storage" ]; then
    echo "✅ Permissão de escrita em storage: OK"
else
    echo "❌ Sem permissão de escrita em storage"
    echo "   sudo chmod -R 775 storage"
    echo "   sudo chown -R www-data:www-data storage"
fi

if [ -w "bootstrap/cache" ]; then
    echo "✅ Permissão de escrita em bootstrap/cache: OK"
else
    echo "❌ Sem permissão de escrita em bootstrap/cache"
    echo "   sudo chmod -R 775 bootstrap/cache"
    echo "   sudo chown -R www-data:www-data bootstrap/cache"
fi

echo ""
echo "🚀 COMANDOS PARA EXECUTAR:"
echo "   php artisan config:clear"
echo "   php artisan cache:clear"
echo "   php artisan view:clear"
echo "   php artisan optimize"

echo ""
echo "📋 SE O PROBLEMA PERSISTIR:"
echo "1. Verifique os logs: tail -f storage/logs/laravel.log"
echo "2. Teste a conexão manualmente:"
echo "   mysql -h $DB_HOST -P $DB_PORT -u $DB_USERNAME -p $DB_DATABASE"
echo "3. Verifique se o banco existe: mysql -e 'SHOW DATABASES;'"
echo ""