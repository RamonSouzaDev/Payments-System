# 💸 Sistema de Processamento de Pagamentos - Asaas

## 📘 Sobre o Projeto

Este projeto implementa um sistema completo de processamento de pagamentos integrado com a **API do Asaas**. Os usuários podem escolher entre **Boleto**, **Cartão de Crédito** ou **PIX**, e recebem comprovantes ou instruções de pagamento adequadas.

---

## 🛠️ Tecnologias Utilizadas

- PHP 8.1
- Laravel 9.x
- MySQL 5.7
- Docker & Docker Compose
- Laravel Queue (Filas Assíncronas)
- Testes Automatizados com PHPUnit
- Enums Tipados do PHP 8.1
- Form Requests para Validação
- API Resources para Padronização de Respostas

---

## ✅ Requisitos

- Docker e Docker Compose instalados
- Conta no [Asaas Sandbox](https://www.asaas.com/) com API Key gerada

---

## ⚙️ Instalação e Configuração

### 🔄 Instalação Rápida (com Script)

```bash
git clone https://github.com/seu-usuario/payment-system.git
cd payment-system
bash ./setup.sh
```

O script realiza automaticamente:

- Verificação de requisitos
- Criação dos diretórios necessários
- Solicitação da API Key do Asaas
- Início dos containers Docker
- Instalação das dependências
- Configuração do banco de dados
- Execução dos testes

> Acesse o sistema em: [http://localhost:8000](http://localhost:8000)

---

### 🛠️ Instalação Manual

#### 1. Clone o repositório:

```bash
git clone https://github.com/seu-usuario/payment-system.git
cd payment-system
```

#### 2. Crie os diretórios necessários:

```bash
mkdir -p docker-compose/nginx
mkdir -p docker-compose/mysql
mkdir -p laravel_app
```

#### 3. Copie e edite o arquivo de ambiente:

```bash
cp .env.example .env.docker
# Edite o arquivo .env.docker e adicione sua API Key do Asaas
```

#### 4. Suba os containers:

```bash
docker-compose up -d
```

#### 5. Instale as dependências e configure o Laravel:

```bash
docker-compose exec app composer install
docker-compose exec app cp .env.example .env
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
```

#### 6. Instale as dependências do front-end:

```bash
docker-compose exec app npm install
docker-compose exec app npm run dev
```

#### 7. Configure permissões:

```bash
docker-compose exec app chmod -R 777 storage bootstrap/cache
```

#### 8. Inicie o processador de filas:

```bash
docker-compose exec app php artisan queue:work
```

---

## 🚀 Uso do Sistema

1. Acesse [http://localhost:8000](http://localhost:8000)
2. Escolha um método de pagamento: **Boleto**, **Cartão de Crédito** ou **PIX**
3. Preencha os dados do cliente
4. Clique em **Finalizar Pagamento**
5. Você será redirecionado para uma página de agradecimento com o comprovante ou QR Code

---

## 📁 Estrutura do Projeto

| Diretório/Arquivo           | Descrição                                                      |
|----------------------------|----------------------------------------------------------------|
| `app/Enums`                | Enums tipados para métodos de pagamento e status               |
| `app/Http/Controllers`     | Controladores principais da aplicação                          |
| `app/Http/Requests`        | Form Requests para validação de entrada                        |
| `app/Http/Resources`       | Resources para padronizar as respostas da API                  |
| `app/Models`               | Modelos Eloquent da aplicação                                  |
| `app/Repositories`         | Repositórios para abstração de acesso a dados                  |
| `app/Services`             | Camada de serviços para lógica de negócios                     |
| `app/Jobs`                 | Jobs para processamento assíncrono via fila                    |

---

## 🧪 Testes

O projeto inclui testes unitários e de funcionalidades. Para executá-los:

```bash
docker-compose exec app php artisan test
```

---

## 🧰 Comandos Úteis

| Ação                            | Comando                                                |
|---------------------------------|---------------------------------------------------------|
| Iniciar containers              | `docker-compose up -d`                                 |
| Parar containers                | `docker-compose down`                                  |
| Acessar o container da app      | `docker-compose exec app bash`                         |
| Executar migrations             | `docker-compose exec app php artisan migrate`          |
| Iniciar worker de filas         | `docker-compose exec app php artisan queue:work`       |
| Ver logs da aplicação           | `docker-compose exec app tail -f storage/logs/laravel.log` |

---

## 🔐 Obtenção da API Key do Asaas

1. Crie uma conta no [Asaas Sandbox](https://www.asaas.com/)
2. Vá até **Configurações da Conta > Integrações**
3. Gere sua **API Key**
4. Insira a chave no arquivo `.env.docker` na variável `ASAAS_API_KEY`

---

## 🤝 Contribuições

Contribuições são muito bem-vindas!  
Sinta-se à vontade para abrir **issues**, enviar **pull requests**, ou sugerir melhorias.

---

## ✅ Verificação de Requisitos

| Requisito                                              | Status | Observação                                                   |
|--------------------------------------------------------|--------|--------------------------------------------------------------|
| Desenvolvido em PHP/Laravel                           | ✅     | Laravel 9.x com PHP 8.1                                      |
| Tratamento de dados com Form Request                  | ✅     | Validação específica por método de pagamento                 |
| Respostas padronizadas com Resources                  | ✅     | Implementado para entidades principais                       |
| Integração com API Asaas padronizada                  | ✅     | Via `AsaasService`                                           |
| Processamento assíncrono (fila) de boletos            | ✅     | Implementado com `Jobs`                                      |
| Processamento assíncrono de cartão de crédito         | ✅     | Implementado com `Jobs`                                      |
| Processamento assíncrono de PIX                       | ✅     | Implementado com `Jobs`                                      |
| Link de boleto na tela de agradecimento               | ✅     | Exibido após pagamento                                       |
| QR Code PIX e código “copia e cola”                   | ✅     | Gerado e exibido                                             |
| Mensagens de erro amigáveis                           | ✅     | Tratamento robusto com fallback e logs                       |
| Front-end responsivo com Bootstrap                    | ✅     | Utilização do Bootstrap 5                                    |
| Boas práticas de programação                          | ✅     | SOLID, design patterns (Service, Repository), tipagem forte |
| Boas práticas de Git                                  | ✅     | Commits semânticos e estrutura organizada                    |
| Documentação clara e detalhada                        | ✅     | README.md + script de setup                                  |

---

## 🌟 Diferenciais

- ✅ **Enums Tipados** (PHP 8.1) para segurança e consistência
- ✅ **Processamento Assíncrono com Queues** (Jobs + Redis)
- ✅ **Docker** para ambiente isolado e portátil
- ✅ **Tratamento de Erros Robusto** com logs e mensagens amigáveis
- ✅ **Uso de Design Patterns** como Repository e Service

---

## 📬 Contato

Caso tenha dúvidas, sugestões ou queira contribuir, entre em contato pelo GitHub ou abra uma issue!

---
