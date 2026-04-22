# Contrato Frontend → Backend: Ligas, Etapas e Inscrições

> Gerado em 2026-04-22. Descreve exatamente o que o SPA precisa da API para as páginas
> de ligas públicas (jogador) e dashboard de etapa (admin de arena).

---

## Contexto: o que já existe e funciona

| Endpoint existente | Status |
|---|---|
| `GET /leagues/open` | ✅ Funcionando |
| `GET /arenas/{arena}/leagues` | ✅ Funcionando |
| `GET /arenas/{arena}/leagues/{league}` | ✅ Funcionando |
| `POST/PUT/DELETE /arenas/{arena}/leagues` | ✅ Funcionando |
| `GET/POST/PUT/DELETE /arenas/{arena}/leagues/{league}/stages` (shallow) | ✅ Funcionando |
| `GET stages/{stage}/players` | ✅ Existe, mas estrutura de retorno precisa de ajuste |
| `POST stages/{stage}/players` | ✅ Existe, mas só usa o player do usuário logado |
| `PATCH stages/{stage}/players/{player}/confirm` | ✅ Existe |
| `DELETE stages/{stage}/players/{player}` | ✅ Existe |

---

## O que precisa ser construído

### 1. Rota pública: detalhe de uma liga

**`GET /leagues/{league}`**

Usado em: `PlayerLeagueDetailPage.vue` → `leagueStore.fetchPublicLeague(leagueId)`

**Resposta esperada:**
```json
{
  "data": {
    "id": 1,
    "nome": "Liga Verão 2026",
    "nivel": "Intermediário",
    "data_inicio": "2026-01-10",
    "data_prevista_termino": "2026-06-30",
    "numero_etapas": 5,
    "descricao": "Liga com 5 etapas...",
    "premiacao": "Troféus e premiação em dinheiro",
    "arena": {
      "id": 1,
      "name": "Arena Coqueiros",
      "city": "Florianópolis"
    },
    "stages": [
      {
        "id": 1,
        "data_etapa": "2026-02-15",
        "tipo": "Rei da Praia",
        "valor_inscricao": 80,
        "jogadores_por_grupo": 4,
        "vagas": 32,
        "classificam_total": 8,
        "disputa_3_lugar": true,
        "pontuacao_1": 150,
        "pontuacao_2": 100,
        "pontuacao_3": 75,
        "pontuacao_4": 75
      }
    ]
  }
}
```

**Implementação sugerida:**
- Adicionar método `publicShow(League $league)` no `LeagueController`
- Carregar relacionamentos: `league->load(['arena:id,name,city', 'stages'])`
- Rota pública (dentro do grupo `auth:sanctum` pois usuário precisa estar logado):
  ```php
  Route::get('leagues/{league}', [LeagueController::class, 'publicShow']);
  ```
- A rota deve ficar ANTES de `Route::apiResource('arenas.leagues', ...)` para não conflitar

---

### 2. Inscrição pública em etapa (jogador se inscreve com formulário)

**`POST /leagues/{league}/stages/{stage}/register`**

Usado em: `PlayerLeagueDetailPage.vue` → `leagueStore.registerForStage(leagueId, stageId, data)`

**Body recebido:**
```json
{
  "name": "João da Silva",
  "nickname": "Japa",
  "gender": "male",
  "level": "Intermediário",
  "whatsapp": "(51) 99999-9999"
}
```

**Lógica:**
1. Verificar se o usuário logado já tem um `Player` vinculado a esta arena
2. Se não tiver: criar um novo `Player` para esta arena com os dados enviados
3. Se tiver: atualizar os campos `nickname`, `level`, `whatsapp` se enviados
4. Verificar se já está inscrito na etapa → 422
5. Resolver status automaticamente (vagas disponíveis → `pending`; sem vagas → `waitlist`)
6. Criar registro em `league_stage_registrations` (ver seção 4)
7. Retornar a inscrição criada com o player

**Resposta esperada (201):**
```json
{
  "message": "Inscrição realizada com sucesso! Aguarde a confirmação.",
  "data": {
    "id": 5,
    "status": "pending",
    "valor_pago": null,
    "observacoes": null,
    "player": {
      "id": 10,
      "name": "João da Silva",
      "nickname": "Japa",
      "gender": "male",
      "level": "Intermediário",
      "whatsapp": "(51) 99999-9999"
    }
  }
}
```

**Rota:**
```php
Route::post('leagues/{league}/stages/{stage}/register', [LeagueStageRegistrationController::class, 'selfRegister']);
```

---

### 3. Migração: ajustar tabela `league_stage_players` → `league_stage_registrations`

O frontend usa campos e status diferentes dos atuais. Criar nova migration:

```
php artisan make:migration create_league_stage_registrations_table
```

**Schema da nova tabela:**
```php
Schema::create('league_stage_registrations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('league_stage_id')
          ->constrained('league_stages')
          ->cascadeOnDelete();
    $table->foreignId('player_id')
          ->constrained('players')
          ->cascadeOnDelete();
    $table->enum('status', ['pending', 'confirmed', 'waitlist', 'cancelled'])
          ->default('pending');
    $table->decimal('valor_pago', 8, 2)->nullable();
    $table->unsignedSmallInteger('posicao_grupo')->nullable();
    $table->text('observacoes')->nullable();
    $table->timestamps();

    $table->unique(['league_stage_id', 'player_id']);
});
```

**Mapeamento de status (para referência):**
| Novo (frontend) | Antigo (pivot) | Significado |
|---|---|---|
| `pending` | `registered` | Inscrito, aguardando confirmação |
| `confirmed` | `confirmed` | Pago e confirmado |
| `waitlist` | `alternate` | Na lista de espera/suplente |
| `cancelled` | — | Inscrição cancelada |

> **Atenção:** Após criar a nova tabela e os novos endpoints, os endpoints antigos
> (`stages/{stage}/players`) podem ser mantidos por compatibilidade ou removidos.
> O frontend novo usa apenas os endpoints abaixo.

---

### 4. CRUD de inscrições (admin de arena) — `LeagueStageRegistrationController`

Criar: `app/Http/Controllers/LeagueStageRegistrationController.php`

Todos os endpoints requerem `auth:sanctum` e que o usuário seja dono da arena.

---

#### 4.1 Listar inscrições

**`GET /arenas/{arena}/leagues/{league}/stages/{stage}/registrations`**

Usado em: `ArenaLeagueStageDetailPage.vue` → `leagueStore.fetchStageRegistrations()`

**Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "status": "confirmed",
      "valor_pago": 80.00,
      "posicao_grupo": null,
      "observacoes": null,
      "player": {
        "id": 10,
        "name": "João da Silva",
        "nickname": "Japa",
        "gender": "male",
        "level": "Intermediário",
        "whatsapp": "(51) 99999-9999"
      }
    }
  ]
}
```

---

#### 4.2 Criar inscrição manualmente (admin)

**`POST /arenas/{arena}/leagues/{league}/stages/{stage}/registrations`**

Usado em: `ArenaLeagueStageDetailPage.vue` → botão "Inscrever Jogador"

**Body recebido:**
```json
{
  "name": "João da Silva",
  "nickname": "Japa",
  "gender": "male",
  "level": "Intermediário",
  "whatsapp": "(51) 99999-9999",
  "status": "pending",
  "valor_pago": null,
  "posicao_grupo": null,
  "observacoes": ""
}
```

**Lógica:**
1. Buscar ou criar `Player` nesta arena com os dados enviados (match por `name` + `arena_id`, ou criar novo)
2. Verificar se já existe inscrição para o player nesta etapa → 422
3. Criar registro em `league_stage_registrations`
4. Retornar registro completo com player

---

#### 4.3 Editar inscrição

**`PUT /arenas/{arena}/leagues/{league}/stages/{stage}/registrations/{registration}`**

Usado em: dialog "Editar Inscrição" no admin

**Body recebido (apenas campos da inscrição, não do jogador):**
```json
{
  "status": "confirmed",
  "valor_pago": 80.00,
  "posicao_grupo": 2,
  "observacoes": "Pagamento via PIX"
}
```

---

#### 4.4 Alterar status rapidamente

**`PATCH /arenas/{arena}/leagues/{league}/stages/{stage}/registrations/{registration}/status`**

Usado em: chip de status clicável na tabela (troca rápida de status)

**Body recebido:**
```json
{
  "status": "confirmed"
}
```

**Validação:** `status` deve ser um dos valores: `pending`, `confirmed`, `waitlist`, `cancelled`

---

#### 4.5 Remover inscrição

**`DELETE /arenas/{arena}/leagues/{league}/stages/{stage}/registrations/{registration}`**

---

### 5. Resources necessários

#### `LeagueStageRegistrationResource`

```php
// app/Http/Resources/LeagueStageRegistrationResource.php
public function toArray($request): array
{
    return [
        'id'           => $this->id,
        'status'       => $this->status,
        'valor_pago'   => $this->valor_pago,
        'posicao_grupo'=> $this->posicao_grupo,
        'observacoes'  => $this->observacoes,
        'player'       => new PlayerResource($this->whenLoaded('player')),
    ];
}
```

#### `LeagueResource` — verificar se já inclui `arena` e `stages`

O `GET /leagues/open` e `GET /leagues/{league}` precisam retornar `arena` e `stages`.
Confirmar que `LeagueResource` inclui esses relacionamentos quando carregados.

---

### 6. Model: `LeagueStageRegistration`

```php
// app/Models/LeagueStageRegistration.php
protected $fillable = [
    'league_stage_id',
    'player_id',
    'status',
    'valor_pago',
    'posicao_grupo',
    'observacoes',
];

protected $casts = [
    'valor_pago' => 'decimal:2',
];

public function stage(): BelongsTo
{
    return $this->belongsTo(LeagueStage::class, 'league_stage_id');
}

public function player(): BelongsTo
{
    return $this->belongsTo(Player::class);
}
```

Adicionar em `LeagueStage`:
```php
public function registrations(): HasMany
{
    return $this->hasMany(LeagueStageRegistration::class);
}
```

---

### 7. Rotas a adicionar em `routes/api.php`

```php
// Dentro do grupo auth:sanctum

// Liga pública (deve ficar antes do apiResource de arenas.leagues)
Route::get('leagues/{league}', [LeagueController::class, 'publicShow']);
Route::post('leagues/{league}/stages/{stage}/register', [LeagueStageRegistrationController::class, 'selfRegister']);

// Admin: CRUD de inscrições da etapa
Route::prefix('arenas/{arena}/leagues/{league}/stages/{stage}/registrations')
    ->controller(LeagueStageRegistrationController::class)
    ->group(function () {
        Route::get('/',                          'index');
        Route::post('/',                         'store');
        Route::put('/{registration}',            'update');
        Route::patch('/{registration}/status',   'updateStatus');
        Route::delete('/{registration}',         'destroy');
    });
```

---

### 8. Checklist de implementação

- [ ] Migration `create_league_stage_registrations_table` com os campos descritos
- [ ] Model `LeagueStageRegistration` com fillable, casts e relacionamentos
- [ ] Relacionamento `registrations()` em `LeagueStage`
- [ ] Resource `LeagueStageRegistrationResource`
- [ ] `LeagueController::publicShow()` — retorna liga com `arena` e `stages`
- [ ] `LeagueStageRegistrationController::selfRegister()` — auto-inscrição pública
- [ ] `LeagueStageRegistrationController::index()` — lista inscrições com player
- [ ] `LeagueStageRegistrationController::store()` — admin cria inscrição manual
- [ ] `LeagueStageRegistrationController::update()` — admin edita inscrição
- [ ] `LeagueStageRegistrationController::updateStatus()` — troca status rapidamente
- [ ] `LeagueStageRegistrationController::destroy()` — remove inscrição
- [ ] Rotas registradas em `api.php` na ordem correta
- [ ] `LeagueResource` retorna `arena:id,name,city` e `stages` quando carregados
- [ ] `LeagueStageRegistrationController::publicIndex()` — lista pública de inscrições de uma etapa

---

### 10. Novo endpoint: lista pública de inscrições de uma etapa

**`GET /leagues/{league}/stages/{stage}/registrations`**

Usado em: `PlayerLeagueDetailPage.vue` → `leagueStore.fetchPublicStageRegistrations(leagueId, stageId)`

Qualquer usuário autenticado pode ver. Retorna apenas inscrições com status `pending`, `confirmed` ou `waitlist` (não retorna `cancelled`).

**Resposta:**
```json
{
  "data": [
    {
      "id": 1,
      "status": "confirmed",
      "valor_pago": 80.00,
      "posicao_grupo": null,
      "observacoes": null,
      "player": {
        "id": 10,
        "name": "João da Silva",
        "nickname": "Japa",
        "gender": "male",
        "level": "Intermediário",
        "whatsapp": "(51) 99999-9999"
      }
    }
  ]
}
```

**Rota (adicionar em `api.php` antes do apiResource de `arenas.leagues`):**
```php
Route::get('leagues/{league}/stages/{stage}/registrations', [LeagueStageRegistrationController::class, 'publicIndex']);
```

**Implementação sugerida em `LeagueStageRegistrationController`:**
```php
public function publicIndex(League $league, LeagueStage $stage): AnonymousResourceCollection
{
    $registrations = $stage->registrations()
        ->with('player')
        ->whereIn('status', ['pending', 'confirmed', 'waitlist'])
        ->orderBy('created_at')
        ->get();

    return LeagueStageRegistrationResource::collection($registrations);
}
```

---

### 9. Autorização

| Endpoint | Quem pode chamar |
|---|---|
| `GET /leagues/open` | Qualquer usuário autenticado |
| `GET /leagues/{league}` | Qualquer usuário autenticado |
| `GET /leagues/{league}/stages/{stage}/registrations` | Qualquer usuário autenticado |
| `POST /leagues/{league}/stages/{stage}/register` | Qualquer usuário autenticado (se inscreve) |
| `GET .../registrations` (admin) | Dono da arena |
| `POST .../registrations` | Dono da arena |
| `PUT .../registrations/{id}` | Dono da arena |
| `PATCH .../registrations/{id}/status` | Dono da arena |
| `DELETE .../registrations/{id}` | Dono da arena |

Para verificar dono da arena usar: `$arena->owner_id !== auth()->id()` → retornar 403.
