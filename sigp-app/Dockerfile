# ==========================================
# Estágio 1: Compilação de Assets Frontend (Vite)
# ==========================================
FROM node:20-alpine AS node_builder

WORKDIR /app

COPY package*.json ./
RUN npm ci || npm install

COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY public ./public

RUN npm run build

# ==========================================
# Estágio 2: Aplicação PHP 8.2 + Nginx (Produção)
# ==========================================
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Instalar pacotes de sistema e bibliotecas
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    curl \
    git \
    netcat-openbsd \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    icu-dev \
    openssl

# Configurar e compilar extensões PHP necessárias para o Laravel e SIGP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        gd \
        zip \
        bcmath \
        intl \
        opcache \
        pcntl \
        exif

# Copiar Composer binário oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar código fonte da aplicação
COPY . .

# Copiar assets estáticos compilados pelo Vite
COPY --from=node_builder /app/public/build ./public/build

# Instalar dependências PHP de produção
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Configurações do Nginx, Supervisor e PHP
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Configurar permissões de diretórios
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
