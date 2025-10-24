# ====== Etapa 1: Composer (dependencias PHP, sin dev) ======
FROM php:8.2-fpm-bullseye AS composer_build

# Extensiones y utilidades para compilar librerías de PHP
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql zip exif \
    && rm -rf /var/lib/apt/lists/*


COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json composer.lock* ./

RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader

COPY . .

RUN php artisan vendor:publish --tag=laravel-assets --force || true \
 && php artisan config:clear \
 && php artisan route:clear \
 && php artisan view:clear

# ====== Etapa 2: Node (build de assets con Vite) ======
FROM node:20-alpine AS node_build
WORKDIR /app


COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* ./

RUN npm ci || npm i

COPY . .
RUN npm run build

# ====== Etapa 3: Runtime (nginx + php-fpm + supervisor) ======
FROM php:8.2-fpm-bullseye AS runtime


ENV APP_ENV=production \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_MEMORY_LIMIT=512M \
    PORT=8080 \
    TZ=America/Bogota

# Paquetes del sistema + nginx + supervisor + zona horaria
RUN apt-get update && apt-get install -y \
    nginx supervisor ca-certificates tzdata \
    libzip-dev libpng-dev libonig-dev libxml2-dev \
    && echo "$TZ" > /etc/timezone && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && docker-php-ext-install pdo_mysql zip exif \
    && rm -rf /var/lib/apt/lists/*

# Opcache recomendado en producción
RUN { \
      echo "opcache.enable=1"; \
      echo "opcache.enable_cli=0"; \
      echo "opcache.memory_consumption=256"; \
      echo "opcache.interned_strings_buffer=16"; \
      echo "opcache.max_accelerated_files=20000"; \
      echo "opcache.validate_timestamps=${PHP_OPCACHE_VALIDATE_TIMESTAMPS}"; \
      echo "opcache.save_comments=1"; \
    } > /usr/local/etc/php/conf.d/opcache.ini \
 && { \
      echo "memory_limit=${PHP_MEMORY_LIMIT}"; \
      echo "expose_php=0"; \
      echo "post_max_size=64M"; \
      echo "upload_max_filesize=64M"; \
    } > /usr/local/etc/php/conf.d/custom.ini


WORKDIR /var/www/html

COPY --from=composer_build /var/www/html /var/www/html


COPY --from=node_build /app/public/build /var/www/html/public/build

# Permisos para cache y storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Nginx y Supervisor configs
COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Enlace público de storage (si tu app lo usa). Si falla, no romper build.
RUN php artisan storage:link || true

# Cloud Run escucha en $PORT; Nginx queda en 8080 (el default de Cloud Run es 8080).
EXPOSE 8080

# Arranca Nginx + PHP-FPM via Supervisor
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
