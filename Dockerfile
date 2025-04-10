FROM php:7.2-fpm

# Instalamos extensiones necesarias
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl

# Instalamos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
