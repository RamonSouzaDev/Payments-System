# Sistema de Processamento de Pagamentos - Asaas

## Sobre o Projeto

Este projeto é um sistema de processamento de pagamentos integrado ao ambiente de homologação (sandbox) do Asaas. Permite que os usuários selecionem entre diferentes métodos de pagamento (Boleto, Cartão de Crédito ou PIX) e realizem pagamentos.

## Tecnologias Utilizadas

- PHP 8.1
- Laravel 9.x
- MySQL 5.7+
- Bootstrap 5
- jQuery

## Características

- Processamento de pagamentos com boleto, cartão de crédito e PIX.
- Exibição de boleto para download/impressão.
- Exibição de QR Code para pagamentos PIX.
- Persistência de dados em banco de dados MySQL.
- Tratamento de erros de pagamento com mensagens amigáveis.
- Testes unitários.
- CI/CD com GitHub Actions.

## Arquitetura e Padrões de Projeto

O projeto segue os seguintes padrões e princípios de design:

1. **Padrão Repository**: Abstração da camada de acesso a dados.
2. **Padrão Service**: Encapsulamento da lógica de negócios.
3. **Princípio Aberto/Fechado**: Através de interfaces e implementações.
4. **Injeção de Dependência**: Usando o container IoC do Laravel.
5. **Resources do Laravel**: Para padronização das respostas da API.

## Requisitos

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Extensões PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- Node.js e NPM (para compilar assets)

## Instalação e Configuração

1. Clone o repositório:
```bash
git clone https://github.com/seu-usuario/payment-system.git
cd payment-system
```

2. Instale as dependências:
```bash
composer install
npm install && npm run dev
```

3. Copie o arquivo de ambiente e gere a chave da aplicação:
```bash
cp .env.example .env
php artisan key:generate
```

4. Defina suas configurações no arquivo `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=payment_system
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

ASAAS_API_KEY=sua_api_key_do_asaas
ASAAS_API_URL=https://sandbox.asaas.com/api/v3/
```

5. Crie o banco de dados MySQL:
```sql
CREATE DATABASE payment_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

6. Execute as migrações:
```bash
php artisan migrate
```

7. Inicie o servidor de desenvolvimento:
```bash
php artisan serve
```

8. Acesse a aplicação em `http://localhost:8000`

## Obtenção de Credenciais do Asaas

1. Crie uma conta no [Asaas Sandbox](https://sandbox.asaas.com/)
2. Acesse a seção de Configuração de Conta -> Integrações
3. Obtenha a API Key e configure no arquivo `.env`

## Testes

Para executar os testes:

```bash
php artisan test
```

Para verificar a cobertura de testes:

```bash
XDEBUG_MODE=coverage php artisan test --coverage
```

## CI/CD

O projeto inclui uma configuração de GitHub Actions para CI/CD que:

1. Executa testes automáticos em cada push ou pull request.
2. Verifica a cobertura de código.
3. Realiza deploy automático para o ambiente de produção em pushes para a branch principal.

## Estrutura do Projeto

- `app/Repositories`: Contém as interfaces e implementações dos repositórios.
- `app/Services`: Contém as interfaces e implementações dos serviços.
- `app/Http/Controllers`: Controladores da aplicação.
- `app/Http/Requests`: Classes de validação de requisições.
- `app/Http/Resources`: Classes para formatação de respostas.
- `app/Models`: Modelos do Eloquent.
- `tests`: Testes unitários e de integração.

## Autor

Seu Nome - [seu-email@exemplo.com](mailto:seu-email@exemplo.com)

## Licença

Este projeto está licenciado sob a [Licença MIT](LICENSE).