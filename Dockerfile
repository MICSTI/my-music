FROM php:8.5-apache
RUN docker-php-ext-install pdo_mysql \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
WORKDIR /var/www/html
COPY . .
RUN chown -R www-data:www-data /var/www/html
CMD ["sh", "-c", "rm -f /etc/apache2/mods-enabled/mpm_* && a2enmod mpm_prefork && exec apache2-foreground"]