#!/bin/sh

echo "=== Iniciar SIGP App Container ==="

# Criar diretórios de runtime do Nginx e Supervisord
mkdir -p /run/nginx /var/log/supervisor /var/run

# Assegurar estrutura de diretórios no volume persistente de storage
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/app/agt
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Se não existir openssl.cnf para a AGT no volume montado, cria o padrão
if [ ! -f /var/www/html/storage/app/agt/openssl.cnf ]; then
    cat << 'EOF' > /var/www/html/storage/app/agt/openssl.cnf
[ req ]
default_bits = 2048
distinguished_name = req_distinguished_name
prompt = no

[ req_distinguished_name ]
C = AO
O = SIGP
CN = SIGP System
EOF
    echo "Ficheiro openssl.cnf criado para AGT."
fi

# Ajustar permissões para www-data e nginx
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Criar atalho de storage público caso ainda não exista
php artisan storage:link --force || true

# Testar conexão com banco e rodar migrações
if [ -n "$DB_HOST" ] && [ "$DB_HOST" != "127.0.0.1" ] && [ "$DB_HOST" != "localhost" ]; then
    echo "A aguardar conexão com o banco de dados ($DB_HOST:${DB_PORT:-3306})..."
    max_tries=15
    count=0
    while ! nc -z "$DB_HOST" "${DB_PORT:-3306}" >/dev/null 2>&1; do
        sleep 1
        count=$((count + 1))
        if [ $count -ge $max_tries ]; then
            echo "Aviso: Banco de dados ainda não está pronto após $max_tries segundos. Prosseguindo..."
            break
        fi
    done

    echo "A executar migrações de base de dados..."
    php artisan migrate --force || echo "Aviso: Falha ao executar migrações."
fi

# Otimizar caches do Laravel se APP_KEY estiver definida
if [ -n "$APP_KEY" ]; then
    echo "A otimizar caches do Laravel..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
else
    echo "Aviso: APP_KEY não está definida nas variáveis de ambiente. Pulando cache."
fi

echo "=== A iniciar Supervisord (Nginx + PHP-FPM) ==="
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
