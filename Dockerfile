FROM php:8.2.12-apache

RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!:80>!:8080>!' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!<Directory /var/www/>!<Directory ${APACHE_DOCUMENT_ROOT}>!' /etc/apache2/apache2.conf

# Cloud Run usa 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf
EXPOSE 8080

WORKDIR /var/www/html
COPY . .

# Composer (prod)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# (Opcional) Vite: npm o yarn
# RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs
# RUN npm ci && npm run build || true
#   —o—
RUN npm i -g yarn && yarn install && yarn build || true

RUN chown -R www-data:www-data storage bootstrap/cache
CMD ["apache2-foreground"]
