<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Logo do Laravel"></a></p>

# 📊 Sistema de Gerenciamento de Vendas (Sales Management)

Sistema completo de gerenciamento de vendas e comissões, construído com **Laravel 12** e **Vue.js 3**, com funcionalidades de:
- 📈 Dashboard com estatísticas e gráficos comparativos
- 👥 Gerenciamento de vendedores
- 💰 Registro e acompanhamento de vendas
- 📧 Envio automático de relatórios por e-mail
- 🔐 Autenticação e controle de acesso por perfil

---

## 📋 Índice

- [Pré-requisitos](#-pré-requisitos)
- [Instalação e Execução](#-instalação-e-execução)
- [Credenciais de Teste](#-credenciais-de-teste)
- [Executando Seeders](#-executando-seeders)
- [Documentação Adicional](#-documentação-adicional)
- [API - Endpoints e Exemplos](#-api---endpoints-e-exemplos)
- [Serviços Disponíveis](#-serviços-disponíveis)
- [Sistema de E-mails](#-sistema-de-e-mails)
- [Comandos Úteis](#-comandos-úteis)

---

## 🔧 Pré-requisitos

Para executar este projeto, você precisará ter instalado:
- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)

> **Nota:** O PHP, Composer e Node.js não são necessários na sua máquina, pois o ambiente é totalmente containerizado.

---

## 🚀 Instalação e Execução

### Passo 1: Clonar o Repositório

```bash
git clone https://github.com/guilherf13/sales-management.git
cd sales-management
```

### Passo 2: Configurar Variáveis de Ambiente

#### Criar arquivo .env do Laravel
```bash
cp .env.example .env
```

#### Verificar configurações do banco de dados no .env
As configurações padrão já estão corretas para o Docker:
```dotenv
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=user_password

# Redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis

# E-mail (Mailpit)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
```

### Passo 3: Ajustar Permissões (Opcional)

Para evitar problemas de permissão entre seu sistema e o container, ajuste o UID/GID no `docker/php/Dockerfile`:

**Descobrir seu UID e GID:**
    ```bash
    echo "Seu UID é: $(id -u)"
    echo "Seu GID é: $(id -g)"
    ```

**Editar `docker/php/Dockerfile`:**
```dockerfile
ARG UID=1000  # Substitua pelo seu UID
ARG GID=1000  # Substitua pelo seu GID
```

### Passo 4: Construir e Iniciar os Containers

```bash
docker-compose up -d --build
```

### Passo 5: Instalar Dependências e Configurar Laravel

```bash
# Instalar dependências do Composer
docker-compose exec app composer install

# Gerar chave da aplicação
docker-compose exec app php artisan key:generate

# Executar migrations e seeders
docker-compose exec app php artisan migrate:fresh --seed
```

### Passo 6: Iniciar o Worker de Filas (Para E-mails)

```bash
docker-compose exec -d app php artisan queue:listen --tries=3 --timeout=60
```

---

## ✅ Aplicação Pronta!

Acesse:
- **Frontend (Vue.js):** 👉 **[http://localhost:5173](http://localhost:5173)**
- **Backend (API):** 👉 **[http://localhost:8080/api](http://localhost:8080/api)**
- **Mailpit (E-mails):** 👉 **[http://localhost:8025](http://localhost:8025)**

---

## 🔐 Credenciais de Teste

Faça login em: **[http://localhost:5173/login](http://localhost:5173/login)**

| Perfil | E-mail | Senha | Permissões |
|--------|--------|-------|------------|
| **Gestor (Admin)** | `admin@test.com` | `password` | Acesso total + recebe relatórios diários |
| **Vendedor** | `vendedor@test.com` | `password` | Gerenciar vendas + recebe comissões |

---

## 🌱 Executando Seeders

### Recriar todo o banco de dados com dados de teste:
```bash
docker-compose exec app php artisan migrate:fresh --seed
```

Este comando irá:
- Limpar todas as tabelas
- Recriar a estrutura do banco
- Popular com dados de teste:
  - 2 usuários (admin + vendedor)
  - 100 vendedores
  - 100 vendas

### Executar seeders específicos:
```bash
# Apenas usuários
docker-compose exec app php artisan db:seed --class=UserSeeder

# Apenas vendedores
docker-compose exec app php artisan db:seed --class=SellerSeeder

# Apenas vendas
docker-compose exec app php artisan db:seed --class=SaleSeeder
```

---

## 📚 Documentação Adicional

Este projeto contém arquivos de documentação detalhada:

| Arquivo | Descrição |
|---------|-----------|
| **[CONFIGURACAO_CONCLUIDA.md](docs/CONFIGURACAO_CONCLUIDA.md)** | Configuração completa de Mailpit e Redis, comandos úteis |
| **[SISTEMA_EMAILS.md](docs/SISTEMA_EMAILS.md)** | Sistema de e-mails automáticos para administradores e vendedores |
| **[COMO_TESTAR_EMAILS.md](docs/COMO_TESTAR_EMAILS.md)** | Guia prático para testar envio de e-mails |

---

## 📡 API - Endpoints e Exemplos

A API está protegida com **Laravel Sanctum**. Para acessar endpoints protegidos, inclua o token no header:
```
Authorization: Bearer {seu_token}
```

### 🔐 Autenticação

#### **POST** `/api/login`
Autenticar usuário e obter token de acesso.

**Request:**
```json
{
  "email": "admin@test.com",
  "password": "password"
}
```

**Response (200):**
```json
{
  "access_token": "1|abc123def456...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Administrador",
    "email": "admin@test.com",
    "perfil": "Gestor",
    "created_at": "2025-10-06T10:00:00.000000Z",
    "updated_at": "2025-10-06T10:00:00.000000Z"
  }
}
```

**Response (401 - Erro):**
```json
{
  "message": "Credenciais inválidas"
}
```

---

#### **POST** `/api/register`
Registrar novo usuário (sempre como perfil "Seller").

**Request:**
```json
{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "senha123",
  "password_confirmation": "senha123"
}
```

**Response (201):**
```json
{
  "access_token": "2|xyz789...",
  "token_type": "Bearer",
  "user": {
    "id": 2,
    "name": "João Silva",
    "email": "joao@example.com",
    "perfil": "Seller",
    "created_at": "2025-10-06T10:05:00.000000Z",
    "updated_at": "2025-10-06T10:05:00.000000Z"
  },
  "message": "Usuário registrado com sucesso"
}
```

---

#### **POST** `/api/logout`
Desconectar usuário (requer autenticação).

**Headers:**
```
Authorization: Bearer {seu_token}
```

**Response (200):**
```json
{
  "message": "Logout realizado com sucesso"
}
```

---

### 👥 Vendedores (Sellers)

#### **GET** `/api/sellers?page=1&per_page=20&search=maria`
Listar vendedores com paginação e busca.

**Query Parameters:**
- `page` (opcional): Número da página (padrão: 1)
- `per_page` (opcional): Itens por página (padrão: 20)
- `search` (opcional): Buscar por nome ou email

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Maria Santos",
      "email": "maria@example.com",
      "sales_count": 15,
      "total_commission": 1234.56,
      "created_at": "2025-09-01T08:00:00.000000Z",
      "updated_at": "2025-10-06T10:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Maria Silva",
      "email": "maria.silva@example.com",
      "sales_count": 8,
      "total_commission": 678.90,
      "created_at": "2025-09-05T09:30:00.000000Z",
      "updated_at": "2025-10-05T15:20:00.000000Z"
    }
  ],
  "current_page": 1,
  "last_page": 5,
  "per_page": 20,
  "total": 100
}
```

---

#### **POST** `/api/sellers`
Criar novo vendedor.

**Request:**
```json
{
  "name": "Carlos Pereira",
  "email": "carlos@example.com"
}
```

**Response (201):**
```json
{
  "id": 101,
  "name": "Carlos Pereira",
  "email": "carlos@example.com",
  "sales_count": 0,
  "total_commission": 0,
  "created_at": "2025-10-06T11:00:00.000000Z",
  "updated_at": "2025-10-06T11:00:00.000000Z"
}
```

---

#### **GET** `/api/sellers/{id}`
Obter detalhes de um vendedor específico.

**Response (200):**
```json
{
  "id": 1,
  "name": "Maria Santos",
  "email": "maria@example.com",
  "sales_count": 15,
  "total_commission": 1234.56,
  "created_at": "2025-09-01T08:00:00.000000Z",
  "updated_at": "2025-10-06T10:00:00.000000Z"
}
```

---

#### **PUT** `/api/sellers/{id}`
Atualizar dados de um vendedor.

**Request:**
```json
{
  "name": "Maria Santos Silva",
  "email": "maria.santos@example.com"
}
```

**Response (200):**
```json
{
  "id": 1,
  "name": "Maria Santos Silva",
  "email": "maria.santos@example.com",
  "sales_count": 15,
  "total_commission": 1234.56,
  "created_at": "2025-09-01T08:00:00.000000Z",
  "updated_at": "2025-10-06T11:30:00.000000Z"
}
```

---

#### **DELETE** `/api/sellers/{id}`
Excluir um vendedor.

**Response (204):** Sem conteúdo (sucesso)

---

#### **POST** `/api/sellers/{id}/resend-commission-email`
Reenviar e-mail de comissão para um vendedor.

**Request (opcional):**
```json
{
  "date": "2025-10-06"
}
```
> Se `date` não for fornecido, usa a data atual.

**Response (200):**
```json
{
  "message": "E-mail de comissão foi agendado para reenvio",
  "seller": "Maria Santos",
  "date": "2025-10-06"
}
```

---

### 💰 Vendas (Sales)

#### **GET** `/api/sales?page=1&per_page=20&seller_id=5&date_from=2025-10-01&date_to=2025-10-31`
Listar vendas com filtros.

**Query Parameters:**
- `page` (opcional): Número da página
- `per_page` (opcional): Itens por página
- `seller_id` (opcional): Filtrar por vendedor
- `date_from` (opcional): Data inicial (YYYY-MM-DD)
- `date_to` (opcional): Data final (YYYY-MM-DD)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "seller_id": 5,
      "amount": 1500.00,
      "commission": 127.50,
      "sale_date": "2025-10-05",
      "created_at": "2025-10-05T14:30:00.000000Z",
      "updated_at": "2025-10-05T14:30:00.000000Z",
      "seller": {
        "id": 5,
        "name": "João Silva",
        "email": "joao@example.com"
      }
    },
    {
      "id": 2,
      "seller_id": 5,
      "amount": 750.00,
      "commission": 63.75,
      "sale_date": "2025-10-06",
      "created_at": "2025-10-06T09:15:00.000000Z",
      "updated_at": "2025-10-06T09:15:00.000000Z",
      "seller": {
        "id": 5,
        "name": "João Silva",
        "email": "joao@example.com"
      }
    }
  ],
  "current_page": 1,
  "last_page": 5,
  "per_page": 20,
  "total": 100
}
```

---

#### **POST** `/api/sales`
Criar nova venda (comissão calculada automaticamente em 8,5%).

**Request:**
```json
{
  "seller_id": 5,
  "amount": 2000.00,
  "sale_date": "2025-10-06"
}
```

**Response (201):**
```json
{
  "id": 101,
  "seller_id": 5,
  "amount": 2000.00,
  "commission": 170.00,
  "sale_date": "2025-10-06",
  "created_at": "2025-10-06T12:00:00.000000Z",
  "updated_at": "2025-10-06T12:00:00.000000Z",
  "seller": {
    "id": 5,
    "name": "João Silva",
    "email": "joao@example.com"
  }
}
```

---

#### **GET** `/api/sales/{id}`
Obter detalhes de uma venda.

**Response (200):**
```json
{
  "id": 1,
  "seller_id": 5,
  "amount": 1500.00,
  "commission": 127.50,
  "sale_date": "2025-10-05",
  "created_at": "2025-10-05T14:30:00.000000Z",
  "updated_at": "2025-10-05T14:30:00.000000Z",
  "seller": {
    "id": 5,
    "name": "João Silva",
    "email": "joao@example.com"
  }
}
```

---

#### **PUT** `/api/sales/{id}`
Atualizar uma venda.

**Request:**
```json
{
  "seller_id": 5,
  "amount": 1800.00,
  "sale_date": "2025-10-05"
}
```

**Response (200):**
```json
{
  "id": 1,
  "seller_id": 5,
  "amount": 1800.00,
  "commission": 153.00,
  "sale_date": "2025-10-05",
  "created_at": "2025-10-05T14:30:00.000000Z",
  "updated_at": "2025-10-06T13:00:00.000000Z",
  "seller": {
    "id": 5,
    "name": "João Silva",
    "email": "joao@example.com"
  }
}
```

---

#### **DELETE** `/api/sales/{id}`
Excluir uma venda.

**Response (204):** Sem conteúdo (sucesso)

---

### 📊 Dashboard

#### **GET** `/api/dashboard/stats`
Obter estatísticas do dashboard.

**Response (200):**
```json
{
  "total_sellers": 100,
  "total_sales": 250,
  "total_revenue": 125000.50,
  "total_commission": 10625.04,
  "recent_sales": [
    {
      "id": 250,
      "seller_id": 45,
      "amount": 890.00,
      "commission": 75.65,
      "sale_date": "2025-10-06",
      "created_at": "2025-10-06T15:30:00.000000Z",
      "updated_at": "2025-10-06T15:30:00.000000Z",
      "seller": {
        "id": 45,
        "name": "Ana Paula",
        "email": "ana@example.com"
      }
    }
  ],
  "current_month": {
    "sellers": 15,
    "sales": 45,
    "revenue": 22500.00,
    "commission": 1912.50
  },
  "previous_month": {
    "sellers": 12,
    "sales": 38,
    "revenue": 18000.00,
    "commission": 1530.00
  }
}
```

---

## 🌐 Serviços Disponíveis

| Serviço | URL/Porta | Descrição |
|---------|-----------|-----------|
| **Frontend (Vue.js)** | http://localhost:5173 | Interface web do sistema |
| **Backend (Laravel API)** | http://localhost:8080 | API RESTful |
| **Mailpit (Web UI)** | http://localhost:8025 | Visualizar e-mails de desenvolvimento |
| **Mailpit (SMTP)** | localhost:1025 | Porta SMTP para envio |
| **Redis** | localhost:6379 | Cache e filas |
| **MySQL** | localhost:33066 | Banco de dados |

---

## 📧 Sistema de E-mails

### 📨 E-mail para Vendedores (Diário)
- **Assunto:** Relatório Diário de Comissões - [DATA]
- **Conteúdo:** Número de vendas, valor total e comissão do dia
- **Enviado:** Diariamente às 08:00

### 📊 E-mail para Administradores (Diário)
- **Assunto:** Relatório Diário de Vendas - [DATA]
- **Conteúdo:** Resumo geral de todas as vendas e detalhes por vendedor
- **Enviado:** Diariamente às 08:00

### Comando Manual
```bash
# Enviar e-mails do dia anterior
docker-compose exec app php artisan sales:send-daily-emails

# Enviar e-mails de uma data específica
docker-compose exec app php artisan sales:send-daily-emails --date=2025-10-05
```

### Ativar Envio Automático
```bash
# Rodar em background (produção)
docker-compose exec app php artisan schedule:work
```

> Consulte **[SISTEMA_EMAILS.md](docs/SISTEMA_EMAILS.md)** para mais detalhes.

---

## 🛠️ Comandos Úteis

### Docker
```bash
# Ver status dos containers
docker-compose ps

# Parar todos os containers
docker-compose down

# Reiniciar um container específico
docker-compose restart app

# Ver logs
docker-compose logs -f
docker-compose logs -f app

# Acessar terminal do container
docker-compose exec app bash
```

### Laravel
```bash
# Limpar cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear

# Ver rotas
docker-compose exec app php artisan route:list

# Rodar testes
docker-compose exec app php artisan test
```

### Filas (Queue)
```bash
# Iniciar worker
docker-compose exec app php artisan queue:work

# Processar apenas um job
docker-compose exec app php artisan queue:work --once

# Ver jobs com falha
docker-compose exec app php artisan queue:failed

# Reprocessar jobs com falha
docker-compose exec app php artisan queue:retry all
```

### Mailpit
```bash
# Limpar todos os e-mails do Mailpit
curl -X DELETE http://localhost:8025/api/v1/messages

# Ver mensagens via API
curl http://localhost:8025/api/v1/messages
```

---

## 🧪 Executando os Testes

```bash
docker-compose exec app php artisan test
```

---

## 📁 Estrutura do Projeto

```
sales-management/
├── app/
│   ├── Console/Commands/       # Comandos Artisan (envio de e-mails)
│   ├── Http/
│   │   ├── Controllers/Api/    # Controllers da API
│   │   ├── Requests/           # Form Requests (validação)
│   │   └── Resources/          # API Resources (transformação de dados)
│   ├── Jobs/                   # Jobs de fila (envio de e-mails)
│   ├── Mail/                   # Mailables (templates de e-mail)
│   ├── Models/                 # Models Eloquent
│   └── Services/               # Lógica de negócio
├── database/
│   ├── factories/              # Factories para testes
│   ├── migrations/             # Migrations do banco
│   └── seeders/                # Seeders (dados de teste)
├── frontend/                   # Aplicação Vue.js 3
│   ├── src/
│   │   ├── components/         # Componentes Vue
│   │   ├── services/           # Serviços de API
│   │   ├── stores/             # Pinia stores
│   │   └── views/              # Páginas/Views
│   └── package.json
├── resources/views/emails/     # Templates Blade de e-mail
├── routes/
│   ├── api.php                 # Rotas da API
│   └── console.php             # Comandos agendados
├── docker/                     # Configurações Docker
├── docker-compose.yml
└── README.md
```

---

## 🤝 Contribuindo

Contribuições são bem-vindas! Sinta-se à vontade para abrir issues e pull requests.

---

## 📄 Licença

Este projeto é open-source sob a licença MIT.

---

## 📞 Suporte

Para mais informações, consulte os arquivos de documentação:
- [CONFIGURACAO_CONCLUIDA.md](docs/CONFIGURACAO_CONCLUIDA.md)
- [SISTEMA_EMAILS.md](docs/SISTEMA_EMAILS.md)
- [COMO_TESTAR_EMAILS.md](docs/COMO_TESTAR_EMAILS.md)

---

**Desenvolvido com ❤️ usando Laravel 12 e Vue.js 3**
