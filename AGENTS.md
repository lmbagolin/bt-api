# Regras para Agentes (AGENTS.md)

Para evitar problemas de permissão e garantir que os comandos rodem com as mesmas versões de dependências do servidor, os comandos deste projeto devem ser executados **dentro do Docker**.

## Container Principal: `bt-workspace-1`

- **Comandos de Backend (Laravel):**
  Devem ser executados no caminho `/var/www/api` dentro do container `bt-workspace-1`.
  Exemplo: `docker exec bt-workspace-1 bash -c "cd /var/www/api && php artisan serve"` ou `docker exec bt-workspace-1 bash -c "cd /var/www/api && composer require <package>"`

- **Comandos de Frontend (Quasar/Vue):**
  Devem ser executados no caminho `/var/www/spa` dentro do container `bt-workspace-1`.
  Exemplo: `docker exec bt-workspace-1 bash -c "cd /var/www/spa && npm install"` ou `docker exec bt-workspace-1 bash -c "cd /var/www/spa && npm run dev"`

Sempre priorize a execução via Docker ao invés de rodar comandos (`npm`, `composer`, `php`, etc.) diretamente na máquina local (`host`).
