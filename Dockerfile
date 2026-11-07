FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html

WORKDIR /var/www

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
