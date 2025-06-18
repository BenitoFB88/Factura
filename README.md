# Proyecto Laravel con Docker

Este repositorio contiene un entorno completo para desarrollar una aplicación Laravel 5.7 utilizando Docker, MySQL y Nginx, junto a un frontend (opcional) en Vue.js.

## Requisitos
- [Docker Desktop](https://www.docker.com/products/docker-desktop) instalado
- Git para clonar el repositorio

---

## Instalación y Configuración

### 1. Clonar el repositorio

```bash
git clone https://github.com/BenitoFB88/Factura.git
cd Factura

estructura :
Factura/
├── backend/              # Laravel 5.7
├── frontend/             # (Opcional) Vue.js
├── nginx/
│   └── default.conf      # Configuración de Nginx
├── Dockerfile
├── docker-compose.yml
├── .gitignore
└── README.md


3. Configurar Laravel
Dentro de backend, copiar .env.example como .env:

bash
Copiar
Editar
cd backend
cp .env.example .env
Modificar los valores de conexión a la base de datos:

env
Copiar
Editar
DB_CONNECTION=mysql
DB_HOST=mysql_db
DB_PORT=3306
DB_DATABASE=db_facturas
DB_USERNAME=laraveluser
DB_PASSWORD=laravelpass
4. Generar clave de aplicación Laravel
bash
Copiar
Editar
docker-compose exec app php artisan key:generate
Levantar contenedores
Desde la raíz del proyecto:

bash
Copiar
Editar
docker-compose up -d --build
Esto inicia:

app: Contenedor PHP con Laravel

db: Contenedor MySQL

nginx: Servidor web

Acceso
Laravel desde: http://localhost

Base de datos desde: localhost:3306 con los datos configurados

Comandos útiles
Ejecutar migraciones:

bash
Copiar
Editar
docker-compose exec app php artisan migrate
Instalar dependencias:

bash
Copiar
Editar
docker-compose exec app composer install
Permisos:

bash
Copiar
Editar
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
Notas
Laravel vive en la carpeta backend/

Puedes agregar un frontend Vue en frontend/ y conectarlo al backend (http://localhost)

Para ingresar al contenedor de laravel
docker exec -it laravel_app bash

Instalar Laravel Passport
composer require laravel/passport "^7.0"

php artisan vendor:publish --tag=passport-migrations
php artisan migrate

Para crear usuario de passport dentro del contenedor de docker
php artisan passport:client --password

Copiar el id y la password entregadas en el archivo .env
PASSPORT_CLIENT_ID=X
PASSPORT_CLIENT_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

ejecutar php artisan serve --port=8081

y luego correo en la raiz del front 'npm install'
y por ultimo 'npm run serve'

en el front se puede loguear con el usuario= 'juan@example.com' pass= '123456'







