# ---- Stage 1: builder (composer + vite) ----
FROM php:8.2-fpm-bullseye AS builder

ENV COMPOSER_ALLOW_SUPERUSER=1

# System deps para PHP y build de extensiones
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev libicu-dev \
    nodejs npm \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql opcache zip intl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Instalar dependencias PHP (ya con gd cargada)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copiar el resto del código
COPY . .

# Build de assets
RUN npm ci && npm run build

# Optimizaciones Laravel
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# ---- Stage 2: runtime (nginx + php-fpm) ----
FROM php:8.2-fpm-bullseye AS runtime

# Extensiones necesarias también en runtime
RUN apt-get update && apt-get install -y \
      nginx supervisor libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql opcache zip intl \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copia todo desde el builder (incluye vendor y public/build)
COPY --from=builder /var/www/html /var/www/html

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Nginx & Supervisord
COPY ./deploy/nginx.conf /etc/nginx/nginx.conf
COPY ./deploy/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
