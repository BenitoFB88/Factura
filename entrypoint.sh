#!/bin/bash

set -e

cd /var/www

# Instalar dependencias si no están
if [ ! -d "vendor" ]; then
    echo "No se encontraron las dependencias de Composer, instalándolas..."
    composer install --no-interaction --prefer-dist
fi

# Asignar permisos
chmod -R 777 /var/www/storage
chmod -R 777 /var/www/bootstrap/cache

# Esperar que la base de datos esté lista
echo "Esperando a la base de datos..."
until php artisan migrate:status > /dev/null 2>&1; do
  sleep 2
done

# Ejecutar migraciones
php artisan migrate --force

# Instalar Passport si no está instalado
php artisan passport:install --force

# Crear cliente Password Grant manualmente solo si no existe en .env
if ! grep -q PASSPORT_PASSWORD_CLIENT_ID .env; then
  echo "Creando cliente Password Grant para Passport..."

  PASSWORD_CLIENT=$(php artisan passport:client --password --name="Laravel57 Password Client" --provider=users --no-interaction)

  CLIENT_ID=$(echo "$PASSWORD_CLIENT" | grep "Client ID:" | awk '{print $3}')
  CLIENT_SECRET=$(echo "$PASSWORD_CLIENT" | grep "Client secret:" | awk '{print $3}')

  # Guardar en .env si existe
  if [ -f .env ]; then
    sed -i "/^PASSPORT_PASSWORD_CLIENT_ID=/d" .env
    sed -i "/^PASSPORT_PASSWORD_CLIENT_SECRET=/d" .env
    echo "PASSPORT_PASSWORD_CLIENT_ID=${CLIENT_ID}" >> .env
    echo "PASSPORT_PASSWORD_CLIENT_SECRET=${CLIENT_SECRET}" >> .env
  fi
else
  echo "Cliente Password Grant para Passport ya existe en .env"
fi

# Ejecutar el comando original (php-fpm o lo que sea)
exec "$@"
