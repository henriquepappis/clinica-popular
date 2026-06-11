# Estágio base
FROM php:8.3-fpm-alpine AS base

# Argumentos
ARG UID=1000
ARG GID=1000

# Instalar dependências do sistema
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev \
    libxml2-dev \
    linux-headers \
    $PHPIZE_DEPS

# Instalar extensões PHP
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    opcache \
    intl \
    xml \
    dom \
    soap

# Instalar Redis
RUN pecl install redis && docker-php-ext-enable redis

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Criar usuário não-root
RUN addgroup -g ${GID} appgroup && \
    adduser -D -u ${UID} -G appgroup appuser

# Definir diretório de trabalho
WORKDIR /var/www

# Copiar arquivos de configuração
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Mudar ownership
RUN chown -R appuser:appgroup /var/www

# Usar usuário não-root
USER appuser

# Expor porta
EXPOSE 9000

# Comando padrão
CMD ["php-fpm"]

# ===== ESTÁGIO DE DESENVOLVIMENTO =====
FROM base AS development

USER root

# Instalar Xdebug para desenvolvimento
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configuração Xdebug (SEM LOG)
RUN echo "xdebug.mode=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Instalar Node.js e NPM
RUN apk add --no-cache nodejs npm

USER appuser

# Instalar Node.js e NPM
RUN apk add --no-cache nodejs npm

USER appuser

# ===== ESTÁGIO DE PRODUÇÃO =====
FROM base AS production

USER root

# Otimizações de produção
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copiar aplicação
COPY --chown=appuser:appgroup . /var/www

# Instalar dependências do Composer (apenas produção)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Otimizar Laravel
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

USER appuser