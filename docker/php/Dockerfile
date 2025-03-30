FROM php:8.2-fpm

# PostgreSQL PDO
RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Working directory
WORKDIR /var/www/html

# Copy the local Symfony application into the container
COPY ../.. .

# Dependencies
RUN composer install

