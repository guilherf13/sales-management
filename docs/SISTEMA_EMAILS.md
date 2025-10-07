# 📧 Sistema de E-mails para Administrador e Vendedor

## ✅ Funcionalidades Implementadas

### 1. **E-mail Diário para Administrador (Gestor)**
- ✅ Enviado automaticamente todos os dias às **08:00**
- ✅ Contém **soma total de todas as vendas do dia**
- ✅ Mostra **número total de vendas**
- ✅ Mostra **comissão total paga**
- ✅ Lista **vendas por vendedor** (nome, email, quantidade, valor, comissão)

### 2. **E-mail Diário para Vendedores**
- ✅ Enviado para **todos os vendedores** com suas comissões individuais
- ✅ Mesmo sem vendas, o e-mail é enviado com valores zerados

---

## 👥 Sistema de Perfis

### Perfis Disponíveis:

| Perfil | Descrição | Acesso |
|--------|-----------|--------|
| **Gestor** | Administrador do sistema | Recebe relatório diário de vendas |
| **Seller** | Vendedor | Recebe relatório diário de comissões |

---

## 🔐 Usuários de Teste

| Nome | E-mail | Senha | Perfil |
|------|--------|-------|--------|
| Administrador | `admin@test.com` | `password` | Gestor

---

## 🤖 Comando Artisan

### Executar Manualmente:
```bash
docker-compose exec app php artisan sales:send-daily-emails
```

### Opções:
```bash
# Enviar para uma data específica
docker-compose exec app php artisan sales:send-daily-emails --date=2025-10-05
```

### O que o comando faz:
1. Busca todos os **vendedores** cadastrados
2. Envia e-mail de **comissão** para cada vendedor
3. Busca todos os **administradores** (perfil Gestor)
4. Envia **relatório de vendas** para cada administrador

---

## ⏰ Agendamento Automático

### Configurado em: `routes/console.php`

```php
Schedule::command('sales:send-daily-emails')->dailyAt('08:00');
```

✅ **E-mails serão enviados automaticamente todos os dias às 08:00**

### Para ativar o agendamento:

**No Docker:**
```bash
docker-compose exec app php artisan schedule:work
```

**Em produção (crontab):**
```bash
* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📧 Formato do E-mail para Administrador

**Assunto:**  
`Relatório Diário de Vendas - 06/10/2025`

**Conteúdo:**
```
Relatório Diário de Vendas
Relatório Administrativo de 06/10/2025

Resumo Geral
- Valor Total de Vendas: R$ 12.345,67
- Número Total de Vendas: 45
- Comissão Total Paga: R$ 1.049,38

Vendas por Vendedor
┌─────────────────┬───────────────────────┬─────────┬──────────────┬────────────┐
│ Nome            │ Email                 │ Qtd     │ Valor Total  │ Comissão   │
├─────────────────┼───────────────────────┼─────────┼──────────────┼────────────┤
│ João Silva      │ joao@example.com      │ 12      │ R$ 3.450,00  │ R$ 293,25  │
│ Maria Santos    │ maria@example.com     │ 8       │ R$ 2.100,00  │ R$ 178,50  │
└─────────────────┴───────────────────────┴─────────┴──────────────┴────────────┘
```

---

## 📧 Formato do E-mail para Vendedor

**Assunto:**  
`Relatório Diário de Comissões - 06/10/2025`

**Conteúdo:**
```
Relatório Diário de Comissões
Olá João Silva,

Aqui está o resumo das suas vendas do dia 06/10/2025:

Resumo de Vendas
- Número de Vendas: 3
- Valor Total das Vendas: R$ 1.500,00
- Comissão Total (8,5%): R$ 127,50

Obrigado pelo seu trabalho! Continue com essa ótima performance de vendas.
```

---

## 🧪 Como Testar

### 1. Verificar Usuários
```bash
docker-compose exec app php artisan tinker
>>> User::where('perfil', 'Gestor')->get();
```

### 2. Enviar E-mails de Teste
```bash
# Limpar Mailpit
curl -X DELETE http://localhost:8025/api/v1/messages

# Enviar e-mails
docker-compose exec app php artisan sales:send-daily-emails

# Verificar Mailpit
http://localhost:8025
```

### 3. Verificar Fila
```bash
# Ver logs do worker
docker-compose logs -f queue

# Processar manualmente (se necessário)
docker-compose exec app php artisan queue:work --once

# Ver logs de email
docker-compose logs app | grep -i mail
```

### 4. Worker Automático
O sistema está configurado para iniciar o worker automaticamente:
- ✅ Container `laravel_queue` roda em background
- ✅ Inicia automaticamente com `docker-compose up -d`
- ✅ Reinicia automaticamente se falhar
- ✅ Processa jobs de email continuamente

---

## 🔧 Arquivos Modificados

### Backend:
- ✅ `database/seeders/UserSeeder.php` - Cria admin e vendedor
- ✅ `app/Console/Commands/SendDailyEmailsCommand.php` - Comando melhorado
- ✅ `app/Mail/DailySalesReportMail.php` - Assunto traduzido
- ✅ `resources/views/emails/daily-sales-report.blade.php` - Data formatada
- ✅ `app/Providers/AppServiceProvider.php` - Removido perfil 'Funcionario'
- ✅ `routes/console.php` - Agendamento configurado

### Frontend:
- ✅ `frontend/src/types/index.ts` - Tipo User atualizado
- ✅ `frontend/src/services/authService.ts` - Tipo User atualizado
- ✅ `frontend/src/components/StudentCard.vue` - Props atualizadas

---

## 📊 Relatórios

### E-mails Enviados:
- **101 vendedores** = 101 e-mails de comissão
- **1 administrador** = 1 e-mail de relatório geral
- **Total**: 102 e-mails por execução

---

## 🚀 Produção

### Checklist:
- [ ] Configurar cron job para `schedule:run`
- [ ] Verificar fuso horário do servidor
- [ ] Configurar SMTP real (substituir Mailpit)
- [ ] Testar em staging primeiro
- [ ] Monitorar logs de e-mail
- [ ] Configurar alerta para falhas

---

**Data de Implementação**: 06/10/2025  
**Status**: ✅ Totalmente Funcional

