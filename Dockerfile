# ---- Stage 1: builder (composer + vite) ----
FROM php:8.2-fpm-bullseye AS builder
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev libicu-dev nodejs npm \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql opcache zip intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

# 1) Instala dependencias PHP SIN scripts (todavía no hay artisan)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# 2) Copia el resto del proyecto (aquí ya llega artisan)
COPY . .

# 3) Ahora sí, corre los scripts de composer/laravel y optimizaciones
RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true \
 && php artisan view:cache || true \
 && php artisan route:cache || true \
 && php artisan config:cache || true

# 4) Build de assets
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
