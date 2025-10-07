# 📧 Como Testar E-mails no Sistema

## ✅ Status Atual

- ✅ **Mailpit** configurado e funcionando
- ✅ **Worker de filas** rodando automaticamente em background (container `laravel_queue`)
- ✅ **E-mail** testado e funcionando
- ✅ **Inicialização automática** com `docker-compose up -d`

---

## 🧪 Como Testar

### 1. Acesse o Sistema
```
http://localhost:5173/sellers
```

### 2. Clique no Ícone de Envelope (✉️)
Clique no ícone de envelope roxo ao lado de qualquer vendedor

### 3. Aguarde a Confirmação
Aparecerá um toast verde com: **"Email enviado"**

### 4. Verifique o Mailpit
Acesse o Mailpit em:
```
http://localhost:8025
```

O e-mail deve aparecer em alguns segundos!

---

## 📨 Informações do E-mail

**Assunto:** `Relatório Diário de Comissões - [DATA]`

**Conteúdo:**
- Nome do vendedor
- Data do relatório
- Número de vendas
- Valor total das vendas
- Comissão total (8,5%)

---

## 🔧 Comandos Úteis

### Ver Jobs na Fila
```bash
docker-compose exec app php artisan queue:monitor
```

### Processar Jobs Manualmente
```bash
docker-compose exec app php artisan queue:work --once
```

### Ver Logs do Worker
```bash
docker-compose exec app tail -f storage/logs/queue.log
```

### Reiniciar Worker
```bash
docker-compose restart app
docker-compose exec -d app php artisan queue:listen --tries=3 --timeout=60
```

### Limpar E-mails do Mailpit
```bash
curl -X DELETE http://localhost:8025/api/v1/messages
```

---

## 🐛 Troubleshooting

### E-mail não chegou?

1. **Verifique se o worker automático está rodando:**
   ```bash
   docker-compose ps queue
   docker-compose logs queue --tail=20
   ```

2. **Se necessário, reinicie o worker:**
   ```bash
   docker-compose restart queue
   ```

3. **Processe manualmente (para testes):**
   ```bash
   docker-compose exec app php artisan queue:work --once --verbose
   ```

4. **Verifique o Mailpit:**
   ```bash
   curl http://localhost:8025/api/v1/messages
   ```

5. **Reinicie todos os serviços:**
   ```bash
   docker-compose restart app mailpit queue
   ```

### ⚠️ Importante
O sistema está configurado com um **worker automático** que inicia junto com o Docker. Você **NÃO** precisa executar manualmente o comando `php artisan queue:work`.

---

## ✨ Funcionamento

1. **Usuário clica** no ícone de envelope
2. **Sistema despacha** job para a fila Redis
3. **Worker processa** o job em background
4. **Laravel envia** e-mail via Mailpit (SMTP)
5. **Mailpit captura** o e-mail
6. **E-mail aparece** em http://localhost:8025

---

**Worker Status:** ✅ Rodando em background  
**Última Atualização:** 06/10/2025

