FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpng-dev libzip-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql gd zip xml \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html

WORKDIR /var/www

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json /var/www/composer.json
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

COPY src/ /var/www/html/

RUN mkdir -p /var/www/server/libs/tcpdf_fonts \
    && chown -R www-data:www-data /var/www/server

RUN printf '%s\n' \
    '<Directory /var/www/html>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' \
    > /etc/apache2/conf-available/sguvt-events.conf \
    && a2enconf sguvt-events

EXPOSE 80
