FROM php:8.2.12-apache

# 1) Apache: rewrite + docroot en /public
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!:80>!:8080>!' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!<Directory /var/www/>!<Directory ${APACHE_DOCUMENT_ROOT}>!' /etc/apache2/apache2.conf

# 2) Cloud Run usa 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf
EXPOSE 8080

# 3) Dependencias del sistema para extensiones PHP
RUN apt-get update && apt-get install -y \
    git unzip curl \
    libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libxml2-dev \
 && rm -rf /var/lib/apt/lists/*

# 4) Extensiones PHP (orden recomendado)
#    - zip (Spreadsheet lo usa)
#    - gd (con soporte jpeg/freetype)
#    - mbstring, xml
#    - pdo_mysql
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) \
      pdo pdo_mysql zip gd mbstring xml

WORKDIR /var/www/html
COPY . .

# 5) Composer (prod)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 6) (Opcional) Vite. Si usas npm:
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
&& apt-get update && apt-get install -y nodejs \
&& npm ci && npm run build || true
#  # O Yarn:
# RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
#  && apt-get update && apt-get install -y nodejs \
#  && npm i -g yarn \
#  && yarn install && yarn build || true

# 7) Permisos Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["apache2-foreground"]
