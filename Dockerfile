# ---- Stage 1: builder (composer + vite) ----
FROM php:8.2-fpm-bullseye AS builder
ENV COMPOSER_ALLOW_SUPERUSER=1

# Paquetes del sistema + extensiones PHP
RUN apt-get update && apt-get install -y \
    git curl ca-certificates gnupg \
    unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev libicu-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql opcache zip intl

# >>> Instalar Node.js 20 LTS (NodeSource)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
 && apt-get install -y nodejs \
 && node -v && npm -v

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Composer sin scripts (aún no existe artisan)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copiar código
COPY . .

# Scripts/optimizaciones Laravel (tolerantes si faltan vars en build)
RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true \
 && php artisan view:cache || true \
 && php artisan route:cache || true \
 && php artisan config:cache || true

# Build front (usa lock si existe)
RUN npm ci || npm install \
 && npm run build

# ---- Stage 2: runtime (nginx + php-fpm) ----
FROM php:8.2-fpm-bullseye AS runtime

RUN apt-get update && apt-get install -y nginx supervisor libzip-dev libpng-dev \
    libjpeg62-turbo-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql opcache zip intl \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY --from=builder /var/www/html /var/www/html

RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

COPY ./deploy/nginx.conf /etc/nginx/nginx.conf
COPY ./deploy/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
