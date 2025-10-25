# ---- Stage 1: builder (composer + vite) ----
FROM php:8.2-fpm-bullseye AS builder

# System deps
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev libicu-dev \
    libpq-dev libjpeg-dev libfreetype6-dev libssl-dev nodejs npm \
    && docker-php-ext-install pdo pdo_mysql opcache zip intl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# App code
WORKDIR /var/www/html
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copia el resto del proyecto
COPY . .

# Build de assets (usa tu script/stack de Vite)
RUN npm ci && npm run build

# Optimización de Laravel
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# ---- Stage 2: runtime (nginx + php-fpm) ----
FROM nginx:1.27-alpine AS nginxbase
# Nginx base separado para copiar config al final

FROM php:8.2-fpm-alpine AS runtime

# Extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql opcache

# Opcional: ajustes de PHP para producción
RUN { \
  echo "opcache.enable=1"; \
  echo "opcache.validate_timestamps=0"; \
  echo "opcache.jit_buffer_size=100M"; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Directorio app
WORKDIR /var/www/html

# Copia desde builder: vendor, public/build y app
COPY --from=builder /var/www/html /var/www/html

# Permisos de storage y cache
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Nginx + supervisord (para correr nginx y php-fpm en el mismo contenedor)
RUN apk add --no-cache nginx supervisor

# Nginx conf
COPY ./deploy/nginx.conf /etc/nginx/nginx.conf

# Supervisor conf
COPY ./deploy/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Exponer puerto para Cloud Run
EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
