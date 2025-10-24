# ====== Etapa 1: Composer ======
FROM php:8.2-fpm-bullseye AS composer_build

# SO + libs para extensiones
RUN apt-get update && apt-get install -y \
    git unzip \
    libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_mysql zip exif gd mbstring bcmath \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

# Instala dependencias (prod)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader

# Copia código
COPY . .
RUN php artisan config:clear && php artisan route:clear && php artisan view:clear

# ====== Etapa 2: Node (Vite) ======
FROM node:20-alpine AS node_build
WORKDIR /app
COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* ./
RUN npm ci || npm i
COPY . .
RUN npm run build

# ====== Etapa 3: Runtime (nginx + php-fpm) ======
FROM php:8.2-fpm-bullseye AS runtime

ENV APP_ENV=production \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_MEMORY_LIMIT=512M \
    PORT=8080 \
    TZ=America/Bogota

RUN apt-get update && apt-get install -y \
    nginx supervisor ca-certificates tzdata \
    libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_mysql zip exif gd mbstring bcmath \
 && rm -rf /var/lib/apt/lists/*

# Opcache y PHP ini
RUN { echo "opcache.enable=1"; echo "opcache.enable_cli=0"; echo "opcache.memory_consumption=256"; echo "opcache.interned_strings_buffer=16"; echo "opcache.max_accelerated_files=20000"; echo "opcache.validate_timestamps=${PHP_OPCACHE_VALIDATE_TIMESTAMPS}"; echo "opcache.save_comments=1"; } > /usr/local/etc/php/conf.d/opcache.ini \
 && { echo "memory_limit=${PHP_MEMORY_LIMIT}"; echo "expose_php=0"; echo "post_max_size=64M"; echo "upload_max_filesize=64M"; } > /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

# Código + vendor desde composer_build
COPY --from=composer_build /var/www/html /var/www/html

# Assets de Vite
COPY --from=node_build /app/public/build /var/www/html/public/build

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Nginx + Supervisor
COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Evita fallar si no usas storage público
RUN php artisan storage:link || true

EXPOSE 8080
CMD ["/usr/bin/supervisord","-n","-c","/etc/supervisor/conf.d/supervisord.conf"]
