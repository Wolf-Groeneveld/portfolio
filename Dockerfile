FROM php:8.3-apache

RUN a2enmod rewrite \
 && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
 && docker-php-ext-install pdo pdo_sqlite pdo_mysql

COPY . /var/www/html/
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh \
 && chown -R www-data:www-data /var/www/html

ENTRYPOINT ["/entrypoint.sh"]
