#!/bin/bash

# Script de Deploy para Produção - Sistema OBM Filament
# Execute este script no servidor de produção após fazer o deploy do código

echo "🚀 Iniciando deploy para produção..."

# Verificar se estamos em produção
if [ "$APP_ENV" != "production" ]; then
    echo "⚠️  ATENÇÃO: APP_ENV não está definido como 'production'"
    echo "   Verifique o arquivo .env antes de continuar"
    read -p "   Deseja continuar mesmo assim? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "❌ Deploy cancelado"
        exit 1
    fi
fi

# Verificar se HTTPS está configurado
if [[ "$APP_URL" != https://* ]]; then
    echo "⚠️  ATENÇÃO: APP_URL não está usando HTTPS"
    echo "   URL atual: $APP_URL"
    echo "   Para produção, recomenda-se usar HTTPS"
fi

echo "📋 Verificando configurações de sessão..."

# Verificar configurações críticas de sessão
if [ "$SESSION_SECURE_COOKIE" != "true" ]; then
    echo "⚠️  RECOMENDAÇÃO: SESSION_SECURE_COOKIE deveria ser 'true' em produção"
fi

if [ "$SESSION_HTTP_ONLY" != "true" ]; then
    echo "⚠️  RECOMENDAÇÃO: SESSION_HTTP_ONLY deveria ser 'true' para segurança"
fi

echo "🔧 Executando comandos de deploy..."

# Instalar dependências
echo "📦 Instalando dependências..."
composer install --no-dev --optimize-autoloader

# Gerar chave da aplicação se não existir
if [ -z "$APP_KEY" ]; then
    echo "🔑 Gerando chave da aplicação..."
    php artisan key:generate --force
fi

# Executar migrações
echo "🗄️  Executando migrações..."
php artisan migrate --force

# Limpar e otimizar caches
echo "🧹 Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "⚡ Otimizando para produção..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Limpar sessões antigas (opcional)
read -p "🗑️  Deseja limpar todas as sessões existentes? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan tinker --execute="DB::table('sessions')->delete(); echo 'Sessões limpas';"
fi

# Definir permissões corretas
echo "🔒 Configurando permissões..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "✅ Deploy concluído!"
echo ""
echo "🔍 Próximos passos:"
echo "   1. Teste o login no ambiente de produção"
echo "   2. Verifique se as sessões persistem"
echo "   3. Monitore os logs para erros: tail -f storage/logs/laravel.log"
echo ""
echo "📊 Configurações atuais:"
echo "   APP_ENV: $APP_ENV"
echo "   APP_URL: $APP_URL"
echo "   SESSION_SECURE_COOKIE: $SESSION_SECURE_COOKIE"
echo "   SESSION_DOMAIN: $SESSION_DOMAIN"