<?php

namespace App\Http\Controllers;

use App\Models\Arena;
use App\Models\League;
use App\Models\LeagueStage;

abstract class Controller
{
    protected function authorizeArenaOwner(Arena $arena): void
    {
        if ($arena->owner_id !== auth()->id()) {
            abort(403, 'Acesso negado.');
        }
    }

    // Verifica que league pertence à arena E que o usuário é dono da arena
    protected function authorizeLeague(Arena $arena, League $league): void
    {
        if ($league->arena_id !== $arena->id) {
            abort(404);
        }
        $this->authorizeArenaOwner($arena);
    }

    // Verifica hierarquia completa arena → league → stage
    protected function authorizeStage(Arena $arena, League $league, LeagueStage $stage): void
    {
        if ($stage->league_id !== $league->id) {
            abort(404);
        }
        $this->authorizeLeague($arena, $league);
    }
}
