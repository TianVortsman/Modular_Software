FROM php:8.1-apache

# Install PostgreSQL and other necessary extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Set permissions (optional)
RUN chown -R www-data:www-data /var/www/html
