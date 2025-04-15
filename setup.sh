#!/bin/bash

# Cores para saída no terminal
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}=== Iniciando setup do Sistema de Pagamentos Asaas ===${NC}"

# Verificar se o Docker está instalado
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Docker não encontrado. Por favor, instale o Docker e o Docker Compose antes de continuar.${NC}"
    exit 1
fi

# Verificar se o Docker Compose está instalado
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}Docker Compose não encontrado. Por favor, instale o Docker Compose antes de continuar.${NC}"
    exit 1
fi

# Passos de configuração
echo -e "${YELLOW}1. Criando diretórios necessários...${NC}"
mkdir -p docker-compose/nginx
mkdir -p docker-compose/mysql
mkdir -p laravel_app

# Verificar se .env.docker existe, se não, criar a partir do .env.example
if [ ! -f ".env.docker" ]; then
    echo -e "${YELLOW}2. Criando arquivo .env.docker...${NC}"
    cp .env.example .env.docker
    # Pedir a API Key do Asaas
    echo -e "${YELLOW}Por favor, insira sua API Key do Asaas Sandbox:${NC}"
    read asaas_key
    # Substituir no arquivo .env.docker
    sed -i "s/ASAAS_API_KEY=/ASAAS_API_KEY=$asaas_key/g" .env.docker
else
    echo -e "${GREEN}2. Arquivo .env.docker já existe.${NC}"
fi

echo -e "${YELLOW}3. Iniciando containers Docker...${NC}"
docker-compose down -v
docker-compose up -d

echo -e "${YELLOW}4. Aguardando containers iniciarem completamente...${NC}"
echo -e "${YELLOW}   Aguardando MySQL iniciar...${NC}"
# Aguardar MySQL ficar pronto para conexões
for i in {1..30}; do
    if docker-compose exec db mysqladmin ping -h localhost -u root -proot --silent; then
        echo -e "${GREEN}   MySQL está pronto!${NC}"
        break
    fi
    echo -e "${YELLOW}   Tentativa $i: MySQL ainda não está pronto, aguardando...${NC}"
    sleep 2
done

echo -e "${YELLOW}5. Instalando dependências do Laravel...${NC}"
docker-compose exec app composer install

echo -e "${YELLOW}6. Copiando arquivo .env...${NC}"
docker-compose exec app cp .env.example .env

echo -e "${YELLOW}7. Atualizando configurações do .env...${NC}"
# Atualizar configurações do banco de dados
docker-compose exec app sed -i "s/DB_HOST=127.0.0.1/DB_HOST=db/g" .env
docker-compose exec app sed -i "s/DB_DATABASE=laravel/DB_DATABASE=payment_system/g" .env
docker-compose exec app sed -i "s/DB_USERNAME=root/DB_USERNAME=laravel/g" .env
docker-compose exec app sed -i "s/DB_PASSWORD=/DB_PASSWORD=root/g" .env
# Atualizar configuração da fila
docker-compose exec app sed -i "s/QUEUE_CONNECTION=sync/QUEUE_CONNECTION=database/g" .env

echo -e "${YELLOW}8. Gerando chave da aplicação...${NC}"
docker-compose exec app php artisan key:generate

echo -e "${YELLOW}9. Criando banco de dados, se não existir...${NC}"
docker-compose exec db mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS payment_system;"
docker-compose exec db mysql -u root -proot -e "GRANT ALL PRIVILEGES ON payment_system.* TO 'laravel'@'%';"
docker-compose exec db mysql -u root -proot -e "FLUSH PRIVILEGES;"

echo -e "${YELLOW}10. Executando migrações do banco de dados...${NC}"
docker-compose exec app php artisan migrate --seed --force

echo -e "${YELLOW}11. Criando tabelas de filas...${NC}"
docker-compose exec app php artisan queue:table
docker-compose exec app php artisan queue:failed-table
docker-compose exec app php artisan migrate --force

echo -e "${YELLOW}12. Instalando dependências do front-end...${NC}"
docker-compose exec app npm install
docker-compose exec app npm run dev &  # Executar em segundo plano

echo -e "${YELLOW}13. Configurando permissões...${NC}"
docker-compose exec app chmod -R 777 storage bootstrap/cache

# Criar banco de dados de teste e executar migrations
echo -e "${YELLOW}14. Configurando banco de dados de teste...${NC}"
docker-compose exec db mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS payment_system_test;"
docker-compose exec db mysql -u root -proot -e "GRANT ALL PRIVILEGES ON payment_system_test.* TO 'laravel'@'%';"
docker-compose exec db mysql -u root -proot -e "FLUSH PRIVILEGES;"

# Configurar arquivo de teste
echo -e "${YELLOW}15. Configurando arquivo phpunit.xml para testes...${NC}"
docker-compose exec app sed -i "s/<env name=\"DB_CONNECTION\" value=\"sqlite\"\/>/<env name=\"DB_CONNECTION\" value=\"mysql\"\/>/g" phpunit.xml
docker-compose exec app sed -i "s/<env name=\"DB_DATABASE\" value=\":memory:\"\/>/<env name=\"DB_DATABASE\" value=\"payment_system_test\"\/>\n        <env name=\"DB_HOST\" value=\"db\"\/>/g" phpunit.xml

# Executar migrações no banco de testes
echo -e "${YELLOW}16. Preparando banco de dados de teste...${NC}"
docker-compose exec app php artisan migrate --env=testing --force

# Executar testes unitários
echo -e "${YELLOW}17. Executando testes unitários...${NC}"
docker-compose exec app php artisan test

# Iniciar o worker de filas em segundo plano
echo -e "${YELLOW}18. Iniciando processador de filas em segundo plano...${NC}"
docker-compose exec -d app php artisan queue:work

echo -e "${GREEN}=== Setup concluído com sucesso! ===${NC}"
echo -e "${GREEN}O sistema está disponível em: http://localhost:8000${NC}"
echo -e "${YELLOW}Processador de filas está rodando em segundo plano${NC}"
echo -e "${YELLOW}Para verificar logs, use: docker-compose logs -f app${NC}"