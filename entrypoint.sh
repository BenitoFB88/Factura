#!/bin/bash

# Comando para instalar las dependencias si no están instaladas
if [ ! -d "vendor" ]; then
    echo "No se encontraron las dependencias de Composer, instalándolas..."
    composer install
fi

# Asignar los permisos correctos a los directorios de almacenamiento y caché
chmod -R 777 /var/www/storage
chmod -R 777 /var/www/bootstrap/cache

# Ejecutar el contenedor de PHP-FPM (esto es lo que lanzará el contenedor al final)
exec "$@"
