# ---- Stage 1: builder (composer + vite) ----
FROM php:8.2-fpm-bullseye AS builder
ENV COMPOSER_ALLOW_SUPERUSER=1

# Paquetes base + extensiones necesarias para compilar PHP
RUN apt-get update && apt-get install -y \
    git curl ca-certificates gnupg unzip \
    libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev libicu-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" gd pdo pdo_mysql opcache zip intl

# Node 20 LTS (para Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
 && apt-get install -y nodejs \
 && node -v && npm -v

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 1) Instalar dependencias PHP sin ejecutar scripts (aún no existe artisan)
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# 2) Copiar el resto del código
COPY . .

# 3) Descubrir paquetes y optimizar autoload (sin cachear config/route en build)
RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true \
 && php artisan view:cache || true \
 && rm -f bootstrap/cache/config.php bootstrap/cache/routes-*.php || true

# 4) Build de assets (usa lock si existe)
RUN npm ci || npm install \
 && npm run build

# ---- Stage 2: runtime (nginx + php-fpm) ----
FROM php:8.2-fpm-bullseye AS runtime

# Paquetes del sistema para Nginx/Supervisor y dependencias de extensiones
RUN apt-get update && apt-get install -y --no-install-recommends \
      nginx supervisor pkg-config \
      libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" gd pdo pdo_mysql opcache zip intl \
 && rm -rf /var/lib/apt/lists/*

# Forzar PHP-FPM a escuchar en 127.0.0.1:9000 (y no en socket)
RUN { \
  echo '[global]'; \
  echo 'daemonize = no'; \
  echo; \
  echo '[www]'; \
  echo 'listen = 127.0.0.1:9000'; \
  echo 'pm = dynamic'; \
  echo 'pm.max_children = 8'; \
  echo 'pm.start_servers = 2'; \
  echo 'pm.min_spare_servers = 2'; \
  echo 'pm.max_spare_servers = 4'; \
} > /usr/local/etc/php-fpm.d/zz-listen.conf

WORKDIR /var/www/html

# Copiar artefactos construidos (app + vendor + public/build)
COPY --from=builder /var/www/html /var/www/html

# Crear el symlink public/storage -> storage/app/public durante la construcción
RUN php artisan storage:link || true

# Permisos para Laravel
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Config de Nginx y Supervisor
COPY ./deploy/nginx.conf /etc/nginx/nginx.conf
COPY ./deploy/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Puerto que usará Cloud Run
EXPOSE 8080

# Iniciar php-fpm y nginx
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
