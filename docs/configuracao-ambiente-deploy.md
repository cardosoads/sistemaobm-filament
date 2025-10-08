# Configuração de Ambiente para Deploy

## Configurações de Sessão por Ambiente

### Desenvolvimento Local (HTTP)
```env
APP_ENV=local
APP_URL=http://sistemaobm-filament.test
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=null
```

### Produção (HTTPS)
```env
APP_ENV=production
APP_URL=https://seudominio.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=.seudominio.com
```

### Staging/Homologação (HTTPS)
```env
APP_ENV=staging
APP_URL=https://staging.seudominio.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=.seudominio.com
```

## Explicação das Configurações

### SESSION_SECURE_COOKIE
- **Local (HTTP)**: `false` - Permite cookies em conexões HTTP
- **Produção/Staging (HTTPS)**: `true` - Força cookies apenas em conexões HTTPS seguras

### SESSION_HTTP_ONLY
- **Todos os ambientes**: `true` - Impede acesso aos cookies via JavaScript (segurança XSS)

### SESSION_SAME_SITE
- **Todos os ambientes**: `lax` - Proteção CSRF moderada, permite navegação normal

### SESSION_DOMAIN
- **Local**: `null` - Usa o domínio atual automaticamente
- **Produção**: `.seudominio.com` - Permite subdomínios (com ponto inicial)

## Checklist para Deploy

### Antes do Deploy
1. ✅ Verificar se `APP_ENV=production`
2. ✅ Configurar `APP_URL` com HTTPS
3. ✅ Definir `SESSION_SECURE_COOKIE=true`
4. ✅ Configurar `SESSION_DOMAIN` adequadamente
5. ✅ Verificar certificado SSL válido

### Após o Deploy
1. ✅ Executar `php artisan config:clear`
2. ✅ Executar `php artisan cache:clear`
3. ✅ Testar login no ambiente de produção
4. ✅ Verificar se as sessões persistem após login

## Comandos Úteis

```bash
# Limpar cache de configuração
php artisan config:clear

# Limpar cache geral
php artisan cache:clear

# Limpar sessões existentes
php artisan tinker --execute="DB::table('sessions')->delete();"

# Verificar configurações atuais
php artisan config:show session
```

## Troubleshooting

### Problema: Login não persiste em produção
**Solução**: Verificar se `SESSION_SECURE_COOKIE=true` e site está usando HTTPS

### Problema: Erro "Session expired" constante
**Solução**: Verificar configuração de domínio e certificado SSL

### Problema: Cookies não funcionam em subdomínios
**Solução**: Configurar `SESSION_DOMAIN=.seudominio.com` (com ponto inicial)