FROM php:8.1-apache

# Enable PHP extensions needed for MySQL
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite (useful for clean URLs)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

COPY docker/web-entrypoint.sh /usr/local/bin/web-entrypoint.sh
RUN chmod +x /usr/local/bin/web-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/web-entrypoint.sh"]
CMD ["apache2-foreground"]
