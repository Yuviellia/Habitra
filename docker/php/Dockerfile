FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    librabbitmq-dev \
    libssl-dev \
    libzip-dev \
    unzip \
    git \
 && docker-php-ext-install pdo pdo_pgsql bcmath zip sockets

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY ../.. .

RUN composer install
