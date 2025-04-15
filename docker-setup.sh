#!/bin/bash

# Criar diretórios necessários
mkdir -p docker-compose/nginx
mkdir -p docker-compose/mysql
mkdir -p laravel_app

# Parar containers anteriores
docker-compose down -v

# Iniciar containers Docker
docker-compose up -d

# Criar projeto Laravel
docker-compose exec app composer create-project --prefer-dist laravel/laravel .

# Configurar permissões
docker-compose exec app chown -R laravel:www-data /var/www/storage
docker-compose exec app chown -R laravel:www-data /var/www/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/storage
docker-compose exec app chmod -R 775 /var/www/bootstrap/cache

# Instalar dependências
docker-compose exec app composer require guzzlehttp/guzzle
docker-compose exec app composer require laravel/ui

# Instalar Bootstrap e autenticação
docker-compose exec app php artisan ui bootstrap --auth
docker-compose exec app npm install
docker-compose exec app npm run dev

echo "Ambiente Laravel configurado com sucesso!"
echo "Acesse o aplicativo em: http://localhost:8000"