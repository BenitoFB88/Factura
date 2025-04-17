# Usamos la imagen base de PHP con FPM
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

# Copiamos el archivo entrypoint.sh al contenedor
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

# Damos permisos de ejecución a entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Definimos el directorio de trabajo
WORKDIR /var/www

# Establecemos el script entrypoint.sh como el punto de entrada al contenedor
ENTRYPOINT ["entrypoint.sh"]

# Iniciamos PHP-FPM
CMD ["php-fpm"]
