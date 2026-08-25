FROM php:8.3-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends libsqlite3-dev \
 && rm -rf /var/lib/apt/lists/* \
 && a2enmod rewrite \
 && docker-php-ext-install pdo_sqlite pdo_mysql \
 && printf '<Directory /var/www/html>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
    > /etc/apache2/conf-available/portfolio.conf \
 && a2enconf portfolio

COPY . /var/www/html/
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh \
 && chown -R www-data:www-data /var/www/html

ENTRYPOINT ["/entrypoint.sh"]
