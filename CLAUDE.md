# CLAUDE.md — Instruções para o Claude Code

> Este arquivo é lido automaticamente pelo Claude Code no início de cada sessão.
> Todas as regras aqui são **obrigatórias e inegociáveis**.

---

## 🚨 REGRA CRÍTICA — PASSO 1 OBRIGATÓRIO

**ANTES de qualquer análise, resposta ou implementação, você DEVE ler o arquivo `AGENTS.md` na raiz do projeto.**

```
AGENTS.md
```

Este arquivo contém as regras de execução de comandos no Docker que se aplicam a **todas** as tarefas deste projeto.

### Por que isso é obrigatório:

- Todos os comandos (`php`, `composer`, `npm`, etc.) devem rodar **dentro do container Docker** `bt-workspace-1`
- Rodar comandos diretamente no host causará erros de permissão e inconsistências de versão
- Ignorar esta regra tornará qualquer solução inválida para este ambiente

---

## ⛔ REGRA CRÍTICA — BANCO DE DADOS

**NUNCA execute comandos que possam APAGAR ou ALTERAR dados do banco de dados.**

Comandos proibidos: `migrate:fresh`, `migrate:reset`, `db:wipe`, `DELETE FROM`, `TRUNCATE`, `DROP TABLE/DATABASE`.

**Esta regra é INEGOCIÁVEL.**

---

## 🐳 Execução de Comandos

### Backend (Laravel) — sempre via Docker:
```bash
docker exec bt-workspace-1 bash -c "cd /var/www/api && <comando>"
```

### Frontend (Quasar/Vue) — sempre via Docker:
```bash
docker exec bt-workspace-1 bash -c "cd /var/www/spa && <comando>"
```

---

## 📁 Estrutura de Agentes e Skills

Este projeto possui documentação estruturada para agentes de IA em `.agent/`:

- **Agentes**: `.agent/agents/` — especialistas com persona e regras definidas
- **Skills**: `.agent/skills/` — capacidades carregadas conforme a necessidade
- **Workflows**: `.agent/workflows/` — fluxos para comandos `/slash`
- **Arquitetura**: `.agent/ARCHITECTURE.md` — visão geral do sistema

Consulte `.cursorrules` para a lista completa de agentes e skills disponíveis.
