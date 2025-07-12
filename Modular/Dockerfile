FROM php:8.1-apache

# Install PostgreSQL and other necessary extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql

# Install Node.js and npm
RUN apt-get update && apt-get install -y nodejs npm

# Install zip extension
RUN apt-get update && apt-get install -y libzip-dev zip && docker-php-ext-install zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy custom php.ini
COPY php.ini /usr/local/etc/php/conf.d/custom-php.ini

# Set working directory
WORKDIR /var/www/html

# Create logs directory and set permissions
RUN mkdir -p storage/logs && chown -R www-data:www-data storage

# Create Uploads directory and set permissions
RUN mkdir -p /var/www/html/Uploads && chmod -R 777 /var/www/html/Uploads

# Set permissions (optional)
RUN chown -R www-data:www-data /var/www/html
